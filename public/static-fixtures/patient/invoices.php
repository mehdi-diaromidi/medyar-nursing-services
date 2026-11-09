<?php
/**
 * Patient invoices — mirrors desgin/png/فاکتورها و پرداخت ها/medyar-invoices-*.png + medyar-invoice-detail-desktop.png.
 *
 * Item: number, service, category, patient_id, order (5-digit tracking), issued (Y-m-d),
 * amount, status unpaid|dispute|paid, type normal|prepay|supplement, revised (bool),
 * paid_at (Y-m-d H:i), pay_method online|manual, txn.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$j = static function ( int $jm, int $jd, string $time = '' ): string {
	list( $gy, $gm, $gd ) = MNS_Format::j2g( 1405, $jm, $jd );
	return sprintf( '%04d-%02d-%02d', $gy, $gm, $gd ) . ( '' !== $time ? ' ' . $time : '' );
};

$items = array(
	array(
		'number'     => 'INV-2031',
		'service'    => 'سرم‌تراپی در منزل',
		'category'   => 'serum',
		'patient_id' => 'p1',
		'order'      => '48190',
		'issued'     => $j( 8, 12 ),
		'amount'     => 1250000,
		'status'     => 'unpaid',
		'type'       => 'supplement',
		'revised'    => array( 'version' => 2, 'at' => $j( 8, 14 ) ),
	),
	array(
		'number'     => 'INV-2027',
		'service'    => 'تزریقات در منزل',
		'category'   => 'injection',
		'patient_id' => 'p2',
		'order'      => '48201',
		'issued'     => $j( 8, 10 ),
		'amount'     => 980000,
		'status'     => 'dispute',
		'type'       => 'normal',
	),
	array(
		'number'     => 'INV-2018',
		'service'    => 'سرم‌تراپی در منزل',
		'category'   => 'serum',
		'patient_id' => 'p1',
		'order'      => '48190',
		'issued'     => $j( 8, 3 ),
		'amount'     => 3280000,
		'status'     => 'paid',
		'type'       => 'prepay',
		'paid_at'    => $j( 8, 3, '11:20' ),
		'pay_method' => 'online',
		'txn'        => '842196375',
	),
	array(
		'number'     => 'INV-2009',
		'service'    => 'پانسمان در منزل',
		'category'   => 'dressing',
		'patient_id' => 'p2',
		'order'      => '48044',
		'issued'     => $j( 7, 28 ),
		'amount'     => 650000,
		'status'     => 'paid',
		'type'       => 'normal',
		'revised'    => array( 'version' => 2, 'at' => $j( 7, 29 ) ),
		'paid_at'    => $j( 7, 29, '13:05' ),
		'pay_method' => 'manual',
		'txn'        => '731045528',
	),
	array(
		'number'     => 'INV-1990',
		'service'    => 'نمونه‌گیری آزمایش',
		'category'   => 'sampling',
		'patient_id' => 'p1',
		'order'      => '47923',
		'issued'     => $j( 7, 20 ),
		'amount'     => 430000,
		'status'     => 'paid',
		'type'       => 'normal',
		'paid_at'    => $j( 7, 20, '09:40' ),
		'pay_method' => 'online',
		'txn'        => '650118734',
	),
);

$older = array(
	array( 'سرم‌تراپی در منزل', 'serum' ),
	array( 'تزریقات در منزل', 'injection' ),
	array( 'پانسمان در منزل', 'dressing' ),
	array( 'مراقبت از سالمند', 'elderly' ),
);
for ( $i = 0; $i < 7; $i++ ) {
	$svc     = $older[ $i % count( $older ) ];
	$jm      = 7 - intdiv( $i, 4 );
	$jd      = max( 1, 16 - ( $i * 2 ) );
	$items[] = array(
		'number'     => 'INV-' . ( 1984 - ( $i * 7 ) ),
		'service'    => $svc[0],
		'category'   => $svc[1],
		'patient_id' => 0 === $i % 3 ? 'p2' : 'p1',
		'order'      => (string) ( 47900 - ( $i * 37 ) ),
		'issued'     => $j( $jm, $jd ),
		'amount'     => 420000 + ( ( $i % 5 ) * 95000 ),
		'status'     => 'paid',
		'type'       => 'normal',
		'paid_at'    => $j( $jm, $jd, sprintf( '%02d:%02d', 10 + $i, ( $i * 7 ) % 60 ) ),
		'pay_method' => 'online',
		'txn'        => (string) ( 600000000 + ( $i * 1234567 ) ),
	);
}

/* Line items: kind => chip label; amounts in toman; negative = discount. */
$lines_2031 = array(
	array( 'kind' => 'base', 'title' => 'سرم‌تراپی در منزل', 'sub' => 'مدت حضور: ۷۵ دقیقه', 'qty' => 1, 'unit' => 900000 ),
	array( 'kind' => 'extra', 'title' => 'تعویض کاتتر', 'qty' => 1, 'unit' => 120000 ),
	array( 'kind' => 'supply', 'title' => 'ست سرم', 'qty' => 2, 'unit' => 90000 ),
	array( 'kind' => 'drug', 'title' => 'مشاوره دارویی', 'qty' => 1, 'unit' => 150000 ),
	array( 'kind' => 'first_discount', 'title' => 'تخفیف سفارش اول', 'qty' => 0, 'unit' => 0, 'total' => -60000 ),
	array( 'kind' => 'loyalty', 'title' => 'کد پاداش وفاداری', 'qty' => 0, 'unit' => 0, 'total' => -40000 ),
);
$lines_2018 = array(
	array( 'kind' => 'base', 'title' => 'سرم‌تراپی در منزل', 'sub' => 'مدت حضور: ۷۵ دقیقه', 'qty' => 1, 'unit' => 840000 ),
	array( 'kind' => 'extra', 'title' => 'تعویض کاتتر', 'qty' => 1, 'unit' => 120000 ),
	array( 'kind' => 'supply', 'title' => 'ست سرم', 'qty' => 2, 'unit' => 90000 ),
	array( 'kind' => 'first_discount', 'title' => 'تخفیف سفارش اول', 'qty' => 0, 'unit' => 0, 'total' => -60000 ),
);

return array(
	'patients' => array(
		'p1' => 'سارا محمدی',
		'p2' => 'فاطمه کریمی',
	),
	'nurses'   => array(
		'48190' => 'نورا احمدی',
		'48201' => 'سارا رضایی',
		'48044' => 'سارا رضایی',
	),
	'services' => array(
		'serum'     => 'سرم‌تراپی',
		'injection' => 'تزریقات',
		'dressing'  => 'پانسمان',
		'sampling'  => 'نمونه‌گیری',
		'elderly'   => 'مراقبت سالمند',
	),
	'wallet'   => array(
		'balance'      => 80000,
		'transactions' => array(
			array( 'at' => $j( 8, 10 ), 'title' => 'بازگشت مابه‌التفاوت پیش‌پرداخت، سرم‌تراپی، نوبت ۳', 'amount' => 80000 ),
		),
	),
	'items'    => $items,
	'lines'    => array(
		'INV-2031' => $lines_2031,
		'INV-2018' => $lines_2018,
	),
	'service_at' => array(
		'INV-2018' => $j( 8, 3, '10:00' ),
	),
	'related'  => array(
		'INV-2031' => array(
			'items'  => array( 'INV-2018', 'INV-2031' ),
			'refund' => array( 'amount' => 80000, 'at' => $j( 8, 10 ) ),
		),
		'INV-2018' => array(
			'items'  => array( 'INV-2018', 'INV-2031' ),
			'refund' => array( 'amount' => 80000, 'at' => $j( 8, 10 ) ),
		),
	),
	'default_number' => 'INV-2031',
);
