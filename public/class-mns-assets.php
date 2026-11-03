<?php
/**
 * Asset registration and conditional enqueue.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Assets {

	public const HANDLE_PUBLIC = 'mns-public';
	public const HANDLE_ADMIN  = 'mns-admin';

	public static function init(): void {
		// Front assets are enqueued explicitly when an MNS screen renders (phase 3).
		// Admin enqueue is triggered from MNS_Admin on mns_* screens.
	}

	public static function enqueue_public(): void {
		wp_enqueue_style(
			self::HANDLE_PUBLIC,
			MNS_PLUGIN_URL . 'assets/css/public.css',
			array(),
			MNS_VERSION
		);

		wp_enqueue_script(
			self::HANDLE_PUBLIC,
			MNS_PLUGIN_URL . 'assets/js/public.js',
			array(),
			MNS_VERSION,
			true
		);

		wp_localize_script(
			self::HANDLE_PUBLIC,
			'mnsPublic',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'restUrl'  => esc_url_raw( rest_url( 'mns/v1' ) ),
				'nonce'    => wp_create_nonce( 'mns_public' ),
				'uiStatic' => MNS_Plugin::is_ui_static(),
			)
		);
	}

	public static function enqueue_admin(): void {
		wp_enqueue_style(
			self::HANDLE_ADMIN,
			MNS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			MNS_VERSION
		);

		wp_enqueue_script(
			self::HANDLE_ADMIN,
			MNS_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			MNS_VERSION,
			true
		);
	}
}
