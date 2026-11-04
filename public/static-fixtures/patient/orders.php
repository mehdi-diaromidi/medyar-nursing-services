<?php
/**
 * Patient orders list fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view = add_query_arg(
	array(
		'mns_panel' => 'patient',
		'mns_page'  => 'order-view',
		'mns_order' => 'MNS-1403-1001',
	),
	get_permalink()
);
$view = is_string( $view ) ? $view : MNS_Pages::panel_url( 'patient', 'order-view' );

return array(
	'items' => array(
		array(
			'tracking'     => 'MNS-1403-1001',
			'service'      => 'سرم‌تراپی',
			'date'         => '۱۴۰۳/۰۸/۱۲',
			'status_label' => 'تخصیص‌یافته',
			'tone'         => 'progress',
			'details'      => 'پرستار: علی رضایی · منطقه ونک · زمان ۱۰:۰۰',
			'view_url'     => $view,
		),
		array(
			'tracking'     => 'MNS-1403-0988',
			'service'      => 'تزریقات',
			'date'         => '۱۴۰۳/۰۸/۰۵',
			'status_label' => 'تکمیل‌شده',
			'tone'         => 'done',
			'details'      => 'پرداخت شده · گزارش پایان ثبت شده است.',
			'view_url'     => $view,
		),
		array(
			'tracking'     => 'MNS-1403-0970',
			'service'      => 'پانسمان',
			'date'         => '۱۴۰۳/۰۷/۲۸',
			'status_label' => 'فاکتور صادر شده',
			'tone'         => 'waiting',
			'details'      => 'در انتظار پرداخت آنلاین.',
			'view_url'     => $view,
		),
	),
);
