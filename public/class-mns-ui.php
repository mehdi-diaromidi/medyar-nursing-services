<?php
/**
 * Shared UI helpers and renderers. Views prefer these over inline markup
 * so every page stays consistent with the design system.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_UI {

	/**
	 * Ensure icon helper is loaded.
	 */
	public static function boot_icons(): void {
		static $done = false;
		if ( $done ) {
			return;
		}
		require_once MNS_PLUGIN_DIR . 'public/views/partials/icons.php';
		$done = true;
	}

	/**
	 * Support phone (digits only for tel:). Overridable via mns_settings[support_phone].
	 */
	public static function support_phone(): string {
		$phone = MNS_Config::setting( 'support_phone', '02188607015' );
		$digits = preg_replace( '/\D+/', '', (string) $phone );
		return is_string( $digits ) && '' !== $digits ? $digits : '02188607015';
	}

	public static function support_tel_href(): string {
		return 'tel:' . self::support_phone();
	}

	/**
	 * @param array<string,mixed> $args See status-badge.php.
	 */
	public static function status_badge( string $slug, array $args = array() ): void {
		$args['slug'] = $slug;
		self::partial( 'status-badge', $args );
	}

	/**
	 * @param array<string,mixed> $args See order-timeline.php.
	 */
	public static function order_timeline( string $slug, array $args = array() ): void {
		$args['slug'] = $slug;
		self::partial( 'order-timeline', $args );
	}

	/**
	 * @param array<string,mixed> $args title, lead, actions (HTML), back_url, back_label.
	 */
	public static function page_header( array $args ): void {
		self::partial( 'page-header', $args );
	}

	/**
	 * @param array<string,mixed> $args See empty-state.php.
	 */
	public static function empty_state( array $args = array() ): void {
		self::partial( 'empty-state', $args );
	}

	/**
	 * @param array<string,mixed> $args See error-state.php.
	 */
	public static function error_state( array $args = array() ): void {
		self::partial( 'error-state', $args );
	}

	/**
	 * @param array<string,mixed> $args See skeleton.php.
	 */
	public static function skeleton( array $args = array() ): void {
		self::partial( 'skeleton', $args );
	}

	/**
	 * @param array<string,mixed> $args See alert.php.
	 */
	public static function alert( array $args ): void {
		self::partial( 'alert', $args );
	}

	/**
	 * @param array<string,mixed> $args See support-call.php.
	 */
	public static function support_call( array $args = array() ): void {
		self::partial( 'support-call', $args );
	}

	/**
	 * @param array<string,mixed> $args See price-summary.php.
	 */
	public static function price_summary( array $args ): void {
		self::partial( 'price-summary', $args );
	}

	/**
	 * @param array<string,mixed> $args See nurse-card.php.
	 */
	public static function nurse_card( array $args ): void {
		self::partial( 'nurse-card', $args );
	}

	/**
	 * @param array<string,mixed> $args See stamp-card.php.
	 */
	public static function stamp_card( array $args ): void {
		self::partial( 'stamp-card', $args );
	}

	/**
	 * @param array<string,mixed> $args See star-rating.php.
	 */
	public static function star_rating( array $args ): void {
		self::partial( 'star-rating', $args );
	}

	/**
	 * @param array<string,mixed> $args See sticky-action.php.
	 */
	public static function sticky_action( array $args ): void {
		self::partial( 'sticky-action', $args );
	}

	/**
	 * Bottom nav for a panel. Items: key/slug, label, icon, url, primary?, external?.
	 *
	 * @param array<int,array<string,mixed>> $items Nav items; empty = defaults.
	 */
	public static function bottom_nav( string $panel, string $current, array $items = array() ): void {
		self::partial(
			'bottom-nav',
			array(
				'panel'   => $panel,
				'current' => $current,
				'items'   => $items,
			)
		);
	}

	/**
	 * Default mobile bottom-nav items for a panel.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function default_bottom_nav_items( string $panel ): array {
		if ( 'nurse' === $panel ) {
			$account = function_exists( 'wc_get_page_permalink' ) ? (string) wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
			return array(
				array(
					'key'   => 'dashboard',
					'label' => MNS_Copy::t( 'nav.home' ),
					'icon'  => 'home',
					'url'   => MNS_Pages::panel_url( 'nurse', 'dashboard' ),
				),
				array(
					'key'   => 'orders',
					'label' => MNS_Copy::t( 'nav.orders' ),
					'icon'  => 'list',
					'url'   => MNS_Pages::panel_url( 'nurse', 'orders' ),
				),
				array(
					'key'   => 'availability',
					'label' => MNS_Copy::t( 'nav.schedule' ),
					'icon'  => 'calendar',
					'url'   => MNS_Pages::panel_url( 'nurse', 'availability' ),
				),
				array(
					'key'      => 'wallet',
					'label'    => MNS_Copy::t( 'nav.wallet' ),
					'icon'     => 'wallet',
					'url'      => $account,
					'external' => true,
				),
			);
		}

		return array(
			array(
				'key'   => 'dashboard',
				'label' => MNS_Copy::t( 'nav.home' ),
				'icon'  => 'home',
				'url'   => MNS_Pages::panel_url( 'patient', 'dashboard' ),
			),
			array(
				'key'   => 'orders',
				'label' => MNS_Copy::t( 'nav.orders' ),
				'icon'  => 'list',
				'url'   => MNS_Pages::panel_url( 'patient', 'orders' ),
			),
			array(
				'key'     => 'new-order',
				'label'   => MNS_Copy::t( 'nav.new' ),
				'icon'    => 'plus',
				'url'     => MNS_Pages::panel_url( 'patient', 'new-order' ),
				'primary' => true,
			),
			array(
				'key'   => 'patients',
				'label' => MNS_Copy::t( 'nav.profile' ),
				'icon'  => 'user',
				'url'   => MNS_Pages::panel_url( 'patient', 'patients' ),
			),
		);
	}

	/**
	 * Compact external-link mark for sidebar.
	 */
	public static function external_mark(): string {
		self::boot_icons();
		return '<span class="mns-external" aria-hidden="true">' . mns_icon( 'external' ) . '</span>';
	}

	/**
	 * Human page titles for document.title / headers.
	 *
	 * @return array<string,string>
	 */
	public static function page_titles( string $panel ): array {
		if ( 'nurse' === $panel ) {
			return array(
				'dashboard'    => __( 'داشبورد پرستار', 'medyar-nursing-services' ),
				'orders'       => __( 'سفارش‌های من', 'medyar-nursing-services' ),
				'order-view'   => __( 'جزئیات سفارش', 'medyar-nursing-services' ),
				'availability' => __( 'برنامه و دسترسی', 'medyar-nursing-services' ),
				'ratings'      => __( 'امتیازهای من', 'medyar-nursing-services' ),
				'profile'      => __( 'پروفایل', 'medyar-nursing-services' ),
				'ui-kit'       => __( 'کیت کامپوننت‌ها', 'medyar-nursing-services' ),
			);
		}
		return array(
			'dashboard'  => __( 'خانهٔ پنل بیمار', 'medyar-nursing-services' ),
			'new-order'  => __( 'ثبت سفارش پرستاری', 'medyar-nursing-services' ),
			'orders'     => __( 'سفارش‌های من', 'medyar-nursing-services' ),
			'order-view' => __( 'جزئیات سفارش', 'medyar-nursing-services' ),
			'patients'   => __( 'بیماران من', 'medyar-nursing-services' ),
			'invoices'   => __( 'فاکتورها و پرداخت‌ها', 'medyar-nursing-services' ),
			'recurring'  => __( 'خدمات دوره‌ای', 'medyar-nursing-services' ),
			'loyalty'    => __( 'کارت وفاداری', 'medyar-nursing-services' ),
			'surveys'    => __( 'پرسشنامه‌ها', 'medyar-nursing-services' ),
			'ui-kit'     => __( 'کیت کامپوننت‌ها', 'medyar-nursing-services' ),
		);
	}

	/**
	 * Render a partial relative to public/views/partials/.
	 *
	 * @param array<string,mixed> $args Variables extracted into the partial.
	 */
	public static function partial( string $name, array $args = array() ): void {
		$file = MNS_PLUGIN_DIR . 'public/views/partials/' . $name . '.php';
		if ( ! is_readable( $file ) ) {
			return;
		}
		self::boot_icons();
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- intentional for view partials.
		extract( $args, EXTR_SKIP );
		require $file;
	}
}
