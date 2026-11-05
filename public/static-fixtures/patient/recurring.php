<?php
/**
 * Patient recurring programs (Phase 4).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
					'when'   => '2026-11-07 16:30',
					'status' => 'submitted',
					'url'    => add_query_arg( 'mns_order', 'MNS-1403-1012', $order_view ),
				),
				array(
					'label'  => 'نوبت ۴ از ۶',
					'when'   => '2026-11-14 16:30',
					'status' => 'planned',
					'url'    => '',
				),
				array(
					'label'  => 'نوبت ۵ از ۶',
					'when'   => '2026-11-21 16:30',
					'status' => 'planned',
					'url'    => '',
				),
			),
		),
	),
);
