<?php
/**
 * Nurse orders list fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view = add_query_arg(
	array(
		'mns_panel' => 'nurse',
		'mns_page'  => 'order-view',
		'mns_order' => 'MNS-1403-1001',
	),
	get_permalink()
);
$view = is_string( $view ) ? $view : MNS_Pages::panel_url( 'nurse', 'order-view' );

return array(
	'items' => array(
		array(
			'service'  => 'سرم‌تراپی',
			'meta'     => 'ونک · ۱۰:۰۰ · بیمار: حسن محمدی',
			'status'   => 'تخصیص‌یافته',
			'tone'     => 'progress',
			'view_url' => $view,
		),
		array(
			'service'  => 'تزریقات',
			'meta'     => 'جردن · ۱۴:۳۰ · بیمار: سارا محمدی',
			'status'   => 'در حال انجام',
			'tone'     => 'progress',
			'view_url' => $view,
		),
	),
);
