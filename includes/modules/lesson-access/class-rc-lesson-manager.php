<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manager for Lesson Access Module
 */
final class Red_Cultural_Lesson_Manager
{
    public static function init()
    {
        if (!class_exists('WooCommerce') || !class_exists('SFWD_LMS')) {
            return;
        }

        // Define constants for the lesson module if needed
        if (!defined('RCIL_PLUGIN_PATH')) {
            define('RCIL_PLUGIN_PATH', RC_CORE_PATH . 'includes/modules/lesson-access/');
        }
        if (!defined('RCIL_PLUGIN_URL')) {
            define('RCIL_PLUGIN_URL', RC_CORE_URL . 'includes/modules/lesson-access/');
        }
        if (!defined('RCIL_VERSION')) {
            define('RCIL_VERSION', RC_CORE_VERSION);
        }

        // Load Lesson Classes
        require_once RC_CORE_PATH . 'includes/modules/lesson-access/functions-helpers.php';
        require_once RC_CORE_PATH . 'includes/modules/lesson-access/class-admin.php';
        require_once RC_CORE_PATH . 'includes/modules/lesson-access/class-frontend.php';
        require_once RC_CORE_PATH . 'includes/modules/lesson-access/class-ajax.php';
        require_once RC_CORE_PATH . 'includes/modules/lesson-access/class-woocommerce.php';
        require_once RC_CORE_PATH . 'includes/modules/lesson-access/class-access-control.php';
        require_once RC_CORE_PATH . 'includes/modules/lesson-access/class-notifications.php';
        require_once RC_CORE_PATH . 'includes/modules/lesson-access/pricing/default-lesson-price.php';

        // Initialize Components
        RCIL_Admin::get_instance();
        RCIL_Frontend::get_instance();
        RCIL_Ajax::get_instance();
        RCIL_WooCommerce::get_instance();
        RCIL_Access_Control::get_instance();
        RCIL_Notifications::get_instance();
    }
}
