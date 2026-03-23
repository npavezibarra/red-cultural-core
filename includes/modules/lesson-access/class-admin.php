<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles Admin metaboxes and course settings for individual lesson pricing.
 */
class RCIL_Admin
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
        add_action('add_meta_boxes_sfwd-courses', [$this, 'add_course_metabox']);
        add_action('save_post_sfwd-courses', [$this, 'save_course_metas']);

        // Admin Menu & Settings
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'handle_manual_notification_trigger']);
    }

    /**
     * Handle manual trigger for upcoming lessons check.
     */
    public function handle_manual_notification_trigger()
    {
        if (isset($_POST['rcil_trigger_notification']) && current_user_can('manage_options')) {
            check_admin_referer('rcil_trigger_notif_nonce');
            
            do_action('rcil_daily_lesson_check');
            
            add_settings_error(
                'rcil_notifications_group',
                'rcil_notif_sent',
                __('Verificación global de notificaciones activada con éxito. Se enviarán correos si se encuentran lecciones próximas (24 h) con compradores.', 'red-cultural-individual-lesson'),
                'updated'
            );
        }

        if (isset($_POST['rcil_send_specific_lesson']) && current_user_can('manage_options')) {
            check_admin_referer('rcil_send_lesson_nonce');
            
            $lesson_id = absint($_POST['rcil_send_specific_lesson']);
            $success = RCIL_Notifications::get_instance()->send_lesson_notification($lesson_id);
            
            if ($success) {
                add_settings_error(
                    'rcil_notifications_group',
                    'rcil_notif_sent_specific',
                    __('Lista de correos enviada con éxito para la lección seleccionada.', 'red-cultural-individual-lesson'),
                    'updated'
                );
            } else {
                add_settings_error(
                    'rcil_notifications_group',
                    'rcil_notif_failed_specific',
                    __('Error al enviar la lista de correos. Asegúrate de que la lección tenga compradores y de que los correos de notificación estén configurados.', 'red-cultural-individual-lesson'),
                    'error'
                );
            }
        }
    }

    /**
     * Register top-level menu and subpages.
     */
    public function register_admin_menu()
    {
        add_menu_page(
            __('Red Cultural', 'red-cultural-individual-lesson'),
            __('Red Cultural', 'red-cultural-individual-lesson'),
            'manage_options',
            'rcil-main',
            [$this, 'render_main_admin_page'],
            'dashicons-welcome-learn-more',
            30
        );

        add_submenu_page(
            'rcil-main',
            __('Notificaciones', 'red-cultural-individual-lesson'),
            __('Notificaciones', 'red-cultural-individual-lesson'),
            'manage_options',
            'rcil-notifications',
            [$this, 'render_notifications_page']
        );
        
        // Remove the default duplicate menu entry
        remove_submenu_page('rcil-main', 'rcil-main');
    }

    /**
     * Register plugin settings.
     */
    public function register_settings()
    {
        register_setting('rcil_notifications_group', 'rcil_notification_emails');

        add_settings_section(
            'rcil_notifications_section',
            __('Configuración de Notificaciones', 'red-cultural-individual-lesson'),
            '',
            'rcil-notifications'
        );

        add_settings_field(
            'rcil_notification_emails',
            __('Correos de Notificación', 'red-cultural-individual-lesson'),
            [$this, 'render_emails_field'],
            'rcil-notifications',
            'rcil_notifications_section'
        );
    }

    /**
     * Render main admin page (Dashboard/Overview).
     */
    public function render_main_admin_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Lecciones de Red Cultural', 'red-cultural-individual-lesson'); ?></h1>
            <p><?php _e('Bienvenido al panel de administración de Lecciones Individuales de Red Cultural.', 'red-cultural-individual-lesson'); ?></p>
        </div>
        <?php
    }

    /**
     * Render notifications subpage.
     */
    public function render_notifications_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('Notificaciones', 'red-cultural-individual-lesson'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('rcil_notifications_group');
                do_settings_sections('rcil-notifications');
                submit_button();
                ?>
            </form>


            <hr>
            <h2><?php _e('Upcoming Lessons (Next 10)', 'red-cultural-individual-lesson'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Título de la Lección', 'red-cultural-individual-lesson'); ?></th>
                        <th><?php _e('Curso', 'red-cultural-individual-lesson'); ?></th>
                        <th><?php _e('Fecha de liberación', 'red-cultural-individual-lesson'); ?></th>
                        <th><?php _e('Compradores Individuales', 'red-cultural-individual-lesson'); ?></th>
                        <th><?php _e('Acciones', 'red-cultural-individual-lesson'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $upcoming = RCIL_Notifications::get_instance()->get_upcoming_lessons_list(10);
                    if (!empty($upcoming)):
                        foreach ($upcoming as $item):
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($item->title); ?></strong></td>
                                <td><?php echo esc_html($item->course_title); ?></td>
                                <td><?php echo esc_html($item->release_date); ?></td>
                                <td><?php echo esc_html($item->buyer_count); ?></td>
                                <td>
                                    <form method="post" action="" style="display:inline;">
                                        <?php wp_nonce_field('rcil_send_lesson_nonce'); ?>
                                        <input type="hidden" name="rcil_send_specific_lesson" value="<?php echo esc_attr($item->ID); ?>">
                                        <input type="submit" class="button button-small" value="<?php esc_attr_e('Enviar lista de correos', 'red-cultural-individual-lesson'); ?>" <?php echo ($item->buyer_count == 0) ? 'disabled' : ''; ?>>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        endforeach;
                    else:
                        ?>
                        <tr>
                            <td colspan="5"><?php _e('No se encontraron lecciones próximas con una fecha de liberación programada.', 'red-cultural-individual-lesson'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (isset($_GET['rcil_debug'])): ?>
            <hr>
            <h2>Debug Meta (Recent Lessons)</h2>
            <pre style="background: #000; color: #0f0; padding: 10px; overflow: auto; max-height: 400px;">
<?php
$debug_lessons = get_posts(['post_type' => 'sfwd-lessons', 'posts_per_page' => 20]);
foreach ($debug_lessons as $dl) {
    echo "ID: {$dl->ID} | Title: {$dl->post_title}\n";
    $m = get_post_meta($dl->ID);
    foreach ($m as $k => $v) {
        if (strpos($k, 'sfwd') !== false || strpos($k, 'visible') !== false || strpos($k, 'date') !== false || strpos($k, 'learndash') !== false) {
             echo "  $k => " . print_r($v[0], true) . "\n";
        }
    }
    echo "--------------------\n";
}
?>
            </pre>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render email addresses field.
     */
    public function render_emails_field()
    {
        $emails = get_option('rcil_notification_emails', '');
        ?>
        <textarea name="rcil_notification_emails" rows="5" cols="50" class="large-text" 
                  placeholder="email1@example.com, email2@example.com"><?php echo esc_textarea($emails); ?></textarea>
        <p class="description">
            <?php _e('Introduce una o más direcciones de correo electrónico separadas por comas.', 'red-cultural-individual-lesson'); ?>
        </p>
        <?php
    }

    /**
     * Register the metabox on LearnDash Course screen.
     */
    public function add_course_metabox()
    {
        add_meta_box(
            'rcil_course_individual_price',
            __('Configuración de Compra de Lecciones Individuales', 'red-cultural-individual-lesson'),
            [$this, 'render_metabox'],
            'sfwd-courses',
            'side',
            'default'
        );
    }

    /**
     * Render metabox HTML.
     */
    public function render_metabox($post)
    {
        $price = get_post_meta($post->ID, '_individual_lesson_price', true);
        wp_nonce_field('rcil_save_metabox', 'rcil_nonce');
        ?>
        <div class="rcil-metabox-field">
            <p><strong><label for="rcil_individual_lesson_price">
                        <?php _e('Precio de Lección Individual', 'red-cultural-individual-lesson'); ?>
                    </label></strong></p>
            <input type="number" id="rcil_individual_lesson_price" name="rcil_individual_lesson_price"
                value="<?php echo esc_attr($price); ?>" step="1" min="0" style="width:100%;" />
            <p class="description">
                <?php _e('Precio aplicado a cada lección cuando se compra individualmente. Dejar vacío para desactivar. <strong>Nota:</strong> Al activar esto, la Progresión del Curso se ajustará automáticamente a "Forma Libre".', 'red-cultural-individual-lesson'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save metabox values.
     */
    public function save_course_metas($post_id)
    {
        if (!isset($_POST['rcil_nonce']) || !wp_verify_nonce($_POST['rcil_nonce'], 'rcil_save_metabox')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['rcil_individual_lesson_price'])) {
            $price = sanitize_text_field($_POST['rcil_individual_lesson_price']);
            if (is_numeric($price) && $price > 0) {
                update_post_meta($post_id, '_individual_lesson_price', absint($price));
                
                // Automatically set progression to "Free Form" if individual purchases are active.
                if (function_exists('learndash_update_setting')) {
                    learndash_update_setting($post_id, 'course_disable_lesson_progression', 'on');
                } else {
                    $settings = get_post_meta($post_id, '_sfwd-courses', true);
                    if (is_array($settings)) {
                        $settings['sfwd-courses_course_disable_lesson_progression'] = 'on';
                        update_post_meta($post_id, '_sfwd-courses', $settings);
                    }
                }
            } else {
                delete_post_meta($post_id, '_individual_lesson_price');
            }
        }
    }
}
