<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class RC_Spam_Users_Table
 *
 * Visualización y gestión de usuarios sospechosos.
 */
final class RC_Spam_Users_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct([
            'singular' => 'spamuser',
            'plural'   => 'spamusers',
            'ajax'     => false,
        ]);
    }

    public function get_columns(): array
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'suspicious'   => __('Sospechoso', 'red-cultural-core'),
            'user_login'   => __('Usuario', 'red-cultural-core'),
            'user_email'   => __('Email', 'red-cultural-core'),
            'display_name' => __('Nombre', 'red-cultural-core'),
            'registered'   => __('Registrado', 'red-cultural-core'),
            'purchases'    => __('Compras', 'red-cultural-core'),
            'courses'      => __('Cursos', 'red-cultural-core'),
            'roles'        => __('Roles', 'red-cultural-core'),
        ];
    }

    protected function get_sortable_columns(): array
    {
        return [
            'user_login' => ['user_login', false],
            'user_email' => ['user_email', false],
            'registered' => ['registered', true],
        ];
    }

    protected function column_cb($item): string
    {
        return sprintf(
            '<input type="checkbox" name="users[]" value="%s" />',
            esc_attr((string) $item['user']->ID)
        );
    }

    protected function column_suspicious($item): string
    {
        $is_suspicious = !empty($item['spamusers_is_suspicious']);
        $is_excluded_cl = !empty($item['spamusers_excluded_cl']);
        $has_purchases = !empty($item['spamusers_has_purchases']);
        $has_courses = !empty($item['spamusers_has_courses']);

        if ($is_excluded_cl) {
            return '<span class="dashicons dashicons-yes" style="color:green;" title="Excluido .cl"></span>';
        }
        if ($has_purchases || $has_courses) {
            return '<span class="dashicons dashicons-yes" style="color:green;" title="Cliente Real"></span>';
        }

        return $is_suspicious ? '<span class="dashicons dashicons-warning" style="color:red;" title="Sospechoso"></span>' : '—';
    }

    protected function column_user_login($item): string
    {
        /** @var WP_User $user */
        $user = $item['user'];
        $edit_link = get_edit_user_link($user->ID);
        $label = $user->user_login ?: (string) $user->ID;

        $delete_url = wp_nonce_url(
            add_query_arg([
                'action' => 'rc_delete_spam_user',
                'user_id' => $user->ID,
            ], admin_url('admin-post.php')),
            'rc_delete_user_' . $user->ID
        );

        $actions = [
            'edit' => sprintf(
                '<a href="%s">%s</a>',
                esc_url($edit_link),
                __('Editar', 'red-cultural-core')
            ),
            'delete' => sprintf(
                '<a href="%s" class="rc-delete-spam-user" style="color:red;" onclick="return confirm(\'%s\');">%s</a>',
                esc_url($delete_url),
                esc_js(sprintf(__('¿Estás seguro de que deseas eliminar permanentemente al usuario %s?', 'red-cultural-core'), $label)),
                __('Eliminar permanentemente', 'red-cultural-core')
            ),
        ];

        return sprintf(
            '<a href="%s"><strong>%s</strong></a> %s',
            esc_url($edit_link),
            esc_html($label),
            $this->row_actions($actions)
        );
    }

    protected function column_user_email($item): string
    {
        return esc_html($item['user']->user_email);
    }

    protected function column_display_name($item): string
    {
        return esc_html($item['user']->display_name);
    }

    protected function column_registered($item): string
    {
        $registered = $item['user']->user_registered;
        if (!$registered || $registered === '0000-00-00 00:00:00') {
            return '—';
        }
        return esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $registered, true));
    }

    protected function column_purchases($item): string
    {
        return $item['spamusers_has_purchases'] ? '<strong>1+</strong>' : '0';
    }

    protected function column_courses($item): string
    {
        return $item['spamusers_has_courses'] ? '<strong>1+</strong>' : '0';
    }

    protected function column_roles($item): string
    {
        $roles = $item['user']->roles;
        if (empty($roles)) {
            return '—';
        }
        return esc_html(implode(', ', array_map('translate_user_role', $roles)));
    }

    protected function get_views(): array
    {
        $current_view = isset($_REQUEST['spamusers_view']) ? sanitize_key((string) $_REQUEST['spamusers_view']) : 'all';
        $base_url = remove_query_arg(['spamusers_view', 'paged']);

        return [
            'all' => sprintf(
                '<a href="%s"%s>%s</a>',
                esc_url(add_query_arg(['spamusers_view' => 'all'], $base_url)),
                $current_view === 'all' ? ' class="current"' : '',
                __('Todos', 'red-cultural-core')
            ),
            'suspicious' => sprintf(
                '<a href="%s"%s>%s</a>',
                esc_url(add_query_arg(['spamusers_view' => 'suspicious'], $base_url)),
                $current_view === 'suspicious' ? ' class="current"' : '',
                __('Sospechosos', 'red-cultural-core')
            ),
        ];
    }

    protected function extra_tablenav($which): void
    {
        if ($which !== 'top') {
            return;
        }

        $exclude_cl = !isset($_REQUEST['filter_action']) || isset($_REQUEST['exclude_cl']);
        $without_purchases = isset($_REQUEST['without_purchases']);

        echo '<div class="alignleft actions">';
        echo '<label style="margin-right:12px;"><input type="checkbox" name="exclude_cl" value="1"' . checked($exclude_cl, true, false) . ' /> ' . __('Excluir .cl', 'red-cultural-core') . '</label>';
        echo '<label style="margin-right:12px;"><input type="checkbox" name="without_purchases" value="1"' . checked($without_purchases, true, false) . ' /> ' . __('Solo sin compras', 'red-cultural-core') . '</label>';
        submit_button(__('Filtrar', 'red-cultural-core'), '', 'filter_action', false);
        echo '</div>';
    }

    public function prepare_items(): void
    {
        $per_page = 50;
        $paged = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $view = isset($_REQUEST['spamusers_view']) ? sanitize_key($_REQUEST['spamusers_view']) : 'all';

        $exclude_cl = !isset($_REQUEST['filter_action']) || isset($_REQUEST['exclude_cl']);
        $without_purchases = isset($_REQUEST['without_purchases']);

        $args = [
            'number' => ($view === 'suspicious') ? 1000 : $per_page,
            'offset' => ($view === 'suspicious') ? 0 : ($paged - 1) * $per_page,
            'orderby' => 'registered',
            'order' => 'DESC',
            'search' => $search ? '*' . $search . '*' : '',
        ];

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();

        $items = [];
        foreach ($users as $user) {
            $email = (string) $user->user_email;
            $login = (string) $user->user_login;

            $is_cl = (bool) preg_match('/\.cl$/i', $email);
            $has_purchase = $this->check_purchases((int) $user->ID);
            $has_course = $this->check_courses((int) $user->ID);

            // Reglas de sospecha básica
            $is_suspicious = false;
            if (!$is_cl && !$has_purchase && !$has_course) {
                // Login con muchas consonantes o muy largo sin sentido
                if (preg_match('/[bcdfghjklmnpqrstvwxyz]{5,}/i', $login) || strlen($login) > 20) {
                    $is_suspicious = true;
                }
                // Bots específicos detectados hoy (btc-transfer, USDT)
                if (stripos($user->display_name, 'USDT') !== false || stripos($user->display_name, 'btc-transfer') !== false || stripos($user->display_name, 'transfer-btc') !== false) {
                    $is_suspicious = true;
                }
            }

            if ($view === 'suspicious' && !$is_suspicious) {
                continue;
            }

            if ($exclude_cl && $is_cl) {
                if ($view === 'suspicious') continue;
            }

            if ($without_purchases && $has_purchase) {
                continue;
            }

            $items[] = [
                'user' => $user,
                'spamusers_is_suspicious' => $is_suspicious,
                'spamusers_excluded_cl' => $is_cl,
                'spamusers_has_purchases' => $has_purchase,
                'spamusers_has_courses' => $has_course,
            ];
        }

        if ($view === 'suspicious') {
            $total_items = count($items);
            $this->items = array_slice($items, ($paged - 1) * $per_page, $per_page);
        } else {
            $total_items = $user_query->get_total();
            $this->items = $items;
        }

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
    }

    private function check_purchases(int $user_id): bool
    {
        if (!function_exists('wc_get_orders')) return false;
        $orders = wc_get_orders(['customer_id' => $user_id, 'limit' => 1, 'status' => ['processing', 'completed', 'on-hold']]);
        return !empty($orders);
    }

    private function check_courses(int $user_id): bool
    {
        if (!function_exists('learndash_user_get_enrolled_courses')) return false;
        $courses = learndash_user_get_enrolled_courses($user_id);
        return !empty($courses);
    }
}
