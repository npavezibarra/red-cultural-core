<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles frontend rendering and assets for courses.
 */
class RCIL_Frontend
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
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Standard LearnDash hooks
        add_action('learndash-course-after-content', [$this, 'render_buy_lessons_button']);
        add_action('learndash-course-after-description', [$this, 'render_buy_lessons_button']);
        
        // Alumni Button Integration (Admin & Authors)
        add_action('learndash-course-after-content', [$this, 'render_alumni_button'], 25);
        add_action('learndash-course-after-description', [$this, 'render_alumni_button'], 25);
        
        // Remove Padlocks logic
        add_filter('learndash_lesson_row_class', [$this, 'filter_lesson_row_class'], 10, 2);

        // Shortcode as fallback
        add_shortcode('rcil_buy_lessons', [$this, 'render_buy_lessons_shortcode']);

        add_action('wp_footer', [$this, 'render_modal_container']);
        add_action('wp_footer', [$this, 'render_alumni_modal_container']);
        add_action('wp_footer', [$this, 'render_unlocked_lessons_js'], 99);

        // Force "Free Form" progression if individual pricing is on
        add_filter('learndash_course_progression_enabled', [$this, 'filter_course_progression_enabled'], 10, 2);
    }

    /**
     * Force "Free Form" progression if individual pricing is on.
     */
    public function filter_course_progression_enabled($enabled, $course_id)
    {
        $price = rcil_get_course_lesson_price($course_id);
        if ($price > 0) {
            return false; // Force Free Form
        }
        return $enabled;
    }

    /**
     * Shortcode handler.
     */
    public function render_buy_lessons_shortcode($atts)
    {
        $course_id = get_the_ID();
        ob_start();
        $this->render_buy_lessons_button($course_id);
        return ob_get_clean();
    }

    /**
     * Enqueue frontend CSS and JS.
     */
    public function enqueue_assets()
    {
        if (!is_singular(['sfwd-courses', 'sfwd-lessons', 'sfwd-topic'])) {
            return;
        }

        wp_enqueue_style(
            'rcil-frontend',
            RCIL_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            RCIL_VERSION
        );

        wp_enqueue_script(
            'rcil-frontend',
            RCIL_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            RCIL_VERSION,
            true
        );

        $params = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rcil_ajax_nonce'),
            'course_id' => is_singular('sfwd-courses') ? get_the_ID() : learndash_get_course_id(),
            'buying_label' => __('Redirigiendo...', 'red-cultural-individual-lesson'),
            'buy_course_label' => __('COMPRAR CURSO COMPLETO', 'red-cultural-individual-lesson'),
            'buy_lessons_label' => __('COMPRAR LECCIONES', 'red-cultural-individual-lesson'),
            'buy_some_lessons_label' => __('COMPRAR ALGUNAS LECCIONES', 'red-cultural-individual-lesson'),
            'loading_label' => __('Cargando...', 'red-cultural-individual-lesson'),
            'no_alumni_label' => __('No se encontraron alumnos para esta lección.', 'red-cultural-individual-lesson'),
            'name_label' => __('Nombre', 'red-cultural-individual-lesson'),
            'email_label' => __('Correo electrónico', 'red-cultural-individual-lesson'),
            'total_students_label' => __('Total de alumnos: ', 'red-cultural-individual-lesson'),
            'server_error_label' => __('Ocurrió un error en el servidor.', 'red-cultural-individual-lesson'),

            // Admin-only: inline lesson video URL editor
            'is_admin' => current_user_can('manage_options'),
            'saving_label' => __('Guardando...', 'red-cultural-individual-lesson'),
            'saved_label' => __('Guardado', 'red-cultural-individual-lesson'),
            'video_url_placeholder' => __('URL de YouTube...', 'red-cultural-individual-lesson'),
            'zoom_url_placeholder' => __('URL de Sesión Zoom...', 'red-cultural-individual-lesson'),
            'delete_confirm_label' => __('¿Estás seguro de que deseas eliminar esta lección? Esto la moverá a la Papelera.', 'red-cultural-individual-lesson'),
            'deleting_label' => __('Eliminando...', 'red-cultural-individual-lesson'),
            'rename_saving_label' => __('Guardando título...', 'red-cultural-individual-lesson'),
        ];

        // Template integration flags for Red Cultural Pages templates.
        $course_id_for_caps = 0;
        if (is_singular('sfwd-courses')) {
            $course_id_for_caps = (int) get_the_ID();
        } else {
            $course_id_for_caps = (int) learndash_get_course_id();
        }
        $user_id = get_current_user_id();
        $params['is_rcp_template'] = (bool) did_action('template_redirect'); // coarse signal; JS will also detect DOM IDs.
        $params['can_view_alumni'] = (bool) (current_user_can('manage_options') || ($course_id_for_caps > 0 && (int) get_post_field('post_author', $course_id_for_caps) === (int) $user_id));

        if (!empty($params['is_admin']) && is_singular('sfwd-courses')) {
            wp_enqueue_style('dashicons');

            $course_id = get_the_ID();
            $lessons = rcil_get_course_lessons($course_id);
            $map = [];

            foreach ($lessons as $lesson) {
                if (empty($lesson['post']) || !($lesson['post'] instanceof WP_Post)) {
                    continue;
                }
                $lesson_id = (int) $lesson['post']->ID;
                if (!$lesson_id) {
                    continue;
                }

                $permalink = get_permalink($lesson_id);
                if (empty($permalink)) {
                    continue;
                }

                $video_url = '';
                if (function_exists('learndash_get_setting')) {
                    $video_url = (string) learndash_get_setting($lesson_id, 'lesson_video_url');
                }
                if ($video_url === '') {
                    $settings = get_post_meta($lesson_id, '_sfwd-lessons', true);
                    if (is_array($settings) && isset($settings['sfwd-lessons_lesson_video_url'])) {
                        $video_url = (string) $settings['sfwd-lessons_lesson_video_url'];
                    }
                }

                $access_from = (int) get_post_meta($lesson_id, 'lesson_access_from', true);
                $available_from_iso = $access_from ? date('Y-m-d\TH:i', $access_from) : '';
                $available_from_display = $access_from ? date_i18n(get_option('date_format') . ' H:i', $access_from) : '';

                $key = untrailingslashit($permalink);
                $map[$key] = [
                    'id'    => $lesson_id,
                    'video_url' => $video_url,
                    'title' => (string) get_the_title($lesson_id),
                    'zoom_url' => (string) get_post_meta($lesson_id, '_rc_zoom_url', true),
                    'available_from_iso' => $available_from_iso,
                    'available_from_display' => $available_from_display,
                ];
            }

            $params['lesson_video_map'] = $map;
        }

        wp_localize_script('rcil-frontend', 'rcil_params', $params);
    }

    /**
     * Show "Buy Some Lessons" button if appropriate.
     */
    public function render_buy_lessons_button($course_id = null)
    {
        // Handle case where third-party actions pass extra arguments
        if (is_string($course_id) || is_bool($course_id)) {
            $course_id = get_the_ID();
        }

        if (!$course_id) {
            $course_id = get_the_ID();
        }

        if (!$this->should_show_button($course_id)) {
            echo "<!-- RCIL: hidding button for course $course_id. Price: " . rcil_get_course_lesson_price($course_id) . " -->"; 
            return;
        }

        ?>
        <div class="rcil-buy-button-container" style="margin-top: 15px; margin-bottom: 15px; clear: both;">
            <button id="rcil-open-modal" class="button btn-buy-some-lessons"
                style="padding: 10px 20px; font-weight: bold; cursor: pointer; display: inline-block;">
                <?php _e('COMPRAR ALGUNAS LECCIONES', 'red-cultural-individual-lesson'); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Show "ALUMNI LIST" button for Admin and Course Author
     */
    public function render_alumni_button($course_id = null)
    {
        static $has_rendered = false;
        if ($has_rendered) {
            return;
        }

        if (is_string($course_id) || is_bool($course_id)) {
            $course_id = get_the_ID();
        }

        if (!$course_id) {
            $course_id = get_the_ID();
        }

        $user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        $is_author = (get_post_field('post_author', $course_id) == $user_id);

        if (!$is_admin && !$is_author) {
            return;
        }

        $has_rendered = true;
        
        ?>
        <div class="rcil-alumni-button-container" style="margin-top: 15px; margin-bottom: 15px; clear: both; width: 100%;">
            <button id="rcil-open-alumni-modal" class="button"
                style="padding: 10px 20px; font-weight: bold; cursor: pointer; display: block; width: 100%; text-align: center; background-color: #f1f1f1; color: #333; border: 1px solid #ddd;">
                <?php _e('LISTA DE ALUMNOS', 'red-cultural-individual-lesson'); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Check if button should be visible.
     */
    private function should_show_button($course_id)
    {
        $price = rcil_get_course_lesson_price($course_id);
        if (!$price) {
            return false;
        }

        $lessons = rcil_get_course_lessons($course_id);
        if (empty($lessons)) {
            return false;
        }

        if (is_user_logged_in()) {
            // Admins can always see the button for testing purposes.
            if (current_user_can('manage_options')) {
                return true;
            }
            
            // Option: If user already have full course access, hide the button.
            if (rcil_user_has_full_course_access(get_current_user_id(), $course_id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filter classes to remove the 'not-available' gray-out effect on purchased lessons.
     */
    public function filter_lesson_row_class($lesson_class, $lesson)
    {
        if (!is_user_logged_in() || !isset($lesson['post'])) {
            return $lesson_class;
        }

        $user_id = get_current_user_id();
        $lesson_id = $lesson['post']->ID;
        $course_id = learndash_get_course_id($lesson_id);

        if (rcil_user_has_lesson_access($user_id, $lesson_id)) {
            $lesson_class = str_replace('learndash-not-available', '', $lesson_class);
            $lesson_class .= ' rcil-unlocked-lesson';
        } else {
            // Force locked class for unpurchased lessons in RCIL courses
            $price = rcil_get_course_lesson_price($course_id);
            if ($price > 0 && !rcil_user_has_full_course_access($user_id, $course_id)) {
                $lesson_class .= ' lms-is-locked';
                $lesson_class = str_replace('lms-not-locked', '', $lesson_class);
            }
        }

        return $lesson_class;
    }

    /**
     * JS helpers for individual lesson access:
     * - Disable "Mark Complete" on locked lessons.
     */
    public function render_unlocked_lessons_js()
    {
        if (!is_singular(['sfwd-courses', 'sfwd-lessons', 'sfwd-topic'])) {
            return;
        }

        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $course_id = learndash_get_course_id();

        if (!$course_id) {
            return;
        }
        
        // Only run logic if the course has a custom RCIL price (meaning individual access is active)
        $price = rcil_get_course_lesson_price($course_id);
        if (!$price) {
            return;
        }
        
        // If they bought the full course legitimately, do not add any padlocks.
        if (rcil_user_has_full_course_access($user_id, $course_id)) {
            return;
        }

        $lessons = rcil_get_course_lessons($course_id);
        if (empty($lessons)) {
            return;
        }

        $locked_urls = [];
        $first_unlocked_url = '';
        foreach ($lessons as $lesson) {
            $lesson_id = $lesson['post']->ID;
            if (!rcil_user_has_lesson_access($user_id, $lesson_id)) {
                $locked_urls[] = get_permalink($lesson_id);
            } else {
                if (empty($first_unlocked_url)) {
                    $first_unlocked_url = get_permalink($lesson_id);
                }
            }
        }
        
        if (empty($locked_urls)) {
            return;
        }
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var lockedUrls = <?php echo json_encode($locked_urls); ?>;
                
                // Disable Mark Complete button if the current lesson is locked
                var currentUrl = "<?php echo esc_js(get_permalink()); ?>";
                if ($.inArray(currentUrl, lockedUrls) !== -1) {
                    var $mcBtn = $('.learndash_mark_complete_button, form#sfwd-mark-complete input[type="submit"], input[name="sfwd_mark_complete"], .lms-mark-complete-button, .ld-mark-complete');
                    $mcBtn.prop('disabled', true).css({ 'opacity': '0.5', 'cursor': 'not-allowed' });
                    $mcBtn.attr('title', '<?php esc_attr_e('Debes comprar esta lección para marcarla como completada.', 'red-cultural-individual-lesson'); ?>');
                }
            });
        </script>
        <?php
    }

    /**
     * Skeleton for the lesson selection modal.
     */
    public function render_modal_container()
    {
        if (!is_singular(['sfwd-courses', 'sfwd-lessons', 'sfwd-topic'])) {
            return;
        }

        $course_id = learndash_get_course_id();
        $price = rcil_get_course_lesson_price($course_id);
        if (!$price) {
            return;
        }

        $lessons = rcil_get_course_lessons($course_id);
        $user_id = get_current_user_id();
        $full_course_price = rcil_get_full_course_price($course_id);

        ?>
        <div id="rcil-modal" class="rcil-modal" style="display:none;" 
             data-course-id="<?php echo $course_id; ?>"
             data-full-price="<?php echo $full_course_price; ?>">
            <div class="rcil-modal-content">
                <span class="rcil-close">&times;</span>
                <h2>
                    <?php echo get_the_title($course_id); ?>
                </h2>
                <p>
                    <?php _e('Selecciona una o más lecciones para comprar individualmente:', 'red-cultural-individual-lesson'); ?>
                </p>
                <form id="rcil-selection-form">
                    <div class="rcil-lessons-list">
                        <?php foreach ($lessons as $lesson):
                            $l_id = $lesson['post']->ID;
                            $has_access = rcil_user_has_lesson_access($user_id, $l_id);
                            ?>
                            <div class="rcil-lesson-item <?php echo $has_access ? 'rcil-purchased' : ''; ?>">
                                <label>
                                    <input type="checkbox" name="rcil_lessons[]" value="<?php echo $l_id; ?>"
                                        data-title="<?php echo esc_attr($lesson['post']->post_title); ?>" <?php echo $has_access ? 'checked disabled' : ''; ?>>
                                    <?php echo $lesson['post']->post_title; ?>
                                    <?php if ($has_access): ?> <span class="rcil-badge">
                                            <?php _e('(Comprado)', 'red-cultural-individual-lesson'); ?>
                                        </span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="rcil-modal-footer">
                        <div class="rcil-total-price">
                            <?php _e('Total:', 'red-cultural-individual-lesson'); ?>
                            <?php echo esc_html(get_woocommerce_currency_symbol()); ?>
                            <span id="rcil-total-amount" data-unit-price="<?php echo $price; ?>">0</span>
                        </div>
                        <button type="submit" id="rcil-submit-selection" class="button" disabled>
                            <?php _e('COMPRAR LECCIONES', 'red-cultural-individual-lesson'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Modal container for Alumni List
     */
    public function render_alumni_modal_container()
    {
        if (!is_singular(['sfwd-courses'])) {
            return;
        }

        $course_id = learndash_get_course_id();
        $user_id = get_current_user_id();
        
        $is_admin = current_user_can('manage_options');
        $is_author = (get_post_field('post_author', $course_id) == $user_id);

        if (!$is_admin && !$is_author) {
            return;
        }

        $lessons = rcil_get_course_lessons($course_id);
        if (empty($lessons)) return;
        ?>
        <div id="rcil-alumni-modal" class="rcil-modal" style="display:none;" data-course-id="<?php echo esc_attr($course_id); ?>">
            <div class="rcil-modal-content" style="max-width: 600px;">
                <span class="rcil-alumni-close rcil-close">&times;</span>
                <h2><?php _e('Lista de Alumnos', 'red-cultural-individual-lesson'); ?></h2>
                <p><?php _e('Selecciona una lección para ver quién tiene acceso:', 'red-cultural-individual-lesson'); ?></p>
                
                <select id="rcil-alumni-lesson-select" style="width: 100%; margin-bottom: 20px; padding: 10px; font-size: 16px;">
                    <option value=""><?php _e('-- Selecciona una Lección --', 'red-cultural-individual-lesson'); ?></option>
                    <?php foreach ($lessons as $lesson): ?>
                        <option value="<?php echo esc_attr($lesson['post']->ID); ?>"><?php echo esc_html($lesson['post']->post_title); ?></option>
                    <?php endforeach; ?>
                </select>

                <div id="rcil-alumni-results" style="max-height: 400px; overflow-y: auto;">
                    <!-- Table goes here -->
                </div>
            </div>
        </div>
        <?php
    }
}
