<?php
/**
 * Loyalty stamps + discount codes (Phase 4).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'filled'      => 3,
	'required'    => 5,
	'progress'    => '۳ از ۵ — ۲ مهر تا تخفیف بعدی',
	'rules'       => 'هر خدمت تکمیل‌شده یک مهر است. با تکمیل کارت، کد تخفیف برای سفارش بعدی صادر می‌شود.',
	'first_order' => 'استفاده‌شده',
	'codes'       => array(
		array(
			'code'    => 'MNS-LOYAL-15',
			'label'   => '۱۵٪ تخفیف سفارش بعدی',
			'expires' => '2026-12-01',
			'active'  => true,
		),
	),
);
