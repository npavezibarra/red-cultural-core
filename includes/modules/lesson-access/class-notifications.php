<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles daily notifications for upcoming lessons.
 */
class RCIL_Notifications
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
        add_action('rcil_daily_lesson_check', [$this, 'check_upcoming_lessons']);
        add_action('rcil_scheduled_lesson_notif', [$this, 'send_lesson_notification']);
        
        // Listen for lesson updates to schedule specific crons
        add_action('save_post_sfwd-lessons', [$this, 'sync_lesson_cron'], 20, 2);

        // Register Cron on init if not scheduled
        if (!wp_next_scheduled('rcil_daily_lesson_check')) {
            wp_schedule_event(time(), 'daily', 'rcil_daily_lesson_check');
        }
    }

    /**
     * Main task: Find lessons becoming public and notify.
     */
    public function check_upcoming_lessons()
    {
        $now = time();
        $tomorrow = $now + (24 * HOUR_IN_SECONDS);

        // Get notification emails
        $emails_option = get_option('rcil_notification_emails', '');
        if (empty($emails_option)) {
            return;
        }

        $notification_emails = array_map('trim', explode(',', $emails_option));
        $notification_emails = array_filter($notification_emails, 'is_email');

        if (empty($notification_emails)) {
            return;
        }

        // Find all lessons and filter by the serialized meta
        $args = [
            'post_type' => 'sfwd-lessons',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_sfwd-lessons',
                    'compare' => 'EXISTS'
                ]
            ]
        ];

        $all_lessons = get_posts($args);
        $upcoming_lessons = [];

        foreach ($all_lessons as $lesson) {
            $settings = get_post_meta($lesson->ID, '_sfwd-lessons', true);
            if (!is_array($settings)) continue;

            $lesson_schedule = isset($settings['sfwd-lessons_lesson_schedule']) ? $settings['sfwd-lessons_lesson_schedule'] : '';
            if ($lesson_schedule !== 'visible_after_specific_date') continue;

            $release_time = isset($settings['sfwd-lessons_visible_after_specific_date']) ? (int)$settings['sfwd-lessons_visible_after_specific_date'] : 0;
            
            if ($release_time >= $now && $release_time <= $tomorrow) {
                $upcoming_lessons[] = $lesson;
            }
        }

        if (empty($upcoming_lessons)) {
            return;
        }

        $email_content = "<h2>" . __('Upcoming Lessons Notification', 'red-cultural-individual-lesson') . "</h2>";
        $email_content .= "<p>" . __('The following lessons are scheduled to become public in the next 24 hours:', 'red-cultural-individual-lesson') . "</p>";

        $found_buyers = false;

        foreach ($upcoming_lessons as $lesson) {
            $lesson_id = $lesson->ID;
            $lesson_title = $lesson->post_title;
            $course_id = learndash_get_course_id($lesson_id);
            $settings = get_post_meta($lesson->ID, '_sfwd-lessons', true);
            $release_time = isset($settings['sfwd-lessons_visible_after_specific_date']) ? (int)$settings['sfwd-lessons_visible_after_specific_date'] : 0;
            $release_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $release_time);

            $buyers = $this->get_lesson_buyers($lesson_id);

            if (!empty($buyers)) {
                $found_buyers = true;
                $email_content .= "<h3>$lesson_title (" . __('Course', 'red-cultural-individual-lesson') . ": " . get_the_title($course_id) . ")</h3>";
                $email_content .= "<p><strong>" . __('Release Date', 'red-cultural-individual-lesson') . ":</strong> $release_date</p>";
                $email_content .= "<ul>";
                foreach ($buyers as $buyer) {
                    $email_content .= "<li>{$buyer->display_name} ({$buyer->user_email})</li>";
                }
                $email_content .= "</ul><hr>";
            }
        }

        if ($found_buyers) {
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            foreach ($notification_emails as $email) {
                wp_mail(
                    $email,
                    __('Alumni list for upcoming lessons', 'red-cultural-individual-lesson'),
                    $email_content,
                    $headers
                );
            }
        }
    }

    /**
     * Send notification for a specific lesson ID.
     */
    public function send_lesson_notification($lesson_id)
    {
        $lesson = get_post($lesson_id);
        if (!$lesson || $lesson->post_type !== 'sfwd-lessons') {
            return false;
        }

        // Get notification emails
        $emails_option = get_option('rcil_notification_emails', '');
        if (empty($emails_option)) {
            return false;
        }

        $notification_emails = array_map('trim', explode(',', $emails_option));
        $notification_emails = array_filter($notification_emails, 'is_email');

        if (empty($notification_emails)) {
            return false;
        }

        $course_id = learndash_get_course_id($lesson_id);
        $settings = get_post_meta($lesson_id, '_sfwd-lessons', true);
        $release_time = isset($settings['sfwd-lessons_visible_after_specific_date']) ? (int)$settings['sfwd-lessons_visible_after_specific_date'] : 0;
        $release_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $release_time);

        $buyers = $this->get_lesson_buyers($lesson_id);

        if (empty($buyers)) {
            return false;
        }

        $email_content = "<h2>" . __('Course Lesson Alumni List', 'red-cultural-individual-lesson') . "</h2>";
        $email_content .= "<h3>{$lesson->post_title} (" . __('Course', 'red-cultural-individual-lesson') . ": " . get_the_title($course_id) . ")</h3>";
        $email_content .= "<p><strong>" . __('Release Date', 'red-cultural-individual-lesson') . ":</strong> $release_date</p>";
        $email_content .= "<p>" . __('The following students have purchased this lesson:', 'red-cultural-individual-lesson') . "</p>";
        $email_content .= "<ul>";
        foreach ($buyers as $buyer) {
            $name_part = trim($buyer->first_name . ' ' . $buyer->last_name);
            if (empty($name_part)) {
                $name_part = $buyer->display_name;
            }
            $email_content .= "<li>{$name_part} - {$buyer->user_email}</li>";
        }
        $email_content .= "</ul><hr>";

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $sent = false;
        foreach ($notification_emails as $email) {
            if (wp_mail(
                $email,
                sprintf(__('Alumni list: %s', 'red-cultural-individual-lesson'), $lesson->post_title),
                $email_content,
                $headers
            )) {
                $sent = true;
            }
        }

        return $sent;
    }

    /**
     * Sync precision cron for a specific lesson (15 min before release).
     */
    public function sync_lesson_cron($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_status !== 'publish') {
             wp_clear_scheduled_hook('rcil_scheduled_lesson_notif', [$post_id]);
             return;
        }

        $settings = get_post_meta($post_id, '_sfwd-lessons', true);
        if (!is_array($settings)) {
            wp_clear_scheduled_hook('rcil_scheduled_lesson_notif', [$post_id]);
            return;
        }

        $lesson_schedule = isset($settings['sfwd-lessons_lesson_schedule']) ? $settings['sfwd-lessons_lesson_schedule'] : '';
        $release_time = isset($settings['sfwd-lessons_visible_after_specific_date']) ? (int)$settings['sfwd-lessons_visible_after_specific_date'] : 0;

        // Clear any existing precision cron for this lesson
        wp_clear_scheduled_hook('rcil_scheduled_lesson_notif', [$post_id]);

        if ($lesson_schedule === 'visible_after_specific_date' && $release_time > 0) {
            $notification_time = $release_time - (15 * MINUTE_IN_SECONDS);
            
            // Only schedule if the notification time is in the future
            if ($notification_time > time()) {
                wp_schedule_single_event($notification_time, 'rcil_scheduled_lesson_notif', [$post_id]);
            }
        }
    }

    /**
     * Get a list of upcoming lessons chronologically.
     */
    public function get_upcoming_lessons_list($limit = 10)
    {
        $now = time();
        $args = [
            'post_type' => 'sfwd-lessons',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_sfwd-lessons',
                    'compare' => 'EXISTS'
                ]
            ]
        ];

        $all_lessons = get_posts($args);
        $result = [];

        foreach ($all_lessons as $lesson) {
            $settings = get_post_meta($lesson->ID, '_sfwd-lessons', true);
            if (!is_array($settings)) continue;

            $lesson_schedule = isset($settings['sfwd-lessons_lesson_schedule']) ? $settings['sfwd-lessons_lesson_schedule'] : '';
            if ($lesson_schedule !== 'visible_after_specific_date') continue;

            $release_time = isset($settings['sfwd-lessons_visible_after_specific_date']) ? (int)$settings['sfwd-lessons_visible_after_specific_date'] : 0;
            
            if ($release_time > $now) {
                $result[] = (object)[
                    'ID' => $lesson->ID,
                    'title' => $lesson->post_title,
                    'course_title' => get_the_title(learndash_get_course_id($lesson->ID)),
                    'release_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $release_time),
                    'release_timestamp' => $release_time,
                    'buyer_count' => count($this->get_lesson_buyers($lesson->ID))
                ];
            }
        }

        // Sort by release date ASC
        usort($result, function($a, $b) {
            return $a->release_timestamp - $b->release_timestamp;
        });

        // Limit to $limit
        return array_slice($result, 0, $limit);
    }

    /**
     * Get users who purchased/have access to a specific lesson.
     */
    public function get_lesson_buyers($lesson_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rcil_lesson_access';

        $user_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM $table_name WHERE lesson_id = %d AND status = 'active'",
            $lesson_id
        ));

        if (empty($user_ids)) {
            return [];
        }

        $users = [];
        foreach ($user_ids as $uid) {
            $user_data = get_userdata($uid);
            if ($user_data) {
                $users[] = (object)[
                    'display_name' => $user_data->display_name,
                    'first_name'   => get_user_meta($uid, 'first_name', true),
                    'last_name'    => get_user_meta($uid, 'last_name', true),
                    'user_email'   => $user_data->user_email
                ];
            }
        }

        return $users;
    }
}
