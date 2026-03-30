<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Fees {
	public static function init() {
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'maybe_add_region_fee' ), 20, 1 );
	}

	/**
	 * If the cart doesn't need shipping (e.g. virtual products), WooCommerce won't
	 * calculate any shipping method totals. In that case we add a fee so "Envío"
	 * is still charged based on the Comuna → Región mapping.
	 */
	public static function maybe_add_region_fee( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( ! $cart instanceof WC_Cart ) {
			return;
		}

		if ( $cart->needs_shipping() ) {
			return;
		}

		// Don't add shipping fee if ALL items are course/lesson products.
		$all_course_items = true;
		foreach ( $cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'] ?? null;
			if ( ! $product || ! is_object( $product ) ) {
				continue;
			}
			$pid = $product->get_id();

			// Check if it's a course-related product (RCIL dynamic, linked to LD course, or course category)
			$is_course_product = false;

			// RCIL dynamic product
			if ( get_post_meta( $pid, '_rcil_is_dynamic_product', true ) ) {
				$is_course_product = true;
			}
			// Explicitly linked to a LearnDash course
			if ( ! $is_course_product && ( get_post_meta( $pid, '_related_course_id', true ) || get_post_meta( $pid, '_related_course', true ) ) ) {
				$is_course_product = true;
			}
			// Linked via LearnDash WooCommerce integration
			if ( ! $is_course_product && function_exists( 'rcil_get_course_woo_product_id' ) ) {
				global $wpdb;
				$like_int = '%i:' . $pid . ';%';
				$like_str = '%"' . $pid . '"%';
				$like_url = '%add-to-cart=' . $pid . '%';
				$found = $wpdb->get_var( $wpdb->prepare(
					"SELECT post_id FROM $wpdb->postmeta 
					WHERE meta_key IN ('_pcg_woo_product_id','learndash_woocommerce_product_ids','_learndash_woocommerce_product_ids','_sfwd-courses') 
					AND (meta_value = %s OR meta_value LIKE %s OR meta_value LIKE %s OR meta_value LIKE %s) LIMIT 1",
					$pid, $like_int, $like_str, $like_url
				) );
				if ( $found ) {
					$is_course_product = true;
				}
			}
			// Product in 'cursos' category
			if ( ! $is_course_product && has_term( array( 'curso', 'cursos', 'course', 'courses' ), 'product_cat', $pid ) ) {
				$is_course_product = true;
			}
			// Cart item meta from RCIL
			if ( ! $is_course_product && ! empty( $cart_item['_is_rcil_purchase'] ) ) {
				$is_course_product = true;
			}

			if ( ! $is_course_product ) {
				$all_course_items = false;
				break;
			}
		}

		if ( $all_course_items ) {
			return; // All items are courses/lessons — no shipping fee.
		}

		$cost = Red_Cultural_Shipping::determine_customer_region_cost();
		if ( null === $cost || $cost <= 0 ) {
			return;
		}

		$cart->add_fee( __( 'Envío', 'red-cultural-shipping' ), (float) $cost, false );
	}
}

