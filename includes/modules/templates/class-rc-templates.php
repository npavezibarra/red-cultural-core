<?php
/**
 * Plugin Name:       Red Cultural Pages
 * Description:       Create and render WordPress pages from raw HTML/CSS code.
 * Version:           0.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Red Cultural
 * License:           GPLv2 or later
 * Text Domain:       red-cultural-pages
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class Red_Cultural_Templates {
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
		add_filter('template_include', array(__CLASS__, 'maybe_render_404_template'), 100);


		add_action('init', array(__CLASS__, 'register_shortcodes'));
		add_action('init', array(__CLASS__, 'register_checkout_auth_handlers'));
		add_action('init', array(__CLASS__, 'register_viaje_italia_interest_handlers'));
		add_action('init', array(__CLASS__, 'register_viaje_japon_interest_handlers'));
		add_action('init', array(__CLASS__, 'register_viaje_escandinavia_interest_handlers'));
		add_action('init', array(__CLASS__, 'register_contacto_form_handlers'));
		add_action('init', array(__CLASS__, 'register_ajax_handlers'));
		add_action('init', array(__CLASS__, 'register_main_menu_items'));
		add_filter('the_content', array(__CLASS__, 'strip_welcome_copy_from_content'), 9);
		add_action('wp_head', array(__CLASS__, 'maybe_hide_nosotros_post_title'), 30);

		add_action('admin_menu', array(__CLASS__, 'register_admin_pages'));
		add_action('admin_post_rcp_save_shop_settings', array(__CLASS__, 'handle_save_shop_settings'));
	}

		public static function register_admin_pages(): void {
			add_menu_page(
				'Red Cultural',
				'Red Cultural',
				'manage_options',
				'red-cultural-pages',
				array(__CLASS__, 'render_admin_root_page'),
				'dashicons-admin-page',
				58
			);

			add_submenu_page(
				'red-cultural-pages',
				'Tienda',
				'Tienda',
				'manage_options',
				'red-cultural-pages-shop',
				array(__CLASS__, 'render_admin_shop_page')
			);
		}

	public static function render_admin_root_page(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para ver esta página.', 'red-cultural-pages'));
		}

			?>
			<div class="wrap">
				<h1><?php echo esc_html__('Páginas Red Cultural', 'red-cultural-pages'); ?></h1>
				<p><?php echo esc_html__('Configuraciones internas para plantillas y páginas personalizadas.', 'red-cultural-pages'); ?></p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url((string) admin_url('admin.php?page=red-cultural-pages-shop')); ?>">
						<?php echo esc_html__('Abrir configuración de Tienda', 'red-cultural-pages'); ?>
					</a>
				</p>
			</div>
			<?php
		}

	public static function render_admin_shop_page(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para ver esta página.', 'red-cultural-pages'));
		}

		$terms = get_terms(
			array(
				'taxonomy' => 'product_cat',
				'hide_empty' => false,
				'orderby' => 'name',
				'order' => 'ASC',
			)
		);

		$selected = get_option('rcp_shop_category_ids', array());
		$selected = is_array($selected) ? array_values(array_filter(array_map('intval', $selected))) : array();

			$updated = isset($_GET['rcp_updated']) && (string) $_GET['rcp_updated'] === '1';
			?>
			<div class="wrap">
				<h1><?php echo esc_html__('Tienda', 'red-cultural-pages'); ?></h1>

			<?php if ($updated) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__('Guardado.', 'red-cultural-pages'); ?></p></div>
			<?php endif; ?>

				<p><?php echo esc_html__('Selecciona las categorías de productos que quieres mostrar en la página de Tienda.', 'red-cultural-pages'); ?></p>

			<form method="post" action="<?php echo esc_url((string) admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="rcp_save_shop_settings" />
				<?php wp_nonce_field('rcp_save_shop_settings', 'rcp_shop_nonce'); ?>

				<table class="widefat striped" style="max-width:900px">
					<thead>
						<tr>
							<th style="width:50px"><?php echo esc_html__('#', 'red-cultural-pages'); ?></th>
							<th><?php echo esc_html__('Categoría', 'red-cultural-pages'); ?></th>
							<th style="width:120px"><?php echo esc_html__('Seleccionar', 'red-cultural-pages'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (is_wp_error($terms) || !is_array($terms) || $terms === array()) : ?>
							<tr><td colspan="3"><?php echo esc_html__('No se encontraron categorías de productos.', 'red-cultural-pages'); ?></td></tr>
						<?php else : ?>
							<?php foreach ($terms as $i => $term) : ?>
								<?php if (!($term instanceof WP_Term)) { continue; } ?>
								<tr>
									<td><?php echo esc_html((string) ($i + 1)); ?></td>
									<td><?php echo esc_html((string) $term->name); ?></td>
									<td>
										<label>
											<input
												type="checkbox"
												name="rcp_shop_categories[]"
												value="<?php echo esc_attr((string) $term->term_id); ?>"
												<?php checked(in_array((int) $term->term_id, $selected, true)); ?>
											/>
										</label>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<p style="margin-top:16px">
					<button type="submit" class="button button-primary"><?php echo esc_html__('Guardar', 'red-cultural-pages'); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	public static function handle_save_shop_settings(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('No tienes permisos para realizar esta acción.', 'red-cultural-pages'));
		}

		if (!isset($_POST['rcp_shop_nonce']) || !wp_verify_nonce((string) $_POST['rcp_shop_nonce'], 'rcp_save_shop_settings')) {
			wp_die(esc_html__('Nonce inválido.', 'red-cultural-pages'));
		}

		$raw = isset($_POST['rcp_shop_categories']) ? (array) $_POST['rcp_shop_categories'] : array();
		$ids = array();
		foreach ($raw as $value) {
			$ids[] = (int) sanitize_text_field((string) wp_unslash($value));
		}
		$ids = array_values(array_filter(array_unique($ids), static fn($v): bool => is_int($v) && $v > 0));

		update_option('rcp_shop_category_ids', $ids, false);

		wp_safe_redirect((string) admin_url('admin.php?page=red-cultural-pages-shop&rcp_updated=1'));
		exit;
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

		// If the page doesn't exist in WP yet (404), still render the template when the URL matches.
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

		// If the page doesn't exist in WP yet (404), still render the template when the URL matches.
		// This keeps the menu link working without requiring DB edits.
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

	public static function maybe_hide_nosotros_post_title(): void {
		if (is_admin()) {
			return;
		}

		if (!function_exists('is_page') || !is_page(array('nosotros', 'quienes-somos'))) {
			return;
		}

		?>
		<style>
			h1.wp-block-post-title{display:none !important}
		</style>
		<?php
	}

	public static function strip_welcome_copy_from_content(string $content): string {
		if (is_admin() || $content === '' || !is_page()) {
			return $content;
		}

		// Remove the specific onboarding copy when it appears in page content (commonly on Cursos).
		$needles = array(
			'Bienvenido a la Plataforma',
			'Explora nuestros cursos y artículos exclusivos para miembros.',
		);

		$updated = str_replace($needles, '', $content);

		// Clean up common empty paragraphs left behind.
		$updated = preg_replace('/<p>\\s*<\\/p>/i', '', (string) $updated);
		$updated = preg_replace('/<p>\\s*(?:&nbsp;)?\\s*<\\/p>/i', '', (string) $updated);

		return is_string($updated) ? $updated : $content;
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

	public static function register_main_menu_items(): void {
		if (is_admin()) {
			return;
		}

		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_main_nav_assets'));
		// add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_myaccount_comunas_autocomplete'), 20); // Handled by Shipping module now
		add_action('wp_head', array(__CLASS__, 'render_main_nav_styles'), 20);
		add_filter('render_block', array(__CLASS__, 'maybe_remove_woocommerce_header_blocks'), 9, 2);
		add_filter('render_block', array(__CLASS__, 'inject_block_navigation_items'), 10, 2);
		add_filter('wp_nav_menu_items', array(__CLASS__, 'inject_classic_menu_items'), 10, 2);
		add_filter('wp_nav_menu_objects', array(__CLASS__, 'rewrite_my_account_submenu'), 10, 2);
		add_action('wp_footer', array(__CLASS__, 'render_main_nav_script'), 5);
		add_action('wp_footer', array(__CLASS__, 'render_site_auth_modal'), 20);
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_404_assets'), 20);
	}

	public static function maybe_remove_woocommerce_header_blocks(string $block_content, array $block): string {
		if (is_admin() || $block_content === '' || empty($block['blockName'])) {
			return $block_content;
		}

		// Remove WooCommerce Blocks header icons (account + mini-cart). We render cart/account in our own nav.
		if ($block['blockName'] === 'woocommerce/customer-account' || $block['blockName'] === 'woocommerce/mini-cart') {
			return '';
		}

		return $block_content;
	}

	public static function render_main_nav_script(): void {
		if (is_admin()) {
			return;
		}

		?>
		<script>
			(function () {
				function closeAll() {
					document.querySelectorAll('[data-rcp-account-dropdown]').forEach(function (el) {
						el.classList.remove('is-open');
					});
					document.querySelectorAll('.rcp-account-arrow').forEach(function (el) {
						el.classList.remove('is-open');
					});
				}

				document.addEventListener('click', function (e) {
					var t = e.target;
					if (!(t instanceof Element)) return;

					var toggle = t.closest('[data-rcp-account-toggle]');
					if (!toggle) {
						if (!t.closest('[data-rcp-account-dropdown]')) closeAll();
						return;
					}

					e.preventDefault();
					var li = toggle.closest('.rcp-account-wrap');
					if (!li) return;

					var dropdown = li.querySelector('[data-rcp-account-dropdown]');
					var arrow = li.querySelector('.rcp-account-arrow');
					if (!dropdown) return;

					var willOpen = !dropdown.classList.contains('is-open');
					closeAll();
					dropdown.classList.toggle('is-open', willOpen);
					if (arrow) arrow.classList.toggle('is-open', willOpen);
				});
			})();
		</script>
		<?php
	}

	public static function enqueue_main_nav_assets(): void {
		wp_enqueue_style(
			'rcp-material-symbols',
			'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200',
			array(),
			null
		);

		if (!is_user_logged_in()) {
			if (!wp_script_is('rcp-tailwind', 'enqueued') && !wp_script_is('rcp-tailwind', 'done')) {
				wp_enqueue_script('rcp-tailwind', 'https://cdn.tailwindcss.com', array(), null, false);
			}
			wp_enqueue_style('rcp-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap', array(), null);
		}
	}

	public static function render_main_nav_styles(): void {
		if (is_admin()) {
			return;
		}

		?>
		<style>
			/* Remove WooCommerce header icons (we render cart/account in our injected nav). */
			.wp-block-woocommerce-customer-account,
			.wp-block-woocommerce-mini-cart{display:none !important}

			/* Never show focus/click borders on nav items or hamburger/close buttons. */
			nav.wp-block-navigation .wp-block-navigation-item__content:focus,
			nav.wp-block-navigation .wp-block-navigation-item__content:focus-visible,
			nav.wp-block-navigation .wp-block-navigation-item__content:active,
			nav.wp-block-navigation .wp-block-navigation__responsive-container-open:focus,
			nav.wp-block-navigation .wp-block-navigation__responsive-container-open:focus-visible,
			nav.wp-block-navigation .wp-block-navigation__responsive-container-close:focus,
			nav.wp-block-navigation .wp-block-navigation__responsive-container-close:focus-visible,
			nav.wp-block-navigation button:focus,
			nav.wp-block-navigation button:focus-visible{
				outline:0 !important;
				box-shadow:none !important;
			}

			/* Hard-disable any border/outline/box-shadow states on nav elements (hover/focus/active). */
			nav.wp-block-navigation a,
			nav.wp-block-navigation button{
				outline:0 !important;
				box-shadow:none !important;
				-webkit-tap-highlight-color:transparent;
			}
			nav.wp-block-navigation a:hover,
			nav.wp-block-navigation a:focus,
			nav.wp-block-navigation a:focus-visible,
			nav.wp-block-navigation a:active,
			nav.wp-block-navigation button:hover,
			nav.wp-block-navigation button:focus,
			nav.wp-block-navigation button:focus-visible,
			nav.wp-block-navigation button:active{
				outline:0 !important;
				box-shadow:none !important;
				border-color:transparent !important;
			}

			/* Also remove focus outline from the header logo/site title link. */
			header .wp-block-site-title a,
			header a.custom-logo-link{
				outline:0 !important;
				box-shadow:none !important;
				-webkit-tap-highlight-color:transparent;
			}
			header .wp-block-site-title a:focus,
			header .wp-block-site-title a:focus-visible,
			header .wp-block-site-title a:active,
			header a.custom-logo-link:focus,
			header a.custom-logo-link:focus-visible,
			header a.custom-logo-link:active{
				outline:0 !important;
				box-shadow:none !important;
			}

			/* Avoid theme-provided underline so only our custom underline shows. */
			nav.wp-block-navigation .wp-block-navigation-item__content,
			nav.wp-block-navigation .wp-block-navigation-item__content:hover{
				text-decoration:none !important;
				background-image:none !important;
			}

			span.wp-block-navigation-item__label{letter-spacing:2px;font-weight:700;font-size:12px;text-transform:uppercase}

			.material-symbols-outlined{
				font-family:'Material Symbols Outlined' !important;
				font-weight:normal;
				font-style:normal;
				font-size:18px;
				line-height:1;
				letter-spacing:normal;
				text-transform:none;
				display:inline-block;
				white-space:nowrap;
				word-wrap:normal;
				direction:ltr;
				-webkit-font-feature-settings:'liga';
				-webkit-font-smoothing:antialiased;
				font-variation-settings:'FILL' 0,'wght' 500,'GRAD' 0,'opsz' 24;
			}

			.rcp-nav-link{position:relative;display:inline-flex;align-items:center;transition:color .3s ease}
			.rcp-nav-link::after{content:'';position:absolute;width:0;height:2px;bottom:-4px;left:0;background-color:#000;transition:width .3s ease}
			.rcp-nav-link:hover::after{width:100%}
			li.wp-block-navigation-item.rcp-nav-auth{border:1px solid black;border-radius:6px}

			.rcp-btn-auth{display:inline-flex;align-items:center;justify-content:center;gap:5px;min-width:110px;padding:6px 18px;background:#ffffff;color:#000000 !important;border-radius:6px;border:2px solid #000;transition:all .2s ease;text-decoration:none}
			.rcp-btn-auth:hover{background:#f9f9f9}
			.rcp-btn-auth--login{background:#000 !important;color:#fff !important;border-radius:6px}
			.rcp-btn-auth--login:hover{background:#333 !important}
			.rcp-btn-auth--logout{border:0 !important}

			.rcp-cart-link{display:inline-flex;align-items:center;justify-content:center;color:#000 !important;text-decoration:none;position:relative}
			.rcp-cart-link:hover{opacity:.75}
			.rcp-cart-text{display:none;align-items:center;gap:10px}
			.rcp-cart-badge{position:absolute;top:-6px;right:-14px;background:#fc5252;color:#fff;border-radius:982px;min-width:20px;height:23px;padding:0px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;line-height:1}

			.rcp-account-wrap{position:relative}
			.rcp-account-toggle{display:inline-flex;align-items:center;gap:6px;background:transparent;border:0;outline:0;box-shadow:none;padding:0;cursor:pointer}
			.rcp-account-toggle:focus,
			.rcp-account-toggle:focus-visible{outline:0 !important;box-shadow:none !important}
			.rcp-account-toggle .wp-block-navigation-item__label{position:relative;top:2px}

			@media (min-width: 768px){
				.wp-block-navigation__container{column-gap:36px !important}
				/* Push cart/auth to the far right when present. */
				.rcp-nav-right{margin-left:auto !important}
			}

			/* Mobile nav (<=1080px): hamburger + right-side drawer */
			@media (max-width: 1080px){
				/* Kill any focus/active borders/outlines in the drawer + hamburger */
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-close,
				nav.wp-block-navigation .wp-block-navigation-item__content,
				nav.wp-block-navigation .wp-block-navigation-item__content:focus,
				nav.wp-block-navigation .wp-block-navigation-item__content:focus-visible,
				nav.wp-block-navigation .wp-block-navigation-item__content:active,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open:focus,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open:focus-visible,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-close:focus,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-close:focus-visible{
					outline:0 !important;
					box-shadow:none !important;
					border:0 !important;
				}

				nav.wp-block-navigation .wp-block-navigation-item__content::after{display:none !important}

				nav.wp-block-navigation .wp-block-navigation__container{display:none !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open{display:inline-flex !important;align-items:center;justify-content:center}

				nav.wp-block-navigation .wp-block-navigation__responsive-container{
					position:fixed !important;
					inset:0 !important;
					background:rgba(0,0,0,.60) !important;
					backdrop-filter:blur(6px);
					opacity:0;
					pointer-events:none;
					transition:opacity .35s cubic-bezier(.22,1,.36,1);
					will-change:opacity;
				}
				nav.wp-block-navigation .wp-block-navigation__responsive-container.is-menu-open,
				nav.wp-block-navigation .wp-block-navigation__responsive-container.has-modal-open{
					opacity:1;
					pointer-events:auto;
				}

				nav.wp-block-navigation .wp-block-navigation__responsive-close{display:block !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-dialog{
					position:absolute;
					top:0;
					left:0;
					height:100%;
					width:60vw;
					max-width:520px;
					background:#fff;
					box-shadow:20px 0 60px rgba(0,0,0,.18);
					transform:translate3d(-102%, 0, 0);
					transition:transform .55s cubic-bezier(.22,1,.36,1);
					will-change:transform;
					padding:18px 16px;
					overflow:auto;
				}
				nav.wp-block-navigation .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__responsive-dialog,
				nav.wp-block-navigation .wp-block-navigation__responsive-container.has-modal-open .wp-block-navigation__responsive-dialog{
					transform:translate3d(0,0,0);
				}

				nav.wp-block-navigation .wp-block-navigation__responsive-container-content{padding-top:10px}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation__container{
					display:flex !important;
					flex-direction:column;
					gap:16px;
					align-items:flex-start;
					justify-content:flex-start;
					text-align:left !important;
					width:100% !important;
					margin:0 !important;
					padding:0 !important;
				}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation-item{
					width:100% !important;
				}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation-item__content,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation-item__content *{
					text-align:left !important;
					justify-content:flex-start !important;
				}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation-item__content{
					width:100% !important;
				}

				/* Ensure our custom items also align left inside the drawer */
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .rcp-account-toggle{
					width:100% !important;
					justify-content:space-between !important;
				}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .rcp-btn-auth{
					width:100% !important;
					justify-content:flex-start !important;
				}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .rcp-btn-auth--logout{
					min-width:0 !important;
					padding:0 !important;
					border:0 !important;
					background:transparent !important;
				}

				/* Cart in drawer: show text + count, hide icon, make badge inline */
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .rcp-nav-right{margin-left:0 !important}
				.rcp-cart-link{width:100% !important;justify-content:flex-start !important;gap:10px}
				.rcp-cart-text{display:inline-flex}
				.rcp-cart-link .material-symbols-outlined{display:none !important}
				.rcp-cart-badge{position:static;top:auto;right:auto;min-width:24px;margin-left:0}
			}

			@media (min-width: 1081px){
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open{display:none !important}
			}
			.rcp-account-arrow{transition:transform .2s ease}
			.rcp-account-arrow.is-open{transform:rotate(180deg)}
			.rcp-dropdown-content{display:none;position:absolute;top:100%;right:0;background:#fff;min-width:220px;border:1px solid #000;z-index:60;margin-top:.5rem;box-shadow:0 4px 6px -1px rgb(0 0 0 / 0.1)}
			.rcp-dropdown-content.is-open{display:block}
			.rcp-dropdown-item{padding:12px 16px;display:block;border-bottom:1px solid #f0f0f0;transition:background-color .2s}
			.rcp-dropdown-item:last-child{border-bottom:none}
			.rcp-dropdown-item:hover{background-color:#f9f9f9}

			/* Footer link columns (override theme footer nav placeholders) */
			.rcp-footer-links{width:100%}
			.rcp-footer-links__inner{display:flex;gap:72px;flex-wrap:wrap;justify-content:flex-end;align-items:flex-start}
			.rcp-footer-links__col{display:flex;flex-direction:column;gap:14px;min-width:180px}
			.rcp-footer-links__link{
				color:#fff !important;
				text-transform:uppercase;
				letter-spacing:2px;
				font-weight:700;
				font-size:12px;
				text-decoration:none !important;
				border:0 !important;
				outline:0 !important;
				box-shadow:none !important;
				background:transparent !important;
			}
			.rcp-footer-links__link:hover,
			.rcp-footer-links__link:focus,
			.rcp-footer-links__link:focus-visible,
			.rcp-footer-links__link:active{
				opacity:.75;
				border:0 !important;
				outline:0 !important;
				box-shadow:none !important;
				background:transparent !important;
			}
			@media (max-width: 900px){
				.rcp-footer-links__inner{justify-content:flex-start;gap:34px}
				.rcp-footer-links__col{min-width:0}
			}
			</style>
		<?php
	}

	public static function enqueue_myaccount_comunas_autocomplete(): void {
		if (!function_exists('is_account_page') || !is_account_page()) {
			return;
		}

		$woo_check_main = WP_PLUGIN_DIR . '/woo-check-new/woo-check.php';
		if (file_exists($woo_check_main)) {
			$plugin_url = plugin_dir_url($woo_check_main);
			if (!wp_script_is('woo-check-comunas-chile', 'registered')) {
				wp_register_script(
					'woo-check-comunas-chile',
					RC_CORE_URL . "includes/modules/templates/" . 'comunas-chile.js',
					array(),
					'1.0',
					true
				);
			}
			if (!wp_script_is('woo-check-autocomplete', 'registered')) {
				wp_register_script(
					'woo-check-autocomplete',
					RC_CORE_URL . "includes/modules/templates/" . 'woo-check-autocomplete.js',
					array('jquery', 'jquery-ui-autocomplete', 'woo-check-comunas-chile'),
					'1.0',
					true
				);
			}
			if (!wp_style_is('woo-check-style', 'registered')) {
				wp_register_style(
					'woo-check-style',
					RC_CORE_URL . "includes/modules/templates/" . 'woo-check-style.css',
					array(),
					'1.0'
				);
			}
		}

		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_script('woo-check-comunas-chile');
		wp_enqueue_script('woo-check-autocomplete');
		wp_enqueue_style('woo-check-style');
	}

	public static function rewrite_my_account_submenu(array $items, \stdClass $args): array {
		if (is_admin() || empty($args->theme_location) || $args->theme_location !== 'header-my-account') {
			return $items;
		}

		$myaccount_url = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('myaccount') : (string) home_url('/my-account/');
		if ($myaccount_url === '') {
			return $items;
		}

		foreach ($items as $item) {
			$title = isset($item->title) ? trim((string) $item->title) : '';
			if ($title === '') {
				continue;
			}
			$normalized = strtolower($title);
			if ($normalized === 'mis cursos' || $normalized === 'mis cursos ') {
				$item->url = add_query_arg('tab', 'cursos', $myaccount_url);
			}
		}

		return $items;
	}

	private static function get_main_nav_links(): array {
		// Base menu order differs for logged out vs logged in.
		if (is_user_logged_in()) {
			$links = array(
				array(
					'type' => 'link',
					'label' => 'NOSOTROS',
					'url' => self::get_nosotros_url(),
					'key' => 'nosotros',
				),
				array(
					'type' => 'link',
					'label' => 'CURSOS',
					'url' => (string) home_url('/cursos/'),
					'key' => 'cursos',
				),
				array(
					'type' => 'link',
					'label' => 'CONTACTO',
					'url' => (string) home_url('/contacto/'),
					'key' => 'contacto',
				),
				array(
					'type' => 'link',
					'label' => 'ARTÍCULOS',
					'url' => (string) home_url('/articulos/'),
					'key' => 'articulos',
				),
			);
		} else {
			$links = array(
				array(
					'type' => 'link',
					'label' => 'CURSOS',
					'url' => (string) home_url('/cursos/'),
					'key' => 'cursos',
				),
				array(
					'type' => 'link',
					'label' => 'ARTÍCULOS',
					'url' => (string) home_url('/articulos/'),
					'key' => 'articulos',
				),
				array(
					'type' => 'link',
					'label' => 'CONTACTO',
					'url' => (string) home_url('/contacto/'),
					'key' => 'contacto',
				),
				array(
					'type' => 'link',
					'label' => 'NOSOTROS',
					'url' => self::get_nosotros_url(),
					'key' => 'nosotros',
				),
			);
		}

		if (is_user_logged_in()) {
			$links[] = array(
				'type' => 'account',
				'label' => self::get_user_first_name_label(),
				'key' => 'account',
				'children' => self::get_account_dropdown_links(),
			);
			$links[] = array(
				'type' => 'cart',
				'label' => 'BOLSA DE COMPRA',
				'url' => self::get_cart_url(),
				'key' => 'cart',
				'icon' => 'shopping_bag',
			);
			$links[] = array(
				'type' => 'auth',
				'label' => 'CERRAR SESIÓN',
				'url' => wp_logout_url((string) home_url('/')),
				'key' => 'auth',
				'icon' => 'logout',
			);
		} else {
			$links[] = array(
				'type' => 'auth',
				'label' => 'INICIAR SESIÓN',
				'url' => '#rcp-auth',
				'key' => 'auth',
			);
		}

		/**
		 * Filter the plugin-provided main nav links.
		 *
		 * @param array $links Array of {label,url,key}.
		 */
		return (array) apply_filters('rcp_main_nav_links', $links);
	}

	private static function get_nosotros_url(): string {
		// Prefer an actual page permalink if it exists (local: /nosotros/, live: /quienes-somos/).
		$nosotros = get_page_by_path('nosotros');
		if ($nosotros && isset($nosotros->ID)) {
			$url = get_permalink((int) $nosotros->ID);
			if (is_string($url) && $url !== '') {
				return (string) $url;
			}
		}

		$quienes = get_page_by_path('quienes-somos');
		if ($quienes && isset($quienes->ID)) {
			$url = get_permalink((int) $quienes->ID);
			if (is_string($url) && $url !== '') {
				return (string) $url;
			}
		}

		return (string) home_url('/nosotros/');
	}

	private static function get_user_first_name_label(): string {
		if (!function_exists('wp_get_current_user')) {
			return 'MI CUENTA';
		}

		$user = wp_get_current_user();
		if (!$user || !isset($user->ID) || (int) $user->ID <= 0) {
			return 'MI CUENTA';
		}

		$first_name = isset($user->first_name) ? trim((string) $user->first_name) : '';
		if ($first_name !== '') {
			return $first_name;
		}

		$display = isset($user->display_name) ? trim((string) $user->display_name) : '';
		if ($display !== '') {
			$parts = preg_split('/\\s+/', $display);
			if (is_array($parts) && isset($parts[0]) && trim((string) $parts[0]) !== '') {
				return trim((string) $parts[0]);
			}
		}

		return 'MI CUENTA';
	}

	private static function get_cart_url(): string {
		if (function_exists('wc_get_cart_url')) {
			$url = (string) wc_get_cart_url();
			if ($url !== '') {
				return $url;
			}
		}

		return (string) home_url('/cart/');
	}

	private static function get_cart_count(): int {
		if (!function_exists('WC')) {
			return 0;
		}

		$wc = WC();
		if (!$wc || !isset($wc->cart) || !$wc->cart) {
			return 0;
		}
		if (!method_exists($wc->cart, 'get_cart_contents_count')) {
			return 0;
		}

		return (int) $wc->cart->get_cart_contents_count();
	}

	private static function get_account_dropdown_links(): array {
		$links = array();

		$cursos_url = self::get_my_account_tab_url('cursos');
		if ($cursos_url !== '') {
			$links[] = array(
				'label' => 'MIS CURSOS',
				'url' => $cursos_url,
				'key' => 'my-courses',
			);
		}

		if (function_exists('wc_get_page_permalink') && function_exists('wc_get_endpoint_url')) {
			$myaccount = (string) wc_get_page_permalink('myaccount');
			if ($myaccount !== '') {
				$links[] = array(
					'label' => 'MIS COMPRAS',
					'url' => (string) wc_get_endpoint_url('orders', '', $myaccount),
					'key' => 'orders',
				);
				$links[] = array(
					'label' => 'DETALLES DE MI CUENTA',
					'url' => (string) wc_get_endpoint_url('edit-account', '', $myaccount),
					'key' => 'edit-account',
				);
			}
		}

		return (array) apply_filters('rcp_account_dropdown_links', $links);
	}

	private static function get_my_account_tab_url(string $tab): string {
		if (!function_exists('wc_get_page_permalink')) {
			return '';
		}

		$myaccount = (string) wc_get_page_permalink('myaccount');
		if ($myaccount === '') {
			return '';
		}

		return (string) add_query_arg('tab', $tab, $myaccount);
	}

	public static function inject_classic_menu_items(string $items, $args): string {
		if (is_admin() || !is_object($args)) {
			return $items;
		}

		$force = (bool) apply_filters('rcp_force_main_menu_links', true);

		$locations = (array) apply_filters(
			'rcp_main_menu_locations',
			array('primary', 'menu-1', 'header', 'main', 'top', 'primary_navigation')
		);

		$theme_location = isset($args->theme_location) ? (string) $args->theme_location : '';
		if ($theme_location === '' || !in_array($theme_location, $locations, true)) {
			return $items;
		}

		$links = self::get_main_nav_links();
		$built = '';
		foreach ($links as $link) {
			$type = isset($link['type']) ? (string) $link['type'] : 'link';
			$url = isset($link['url']) ? (string) $link['url'] : '';
			$label = isset($link['label']) ? (string) $link['label'] : '';
			$key = isset($link['key']) ? (string) $link['key'] : '';
			$icon = isset($link['icon']) ? (string) $link['icon'] : '';
			if ($label === '' || $key === '') {
				continue;
			}

			if (strpos($items, 'rcp-nav-' . $key) !== false || strpos($built, 'rcp-nav-' . $key) !== false) {
				continue;
			}

			// Classic menus: keep it simple (no dropdown).
			if ($type === 'account') {
				continue;
			}

			$attrs = '';
			$label_html = esc_html($label);
			if ($type === 'cart') {
				$attrs .= ' class="rcp-cart-link"';
				$icon_name = $icon !== '' ? $icon : 'shopping_bag';
				$count = self::get_cart_count();
				$badge = $count > 0 ? ('<span class="rcp-cart-badge">' . esc_html((string) $count) . '</span>') : '';
				$label_html = '<span class="material-symbols-outlined" aria-hidden="true">' . esc_html($icon_name) . '</span>' . $badge;
			} elseif ($type === 'auth') {
				if (is_user_logged_in()) {
					$attrs .= ' class="rcp-btn-auth rcp-btn-auth--logout"';
					$label_html = '<span class="wp-block-navigation-item__label">' . esc_html($label) . '</span><span class="material-symbols-outlined" aria-hidden="true">logout</span>';
				} else {
					$attrs .= ' class="rcp-btn-auth rcp-btn-auth--login" data-rcp-auth-open="1"';
					$url = '#rcp-auth';
					$label_html = '<span class="wp-block-navigation-item__label">' . esc_html($label) . '</span>';
				}
			} else {
				$label_html = '<span class="wp-block-navigation-item__label">' . esc_html($label) . '</span>';
				$attrs .= ' class="rcp-nav-link"';
			}

			if ($url === '') {
				$url = '#';
			}

			$built .= sprintf(
				'<li class="menu-item rcp-nav-%1$s"><a href="%2$s"%4$s>%3$s</a></li>',
				esc_attr($key),
				esc_url($url),
				$label_html,
				$attrs
			);
		}

		if ($built === '') {
			return $items;
		}

		return $force ? $built : ($items . $built);
	}

	public static function inject_block_navigation_items(string $block_content, array $block): string {
		if (is_admin() || $block_content === '' || empty($block['blockName'])) {
			return $block_content;
		}

		if ($block['blockName'] !== 'core/navigation') {
			return $block_content;
		}

		if (self::is_footer_navigation_block($block)) {
			return self::inject_footer_navigation_items($block_content);
		}

		// If there is no navigation container, avoid touching the markup.
		if (strpos($block_content, 'wp-block-navigation__container') === false) {
			return $block_content;
		}

		$links = self::get_main_nav_links();
		$inserts = '';
		foreach ($links as $link) {
			$type = isset($link['type']) ? (string) $link['type'] : 'link';
			$label = isset($link['label']) ? (string) $link['label'] : '';
			$key = isset($link['key']) ? (string) $link['key'] : '';
			$icon = isset($link['icon']) ? (string) $link['icon'] : '';
			if ($label === '' || $key === '') {
				continue;
			}
			if (strpos($block_content, 'rcp-nav-' . $key) !== false) {
				continue;
			}

			if ($type === 'account') {
				$children = isset($link['children']) && is_array($link['children']) ? (array) $link['children'] : array();
				$children_html = '';
				foreach ($children as $child) {
					$c_label = isset($child['label']) ? (string) $child['label'] : '';
					$c_url = isset($child['url']) ? (string) $child['url'] : '';
					if ($c_label === '' || $c_url === '') {
						continue;
					}
					$children_html .= sprintf(
						'<a class="rcp-dropdown-item" href="%1$s"><span class="wp-block-navigation-item__label" style="font-size:10px">%2$s</span></a>',
						esc_url($c_url),
						esc_html($c_label)
					);
				}

				if ($children_html === '') {
					continue;
				}

				$inserts .= sprintf(
					'<li class="wp-block-navigation-item rcp-nav-%1$s rcp-account-wrap"><button type="button" class="rcp-account-toggle" data-rcp-account-toggle="1"><span class="wp-block-navigation-item__label">%2$s</span><span class="material-symbols-outlined rcp-account-arrow" aria-hidden="true">expand_more</span></button><div class="rcp-dropdown-content" data-rcp-account-dropdown="1">%3$s</div></li>',
					esc_attr($key),
					esc_html($label),
					$children_html
				);
				continue;
			}

			if ($type === 'auth') {
				if (is_user_logged_in()) {
					$url = isset($link['url']) ? (string) $link['url'] : '';
					if ($url === '') {
						$url = (string) wp_logout_url((string) home_url('/'));
					}
					$inserts .= sprintf(
						'<li class="wp-block-navigation-item rcp-nav-%1$s"><a class="rcp-btn-auth rcp-btn-auth--logout" href="%2$s"><span class="wp-block-navigation-item__label">%3$s</span><span class="material-symbols-outlined" aria-hidden="true">logout</span></a></li>',
						esc_attr($key),
						esc_url($url),
						esc_html($label)
					);
				} else {
					$inserts .= sprintf(
						'<li class="wp-block-navigation-item rcp-nav-%1$s rcp-nav-right"><button type="button" class="rcp-btn-auth rcp-btn-auth--login" data-rcp-auth-open="1"><span class="wp-block-navigation-item__label">%2$s</span></button></li>',
						esc_attr($key),
						esc_html($label)
					);
				}
				continue;
			}

			if ($type === 'cart') {
				$url = isset($link['url']) ? (string) $link['url'] : self::get_cart_url();
				$icon_name = $icon !== '' ? $icon : 'shopping_bag';
				$count = self::get_cart_count();
				$badge = $count > 0 ? ('<span class="rcp-cart-badge">' . esc_html((string) $count) . '</span>') : '';
				$text = '<span class="rcp-cart-text"><span class="wp-block-navigation-item__label">' . esc_html($label) . '</span></span>';
				$inserts .= sprintf(
					'<li class="wp-block-navigation-item wp-block-navigation-link rcp-nav-%1$s rcp-nav-right"><a class="rcp-cart-link" href="%2$s" aria-label="%3$s"><span class="material-symbols-outlined" aria-hidden="true">%4$s</span>%5$s%6$s</a></li>',
					esc_attr($key),
					esc_url($url),
					esc_attr($label),
					esc_html($icon_name),
					$text,
					$badge
				);
				continue;
			}

			$url = isset($link['url']) ? (string) $link['url'] : '';
			if ($url === '') {
				continue;
			}

			$inserts .= sprintf(
				'<li class="wp-block-navigation-item wp-block-navigation-link rcp-nav-%1$s"><a class="wp-block-navigation-item__content rcp-nav-link" href="%2$s"><span class="wp-block-navigation-item__label">%3$s</span></a></li>',
				esc_attr($key),
				esc_url($url),
				esc_html($label)
			);
		}

		if ($inserts === '') {
			return $block_content;
		}

		$force = (bool) apply_filters('rcp_force_main_menu_links', true);

		// Prefer DOM parsing so we append to the correct <ul> even when submenus exist.
		if (class_exists('DOMDocument')) {
			$prev = libxml_use_internal_errors(true);
			$dom = new \DOMDocument('1.0', 'UTF-8');
			$wrapped = '<div id="rcp-wrap">' . $block_content . '</div>';
			$loaded = $dom->loadHTML(
				'<!doctype html><html><head><meta charset="utf-8"></head><body>' . $wrapped . '</body></html>',
				LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
			);
			libxml_clear_errors();
			libxml_use_internal_errors($prev);

			if ($loaded) {
				$xpath = new \DOMXPath($dom);
				$ul = $xpath->query('//ul[contains(concat(" ", normalize-space(@class), " "), " wp-block-navigation__container ")]')->item(0);
				if ($ul instanceof \DOMElement) {
					$frag = $dom->createDocumentFragment();
					// appendXML expects valid markup; our inserts are <li>...</li> nodes.
					if (@$frag->appendXML($inserts)) {
						if ($force) {
							while ($ul->firstChild) {
								$ul->removeChild($ul->firstChild);
							}
						}

						$ul->appendChild($frag);
						$wrap_node = $xpath->query('//*[@id="rcp-wrap"]')->item(0);
						if ($wrap_node instanceof \DOMElement) {
							$html = '';
							foreach ($wrap_node->childNodes as $child) {
								$html .= $dom->saveHTML($child);
							}
							if ($html !== '') {
								return $html;
							}
						}
					}
				}
			}
		}

		// Fallback: replace (or append to) the navigation container <ul>.
		$replacement = $force ? '$1' . $inserts . '$3' : '$1$2' . $inserts . '$3';
		$updated = preg_replace(
			'/(<ul[^>]*class="[^"]*wp-block-navigation__container[^"]*"[^>]*>)(.*?)(<\/ul>)/s',
			$replacement,
			$block_content,
			1
		);

		return is_string($updated) && $updated !== '' ? $updated : $block_content;
	}

	private static function is_footer_navigation_block(array $block): bool {
		if (empty($block['attrs']) || !is_array($block['attrs'])) {
			return false;
		}

		$attrs = (array) $block['attrs'];
		$layout = isset($attrs['layout']) && is_array($attrs['layout']) ? (array) $attrs['layout'] : array();
		$orientation = isset($layout['orientation']) ? (string) $layout['orientation'] : '';
		$overlay_menu = isset($attrs['overlayMenu']) ? (string) $attrs['overlayMenu'] : '';

		// The theme footer uses vertical navigation blocks (overlayMenu="never").
		return $orientation === 'vertical' && ($overlay_menu === '' || $overlay_menu === 'never');
	}

	private static function get_footer_menu_columns(): array {
		$shop_url = (string) home_url('/tienda/');

		$columns = array(
			array(
				array(
					'label' => 'NOSOTROS',
					'url' => self::get_nosotros_url(),
					'key' => 'nosotros',
				),
				array(
					'label' => 'ARTÍCULOS',
					'url' => (string) home_url('/articulos/'),
					'key' => 'articulos',
				),
				array(
					'label' => 'CURSOS',
					'url' => (string) home_url('/cursos/'),
					'key' => 'cursos',
				),
				array(
					'label' => 'CONTACTO',
					'url' => (string) home_url('/contacto/'),
					'key' => 'contacto',
				),
			),
			array(
				array(
					'label' => 'FACEBOOK',
					'url' => 'https://www.facebook.com/FRedCultural?_rdc=1&_rdr#',
					'key' => 'facebook',
					'new_tab' => true,
				),
				array(
					'label' => 'YOUTUBE',
					'url' => 'https://www.youtube.com/channel/UCzcXD00Q645grTnWrmInGOg',
					'key' => 'youtube',
					'new_tab' => true,
				),
				array(
					'label' => 'INSTAGRAM',
					'url' => 'https://www.instagram.com/red.cultural/',
					'key' => 'instagram',
					'new_tab' => true,
				),
				array(
					'label' => 'TWITTER',
					'url' => 'https://x.com/red__cultural',
					'key' => 'twitter',
					'new_tab' => true,
				),
			),
			array(
				array(
					'label' => 'TIENDA',
					'url' => $shop_url,
					'key' => 'tienda',
				),
				array(
					'label' => 'TÉRMINOS Y CONDICIONES',
					'url' => (string) home_url('/terminos-y-condiciones/'),
					'key' => 'terminos',
				),
			),
		);

		/**
		 * Filter footer link columns.
		 *
		 * @param array $columns Array of columns, each a list of {label,url,key}.
		 */
		return (array) apply_filters('rcp_footer_menu_columns', $columns);
	}

	private static function is_internal_url(string $url): bool {
		$url = trim($url);
		if ($url === '' || strpos($url, '#') === 0 || strpos($url, '/') === 0) {
			return true;
		}

		$site_host = (string) parse_url((string) home_url('/'), PHP_URL_HOST);
		$url_host = (string) parse_url($url, PHP_URL_HOST);
		if ($site_host === '' || $url_host === '') {
			return false;
		}

		return strtolower($site_host) === strtolower($url_host);
	}

	private static function inject_footer_navigation_items(string $block_content): string {
		static $rendered = false;
		if ($rendered) {
			return '';
		}
		$rendered = true;

		$columns = self::get_footer_menu_columns();
		if (!is_array($columns) || $columns === array()) {
			return $block_content;
		}

			$html = '<nav class="rcp-footer-links" aria-label="' . esc_attr__('Pie de página', 'red-cultural-pages') . '"><div class="rcp-footer-links__inner">';
		foreach ($columns as $col_index => $links) {
			if (!is_array($links) || $links === array()) {
				continue;
			}
			$html .= '<div class="rcp-footer-links__col" data-col="' . esc_attr((string) $col_index) . '">';
			foreach ($links as $link) {
				$label = isset($link['label']) ? (string) $link['label'] : '';
				$url = isset($link['url']) ? (string) $link['url'] : '';
				$new_tab = isset($link['new_tab']) ? (bool) $link['new_tab'] : false;
				if ($label === '' || $url === '') {
					continue;
				}
				$is_external = !$new_tab && self::is_internal_url($url) ? false : (preg_match('#^https?://#i', $url) === 1 && !self::is_internal_url($url));
				$open_new_tab = $new_tab || $is_external;
				$attrs = $open_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
				$html .= '<a class="rcp-footer-links__link" href="' . esc_url($url) . '"' . $attrs . '>' . esc_html($label) . '</a>';
			}
			$html .= '</div>';
		}
		$html .= '</div></nav>';

		return $html;
	}

	public static function render_site_auth_modal(): void {
		if (is_admin() || is_user_logged_in()) {
			return;
		}

			$admin_post = admin_url('admin-post.php');
			$auth_nonce = wp_create_nonce('rcp_checkout_auth');
			$exists_nonce = wp_create_nonce('rcp_user_exists');
			$redirect_to = (string) home_url(add_query_arg(array(), (string) wp_unslash($_SERVER['REQUEST_URI'] ?? '/')));

		?>
			<style>
				/* Auth modal model (Tailwind-based) */
				#red-cultural-login-overlay{font-family:'Inter',sans-serif}
				#red-cultural-login-overlay .auth-card{transition:all .3s cubic-bezier(.4,0,.2,1);border-radius:9px !important}
				#red-cultural-login-overlay .focus-gold:focus{border-color:#c5a367 !important;box-shadow:0 0 0 2px rgba(197,163,103,.15) !important}
				#red-cultural-login-overlay .rounded-3px{border-radius:3px !important}
				#red-cultural-login-overlay label.block.text-\[10px\].font-bold.text-gray-400.uppercase.tracking-widest.mb-1{font-size:14px;color:#000}
				#red-cultural-login-overlay button#red-cultural-login-submit{font-size:14px;border-radius:6px}
				#red-cultural-login-overlay button#red-cultural-login-submit{letter-spacing:3px;font-size:13px;font-weight:600;border-radius:6px !important}
				#red-cultural-login-overlay p#red-cultural-login-subtitle{font-size:14px}
				#red-cultural-login-overlay p#red-cultural-login-toggle-text{font-size:14px}
			</style>
			<div id="red-cultural-login-overlay"
				class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center opacity-0 invisible transition-all duration-300 p-4"
				data-admin-post="<?php echo esc_attr((string) $admin_post); ?>"
				data-ajax-url="<?php echo esc_attr((string) admin_url('admin-ajax.php')); ?>"
				data-nonce="<?php echo esc_attr((string) $auth_nonce); ?>"
				data-exists-nonce="<?php echo esc_attr((string) $exists_nonce); ?>"
				data-redirect="<?php echo esc_attr((string) $redirect_to); ?>"
				role="dialog"
				aria-modal="true"
				aria-label="Red Cultural - Acceso"
			>
				<div id="red-cultural-login-card" class="bg-white w-full max-w-sm shadow-2xl overflow-hidden relative auth-card scale-95 transform">
					<button id="red-cultural-login-close" class="absolute top-3 right-3 text-gray-400 hover:text-black transition p-1" type="button" aria-label="Cerrar">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
						</svg>
					</button>

					<div id="red-cultural-login-inner" class="p-8">
						<div id="red-cultural-login-header" class="text-center mb-8">
							<h2 id="red-cultural-login-brand" class="text-2xl font-bold text-black tracking-tight mb-1">Red Cultural</h2>
							<p id="red-cultural-login-subtitle" class="text-gray-500 text-xs">Bienvenido de nuevo. Por favor, inicia sesión.</p>
						</div>

						<form id="red-cultural-login-form" class="space-y-4">
							<div id="red-cultural-login-register-fields" class="hidden space-y-4">
								<div id="red-cultural-login-register-grid" class="grid grid-cols-2 gap-3">
									<div id="red-cultural-login-first-name-wrap">
										<label id="red-cultural-login-first-name-label" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-first-name">Nombre</label>
										<input id="red-cultural-login-first-name" type="text" placeholder="Ej. Ana" autocomplete="given-name" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
									</div>
									<div id="red-cultural-login-last-name-wrap">
										<label id="red-cultural-login-last-name-label" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-last-name">Apellido</label>
										<input id="red-cultural-login-last-name" type="text" placeholder="Ej. García" autocomplete="family-name" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
									</div>
								</div>
							</div>

							<div id="red-cultural-login-email-wrap">
								<label id="red-cultural-login-email-label" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-email">Correo Electrónico</label>
								<input id="red-cultural-login-email" type="email" placeholder="correo@ejemplo.com" autocomplete="email" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
								<p id="red-cultural-login-forgot-status" class="mt-2 text-[12px] font-semibold hidden"></p>
							</div>
							<div id="red-cultural-login-email-confirm-wrap" class="hidden space-y-1">
								<label id="red-cultural-login-email-confirm-label" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-email-confirm">Confirmar Correo Electrónico</label>
								<input id="red-cultural-login-email-confirm" type="email" placeholder="correo@ejemplo.com" autocomplete="email" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
							</div>

							<div id="red-cultural-login-password-wrap">
								<label id="red-cultural-login-password-label" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1" for="red-cultural-login-password">Contraseña</label>
								<input id="red-cultural-login-password" type="password" placeholder="••••••••" autocomplete="current-password" class="w-full px-3 py-2 text-sm rounded-3px border border-gray-200 bg-gray-50 focus:bg-white focus-gold outline-none transition duration-200" />
							</div>

							<div id="red-cultural-login-extras" class="flex items-center justify-between text-[11px]">
								<label id="red-cultural-login-remember-wrap" class="flex items-center text-gray-500 cursor-pointer">
									<input id="red-cultural-login-remember" type="checkbox" class="mr-1.5 rounded-sm border-gray-300 text-[#c5a367] focus:ring-[#c5a367]" />
									Recordarme
								</label>
								<button id="red-cultural-login-forgot" type="button" class="text-[#c5a367] hover:brightness-90 font-medium transition">¿Olvidaste tu contraseña?</button>
							</div>

							<?php 
							$show_login_turnstile = !(function_exists('rcp_is_local_environment') && rcp_is_local_environment());
							$cft_key = get_option('cfturnstile_key');
							if ($show_login_turnstile && $cft_key) : ?>
								<div class="cf-turnstile mb-4" data-sitekey="<?php echo esc_attr($cft_key); ?>" data-size="normal"></div>
								<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
							<?php elseif ($show_login_turnstile) : ?>
								<?php do_action('cfturnstile_display_widget'); ?>
							<?php endif; ?>

							<button id="red-cultural-login-submit" type="submit" class="w-full py-3 bg-black text-white rounded-3px font-bold hover:bg-zinc-800 transform active:scale-[0.99] transition-all duration-200 shadow-md tracking-widest uppercase text-[10px]">
								Iniciar Sesión
							</button>

							<div id="red-cultural-login-forgot-back-wrap" class="hidden mt-5 pt-4 border-t border-gray-100 text-center">
								<p class="text-gray-500 text-xs">
									<button id="red-cultural-login-forgot-back" type="button" class="text-[#c5a367] font-bold hover:brightness-90 transition">
										<?php echo esc_html__('Volver a iniciar sesión', 'red-cultural-pages'); ?>
									</button>
								</p>
							</div>
						</form>

						<div id="red-cultural-login-footer" class="mt-8 pt-5 border-t border-gray-100 text-center">
							<p id="red-cultural-login-toggle-text" class="text-gray-500 text-xs">
								¿Eres nuevo en Red Cultural?
								<button id="red-cultural-login-toggle" type="button" class="text-[#c5a367] font-bold hover:brightness-90 transition ml-1">Crea una cuenta</button>
							</p>
						</div>
					</div>
				</div>
			</div>
				<script>
					(function () {
						var overlay = document.getElementById('red-cultural-login-overlay');
						if (!overlay) return;
						var turnstileRequired = <?php echo $show_login_turnstile ? 'true' : 'false'; ?>;

						var card = overlay.querySelector('.auth-card');
						var closeBtn = document.getElementById('red-cultural-login-close');
						var form = document.getElementById('red-cultural-login-form');
						var subtitle = document.getElementById('red-cultural-login-subtitle');
						var toggle = document.getElementById('red-cultural-login-toggle');
						var toggleText = document.getElementById('red-cultural-login-toggle-text');
						var registerFields = document.getElementById('red-cultural-login-register-fields');
						var emailWrap = document.getElementById('red-cultural-login-email-wrap');
						var emailInput = document.getElementById('red-cultural-login-email');
						var passwordWrap = document.getElementById('red-cultural-login-password-wrap');
						var loginExtras = document.getElementById('red-cultural-login-extras');
						var submitBtn = document.getElementById('red-cultural-login-submit');
						var forgotBtn = document.getElementById('red-cultural-login-forgot');
						var forgotStatus = document.getElementById('red-cultural-login-forgot-status');
						var emailConfirmWrap = document.getElementById('red-cultural-login-email-confirm-wrap');
						var emailConfirmInput = document.getElementById('red-cultural-login-email-confirm');
						var forgotBackWrap = document.getElementById('red-cultural-login-forgot-back-wrap');
						var forgotBackBtn = document.getElementById('red-cultural-login-forgot-back');
						var footer = document.getElementById('red-cultural-login-footer');

						var currentView = 'login';
						var forgotExists = null;
						var forgotCheckTimer = null;

						function emailsMatch() {
							if (!emailConfirmInput) {
								return true;
							}
							var main = String(document.getElementById('red-cultural-login-email')?.value || '').trim();
							var confirm = String(emailConfirmInput.value || '').trim();
							if (main === '' || confirm === '') {
								return true;
							}
							return main.toLowerCase() === confirm.toLowerCase();
						}

						function handleConfirmInput() {
							if (emailsMatch()) {
								resetStatus();
							}
						}

						if (emailInput) {
							emailInput.addEventListener('input', handleConfirmInput);
						}
						if (emailConfirmInput) {
							emailConfirmInput.addEventListener('input', handleConfirmInput);
						}

						function openOverlay(initialView) {
							overlay.classList.remove('opacity-0', 'invisible');
							overlay.classList.add('opacity-100', 'visible');
							if (card) card.classList.replace('scale-95', 'scale-100');
							setView(initialView || 'login');
						}

						function closeOverlay() {
							overlay.classList.replace('opacity-100', 'opacity-0');
							if (card) card.classList.replace('scale-100', 'scale-95');
							setTimeout(function () {
								overlay.classList.replace('visible', 'invisible');
							}, 300);
						}

						function resetStatus() {
							if (!forgotStatus) return;
							forgotStatus.classList.add('hidden');
							forgotStatus.classList.remove('text-red-600', 'text-emerald-600');
							forgotStatus.textContent = '';
							forgotExists = null;
						}

						function setStatusError(msg) {
							if (!forgotStatus) return;
							forgotStatus.textContent = String(msg || '');
							forgotStatus.classList.remove('hidden');
							forgotStatus.classList.add('text-red-600');
							forgotStatus.classList.remove('text-emerald-600');
						}

						function setStatusSuccess(msg) {
							if (!forgotStatus) return;
							forgotStatus.textContent = String(msg || '');
							forgotStatus.classList.remove('hidden');
							forgotStatus.classList.add('text-emerald-600');
							forgotStatus.classList.remove('text-red-600');
						}

						function looksLikeEmail(v) {
							var s = String(v || '').trim();
							// Minimal sanity check; we only validate existence when it resembles an email.
							return s.length >= 5 && s.indexOf('@') > 0 && s.indexOf('.') > 0;
						}

						function checkForgotEmailExistsDebounced() {
							if (currentView !== 'forgot') return;
							var emailEl = document.getElementById('red-cultural-login-email');
							if (!emailEl) return;

							var value = String(emailEl.value || '').trim();
							if (!looksLikeEmail(value)) {
								resetStatus();
								return;
							}

							if (forgotCheckTimer) window.clearTimeout(forgotCheckTimer);
							forgotCheckTimer = window.setTimeout(function () {
								var ajaxUrl = overlay.getAttribute('data-ajax-url');
								var nonce = overlay.getAttribute('data-exists-nonce');
								if (!ajaxUrl || !nonce) return;

								fetch(ajaxUrl, {
									method: 'POST',
									headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
									body: new URLSearchParams({
										action: 'rcp_user_exists',
										nonce: nonce,
										user_login: value
									}).toString()
								})
									.then(function (r) { return r.json(); })
									.then(function (json) {
										var exists = !!(json && json.success && json.data && json.data.exists);
										forgotExists = exists;
										if (!exists) {
											setStatusError('Esta cuenta no existe en nuestro sistema');
										} else {
											// Hide the error immediately when it becomes valid.
											resetStatus();
											forgotExists = true;
										}
									})
									.catch(function () {
										// Silent: don't show noisy network errors while typing.
									});
							}, 250);
						}

						function setView(view) {
							currentView = view;
							resetStatus();

							var isLogin = view === 'login';
							var isRegister = view === 'register';
							var isForgot = view === 'forgot';

							if (registerFields) registerFields.classList.toggle('hidden', !isRegister);
							if (passwordWrap) passwordWrap.classList.toggle('hidden', isForgot);
							if (loginExtras) loginExtras.classList.toggle('hidden', !isLogin);
							if (footer) footer.classList.toggle('hidden', isForgot);
							if (forgotBackWrap) forgotBackWrap.classList.toggle('hidden', !isForgot);
							if (emailConfirmWrap) emailConfirmWrap.classList.toggle('hidden', !isRegister);
							if (!isRegister && emailConfirmInput) {
								emailConfirmInput.value = '';
							}

							if (subtitle) {
								if (isLogin) subtitle.innerText = 'Bienvenido de nuevo. Por favor, inicia sesión.';
								if (isRegister) subtitle.innerText = 'Únete a nuestra comunidad cultural hoy.';
								if (isForgot) subtitle.innerText = 'Ingresa el correo asociado a tu cuenta.';
							}

							if (submitBtn) {
								submitBtn.disabled = false;
								if (isLogin) submitBtn.innerText = 'Iniciar Sesión';
								if (isRegister) submitBtn.innerText = 'Crear Cuenta';
								if (isForgot) submitBtn.innerText = 'Enviar correo';
							}

							// Live email check for forgot view.
							if (isForgot) {
								var emailEl = document.getElementById('red-cultural-login-email');
								if (emailEl) {
									emailEl.addEventListener('input', checkForgotEmailExistsDebounced);
									emailEl.addEventListener('blur', checkForgotEmailExistsDebounced);
									// Run once on entry (in case it was prefilled).
									checkForgotEmailExistsDebounced();
								}
							}

							if (toggleText) {
								if (isLogin) {
									toggleText.innerHTML = '¿Eres nuevo en Red Cultural? <button id="red-cultural-login-toggle" type="button" class="text-[#c5a367] font-bold hover:brightness-90 transition ml-1">Crea una cuenta</button>';
								} else if (isRegister) {
									toggleText.innerHTML = '¿Ya tienes una cuenta? <button id="red-cultural-login-toggle" type="button" class="text-[#c5a367] font-bold hover:brightness-90 transition ml-1">Inicia sesión</button>';
								}
								toggle = document.getElementById('red-cultural-login-toggle');
								if (toggle) toggle.addEventListener('click', function () { setView(isLogin ? 'register' : 'login'); });
							}
						}

						function inferAuthViewFromHref(href) {
							var h = String(href || '');
							if (!h) return 'login';
							var lower = h.toLowerCase();
							// WP register flows
							if (lower.indexOf('action=register') !== -1) return 'register';
							if (lower.indexOf('wp-register.php') !== -1) return 'register';
							if (lower.indexOf('wp-signup.php') !== -1) return 'register';
							// Otherwise treat as login.
							return 'login';
						}

						document.addEventListener('click', function (e) {
							var t = e.target;
							if (!(t instanceof Element)) return;

							var open = t.closest('[data-rcp-auth-open]');
							if (open) {
								e.preventDefault();
								openOverlay('login');
								return;
							}

							// Unify header login buttons (BuddyBoss/other themes) with this exact modal.
							// Many themes output Sign in/Sign up links to wp-login.php / registration; we intercept and open this modal instead.
							var link = t.closest('a');
							if (link) {
								var href = link.getAttribute('href') || '';
								var hasWpAuthHref =
									href.indexOf('wp-login.php') !== -1 ||
									href.indexOf('wp-register.php') !== -1 ||
									href.indexOf('wp-signup.php') !== -1;

								var isThemeAuthButton =
									link.classList.contains('signin-button') ||
									link.classList.contains('signup') ||
									link.classList.contains('sign-in') ||
									link.classList.contains('sign-up');

								if (hasWpAuthHref || isThemeAuthButton) {
									e.preventDefault();
									openOverlay(inferAuthViewFromHref(href) || (link.classList.contains('signup') ? 'register' : 'login'));
									return;
								}
							}
						});

						if (closeBtn) closeBtn.addEventListener('click', closeOverlay);
						overlay.addEventListener('click', function (e) {
							if (e.target === overlay) closeOverlay();
						});

						function submitAuth(mode, payload) {
							var actionUrl = overlay.getAttribute('data-admin-post');
							var nonce = overlay.getAttribute('data-nonce');
							var redirectTo = overlay.getAttribute('data-redirect');
							if (!actionUrl || !nonce || !redirectTo) return;

							var form = document.createElement('form');
							form.method = 'POST';
							form.action = actionUrl;

							function add(name, value) {
								var input = document.createElement('input');
								input.type = 'hidden';
								input.name = name;
								input.value = value;
								form.appendChild(input);
							}

							add('action', 'rcp_checkout_auth');
							add('rcp_nonce', nonce);
							add('redirect_to', redirectTo);
							add('mode', mode);

							Object.keys(payload || {}).forEach(function (k) {
								add(k, payload[k]);
							});

							var turnstileToken = typeof turnstile !== 'undefined' ? turnstile.getResponse() : '';

							if (turnstileRequired) {
								if (!turnstileToken) {
									alert('Por favor, completa la verificación de seguridad (Captcha).');
									return;
								}
								add('cf-turnstile-response', turnstileToken);
							} else if (turnstileToken) {
								add('cf-turnstile-response', turnstileToken);
							}

							document.body.appendChild(form);
							form.submit();
						}

						function forgotPassword(email) {
							var ajaxUrl = overlay.getAttribute('data-ajax-url');
							var nonce = overlay.getAttribute('data-nonce');
							if (!ajaxUrl || !nonce) return;

							resetStatus();

							if (!forgotStatus) return;
							var cleanEmail = String(email || '').trim();
							if (!cleanEmail) {
								setStatusError('Ingresa tu correo.');
								return;
							}

							// If we already know it doesn't exist, show immediately without submitting.
							if (forgotExists === false) {
								setStatusError('Esta cuenta no existe en nuestro sistema');
								return;
							}

							if (submitBtn) submitBtn.disabled = true;

							fetch(ajaxUrl, {
								method: 'POST',
								headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
								body: new URLSearchParams({
									action: 'rcp_forgot_password',
									rcp_nonce: nonce,
									email: cleanEmail
								}).toString()
							})
								.then(function (r) { return r.json(); })
								.then(function (json) {
									if (!json || !json.success) {
										var msg = (json && json.data && json.data.message) ? String(json.data.message) : 'No pudimos enviar el correo. Inténtalo de nuevo.';
										setStatusError(msg);
										if (submitBtn) submitBtn.disabled = false;
										return;
									}

									var okMsg = (json.data && json.data.message) ? String(json.data.message) : 'Te enviamos un correo para restablecer tu contraseña. Revisa tu bandeja de entrada.';
									setStatusSuccess(okMsg);
									if (submitBtn) submitBtn.innerText = 'Enviado';
								})
								.catch(function () {
									setStatusError('No pudimos enviar el correo. Inténtalo de nuevo.');
									if (submitBtn) submitBtn.disabled = false;
								});
						}

						if (form) {
							form.addEventListener('submit', function (e) {
								e.preventDefault();

								var email = document.getElementById('red-cultural-login-email');
								var pass = document.getElementById('red-cultural-login-password');
								var remember = document.getElementById('red-cultural-login-remember');
								var firstName = document.getElementById('red-cultural-login-first-name');
								var lastName = document.getElementById('red-cultural-login-last-name');

								if (currentView === 'forgot') {
									// Trigger an existence check immediately (in case user pasted and hit enter fast).
									checkForgotEmailExistsDebounced();
									forgotPassword(email ? email.value : '');
									return;
								}

								if (currentView === 'login') {
									submitAuth('login', {
										user_login: email ? email.value : '',
										password: pass ? pass.value : '',
										remember: remember && remember.checked ? '1' : ''
									});
									return;
								}

								if (currentView === 'register') {
									if (!emailsMatch()) {
										setStatusError('Los correos electrónicos deben coincidir.');
										if (submitBtn) submitBtn.disabled = false;
										return;
									}
									submitAuth('register', {
										first_name: firstName ? firstName.value : '',
										last_name: lastName ? lastName.value : '',
										email: email ? email.value : '',
										password: pass ? pass.value : ''
									});
									return;
								}

							});
						}

						if (forgotBtn) {
							forgotBtn.addEventListener('click', function () { setView('forgot'); });
						}
						if (forgotBackBtn) {
							forgotBackBtn.addEventListener('click', function () { setView('login'); });
						}

						if (toggle) toggle.addEventListener('click', function () { setView('register'); });
						setView('login');
					})();
				</script>
		<?php
	}

	public static function register_shortcodes(): void {
		$shortcode_file = RC_CORE_PATH . 'includes/modules/templates/shortcodes/red-cultural-cursos.php';
		if (file_exists($shortcode_file)) {
			require_once $shortcode_file;
		}

		$carousel_file = RC_CORE_PATH . 'includes/modules/templates/shortcodes/red-cultural-cursos-carousel.php';
		if (file_exists($carousel_file)) {
			require_once $carousel_file;
		}

			$us_file = RC_CORE_PATH . 'includes/modules/templates/shortcodes/red-cultural-us.php';
			if (file_exists($us_file)) {
				require_once $us_file;
			}

			$viajes_file = RC_CORE_PATH . 'includes/modules/templates/shortcodes/red-cultural-viajes.php';
			if (file_exists($viajes_file)) {
				require_once $viajes_file;
			}

			if (function_exists('rcp_red_cultural_cursos_shortcode')) {
				add_shortcode('red-cultural-cursos', 'rcp_red_cultural_cursos_shortcode');
			}

		if (function_exists('rcp_red_cultural_cursos_carousel_shortcode')) {
			add_shortcode('red-cultural-cursos-carousel', 'rcp_red_cultural_cursos_carousel_shortcode');
		}

			if (function_exists('rcp_red_cultural_us_shortcode')) {
				add_shortcode('red-cultural-us', 'rcp_red_cultural_us_shortcode');
			}

			if (function_exists('rcp_red_cultural_viajes_shortcode')) {
				add_shortcode('red-cultural-viajes', 'rcp_red_cultural_viajes_shortcode');
			}
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

		// Let WooCommerce handle order-received / pay-for-order pages.
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

		// Handle '/tienda' as an alias for the shop page.
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

	public static function register_checkout_auth_handlers(): void {
		add_action('admin_post_nopriv_rcp_checkout_auth', array(__CLASS__, 'handle_checkout_auth'));
		add_action('admin_post_rcp_checkout_auth', array(__CLASS__, 'handle_checkout_auth'));
	}

	public static function register_viaje_italia_interest_handlers(): void {
		add_action('admin_post_nopriv_rcp_viaje_italia_interest', array(__CLASS__, 'handle_viaje_italia_interest_form'));
		add_action('admin_post_rcp_viaje_italia_interest', array(__CLASS__, 'handle_viaje_italia_interest_form'));
	}

	public static function register_viaje_japon_interest_handlers(): void {
		add_action('admin_post_nopriv_rcp_viaje_japon_interest', array(__CLASS__, 'handle_viaje_japon_interest_form'));
		add_action('admin_post_rcp_viaje_japon_interest', array(__CLASS__, 'handle_viaje_japon_interest_form'));
	}

	public static function register_viaje_escandinavia_interest_handlers(): void {
		add_action('admin_post_nopriv_rcp_viaje_escandinavia_interest', array(__CLASS__, 'handle_viaje_escandinavia_interest_form'));
		add_action('admin_post_rcp_viaje_escandinavia_interest', array(__CLASS__, 'handle_viaje_escandinavia_interest_form'));
	}

	public static function register_contacto_form_handlers(): void {
		add_action('admin_post_nopriv_rcp_contact_form', array(__CLASS__, 'handle_contacto_form'));
		add_action('admin_post_rcp_contact_form', array(__CLASS__, 'handle_contacto_form'));
	}

	public static function handle_contacto_form(): void {
		if (!isset($_POST['rcp_contact_nonce']) || !wp_verify_nonce((string) $_POST['rcp_contact_nonce'], 'rcp_contact_form')) {
			wp_safe_redirect((string) home_url('/contacto/'));
			exit;
		}

		$name = isset($_POST['name']) ? sanitize_text_field((string) wp_unslash($_POST['name'])) : '';
		$email = isset($_POST['email']) ? sanitize_email((string) wp_unslash($_POST['email'])) : '';
		$phone = isset($_POST['phone']) ? sanitize_text_field((string) wp_unslash($_POST['phone'])) : '';
		$subject = isset($_POST['subject']) ? sanitize_text_field((string) wp_unslash($_POST['subject'])) : '';
		$message = isset($_POST['message']) ? sanitize_textarea_field((string) wp_unslash($_POST['message'])) : '';

		$to = (string) get_option('admin_email');
		$mail_subject = 'Contacto — ' . ($subject !== '' ? $subject : 'Nuevo mensaje');
		$body = "Nombre: {$name}\nEmail: {$email}\nCelular: {$phone}\nAsunto: {$subject}\n\nMensaje:\n{$message}\n";

		$headers = array();
		if ($email !== '') {
			$headers[] = 'Reply-To: ' . $email;
		}

		wp_mail($to, $mail_subject, $body, $headers);

		$redirect = wp_get_referer();
		if (!$redirect) {
			$redirect = home_url('/contacto/');
		}
		wp_safe_redirect(add_query_arg('rcp_contact', 'success', (string) $redirect));
		exit;
	}

	public static function handle_viaje_italia_interest_form(): void {
		if (!isset($_POST['rcp_vi_nonce']) || !wp_verify_nonce((string) $_POST['rcp_vi_nonce'], 'rcp_viaje_italia_interest')) {
			wp_safe_redirect((string) home_url('/viaje-italia/'));
			exit;
		}

		$name = isset($_POST['rcp_vi_name']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_vi_name'])) : '';
		$email = isset($_POST['rcp_vi_email']) ? sanitize_email((string) wp_unslash($_POST['rcp_vi_email'])) : '';
		$phone = isset($_POST['rcp_vi_phone']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_vi_phone'])) : '';
		$message = isset($_POST['rcp_vi_message']) ? sanitize_textarea_field((string) wp_unslash($_POST['rcp_vi_message'])) : '';

		$to = (string) get_option('admin_email');
		$subject = 'Viaje Italia — Nuevo interés';
		$body = "Viaje: Italia\n\nNombre: {$name}\nEmail: {$email}\nTeléfono: {$phone}\n\nMensaje:\n{$message}\n";

		$headers = array();
		if ($email !== '') {
			$headers[] = 'Reply-To: ' . $email;
		}

		wp_mail($to, $subject, $body, $headers);

		$redirect = wp_get_referer();
		if (!$redirect) {
			$redirect = home_url('/viaje-italia/');
		}
		wp_safe_redirect(add_query_arg('rcp_vi_interest', 'success', (string) $redirect));
		exit;
	}

	public static function handle_viaje_escandinavia_interest_form(): void {
		if (!isset($_POST['rcp_ve_nonce']) || !wp_verify_nonce((string) $_POST['rcp_ve_nonce'], 'rcp_viaje_escandinavia_interest')) {
			wp_safe_redirect((string) home_url('/viaje-escandinavia/'));
			exit;
		}

		$name = isset($_POST['rcp_ve_name']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_ve_name'])) : '';
		$email = isset($_POST['rcp_ve_email']) ? sanitize_email((string) wp_unslash($_POST['rcp_ve_email'])) : '';
		$phone = isset($_POST['rcp_ve_phone']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_ve_phone'])) : '';
		$message = isset($_POST['rcp_ve_message']) ? sanitize_textarea_field((string) wp_unslash($_POST['rcp_ve_message'])) : '';

		$to = 'magdalena@redcultural.cl';
		$subject = 'Viaje Escandinavia — Nuevo interés';
		$body = "Viaje: Escandinavia 2026\nFechas: 25 de agosto al 09 de septiembre de 2026\n\nNombre: {$name}\nEmail: {$email}\nTeléfono: {$phone}\n\nMensaje:\n{$message}\n";

		$headers = array();
		if ($email !== '') {
			$headers[] = 'Reply-To: ' . $email;
		}

		wp_mail($to, $subject, $body, $headers);

		$redirect = wp_get_referer();
		if (!$redirect) {
			$redirect = home_url('/viaje-escandinavia/');
		}
		wp_safe_redirect(add_query_arg('rcp_ve_interest', 'success', (string) $redirect));
		exit;
	}

	public static function handle_viaje_japon_interest_form(): void {
		if (!isset($_POST['rcp_vj_nonce']) || !wp_verify_nonce((string) $_POST['rcp_vj_nonce'], 'rcp_viaje_japon_interest')) {
			wp_safe_redirect((string) home_url('/viaje-japon/'));
			exit;
		}

		$name = isset($_POST['rcp_vj_name']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_vj_name'])) : '';
		$email = isset($_POST['rcp_vj_email']) ? sanitize_email((string) wp_unslash($_POST['rcp_vj_email'])) : '';
		$phone = isset($_POST['rcp_vj_phone']) ? sanitize_text_field((string) wp_unslash($_POST['rcp_vj_phone'])) : '';
		$message = isset($_POST['rcp_vj_message']) ? sanitize_textarea_field((string) wp_unslash($_POST['rcp_vj_message'])) : '';

		$to = 'magdalena@redcultural.cl';
		$subject = 'Viaje Japón — Nuevo interés';
		$body = "Viaje: Japón\nFechas: 26-octubre al 08 de noviembre de 2026\n\nNombre: {$name}\nEmail: {$email}\nTeléfono: {$phone}\n\nMensaje:\n{$message}\n";

		$headers = array();
		if ($email !== '') {
			$headers[] = 'Reply-To: ' . $email;
		}

		wp_mail($to, $subject, $body, $headers);

		$redirect = wp_get_referer();
		if (!$redirect) {
			$redirect = home_url('/viaje-japon/');
		}
		wp_safe_redirect(add_query_arg('rcp_vj_interest', 'success', (string) $redirect));
		exit;
	}

		public static function register_ajax_handlers(): void {
			add_action('wp_ajax_nopriv_rcp_user_exists', array(__CLASS__, 'handle_user_exists_ajax'));
			add_action('wp_ajax_rcp_user_exists', array(__CLASS__, 'handle_user_exists_ajax'));
			add_action('wp_ajax_nopriv_rcp_forgot_password', array(__CLASS__, 'handle_forgot_password_ajax'));
			add_action('wp_ajax_rcp_forgot_password', array(__CLASS__, 'handle_forgot_password_ajax'));
		}

		public static function handle_user_exists_ajax(): void {
			check_ajax_referer('rcp_user_exists', 'nonce');

		$login_or_email = isset($_POST['user_login']) ? sanitize_text_field((string) wp_unslash($_POST['user_login'])) : '';
		$login_or_email = trim((string) $login_or_email);

		if ($login_or_email === '') {
			wp_send_json_success(array('exists' => false));
		}

		$exists = false;
		if (is_email($login_or_email)) {
			$exists = (bool) email_exists($login_or_email);
		} else {
			$exists = (bool) username_exists($login_or_email);
		}

			wp_send_json_success(array('exists' => $exists));
		}

		public static function handle_forgot_password_ajax(): void {
			$nonce = isset($_POST['rcp_nonce']) ? (string) wp_unslash($_POST['rcp_nonce']) : '';
			if ($nonce === '' || !wp_verify_nonce($nonce, 'rcp_checkout_auth')) {
				wp_send_json_error(array('message' => 'No autorizado.'), 403);
			}

			$email = isset($_POST['email']) ? sanitize_email((string) wp_unslash($_POST['email'])) : '';
			$email = trim((string) $email);
			if ($email === '' || !is_email($email)) {
				wp_send_json_error(array('message' => 'Correo inválido.'), 400);
			}

			if (!email_exists($email)) {
				wp_send_json_error(
					array(
						'code' => 'not_found',
						'message' => 'Esta cuenta no existe en nuestro sistema',
					),
					404
				);
			}

			// retrieve_password() reads from $_POST['user_login'].
			$_POST['user_login'] = $email;
			$result = retrieve_password();

			if (is_wp_error($result)) {
				wp_send_json_error(array('message' => 'No pudimos enviar el correo. Inténtalo de nuevo.'), 500);
			}

			wp_send_json_success(
				array(
					'message' => 'Te enviamos un correo para restablecer tu contraseña. Revisa tu bandeja de entrada.',
				)
			);
		}

	public static function handle_checkout_auth(): void {
		if (!isset($_POST['rcp_nonce']) || !wp_verify_nonce((string) $_POST['rcp_nonce'], 'rcp_checkout_auth')) {
			wp_safe_redirect(home_url('/'));
			exit;
		}

		// Turnstile validation (skipped on local environments).
		$turnstile_response = isset($_POST['cf-turnstile-response']) ? sanitize_text_field((string) wp_unslash($_POST['cf-turnstile-response'])) : '';
		$turnstile_valid    = true;
		$skip_turnstile     = function_exists('rcp_is_local_environment') && rcp_is_local_environment();

		if (!$skip_turnstile && function_exists('cfturnstile_check')) {
			$check = cfturnstile_check($turnstile_response);
			$turnstile_valid = ($check && is_array($check) && isset($check['success']) && $check['success']);
		}

		if (!$skip_turnstile && ($turnstile_response === '' || !$turnstile_valid)) {
			$redirect_to = isset($_POST['redirect_to']) ? esc_url_raw((string) wp_unslash($_POST['redirect_to'])) : (string) home_url('/');
			wp_safe_redirect(add_query_arg('rcp_auth_error', 'captcha', $redirect_to));
			exit;
		}

		$redirect_to = isset($_POST['redirect_to']) ? esc_url_raw((string) wp_unslash($_POST['redirect_to'])) : (string) home_url('/');
		if ($redirect_to === '') {
			$redirect_to = (string) home_url('/');
		}

		$mode = isset($_POST['mode']) ? (string) wp_unslash($_POST['mode']) : 'login';

		$user_login = isset($_POST['user_login']) ? sanitize_text_field((string) wp_unslash($_POST['user_login'])) : '';
		$email = isset($_POST['email']) ? sanitize_email((string) wp_unslash($_POST['email'])) : '';
		$password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';
		$remember = !empty($_POST['remember']);

		// Forgot password: send the reset email and return to checkout.
		if ($mode === 'forgot') {
			$login_or_email = $user_login !== '' ? $user_login : $email;
			$login_or_email = trim((string) $login_or_email);

			if ($login_or_email === '') {
				wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
				exit;
			}

			// retrieve_password() reads from $_POST['user_login'].
			$_POST['user_login'] = $login_or_email;
			$result = retrieve_password();

			if (is_wp_error($result)) {
				wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
				exit;
			}

			wp_safe_redirect(add_query_arg('rcp_auth_notice', 'reset_sent', $redirect_to));
			exit;
		}

		// Login/Register: require password + a login/email.
		if (($user_login === '' && $email === '') || $password === '') {
			wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
			exit;
		}

		if ($user_login === '') {
			$user_login = $email;
		}

		if ($mode === 'register') {
			if ($email === '' || !is_email($email)) {
				wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
				exit;
			}

			if (email_exists($email)) {
				// If user exists, fall back to login attempt.
				$mode = 'login';
			} else {
				$first_name = isset($_POST['first_name']) ? sanitize_text_field((string) wp_unslash($_POST['first_name'])) : '';
				$last_name = isset($_POST['last_name']) ? sanitize_text_field((string) wp_unslash($_POST['last_name'])) : '';

				$username = sanitize_user(strstr($email, '@', true) ?: $email, true);
				if ($username === '') {
					$username = 'user';
				}
				$base = $username;
				$i = 1;
				while (username_exists($username)) {
					$username = $base . $i;
					$i++;
				}

				$user_id = wp_create_user($username, $password, $email);
				if (is_wp_error($user_id)) {
					wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
					exit;
				}

				if ($first_name !== '') {
					update_user_meta((int) $user_id, 'first_name', $first_name);
				}
				if ($last_name !== '') {
					update_user_meta((int) $user_id, 'last_name', $last_name);
				}

				$user_login = $username;
			}
		}

		// If user typed an email for login, translate it to the actual username.
		if (strpos($user_login, '@') !== false) {
			$by_email = get_user_by('email', $user_login);
			if ($by_email && isset($by_email->user_login)) {
				$user_login = (string) $by_email->user_login;
			}
		}

		// Evitamos la doble validación del plugin de Turnstile durante wp_signon,
		// ya que nosotros ya lo validamos manualmente antes de iniciar este proceso.
		if (function_exists('cfturnstile_wp_login_check')) {
			remove_filter('authenticate', 'cfturnstile_wp_login_check', 21);
		}

		$user = wp_signon(
			array(
				'user_login' => $user_login,
				'user_password' => $password,
				'remember' => $remember,
			),
			is_ssl()
		);

		if (is_wp_error($user)) {
			wp_safe_redirect(add_query_arg('rcp_auth_error', '1', $redirect_to));
			exit;
		}

		wp_safe_redirect($redirect_to);
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

	public static function enqueue_404_assets(): void {
		if (!is_404() || is_admin()) {
			return;
		}

		$css_url = RC_CORE_URL . 'assets/css/redcultural-404.css';
		$css_path = RC_CORE_PATH . 'assets/css/redcultural-404.css';

		wp_enqueue_style(
			'redcultural-404',
			$css_url,
			array(),
			file_exists($css_path) ? (string) filemtime($css_path) : '1.0.0'
		);
	}
}

 // Red_Cultural_Templates::init() called by core ;
