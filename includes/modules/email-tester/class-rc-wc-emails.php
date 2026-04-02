<?php
/**
 * Handles Custom WooCommerce Emails for Red Cultural.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Red_Cultural_WC_Emails
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function init()
    {
        return self::get_instance();
    }

    private function __construct()
    {
        // Hook into order status changes for Bank Transfer (bacs)
        add_action('woocommerce_order_status_on-hold', [$this, 'maybe_trigger_bank_transfer_notif'], 10, 2);

        // Hook into successful payments/completions to send access links
        add_action('woocommerce_order_status_processing', [$this, 'maybe_trigger_access_email'], 10, 2);
        add_action('woocommerce_order_status_completed', [$this, 'maybe_trigger_access_email'], 10, 2);

        // New hook for confirming transfers via email button
        add_action('init', [$this, 'handle_confirm_transfer']);

        // Disable ALL default WooCommerce emails that we are replacing (highest priority)
        $wc_emails = [
            'new_order',
            'customer_on_hold_order',
            'customer_processing_order',
            'customer_completed_order',
            'customer_refunded_order',
            'customer_invoice',
            'customer_note',
            'customer_reset_password',
            'customer_new_account'
        ];

        foreach ($wc_emails as $id) {
            add_filter("woocommerce_email_recipient_{$id}", '__return_empty_string', 9999);
            add_filter("woocommerce_email_enabled_{$id}", '__return_false', 9999);
        }

        // Hook for NEW Admin Notification
        add_action('woocommerce_new_order', [$this, 'send_admin_new_order_notification'], 20, 1);
        add_action('woocommerce_checkout_order_processed', [$this, 'send_admin_new_order_notification'], 20, 1);
    }

    /**
     * Remove default WC emails from the registration list to prevent them from firing.
     */
    public function remove_default_wc_emails($email_classes)
    {
        // IDs we want to remove completely because we handle them manually
        $to_remove = [
            'WC_Email_New_Order',
            'WC_Email_Customer_On_Hold_Order',
            'WC_Email_Customer_Processing_Order'
        ];

        foreach ($to_remove as $class_name) {
            if (isset($email_classes[$class_name])) {
                unset($email_classes[$class_name]);
            }
        }

        return $email_classes;
    }

    /**
     * Helper to disable default enabled flag only for digital products (Courses/Lessons)
     */
    public function disable_default_enabled_for_digital($enabled, $order)
    {
        if (!$order) return $enabled;
        $type = self::identify_order_type($order);
        if ($type !== 'physical') {
            return false; // Disable default for Courses/Lessons
        }
        return $enabled;
    }

    /**
     * Helper to disable default recipient only for digital products (Courses/Lessons)
     */
    public function disable_default_recipient_for_digital($recipient, $order)
    {
        if (!$order) return $recipient;
        $type = self::identify_order_type($order);
        if ($type !== 'physical') {
            return ''; // Stop default email
        }
        return $recipient;
    }

    /**
     * Handle the "Confirm Transfer" button from accountant email.
     */
    public function handle_confirm_transfer()
    {
        if (!isset($_GET['rc_confirm_transfer']) || !isset($_GET['rc_token'])) {
            return;
        }

        $order_id = absint($_GET['rc_confirm_transfer']);
        $token = sanitize_text_field($_GET['rc_token']);

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die('Pedido no encontrado.');
        }

        // Verify token
        $expected_token = wp_hash($order_id . '|' . $order->get_order_key() . '|rc_confirm_transfer');
        if (!hash_equals($expected_token, $token)) {
            wp_die('Token de seguridad inválido o expirado.');
        }

        // Check if order is already processed
        if ($order->has_status(['completed'])) {
            wp_die('Este pedido ya ha sido confirmado anteriormente.', 'Pedido Ya Confirmado', ['response' => 200, 'back_link' => true]);
        }

        // Update status to COMPLETED
        $order->update_status('completed', __('Transferencia confirmada por contabilidad vía email.', 'red-cultural-core'));

        // Redirect with a success message
        wp_die(
            sprintf('Transferencia confirmada correctamente para el pedido #%s. El pedido ahora está COMPLETADO y el usuario ya tiene acceso a sus contenidos.', $order->get_order_number()),
            'Transferencia Confirmada',
            ['response' => 200, 'back_link' => true]
        );
    }

    /**
     * Trigger access email (New Order Custom) if it hasn't been sent yet.
     */
    public function maybe_trigger_access_email($order_id, $order)
    {
        // Prevent double sending if status changes from processing to completed quickly
        if ($order->get_meta('_rc_access_email_sent') === '1') {
            return;
        }

        $sent = $this->send_custom_new_order($order_id);
        if ($sent) {
            $order->update_meta_data('_rc_access_email_sent', '1');
            $order->save();
        }
    }

    /**
     * Send the custom Bank Transfer "On Hold" notification.
     */
    public function send_bank_transfer_notification($order_id, $forced_to = '', $extra_args = [])
    {
        $order = wc_get_order($order_id);
        if (!$order) return false;

        $to = $forced_to ?: $order->get_billing_email();
        $accountant_email = get_option('rc_accountant_email', '');

        $subject = 'Recibimos tu pedido - Pendiente de transferencia';
        $template_args = array_merge(['order' => $order], $extra_args);
        $content = $this->get_template_content('emails/wc-bank-transfer-on-hold.php', $template_args);

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        // Send to customer
        $sent_customer = wp_mail($to, $subject, $content, $headers);

        // Send to accountant if configured
        if ($accountant_email) {
            $accountant_subject = 'Nueva orden por Transferencia - #' . $order->get_order_number();
            
            // Generate confirmation URL
            $token = wp_hash($order_id . '|' . $order->get_order_key() . '|rc_confirm_transfer');
            $confirm_url = add_query_arg([
                'rc_confirm_transfer' => $order_id,
                'rc_token' => $token
            ], home_url('/'));

            $acc_template_args = array_merge(['order' => $order, 'confirm_url' => $confirm_url], $extra_args);
            $accountant_content = $this->get_template_content('emails/wc-bank-transfer-accountant.php', $acc_template_args);
            
            wp_mail($accountant_email, $accountant_subject, $accountant_content, $headers);
        }

        return $sent_customer;
    }

    /**
     * Trigger bank transfer notification if payment method is BACS.
     */
    public function maybe_trigger_bank_transfer_notif($order_id, $order)
    {
        if ($order->get_payment_method() === 'bacs') {
            $this->send_bank_transfer_notification($order_id);
        }
    }

    /**
     * Send Custom Admin New Order notification.
     */
    public function send_admin_new_order_notification($order_id)
    {
        if (!$order_id) return;
        $order = wc_get_order($order_id);
        if (!$order) return;

        // Prevent double sending
        if ($order->get_meta('_rc_admin_notif_sent') === '1') return;

        $recipients = get_option('admin_email');
        if (class_exists('RC_Sale_Notifications')) {
            $extra = RC_Sale_Notifications::get_notification_emails();
            if (!empty($extra)) {
                $recipients .= ',' . implode(',', $extra);
            }
        }

        $subject = 'Nueva Venta - #' . $order->get_order_number() . ' — ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $content = $this->get_template_content('emails/wc-admin-new-order.php', ['order' => $order]);
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        $sent = wp_mail($recipients, $subject, $content, $headers);
        if ($sent) {
            $order->update_meta_data('_rc_admin_notif_sent', '1');
            $order->save();
        }
    }

    /**
     * Send the custom Customer Processing (Confirmation) email.
     */
    public function send_custom_new_order($order_id, $forced_to = '', $extra_args = [])
    {
        $order = wc_get_order($order_id);
        if (!$order) return false;

        $to = $forced_to ?: $order->get_billing_email();
        $subject = '¡Confirmado! Ya puedes acceder a tus contenidos';
        
        $template_args = array_merge(['order' => $order], $extra_args);
        $content = $this->get_template_content('emails/wc-customer-processing.php', $template_args);

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $sent = wp_mail($to, $subject, $content, $headers);

        return $sent;
    }

    /**
     * Helper to get template content with data.
     */
    public function get_template_content($template_name, $args = [])
    {
        extract($args);
        ob_start();
        $template_path = RC_CORE_PATH . 'templates/' . $template_name;
        
        // Notify Email Log Manager of the template being used
        if (class_exists('RC_Email_Log_Manager')) {
            RC_Email_Log_Manager::set_last_template_file($template_path);
        }

        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo "Template not found: " . esc_html($template_name);
        }
        return ob_get_clean();
    }

    /**
     * Identify the type of order to determine which email design to show.
     */
    public static function identify_order_type($order)
    {
        $has_physical = false;
        $has_lessons = false;
        $has_course = false;

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product && !$product->is_virtual()) {
                $has_physical = true;
            }

            if ($item->get_meta('_is_rcil_purchase')) {
                if ($item->get_meta('_rcil_is_full_course') === '1') {
                    $has_course = true;
                } else {
                    $has_lessons = true;
                }
            }
        }

        $type = 'physical'; // Default fallback
        if ($has_course) $type = 'course';
        elseif ($has_lessons) $type = 'lessons';
        elseif ($has_physical) $type = 'physical';

        return apply_filters('red_cultural_identify_order_type', $type, $order);
    }

    /**
     * Helper logic to find Course/Lesson access links for an order.
     */
    public static function get_access_links($order)
    {
        $links = [];
        foreach ($order->get_items() as $item) {
            $product_id = (int) $item->get_product_id();
            $course_id = 0;
            $is_rcil = ($item->get_meta('_is_rcil_purchase') === '1');
            
            // 1. Check RCIL (Individual Lessons)
            $rcil_id = $item->get_meta('_rcil_course_id');
            if ($rcil_id) {
                $course_id = (int) $rcil_id;
            } else {
                // 2. Standard LearnDash Metadata
                $meta_id = get_post_meta($product_id, '_course_id', true);
                if (!$meta_id) $meta_id = get_post_meta($product_id, '_related_course', true);
                if ($meta_id) {
                    $course_id = (int) $meta_id;
                }
            }

            // 3. Fallback: Check category "Cursos" if no ID but is a course product
            if (!$course_id && has_term(['Cursos', 'Curso'], 'product_cat', $product_id)) {
                // Try to find course by slug
                $product = $item->get_product();
                if ($product) {
                    $course_post = get_page_by_path($product->get_slug(), OBJECT, 'sfwd-courses');
                    if ($course_post) $course_id = $course_post->ID;
                }
            }

            if ($course_id > 0) {
                $course_url = get_permalink($course_id);
                $course_title = get_the_title($course_id);
                $lesson_ids = maybe_unserialize($item->get_meta('_rcil_lesson_ids'));
                
                if ($is_rcil && !empty($lesson_ids) && is_array($lesson_ids)) {
                    $first_lesson_id = reset($lesson_ids);
                    $links[] = [
                        'url' => get_permalink($first_lesson_id),
                        'label' => 'Ir a la Lección',
                        'context' => $course_title
                    ];
                } else {
                    $links[] = [
                        'url' => $course_url,
                        'label' => 'Ir al Curso',
                        'context' => $course_title
                    ];
                }
            }
        }

        // Deduplicate links by URL
        $unique_links = [];
        $seen_urls = [];
        foreach ($links as $link) {
            if (!in_array($link['url'], $seen_urls)) {
                $seen_urls[] = $link['url'];
                $unique_links[] = $link;
            }
        }

        return $unique_links;
    }
}
