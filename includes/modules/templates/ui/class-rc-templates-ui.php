<?php
/**
 * UI and Navigation logic for Red Cultural Templates.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_UI {
	public static function init(): void {
		add_filter('render_block', array(__CLASS__, 'maybe_remove_woocommerce_header_blocks'), 9, 2);
		add_filter('render_block', array(__CLASS__, 'inject_block_navigation_items'), 10, 2);
		add_filter('wp_nav_menu_items', array(__CLASS__, 'inject_classic_menu_items'), 10, 2);
		add_filter('wp_nav_menu_objects', array(__CLASS__, 'rewrite_my_account_submenu'), 10, 2);
	}

	public static function maybe_remove_woocommerce_header_blocks(string $block_content, array $block): string {
		if (is_admin() || $block_content === '' || empty($block['blockName'])) {
			return $block_content;
		}

		if ($block['blockName'] === 'woocommerce/customer-account' || $block['blockName'] === 'woocommerce/mini-cart') {
			return '';
		}

		return $block_content;
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

		$replacement = $force ? '$1' . $inserts . '$3' : '$1$2' . $inserts . '$3';
		$updated = preg_replace(
			'/(<ul[^>]*class="[^"]*wp-block-navigation__container[^"]*"[^>]*>)(.*?)(<\/ul>)/s',
			$replacement,
			$block_content,
			1
		);

		return is_string($updated) && $updated !== '' ? $updated : $block_content;
	}

	public static function inject_footer_navigation_items(string $block_content): string {
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

	public static function get_main_nav_links(): array {
		if (is_user_logged_in()) {
			$links = array(
				array('type' => 'link', 'label' => 'NOSOTROS', 'url' => self::get_nosotros_url(), 'key' => 'nosotros'),
				array('type' => 'link', 'label' => 'CURSOS', 'url' => (string) home_url('/cursos/'), 'key' => 'cursos'),
				array('type' => 'link', 'label' => 'CONTACTO', 'url' => (string) home_url('/contacto/'), 'key' => 'contacto'),
				array('type' => 'link', 'label' => 'ARTÍCULOS', 'url' => (string) home_url('/articulos/'), 'key' => 'articulos'),
			);
		} else {
			$links = array(
				array('type' => 'link', 'label' => 'CURSOS', 'url' => (string) home_url('/cursos/'), 'key' => 'cursos'),
				array('type' => 'link', 'label' => 'ARTÍCULOS', 'url' => (string) home_url('/articulos/'), 'key' => 'articulos'),
				array('type' => 'link', 'label' => 'CONTACTO', 'url' => (string) home_url('/contacto/'), 'key' => 'contacto'),
				array('type' => 'link', 'label' => 'NOSOTROS', 'url' => self::get_nosotros_url(), 'key' => 'nosotros'),
			);
		}

		if (is_user_logged_in()) {
			$links[] = array(
				'type' => 'account',
				'label' => self::get_user_first_name_label(),
				'key' => 'account',
				'children' => self::get_account_dropdown_links(),
			);
			$links[] = array('type' => 'cart', 'label' => 'BOLSA DE COMPRA', 'url' => self::get_cart_url(), 'key' => 'cart', 'icon' => 'shopping_bag');
			$links[] = array('type' => 'auth', 'label' => 'CERRAR SESIÓN', 'url' => wp_logout_url((string) home_url('/')), 'key' => 'auth', 'icon' => 'logout');
		} else {
			$links[] = array('type' => 'auth', 'label' => 'INICIAR SESIÓN', 'url' => '#rcp-auth', 'key' => 'auth');
		}

		return (array) apply_filters('rcp_main_nav_links', $links);
	}

	private static function get_nosotros_url(): string {
		$nosotros = get_page_by_path('nosotros');
		if ($nosotros && isset($nosotros->ID)) {
			$url = get_permalink((int) $nosotros->ID);
			if (is_string($url) && $url !== '') return (string) $url;
		}
		$quienes = get_page_by_path('quienes-somos');
		if ($quienes && isset($quienes->ID)) {
			$url = get_permalink((int) $quienes->ID);
			if (is_string($url) && $url !== '') return (string) $url;
		}
		return (string) home_url('/nosotros/');
	}

	private static function get_user_first_name_label(): string {
		if (!function_exists('wp_get_current_user')) return 'MI CUENTA';
		$user = wp_get_current_user();
		if (!$user || !isset($user->ID) || (int) $user->ID <= 0) return 'MI CUENTA';
		$first_name = isset($user->first_name) ? trim((string) $user->first_name) : '';
		if ($first_name !== '') return $first_name;
		$display = isset($user->display_name) ? trim((string) $user->display_name) : '';
		if ($display !== '') {
			$parts = preg_split('/\\s+/', $display);
			if (is_array($parts) && isset($parts[0]) && trim((string) $parts[0]) !== '') return trim((string) $parts[0]);
		}
		return 'MI CUENTA';
	}

	public static function get_cart_url(): string {
		if (function_exists('wc_get_cart_url')) {
			$url = (string) wc_get_cart_url();
			if ($url !== '') return $url;
		}
		return (string) home_url('/cart/');
	}

	public static function get_cart_count(): int {
		if (!function_exists('WC')) return 0;
		$wc = WC();
		if (!$wc || !isset($wc->cart) || !$wc->cart) return 0;
		if (!method_exists($wc->cart, 'get_cart_contents_count')) return 0;
		return (int) $wc->cart->get_cart_contents_count();
	}

	private static function get_account_dropdown_links(): array {
		$links = array();
		$cursos_url = self::get_my_account_tab_url('cursos');
		if ($cursos_url !== '') {
			$links[] = array('label' => 'MIS CURSOS', 'url' => $cursos_url, 'key' => 'my-courses');
		}
		if (function_exists('wc_get_page_permalink') && function_exists('wc_get_endpoint_url')) {
			$myaccount = (string) wc_get_page_permalink('myaccount');
			if ($myaccount !== '') {
				$links[] = array('label' => 'MIS COMPRAS', 'url' => (string) wc_get_endpoint_url('orders', '', $myaccount), 'key' => 'orders');
				$links[] = array('label' => 'DETALLES DE MI CUENTA', 'url' => (string) wc_get_endpoint_url('edit-account', '', $myaccount), 'key' => 'edit-account');
			}
		}
		return (array) apply_filters('rcp_account_dropdown_links', $links);
	}

	private static function get_my_account_tab_url(string $tab): string {
		if (!function_exists('wc_get_page_permalink')) return '';
		$myaccount = (string) wc_get_page_permalink('myaccount');
		if ($myaccount === '') return '';
		return (string) add_query_arg('tab', $tab, $myaccount);
	}

	private static function is_footer_navigation_block(array $block): bool {
		if (empty($block['attrs']) || !is_array($block['attrs'])) return false;
		$attrs = (array) $block['attrs'];
		$layout = isset($attrs['layout']) && is_array($attrs['layout']) ? (array) $attrs['layout'] : array();
		$orientation = isset($layout['orientation']) ? (string) $layout['orientation'] : '';
		$overlay_menu = isset($attrs['overlayMenu']) ? (string) $attrs['overlayMenu'] : '';
		return $orientation === 'vertical' && ($overlay_menu === '' || $overlay_menu === 'never');
	}

	private static function get_footer_menu_columns(): array {
		$shop_url = (string) home_url('/tienda/');
		$columns = array(
			array(
				array('label' => 'NOSOTROS', 'url' => self::get_nosotros_url(), 'key' => 'nosotros'),
				array('label' => 'ARTÍCULOS', 'url' => (string) home_url('/articulos/'), 'key' => 'articulos'),
				array('label' => 'CURSOS', 'url' => (string) home_url('/cursos/'), 'key' => 'cursos'),
				array('label' => 'CONTACTO', 'url' => (string) home_url('/contacto/'), 'key' => 'contacto'),
			),
			array(
				array('label' => 'FACEBOOK', 'url' => 'https://www.facebook.com/FRedCultural?_rdc=1&_rdr#', 'key' => 'facebook', 'new_tab' => true),
				array('label' => 'YOUTUBE', 'url' => 'https://www.youtube.com/channel/UCzcXD00Q645grTnWrmInGOg', 'key' => 'youtube', 'new_tab' => true),
				array('label' => 'INSTAGRAM', 'url' => 'https://www.instagram.com/red.cultural/', 'key' => 'instagram', 'new_tab' => true),
				array('label' => 'TWITTER', 'url' => 'https://x.com/red__cultural', 'key' => 'twitter', 'new_tab' => true),
			),
			array(
				array('label' => 'TIENDA', 'url' => $shop_url, 'key' => 'tienda'),
				array('label' => 'TÉRMINOS Y CONDICIONES', 'url' => (string) home_url('/terminos-y-condiciones/'), 'key' => 'terminos'),
			),
		);
		return (array) apply_filters('rcp_footer_menu_columns', $columns);
	}

	private static function is_internal_url(string $url): bool {
		$url = trim($url);
		if ($url === '' || strpos($url, '#') === 0 || strpos($url, '/') === 0) return true;
		$site_host = (string) parse_url((string) home_url('/'), PHP_URL_HOST);
		$url_host = (string) parse_url($url, PHP_URL_HOST);
		if ($site_host === '' || $url_host === '') return false;
		return strtolower($site_host) === strtolower($url_host);
	}
}
