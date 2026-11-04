<?php
/**
 * Patient order detail fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$invoice = add_query_arg(
	array(
		'mns_panel' => 'patient',
		'mns_page'  => 'invoices',
	),
	get_permalink()
);
$reorder = add_query_arg(
	array(
		'mns_panel' => 'patient',
		'mns_page'  => 'new-order',
	),
	get_permalink()
);

return array(
	'tracking'     => 'MNS-1403-1001',
	'service'      => 'سرم‌تراپی',
	'status_label' => 'تخصیص‌یافته',
	'tone'         => 'progress',
	'patient'      => 'حسن محمدی',
	'address'      => 'تهران، ونک، خیابان فلان، پلاک ۱۰',
	'scheduled'    => '۱۴۰۳/۰۸/۱۲ · ۱۰:۰۰',
	'nurse_gender' => 'آقا',
	'nurse'        => array(
		'name'   => 'علی رضایی',
		'rating' => '۴٫۸',
		'phone'  => '09129876543',
	),
	'timeline'     => array(
		array( 'label' => 'ثبت سفارش', 'at' => '۱۴۰۳/۰۸/۱۱ · ۲۱:۱۰', 'state' => 'done' ),
		array( 'label' => 'تخصیص پرستار', 'at' => '۱۴۰۳/۰۸/۱۱ · ۲۱:۴۰', 'state' => 'done' ),
		array( 'label' => 'در انتظار مراجعه', 'at' => '۱۴۰۳/۰۸/۱۲ · ۱۰:۰۰', 'state' => 'current' ),
		array( 'label' => 'انجام خدمت و گزارش', 'at' => '—', 'state' => 'todo' ),
		array( 'label' => 'فاکتور و پرداخت', 'at' => '—', 'state' => 'todo' ),
	),
	'medication'   => array(
		'enabled' => true,
		'status'  => 'در انتظار بررسی مشاور',
		'hint'    => 'دارو فقط پس از تأیید پزشک مشاور به فاکتور اضافه می‌شود.',
	),
	'invoice_url'  => is_string( $invoice ) ? $invoice : '',
	'reorder_url'  => is_string( $reorder ) ? $reorder : '',
	'can_repick'   => true,
);
