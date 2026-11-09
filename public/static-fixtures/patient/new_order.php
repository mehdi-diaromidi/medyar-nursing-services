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
			'label'     => 'خودم — سارا محمدی',
			'meta'      => '۳۴ سال · خانم',
			'age_group' => 'adult',
			'gender'    => 'female',
		),
		array(
			'id'        => '4',
			'name'      => 'فاطمه کریمی',
			'label'     => 'فاطمه کریمی',
			'meta'      => '۴۲ سال · خانم',
			'age_group' => 'adult',
			'gender'    => 'female',
		),
		array(
			'id'        => '2',
			'name'      => 'حسن محمدی',
			'label'     => 'پدر — حسن محمدی',
			'meta'      => '۶۸ سال · آقا · دیابت نوع ۲',
			'age_group' => 'elder',
			'gender'    => 'male',
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
		array( 'id' => '8-10', 'label' => '۸ تا ۱۰' ),
		array( 'id' => '10-12', 'label' => '۱۰ تا ۱۲' ),
		array( 'id' => '12-14', 'label' => '۱۲ تا ۱۴' ),
		array( 'id' => '14-16', 'label' => '۱۴ تا ۱۶' ),
		array( 'id' => '16-18', 'label' => '۱۶ تا ۱۸' ),
		array( 'id' => '18-20', 'label' => '۱۸ تا ۲۰' ),
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
		'alt_slots'       => array( '12-14', '14-16', '16-18' ),
	),
);
