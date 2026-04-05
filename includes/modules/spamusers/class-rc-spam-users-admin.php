<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class RC_Spam_Users_Admin
 *
 * Administrador del panel de control de Spam Users.
 */
final class RC_Spam_Users_Admin
{
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'register_menu']);
        add_action('admin_post_rc_delete_spam_user', [self::class, 'handle_delete']);
    }

    public static function register_menu(): void
    {
        add_menu_page(
            __('Spam Users', 'red-cultural-core'),
            __('Spam Users', 'red-cultural-core'),
            'list_users',
            'rc-spam-users',
            [self::class, 'render_page'],
            'dashicons-shield',
            81
        );
    }

    public static function handle_delete(): void
    {
        if (!current_user_can('delete_users')) {
            wp_die(__('No tienes permisos para realizar esta acción.', 'red-cultural-core'));
        }

        $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
        check_admin_referer('rc_delete_user_' . $user_id);

        if ($user_id > 0) {
            // No permitir borrarse a sí mismo por accidente
            if ($user_id === get_current_user_id()) {
                wp_die(__('No puedes eliminarte a ti mismo.', 'red-cultural-core'));
            }

            // Borrar el usuario y sus metadatos (WP lo hace por defecto)
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user($user_id);

            wp_redirect(add_query_arg([
                'page' => 'rc-spam-users',
                'deleted' => 1,
            ], admin_url('admin.php')));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=rc-spam-users'));
        exit;
    }

    public static function render_page(): void
    {
        if (!current_user_can('list_users')) {
            wp_die(__('No tienes permisos para ver esta página.', 'red-cultural-core'));
        }

        require_once RC_CORE_PATH . 'includes/modules/spamusers/class-rc-spam-users-table.php';

        $table = new RC_Spam_Users_Table();
        $table->prepare_items();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Gestión de Spam Users', 'red-cultural-core') . '</h1>';

        if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Usuario eliminado correctamente.', 'red-cultural-core') . '</p></div>';
        }

        echo '<p>' . esc_html__('Este panel permite identificar y eliminar cuentas generadas por bots. Usa la pestaña "Sospechosos" para ver los registros con patrones de spam conocidos.', 'red-cultural-core') . '</p>';

        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="rc-spam-users" />';
        $table->search_box(__('Buscar usuarios', 'red-cultural-core'), 'rc-spam-users');
        $table->display();
        echo '</form>';

        echo '</div>';
    }
}
