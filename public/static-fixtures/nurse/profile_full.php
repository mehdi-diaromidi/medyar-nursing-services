<?php
/**
 * Nurse profile fixture (P-05): same page as the affiliate panel's "My profile" with nurse fields.
 *
 * Everything is editable by the nurse except the mobile number (it is the login number; changing it goes
 * through support).
 *
 * TODO(backend): store in the nurse profile + user meta; avatar upload (5 MB, jpg/png/webp) with the same
 * handling as the affiliate panel; locked fields are rejected server-side.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'first_name'    => 'علی',
	'last_name'     => 'رضایی',
	'display_name'  => 'علی رضایی',
	'email'         => 'ali.rezaei@example.com',
	'mobile'        => '09129876543',
	'birthday'      => '۱۳۷۰/۰۵/۱۲',
	'nid'           => '0012345678',
	'gender'        => 'مرد',
	'qualification' => 'bsc',
	'qualification_options' => array(
		'diploma' => 'بهیار / کمک‌پرستار',
		'assoc'   => 'کاردانی پرستاری',
		'bsc'     => 'کارشناس پرستاری',
		'msc'     => 'کارشناس ارشد پرستاری',
		'phd'     => 'دکترای پرستاری',
	),
	'license_no'    => '۱۲۳۴۵۶',
	'experience'    => '5-10',
	'workplace'     => 'بیمارستان میلاد',
	'about'         => 'پرستار با سابقه مراقبت در منزل؛ تمرکز روی سرم‌تراپی و تزریقات ایمن با رعایت پروتکل‌های مدیار.',
	'experience_options' => array(
		'0-1'  => 'کمتر از ۱ سال',
		'1-3'  => '۱ تا ۳ سال',
		'3-5'  => '۳ تا ۵ سال',
		'5-10' => '۵ تا ۱۰ سال',
		'10+'  => 'بیش از ۱۰ سال',
	),
	'support_tel'   => 'tel:02188607015',
);
