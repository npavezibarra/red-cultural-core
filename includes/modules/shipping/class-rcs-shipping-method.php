<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Shipping_Method extends WC_Shipping_Method {
	public function __construct( $instance_id = 0 ) {
		$this->id                 = Red_Cultural_Shipping::SHIPPING_METHOD_ID;
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Red Cultural (Regiones)', 'red-cultural-shipping' );
		$this->method_description = __( 'Shipping cost is based on the Región mapped from the selected Comuna.', 'red-cultural-shipping' );
		$this->supports           = array( 'shipping-zones', 'instance-settings' );

		$this->init();
	}

	public function init() {
		$this->instance_form_fields = array(
			'title' => array(
				'title'       => __( 'Title', 'red-cultural-shipping' ),
				'type'        => 'text',
				'description' => __( 'Label shown at checkout.', 'red-cultural-shipping' ),
				'default'     => __( 'Envío', 'red-cultural-shipping' ),
			),
		);

		$this->title = $this->get_option( 'title', __( 'Envío', 'red-cultural-shipping' ) );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function calculate_shipping( $package = array() ) {
		$destination = isset( $package['destination'] ) && is_array( $package['destination'] )
			? $package['destination']
			: array();

		$shipping_city = trim( (string) ( $destination['city'] ?? '' ) );
		$billing_city  = function_exists( 'WC' ) && WC()->customer ? trim( (string) WC()->customer->get_billing_city() ) : '';
		$city          = '' !== $shipping_city ? $shipping_city : $billing_city;

		$state_code = strtoupper( trim( (string) ( $destination['state'] ?? '' ) ) );
		if ( '' === $state_code && function_exists( 'WC' ) && WC()->customer ) {
			$state_code = strtoupper( trim( (string) WC()->customer->get_shipping_state() ) );
			if ( '' === $state_code ) {
				$state_code = strtoupper( trim( (string) WC()->customer->get_billing_state() ) );
			}
		}

		$cost = null;

		$cost = Red_Cultural_Shipping::determine_region_cost( $city, $state_code );

		if ( null === $cost ) {
			$cost = 0.0;
		}

		$rate = array(
			'id'    => $this->id,
			'label' => $this->title,
			'cost'  => (float) $cost,
		);

		$this->add_rate( $rate );
	}
}
