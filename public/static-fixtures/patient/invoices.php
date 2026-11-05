<?php
/**
 * Patient invoices list + detail pack (relative dates, panel_url).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz  = wp_timezone();
$now = new DateTimeImmutable( 'now', $tz );
$jy  = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );

$inv = static function ( string $suffix ) use ( $jy ): string {
	return 'INV-' . $jy[0] . '-' . $suffix;
};
$code = static function ( string $suffix ) use ( $jy ): string {
	return 'MNS-' . $jy[0] . '-' . $suffix;
};
$t = static function ( string $modify, string $time ) use ( $now ): string {
	$base = ( 'today' === $modify ) ? $now : $now->modify( $modify );
	return $base->setTime( (int) substr( $time, 0, 2 ), (int) substr( $time, 3, 2 ) )->format( 'Y-m-d H:i' );
};

$view = static function ( string $number ): string {
	return add_query_arg(
		'mns_invoice',
		$number,
		MNS_Pages::panel_url( 'patient', 'invoice-view' )
	);
};

$n1001 = $inv( '1001' );
$n0988 = $inv( '0988' );
$n0970 = $inv( '0970' );

$items = array(
	array(
		'number'  => $n1001,
		'service' => 'سرم‌تراپی',
		'when'    => $t( 'today', '18:00' ),
		'amount'  => 920000,
		'status'  => 'unpaid',
		'payable' => true,
		'url'     => $view( $n1001 ),
	),
	array(
		'number'  => $n0988,
		'service' => 'تزریقات',
		'when'    => $t( '-16 days', '09:00' ),
		'amount'  => 420000,
		'status'  => 'paid',
		'payable' => false,
		'url'     => $view( $n0988 ),
	),
	array(
		'number'  => $n0970,
		'service' => 'پانسمان',
		'when'    => $t( '-35 days', '14:00' ),
		'amount'  => 350000,
		'status'  => 'void',
		'payable' => false,
		'url'     => $view( $n0970 ),
	),
);

$details = array(
	$n1001 => array(
		'number'   => $n1001,
		'service'  => 'سرم‌تراپی',
		'when'     => $t( 'today', '18:00' ),
		'status'   => 'unpaid',
		'order'    => $code( '1001' ),
		'patient'  => 'حسن محمدی',
		'base'     => 850000,
		'lines'    => array(
			array( 'label' => 'اقلام مصرفی', 'amount' => 70000 ),
		),
		'discount' => 0,
		'total'    => 920000,
		'note'     => MNS_Copy::t( 'price.travel' ),
	),
	$n0988 => array(
		'number'   => $n0988,
		'service'  => 'تزریقات',
		'when'     => $t( '-16 days', '09:00' ),
		'status'   => 'paid',
		'order'    => $code( '0988' ),
		'patient'  => 'سارا محمدی',
		'base'     => 420000,
		'lines'    => array(),
		'discount' => 0,
		'total'    => 420000,
		'note'     => '',
		'paid_at'  => $t( '-16 days', '10:15' ),
	),
	$n0970 => array(
		'number'   => $n0970,
		'service'  => 'پانسمان',
		'when'     => $t( '-35 days', '14:00' ),
		'status'   => 'void',
		'order'    => $code( '0901' ),
		'patient'  => 'پدر',
		'base'     => 350000,
		'lines'    => array(),
		'discount' => 0,
		'total'    => 350000,
		'note'     => __( 'این فاکتور باطل شده است.', 'medyar-nursing-services' ),
	),
);

return array(
	'items'          => $items,
	'details'        => $details,
	'default_number' => $n1001,
);
