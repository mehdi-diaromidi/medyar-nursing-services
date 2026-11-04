<?php
/**
 * Loyalty stamps fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'filled'      => 2,
	'required'    => 5,
	'stamps'      => '۲ از ۵',
	'progress'    => '۳ مهر تا تخفیف بعدی',
	'code'        => '',
	'codes'       => array(),
	'first_order' => 'استفاده‌شده',
	'rules'       => 'هر خدمت تکمیل‌شده یک مهر است. با تکمیل کارت، کد تخفیف برای سفارش بعدی صادر می‌شود.',
);
