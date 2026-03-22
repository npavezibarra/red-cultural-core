<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Account {
	public static function init() {
		add_action( 'woocommerce_customer_save_address', array( __CLASS__, 'sync_state_from_city' ), 10, 2 );
	}

	/**
	 * Automatically update the customer state/region based on the city (Comuna)
	 * when an address is saved in My Account.
	 */
	public static function sync_state_from_city( $user_id, $address_type ) {
		if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->customer ) {
			return;
		}

		$customer = new WC_Customer( $user_id );
		if ( ! $customer ) {
			return;
		}

		$city = '';
		if ( 'billing' === $address_type ) {
			$city = $customer->get_billing_city();
		} elseif ( 'shipping' === $address_type ) {
			$city = $customer->get_shipping_city();
		}

		if ( '' === $city ) {
			return;
		}

		$state_code = Red_Cultural_Shipping::resolve_state_code_from_city( $city );
		if ( '' === $state_code ) {
			return;
		}

		if ( 'billing' === $address_type ) {
			$customer->set_billing_state( $state_code );
		} else {
			$customer->set_shipping_state( $state_code );
		}

		$customer->save();
	}
}
