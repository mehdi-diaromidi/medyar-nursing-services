<?php
/**
 * Patient dashboard fixture (Phase 3).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order_view = MNS_Pages::panel_url( 'patient', 'order-view' );
$invoice    = MNS_Pages::panel_url( 'patient', 'invoices' );
$survey     = MNS_Pages::panel_url( 'patient', 'surveys' );
$new_order  = MNS_Pages::panel_url( 'patient', 'new-order' );

return array(
	/**
	 * Priority action card variants (dev switcher / default).
	 * unpaid | active | survey | new_user | none
	 */
	'primary_action' => array(
		'type'        => 'unpaid',
		'title'       => 'فاکتور پرداخت‌نشده دارید',
		'text'        => 'پرداخت را همین حالا انجام دهید تا سفارش کامل شود.',
		'cta_label'   => 'پرداخت ۱۲۰٬۰۰۰ تومان',
		'cta_url'     => $invoice,
		'amount'      => 120000,
	),
	'variants'       => array(
		'new_user' => array(
			'type'      => 'new_user',
			'title'     => 'اولین سفارش پرستاری',
			'text'      => 'با ثبت اولین خدمت، تخفیف سفارش اول برای شما فعال می‌شود.',
			'cta_label' => 'شروع ثبت سفارش',
			'cta_url'   => $new_order,
		),
		'active'   => array(
			'type'      => 'active',
			'title'     => 'پرستار برای سرم‌تراپی تخصیص یافت',
			'text'      => 'مراجعه امروز ساعت ۱۰:۰۰ · ونک',
			'cta_label' => 'مشاهده سفارش',
			'cta_url'   => add_query_arg( 'mns_order', 'MNS-1403-1001', $order_view ),
		),
		'survey'   => array(
			'type'      => 'survey',
			'title'     => 'پرسشنامه در انتظار پاسخ',
			'text'      => 'نظر شما دربارهٔ آخرین مراجعه هنوز ثبت نشده است.',
			'cta_label' => 'پاسخ به پرسشنامه',
			'cta_url'   => $survey,
		),
		'unpaid'   => array(
			'type'      => 'unpaid',
			'title'     => 'فاکتور پرداخت‌نشده دارید',
			'text'      => 'پرداخت را همین حالا انجام دهید تا سفارش کامل شود.',
			'cta_label' => 'پرداخت ۱۲۰٬۰۰۰ تومان',
			'cta_url'   => $invoice,
			'amount'    => 120000,
		),
	),
	'categories'     => array(
		array( 'slug' => 'serum', 'label' => 'سرم‌تراپی', 'icon' => 'pill' ),
		array( 'slug' => 'injection', 'label' => 'تزریقات', 'icon' => 'activity' ),
		array( 'slug' => 'dressing', 'label' => 'پانسمان', 'icon' => 'heart' ),
		array( 'slug' => 'catheter', 'label' => 'سوندگذاری', 'icon' => 'shield' ),
		array( 'slug' => 'care', 'label' => 'مراقبت', 'icon' => 'users' ),
		array( 'slug' => 'other', 'label' => 'سایر', 'icon' => 'more' ),
	),
	'upcoming'       => array(
		array(
			'tracking' => 'MNS-1403-1001',
			'service'  => 'سرم‌تراپی',
			'when'     => '2026-11-05 10:00',
			'status'   => 'assigned',
			'patient'  => 'خودم',
			'url'      => add_query_arg( 'mns_order', 'MNS-1403-1001', $order_view ),
		),
		array(
			'tracking' => 'MNS-1403-1012',
			'service'  => 'پانسمان',
			'when'     => '2026-11-07 16:30',
			'status'   => 'submitted',
			'patient'  => 'مادر',
			'url'      => add_query_arg( 'mns_order', 'MNS-1403-1012', $order_view ),
		),
	),
	'loyalty'        => array(
		'filled' => 3,
		'total'  => 5,
		'hint'   => '۲ مهر دیگر تا تخفیف بعدی',
	),
	'last_service'   => array(
		'slug'  => 'serum',
		'label' => 'تکرار آخرین خدمت (سرم‌تراپی)',
	),
);
