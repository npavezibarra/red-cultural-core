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
            'Probar correos',
            'Probar correos',
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
            'register' => 'Registro: Bienvenida (Email de confirmación)',
            'contacto' => 'Formulario de Contacto (General)',
            'viaje_italia' => 'Interés: Viaje Italia',
            'viaje_japon' => 'Interés: Viaje Japón',
            'viaje_escandinavia' => 'Interés: Viaje Escandinavia',
            'lesson_notif_global' => 'Notificación: Listado de Alumnos (Global 24h)',
            'lesson_notif_specific' => 'Notificación: Listado de Alumnos (Última lección)',
            'wc_new_order' => 'WooCommerce: Nuevo Pedido (Auto-detectar)',
            'wc_new_order_course' => 'WooCommerce: Nuevo Pedido Curso (Botón Negro)',
            'wc_new_order_lessons' => 'WooCommerce: Nuevo Pedido Lecciones (Individuales)',
            'wc_new_order_book' => 'WooCommerce: Nuevo Pedido Libro (Físico)',
            'wc_bank_transfer' => 'WooCommerce: Transferencia Bancaria (On Hold)',
        ];

        $accountant_email = get_option('rc_accountant_email', '');

        $updated = (isset($_GET['rc_email_sent']) && $_GET['rc_email_sent'] === '1');
        $error = isset($_GET['rc_error']) ? sanitize_text_field($_GET['rc_error']) : '';

        // Fetch courses and lessons for the tester
        $courses = get_posts([
            'post_type' => 'sfwd-courses',
            'posts_per_page' => 20,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        $lessons = get_posts([
            'post_type' => 'sfwd-lessons',
            'posts_per_page' => 20,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Probar correos', 'red-cultural-core'); ?></h1>

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
                                <select name="email_type" id="email_type" class="postform" style="width: 100%;" onchange="toggleResourceSelector(this.value)">
                                    <option value=""><?php echo esc_html__('-- Seleccionar --', 'red-cultural-core'); ?></option>
                                    <?php foreach ($emails as $value => $label) : ?>
                                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr id="resource_selector_row" style="display: none;">
                            <th scope="row"><label for="resource_id"><?php echo esc_html__('Seleccionar Curso/Lección', 'red-cultural-core'); ?></label></th>
                            <td>
                                <select name="resource_id" id="resource_id" class="postform" style="width: 100%;">
                                    <option value=""><?php echo esc_html__('-- Usar datos automáticos --', 'red-cultural-core'); ?></option>
                                    
                                    <optgroup label="Cursos (Full Course)">
                                        <?php foreach ($courses as $c) : ?>
                                            <option value="<?php echo esc_attr($c->ID); ?>"><?php echo esc_html($c->post_title); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>

                                    <optgroup label="Lecciones (Individuales)">
                                        <?php foreach ($lessons as $l) : ?>
                                            <?php 
                                            $course_title = 'Sin curso';
                                            if (function_exists('learndash_get_course_id')) {
                                                $cid = learndash_get_course_id($l->ID);
                                                if ($cid) $course_title = get_the_title($cid);
                                            }
                                            ?>
                                            <option value="<?php echo esc_attr($l->ID); ?>"><?php echo esc_html($l->post_title . ' — [' . $course_title . ']'); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                                <p class="description"><?php echo esc_html__('El diseño del correo se forzará para mostrar este recurso.', 'red-cultural-core'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label><?php echo esc_html__('Origen de los datos', 'red-cultural-core'); ?></label></th>
                            <td>
                                <label><input type="radio" name="data_source" value="fake" checked> <?php echo esc_html__('Datos de Prueba (Fake)', 'red-cultural-core'); ?></label><br>
                                <label><input type="radio" name="data_source" value="real"> <?php echo esc_html__('Datos Reales (Última orden/usuario)', 'red-cultural-core'); ?></label>
                                <p class="description"><?php echo esc_html__('Usa datos reales para verificar cómo se ven los pedidos actuales.', 'red-cultural-core'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="test_recipient"><?php echo esc_html__('Enviar a', 'red-cultural-core'); ?></label></th>
                            <td>
                                <input name="test_recipient" type="email" id="test_recipient" value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text" style="width: 100%;" required>
                                <p class="description"><?php echo esc_html__('El correo de prueba se enviará a esta dirección.', 'red-cultural-core'); ?></p>
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid #ddd;">
                            <th scope="row"><label for="accountant_email"><?php echo esc_html__('Email Contabilidad', 'red-cultural-core'); ?></label></th>
                            <td>
                                <input name="accountant_email" type="email" id="accountant_email" value="<?php echo esc_attr($accountant_email); ?>" class="regular-text" style="width: 100%;">
                                <p class="description"><?php echo esc_html__('Email donde se enviarán las notificaciones de Transferencia Bancaria.', 'red-cultural-core'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary"><?php echo esc_html__('Enviar Email de Prueba', 'red-cultural-core'); ?></button>
                    </p>
                </form>
            </div>
            
        </div>

        <script>
        function toggleResourceSelector(emailType) {
            const row = document.getElementById('resource_selector_row');
            if (emailType.startsWith('wc_new_order') || emailType === 'wc_bank_transfer') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
        </script>
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
        $data_source = isset($_POST['data_source']) ? sanitize_text_field($_POST['data_source']) : 'fake';
        $resource_id = isset($_POST['resource_id']) ? absint($_POST['resource_id']) : 0;
        $accountant_email_post = isset($_POST['accountant_email']) ? sanitize_email($_POST['accountant_email']) : '';

        // Update accountant email option
        if ($accountant_email_post) {
            update_option('rc_accountant_email', $accountant_email_post);
        }

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
                case 'register':
                    $sent = $this->send_test_register($to, $data_source);
                    break;
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
                    $sent = $this->send_test_wc_new_order($to, $data_source);
                    break;
                case 'wc_new_order_course':
                    $sent = $this->send_test_wc_new_order($to, $data_source, 'course', $resource_id);
                    break;
                case 'wc_new_order_lessons':
                    $sent = $this->send_test_wc_new_order($to, $data_source, 'lessons', $resource_id);
                    break;
                case 'wc_new_order_book':
                    $sent = $this->send_test_wc_new_order($to, $data_source, 'book');
                    break;
                case 'wc_bank_transfer':
                    $sent = $this->send_test_wc_bank_transfer($to, $data_source, $resource_id);
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
        $email_content = "<h2>TEST: " . __('Notificación de Próximas Lecciones', 'red-cultural-individual-lesson') . "</h2>";
        $email_content .= "<p>Esta es una lista simulada de las lecciones que se liberarán en las próximas 24 horas.</p>";
        $email_content .= "<h3>Lección de Prueba (Curso: Literatura Universal)</h3>";
        $email_content .= "<p><strong>Fecha de liberación:</strong> " . date_i18n(get_option('date_format') . ' ' . get_option('time_format')) . "</p>";
        $email_content .= "<ul><li>Usuario Tester (tester@ejemplo.cl)</li></ul><hr>";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($to, 'TEST: ' . __('Lista de alumnos para próximas lecciones', 'red-cultural-individual-lesson'), $email_content, $headers);
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
        
        $email_content = "<h2>TEST: " . __('Lista de Alumnos de Lección de Curso', 'red-cultural-individual-lesson') . "</h2>";
        $email_content .= "<h3>{$lesson_title} (Curso: {$course_title})</h3>";
        $email_content .= "<p><strong>Fecha de liberación:</strong> " . date_i18n(get_option('date_format') . ' ' . get_option('time_format')) . "</p>";
        $email_content .= "<p>Los siguientes alumnos han comprado esta lección:</p>";
        $email_content .= "<ul><li>Tester User - tester@ejemplo.cl</li></ul><hr>";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($to, 'TEST: ' . sprintf(__('Lista de alumnos: %s', 'red-cultural-individual-lesson'), $lesson_title), $email_content, $headers);
    }

    private function send_test_register($to, $data_source)
    {
        $user_id = 0;
        $first_name = 'Usuario';
        
        if ($data_source === 'real') {
            $users = get_users(['number' => 1, 'orderby' => 'ID', 'order' => 'DESC']);
            if (!empty($users)) {
                $user_id = $users[0]->ID;
                $first_name = $users[0]->first_name ?: $users[0]->display_name;
            }
        } else {
            $first_name = 'Tester (Fake)';
        }

        if (class_exists('Red_Cultural_Templates')) {
            Red_Cultural_Templates::send_welcome_email($user_id, $first_name, $to);
            return true;
        }

        return false;
    }

    private function send_test_wc_new_order($to, $data_source, $subtype = '', $resource_id = 0)
    {
        if (!class_exists('WooCommerce')) {
            throw new Exception('WooCommerce no está activo.');
        }

        $order = null;
        if ($data_source === 'real') {
            $orders = wc_get_orders(['limit' => 1, 'orderby' => 'date', 'order' => 'DESC']);
            if (!empty($orders)) {
                $order = $orders[0];
            }
        }

        if (!$order) {
            // Create a fake order if no real order found or fake requested
            $order = $this->get_mock_order($subtype);
        }

        // Force the layout type for testing purposes
        if ($subtype) {
            add_filter('red_cultural_identify_order_type', function($type) use ($subtype) {
                if ($subtype === 'course') return 'course';
                if ($subtype === 'lessons') return 'lessons';
                if ($subtype === 'book') return 'physical';
                return $type;
            });
        }

        // Mock Resource Data if resource_id is provided
        // [OLD FILTER REMOVED - NOW PASSING VIA ARGS]

        // Override recipient for just this call
        add_filter('woocommerce_email_recipient_new_order', function($recipient, $order_id) use ($to) {
            return $to;
        }, 999, 2);

        // We will use our custom email class once it's implemented
        if (class_exists('Red_Cultural_WC_Emails')) {
            $sent = Red_Cultural_WC_Emails::get_instance()->send_custom_new_order($order->get_id(), $to, ['resource_id' => $resource_id]);
        } else {
            $mailer = WC()->mailer();
            $email = isset($mailer->emails['WC_Email_New_Order']) ? $mailer->emails['WC_Email_New_Order'] : null;
            if (!$email) throw new Exception('No se encontró la clase de email WC_Email_New_Order.');
            $sent = $email->trigger($order->get_id());
        }
        
        remove_all_filters('woocommerce_email_recipient_new_order', 999);
        return $sent;
    }

    private function send_test_wc_bank_transfer($to, $data_source, $resource_id = 0)
    {
        if (!class_exists('WooCommerce')) {
            throw new Exception('WooCommerce no está activo.');
        }

        $order = null;
        if ($data_source === 'real') {
            $orders = wc_get_orders(['limit' => 1, 'orderby' => 'date', 'order' => 'DESC', 'payment_method' => 'bacs']);
            if (!empty($orders)) {
                $order = $orders[0];
            }
        }

        if (!$order) {
            $order = $this->get_mock_order();
            $order->set_payment_method('bacs');
        }

        if (class_exists('Red_Cultural_WC_Emails')) {
            return Red_Cultural_WC_Emails::get_instance()->send_bank_transfer_notification($order->get_id(), $to, ['resource_id' => $resource_id]);
        }

        throw new Exception('La clase Red_Cultural_WC_Emails aún no está implementada.');
    }

    /**
     * Helper to get a mock order for testing designs.
     */
    private function get_mock_order($subtype = '')
    {
        $args = [
            'limit' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        // Refine search if subtype is provided
        if ($subtype === 'course') {
            $args['meta_query'] = [
                [
                    'key' => '_rcil_is_full_course',
                    'value' => '1',
                    'compare' => '='
                ]
            ];
        } elseif ($subtype === 'lessons') {
            $args['meta_query'] = [
                [
                    'key' => '_is_rcil_purchase',
                    'value' => '1',
                    'compare' => '='
                ],
                [
                    'key' => '_rcil_is_full_course',
                    'compare' => 'NOT EXISTS'
                ]
            ];
        }

        $orders = wc_get_orders($args);

        if ($subtype) {
            foreach ($orders as $o) {
                $type = Red_Cultural_WC_Emails::identify_order_type($o);
                if ($subtype === 'course' && $type === 'course') return $o;
                if ($subtype === 'lessons' && $type === 'lessons') return $o;
                if ($subtype === 'book' && $type === 'physical') return $o;
            }
        }

        // Final fallback if no specific order found with meta_query
        if (empty($orders)) {
            $orders = wc_get_orders(['limit' => 1, 'orderby' => 'date', 'order' => 'DESC']);
        }

        if (!empty($orders)) return $orders[0];
        
        throw new Exception('No se encontró ninguna orden para usar como base. Por favor realiza una compra de prueba en el sitio.');
    }
}
