<?php
/**
 * Purchase Intent Helper.
 *
 * Manages purchase intent data via WC()->session so that guest users
 * can be redirected to the correct purchase flow after logging in or registering.
 *
 * Intent types:
 *   - full_course:     User clicked "Comprar Curso" (full course product).
 *   - partial_lessons: User selected individual lessons from the modal.
 */

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Purchase_Intent {

	/**
	 * Session key used to store purchase intent data.
	 */
	const SESSION_KEY = 'rc_purchase_intent';

	/* ------------------------------------------------------------------
	 * SET — store intent in the WC session (works for guests too).
	 * ----------------------------------------------------------------*/

	/**
	 * @param array $data {
	 *     @type string $type          'full_course' or 'partial_lessons'
	 *     @type int    $course_id     LearnDash course post ID
	 *     @type int    $product_id    (optional) WooCommerce product ID for full course
	 *     @type array  $lesson_ids    (optional) list of lesson post IDs
	 *     @type bool   $is_full_course (optional)
	 * }
	 */
	public static function set(array $data): void {
		$data['created_at'] = time();
		$data['prepared']   = false;

		if (self::has_wc_session()) {
			WC()->session->set(self::SESSION_KEY, $data);
			return;
		}

		// Fallback: use a cookie (JSON-encoded, 30 min expiry).
		$json = wp_json_encode($data);
		if ($json) {
			setcookie('rc_purchase_intent', $json, time() + 1800, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
			$_COOKIE['rc_purchase_intent'] = $json;
		}
	}

	/* ------------------------------------------------------------------
	 * GET — retrieve the current intent (or null if none).
	 * ----------------------------------------------------------------*/

	/**
	 * @return array|null
	 */
	public static function get(): ?array {
		if (self::has_wc_session()) {
			$data = WC()->session->get(self::SESSION_KEY);
			if (is_array($data) && !empty($data['type'])) {
				return $data;
			}
		}

		// Fallback: try cookie.
		if (!empty($_COOKIE['rc_purchase_intent'])) {
			$data = json_decode(wp_unslash($_COOKIE['rc_purchase_intent']), true);
			if (is_array($data) && !empty($data['type'])) {
				// Migrate cookie intent into session if session is now available.
				if (self::has_wc_session()) {
					WC()->session->set(self::SESSION_KEY, $data);
					self::clear_cookie();
				}
				return $data;
			}
		}

		return null;
	}

	/* ------------------------------------------------------------------
	 * CLEAR — remove intent from session and cookie.
	 * ----------------------------------------------------------------*/

	public static function clear(): void {
		if (self::has_wc_session()) {
			WC()->session->set(self::SESSION_KEY, null);
		}
		self::clear_cookie();
	}

	/* ------------------------------------------------------------------
	 * MARK AS PREPARED — flag that the cart has already been built.
	 * ----------------------------------------------------------------*/

	public static function mark_as_prepared(): void {
		$data = self::get();
		if (!$data) {
			return;
		}
		$data['prepared'] = true;
		if (self::has_wc_session()) {
			WC()->session->set(self::SESSION_KEY, $data);
		}
	}

	/* ------------------------------------------------------------------
	 * PREPARE CART — empties existing cart and adds the intent product.
	 * Returns true on success, WP_Error or false on failure.
	 * ----------------------------------------------------------------*/

	/**
	 * @return bool|\WP_Error
	 */
	public static function prepare_cart() {
		$intent = self::get();
		if (!$intent) {
			return new \WP_Error('no_intent', 'No purchase intent found.');
		}

		if (!empty($intent['prepared'])) {
			return true; // Already prepared.
		}

		if (!class_exists('WooCommerce') || is_null(WC()->cart)) {
			return new \WP_Error('wc_unavailable', 'WooCommerce cart is not available.');
		}

		WC()->cart->empty_cart();

		$type      = $intent['type'] ?? '';
		$course_id = absint($intent['course_id'] ?? 0);

		if ($type === 'full_course') {
			$product_id = absint($intent['product_id'] ?? 0);
			if (!$product_id) {
				// Try to resolve it from the course.
				if (function_exists('rcil_get_course_woo_product_id')) {
					$product_id = rcil_get_course_woo_product_id($course_id);
				}
			}
			if (!$product_id) {
				return new \WP_Error('no_product', 'Could not find a product for this course.');
			}
			WC()->cart->add_to_cart($product_id);

		} elseif ($type === 'partial_lessons') {
			$lesson_ids    = isset($intent['lesson_ids']) ? array_map('absint', (array) $intent['lesson_ids']) : [];
			$is_full_course = !empty($intent['is_full_course']);

			if (empty($lesson_ids) && !$is_full_course) {
				return new \WP_Error('no_lessons', 'No lessons in intent.');
			}

			// Use the same dynamic product creation as the existing AJAX handler.
			if (!class_exists('RCIL_WooCommerce') || !function_exists('rcil_get_course_lesson_price')) {
				return new \WP_Error('rcil_unavailable', 'RCIL module is not available.');
			}

			$unit_price        = rcil_get_course_lesson_price($course_id);
			$full_course_price = function_exists('rcil_get_full_course_price') ? rcil_get_full_course_price($course_id) : 0;

			// If it's a full-course intent via the lessons modal, use the native product.
			if ($is_full_course && $full_course_price > 0) {
				$woo_product_id = function_exists('rcil_get_course_woo_product_id') ? rcil_get_course_woo_product_id($course_id) : 0;
				if ($woo_product_id) {
					WC()->cart->add_to_cart($woo_product_id);
					self::mark_as_prepared();
					return true;
				}
				// Fallback to dynamic product with full course price.
				$total_price = $full_course_price;
			} else {
				// Filter out lessons the user already owns.
				$user_id = get_current_user_id();
				$filtered_ids    = [];
				$filtered_titles = [];
				foreach ($lesson_ids as $lid) {
					if ($user_id > 0 && function_exists('rcil_user_has_lesson_access') && rcil_user_has_lesson_access($user_id, $lid)) {
						continue;
					}
					$filtered_ids[]    = $lid;
					$filtered_titles[] = get_the_title($lid);
				}
				if (empty($filtered_ids)) {
					return new \WP_Error('already_owned', 'All selected lessons are already owned.');
				}
				$lesson_ids    = $filtered_ids;
				$total_price   = count($lesson_ids) * $unit_price;
			}

			$lesson_titles = [];
			foreach ($lesson_ids as $lid) {
				$lesson_titles[] = get_the_title($lid);
			}

			$product_id = RCIL_WooCommerce::get_instance()->get_or_create_dynamic_product([
				'course_id'      => $course_id,
				'lesson_ids'     => $lesson_ids,
				'lesson_titles'  => $lesson_titles,
				'total_price'    => $total_price,
				'unit_price'     => $unit_price,
				'is_full_course' => $is_full_course,
			]);

			if (is_wp_error($product_id)) {
				return $product_id;
			}

			WC()->cart->add_to_cart($product_id, 1, 0, [], [
				'_is_rcil_purchase'     => true,
				'_rcil_course_id'       => $course_id,
				'_rcil_lesson_ids'      => $lesson_ids,
				'_rcil_lesson_titles'   => $lesson_titles,
				'_rcil_per_lesson_price' => $unit_price,
				'_rcil_is_full_course'  => $is_full_course,
			]);
		} else {
			return new \WP_Error('unknown_type', 'Unknown intent type: ' . $type);
		}

		self::mark_as_prepared();
		return true;
	}

	/* ------------------------------------------------------------------
	 * Helpers.
	 * ----------------------------------------------------------------*/

	private static function has_wc_session(): bool {
		return (function_exists('WC') && WC()->session instanceof \WC_Session);
	}

	private static function clear_cookie(): void {
		if (isset($_COOKIE['rc_purchase_intent'])) {
			setcookie('rc_purchase_intent', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
			unset($_COOKIE['rc_purchase_intent']);
		}
	}
}
