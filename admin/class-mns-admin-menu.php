<?php
/**
 * Admin menu registration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Admin_Menu {

	public const SLUG_DASHBOARD = 'mns-dashboard';

	/** @var self|null */
	private static $instance = null;

	/** @var array<string,string> slug => hook suffix */
	private $hooks = array();

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
	}

	/**
	 * @return string[]
	 */
	public function hooks(): array {
		return array_values( $this->hooks );
	}

	public function is_mns_screen( string $hook_suffix ): bool {
		if ( in_array( $hook_suffix, $this->hooks(), true ) ) {
			return true;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return is_string( $page ) && 0 === strpos( $page, 'mns-' );
	}

	public function register_menus(): void {
		$cap = MNS_Roles::CAP_MANAGE;

		$hook = add_menu_page(
			__( 'خدمات پرستاری', 'medyar-nursing-services' ),
			__( 'خدمات پرستاری', 'medyar-nursing-services' ),
			$cap,
			self::SLUG_DASHBOARD,
			array( $this, 'render_dashboard' ),
			'dashicons-heart',
			27
		);

		if ( is_string( $hook ) ) {
			$this->hooks[ self::SLUG_DASHBOARD ] = $hook;
		}
	}

	public function render_dashboard(): void {
		if ( ! current_user_can( MNS_Roles::CAP_MANAGE ) ) {
			wp_die( esc_html__( 'شما اجازه دسترسی به این صفحه را ندارید.', 'medyar-nursing-services' ) );
		}

		$view = MNS_PLUGIN_DIR . 'admin/views/dashboard-stub.php';
		if ( is_readable( $view ) ) {
			require $view;
		}
	}
}
