<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Admin {
	private static function field_hash_for_code( $code ) {
		$code = (string) $code;
		return substr( sha1( 'rcs|' . $code ), 0, 12 );
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
	}

	public static function register_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Envío por Región', 'red-cultural-shipping' ),
			__( 'Envío por Región', 'red-cultural-shipping' ),
			'manage_woocommerce',
			'rcs-region-shipping',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$states = function_exists( 'WC' ) && WC()->countries ? (array) WC()->countries->get_states( 'CL' ) : array();
		$prices = Red_Cultural_Shipping::get_region_prices();

		$updated = false;

		if ( isset( $_POST['rcs_action'] ) && 'save' === (string) $_POST['rcs_action'] ) {
			check_admin_referer( 'rcs_save_region_prices', 'rcs_nonce' );

			$new_prices = array();
			$posted_prices = isset( $_POST['rcs_price'] ) && is_array( $_POST['rcs_price'] ) ? (array) $_POST['rcs_price'] : array();
			$posted_codes  = isset( $_POST['rcs_price_code'] ) && is_array( $_POST['rcs_price_code'] ) ? (array) $_POST['rcs_price_code'] : array();

			foreach ( $states as $code => $label ) {
				$hash = self::field_hash_for_code( $code );

				if ( ! isset( $posted_prices[ $hash ] ) || ! isset( $posted_codes[ $hash ] ) ) {
					continue;
				}

				$original_code = (string) wp_unslash( $posted_codes[ $hash ] );

				if ( (string) $original_code !== (string) $code ) {
					continue;
				}

				$raw = trim( (string) wp_unslash( $posted_prices[ $hash ] ) );
				if ( '' === $raw ) {
					continue;
				}

				$value = (float) str_replace( array( '.', ',' ), array( '', '.' ), preg_replace( '/[^0-9\.,-]/', '', $raw ) );
				if ( $value < 0 ) {
					$value = 0;
				}

				$new_prices[ strtoupper( (string) $code ) ] = $value;
			}

			update_option( Red_Cultural_Shipping::OPTION_REGION_PRICES, $new_prices, false );
			$prices  = $new_prices;
			$updated = true;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Envío por Región', 'red-cultural-shipping' ) . '</h1>';

		if ( $updated ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Precios guardados.', 'red-cultural-shipping' ) . '</p></div>';
		}

		if ( empty( $states ) ) {
			echo '<p>' . esc_html__( 'No se pudieron cargar las regiones de Chile desde WooCommerce.', 'red-cultural-shipping' ) . '</p>';
			echo '</div>';
			return;
		}

		echo '<form method="post">';
		echo '<input type="hidden" name="rcs_action" value="save" />';
		wp_nonce_field( 'rcs_save_region_prices', 'rcs_nonce' );

		echo '<table class="widefat striped" style="max-width: 900px;">';
		echo '<thead><tr><th>' . esc_html__( 'Región (estado)', 'red-cultural-shipping' ) . '</th><th style="width:220px;">' . esc_html__( 'Precio', 'red-cultural-shipping' ) . '</th></tr></thead>';
		echo '<tbody>';

		foreach ( $states as $code => $label ) {
			$hash = self::field_hash_for_code( $code );
			$code_display = strtoupper( (string) $code );
			$value = isset( $prices[ $code_display ] ) ? (string) $prices[ $code_display ] : '';

			echo '<tr>';
			echo '<td>' . esc_html( $label ) . ' <code style="opacity:.75;">(' . esc_html( $code_display ) . ')</code></td>';
			echo '<td>';
			echo '<input type="hidden" name="' . esc_attr( 'rcs_price_code[' . $hash . ']' ) . '" value="' . esc_attr( (string) $code ) . '" />';
			echo '<input class="regular-text" type="text" name="' . esc_attr( 'rcs_price[' . $hash . ']' ) . '" value="' . esc_attr( $value ) . '" placeholder="0" />';
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		submit_button( __( 'Guardar', 'red-cultural-shipping' ) );
		echo '</form>';
		echo '</div>';
	}
}
