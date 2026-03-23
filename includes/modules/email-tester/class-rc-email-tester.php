<?php
/**
 * Handles Email Testing in the Red Cultural Dashboard.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Red_Cultural_Email_Tester
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
        add_action('admin_menu', [$this, 'register_admin_pages'], 99); // Higher priority to ensure main menu is already registered
        add_action('admin_post_rc_trigger_email_test', [$this, 'handle_trigger_email_test']);
    }

    /**
     * Register the "Test emails" submenu under the "Red Cultural" main menu.
     */
    public function register_admin_pages()
    {
        // Attempt to find the main menu registered by Templates or Lesson Access.
        // Slack is red-cultural-pages or rcil-main.
        
        $parent_slug = 'red-cultural-pages'; // Default from Templates module.

        add_submenu_page(
            $parent_slug,
            'Test emails',
            'Test emails',
            'manage_options',
            'red-cultural-email-tester',
            [$this, 'render_admin_page']
        );
    }

    /**
     * Render the admin page for email testing.
     */
    public function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para ver esta página.', 'red-cultural-core'));
        }

        $emails = [
            'contacto' => 'Formulario de Contacto (General)',
            'viaje_italia' => 'Interés: Viaje Italia',
            'viaje_japon' => 'Interés: Viaje Japón',
            'viaje_escandinavia' => 'Interés: Viaje Escandinavia',
            'lesson_notif_global' => 'Notificación: Listado de Alumnos (Global 24h)',
            'lesson_notif_specific' => 'Notificación: Listado de Alumnos (Última lección)',
            'wc_new_order' => 'WooCommerce: Nuevo Pedido (Última orden)',
        ];

        $updated = (isset($_GET['rc_email_sent']) && $_GET['rc_email_sent'] === '1');
        $error = isset($_GET['rc_error']) ? sanitize_text_field($_GET['rc_error']) : '';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Test emails', 'red-cultural-core'); ?></h1>

            <?php if ($updated) : ?>
                <div class="notice notice-success is-dismissible"><p><?php echo esc_html__('Email de prueba enviado correctamente.', 'red-cultural-core'); ?></p></div>
            <?php endif; ?>

            <?php if ($error) : ?>
                <div class="notice notice-error is-dismissible"><p><?php echo esc_html($error); ?></p></div>
            <?php endif; ?>

            <p><?php echo esc_html__('Desde aquí puedes disparar los correos que genera el plugin para verificar su diseño y funcionamiento.', 'red-cultural-core'); ?></p>

            <div class="card" style="max-width: 600px; padding: 20px; border-radius: 4px; margin-top: 20px;">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="rc_trigger_email_test" />
                    <?php wp_nonce_field('rc_trigger_email_test_nonce', 'rc_email_nonce'); ?>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="email_type"><?php echo esc_html__('Selecciona un Email', 'red-cultural-core'); ?></label></th>
                            <td>
                                <select name="email_type" id="email_type" class="postform" style="width: 100%;">
                                    <option value=""><?php echo esc_html__('-- Seleccionar --', 'red-cultural-core'); ?></option>
                                    <?php foreach ($emails as $value => $label) : ?>
                                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="test_recipient"><?php echo esc_html__('Enviar a', 'red-cultural-core'); ?></label></th>
                            <td>
                                <input name="test_recipient" type="email" id="test_recipient" value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text" style="width: 100%;" required>
                                <p class="description"><?php echo esc_html__('El correo de prueba se enviará a esta dirección.', 'red-cultural-core'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary"><?php echo esc_html__('Enviar Email de Prueba', 'red-cultural-core'); ?></button>
                    </p>
                </form>
            </div>
            
            <div style="margin-top: 20px; color: #666;">
                <p><strong>Nota:</strong> Para los correos de WooCommerce y de Lecciones, se utilizarán los datos de la última orden o lección registrada para simular los tags y el contenido.</p>
            </div>
        </div>
        <?php
    }

    /**
     * Handle the admin post request to trigger a test email.
     */
    public function handle_trigger_email_test()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para realizar esta acción.', 'red-cultural-core'));
        }

        if (!isset($_POST['rc_email_nonce']) || !wp_verify_nonce($_POST['rc_email_nonce'], 'rc_trigger_email_test_nonce')) {
            wp_die(esc_html__('Nonce inválido.', 'red-cultural-core'));
        }

        $email_type = isset($_POST['email_type']) ? sanitize_text_field($_POST['email_type']) : '';
        $to = isset($_POST['test_recipient']) ? sanitize_email($_POST['test_recipient']) : '';

        if (empty($email_type)) {
            wp_safe_redirect(add_query_arg(['rc_error' => 'Debes seleccionar un tipo de email'], admin_url('admin.php?page=red-cultural-email-tester')));
            exit;
        }

        if (!$to) {
            wp_safe_redirect(add_query_arg(['rc_error' => 'Email de destino no válido'], admin_url('admin.php?page=red-cultural-email-tester')));
            exit;
        }

        $sent = false;
        $error_msg = '';

        try {
            switch ($email_type) {
                case 'contacto':
                    $sent = $this->send_test_contacto($to);
                    break;
                case 'viaje_italia':
                    $sent = $this->send_test_interest_form('Italia', $to);
                    break;
                case 'viaje_japon':
                    $sent = $this->send_test_interest_form('Japón', $to);
                    break;
                case 'viaje_escandinavia':
                    $sent = $this->send_test_interest_form('Escandinavia 2026', $to);
                    break;
                case 'lesson_notif_global':
                    $sent = $this->send_test_lesson_notif_global($to);
                    break;
                case 'lesson_notif_specific':
                    $sent = $this->send_test_lesson_notif_specific($to);
                    break;
                case 'wc_new_order':
                    $sent = $this->send_test_wc_new_order($to);
                    break;
                default:
                    $error_msg = 'Tipo de email desconocido';
                    break;
            }
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
        }

        if ($sent) {
            wp_safe_redirect(add_query_arg('rc_email_sent', '1', admin_url('admin.php?page=red-cultural-email-tester')));
        } else {
            $error_msg = $error_msg ?: 'Error al enviar el email.';
            wp_safe_redirect(add_query_arg('rc_error', $error_msg, admin_url('admin.php?page=red-cultural-email-tester')));
        }
        exit;
    }

    private function send_test_contacto($to)
    {
        $subject = 'TEST: Contacto — Mensaje de prueba';
        $body = "Nombre: Usuario de Prueba\nEmail: tester@ejemplo.cl\nCelular: +56912345678\nAsunto: Consulta General de Prueba\n\nMensaje:\nEste es un mensaje de prueba disparado desde el administrador de Red Cultural. Si estás leyendo esto, el sistema de mensajería básico funciona correctamente.\n";
        $headers = array('Reply-To: tester@ejemplo.cl');
        return wp_mail($to, $subject, $body, $headers);
    }

    private function send_test_interest_form($viaje, $to)
    {
        $subject = "TEST: Viaje {$viaje} — Nuevo interés";
        $body = "Viaje: {$viaje}\n\nNombre: Usuario de Prueba\nEmail: tester@ejemplo.cl\nTeléfono: +56912345678\n\nMensaje:\nInterés generado de prueba para verificar el formato del correo de viajes.\n";
        $headers = array('Reply-To: tester@ejemplo.cl');
        return wp_mail($to, $subject, $body, $headers);
    }

    private function send_test_lesson_notif_global($to)
    {
        $email_content = "<h2>TEST: Upcoming Lessons Notification</h2>";
        $email_content .= "<p>Esta es una lista simulada de las lecciones que se liberarán en las próximas 24 horas.</p>";
        $email_content .= "<h3>Lección de Prueba (Curso: Literatura Universal)</h3>";
        $email_content .= "<p><strong>Fecha de liberación:</strong> " . date_i18n(get_option('date_format') . ' ' . get_option('time_format')) . "</p>";
        $email_content .= "<ul><li>Usuario Tester (tester@ejemplo.cl)</li></ul><hr>";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($to, 'TEST: Alumni list for upcoming lessons', $email_content, $headers);
    }

    private function send_test_lesson_notif_specific($to)
    {
        $lessons = get_posts(['post_type' => 'sfwd-lessons', 'posts_per_page' => 1]);
        $lesson_title = !empty($lessons) ? $lessons[0]->post_title : 'Lección de Prueba';
        $course_title = 'Curso Literario';

        if (!empty($lessons) && function_exists('learndash_get_course_id')) {
            $course_id = learndash_get_course_id($lessons[0]->ID);
            if ($course_id) {
                $course_title = get_the_title($course_id);
            }
        }
        
        $email_content = "<h2>TEST: Course Lesson Alumni List</h2>";
        $email_content .= "<h3>{$lesson_title} (Curso: {$course_title})</h3>";
        $email_content .= "<p><strong>Fecha de liberación:</strong> " . date_i18n(get_option('date_format') . ' ' . get_option('time_format')) . "</p>";
        $email_content .= "<p>Los siguientes alumnos han comprado esta lección:</p>";
        $email_content .= "<ul><li>Tester User - tester@ejemplo.cl</li></ul><hr>";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($to, "TEST: Alumni list: {$lesson_title}", $email_content, $headers);
    }

    private function send_test_wc_new_order($to)
    {
        if (!class_exists('WooCommerce')) {
            throw new Exception('WooCommerce no está activo.');
        }

        $orders = wc_get_orders(['limit' => 1, 'orderby' => 'date', 'order' => 'DESC']);
        if (empty($orders)) {
            throw new Exception('No hay órdenes en la tienda para simular el email.');
        }
        $order = $orders[0];

        // Override recipient for just this call
        add_filter('woocommerce_email_recipient_new_order', function($recipient, $order_id) use ($to) {
            return $to;
        }, 999, 2);

        $mailer = WC()->mailer();
        $email = isset($mailer->emails['WC_Email_New_Order']) ? $mailer->emails['WC_Email_New_Order'] : null;
        
        if (!$email) {
            throw new Exception('No se encontró la clase de email WC_Email_New_Order.');
        }

        $sent = $email->trigger($order->get_id());
        
        // Remove filter
        remove_all_filters('woocommerce_email_recipient_new_order', 999);
        
        return $sent;
    }
}
