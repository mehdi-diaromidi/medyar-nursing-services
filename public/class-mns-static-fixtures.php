<?php
/**
 * Static fixture loader for UI-first phase (MNS_UI_STATIC).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Static_Fixtures {

	/**
	 * @param string               $key     Dot path, e.g. patient.dashboard.
	 * @param array<string,mixed>  $default Default when missing.
	 * @return array<string,mixed>
	 */
	public static function get( string $key, array $default = array() ): array {
		$key  = strtolower( trim( $key ) );
		$file = self::file_for_key( $key );
		if ( ! $file || ! is_readable( $file ) ) {
			return $default;
		}

		$data = require $file;
		return is_array( $data ) ? $data : $default;
	}

	/**
	 * Single catalog service by slug.
	 *
	 * @return array<string,mixed>
	 */
	public static function catalog_service( string $slug ): array {
		$all = self::get( 'catalog/services' );
		$slug = sanitize_key( $slug );
		if ( '' !== $slug && isset( $all[ $slug ] ) && is_array( $all[ $slug ] ) ) {
			return $all[ $slug ];
		}
		return array();
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public static function catalog_services(): array {
		$all = self::get( 'catalog/services' );
		$out = array();
		foreach ( $all as $key => $row ) {
			if ( is_array( $row ) ) {
				$out[ (string) $key ] = $row;
			}
		}
		return $out;
	}

	/**
	 * @param string $key Fixture key.
	 */
	private static function file_for_key( string $key ): string {
		$safe = preg_replace( '/[^a-z0-9.\/_-]/', '', $key );
		if ( ! is_string( $safe ) || '' === $safe ) {
			return '';
		}

		$relative = str_replace( '.', '/', $safe ) . '.php';
		$path     = MNS_PLUGIN_DIR . 'public/static-fixtures/' . $relative;
		return $path;
	}
}
