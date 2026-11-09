<?php
/**
 * New-order wizard fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'default_phone' => '09123456789',
	'service'       => array(
		'id'        => '1',
		'slug'      => 'serum-home',
		'name'      => 'سرم‌تراپی در منزل',
		'price_min' => 850000,
		'price_max' => 1100000,
	),
	'patients'      => array(
		array(
			'id'        => 'self',
			'name'      => 'سارا محمدی',
			'label'     => 'خودم',
			'meta'      => 'سارا محمدی',
			'age_group' => 'adult',
			'gender'    => 'female',
		),
		array(
			'id'        => '4',
			'name'      => 'فاطمه کریمی',
			'label'     => 'فاطمه کریمی',
			'meta'      => 'بیمار ثبت‌شده قبلی',
			'age_group' => 'adult',
			'gender'    => 'female',
		),
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
	'time_slots'    => array(
		/* Six 4-hour slots (client decision, 10 Nov 2026); hours are admin settings in the real backend. `disabled` = no nurse in that slot. */
		array( 'id' => '0-4', 'label' => '۰ تا ۴', 'disabled' => true ),
		array( 'id' => '4-8', 'label' => '۴ تا ۸' ),
		array( 'id' => '8-12', 'label' => '۸ تا ۱۲' ),
		array( 'id' => '12-16', 'label' => '۱۲ تا ۱۶' ),
		array( 'id' => '16-20', 'label' => '۱۶ تا ۲۰' ),
		array( 'id' => '20-24', 'label' => '۲۰ تا ۲۴' ),
	),
	'service_fields' => array(
		'serum_types' => array( 'نرمال سالین', 'دکستروز', 'رینگر', 'تقویتی و ویتامین', 'طبق نسخه پزشک' ),
		'vein_access' => array(
			array( 'value' => 'normal', 'label' => 'معمولی' ),
			array( 'value' => 'hard', 'label' => 'دشوار' ),
			array( 'value' => 'unknown', 'label' => 'نمی‌دانم' ),
		),
		'allergy'     => array(
			array( 'value' => 'no', 'label' => 'ندارد' ),
			array( 'value' => 'yes', 'label' => 'دارد' ),
			array( 'value' => 'unknown', 'label' => 'نمی‌دانم' ),
		),
	),
	'availability'  => array(
		'yellow_areas'    => array( 'گوهردشت' ),
		'red_area_gender' => array(
			'نیاوران' => 'male',
		),
		'alt_slots'       => array( '12-16', '16-20', '20-24' ),
	),
);
