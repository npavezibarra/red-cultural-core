<?php
/**
 * Red Cultural Templates Module.
 *
 * This class acts as a loader/bootstrap for the modularized template system.
 * It coordinates sub-modules for Admin, Routing, UI, Emails, Handlers, and Assets.
 *
 * @see README.md for a detailed breakdown of the module's architecture and responsibilities.
 *
 * @package RedCulturalCore
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Red_Cultural_Templates
 */
final class Red_Cultural_Templates {

	/**
	 * Initialize the module and its sub-modules.
	 */
	public static function init(): void {
		self::load_modules();

		// Initialize sub-modules
		RC_Templates_Admin::init();
		RC_Templates_Router::init();
		RC_Templates_UI::init();
		RC_Templates_Assets::init();
		RC_Templates_Mobile_Menu::init();
		RC_Templates_Auth_Modal::init();
		RC_Templates_Emails::init();
		RC_Templates_Handlers::init();
		RC_Templates_Shortcodes::init();

		// Global Filters
		add_filter('the_content', array(__CLASS__, 'strip_welcome_copy_from_content'), 1);
	}

	/**
	 * Load all component classes.
	 */
	private static function load_modules(): void {
		$base_path = plugin_dir_path(__FILE__);

		$modules = array(
			'admin/class-rc-templates-admin.php',
			'routing/class-rc-templates-router.php',
			'ui/class-rc-templates-ui.php',
			'ui/class-rc-templates-assets.php',
			'ui/class-rc-templates-auth-modal.php',
			'ui/class-rc-templates-mobile-menu.php',
			'emails/class-rc-templates-emails.php',
			'handlers/class-rc-templates-handlers.php',
			'shortcodes/class-rc-templates-shortcodes.php',
		);

		foreach ($modules as $module) {
			$file = $base_path . $module;
			if (file_exists($file)) {
				require_once $file;
			}
		}
	}

	/**
	 * Strip welcome copy from content if needed.
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public static function strip_welcome_copy_from_content(string $content): string {
		if (is_admin()) {
			return $content;
		}

		$is_target_page = function_exists('is_page') && is_page(array('nosotros', 'quienes-somos', 'articulos', 'artículos', 'viaje-italia', 'viaje-japon', 'viaje-escandinavia', 'viaje-escocia', 'contacto', 'terminos-y-condiciones', 'terminos', 'condiciones'));

		if (!$is_target_page) {
			return $content;
		}

		$patterns = array(
			'/<!--\s*wp:paragraph\s*-->\s*<p><strong>Bienvenido a Red Cultural.*?<\/p>\s*<!--\s*\/wp:paragraph\s*-->/is',
			'/<p><strong>Bienvenido a Red Cultural.*?<\/p>/is',
		);

		return preg_replace($patterns, '', $content) ?: $content;
	}
}

// Global initialization is usually handled by the main plugin file, 
// but we keep the class definition here.
