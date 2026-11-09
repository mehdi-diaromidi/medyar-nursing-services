<?php
/**
 * Nurse availability fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz      = wp_timezone();
$blocked = ( new DateTimeImmutable( 'now', $tz ) )->modify( '+7 days' )->format( 'Y-m-d' );

return array(
	'accepting' => true,
	'areas'     => 'ونک، جردن، سعادت‌آباد',
	'services'  => 'سرم‌تراپی، تزریقات، پانسمان',
	'blocked'   => array( $blocked => 1 ),
	'week'      => array(
		array( 'day' => 'شنبه', 'hours' => '۰۹:۰۰–۱۸:۰۰', 'on' => true ),
		array( 'day' => 'یکشنبه', 'hours' => '۰۹:۰۰–۱۸:۰۰', 'on' => true ),
		array( 'day' => 'دوشنبه', 'hours' => '۰۹:۰۰–۱۴:۰۰', 'on' => true ),
		array( 'day' => 'سه‌شنبه', 'hours' => '۰۹:۰۰–۱۸:۰۰', 'on' => true ),
		array( 'day' => 'چهارشنبه', 'hours' => '۰۹:۰۰–۱۸:۰۰', 'on' => true ),
		array( 'day' => 'پنجشنبه', 'hours' => '۰۹:۰۰–۱۳:۰۰', 'on' => true ),
		array( 'day' => 'جمعه', 'hours' => 'تعطیل', 'on' => false ),
	),
);
