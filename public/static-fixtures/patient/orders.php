<?php
/**
 * Patient «my orders» fixture — mirrors desgin/png/سفارش های من/medyar-my-orders-*.png.
 *
 * Each item: code, service, patient_id, when (Y-m-d H:i), status, nurse
 * (name|'' + gender), amount (int|null; null = before invoice), recurring
 * label, reviewed, dispute, ticket, created (for «newest first»).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$j = static function ( int $jm, int $jd, string $time ): string {
	list( $gy, $gm, $gd ) = MNS_Format::j2g( 1405, $jm, $jd );
	return sprintf( '%04d-%02d-%02d %s', $gy, $gm, $gd, $time );
};

$items = array(
	array(
		'code'       => '48190',
		'service'    => 'سرم‌تراپی در منزل',
		'category'   => 'serum',
		'patient_id' => 'p1',
		'when'       => $j( 8, 12, '10:00' ),
		'status'     => 'invoiced',
		'nurse'      => array( 'name' => 'نورا احمدی', 'gender' => 'female' ),
		'amount'     => 980000,
	),
	array(
		'code'       => '48215',
		'service'    => 'سرم‌تراپی در منزل',
		'category'   => 'serum',
		'patient_id' => 'p1',
		'when'       => MNS_Format::rel( '+1 day', '10:00' ),
		'status'     => 'submitted',
		'nurse'      => null,
		'amount'     => null,
		'recurring'  => array( 'n' => 3, 'of' => 6 ),
	),
	array(
		'code'       => '48201',
		'service'    => 'تزریقات در منزل',
		'category'   => 'injection',
		'patient_id' => 'p2',
		'when'       => MNS_Format::rel( 'today', '16:00' ),
		'status'     => 'assigned',
		'nurse'      => array( 'name' => 'سارا رضایی', 'gender' => 'female' ),
		'amount'     => null,
	),
	array(
		'code'       => '48177',
		'service'    => 'نمونه‌گیری آزمایش',
		'category'   => 'sampling',
		'patient_id' => 'p1',
		'when'       => $j( 8, 14, '08:00' ),
		'status'     => 'in_progress',
		'nurse'      => array( 'name' => 'علی نجفی', 'gender' => 'male' ),
		'amount'     => null,
	),
	array(
		'code'       => '48102',
		'service'    => 'سرم‌تراپی در منزل',
		'category'   => 'serum',
		'patient_id' => 'p1',
		'when'       => $j( 8, 3, '10:00' ),
		'status'     => 'completed',
		'nurse'      => array( 'name' => 'نورا احمدی', 'gender' => 'female' ),
		'amount'     => 930000,
		'reviewed'   => false,
	),
	array(
		'code'       => '48044',
		'service'    => 'پانسمان در منزل',
		'category'   => 'dressing',
		'patient_id' => 'p2',
		'when'       => $j( 7, 28, '17:00' ),
		'status'     => 'completed',
		'nurse'      => array( 'name' => 'سارا رضایی', 'gender' => 'female' ),
		'amount'     => 650000,
		'reviewed'   => true,
	),
	array(
		'code'       => '47980',
		'service'    => 'تزریقات در منزل',
		'category'   => 'injection',
		'patient_id' => 'p1',
		'when'       => $j( 7, 20, '09:00' ),
		'status'     => 'cancelled',
		'nurse'      => null,
		'amount'     => null,
	),
);

/* Older history so counts match the design (۱۸ completed, ۲ cancelled). */
$older_services = array(
	array( 'سرم‌تراپی در منزل', 'serum' ),
	array( 'تزریقات در منزل', 'injection' ),
	array( 'پانسمان در منزل', 'dressing' ),
	array( 'نمونه‌گیری آزمایش', 'sampling' ),
	array( 'مراقبت از سالمند', 'elderly' ),
);
$older_nurses = array(
	array( 'name' => 'نورا احمدی', 'gender' => 'female' ),
	array( 'name' => 'سارا رضایی', 'gender' => 'female' ),
	array( 'name' => 'علی نجفی', 'gender' => 'male' ),
);
for ( $i = 0; $i < 17; $i++ ) {
	$svc       = $older_services[ $i % count( $older_services ) ];
	$cancelled = ( 16 === $i );
	$jm        = 7 - intdiv( $i, 5 );
	$jd        = max( 1, 18 - ( ( $i * 4 ) % 17 ) );
	$items[]   = array(
		'code'       => '' . ( 47960 - ( $i * 37 ) ),
		'service'    => $svc[0],
		'category'   => $svc[1],
		'patient_id' => 0 === $i % 3 ? 'p2' : 'p1',
		'when'       => $j( $jm, $jd, sprintf( '%02d:00', 9 + ( $i % 8 ) ) ),
		'status'     => $cancelled ? 'cancelled' : 'completed',
		'nurse'      => $cancelled ? null : $older_nurses[ $i % 3 ],
		'amount'     => $cancelled ? null : 420000 + ( ( $i % 6 ) * 85000 ),
		'reviewed'   => true,
	);
}

return array(
	'patients' => array(
		array(
			'id'       => 'p1',
			'name'     => 'مریم کریمی',
			'relation' => 'خودم',
		),
		array(
			'id'       => 'p2',
			'name'     => 'فاطمه کریمی',
			'relation' => '',
		),
	),
	'services' => array(
		'serum'     => 'سرم‌تراپی',
		'injection' => 'تزریقات',
		'dressing'  => 'پانسمان',
		'sampling'  => 'نمونه‌گیری',
		'elderly'   => 'مراقبت سالمند',
		'other'     => 'سایر',
	),
	'first_order_discount' => true,
	'items'    => $items,
);
