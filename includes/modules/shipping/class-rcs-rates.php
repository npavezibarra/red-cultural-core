<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Rates {
	private static function log( $message, array $context = array() ) {
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			return;
		}

		$payload = $context ? ' ' . wp_json_encode( $context ) : '';
		error_log( 'RCS_Rates: ' . $message . $payload );
	}

	public static function init() {
		// Ensure even "Free shipping" / other methods reflect the Región price.
		add_filter( 'woocommerce_package_rates', array( __CLASS__, 'override_rates_with_region_cost' ), 9999, 2 );
		// Last-resort: force the shipping total used by templates/totals to be the Región cost.
		add_filter( 'woocommerce_cart_get_shipping_total', array( __CLASS__, 'override_cart_shipping_total' ), 9999, 1 );
	}

	public static function override_rates_with_region_cost( $rates, $package ) {
		if ( empty( $rates ) || ! is_array( $rates ) ) {
			return $rates;
		}

		$destination = isset( $package['destination'] ) && is_array( $package['destination'] ) ? $package['destination'] : array();
		$country     = strtoupper( trim( (string) ( $destination['country'] ?? '' ) ) );

		// Only enforce for Chile.
		if ( '' !== $country && 'CL' !== $country ) {
			return $rates;
		}

		$city  = trim( (string) ( $destination['city'] ?? '' ) );
		$state = strtoupper( trim( (string) ( $destination['state'] ?? '' ) ) );

		$cost = Red_Cultural_Shipping::determine_region_cost( $city, $state );
		if ( null === $cost ) {
			$cost = Red_Cultural_Shipping::determine_customer_region_cost();
		}

		if ( null === $cost ) {
			self::log(
				'override_rates_with_region_cost skipped',
				array(
					'city'   => $city,
					'state'  => $state,
					'rates'  => array_keys( $rates ),
				)
			);
			return $rates;
		}

		$cost = max( 0, (float) $cost );
		self::log(
			'override_rates_with_region_cost',
			array(
				'city'  => $city,
				'state' => $state,
				'cost'  => $cost,
				'rates' => array_keys( $rates ),
			)
		);

		foreach ( $rates as $rate_id => $rate ) {
			if ( ! is_object( $rate ) ) {
				continue;
			}

			$method_id = '';
			if ( isset( $rate->method_id ) ) {
				$method_id = (string) $rate->method_id;
			} elseif ( method_exists( $rate, 'get_method_id' ) ) {
				$method_id = (string) $rate->get_method_id();
			}

			// Don't touch local pickup.
			if ( 'local_pickup' === $method_id ) {
				continue;
			}

			if ( method_exists( $rate, 'set_cost' ) ) {
				$rate->set_cost( $cost );
			} else {
				$rate->cost = $cost;
			}

			if ( method_exists( $rate, 'set_taxes' ) ) {
				$rate->set_taxes( array() );
			}

			$rates[ $rate_id ] = $rate;
		}

		return $rates;
	}

	public static function override_cart_shipping_total( $total ) {
		if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->customer ) {
			return $total;
		}

		// Don't override local pickup.
		$chosen = WC()->session ? WC()->session->get( 'chosen_shipping_methods' ) : array();
		if ( is_array( $chosen ) && ! empty( $chosen[0] ) && 0 === strpos( (string) $chosen[0], 'local_pickup' ) ) {
			return $total;
		}

		$country = strtoupper( trim( (string) WC()->customer->get_shipping_country() ) );
		if ( '' === $country ) {
			$country = strtoupper( trim( (string) WC()->customer->get_billing_country() ) );
		}

		if ( '' !== $country && 'CL' !== $country ) {
			return $total;
		}

		$cost = Red_Cultural_Shipping::determine_customer_region_cost();
		if ( null === $cost ) {
			self::log(
				'override_cart_shipping_total skipped',
				array(
					'total'           => $total,
					'shipping_city'   => WC()->customer->get_shipping_city(),
					'billing_city'    => WC()->customer->get_billing_city(),
					'shipping_state'  => WC()->customer->get_shipping_state(),
					'billing_state'   => WC()->customer->get_billing_state(),
				)
			);
			return $total;
		}

		$cost = max( 0, (float) $cost );
		self::log(
			'override_cart_shipping_total',
			array(
				'total_before'    => $total,
				'total_after'     => $cost,
				'shipping_city'   => WC()->customer->get_shipping_city(),
				'billing_city'    => WC()->customer->get_billing_city(),
				'shipping_state'  => WC()->customer->get_shipping_state(),
				'billing_state'   => WC()->customer->get_billing_state(),
			)
		);

		return $cost;
	}
}
