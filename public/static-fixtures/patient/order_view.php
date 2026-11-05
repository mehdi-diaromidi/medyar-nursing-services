<?php
/**
 * Patient order detail fixtures keyed by status + tracking deep link.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz  = wp_timezone();
$now = new DateTimeImmutable( 'now', $tz );
$jy  = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );
$code = static function ( string $suffix ) use ( $jy ): string {
	return 'MNS-' . $jy[0] . '-' . $suffix;
};
$when = $now->setTime( 10, 0 )->format( 'Y-m-d H:i' );

$invoice = add_query_arg( 'mns_invoice', 'INV-' . $jy[0] . '-1001', MNS_Pages::panel_url( 'patient', 'invoice-view' ) );
$reorder = MNS_Pages::panel_url( 'patient', 'new-order' );
$survey  = MNS_Pages::panel_url( 'patient', 'surveys' );

$base = array(
	'tracking'    => $code( '1001' ),
	'service'     => 'سرم‌تراپی',
	'patient'     => 'حسن محمدی',
	'phone'       => '09121234567',
	'address'     => 'تهران، ونک، خیابان فلان، پلاک ۱۰',
	'when'        => $when,
	'nurse'       => array(
		'name'      => 'علی رضایی',
		'specialty' => 'پرستار ارشد',
		'rating'    => 4.8,
		'reviews'   => 126,
		'meta'      => 'تأییدشدهٔ مدیار',
	),
	'essentials'  => array(
		'نوع سرم: نرمال سالین',
		'حساسیت: ندارد',
	),
	'report'      => '',
	'invoice'     => null,
	'can_cancel'  => true,
	'can_repick'  => true,
	'reorder_url' => $reorder,
	'survey_url'  => $survey,
	'invoice_url' => $invoice,
);

$by_status = array(
	'submitted'     => array_merge(
		$base,
		array(
			'tracking'   => $code( '1012' ),
			'status'     => 'submitted',
			'service'    => 'پانسمان و زخم',
			'patient'    => 'مادر',
			'when'       => $now->modify( '+2 days' )->setTime( 16, 30 )->format( 'Y-m-d H:i' ),
			'nurse'      => null,
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
			'tracking'   => $code( '0970' ),
			'status'     => 'invoiced',
			'service'    => 'تزریقات',
			'can_cancel' => false,
			'report'     => 'تزریق انجام شد.',
			'invoice'    => array(
				'base'  => 850000,
				'total' => 920000,
				'lines' => array(
					array( 'label' => 'اقلام مصرفی', 'amount' => 70000 ),
				),
			),
			'invoice_url'=> add_query_arg( 'mns_invoice', 'INV-' . $jy[0] . '-0970', MNS_Pages::panel_url( 'patient', 'invoice-view' ) ),
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
			'tracking'   => $code( '0988' ),
			'status'     => 'completed',
			'service'    => 'تزریقات',
			'can_cancel' => false,
			'report'     => 'تزریق انجام شد.',
			'can_survey' => true,
		)
	),
	'cancelled'     => array_merge(
		$base,
		array(
			'tracking'   => $code( '0901' ),
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

$by_tracking = array(
	$code( '1001' ) => 'assigned',
	$code( '1012' ) => 'submitted',
	$code( '0970' ) => 'invoiced',
	$code( '0988' ) => 'completed',
	$code( '0901' ) => 'cancelled',
);

return array(
	'default_status' => 'assigned',
	'by_status'      => $by_status,
	'by_tracking'    => $by_tracking,
);
