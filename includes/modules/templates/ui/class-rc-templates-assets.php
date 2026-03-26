<?php
/**
 * Assets management for Red Cultural Templates.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_Assets {
	public static function init(): void {
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_main_nav_assets'));
		add_action('wp_head', array(__CLASS__, 'render_main_nav_styles'), 20);
		add_action('wp_footer', array(__CLASS__, 'render_main_nav_script'), 5);
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_404_assets'), 20);
		add_action('wp_head', array(__CLASS__, 'maybe_hide_nosotros_post_title'), 30);
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
		if (is_admin()) return;
		?>
		<style>
			.wp-block-woocommerce-customer-account,
			.wp-block-woocommerce-mini-cart{display:none !important}
			nav.wp-block-navigation .wp-block-navigation-item__content:focus,
			nav.wp-block-navigation .wp-block-navigation-item__content:focus-visible,
			nav.wp-block-navigation .wp-block-navigation-item__content:active,
			nav.wp-block-navigation .wp-block-navigation__responsive-container-open:focus,
			nav.wp-block-navigation .wp-block-navigation__responsive-container-open:focus-visible,
			nav.wp-block-navigation .wp-block-navigation__responsive-container-close:focus,
			nav.wp-block-navigation .wp-block-navigation__responsive-container-close:focus-visible,
			nav.wp-block-navigation button:focus,
			nav.wp-block-navigation button:focus-visible{outline:0 !important;box-shadow:none !important}
			nav.wp-block-navigation a,
			nav.wp-block-navigation button{outline:0 !important;box-shadow:none !important;-webkit-tap-highlight-color:transparent}
			nav.wp-block-navigation a:hover,
			nav.wp-block-navigation a:focus,
			nav.wp-block-navigation a:focus-visible,
			nav.wp-block-navigation a:active,
			nav.wp-block-navigation button:hover,
			nav.wp-block-navigation button:focus,
			nav.wp-block-navigation button:focus-visible,
			nav.wp-block-navigation button:active{outline:0 !important;box-shadow:none !important;border-color:transparent !important}
			header .wp-block-site-title a,
			header a.custom-logo-link{outline:0 !important;box-shadow:none !important;-webkit-tap-highlight-color:transparent}
			header .wp-block-site-title a:focus,
			header .wp-block-site-title a:focus-visible,
			header .wp-block-site-title a:active,
			header a.custom-logo-link:focus,
			header a.custom-logo-link:focus-visible,
			header a.custom-logo-link:active{outline:0 !important;box-shadow:none !important}
			nav.wp-block-navigation .wp-block-navigation-item__content,
			nav.wp-block-navigation .wp-block-navigation-item__content:hover{text-decoration:none !important;background-image:none !important}
			span.wp-block-navigation-item__label{letter-spacing:2px;font-weight:700;font-size:12px;text-transform:uppercase}
			.material-symbols-outlined{font-family:'Material Symbols Outlined' !important;font-weight:normal;font-style:normal;font-size:18px;line-height:1;letter-spacing:normal;text-transform:none;display:inline-block;white-space:nowrap;word-wrap:normal;direction:ltr;-webkit-font-feature-settings:'liga';-webkit-font-smoothing:antialiased;font-variation-settings:'FILL' 0,'wght' 500,'GRAD' 0,'opsz' 24}
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
			@media (min-width: 768px){.wp-block-navigation__container{column-gap:36px !important}.rcp-nav-right{margin-left:auto !important}}
			@media (max-width: 1080px){
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-close,
				nav.wp-block-navigation .wp-block-navigation-item__content,
				nav.wp-block-navigation .wp-block-navigation-item__content:focus,
				nav.wp-block-navigation .wp-block-navigation-item__content:focus-visible,
				nav.wp-block-navigation .wp-block-navigation-item__content:active,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open:focus,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open:focus-visible,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-close:focus,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-close:focus-visible{outline:0 !important;box-shadow:none !important;border:0 !important}
				nav.wp-block-navigation .wp-block-navigation-item__content::after{display:none !important}
				nav.wp-block-navigation .wp-block-navigation__container{display:none !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-open{display:inline-flex !important;align-items:center;justify-content:center}
				nav.wp-block-navigation .wp-block-navigation__responsive-container{position:fixed !important;inset:0 !important;background:rgba(0,0,0,.60) !important;backdrop-filter:blur(6px);opacity:0;pointer-events:none;transition:opacity .35s cubic-bezier(.22,1,.36,1);will-change:opacity}
				nav.wp-block-navigation .wp-block-navigation__responsive-container.is-menu-open,
				nav.wp-block-navigation .wp-block-navigation__responsive-container.has-modal-open{opacity:1;pointer-events:auto}
				nav.wp-block-navigation .wp-block-navigation__responsive-close{display:block !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-dialog{position:absolute;top:0;left:0;height:100%;width:60vw;max-width:520px;background:#fff;box-shadow:20px 0 60px rgba(0,0,0,.18);transform:translate3d(-102%, 0, 0);transition:transform .55s cubic-bezier(.22,1,.36,1);will-change:transform;padding:18px 16px;overflow:auto}
				nav.wp-block-navigation .wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__responsive-dialog,
				nav.wp-block-navigation .wp-block-navigation__responsive-container.has-modal-open .wp-block-navigation__responsive-dialog{transform:translate3d(0,0,0)}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content{padding-top:10px}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation__container{display:flex !important;flex-direction:column;gap:16px;align-items:flex-start;justify-content:flex-start;text-align:left !important;width:100% !important;margin:0 !important;padding:0 !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation-item{width:100% !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation-item__content,
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation-item__content *{text-align:left !important;justify-content:flex-start !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .wp-block-navigation-item__content{width:100% !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .rcp-account-toggle{width:100% !important;justify-content:space-between !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .rcp-btn-auth{width:100% !important;justify-content:flex-start !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .rcp-btn-auth--logout{min-width:0 !important;padding:0 !important;border:0 !important;background:transparent !important}
				nav.wp-block-navigation .wp-block-navigation__responsive-container-content .rcp-nav-right{margin-left:0 !important}
				.rcp-cart-link{width:100% !important;justify-content:flex-start !important;gap:10px}
				.rcp-cart-text{display:inline-flex}
				.rcp-cart-link .material-symbols-outlined{display:none !important}
				.rcp-cart-badge{position:static;top:auto;right:auto;min-width:24px;margin-left:0}
			}
			@media (min-width: 1081px){nav.wp-block-navigation .wp-block-navigation__responsive-container-open{display:none !important}}
			.rcp-account-arrow{transition:transform .2s ease}
			.rcp-account-arrow.is-open{transform:rotate(180deg)}
			.rcp-dropdown-content{display:none;position:absolute;top:100%;right:0;background:#fff;min-width:220px;border:1px solid #000;z-index:60;margin-top:.5rem;box-shadow:0 4px 6px -1px rgb(0 0 0 / 0.1)}
			.rcp-dropdown-content.is-open{display:block}
			.rcp-dropdown-item{padding:12px 16px;display:block;border-bottom:1px solid #f0f0f0;transition:background-color .2s}
			.rcp-dropdown-item:last-child{border-bottom:none}
			.rcp-dropdown-item:hover{background-color:#f9f9f9}
			.rcp-footer-links{width:100%}
			.rcp-footer-links__inner{display:flex;gap:72px;flex-wrap:wrap;justify-content:flex-end;align-items:flex-start}
			.rcp-footer-links__col{display:flex;flex-direction:column;gap:14px;min-width:180px}
			.rcp-footer-links__link{color:#fff !important;text-transform:uppercase;letter-spacing:2px;font-weight:700;font-size:12px;text-decoration:none !important;border:0 !important;outline:0 !important;box-shadow:none !important;background:transparent !important}
			.rcp-footer-links__link:hover,
			.rcp-footer-links__link:focus,
			.rcp-footer-links__link:focus-visible,
			.rcp-footer-links__link:active{opacity:.75;border:0 !important;outline:0 !important;box-shadow:none !important;background:transparent !important}
			@media (max-width: 900px){.rcp-footer-links__inner{justify-content:flex-start;gap:34px}.rcp-footer-links__col{min-width:0}}
		</style>
		<?php
	}

	public static function render_main_nav_script(): void {
		if (is_admin()) return;
		?>
		<script>
			(function () {
				function closeAll() {
					document.querySelectorAll('[data-rcp-account-dropdown]').forEach(function (el) { el.classList.remove('is-open'); });
					document.querySelectorAll('.rcp-account-arrow').forEach(function (el) { el.classList.remove('is-open'); });
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

	public static function enqueue_404_assets(): void {
		if (!is_404() || is_admin()) return;
		$css_url = RC_CORE_URL . 'assets/css/redcultural-404.css';
		$css_path = RC_CORE_PATH . 'assets/css/redcultural-404.css';
		wp_enqueue_style('redcultural-404', $css_url, array(), file_exists($css_path) ? (string) filemtime($css_path) : '1.0.0');
	}

	public static function maybe_hide_nosotros_post_title(): void {
		if (is_admin()) return;
		if (!function_exists('is_page') || !is_page(array('nosotros', 'quienes-somos'))) return;
		?>
		<style>h1.wp-block-post-title{display:none !important}</style>
		<?php
	}

	public static function enqueue_myaccount_comunas_autocomplete(): void {
		if (!function_exists('is_account_page') || !is_account_page()) return;
		$woo_check_main = WP_PLUGIN_DIR . '/woo-check-new/woo-check.php';
		if (file_exists($woo_check_main)) {
			if (!wp_script_is('woo-check-comunas-chile', 'registered')) {
				wp_register_script('woo-check-comunas-chile', RC_CORE_URL . "includes/modules/templates/comunas-chile.js", array(), '1.0', true);
			}
			if (!wp_script_is('woo-check-autocomplete', 'registered')) {
				wp_register_script('woo-check-autocomplete', RC_CORE_URL . "includes/modules/templates/woo-check-autocomplete.js", array('jquery', 'jquery-ui-autocomplete', 'woo-check-comunas-chile'), '1.0', true);
			}
			if (!wp_style_is('woo-check-style', 'registered')) {
				wp_register_style('woo-check-style', RC_CORE_URL . "includes/modules/templates/woo-check-style.css", array(), '1.0');
			}
		}
		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_script('woo-check-comunas-chile');
		wp_enqueue_script('woo-check-autocomplete');
		wp_enqueue_style('woo-check-style');
	}
}
