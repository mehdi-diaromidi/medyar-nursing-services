<?php
/**
 * Front panel routing helpers and template map.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Pages {

	public const QV_PANEL = 'mns_panel';
	public const QV_PAGE  = 'mns_page';

	/**
	 * @return array<string,string> template file => label
	 */
	public static function templates(): array {
		return array(
			'mns-patient.php'  => __( 'MNS — پنل بیمار', 'medyar-nursing-services' ),
			'mns-nurse.php'    => __( 'MNS — پنل پرستار', 'medyar-nursing-services' ),
			'mns-catalog.php'  => __( 'MNS — کاتالوگ خدمات پرستاری', 'medyar-nursing-services' ),
			'mns-nurse-signup.php' => __( 'MNS — عضویت پرستار', 'medyar-nursing-services' ),
		);
	}

	public static function is_mns_template( string $slug ): bool {
		return isset( self::templates()[ $slug ] );
	}

	/**
	 * @return string[]
	 */
	public static function patient_slugs(): array {
		return array(
			'dashboard',
			'new-order',
			'orders',
			'patients',
			'invoices',
			'recurring',
			'loyalty',
			'surveys',
		);
	}

	/**
	 * @return string[]
	 */
	public static function nurse_slugs(): array {
		return array(
			'dashboard',
			'orders',
			'availability',
			'ratings',
			'profile',
		);
	}

	public static function current_panel(): string {
		$panel = isset( $_GET[ self::QV_PANEL ] ) ? sanitize_key( wp_unslash( $_GET[ self::QV_PANEL ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return in_array( $panel, array( 'patient', 'nurse' ), true ) ? $panel : '';
	}

	public static function current_page( string $panel ): string {
		$page = isset( $_GET[ self::QV_PAGE ] ) ? sanitize_key( wp_unslash( $_GET[ self::QV_PAGE ] ) ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$allowed = 'nurse' === $panel ? self::nurse_slugs() : self::patient_slugs();
		return in_array( $page, $allowed, true ) ? $page : 'dashboard';
	}

	public static function panel_url( string $panel, string $page = 'dashboard' ): string {
		$url = get_permalink();
		if ( ! is_string( $url ) || '' === $url ) {
			$url = home_url( '/' );
		}
		return add_query_arg(
			array(
				self::QV_PANEL => $panel,
				self::QV_PAGE  => $page,
			),
			$url
		);
	}
}
