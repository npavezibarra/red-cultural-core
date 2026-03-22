<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX endpoints for RCIL.
 */
class RCIL_Ajax
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
        add_action('wp_ajax_rcil_create_individual_lesson_product', [$this, 'handle_purchase_request']);
        add_action('wp_ajax_nopriv_rcil_create_individual_lesson_product', [$this, 'handle_purchase_request']);
        
        // Alumni List endpoints
        add_action('wp_ajax_rcil_get_alumni_list', [$this, 'handle_get_alumni_list']);

        // Admin-only: inline lesson video URL update
        add_action('wp_ajax_rcil_update_lesson_video_url', [$this, 'handle_update_lesson_video_url']);
        add_action('wp_ajax_rcil_update_lesson_title', [$this, 'handle_update_lesson_title']);
        add_action('wp_ajax_rcil_update_lesson_details', [$this, 'handle_update_lesson_details']);
        add_action('wp_ajax_rcil_delete_lesson', [$this, 'handle_delete_lesson']);
    }

    /**
     * Sanitize LearnDash "Video URL" input (URL, iFrame, or shortcode).
     */
    private function sanitize_video_url_input($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $allowed = [
            'iframe' => [
                'src' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'allow' => true,
                'allowfullscreen' => true,
                'title' => true,
                'referrerpolicy' => true,
                'loading' => true,
                'style' => true,
            ],
        ];

        return wp_kses($value, $allowed);
    }

    /**
     * Admin-only: update LearnDash lesson "Video Progression" settings.
     */
    public function handle_update_lesson_video_url()
    {
        check_ajax_referer('rcil_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized.', 'red-cultural-individual-lesson'));
        }

        $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
        if (!$lesson_id || get_post_type($lesson_id) !== 'sfwd-lessons') {
            wp_send_json_error(__('Invalid lesson.', 'red-cultural-individual-lesson'));
        }

        if (!current_user_can('edit_post', $lesson_id)) {
            wp_send_json_error(__('Unauthorized.', 'red-cultural-individual-lesson'));
        }

        $raw = isset($_POST['video_url']) ? wp_unslash($_POST['video_url']) : '';
        $video_url = $this->sanitize_video_url_input($raw);

        $enabled = ($video_url !== '') ? 'on' : '';

        if (function_exists('learndash_update_setting')) {
            learndash_update_setting($lesson_id, 'lesson_video_url', $video_url);
            learndash_update_setting($lesson_id, 'lesson_video_enabled', $enabled);

            if ('on' === $enabled && function_exists('learndash_get_setting')) {
                $shown = learndash_get_setting($lesson_id, 'lesson_video_shown');
                if (empty($shown)) {
                    learndash_update_setting($lesson_id, 'lesson_video_shown', 'BEFORE');
                }
            }
        } else {
            $settings = get_post_meta($lesson_id, '_sfwd-lessons', true);
            if (!is_array($settings)) {
                $settings = [];
            }
            $settings['sfwd-lessons_lesson_video_url'] = $video_url;
            $settings['sfwd-lessons_lesson_video_enabled'] = $enabled;
            if ('on' === $enabled && empty($settings['sfwd-lessons_lesson_video_shown'])) {
                $settings['sfwd-lessons_lesson_video_shown'] = 'BEFORE';
            }
            update_post_meta($lesson_id, '_sfwd-lessons', $settings);
        }

        wp_send_json_success([
            'lesson_id' => $lesson_id,
            'video_url' => $video_url,
            'enabled' => $enabled,
        ]);
    }

    /**
     * Admin-only: update lesson title from the course page.
     */
    public function handle_update_lesson_title()
    {
        check_ajax_referer('rcil_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized.', 'red-cultural-individual-lesson'));
        }

        $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
        if (!$lesson_id || get_post_type($lesson_id) !== 'sfwd-lessons') {
            wp_send_json_error(__('Invalid lesson.', 'red-cultural-individual-lesson'));
        }

        if (!current_user_can('edit_post', $lesson_id)) {
            wp_send_json_error(__('Unauthorized.', 'red-cultural-individual-lesson'));
        }

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        if ($title === '') {
            wp_send_json_error(__('Title cannot be empty.', 'red-cultural-individual-lesson'));
        }

        $result = wp_update_post([
            'ID' => $lesson_id,
            'post_title' => $title,
        ], true);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success([
            'lesson_id' => $lesson_id,
            'title' => (string) get_the_title($lesson_id),
        ]);
    }

    /**
     * Admin-only: update lesson title + video progression fields in one request.
     */
    public function handle_update_lesson_details()
    {
        check_ajax_referer('rcil_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('No autorizado.', 'red-cultural-individual-lesson'));
        }

        $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
        if (!$lesson_id || get_post_type($lesson_id) !== 'sfwd-lessons') {
            wp_send_json_error(__('Lección no válida.', 'red-cultural-individual-lesson'));
        }

        if (!current_user_can('edit_post', $lesson_id)) {
            wp_send_json_error(__('No autorizado.', 'red-cultural-individual-lesson'));
        }

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
        if ($title === '') {
            wp_send_json_error(__('El título no puede estar vacío.', 'red-cultural-individual-lesson'));
        }

        $raw_video = isset($_POST['video_url']) ? wp_unslash($_POST['video_url']) : '';
        $video_url = $this->sanitize_video_url_input($raw_video);

        $zoom_url = isset($_POST['zoom_url']) ? esc_url_raw(wp_unslash($_POST['zoom_url'])) : '';
        $available_from = isset($_POST['available_from']) ? (string) wp_unslash($_POST['available_from']) : '';
        $timestamp = ($available_from !== '') ? strtotime($available_from) : 0;

        $result = wp_update_post([
            'ID' => $lesson_id,
            'post_title' => $title,
        ], true);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Save Zoom URL
        update_post_meta($lesson_id, '_rc_zoom_url', $zoom_url);

        // Save Availability
        if (function_exists('learndash_update_setting')) {
            learndash_update_setting($lesson_id, 'lesson_access_from', $timestamp);
            update_post_meta($lesson_id, 'lesson_access_from', $timestamp);
        } else {
             update_post_meta($lesson_id, 'lesson_access_from', $timestamp);
        }

        // Reuse the same logic as handle_update_lesson_video_url().
        $enabled = ($video_url !== '') ? 'on' : '';

        if (function_exists('learndash_update_setting')) {
            learndash_update_setting($lesson_id, 'lesson_video_url', $video_url);
            learndash_update_setting($lesson_id, 'lesson_video_enabled', $enabled);

            if ('on' === $enabled && function_exists('learndash_get_setting')) {
                $shown = learndash_get_setting($lesson_id, 'lesson_video_shown');
                if (empty($shown)) {
                    learndash_update_setting($lesson_id, 'lesson_video_shown', 'BEFORE');
                }
            }
        } else {
            $settings = get_post_meta($lesson_id, '_sfwd-lessons', true);
            if (!is_array($settings)) {
                $settings = [];
            }
            $settings['sfwd-lessons_lesson_video_url'] = $video_url;
            $settings['sfwd-lessons_lesson_video_enabled'] = $enabled;
            if ('on' === $enabled && empty($settings['sfwd-lessons_lesson_video_shown'])) {
                $settings['sfwd-lessons_lesson_video_shown'] = 'BEFORE';
            }
            update_post_meta($lesson_id, '_sfwd-lessons', $settings);
        }

        $access_from = (int) get_post_meta($lesson_id, 'lesson_access_from', true);
        $available_from_iso = $access_from ? date('Y-m-d\TH:i', $access_from) : '';
        $available_from_display = $access_from ? date_i18n(get_option('date_format') . ' H:i', $access_from) : '';

        wp_send_json_success([
            'lesson_id' => $lesson_id,
            'title' => (string) get_the_title($lesson_id),
            'video_url' => $video_url,
            'zoom_url' => $zoom_url,
            'available_from_iso' => $available_from_iso,
            'available_from_display' => $available_from_display,
            'enabled' => $enabled,
        ]);
    }

    /**
     * Admin-only: delete lesson (moves it to Trash).
     */
    public function handle_delete_lesson()
    {
        check_ajax_referer('rcil_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized.', 'red-cultural-individual-lesson'));
        }

        $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
        if (!$lesson_id || get_post_type($lesson_id) !== 'sfwd-lessons') {
            wp_send_json_error(__('Invalid lesson.', 'red-cultural-individual-lesson'));
        }

        if (!current_user_can('delete_post', $lesson_id)) {
            wp_send_json_error(__('Unauthorized.', 'red-cultural-individual-lesson'));
        }

        $trashed = wp_trash_post($lesson_id);
        if (empty($trashed) || is_wp_error($trashed)) {
            wp_send_json_error(__('Could not delete lesson.', 'red-cultural-individual-lesson'));
        }

        wp_send_json_success([
            'lesson_id' => $lesson_id,
        ]);
    }

    /**
     * Handle AJAX creation/preparation of individual lesson product.
     */
    public function handle_purchase_request()
    {
        check_ajax_referer('rcil_ajax_nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $lessons_data = isset($_POST['lessons']) ? (array) $_POST['lessons'] : [];

        if (!$course_id || empty($lessons_data)) {
            wp_send_json_error(__('Invalid course or no lessons selected.', 'red-cultural-individual-lesson'));
        }

        // Recalculate everything server-side
        $unit_price = rcil_get_course_lesson_price($course_id);
        if (!$unit_price) {
            wp_send_json_error(__('Individual purchase is disabled for this course.', 'red-cultural-individual-lesson'));
        }

        // Validate that selected lessons belong to this course
        $valid_lessons_in_course = rcil_get_course_lessons($course_id);
        $valid_ids = [];
        foreach ($valid_lessons_in_course as $v_lesson) {
            $valid_ids[] = (int) $v_lesson['post']->ID;
        }

        $selected_lesson_ids_all = [];
        $selected_lesson_ids = [];
        $selected_lesson_titles = [];
        foreach ($lessons_data as $l_item) {
            $l_id = absint($l_item['id']);
            if (in_array($l_id, $valid_ids)) {
                $selected_lesson_ids_all[] = $l_id;
                // Also check if user ALREADY has access.
                if (is_user_logged_in() && rcil_user_has_lesson_access(get_current_user_id(), $l_id)) {
                    continue; // Skip already owned.
                }
                $selected_lesson_ids[] = $l_id;
                $selected_lesson_titles[] = get_the_title($l_id); // Get fresh title
            }
        }

        // If the user only selected lessons they already own, we still allow a full-course purchase flow.
        $selected_lesson_ids_all = array_values(array_unique(array_filter($selected_lesson_ids_all)));

        if (empty($selected_lesson_ids) && empty($selected_lesson_ids_all)) {
            wp_send_json_error(__('All selected lessons are already owned or invalid.', 'red-cultural-individual-lesson'));
        }

        $is_full_course = (isset($_POST['is_full_course']) && $_POST['is_full_course'] == '1');
        
        // Comprehensive Price Detection (Same as Frontend)
        $full_course_price = rcil_get_full_course_price($course_id);

        $all_lessons_selected = (!empty($selected_lesson_ids_all) && count($selected_lesson_ids_all) === count($valid_ids));

        if ($is_full_course && $full_course_price > 0 && $all_lessons_selected) {
            $woo_product_id = rcil_get_course_woo_product_id($course_id);
            if ($woo_product_id) {
                if (!is_null(WC()->cart)) {
                    WC()->cart->empty_cart();
                    WC()->cart->add_to_cart($woo_product_id); // Native full course product
                    
                    wp_send_json_success([
                        'redirect_url' => wc_get_checkout_url(),
                        'product_id' => $woo_product_id,
                        'total' => $full_course_price
                    ]);
                } else {
                    wp_send_json_error(__('WooCommerce cart is not available.', 'red-cultural-individual-lesson'));
                }
            } else {
                // We'll fall back to a dynamic product representing the full course.
                $total_price = $full_course_price;
            }
        } else {
            $total_price = count($selected_lesson_ids) * $unit_price;
        }

        // Only create a dynamic individual lesson product if we didn't natively add the full course
        if (!isset($woo_product_id) || !$woo_product_id) {
            $product_id = RCIL_WooCommerce::get_instance()->get_or_create_dynamic_product([
                'course_id' => $course_id,
                'lesson_ids' => $selected_lesson_ids,
                'lesson_titles' => $selected_lesson_titles,
                'total_price' => $total_price,
                'unit_price' => $unit_price,
                'is_full_course' => $is_full_course
            ]);

            if (is_wp_error($product_id)) {
                wp_send_json_error($product_id->get_error_message());
            }

            // Prepare Cart for Individual Lesson Bundle
            if (!is_null(WC()->cart)) {
                WC()->cart->empty_cart();
                WC()->cart->add_to_cart($product_id, 1, 0, [], [
                    '_is_rcil_purchase' => true,
                    '_rcil_course_id' => $course_id,
                    '_rcil_lesson_ids' => $selected_lesson_ids,
                    '_rcil_lesson_titles' => $selected_lesson_titles,
                    '_rcil_per_lesson_price' => $unit_price,
                    '_rcil_is_full_course' => $is_full_course
                ]);

                wp_send_json_success([
                    'redirect_url' => wc_get_checkout_url(),
                    'product_id' => $product_id,
                    'total' => $total_price
                ]);
            } else {
                wp_send_json_error(__('WooCommerce cart is not available.', 'red-cultural-individual-lesson'));
            }
        }
    }

    /**
     * Get list of users with access to a specific lesson.
     */
    public function handle_get_alumni_list()
    {
        check_ajax_referer('rcil_ajax_nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
        $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;

        if (!$course_id || !$lesson_id) {
            wp_send_json_error(__('Invalid parameters.', 'red-cultural-individual-lesson'));
        }

        $user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        $is_author = (get_post_field('post_author', $course_id) == $user_id);

        if (!$is_admin && !$is_author) {
            wp_send_json_error(__('Unauthorized.', 'red-cultural-individual-lesson'));
        }

        global $wpdb;
        $user_ids = [];

        // 1. Native Course Enrollment
        $ld_users = get_users([
            'meta_query' => [
                ['key' => 'course_' . $course_id . '_access', 'compare' => 'EXISTS']
            ],
            'fields' => 'ids'
        ]);
        if (!empty($ld_users)) {
            $user_ids = array_merge($user_ids, $ld_users);
        }

        // 2. RCIL specific individual/partial access
        $rcil_users = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$wpdb->prefix}rcil_lesson_access WHERE course_id = %d AND (lesson_id = %d OR is_full_access = 1) AND status = 'active'",
            $course_id, $lesson_id
        ));
        if (!empty($rcil_users)) {
            $user_ids = array_merge($user_ids, $rcil_users);
        }

        $user_ids = array_unique($user_ids);
        $alumni = [];

        foreach ($user_ids as $uid) {
            if (!rcil_user_has_lesson_access($uid, $lesson_id)) {
                continue;
            }
            $u = get_userdata($uid);
            if ($u) {
                $alumni[] = [
                    'name' => $u->display_name,
                    'email' => $u->user_email
                ];
            }
        }

        usort($alumni, function($a, $b) { return strcasecmp($a['name'], $b['name']); });

        wp_send_json_success(['alumni' => $alumni]);
    }
}
