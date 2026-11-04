<?php
/**
 * New-order form fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'default_phone' => '09121234567',
	'patients'      => array(
		array(
			'id'     => 'self',
			'name'   => 'سارا محمدی',
			'label'  => 'خودم — سارا محمدی',
			'age'    => '۳۴',
			'gender' => 'female',
		),
		array(
			'id'     => '2',
			'name'   => 'حسن محمدی',
			'label'  => 'پدر — حسن محمدی',
			'age'    => '۶۸',
			'gender' => 'male',
		),
	),
	'categories'    => array(
		array( 'id' => 'serum', 'name' => 'سرم‌تراپی' ),
		array( 'id' => 'inject', 'name' => 'تزریقات' ),
		array( 'id' => 'dress', 'name' => 'پانسمان' ),
		array( 'id' => 'cath', 'name' => 'سوندگذاری' ),
		array( 'id' => 'care', 'name' => 'مراقبت' ),
		array( 'id' => 'other', 'name' => 'سایر' ),
	),
	'services'      => array(
		array( 'id' => '1', 'category' => 'serum', 'name' => 'سرم‌تراپی' ),
		array( 'id' => '2', 'category' => 'inject', 'name' => 'تزریقات' ),
		array( 'id' => '3', 'category' => 'dress', 'name' => 'پانسمان' ),
		array( 'id' => '4', 'category' => 'cath', 'name' => 'سوندگذاری' ),
		array( 'id' => '5', 'category' => 'care', 'name' => 'مراقبت کوتاه‌مدت' ),
	),
	'cities'        => array(
		array(
			'id'    => '1',
			'name'  => 'تهران',
			'areas' => array( 'ونک', 'جردن', 'سعادت‌آباد', 'نیاوران' ),
		),
		array(
			'id'    => '2',
			'name'  => 'کرج',
			'areas' => array( 'گوهردشت', 'عظیمیه' ),
		),
	),
	'times'         => array( '۰۹:۰۰', '۱۰:۰۰', '۱۱:۰۰', '۱۴:۰۰', '۱۶:۰۰', '۱۸:۰۰' ),
);
