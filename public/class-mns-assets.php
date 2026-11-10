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

	/**
	 * Cache-busting version: the host serves assets with a one-year max-age, so the URL must change
	 * whenever a file does (plugin version + file mtime).
	 */
	private static function asset_ver( string $relative ): string {
		$path = MNS_PLUGIN_DIR . $relative;
		return is_readable( $path ) ? MNS_VERSION . '.' . (string) filemtime( $path ) : MNS_VERSION;
	}

	public static function enqueue_public(): void {
		wp_enqueue_style(
			self::HANDLE_PUBLIC,
			MNS_PLUGIN_URL . 'assets/css/public.css',
			array(),
			self::asset_ver( 'assets/css/public.css' )
		);

		wp_enqueue_script(
			self::HANDLE_PUBLIC,
			MNS_PLUGIN_URL . 'assets/js/public.js',
			array(),
			self::asset_ver( 'assets/js/public.js' ),
			true
		);

		wp_localize_script(
			self::HANDLE_PUBLIC,
			'mnsPublic',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'restUrl'   => esc_url_raw( rest_url( 'mns/v1' ) ),
				'nonce'     => wp_create_nonce( 'mns_public' ),
				'uiStatic'  => MNS_Plugin::is_ui_static(),
				'devTools'  => class_exists( 'MNS_Dev' ) && MNS_Dev::enabled(),
				'i18n'      => MNS_Copy::for_js(),
				'calendar'  => MNS_Format::js_calendar(),
				'supportTel'=> MNS_UI::support_tel_href(),
			)
		);

		if ( 'patient' === MNS_Pages::current_panel() && 'new-order' === MNS_Pages::current_page( 'patient' ) ) {
			self::enqueue_jalali_datepicker();
		}

		if ( 'patient' === MNS_Pages::current_panel() && in_array( MNS_Pages::current_page( 'patient' ), array( 'orders', 'invoices', 'invoice-view' ), true ) ) {
			self::enqueue_arp_list_kit();
		}
	}

	/**
	 * Jalali datepicker (Persian WooCommerce's persianDatepicker, themed "mns" in public.css) for the
	 * order wizard's date field. Without Persian WooCommerce the field accepts a typed, validated date.
	 */
	private static function enqueue_jalali_datepicker(): void {
		if ( ! function_exists( 'PW' ) || ! defined( 'PW_VERSION' ) ) {
			return;
		}
		wp_enqueue_script( 'pw-datepicker-js', PW()->plugin_url( 'assets/js/persian-datepicker.min.js' ), array( 'jquery' ), PW_VERSION, true );
		// The wizard script must load after the picker (and jQuery).
		global $wp_scripts;
		if ( isset( $wp_scripts->registered[ self::HANDLE_PUBLIC ] ) ) {
			$wp_scripts->registered[ self::HANDLE_PUBLIC ]->deps = array_merge( $wp_scripts->registered[ self::HANDLE_PUBLIC ]->deps, array( 'jquery', 'pw-datepicker-js' ) );
		}
		// The theme's babakhani picker redefines the global `persianDate` the PW picker needs.
		add_action(
			'wp_enqueue_scripts',
			static function () {
				wp_dequeue_script( 'drplus-pdp' );
				wp_dequeue_script( 'drplus-pd' );
				wp_dequeue_style( 'drplus-pdp' );
			},
			1000
		);
	}

	/**
	 * «My orders» reuses the affiliate panel's filter form, table and pager
	 * (`.arp-refs-filters`, `.arp-table`, `.arp-refs-pager`) with its CSS + JS
	 * (Jalali pickers, custom selects, auto-submit). Without the ARP plugin the
	 * form still works with native selects and typed dates.
	 */
	private static function enqueue_arp_list_kit(): void {
		if ( ! defined( 'ARP_PLUGIN_URL' ) || ! defined( 'ARP_PLUGIN_DIR' ) ) {
			return;
		}
		$css_path = ARP_PLUGIN_DIR . 'assets/css/public.css';
		$js_path  = ARP_PLUGIN_DIR . 'assets/js/public.js';
		if ( ! is_readable( $css_path ) || ! is_readable( $js_path ) ) {
			return;
		}
		wp_enqueue_style( 'arp-public', ARP_PLUGIN_URL . 'assets/css/public.css', array(), (string) filemtime( $css_path ) );

		$deps = array( 'jquery' );
		if ( function_exists( 'PW' ) && defined( 'PW_VERSION' ) ) {
			wp_enqueue_script( 'pw-datepicker-js', PW()->plugin_url( 'assets/js/persian-datepicker.min.js' ), array( 'jquery' ), PW_VERSION, true );
			$deps[] = 'pw-datepicker-js';
			// The theme's babakhani picker redefines the global `persianDate` the PW picker needs.
			add_action(
				'wp_enqueue_scripts',
				static function () {
					wp_dequeue_script( 'drplus-pdp' );
					wp_dequeue_script( 'drplus-pd' );
					wp_dequeue_style( 'drplus-pdp' );
				},
				1000
			);
		}
		wp_enqueue_script( 'arp-public', ARP_PLUGIN_URL . 'assets/js/public.js', $deps, (string) filemtime( $js_path ), true );

		// MNS overrides must win over ARP rules of equal specificity.
		global $wp_styles;
		if ( isset( $wp_styles->registered[ self::HANDLE_PUBLIC ] ) ) {
			$wp_styles->registered[ self::HANDLE_PUBLIC ]->deps[] = 'arp-public';
		}
	}

	public static function enqueue_admin(): void {
		wp_enqueue_style(
			self::HANDLE_ADMIN,
			MNS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			self::asset_ver( 'assets/css/admin.css' )
		);

		wp_enqueue_script(
			self::HANDLE_ADMIN,
			MNS_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			self::asset_ver( 'assets/js/admin.js' ),
			true
		);
	}
}
