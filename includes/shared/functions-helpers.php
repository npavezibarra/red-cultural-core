<?php
/**
 * Shared helper functions for Red Cultural Core.
 *
 * @package RedCulturalCore
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('rcp_is_local_environment')) {
	/**
	 * Detect whether the current site is running on a local domain.
	 *
	 * @return bool
	 */
	function rcp_is_local_environment()
	{
		$home_url = (string) home_url();
		$host = (string) wp_parse_url($home_url, PHP_URL_HOST);
		$host = strtolower(trim($host));

		$local_domains = apply_filters(
			'rcp_local_domains',
			array_filter(
				array_map('strtolower', array_map('trim', array('redcultural.local'))),
				static function ($value) {
					return $value !== '';
				}
			)
		);

		if ($host === '') {
			return false;
		}

		if (in_array($host, $local_domains, true)) {
			return true;
		}

		if (substr($host, -6) === '.local') {
			return true;
		}

	return false;
}
}

if (function_exists('add_filter')) {
	add_filter('cfturnstile_widget_disable', function ($disabled) {
		if (function_exists('rcp_is_local_environment') && rcp_is_local_environment()) {
			return true;
		}
		return $disabled;
	});
}
