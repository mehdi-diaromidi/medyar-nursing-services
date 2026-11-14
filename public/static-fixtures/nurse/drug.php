<?php
/**
 * Nurse medication-step fixture (P-33/34).
 *
 * One form. Not every drug needs a doctor's prescription, so the question lives on each drug card
 * ("needs a prescription or the doctor's approval?"). Only when at least one drug says yes does the
 * "doctor approval" section appear: prescription photo, or a consult whose result is recorded at once and then locked.
 * A "not approved" consult forces "not given" only on the drugs that needed approval.
 * We never judge what is urgent; "not approved" only follows the doctor's answer.
 *
 * Cases (static UI preview): none, otc, rx, ok, denied, noanswer.
 *
 * TODO(backend): the consult result is a separate immediate, uneditable action (server time stored, the order flagged
 * "needs review" and admins notified on "not approved", all in one transaction); one fixed support-doctor phone number,
 * revealed only once the step is open; files go to private storage (real type check, GPS stripped, <= 10MB each).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$needs_approval = array(
	'name'   => 'سفتریاکسون',
	'dose'   => '۱ گرم',
	'route'  => 'تزریقی',
	'needs'  => 'yes',
	'status' => 'تجویز شد',
	'photos' => array( 'دارو-۱.jpg' ),
	'note'   => '',
);
$over_the_counter = array(
	'name'   => 'استامینوفن',
	'dose'   => '۵۰۰ میلی‌گرم',
	'route'  => 'خوراکی',
	'needs'  => 'no',
	'status' => 'تجویز شد',
	'photos' => array( 'دارو-۲.jpg' ),
	'note'   => '',
);

return array(
	'phone'  => '02188607015',
	'routes' => array( 'تزریقی', 'سرم', 'خوراکی', 'موضعی', 'سایر' ),
	'info'   => 'حساسیت: پنی‌سیلین · دیابت نوع ۲',
	'cases'  => array(
		'none'     => array(),
		'otc'      => array(
			'drugs' => array( $over_the_counter ),
		),
		'rx'       => array(
			'path'      => 'rx',
			'rx_photos' => array( 'نسخه-۱.jpg' ),
			'rx_doctor' => 'دکتر رضایی',
			'drugs'     => array( $needs_approval, $over_the_counter ),
		),
		'ok'       => array(
			'path'    => 'consult',
			'consult' => array(
				'result'   => 'ok',
				'time'     => '۱۴:۲۰',
				'note'     => '',
				'attempts' => array(),
			),
			'drugs'   => array( $needs_approval, $over_the_counter ),
		),
		'denied'   => array(
			'path'    => 'consult',
			'consult' => array(
				'result'   => 'denied',
				'time'     => '۱۴:۲۰',
				'note'     => 'دوز نیاز به بازبینی دارد',
				'attempts' => array(),
			),
			'drugs'   => array( array_merge( $needs_approval, array( 'status' => 'تجویز نشد' ) ), $over_the_counter ),
		),
		'noanswer' => array(
			'path'    => 'consult',
			'consult' => array(
				'result'   => '',
				'time'     => '',
				'note'     => '',
				'attempts' => array( '۱۴:۱۰' ),
			),
			'drugs'   => array( $needs_approval ),
		),
	),
);
