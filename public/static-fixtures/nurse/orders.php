<?php
/**
 * Nurse orders list (Phase 5).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view = static function ( string $code ): string {
	return add_query_arg(
		array(
			'mns_panel' => 'nurse',
			'mns_page'  => 'order-view',
			'mns_order' => $code,
		),
		home_url( '/nursing-nurse-panel/' )
	);
};

return array(
	'items' => array(
		array(
			'tracking' => 'MNS-1403-1001',
			'service'  => 'سرم‌تراپی',
			'patient'  => 'حسن محمدی',
			'area'     => 'ونک',
			'when'     => '2026-11-05 10:00',
			'status'   => 'assigned',
			'tab'      => 'active',
			'url'      => $view( 'MNS-1403-1001' ),
		),
		array(
			'tracking' => 'MNS-1403-1002',
			'service'  => 'تزریقات',
			'patient'  => 'سارا محمدی',
			'area'     => 'جردن',
			'when'     => '2026-11-05 14:30',
			'status'   => 'in_progress',
			'tab'      => 'active',
			'url'      => $view( 'MNS-1403-1002' ),
		),
		array(
			'tracking' => 'MNS-1403-0980',
			'service'  => 'پانسمان',
			'patient'  => 'مریم محمدی',
			'area'     => 'نیاوران',
			'when'     => '2026-11-04 16:00',
			'status'   => 'service_ended',
			'tab'      => 'past',
			'url'      => $view( 'MNS-1403-0980' ),
		),
	),
);
