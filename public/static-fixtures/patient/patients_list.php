<?php
/**
 * Patient saved-people list (Phase 4).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'items' => array(
		array(
			'id'            => 'self',
			'name'          => 'سارا محمدی',
			'relation'      => 'خودم',
			'age'           => 34,
			'gender'        => 'female',
			'gender_label'  => 'خانم',
			'phone'         => '09121234567',
			'comorbidities' => '',
			'is_self'       => true,
		),
		array(
			'id'            => '2',
			'name'          => 'حسن محمدی',
			'relation'      => 'پدر',
			'age'           => 68,
			'gender'        => 'male',
			'gender_label'  => 'آقا',
			'phone'         => '09121112233',
			'comorbidities' => '',
			'conditions'    => array( 'diabetes', 'hypertension' ),
			'allergy'       => 'yes',
			'allergy_detail' => 'پنی‌سیلین',
			'medications'   => 'متفورمین، آملودیپین',
			'mobility'      => 'assisted',
			'is_self'       => false,
		),
		array(
			'id'            => '3',
			'name'          => 'مریم محمدی',
			'relation'      => 'مادر',
			'age'           => 64,
			'gender'        => 'female',
			'gender_label'  => 'خانم',
			'phone'         => '09125556677',
			'comorbidities' => '',
			'conditions'    => array( 'hypertension' ),
			'allergy'       => 'no',
			'mobility'      => 'independent',
			'is_self'       => false,
		),
	),
);
