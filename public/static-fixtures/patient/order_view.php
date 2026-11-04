<?php
/**
 * Patient order detail fixtures keyed by status (Phase 3).
 * Default = assigned. Override with ?mns_state=<status> or ?mns_order=.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$invoice = MNS_Pages::panel_url( 'patient', 'invoices' );
$reorder = MNS_Pages::panel_url( 'patient', 'new-order' );
$survey  = MNS_Pages::panel_url( 'patient', 'surveys' );

$base = array(
	'tracking'   => 'MNS-1403-1001',
	'service'    => 'سرم‌تراپی',
	'patient'    => 'حسن محمدی',
	'phone'      => '09121234567',
	'address'    => 'تهران، ونک، خیابان فلان، پلاک ۱۰',
	'when'       => '2026-11-05 10:00',
	'nurse'      => array(
		'name'       => 'علی رضایی',
		'specialty'  => 'پرستار ارشد',
		'rating'     => 4.8,
		'reviews'    => 126,
		'meta'       => 'تأییدشدهٔ مدیار',
	),
	'essentials' => array(
		'نوع سرم: نرمال سالین',
		'حساسیت: ندارد',
	),
	'report'     => '',
	'invoice'    => null,
	'can_cancel' => true,
	'can_repick' => true,
	'reorder_url'=> $reorder,
	'survey_url' => $survey,
	'invoice_url'=> $invoice,
);

$by_status = array(
	'submitted'     => array_merge(
		$base,
		array(
			'status' => 'submitted',
			'nurse'  => null,
			'can_repick' => false,
		)
	),
	'assigned'      => array_merge( $base, array( 'status' => 'assigned' ) ),
	'in_progress'   => array_merge( $base, array( 'status' => 'in_progress', 'can_cancel' => false ) ),
	'medication'    => array_merge(
		$base,
		array(
			'status'     => 'medication',
			'can_cancel' => false,
			'medication' => array(
				'enabled' => true,
				'hint'    => 'دارو در حال بررسی است؛ نتیجه در فاکتور اعمال می‌شود.',
			),
		)
	),
	'service_ended' => array_merge(
		$base,
		array(
			'status'     => 'service_ended',
			'can_cancel' => false,
			'report'     => 'سرم به‌طور کامل تزریق شد. علائم پایدار بود.',
		)
	),
	'invoiced'      => array_merge(
		$base,
		array(
			'status'     => 'invoiced',
			'can_cancel' => false,
			'report'     => 'سرم به‌طور کامل تزریق شد. علائم پایدار بود.',
			'invoice'    => array(
				'base'  => 850000,
				'total' => 920000,
				'lines' => array(
					array( 'label' => 'اقلام مصرفی', 'amount' => 70000 ),
				),
			),
		)
	),
	'paid'          => array_merge(
		$base,
		array(
			'status'     => 'paid',
			'can_cancel' => false,
			'report'     => 'سرم به‌طور کامل تزریق شد.',
			'invoice'    => array(
				'base'  => 850000,
				'total' => 920000,
				'paid'  => true,
			),
		)
	),
	'completed'     => array_merge(
		$base,
		array(
			'status'     => 'completed',
			'can_cancel' => false,
			'report'     => 'سرم به‌طور کامل تزریق شد.',
			'can_survey' => true,
		)
	),
	'cancelled'     => array_merge(
		$base,
		array(
			'status'     => 'cancelled',
			'nurse'      => null,
			'can_cancel' => false,
			'can_repick' => false,
		)
	),
	'needs_review'  => array_merge(
		$base,
		array(
			'status'     => 'needs_review',
			'can_cancel' => false,
			'can_repick' => false,
		)
	),
);

return array(
	'default_status' => 'assigned',
	'by_status'      => $by_status,
);
