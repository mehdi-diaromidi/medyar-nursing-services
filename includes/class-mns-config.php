<?php
/**
 * Settings and temporary assumptions access.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Config {

	/** @var array<string,mixed>|null */
	private static $assumptions = null;

	/**
	 * @param string $key     Assumption key.
	 * @param mixed  $default Default when missing.
	 * @return mixed
	 */
	public static function assumption( string $key, $default = null ) {
		$all = self::assumptions();
		return array_key_exists( $key, $all ) ? $all[ $key ] : $default;
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function assumptions(): array {
		if ( null !== self::$assumptions ) {
			return self::$assumptions;
		}

		$path = MNS_PLUGIN_DIR . 'includes/config/assumptions.php';
		$data = is_readable( $path ) ? require $path : array();
		self::$assumptions = is_array( $data ) ? $data : array();

		return self::$assumptions;
	}

	/**
	 * @param string $key     Settings key.
	 * @param mixed  $default Default when missing.
	 * @return mixed
	 */
	public static function setting( string $key, $default = null ) {
		$settings = get_option( 'mns_settings', array() );
		if ( ! is_array( $settings ) || ! array_key_exists( $key, $settings ) ) {
			return $default;
		}
		return $settings[ $key ];
	}
}

/**
 * @param string $key     Assumption key.
 * @param mixed  $default Default when missing.
 * @return mixed
 */
function mns_assumption( string $key, $default = null ) {
	return MNS_Config::assumption( $key, $default );
}
