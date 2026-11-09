<?php
/**
 * Patient dashboard fixture (relative to now).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz         = wp_timezone();
$now        = new DateTimeImmutable( 'now', $tz );
$today_10   = $now->setTime( 10, 0 )->format( 'Y-m-d H:i' );
$plus_2_1630 = $now->modify( '+2 days' )->setTime( 16, 30 )->format( 'Y-m-d H:i' );
$jy         = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );
$year_code  = MNS_Format::digits( (string) $jy[0] );
$track_a    = 'MNS-' . $jy[0] . '-1001';
$track_b    = 'MNS-' . $jy[0] . '-1012';

$order_view = MNS_Pages::panel_url( 'patient', 'order-view' );
$invoice_v  = MNS_Pages::panel_url( 'patient', 'invoice-view' );
$survey     = MNS_Pages::panel_url( 'patient', 'surveys' );
$new_order  = MNS_Pages::panel_url( 'patient', 'new-order' );

return array(
	'primary_action' => array(
		'type'      => 'unpaid',
		'title'     => 'فاکتور پرداخت‌نشده دارید',
		'text'      => 'پرداخت را انجام دهید تا سفارش کامل شود.',
		'context'   => 'سرم‌تراپی · خودم · ' . MNS_Format::human( $today_10 ),
		'cta_label' => 'پرداخت',
		'cta_url'   => add_query_arg( 'mns_invoice', 'INV-' . $jy[0] . '-1001', $invoice_v ),
		'amount'    => 120000,
		'icon'      => 'invoice',
	),
	'variants'       => array(
		'new_user' => array(
			'type'      => 'new_user',
			'title'     => 'اولین سفارش پرستاری',
			'text'      => 'با ثبت اولین خدمت، تخفیف سفارش اول برای شما فعال می‌شود.',
			'context'   => '',
			'cta_label' => 'شروع ثبت سفارش',
			'cta_url'   => $new_order,
			'icon'      => 'plus',
		),
		'active'   => array(
			'type'      => 'active',
			'title'     => 'پرستار برای سرم‌تراپی تخصیص یافت',
			'text'      => '',
			'context'   => 'مراجعه ' . MNS_Format::human( $today_10 ) . ' · ونک',
			'cta_label' => 'مشاهده سفارش',
			'cta_url'   => add_query_arg( 'mns_order', $track_a, $order_view ),
			'icon'      => 'check-circle',
		),
		'survey'   => array(
			'type'      => 'survey',
			'title'     => 'پرسشنامه در انتظار پاسخ',
			'text'      => 'نظر شما دربارهٔ آخرین مراجعه هنوز ثبت نشده است.',
			'context'   => 'سرم‌تراپی · خودم',
			'cta_label' => 'پاسخ به پرسشنامه',
			'cta_url'   => $survey,
			'icon'      => 'star',
		),
		'unpaid'   => array(
			'type'      => 'unpaid',
			'title'     => 'فاکتور پرداخت‌نشده دارید',
			'text'      => 'پرداخت را انجام دهید تا سفارش کامل شود.',
			'context'   => 'سرم‌تراپی · خودم · ' . MNS_Format::human( $today_10 ),
			'cta_label' => 'پرداخت',
			'cta_url'   => add_query_arg( 'mns_invoice', 'INV-' . $jy[0] . '-1001', $invoice_v ),
			'amount'    => 120000,
			'icon'      => 'invoice',
		),
	),
	'categories'     => array(
		array( 'slug' => 'serum', 'label' => 'سرم‌تراپی', 'icon' => 'pill' ),
		array( 'slug' => 'injection', 'label' => 'تزریقات', 'icon' => 'activity' ),
		array( 'slug' => 'dressing', 'label' => 'پانسمان و زخم', 'icon' => 'bandage' ),
		array( 'slug' => 'catheter', 'label' => 'سوندگذاری', 'icon' => 'droplet' ),
		array( 'slug' => 'care', 'label' => 'مراقبت از بیمار', 'icon' => 'users' ),
		array( 'slug' => 'other', 'label' => 'سایر خدمات پرستاری', 'icon' => 'list' ),
	),
	'upcoming'       => array(
		array(
			'tracking' => $track_a,
			'service'  => 'سرم‌تراپی',
			'when'     => $today_10,
			'status'   => 'assigned',
			'patient'  => 'خودم',
			'nurse'    => 'علی رضایی',
			'url'      => add_query_arg( 'mns_order', $track_a, $order_view ),
		),
		array(
			'tracking' => $track_b,
			'service'  => 'پانسمان و زخم',
			'when'     => $plus_2_1630,
			'status'   => 'submitted',
			'patient'  => 'مادر',
			'nurse'    => '',
			'url'      => add_query_arg( 'mns_order', $track_b, $order_view ),
		),
	),
	'loyalty'        => array(
		'filled' => 3,
		'total'  => 5,
		'hint'   => '۲ مهر دیگر تا تخفیف بعدی',
	),
	'last_service'   => array(
		'slug'    => 'serum',
		'label'   => 'تکرار سرم‌تراپی',
		'patient' => 'خودم',
	),
	'stats'          => array(
		'unpaid_invoices' => 1,
		'patients'        => 3,
	),
	'_year_fa'       => $year_code,
);
