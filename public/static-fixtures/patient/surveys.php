<?php
/**
 * Open surveys + complaint path (relative codes).
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

return array(
	'open' => array(
		array(
			'id'      => 's1',
			'order'   => $code( '0988' ),
			'service' => 'تزریقات',
			'nurse'   => 'سارا احمدی',
			'when'    => $now->modify( '-16 days' )->setTime( 9, 0 )->format( 'Y-m-d H:i' ),
		),
	),
	'done' => array(
		array(
			'id'      => 's0',
			'order'   => $code( '0800' ),
			'service' => 'سرم‌تراپی',
			'nurse'   => 'علی رضایی',
			'science' => 5,
			'ethics'  => 4,
			'orderly' => 5,
			'comment' => 'حرفه‌ای و دقیق بودند.',
		),
	),
	'complaints' => array(
		array(
			'id'      => 'c1',
			'subject' => 'تأخیر در مراجعه',
			'status'  => 'reviewing',
			'label'   => 'در حال بررسی',
			'when'    => $now->modify( '-21 days' )->format( 'Y-m-d' ),
		),
	),
);
