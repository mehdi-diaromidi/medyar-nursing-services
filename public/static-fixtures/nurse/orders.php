<?php
/**
 * Nurse orders list (relative dates + panel_url).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz  = wp_timezone();
$now = new DateTimeImmutable( 'now', $tz );
$jy  = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );
$code = static function ( string $suffix ) use ( $jy ): string {
	return 'MNS-' . $jy[0] . '-' . $suffix;
};
$t = static function ( string $modify, string $time ) use ( $now ): string {
	$base = ( 'today' === $modify ) ? $now : $now->modify( $modify );
	return $base->setTime( (int) substr( $time, 0, 2 ), (int) substr( $time, 3, 2 ) )->format( 'Y-m-d H:i' );
};

$view = static function ( string $tracking ): string {
	return add_query_arg(
		'mns_order',
		$tracking,
		MNS_Pages::panel_url( 'nurse', 'order-view' )
	);
};

return array(
	'items' => array(
		array(
			'tracking' => $code( '1001' ),
			'service'  => 'سرم‌تراپی',
			'patient'  => 'حسن محمدی',
			'area'     => 'ونک',
			'when'     => $t( 'today', '10:00' ),
			'status'   => 'assigned',
			'tab'      => 'active',
			'url'      => $view( $code( '1001' ) ),
		),
		array(
			'tracking' => $code( '1002' ),
			'service'  => 'تزریقات',
			'patient'  => 'سارا محمدی',
			'area'     => 'جردن',
			'when'     => $t( 'today', '14:30' ),
			'status'   => 'in_progress',
			'tab'      => 'active',
			'url'      => $view( $code( '1002' ) ),
		),
		array(
			'tracking' => $code( '0980' ),
			'service'  => 'پانسمان',
			'patient'  => 'مریم محمدی',
			'area'     => 'نیاوران',
			'when'     => $t( '-1 day', '16:00' ),
			'status'   => 'service_ended',
			'tab'      => 'past',
			'url'      => $view( $code( '0980' ) ),
		),
	),
);
