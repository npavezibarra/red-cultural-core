<?php
/**
 * Manager for Email Log.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class RC_Email_Log_Manager
{
    private static $instance = null;
    private $capture_data = null;
    private static $last_template_file = '';
    private static $context_order_id = 0;

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
        // Capture email data
        add_filter('wp_mail', [$this, 'capture_wp_mail_args'], 9999);
        add_action('wp_mail_failed', [$this, 'log_failed_email'], 10, 1);

        // Capture WooCommerce order context (so admin can open the exact Thank You page).
        add_action('woocommerce_email_before_order_table', [$this, 'capture_wc_order_context'], 1, 4);
        add_action('woocommerce_email_after_order_table', [$this, 'clear_wc_order_context'], 9999, 4);
        
        // We use a custom hook or just the filter success
        // Since wp_mail_succeeded is only since WP 5.9, we check existence
        if (has_action('wp_mail_succeeded')) {
            add_action('wp_mail_succeeded', [$this, 'log_successful_email'], 10, 1);
        } else {
            // Fallback for older WP if needed, but we assume modern WP
            add_action('phpmailer_init', [$this, 'fallback_capture_phpmailer'], 10, 1);
        }

        // Initialize Admin
        require_once RC_CORE_PATH . 'includes/modules/email-log/class-rc-email-log-admin.php';
        RC_Email_Log_Admin::get_instance();

        // Ensure DB table is up to date on all requests (orders/emails can happen on the frontend).
        add_action('init', [$this, 'maybe_create_table']);
    }

    /**
     * Ensure table exists.
     */
    public function maybe_create_table()
    {
        if (get_option('rc_email_log_db_version_v3') !== '1.0.3') {
            RC_Email_Log_DB::get_instance()->create_table();
            update_option('rc_email_log_db_version_v3', '1.0.3');
        }
    }

    /**
     * Store the last template file processed (for custom plugin emails).
     */
    public static function set_last_template_file($file)
    {
        self::$last_template_file = $file;
    }

    public static function set_context_order_id($order_id)
    {
        self::$context_order_id = absint($order_id);
    }

    public function capture_wc_order_context($order, $sent_to_admin, $plain_text, $email)
    {
        if ($order instanceof \WC_Order) {
            self::$context_order_id = (int) $order->get_id();
        }
    }

    public function clear_wc_order_context($order, $sent_to_admin, $plain_text, $email)
    {
        self::$context_order_id = 0;
    }

    /**
     * Capture arguments from wp_mail filter.
     */
    public function capture_wp_mail_args($args)
    {
        $this->capture_data = $args;
        
        // Find the caller file
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $caller_file = '';
        
        foreach ($trace as $step) {
            if (!isset($step['file'])) {
                continue;
            }

            $file = wp_normalize_path($step['file']);
            
            // Skip WordPress core
            if (strpos($file, '/wp-includes/') !== false || strpos($file, '/wp-admin/') !== false) {
                continue;
            }
            
            // Skip the Email Log module itself
            if (strpos($file, 'class-rc-email-log-manager.php') !== false || strpos($file, 'class-rc-email-log-db.php') !== false) {
                continue;
            }

            // We found a likely candidate (Plugin or Theme)
            $caller_file = $file;
            break;
        }
        
        $this->capture_data['caller_file'] = self::$last_template_file ?: ($caller_file ?: 'unknown');

        if (self::$context_order_id > 0) {
            $header_line = 'X-RC-Order-ID: ' . self::$context_order_id;
            if (empty($this->capture_data['headers'])) {
                $this->capture_data['headers'] = [$header_line];
            } elseif (is_array($this->capture_data['headers'])) {
                $this->capture_data['headers'][] = $header_line;
            } else {
                $this->capture_data['headers'] .= "\n" . $header_line;
            }

            // Only apply this context to the next mail send.
            self::$context_order_id = 0;
        }
        
        // Reset after capture to not pollute the next generic email
        self::$last_template_file = '';

        return $args;
    }

    /**
     * Log successful email.
     */
    public function log_successful_email($mail_data)
    {
        $this->save_log('success');
    }

    /**
     * Log failed email.
     */
    public function log_failed_email($wp_error)
    {
        $this->save_log('failed');
    }

    /**
     * Fallback for older WP versions or when succeeded hook isn't fired.
     */
    public function fallback_capture_phpmailer($phpmailer)
    {
        // Save immediately as phpmailer_init is called right before sending
        $this->save_log('sent');
    }

    /**
     * Detect email type and save to DB.
     */
    private function save_log($status = 'sent')
    {
        if (!$this->capture_data) {
            return;
        }

        $args = $this->capture_data;
        $this->capture_data = null; // Clear to avoid double logging

        $to = is_array($args['to']) ? implode(', ', $args['to']) : $args['to'];
        $subject = $args['subject'];
        $message = $args['message'];
        $headers = is_array($args['headers']) ? implode("\n", $args['headers']) : $args['headers'];
        $order_id = 0;
        if (is_string($headers) && preg_match('/x-rc-order-id:\\s*(\\d+)/i', $headers, $m)) {
            $order_id = absint($m[1]);
        }

        $type_info = $this->identify_email_info($subject, $headers, $message);
        $caller_file = $args['caller_file'] ?? 'unknown';

        // Simplify file path for display
        $display_file = basename($caller_file);
        if (strpos($caller_file, 'red-cultural-core') !== false) {
            $parts = explode('red-cultural-core/', $caller_file);
            if (count($parts) > 1) {
                $display_file = '.../' . $parts[1];
            }
        }

        RC_Email_Log_DB::get_instance()->insert_log([
            'order_id'   => $order_id,
            'recipient'  => $to,
            'subject'    => $subject,
            'content'    => $message,
            'headers'    => $headers,
            'email_type' => $type_info['type'],
            'template'   => $type_info['template'],
            'file_path'  => $display_file,
            'sent_at'    => current_time('mysql'),
        ]);
    }

    /**
     * Logic to identify email type and template.
     */
    private function identify_email_info($subject, $headers, $message)
    {
        $subject_low = strtolower($subject);
        $headers_low = strtolower($headers);
        
        $type = 'General';
        $template = 'Genérico';

        // 1. Detect WooCommerce Specifics if present
        if (strpos($headers_low, 'x-wc-email') !== false) {
            $type = 'WooCommerce';
            
            // Extract the template ID from headers
            // Headers format: X-WC-Email: customer_new_order
            if (preg_match('/x-wc-email:\s*([a-z0-9_]+)/i', $headers, $matches)) {
                $template_id = $matches[1];
                $template = $this->map_wc_template($template_id);
            }
        } elseif (strpos($headers_low, 'woocommerce') !== false || strpos($subject_low, 'pedido') !== false || strpos($subject_low, 'orden') !== false) {
            $type = 'WooCommerce';
            $template = 'Pedido (General)';
        }

        // 2. Contact Forms
        if ($type === 'General') {
            if (strpos($subject_low, 'interés: viaje') !== false || strpos($subject_low, 'viaje') !== false) {
                $type = 'Viajes';
                if (preg_match('/viaje\s+(.+)\s+—/i', $subject, $m)) {
                    $template = "Interés: " . trim($m[1]);
                } else {
                    $template = 'Interés: Viaje';
                }
            } elseif (strpos($subject_low, 'contacto') !== false) {
                $type = 'Contacto';
                $template = 'Consulta General';
            }
        }

        // 3. User & Auth
        if ($type === 'General') {
            if (strpos($subject_low, 'bienvenida') !== false || strpos($subject_low, 'registro') !== false) {
                $type = 'Registro';
                $template = 'Bienvenida (Confirmación)';
            } elseif (strpos($subject_low, 'restablecer') !== false || (strpos($subject_low, 'contraseña') !== false && strpos($subject_low, 'reset') !== false)) {
                $type = 'Cuenta';
                $template = 'Restablecer Contraseña';
            }
        }

        // 4. Notifications
        if ($type === 'General' && (strpos($subject_low, 'notificación') !== false || strpos($subject_low, 'lecciones') !== false)) {
            $type = 'Lecciones';
            $template = 'Notificación Alumnos';
        }

        return ['type' => $type, 'template' => $template];
    }

    /**
     * Map WooCommerce template IDs to friendly names.
     */
    private function map_wc_template($id)
    {
        $map = [
            'new_order'                        => 'Nueva Orden (Admin)',
            'customer_processing_order'        => 'Pedido Recibido (Cliente)',
            'customer_completed_order'         => 'Pedido Completado',
            'customer_on_hold_order'           => 'Pedido en Espera',
            'cancelled_order'                  => 'Pedido Cancelado',
            'failed_order'                     => 'Pedido Fallido',
            'customer_invoice'                 => 'Factura Cliente',
            'customer_note'                    => 'Nota del Pedido',
            'customer_reset_password'          => 'Restablecer Contraseña',
            'customer_new_account'             => 'Bienvenida Cliente',
            'customer_refunded_order'          => 'Pedido Reembolsado',
            'customer_partially_refunded_order' => 'Pedido Rerembolsado Parcial',
        ];

        return $map[$id] ?? ucwords(str_replace('_', ' ', $id));
    }
}
