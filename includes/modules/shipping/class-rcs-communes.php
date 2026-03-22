<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RCS_Communes {
	private static $catalog = null;
	private static $region_state_map = null;

	public static function normalize( $value ) {
		$value = (string) $value;
		$value = remove_accents( $value );
		$value = strtoupper( $value );
		$value = preg_replace( '/[^A-Z0-9 ]+/', ' ', $value );
		$value = preg_replace( '/\s+/', ' ', $value );
		return trim( $value );
	}

	public static function get_catalog() {
		if ( null !== self::$catalog ) {
			return self::$catalog;
		}

		self::$catalog = array(
			'by_name' => array(),
			'by_id'   => array(),
		);

		$file = RC_CORE_PATH . 'assets/communes.json';

		if ( ! file_exists( $file ) ) {
			$fallback = WP_PLUGIN_DIR . '/woo-check-new/includes/communes.json';
			if ( file_exists( $fallback ) ) {
				$file = $fallback;
			}
		}

		if ( ! file_exists( $file ) ) {
			return self::$catalog;
		}

		$data = json_decode( file_get_contents( $file ), true );
		if ( ! is_array( $data ) ) {
			return self::$catalog;
		}

		foreach ( $data as $entry ) {
			if ( empty( $entry['id'] ) || empty( $entry['name'] ) ) {
				continue;
			}

			$id = (int) $entry['id'];
			$name = (string) $entry['name'];

			$region_id   = isset( $entry['region_id'] ) ? (int) $entry['region_id'] : null;
			$region_name = isset( $entry['region_name'] ) ? (string) $entry['region_name'] : '';

			$payload = array(
				'id'          => $id,
				'name'        => $name,
				'region_id'   => $region_id,
				'region_name' => $region_name,
			);

			self::$catalog['by_id'][ $id ] = $payload;
			self::$catalog['by_name'][ self::normalize( $name ) ] = $payload;
		}

		return self::$catalog;
	}

	public static function find_by_name( $commune_name ) {
		$commune_name = trim( (string) $commune_name );
		if ( '' === $commune_name ) {
			return null;
		}

		$catalog = self::get_catalog();
		$key = self::normalize( $commune_name );

		return $catalog['by_name'][ $key ] ?? null;
	}

	public static function map_region_name_to_state_code( $region_name ) {
		if ( '' === trim( (string) $region_name ) ) {
			return '';
		}

		if ( null === self::$region_state_map ) {
			self::$region_state_map = array();

			if ( function_exists( 'WC' ) && WC() && WC()->countries ) {
				$states = (array) WC()->countries->get_states( 'CL' );
				foreach ( $states as $code => $label ) {
					self::$region_state_map[ self::normalize( $label ) ] = $code;
				}

				$aliases = array(
					'OHIGGINS'                          => "Libertador General Bernardo O'Higgins",
					'LIBERTADOR GENERAL BERNARDO OHIGGINS' => "Libertador General Bernardo O'Higgins",
					'METROPOLITANA'                     => 'Región Metropolitana de Santiago',
					'METROPOLITANA DE SANTIAGO'         => 'Región Metropolitana de Santiago',
					'REGION METROPOLITANA'              => 'Región Metropolitana de Santiago',
				);

				foreach ( $aliases as $alias => $canonical ) {
					$normalized_alias = self::normalize( $alias );
					$normalized_canonical = self::normalize( $canonical );

					if ( isset( self::$region_state_map[ $normalized_canonical ] ) ) {
						self::$region_state_map[ $normalized_alias ] = self::$region_state_map[ $normalized_canonical ];
					}
				}
			}
		}

		$normalized = self::normalize( $region_name );
		if ( isset( self::$region_state_map[ $normalized ] ) ) {
			return self::$region_state_map[ $normalized ];
		}

		foreach ( self::$region_state_map as $normalized_label => $code ) {
			if ( false !== strpos( $normalized_label, $normalized ) || false !== strpos( $normalized, $normalized_label ) ) {
				return $code;
			}
		}

		return '';
	}
}

