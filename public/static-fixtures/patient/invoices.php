<?php
/**
 * Patient invoices list + detail pack (Phase 4).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view = static function ( string $number ): string {
	return add_query_arg(
		array(
			'mns_panel'   => 'patient',
			'mns_page'    => 'invoice-view',
			'mns_invoice' => $number,
		),
		home_url( '/nursing-patient-panel/' )
	);
};

$items = array(
	array(
		'number'  => 'INV-1001',
		'service' => 'سرم‌تراپی',
		'when'    => '2026-11-04 18:00',
		'amount'  => 920000,
		'status'  => 'unpaid',
		'payable' => true,
		'url'     => $view( 'INV-1001' ),
	),
	array(
		'number'  => 'INV-0988',
		'service' => 'تزریقات',
		'when'    => '2026-10-20 09:00',
		'amount'  => 420000,
		'status'  => 'paid',
		'payable' => false,
		'url'     => $view( 'INV-0988' ),
	),
	array(
		'number'  => 'INV-0970',
		'service' => 'پانسمان',
		'when'    => '2026-10-01 14:00',
		'amount'  => 350000,
		'status'  => 'void',
		'payable' => false,
		'url'     => $view( 'INV-0970' ),
	),
);

$details = array(
	'INV-1001' => array(
		'number'  => 'INV-1001',
		'service' => 'سرم‌تراپی',
		'when'    => '2026-11-04 18:00',
		'status'  => 'unpaid',
		'order'   => 'MNS-1403-1001',
		'patient' => 'حسن محمدی',
		'base'    => 850000,
		'lines'   => array(
			array( 'label' => 'اقلام مصرفی', 'amount' => 70000 ),
		),
		'discount' => 0,
		'total'    => 920000,
		'note'     => MNS_Copy::t( 'price.travel' ),
	),
	'INV-0988' => array(
		'number'   => 'INV-0988',
		'service'  => 'تزریقات',
		'when'     => '2026-10-20 09:00',
		'status'   => 'paid',
		'order'    => 'MNS-1403-0988',
		'patient'  => 'سارا محمدی',
		'base'     => 420000,
		'lines'    => array(),
		'discount' => 0,
		'total'    => 420000,
		'note'     => '',
		'paid_at'  => '2026-10-20 10:15',
	),
	'INV-0970' => array(
		'number'  => 'INV-0970',
		'service' => 'پانسمان',
		'when'    => '2026-10-01 14:00',
		'status'  => 'void',
		'order'   => 'MNS-1403-0901',
		'patient' => 'پدر',
		'base'    => 350000,
		'lines'   => array(),
		'discount'=> 0,
		'total'   => 350000,
		'note'    => __( 'این فاکتور باطل شده است.', 'medyar-nursing-services' ),
	),
);

return array(
	'items'          => $items,
	'details'        => $details,
	'default_number' => 'INV-1001',
);
