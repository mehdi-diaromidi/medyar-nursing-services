<?php
/**
 * Nurse order workspace fixture (Phase 5) — status-driven.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base = array(
	'tracking'  => 'MNS-1403-1001',
	'service'   => 'سرم‌تراپی',
	'patient'   => 'حسن محمدی',
	'patient_meta' => '۶۸ ساله · آقا',
	'area'      => 'ونک',
	'address'   => 'تهران، ونک، پلاک ۱۰',
	'when'      => '2026-11-05 10:00',
	'phone'     => '09121112233',
	'essentials'=> array( 'نوع سرم: نرمال سالین', 'حساسیت: پنی‌سیلین' ),
	'notes'     => 'بیمار دیابت نوع ۲ دارد.',
	'checklist' => array(
		array( 'id' => 'c1', 'label' => 'نوع سرم و دستور پزشک ثبت شد' ),
		array( 'id' => 'c2', 'label' => 'مدت حضور ثبت شد' ),
		array( 'id' => 'c3', 'label' => 'علائم حیاتی کنترل شد' ),
		array( 'id' => 'c4', 'label' => 'مشکلات احتمالی یادداشت شد' ),
	),
	'extras'    => array(
		array( 'id' => 'e1', 'label' => 'کنترل فشار خون', 'amount' => 80000 ),
		array( 'id' => 'e2', 'label' => 'تزریق عضلانی اضافه', 'amount' => 120000 ),
	),
	'consumables' => array(
		array( 'id' => 'k1', 'label' => 'ست سرم', 'amount' => 45000 ),
		array( 'id' => 'k2', 'label' => 'چسب و گاز', 'amount' => 25000 ),
	),
	'medication' => array(
		'enabled' => true,
		'status'  => 'pending',
		'label'   => 'در انتظار استعلام مشاور',
	),
);

return array(
	'default_status' => MNS_Order_Status::ASSIGNED,
	'by_status'      => array(
		MNS_Order_Status::ASSIGNED     => array_merge(
			$base,
			array(
				'status'     => MNS_Order_Status::ASSIGNED,
				'can_start'  => true,
				'can_end'    => false,
				'can_urgent' => true,
				'workspace'  => false,
			)
		),
		MNS_Order_Status::IN_PROGRESS  => array_merge(
			$base,
			array(
				'status'     => MNS_Order_Status::IN_PROGRESS,
				'can_start'  => false,
				'can_end'    => true,
				'can_urgent' => true,
				'workspace'  => true,
				'report'     => '',
				'report_locked' => false,
			)
		),
		MNS_Order_Status::SERVICE_ENDED => array_merge(
			$base,
			array(
				'status'        => MNS_Order_Status::SERVICE_ENDED,
				'can_start'     => false,
				'can_end'       => false,
				'can_urgent'    => false,
				'workspace'     => true,
				'report'        => 'سرم به‌طور کامل تزریق شد. علائم پایدار بود.',
				'report_locked' => true,
				'report_at'     => '2026-11-05 11:20',
			)
		),
		MNS_Order_Status::NEEDS_REVIEW => array_merge(
			$base,
			array(
				'status'     => MNS_Order_Status::NEEDS_REVIEW,
				'can_start'  => false,
				'can_end'    => false,
				'can_urgent' => true,
				'workspace'  => false,
			)
		),
	),
);
