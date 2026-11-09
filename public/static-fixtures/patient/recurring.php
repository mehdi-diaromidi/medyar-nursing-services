<?php
/**
 * Patient recurring programs (relative dates).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz  = wp_timezone();
$now = new DateTimeImmutable( 'now', $tz );
$jy  = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );
$code = 'MNS-' . $jy[0] . '-1012';
$order_view = MNS_Pages::panel_url( 'patient', 'order-view' );

return array(
	'programs' => array(
		array(
			'id'              => 'r1',
			'service'         => 'پانسمان',
			'patient'         => 'مادر',
			'pattern'         => 'هفتگی',
			'pattern_detail'  => 'هر شنبه ساعت ۱۶:۳۰',
			'preferred_nurse' => 'علی رضایی',
			'status'          => 'active',
			'status_label'    => 'فعال',
			'slots'           => array(
				array(
					'label'  => 'نوبت ۳ از ۶',
					'when'   => $now->modify( '+2 days' )->setTime( 16, 30 )->format( 'Y-m-d H:i' ),
					'status' => 'submitted',
					'url'    => add_query_arg( 'mns_order', $code, $order_view ),
				),
				array(
					'label'  => 'نوبت ۴ از ۶',
					'when'   => $now->modify( '+9 days' )->setTime( 16, 30 )->format( 'Y-m-d H:i' ),
					'status' => 'planned',
					'url'    => '',
				),
				array(
					'label'  => 'نوبت ۵ از ۶',
					'when'   => $now->modify( '+16 days' )->setTime( 16, 30 )->format( 'Y-m-d H:i' ),
					'status' => 'planned',
					'url'    => '',
				),
			),
		),
	),
);
