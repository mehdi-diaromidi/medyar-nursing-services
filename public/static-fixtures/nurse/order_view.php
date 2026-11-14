<?php
/**
 * Nurse order workspace fixture (P-28): one order (serum therapy, which has the medication step),
 * shown in every status. Steps: checklist, medication (only for services marked so by the admin), report.
 *
 * TODO(backend): phone, address and map link only come from the server response for the assigned nurse
 * and are removed after completed / cancelled; the share is 90% of the stored invoice version.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz    = wp_timezone();
$now   = new DateTimeImmutable( 'now', $tz );
$today = $now->setTime( 0, 0 );
$ts    = static function ( string $mod, int $h, int $m = 0 ) use ( $today ): int {
	return $today->modify( $mod )->setTime( $h, $m )->getTimestamp();
};
$jy    = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );

$base = array(
	'tracking'     => 'MNS-' . $jy[0] . '-1001',
	'service'      => 'سرم‌تراپی',
	'patient'      => 'حسن محمدی',
	'patient_meta' => 'آقا · ۶۸ ساله',
	'area'         => 'تهران، ونک',
	'address'      => 'ونک، خیابان ملاصدرا، کوچهٔ شیراز جنوبی، پلاک ۱۰، واحد ۴',
	'phone'        => '09121112233',
	'map_url'      => 'https://www.google.com/maps/dir/?api=1&destination=35.7575,51.4098',
	'date'         => $today->format( 'Y-m-d' ),
	'from'         => 12,
	'to'           => 16,
	'start_ts'     => $ts( 'today', 12 ),
	'session'      => array( 3, 7 ),
	'urgent'       => false,
	'essentials'   => array(
		array( 'label' => 'حساسیت: پنی‌سیلین', 'tone' => 'danger' ),
		array( 'label' => 'دیابت نوع ۲', 'tone' => 'warn' ),
		array( 'label' => 'نوع سرم: نرمال سالین', 'tone' => 'info' ),
	),
	'notes'        => 'رگ دست چپ سخت پیدا می‌شود.',
	'needs_drug'   => true,
	'time_changed' => array(
		'from' => 'امروز ۱۰:۰۰ تا ۱۴:۰۰',
		'to'   => 'امروز ۱۲:۰۰ تا ۱۶:۰۰',
	),
);

$ev = static function ( string $label, string $at ): array {
	return array( 'label' => $label, 'at' => $at );
};
$timeline = array(
	'placed'   => $ev( 'سفارش ثبت شد', 'دیروز ۲۱:۱۰' ),
	'assigned' => $ev( 'سفارش به شما تخصیص یافت', 'دیروز ۲۱:۱۲' ),
	'changed'  => $ev( 'زمان سفارش تغییر کرد', 'امروز ۰۸:۳۰' ),
	'started'  => $ev( 'خدمت شروع شد', 'امروز ۱۲:۰۵' ),
	'ended'    => $ev( 'خدمت پایان یافت · مدت ۴۵ دقیقه', 'امروز ۱۲:۵۰' ),
	'checklist' => $ev( 'چک‌لیست ثبت نهایی شد', 'امروز ۱۳:۰۰' ),
	'drug'     => $ev( 'مرحلهٔ دارو ثبت نهایی شد', 'امروز ۱۳:۰۵' ),
	'report'   => $ev( 'گزارش پایان مراجعه ثبت شد', 'امروز ۱۳:۱۰' ),
	'invoiced' => $ev( 'فاکتور صادر شد', 'امروز ۱۳:۱۰' ),
	'paid'     => $ev( 'بیمار پرداخت کرد و سهم شما به کیف پول اضافه شد', 'امروز ۱۸:۴۰' ),
);

$steps = static function ( string $check, string $drug, string $report, int $done = 5 ): array {
	return array(
		'checklist' => array( 'state' => $check, 'done' => $done, 'total' => 8 ),
		'drug'      => array( 'state' => $drug ),
		'report'    => array( 'state' => $report ),
	);
};

$finance_wait = array(
	'total'  => 1250000,
	'share'  => 1125000,
	'state'  => 'waiting',
	'rows'   => array(
		array( 'label' => 'سرم‌تراپی', 'amount' => 900000 ),
		array( 'label' => 'کنترل فشار خون', 'amount' => 80000 ),
		array( 'label' => 'ست سرم و اقلام', 'amount' => 70000 ),
		array( 'label' => 'مشاوره پزشکی', 'amount' => 200000 ),
	),
);
$finance_paid = array_merge(
	$finance_wait,
	array(
		'state'   => 'paid',
		'paid_at' => 'امروز ۱۸:۴۰',
		'tx'      => 'TX-48215',
	)
);

return array(
	'default_status' => MNS_Order_Status::ASSIGNED,
	'by_status'      => array(
		MNS_Order_Status::ASSIGNED      => array_merge(
			$base,
			array(
				'status'   => MNS_Order_Status::ASSIGNED,
				'can_start' => $now->getTimestamp() >= $ts( 'today', 11 ),
				'steps'    => $steps( 'locked', 'locked', 'locked' ),
				'timeline' => array( $timeline['placed'], $timeline['assigned'], $timeline['changed'] ),
				'changed'  => true,
			)
		),
		MNS_Order_Status::IN_PROGRESS   => array_merge(
			$base,
			array(
				'status'   => MNS_Order_Status::IN_PROGRESS,
				'steps'    => $steps( 'draft', 'todo', 'locked' ),
				'timeline' => array( $timeline['placed'], $timeline['assigned'], $timeline['started'] ),
			)
		),
		MNS_Order_Status::SERVICE_ENDED => array_merge(
			$base,
			array(
				'status'   => MNS_Order_Status::SERVICE_ENDED,
				'steps'    => $steps( 'draft', 'todo', 'todo' ),
				'timeline' => array( $timeline['placed'], $timeline['assigned'], $timeline['started'], $timeline['ended'] ),
			)
		),
		MNS_Order_Status::INVOICED      => array_merge(
			$base,
			array(
				'status'   => MNS_Order_Status::INVOICED,
				'steps'    => $steps( 'submitted', 'submitted', 'submitted', 8 ),
				'finance'  => $finance_wait,
				'timeline' => array( $timeline['placed'], $timeline['assigned'], $timeline['started'], $timeline['ended'], $timeline['checklist'], $timeline['drug'], $timeline['report'], $timeline['invoiced'] ),
			)
		),
		MNS_Order_Status::PAID          => array_merge(
			$base,
			array(
				'status'   => MNS_Order_Status::PAID,
				'steps'    => $steps( 'submitted', 'submitted', 'submitted', 8 ),
				'finance'  => $finance_paid,
				'restricted' => true,
				'timeline' => array( $timeline['placed'], $timeline['assigned'], $timeline['started'], $timeline['ended'], $timeline['checklist'], $timeline['drug'], $timeline['report'], $timeline['invoiced'], $timeline['paid'] ),
			)
		),
		MNS_Order_Status::COMPLETED     => array_merge(
			$base,
			array(
				'status'   => MNS_Order_Status::COMPLETED,
				'steps'    => $steps( 'submitted', 'submitted', 'submitted', 8 ),
				'finance'  => $finance_paid,
				'restricted' => true,
				'timeline' => array( $timeline['placed'], $timeline['assigned'], $timeline['started'], $timeline['ended'], $timeline['checklist'], $timeline['drug'], $timeline['report'], $timeline['invoiced'], $timeline['paid'] ),
			)
		),
		MNS_Order_Status::CANCELLED     => array_merge(
			$base,
			array(
				'status'     => MNS_Order_Status::CANCELLED,
				'restricted' => true,
				'cancelled_by' => 'بیمار',
				'steps'      => $steps( 'locked', 'locked', 'locked' ),
				'timeline'   => array( $timeline['placed'], $timeline['assigned'], $ev( 'سفارش توسط بیمار لغو شد', 'امروز ۰۹:۱۰' ) ),
			)
		),
		MNS_Order_Status::NEEDS_REVIEW  => array_merge(
			$base,
			array(
				'status'     => MNS_Order_Status::NEEDS_REVIEW,
				'review_why' => 'زمان خدمت گذشته و سفارش شروع نشده است.',
				'steps'      => $steps( 'locked', 'locked', 'locked' ),
				'timeline'   => array( $timeline['placed'], $timeline['assigned'], $ev( 'سفارش برای بررسی علامت خورد', 'امروز ۰۰:۰۵' ) ),
			)
		),
	),
);
