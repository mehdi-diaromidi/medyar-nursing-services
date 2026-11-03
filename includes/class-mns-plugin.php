<?php
/**
 * Main plugin bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Plugin {

	/** @var self|null */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		MNS_Activator::maybe_upgrade();
		MNS_Roles::register();

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		if ( is_admin() ) {
			MNS_Admin::instance()->init();
		}

		MNS_Public::instance()->init();
		MNS_Assets::init();
		MNS_Rewrite::instance()->init();
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'medyar-nursing-services',
			false,
			dirname( MNS_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Whether front-end should serve static fixtures.
	 * Constant false always wins (production kill-switch).
	 */
	public static function is_ui_static(): bool {
		if ( defined( 'MNS_UI_STATIC' ) && ! MNS_UI_STATIC ) {
			return false;
		}

		$settings = get_option( 'mns_settings', array() );
		if ( is_array( $settings ) && array_key_exists( 'ui_static', $settings ) ) {
			return (bool) $settings['ui_static'];
		}

		return defined( 'MNS_UI_STATIC' ) ? (bool) MNS_UI_STATIC : true;
	}
}
