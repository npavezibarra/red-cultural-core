<?php
/**
 * Template Router for Red Cultural Templates.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_Router {
	public static function init(): void {
		add_action('template_redirect', array(__CLASS__, 'maybe_render_learndash_course_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_learndash_lesson_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_blog_post_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_woocommerce_shop_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_woocommerce_my_account_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_woocommerce_cart_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_woocommerce_checkout_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_woocommerce_thankyou_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_nosotros_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_articulos_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_viaje_italia_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_viaje_japon_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_viaje_escandinavia_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_viaje_escocia_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_contacto_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_terminos_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_author_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_cuentas_template'), 20);
		add_action('template_redirect', array(__CLASS__, 'maybe_render_reset_password_template'), 20);
		add_filter('template_include', array(__CLASS__, 'maybe_render_404_template'), 100);
	}

	public static function maybe_render_contacto_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_page') || !is_page('contacto')) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_contacto_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/contacto.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_terminos_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		$is_terminos_page = function_exists('is_page') && is_page(array('terminos-y-condiciones', 'terminos', 'condiciones'));

		if (!$is_terminos_page && function_exists('is_404') && is_404()) {
			$path = '';
			if (isset($_SERVER['REQUEST_URI'])) {
				$path = (string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
			}
			$path = trim($path, '/');
			if (in_array($path, array('terminos-y-condiciones', 'terminos', 'condiciones'), true)) {
				$is_terminos_page = true;
			}
		}

		if (!$is_terminos_page) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_terminos_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/terminos-y-condiciones.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_nosotros_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_page') || !is_page(array('nosotros', 'quienes-somos'))) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_nosotros_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/nosotros.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_articulos_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		$is_articulos_page = function_exists('is_page') && is_page(array('articulos', 'artículos'));

		if (!$is_articulos_page && function_exists('is_404') && is_404()) {
			$path = '';
			if (isset($_SERVER['REQUEST_URI'])) {
				$path = (string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
			}
			$path = trim($path, '/');
			if (in_array($path, array('articulos', 'artículos'), true)) {
				$is_articulos_page = true;
			}
		}

		if (!$is_articulos_page) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_articulos_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/articulos.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_blog_post_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!is_singular('post')) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_blog_post_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/blog/post-single.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_viaje_italia_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_page') || !is_page('viaje-italia')) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_viaje_italia_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/viaje-italia.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_viaje_japon_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_page') || !is_page(array('viaje-japon', 'japon'))) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_viaje_japon_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/viaje-japon.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_viaje_escandinavia_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_page') || !is_page(array('viaje-escandinavia', 'escandinavia'))) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_viaje_escandinavia_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/viaje-escandinavia.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_viaje_escocia_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_page') || !is_page(array('viaje-escocia', 'escocia'))) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_viaje_escocia_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/viaje-escocia.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_reset_password_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		$path = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$path = (string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
		}
		$path = trim($path, '/');

		if ($path !== 'restablecer-contrasena') {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/restablecer-contrasena.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_cuentas_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		$is_cuentas_page = function_exists('is_page') && is_page('cuentas');

		if (!$is_cuentas_page && function_exists('is_404') && is_404()) {
			$path = '';
			if (isset($_SERVER['REQUEST_URI'])) {
				$path = (string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
			}
			$path = trim($path, '/');
			if ($path === 'cuentas') {
				$is_cuentas_page = true;
			}
		}

		if (!$is_cuentas_page) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/cuentas.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_author_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!is_author()) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_author_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/author-page.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_learndash_course_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!is_singular('sfwd-courses')) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_learndash_course_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/learndash/course-single.php';
		if (!file_exists($template_file)) {
			return;
		}

		$helpers = RC_CORE_PATH . 'includes/modules/templates/templates/learndash-course.php';
		if (file_exists($helpers)) {
			require_once $helpers;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_learndash_lesson_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!is_singular('sfwd-lessons')) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_learndash_lesson_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/learndash/lesson-single.php';
		if (!file_exists($template_file)) {
			return;
		}

		$helpers = RC_CORE_PATH . 'includes/modules/templates/templates/learndash-course.php';
		if (file_exists($helpers)) {
			require_once $helpers;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_woocommerce_thankyou_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_order_received_page') || !is_order_received_page()) {
			return;
		}

		$template_path = RC_CORE_PATH . 'templates/woocommerce/thankyou.php';
		if (file_exists($template_path)) {
			include $template_path;
			exit;
		}
	}

	public static function maybe_render_woocommerce_checkout_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_checkout') || !is_checkout()) {
			return;
		}

		if (function_exists('is_order_received_page') && is_order_received_page()) {
			return;
		}
		if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_woocommerce_checkout_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/woocommerce/checkout.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_woocommerce_shop_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		$is_shop = function_exists('is_shop') && is_shop();

		if (!$is_shop && function_exists('is_404') && is_404()) {
			$path = '';
			if (isset($_SERVER['REQUEST_URI'])) {
				$path = (string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
			}
			$path = trim($path, '/');
			if ($path === 'tienda') {
				$is_shop = true;
			}
		}

		if (!$is_shop) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_woocommerce_shop_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/woocommerce/shop.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_woocommerce_my_account_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_account_page') || !is_account_page()) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_woocommerce_my_account_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/woocommerce/my-account.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_woocommerce_cart_template(): void {
		if (is_admin() || is_feed()) {
			return;
		}

		if (!function_exists('is_cart') || !is_cart()) {
			return;
		}

		$enabled = (bool) apply_filters('rcp_enable_woocommerce_cart_template', true);
		if (!$enabled) {
			return;
		}

		$template_file = RC_CORE_PATH . 'templates/woocommerce/cart.php';
		if (!file_exists($template_file)) {
			return;
		}

		require $template_file;
		exit;
	}

	public static function maybe_render_404_template(string $template): string {
		if (!is_404() || is_admin() || wp_doing_ajax()) {
			return $template;
		}

		$template_file = RC_CORE_PATH . 'templates/pages/404.php';
		if (file_exists($template_file)) {
			return $template_file;
		}

		return $template;
	}
}
