<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles security checks and LearnDash access filtering for individual lessons.
 */
class RCIL_Access_Control
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
        add_filter('the_content', [$this, 'filter_lesson_content'], 20);
        add_filter('sfwd_lms_has_access', [$this, 'filter_sfwd_lms_has_access'], 20, 3);
    }

    /**
     * Grant permission log for specific lessons.
     */
    public function grant_access($user_id, $course_id, $lesson_ids, $order_id, $is_full = false)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rcil_lesson_access';

        foreach ($lesson_ids as $lesson_id) {
            $lesson_id = absint($lesson_id);
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE user_id = %d AND lesson_id = %d AND status = 'active'",
                $user_id,
                $lesson_id
            ));

            if (!$exists) {
                $wpdb->insert($table_name, [
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'lesson_id' => $lesson_id,
                    'order_id' => $order_id,
                    'date_purchased' => date('Y-m-d H:i:s'),
                    'status' => 'active',
                    'is_full_access' => $is_full ? 1 : 0
                ]);
            }
        }

        // Enroll the user in the course to allow entry point.
        if (function_exists('ld_update_course_access')) {
            ld_update_course_access($user_id, $course_id, false);
        }
    }

    /**
     * Intercept access check.
     */
    public function filter_lesson_access($can_access, $lesson_id, $user_id, $course_id)
    {
        if (rcil_user_has_lesson_access($user_id, $lesson_id)) {
            return true;
        }

        $price = rcil_get_course_lesson_price($course_id);
        if ($price && !rcil_user_has_full_course_access($user_id, $course_id)) {
            return false;
        }

        return $can_access;
    }

    /**
     * Filter sfwd_lms_has_access.
     * We return TRUE to allow the user into the structure, but filter_lesson_access/content will block content.
     */
    public function filter_sfwd_lms_has_access($has_access, $post_id, $user_id)
    {
        $post_type = get_post_type($post_id);
        if (in_array($post_type, ['sfwd-lessons', 'sfwd-topic', 'sfwd-courses'])) {
            $course_id = ($post_type === 'sfwd-courses') ? $post_id : learndash_get_course_id($post_id);
            if (rcil_get_course_lesson_price($course_id) > 0) {
                return true; 
            }
        }
        return $has_access;
    }

    /**
     * Show custom message instead of lesson content if they don't have access.
     */
    public function filter_lesson_content($content)
    {
        if (is_singular(['sfwd-lessons', 'sfwd-topic'])) {
            $lesson_id = get_the_ID();
            $course_id = learndash_get_course_id($lesson_id);
            $user_id = get_current_user_id();

            // Do not bypass for guests. We need to check if the course requires access.
            $can_access = $this->filter_lesson_access(true, $lesson_id, (int)$user_id, $course_id);
            
            if (!$can_access) {
                $lesson_title = get_the_title($lesson_id);
                $block_msg = '<div class="rcil-locked-message" style="margin: 30px 0;">' .
                    '<h2 style="margin: 0 0 15px 0;">' . esc_html($lesson_title) . '</h2>' .
                    '<p style="margin: 0 0 15px 0;">' .
                    esc_html__('No tienes acceso a esta lección según tu selección de compra individual. Por favor, compra esta lección o el curso completo para ver el contenido.', 'red-cultural-individual-lesson') .
                    '</p>' .
                    '<div style="text-align: left; margin-top: 10px;">' .
                    '<button class="button rcil-buy-lessons-btn" data-course="' . esc_attr($course_id) . '">' . esc_html__('Comprar Lección', 'red-cultural-individual-lesson') . '</button>' .
                    '</div>' .
                    '</div>';
                
                return $block_msg;
            }
        }
        return $content;
    }
}
