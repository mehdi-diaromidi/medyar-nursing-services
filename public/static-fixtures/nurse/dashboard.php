<?php
/**
 * Nurse dashboard fixture (Phase 5).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view = MNS_Pages::panel_url( 'nurse', 'order-view' );

return array(
	'accepting'    => true,
	'hospital'     => 'بیمارستان میلاد',
	'metrics'      => array(
		'today'      => 3,
		'upcoming'   => 4,
		'rating_avg' => 4.7,
	),
	'today_orders' => array(
		array(
			'time'    => '10:00',
			'service' => 'سرم‌تراپی',
			'area'    => 'ونک',
			'status'  => 'assigned',
			'url'     => add_query_arg( 'mns_order', 'MNS-1403-1001', $view ),
		),
		array(
			'time'    => '14:30',
			'service' => 'تزریقات',
			'area'    => 'جردن',
			'status'  => 'in_progress',
			'url'     => add_query_arg( 'mns_order', 'MNS-1403-1002', $view ),
		),
		array(
			'time'    => '17:00',
			'service' => 'پانسمان',
			'area'    => 'سعادت‌آباد',
			'status'  => 'assigned',
			'url'     => add_query_arg( 'mns_order', 'MNS-1403-1003', $view ),
		),
	),
);
