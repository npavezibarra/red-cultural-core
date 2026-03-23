<?php
/**
 * Plugin Name: Red Cultural Core
 * Description: Main core plugin for Red Cultural. Merges lesson access, custom shipping, and page templates.
 * Version: 1.0.0
 * Author: Nico / Politeia
 * Text Domain: red-cultural-core
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define Core Constants
define('RC_CORE_VERSION', '1.0.0');
define('RC_CORE_FILE', __FILE__);
define('RC_CORE_PATH', plugin_dir_path(__FILE__));
define('RC_CORE_URL', plugin_dir_url(__FILE__));

/**
 * Main Core Class
 */
final class Red_Cultural_Core
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_dependencies();
        $this->init_modules();
    }

    private function load_dependencies()
    {
        // Core logical handlers from merged plugins
        require_once RC_CORE_PATH . 'includes/shared/functions-helpers.php';
    }

    private function init_modules()
    {
        // 1. Templates Component (formerly red-cultural-pages)
        require_once RC_CORE_PATH . 'includes/modules/templates/class-rc-templates.php';
        Red_Cultural_Templates::init();

        // 2. Shipping Component (formerly red-cultural-shipping)
        if (class_exists('WooCommerce')) {
            require_once RC_CORE_PATH . 'includes/modules/shipping/class-rc-shipping-manager.php';
            Red_Cultural_Shipping::init();
        }

        // 3. Lesson Access Component (formerly red-cultural-individual-lesson)
        if (class_exists('WooCommerce') && class_exists('SFWD_LMS')) {
            require_once RC_CORE_PATH . 'includes/modules/lesson-access/class-rc-lesson-manager.php';
            Red_Cultural_Lesson_Manager::init();
        }

        // 4. Email Tester Component
        require_once RC_CORE_PATH . 'includes/modules/email-tester/class-rc-email-tester.php';
        Red_Cultural_Email_Tester::init();
    }
}

/**
 * Boot the core
 */
function rc_core_init()
{
    return Red_Cultural_Core::get_instance();
}

add_action('plugins_loaded', 'rc_core_init', 10);
