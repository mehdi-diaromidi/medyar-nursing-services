<?php
/**
 * New-order wizard fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$districts = array();
for ( $i = 1; $i <= 22; $i++ ) {
	$districts[ 'THR|تهران' ][] = sprintf( 'منطقه %s', MNS_Format::digits( (string) $i ) );
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
			'last_order' => '۱۲ آبان',
			'age_group' => 'adult',
			'gender'    => 'female',
		),
	),
	/* Province > city come from the shared library (assets/data/iran-locations.json); only cities divided into districts are listed here. */
	'districts'     => $districts,
	'time_slots'    => array(
		/* Six 4-hour slots (client decision, 10 Nov 2026); hours are admin settings in the real backend. `disabled` = no nurse in that slot. */
		array( 'id' => '0-4', 'label' => '۰ تا ۴', 'disabled' => true, 'name' => 'بامداد' ),
		array( 'id' => '4-8', 'label' => '۴ تا ۸', 'name' => 'صبح زود' ),
		array( 'id' => '8-12', 'label' => '۸ تا ۱۲', 'name' => 'صبح' ),
		array( 'id' => '12-16', 'label' => '۱۲ تا ۱۶', 'name' => 'ظهر' ),
		array( 'id' => '16-20', 'label' => '۱۶ تا ۲۰', 'name' => 'عصر' ),
		array( 'id' => '20-24', 'label' => '۲۰ تا ۲۴', 'name' => 'شب' ),
	),
	'service_fields' => array(
		'serum_types' => array( 'نرمال سالین', 'دکستروز', 'رینگر', 'تقویتی و ویتامین', 'طبق نسخه پزشک' ),
		'rx_types'    => array( 'طبق نسخه پزشک' ), /* serum types that need a prescription photo */
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
		'yellow_areas'    => array( 'منطقه ۱۸', 'کرج' ),
		'red_area_gender' => array(
			'منطقه ۲۲' => 'male',
		),
		'alt_slots'       => array( '12-16', '16-20', '20-24' ),
	),
);
