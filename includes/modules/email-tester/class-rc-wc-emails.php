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
        if ($order->has_status(['processing', 'completed'])) {
            wp_die('Este pedido ya ha sido confirmado anteriormente.', 'Pedido Ya Confirmado', ['response' => 200, 'back_link' => true]);
        }

        // Update status
        $order->update_status('processing', __('Transferencia confirmada por contabilidad vía email.', 'red-cultural-core'));

        // Redirect with a success message
        wp_die(
            sprintf('Transferencia confirmada correctamente para el pedido #%s. El usuario ya tiene acceso a sus contenidos.', $order->get_order_number()),
            'Transferencia Confirmada',
            ['response' => 200, 'back_link' => true]
        );
    }

    /**
     * Trigger access email (New Order Custom) if it hasn't been sent yet.
     */
    public function maybe_trigger_access_email($order_id, $order)
    {
        // Prevent sending for pure physical products (unless required)
        $type = self::identify_order_type($order);
        if ($type === 'physical' || $type === 'book') {
            return;
        }

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
     * Trigger bank transfer notification if payment method is BACS.
     */
    public function maybe_trigger_bank_transfer_notif($order_id, $order)
    {
        if ($order->get_payment_method() === 'bacs') {
            $this->send_bank_transfer_notification($order_id);
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

        // Send copy to extra sale-notification recipients (only for real orders)
        if (empty($forced_to) && class_exists('RC_Sale_Notifications')) {
            $extra_recipients = RC_Sale_Notifications::get_notification_emails();
            if (!empty($extra_recipients)) {
                $notif_subject = 'Nueva orden por Transferencia - #' . $order->get_order_number() . ' — ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                foreach ($extra_recipients as $notif_email) {
                    wp_mail($notif_email, $notif_subject, $content, $headers);
                }
            }
        }

        return $sent_customer;
    }

    /**
     * Send the custom New Order email.
     */
    public function send_custom_new_order($order_id, $forced_to = '', $extra_args = [])
    {
        $order = wc_get_order($order_id);
        if (!$order) return false;

        $to = $forced_to ?: $order->get_billing_email();
        $subject = 'Confirmación de tu pedido en Red Cultural';
        
        $template_args = array_merge(['order' => $order], $extra_args);
        $content = $this->get_template_content('emails/wc-new-order-custom.php', $template_args);

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $sent = wp_mail($to, $subject, $content, $headers);

        // Send copy to extra sale-notification recipients (only for real orders, not test sends)
        if (empty($forced_to) && class_exists('RC_Sale_Notifications')) {
            $extra_recipients = RC_Sale_Notifications::get_notification_emails();
            if (!empty($extra_recipients)) {
                $notif_subject = 'Nueva Venta - #' . $order->get_order_number() . ' — ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                foreach ($extra_recipients as $notif_email) {
                    wp_mail($notif_email, $notif_subject, $content, $headers);
                }
            }
        }

        return $sent;
    }

    /**
     * Helper to get template content with data.
     */
    private function get_template_content($template_name, $args = [])
    {
        extract($args);
        ob_start();
        $template_path = RC_CORE_PATH . 'templates/' . $template_name;
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
            if ($item->get_meta('_is_rcil_purchase')) {
                $course_id = (int) $item->get_meta('_rcil_course_id');
                $lesson_ids = maybe_unserialize($item->get_meta('_rcil_lesson_ids'));
                
                if ($course_id) {
                    $course_url = get_permalink($course_id);
                    $course_title = get_the_title($course_id);
                    
                    if (count($lesson_ids) === 1) {
                        $lesson_id = $lesson_ids[0];
                        $links[] = [
                            'url' => get_permalink($lesson_id),
                            'label' => 'Ir a la Lección: ' . get_the_title($lesson_id),
                            'context' => $course_title
                        ];
                    } else {
                        $links[] = [
                            'url' => $course_url,
                            'label' => 'Ir al Curso: ' . $course_title,
                            'context' => $course_title
                        ];
                    }
                }
            }
        }
        return $links;
    }
}
