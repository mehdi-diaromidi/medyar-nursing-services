<?php
/**
 * Patient dashboard fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'metrics'       => array(
		'open'      => '۲',
		'completed' => '۵',
		'unpaid'    => '۱',
	),
	'recent_orders' => array(
		array(
			'tracking'     => 'MNS-1403-1001',
			'service'      => 'سرم‌تراپی',
			'date'         => '۱۴۰۳/۰۸/۱۲',
			'status_label' => 'تخصیص‌یافته',
			'tone'         => 'progress',
		),
		array(
			'tracking'     => 'MNS-1403-0988',
			'service'      => 'تزریقات',
			'date'         => '۱۴۰۳/۰۸/۰۵',
			'status_label' => 'تکمیل‌شده',
			'tone'         => 'done',
		),
		array(
			'tracking'     => 'MNS-1403-0970',
			'service'      => 'پانسمان',
			'date'         => '۱۴۰۳/۰۷/۲۸',
			'status_label' => 'فاکتور صادر شده',
			'tone'         => 'waiting',
		),
	),
);
