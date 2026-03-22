<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles WooCommerce dynamic product creation, cart/order meta management, and access grant on order complete.
 */
class RCIL_WooCommerce
{

    /**
     * Instance of this class.
     */
    private static $instance = null;

    /**
     * Get instance.
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'persist_line_item_meta'], 10, 4);
        add_action('woocommerce_order_item_name', [$this, 'display_lessons_in_order_items'], 10, 2);
        add_action('woocommerce_payment_complete', [$this, 'grant_lesson_access_on_complete']);
        add_action('woocommerce_order_status_completed', [$this, 'grant_lesson_access_on_complete']);
        add_action('woocommerce_order_status_processing', [$this, 'grant_lesson_access_on_complete']);
        add_filter('woocommerce_hidden_order_itemmeta', [$this, 'hide_order_item_meta']);
        add_action('template_redirect', [$this, 'prevent_direct_product_access']);
    }

    /**
     * Prevent direct access to dynamic product pages.
     */
    public function prevent_direct_product_access()
    {
        if (function_exists('is_product') && is_product()) {
            $product_id = get_the_ID();
            if (get_post_meta($product_id, '_rcil_is_dynamic_product', true) === '1') {
                wp_redirect(home_url());
                exit;
            }
        }
    }

    /**
     * Find or create a dynamic product for the exact selection.
     * Re-uses same product based on course + selected ids to avoid bloating db.
     */
    public function get_or_create_dynamic_product($data)
    {
        $course_id = (int) $data['course_id'];
        $lesson_ids = (array) $data['lesson_ids'];
        $total_price = (int) $data['total_price'];
        $unit_price = (int) $data['unit_price'];

        $course_title = get_the_title($course_id);
        $lesson_count = count($lesson_ids);

        if ($lesson_count === 1) {
            $product_name = sprintf(__('1 lesson %s', 'red-cultural-individual-lesson'), $course_title);
        } else {
            $product_name = sprintf(__('%d lessons %s', 'red-cultural-individual-lesson'), $lesson_count, $course_title);
        }

        // Logic: Search for an existing product marked as RCIL purchase with these specific lessons.
        // If found, update price and return. Otherwise create.
        // Optimization: store lesson selection in meta and compare.

        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'title' => $product_name,
            'posts_per_page' => 1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_rcil_is_dynamic_product',
                    'value' => '1',
                ],
                [
                    'key' => '_rcil_course_id',
                    'value' => $course_id,
                ],
                [
                    'key' => '_rcil_selected_lesson_ids',
                    'value' => maybe_serialize($lesson_ids),
                ]
            ]
        ];

        $existing_products = get_posts($args);

        if (!empty($existing_products)) {
            $product_id = $existing_products[0]->ID;
            update_post_meta($product_id, '_price', $total_price);
            update_post_meta($product_id, '_regular_price', $total_price);
            return $product_id;
        }

        // Create dynamic product
        $product = new WC_Product_Simple();
        $product->set_name($product_name);
        $product->set_regular_price($total_price);
        $product->set_status('publish');
        $product->set_virtual(true);
        $product->set_downloadable(false);
        $product->set_catalog_visibility('hidden');

        $description = "<h2>" . __('Purchased Lessons:', 'red-cultural-individual-lesson') . "</h2><ul>";
        foreach ($data['lesson_titles'] as $index => $title) {
            $description .= "<li>" . esc_html($title) . " - id=" . $lesson_ids[$index] . "</li>";
        }
        $description .= "</ul>";
        $product->set_description($description);

        $product_id = $product->save();

        if (!$product_id) {
            return new WP_Error('rcil_product_creation_failed', __('Failed to prepare specific purchase item.', 'red-cultural-individual-lesson'));
        }

        update_post_meta($product_id, '_rcil_is_dynamic_product', '1');
        update_post_meta($product_id, '_rcil_course_id', $course_id);
        update_post_meta($product_id, '_rcil_selected_lesson_ids', maybe_serialize($lesson_ids));
        update_post_meta($product_id, '_rcil_selected_lesson_titles', maybe_serialize($data['lesson_titles']));
        update_post_meta($product_id, '_rcil_per_lesson_price', $unit_price);
        update_post_meta($product_id, '_rcil_lesson_count', count($lesson_ids));
        update_post_meta($product_id, '_rcil_is_full_course', isset($data['is_full_course']) ? $data['is_full_course'] : '0');

        return $product_id;
    }

    /**
     * Persist custom data from cart item into the order line item metadata.
     */
    public function persist_line_item_meta($item, $cart_item_key, $values, $order)
    {
        // If cart values contain it, persist it.
        if (isset($values['_is_rcil_purchase'])) {
            $item->add_meta_data('_rcil_target_course', get_the_title($values['_rcil_course_id']), true);
            $item->add_meta_data('_rcil_course_id', $values['_rcil_course_id'], true);
            $item->add_meta_data('_rcil_lesson_ids', maybe_serialize($values['_rcil_lesson_ids']), true);
            $item->add_meta_data('_rcil_lesson_titles', maybe_serialize($values['_rcil_lesson_titles']), true);
            $item->add_meta_data('_rcil_per_lesson_price', $values['_rcil_per_lesson_price'], true);
            $item->add_meta_data('_rcil_lesson_count', count($values['_rcil_lesson_ids']), true);
            $item->add_meta_data('_is_rcil_purchase', '1', true);
            $item->add_meta_data('_rcil_is_full_course', isset($values['_rcil_is_full_course']) ? $values['_rcil_is_full_course'] : '0', true);
        }
    }

    /**
     * Explicitly hide our internal metadata from WooCommerce front-end views (like order-received receipt and emails).
     */
    public function hide_order_item_meta($hidden_meta)
    {
        $hidden_meta[] = '_rcil_target_course';
        $hidden_meta[] = '_rcil_course_id';
        $hidden_meta[] = '_rcil_lesson_ids';
        $hidden_meta[] = '_rcil_lesson_titles';
        $hidden_meta[] = '_rcil_per_lesson_price';
        $hidden_meta[] = '_rcil_lesson_count';
        $hidden_meta[] = '_is_rcil_purchase';
        $hidden_meta[] = '_rcil_is_full_course';
        return $hidden_meta;
    }

    /**
     * Display selected lessons in the order dashboard and emails.
     */
    public function display_lessons_in_order_items($item_name, $item)
    {
        if ($item->get_meta('_is_rcil_purchase')) {
            $titles = maybe_unserialize($item->get_meta('_rcil_lesson_titles'));
            $ids = maybe_unserialize($item->get_meta('_rcil_lesson_ids'));

            if (is_array($titles)) {
                $item_name .= '<br/><small><strong>' . __('Individual Lessons Included:', 'red-cultural-individual-lesson') . '</strong><ul style="margin:5px 0;">';
                foreach ($titles as $index => $title) {
                    $item_name .= '<li>' . esc_html($title) . ' - id=' . $ids[$index] . '</li>';
                }
                $item_name .= '</ul></small>';
            }
        }
        return $item_name;
    }

    /**
     * Grant access when payment completes.
     */
    public function grant_lesson_access_on_complete($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        if (!$user_id) {
            return;
        }

        foreach ($order->get_items() as $item_id => $item) {
            if ($item->get_meta('_is_rcil_purchase')) {
                $course_id = (int) $item->get_meta('_rcil_course_id');
                $lesson_ids = maybe_unserialize($item->get_meta('_rcil_lesson_ids'));
                $is_full = ($item->get_meta('_rcil_is_full_course') == '1');

                if ($course_id && is_array($lesson_ids)) {
                    RCIL_Access_Control::get_instance()->grant_access($user_id, $course_id, $lesson_ids, $order_id, $is_full);
                }
            }
        }
    }
}
