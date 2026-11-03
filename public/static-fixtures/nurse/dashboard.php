<?php
/**
 * Nurse dashboard fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'metrics'      => array(
		'today'      => '۳',
		'upcoming'   => '۴',
		'rating_avg' => '۴٫۷',
	),
	'today_orders' => array(
		array(
			'time'         => '۱۰:۰۰',
			'service'      => 'سرم‌تراپی',
			'area'         => 'ونک',
			'status_label' => 'تخصیص‌یافته',
			'tone'         => 'progress',
		),
		array(
			'time'         => '۱۴:۳۰',
			'service'      => 'تزریقات',
			'area'         => 'جردن',
			'status_label' => 'در حال انجام',
			'tone'         => 'progress',
		),
	),
);
