<?php
/**
 * Patient orders list fixture (Phase 3 — card tabs).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view = static function ( string $code ): string {
	return add_query_arg(
		array(
			'mns_panel' => 'patient',
			'mns_page'  => 'order-view',
			'mns_order' => $code,
		),
		home_url( '/nursing-patient-panel/' )
	);
};

return array(
	'items' => array(
		array(
			'tracking'      => 'MNS-1403-1001',
			'service'       => 'سرم‌تراپی',
			'patient'       => 'خودم',
			'when'          => '2026-11-05 10:00',
			'status'        => 'assigned',
			'tab'           => 'active',
			'nurse'         => 'علی رضایی',
			'next_action'   => 'جزئیات',
			'next_url'      => $view( 'MNS-1403-1001' ),
			'recurring'     => false,
		),
		array(
			'tracking'      => 'MNS-1403-1012',
			'service'       => 'پانسمان',
			'patient'       => 'مادر',
			'when'          => '2026-11-07 16:30',
			'status'        => 'submitted',
			'tab'           => 'active',
			'nurse'         => '',
			'next_action'   => 'پیگیری',
			'next_url'      => $view( 'MNS-1403-1012' ),
			'recurring'     => true,
			'recurring_label' => 'نوبت ۳ از ۶',
		),
		array(
			'tracking'      => 'MNS-1403-0970',
			'service'       => 'تزریقات',
			'patient'       => 'خودم',
			'when'          => '2026-10-28 11:00',
			'status'        => 'invoiced',
			'tab'           => 'active',
			'nurse'         => 'سارا احمدی',
			'next_action'   => 'پرداخت',
			'next_url'      => MNS_Pages::panel_url( 'patient', 'invoices' ),
			'recurring'     => false,
		),
		array(
			'tracking'      => 'MNS-1403-0988',
			'service'       => 'تزریقات',
			'patient'       => 'خودم',
			'when'          => '2026-10-20 09:00',
			'status'        => 'completed',
			'tab'           => 'past',
			'nurse'         => 'سارا احمدی',
			'next_action'   => 'ثبت نظر',
			'next_url'      => MNS_Pages::panel_url( 'patient', 'surveys' ),
			'recurring'     => false,
		),
		array(
			'tracking'      => 'MNS-1403-0901',
			'service'       => 'مراقبت',
			'patient'       => 'پدر',
			'when'          => '2026-10-01 14:00',
			'status'        => 'cancelled',
			'tab'           => 'cancelled',
			'nurse'         => '',
			'next_action'   => 'درخواست مجدد',
			'next_url'      => MNS_Pages::panel_url( 'patient', 'new-order' ),
			'recurring'     => false,
		),
	),
);
