<?php
/**
 * Patient orders list fixture (relative dates).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz  = wp_timezone();
$now = new DateTimeImmutable( 'now', $tz );
$jy  = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );

$view = static function ( string $code ): string {
	return add_query_arg(
		'mns_order',
		$code,
		MNS_Pages::panel_url( 'patient', 'order-view' )
	);
};

$t = static function ( string $modify, string $time ) use ( $now ): string {
	$base = ( 'today' === $modify ) ? $now : $now->modify( $modify );
	return $base->setTime( (int) substr( $time, 0, 2 ), (int) substr( $time, 3, 2 ) )->format( 'Y-m-d H:i' );
};

$code = static function ( string $suffix ) use ( $jy ): string {
	return 'MNS-' . $jy[0] . '-' . $suffix;
};

return array(
	'items' => array(
		array(
			'tracking'    => $code( '1001' ),
			'service'     => 'سرم‌تراپی',
			'patient'     => 'خودم',
			'when'        => $t( 'today', '10:00' ),
			'status'      => 'assigned',
			'tab'         => 'active',
			'nurse'       => 'علی رضایی',
			'next_action' => 'مشاهده',
			'next_url'    => $view( $code( '1001' ) ),
			'recurring'   => false,
		),
		array(
			'tracking'        => $code( '1012' ),
			'service'         => 'پانسمان و زخم',
			'patient'         => 'مادر',
			'when'            => $t( '+2 days', '16:30' ),
			'status'          => 'submitted',
			'tab'             => 'active',
			'nurse'           => '',
			'next_action'     => 'مشاهده',
			'next_url'        => $view( $code( '1012' ) ),
			'recurring'       => true,
			'recurring_label' => 'نوبت ۳ از ۶',
		),
		array(
			'tracking'    => $code( '0970' ),
			'service'     => 'تزریقات',
			'patient'     => 'خودم',
			'when'        => $t( '-8 days', '11:00' ),
			'status'      => 'invoiced',
			'tab'         => 'active',
			'nurse'       => 'سارا احمدی',
			'next_action' => 'پرداخت',
			'next_url'    => add_query_arg( 'mns_invoice', 'INV-' . $jy[0] . '-0970', MNS_Pages::panel_url( 'patient', 'invoice-view' ) ),
			'recurring'   => false,
		),
		array(
			'tracking'    => $code( '0988' ),
			'service'     => 'تزریقات',
			'patient'     => 'خودم',
			'when'        => $t( '-16 days', '09:00' ),
			'status'      => 'completed',
			'tab'         => 'past',
			'nurse'       => 'سارا احمدی',
			'next_action' => 'ثبت نظر',
			'next_url'    => MNS_Pages::panel_url( 'patient', 'surveys' ),
			'recurring'   => false,
		),
		array(
			'tracking'    => $code( '0901' ),
			'service'     => 'مراقبت از بیمار',
			'patient'     => 'پدر',
			'when'        => $t( '-35 days', '14:00' ),
			'status'      => 'cancelled',
			'tab'         => 'cancelled',
			'nurse'       => '',
			'next_action' => 'درخواست مجدد',
			'next_url'    => MNS_Pages::panel_url( 'patient', 'new-order' ),
			'recurring'   => false,
		),
	),
);
