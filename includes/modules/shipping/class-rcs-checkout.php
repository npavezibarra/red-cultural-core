<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Checkout {
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'woocommerce_checkout_posted_data', array( __CLASS__, 'sync_checkout_posted_data' ), 20, 1 );
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'validate_communes' ), 20, 2 );
		add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'sync_customer_from_order_review' ), 5, 1 );
	}

	private static function log( $message, array $context = array() ) {
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			return;
		}

		$payload = $context ? ' ' . wp_json_encode( $context ) : '';
		error_log( 'RCS_Checkout: ' . $message . $payload );
	}

	public static function enqueue_assets() {
		if ( ! function_exists( 'is_account_page' ) ) {
			return;
		}

		if ( ! is_checkout() && ! is_account_page() ) {
			return;
		}

		if ( is_order_received_page() ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-autocomplete' );

		wp_enqueue_style( 'rcs-checkout', RCS_PLUGIN_URL . 'assets/rcs-checkout.css', array(), '0.1.0' );

		$handle = 'rcs-checkout';
		$src    = RCS_PLUGIN_URL . 'assets/rcs-checkout.js';

		wp_enqueue_script( $handle, $src, array( 'jquery', 'jquery-ui-autocomplete' ), '0.1.0', true );

		$states = array();
		if ( function_exists( 'WC' ) && WC() && WC()->countries ) {
			$states = (array) WC()->countries->get_states( 'CL' );
		}

		wp_localize_script(
			$handle,
			'rcsCheckout',
			array(
				'communesUrl'          => RC_CORE_URL . 'assets/communes.json',
				'states'               => $states,
				'regionPrices'         => Red_Cultural_Shipping::get_region_prices(),
				'currencySymbol'       => get_woocommerce_currency_symbol(),
				'currencyDecimalSep'   => wc_get_price_decimal_separator(),
				'currencyThousandSep'  => wc_get_price_thousand_separator(),
				'currencyDecimals'     => wc_get_price_decimals(),
			)
		);
	}

	public static function validate_communes( $data, $errors ) {
		if ( ! is_array( $data ) ) {
			return;
		}

		if ( ! $errors instanceof WP_Error ) {
			return;
		}

		$ship_to_different = ! empty( $data['ship_to_different_address'] ) && '1' === (string) $data['ship_to_different_address'];

		$billing_city  = isset( $data['billing_city'] ) ? wc_clean( wp_unslash( $data['billing_city'] ) ) : '';
		$shipping_city = isset( $data['shipping_city'] ) ? wc_clean( wp_unslash( $data['shipping_city'] ) ) : '';

		$catalog = RCS_Communes::get_catalog();

		$check_value = function( $value, $label ) use ( $errors, $catalog ) {
			$value = trim( (string) $value );
			if ( '' === $value ) {
				return;
			}

			$normalized = RCS_Communes::normalize( $value );
			if ( ! isset( $catalog['by_name'][ $normalized ] ) ) {
				$errors->add( 'rcs_invalid_' . sanitize_key( $label ), sprintf( __( '%s debe ser una comuna válida.', 'red-cultural-shipping' ), $label ) );
			}
		};

		$check_value( $billing_city, __( 'Comuna (facturación)', 'red-cultural-shipping' ) );

		if ( $ship_to_different ) {
			$check_value( $shipping_city, __( 'Comuna (envío)', 'red-cultural-shipping' ) );
		} elseif ( '' !== $billing_city && '' === $shipping_city ) {
			// If the theme uses billing_city as comuna, accept it even if shipping_city is empty.
			$check_value( $billing_city, __( 'Comuna (envío)', 'red-cultural-shipping' ) );
		}
	}

	public static function sync_checkout_posted_data( $data ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		$ship_to_different = ! empty( $data['ship_to_different_address'] ) && '1' === (string) $data['ship_to_different_address'];
		$billing_city      = isset( $data['billing_city'] ) ? wc_clean( wp_unslash( $data['billing_city'] ) ) : '';
		$shipping_city     = isset( $data['shipping_city'] ) ? wc_clean( wp_unslash( $data['shipping_city'] ) ) : '';

		if ( '' !== $billing_city ) {
			$data['billing_city'] = $billing_city;
		}

		if ( $ship_to_different ) {
			if ( '' !== $shipping_city ) {
				$data['shipping_city'] = $shipping_city;
			}
		} elseif ( '' !== $billing_city ) {
			$data['shipping_city'] = $billing_city;
			$shipping_city         = $billing_city;
		}

		$billing_state = Red_Cultural_Shipping::resolve_state_code_from_city( $billing_city );
		$shipping_base = '' !== $shipping_city ? $shipping_city : $billing_city;
		$shipping_state = Red_Cultural_Shipping::resolve_state_code_from_city( $shipping_base );

		if ( '' !== $billing_state ) {
			$data['billing_state'] = $billing_state;
		}

		if ( '' !== $shipping_state ) {
			$data['shipping_state'] = $shipping_state;
		}

		if ( empty( $data['billing_country'] ) ) {
			$data['billing_country'] = 'CL';
		}

		if ( empty( $data['shipping_country'] ) ) {
			$data['shipping_country'] = 'CL';
		}

		self::log(
			'sync_checkout_posted_data',
			array(
				'billing_city'   => $billing_city,
				'shipping_city'  => $shipping_base,
				'billing_state'  => $billing_state,
				'shipping_state' => $shipping_state,
				'ship_to_diff'   => $ship_to_different ? '1' : '0',
			)
		);

		return $data;
	}

	/**
	 * WooCommerce calculates shipping based on the customer session (shipping/billing fields).
	 * Some themes/custom checkouts don't fully keep shipping_* in sync when using billing_city as "Comuna".
	 * This ensures the WC customer has the right comuna (city) + derived región (state) before totals are calculated.
	 */
	public static function sync_customer_from_order_review( $posted_data ) {
		if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->customer ) {
			return;
		}

		if ( ! is_string( $posted_data ) || '' === $posted_data ) {
			return;
		}

		$data = array();
		parse_str( $posted_data, $data );

		if ( ! is_array( $data ) ) {
			return;
		}

		$ship_to_different = ! empty( $data['ship_to_different_address'] ) && '1' === (string) $data['ship_to_different_address'];

		$billing_city  = isset( $data['billing_city'] ) ? wc_clean( wp_unslash( $data['billing_city'] ) ) : '';
		$shipping_city = isset( $data['shipping_city'] ) ? wc_clean( wp_unslash( $data['shipping_city'] ) ) : '';

		if ( '' !== $billing_city ) {
			WC()->customer->set_billing_city( $billing_city );
		}

		if ( $ship_to_different ) {
			if ( '' !== $shipping_city ) {
				WC()->customer->set_shipping_city( $shipping_city );
			}
		} elseif ( '' !== $billing_city ) {
			WC()->customer->set_shipping_city( $billing_city );
			$shipping_city = $billing_city;
		}

		// Force country CL if empty (common in customized checkouts).
		if ( '' === (string) WC()->customer->get_billing_country() ) {
			WC()->customer->set_billing_country( 'CL' );
		}
		if ( '' === (string) WC()->customer->get_shipping_country() ) {
			WC()->customer->set_shipping_country( 'CL' );
		}

		// Derive región (state) from comuna and set it on the customer.
		$commune = '' !== $shipping_city ? $shipping_city : $billing_city;
		if ( '' !== $commune ) {
			$entry = RCS_Communes::find_by_name( $commune );
			if ( $entry && ! empty( $entry['region_name'] ) ) {
				$state_code = RCS_Communes::map_region_name_to_state_code( $entry['region_name'] );
				if ( '' !== $state_code ) {
					WC()->customer->set_shipping_state( $state_code );
					if ( ! $ship_to_different ) {
						WC()->customer->set_billing_state( $state_code );
					}
				}
			}
		}

		self::log(
			'sync_customer_from_order_review',
			array(
				'billing_city'            => $billing_city,
				'shipping_city'           => $shipping_city,
				'commune_used'            => $commune,
				'customer_billing_state'  => WC()->customer->get_billing_state(),
				'customer_shipping_state' => WC()->customer->get_shipping_state(),
				'customer_billing_city'   => WC()->customer->get_billing_city(),
				'customer_shipping_city'  => WC()->customer->get_shipping_city(),
			)
		);

		WC()->customer->save();
	}
}
