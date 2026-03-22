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

		$cost = Red_Cultural_Shipping::determine_customer_region_cost();
		if ( null === $cost || $cost <= 0 ) {
			return;
		}

		$cart->add_fee( __( 'Envío', 'red-cultural-shipping' ), (float) $cost, false );
	}
}

