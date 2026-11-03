<?php
/**
 * Activation / deactivation / lightweight upgrades (no domain tables in phase 0).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Activator {

	public static function activate(): void {
		MNS_Roles::register();

		if ( false === get_option( 'mns_settings', false ) ) {
			add_option( 'mns_settings', self::default_settings() );
		}

		update_option( 'mns_db_version', MNS_DB_VERSION );
	}

	public static function deactivate(): void {
		MNS_Roles::remove_caps_on_deactivate();
	}

	public static function maybe_upgrade(): void {
		$current = (string) get_option( 'mns_db_version', '' );
		if ( $current === MNS_DB_VERSION ) {
			return;
		}

		MNS_Roles::register();
		update_option( 'mns_db_version', MNS_DB_VERSION );
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function default_settings(): array {
		return array(
			'assignment_mode' => 'manual',
			'ui_static'       => true,
			'assumptions_ver' => 1,
		);
	}
}
