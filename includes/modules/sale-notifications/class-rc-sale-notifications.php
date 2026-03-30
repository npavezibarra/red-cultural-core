<?php
/**
 * Handles the "Notificaciones de Venta" admin page.
 *
 * Allows configuring an extra email recipient for WooCommerce
 * new-order notifications (card & bank transfer approvals).
 */

if (!defined('ABSPATH')) {
    exit;
}

final class RC_Sale_Notifications
{
    private static $instance = null;

    /** wp_options key where the notification email(s) are stored. */
    const OPTION_KEY = 'rc_sale_notification_emails';

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
        add_action('admin_menu', [$this, 'register_admin_page'], 99);
        add_action('admin_post_rc_save_sale_notifications', [$this, 'handle_save']);
    }

    /* ------------------------------------------------------------------ */
    /*  Admin Menu                                                        */
    /* ------------------------------------------------------------------ */

    public function register_admin_page()
    {
        add_submenu_page(
            'red-cultural-pages',              // parent slug
            'Notificaciones de Venta',         // page title
            'Notificaciones de Venta',         // menu title
            'manage_options',                  // capability
            'rc-sale-notifications',           // slug
            [$this, 'render_admin_page']       // callback
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Render Page                                                       */
    /* ------------------------------------------------------------------ */

    public function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para ver esta página.', 'red-cultural-core'));
        }

        $saved   = get_option(self::OPTION_KEY, '');
        $updated = isset($_GET['rc_sn_updated']) && $_GET['rc_sn_updated'] === '1';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Notificaciones de Venta', 'red-cultural-core'); ?></h1>

            <?php if ($updated) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__('Configuración guardada correctamente.', 'red-cultural-core'); ?></p>
                </div>
            <?php endif; ?>

            <p><?php echo esc_html__('Configura las direcciones de correo electrónico que recibirán una copia de las notificaciones cuando se apruebe una nueva orden de compra.', 'red-cultural-core'); ?></p>
            <p class="description" style="margin-bottom: 20px;">
                <?php echo esc_html__('Estas notificaciones se enviarán además del correo del administrador del sitio. Aplica para órdenes aprobadas por tarjeta de crédito/débito y transferencia bancaria.', 'red-cultural-core'); ?>
            </p>

            <div class="card" style="max-width: 700px; padding: 24px; border-radius: 4px;">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="rc_save_sale_notifications" />
                    <?php wp_nonce_field('rc_save_sale_notifications', 'rc_sn_nonce'); ?>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <label for="rc_sale_notif_emails"><?php echo esc_html__('Correos de notificación', 'red-cultural-core'); ?></label>
                            </th>
                            <td>
                                <input
                                    name="rc_sale_notif_emails"
                                    type="text"
                                    id="rc_sale_notif_emails"
                                    value="<?php echo esc_attr($saved); ?>"
                                    class="regular-text"
                                    style="width: 100%;"
                                    placeholder="ventas@redcultural.cl, otro@ejemplo.cl"
                                />
                                <p class="description">
                                    <?php echo esc_html__('Puedes agregar varios correos separados por coma. Estos recibirán una copia del email de confirmación de venta.', 'red-cultural-core'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 8px; padding: 12px 16px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 2px;">
                        <strong><?php echo esc_html__('¿Cuándo se envía?', 'red-cultural-core'); ?></strong>
                        <ul style="margin: 8px 0 0 20px; list-style: disc;">
                            <li><?php echo esc_html__('Pago con tarjeta de crédito/débito: al aprobarse el pago (estado "Procesando").', 'red-cultural-core'); ?></li>
                            <li><?php echo esc_html__('Transferencia bancaria: al confirmarse la transferencia (de "En espera" a "Procesando").', 'red-cultural-core'); ?></li>
                        </ul>
                    </div>

                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php echo esc_html__('Guardar Configuración', 'red-cultural-core'); ?>
                        </button>
                    </p>
                </form>
            </div>

            <?php if ($saved) : ?>
            <div style="margin-top: 20px; padding: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; max-width: 700px;">
                <strong style="color: #15803d;">✓ <?php echo esc_html__('Activo', 'red-cultural-core'); ?></strong>
                <p style="margin: 4px 0 0;">
                    <?php
                    printf(
                        esc_html__('Las notificaciones de nuevas ventas se enviarán a: %s', 'red-cultural-core'),
                        '<code>' . esc_html($saved) . '</code>'
                    );
                    ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /* ------------------------------------------------------------------ */
    /*  Save Handler                                                      */
    /* ------------------------------------------------------------------ */

    public function handle_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('No tienes permisos para realizar esta acción.', 'red-cultural-core'));
        }

        if (!isset($_POST['rc_sn_nonce']) || !wp_verify_nonce($_POST['rc_sn_nonce'], 'rc_save_sale_notifications')) {
            wp_die(esc_html__('Nonce inválido.', 'red-cultural-core'));
        }

        $raw   = isset($_POST['rc_sale_notif_emails']) ? sanitize_text_field($_POST['rc_sale_notif_emails']) : '';
        $parts = explode(',', $raw);
        $clean = [];

        foreach ($parts as $email) {
            $email = sanitize_email(trim($email));
            if ($email !== '' && is_email($email)) {
                $clean[] = $email;
            }
        }

        $value = implode(', ', $clean);
        update_option(self::OPTION_KEY, $value, false);

        wp_safe_redirect(admin_url('admin.php?page=rc-sale-notifications&rc_sn_updated=1'));
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  Public Helper — get configured recipients                         */
    /* ------------------------------------------------------------------ */

    /**
     * Return an array of validated email addresses, or empty array.
     */
    public static function get_notification_emails(): array
    {
        $raw = get_option(self::OPTION_KEY, '');
        if (empty($raw)) {
            return [];
        }

        $emails = array_map('trim', explode(',', $raw));
        return array_filter($emails, 'is_email');
    }
}
