<?php
/**
 * Admin hub fixture (Phase 6 static).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'bubbles' => array(
		'nurses_pending' => 3,
		'needs_review'   => 2,
		'urgent_open'    => 1,
	),
	'stats'   => array(
		'orders_today'   => 12,
		'unassigned'     => 4,
		'in_progress'    => 7,
		'unpaid'         => 5,
		'nurses_online'  => 18,
		'withdrawals'    => 2,
	),
	'queue'   => array(
		array(
			'code'    => 'MNS-1403-1105',
			'service' => 'سرم‌تراپی',
			'city'    => 'تهران / ونک',
			'status'  => 'submitted',
			'label'   => 'در انتظار تخصیص',
		),
		array(
			'code'    => 'MNS-1403-1101',
			'service' => 'تزریقات',
			'city'    => 'کرج',
			'status'  => 'needs_review',
			'label'   => 'نیازمند بررسی',
		),
		array(
			'code'    => 'MNS-1403-1098',
			'service' => 'پانسمان',
			'city'    => 'تهران / جردن',
			'status'  => 'in_progress',
			'label'   => 'فوری باز',
			'urgent'  => true,
		),
	),
	'nurses'  => array(
		array( 'name' => 'مریم کاظمی', 'hospital' => 'شریعتی', 'status' => 'pending' ),
		array( 'name' => 'رضا نوری', 'hospital' => 'میلاد', 'status' => 'pending' ),
		array( 'name' => 'سارا احمدی', 'hospital' => 'پارسیان', 'status' => 'pending' ),
	),
);
