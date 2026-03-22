<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (!function_exists('rcp_ld_course_price_display')) {
	function rcp_ld_course_price_display(int $course_id): string {
		if (!function_exists('learndash_get_setting')) {
			return '';
		}

		$raw_price = (string) learndash_get_setting($course_id, 'course_price');
		if (trim($raw_price) === '') {
			$meta = get_post_meta($course_id, '_sfwd-courses', true);
			if (is_array($meta)) {
				foreach (array('sfwd-courses_course_price', 'course_price') as $key) {
					if (isset($meta[$key]) && is_scalar($meta[$key]) && trim((string) $meta[$key]) !== '') {
						$raw_price = (string) $meta[$key];
						break;
					}
				}
			}
		}

		$raw = trim($raw_price);
		if ($raw === '') {
			return '';
		}

		// Keep what the admin entered if it already contains separators like "$220,000".
		if (preg_match('/[\\,\\.]/', $raw) === 1) {
			return esc_html($raw);
		}

		$symbol = '';
		if (preg_match('/^\\s*([^0-9\\s]+)/u', $raw, $m) === 1) {
			$symbol = trim((string) $m[1]);
		}

		$digits = preg_replace('/[^0-9\\-]/', '', $raw);
		if ($digits === '' || !is_numeric($digits)) {
			return esc_html($raw);
		}

		$value = (int) $digits;
		$formatted = number_format($value, 0, '.', ',');

		if ($symbol === '') {
			$symbol = '$';
		}

		return esc_html($symbol . $formatted);
	}
}

if (!function_exists('rcp_ld_course_lessons')) {
	/**
	 * @return array<int, array>
	 */
	function rcp_ld_course_lessons(int $course_id, int $user_id): array {
		if (!function_exists('learndash_get_course_lessons_list')) {
			return array();
		}

		$items = learndash_get_course_lessons_list($course_id, $user_id, array('nopaging' => true, 'per_page' => 0));
		if (!is_array($items)) {
			return array();
		}

		return $items;
	}
}

