<?php
/**
 * Nurse sidebar nav fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array( 'slug' => 'dashboard', 'label' => 'داشبورد', 'icon' => 'home' ),
	array( 'slug' => 'orders', 'label' => 'سفارش‌های من', 'icon' => 'list' ),
	array( 'slug' => 'availability', 'label' => 'برنامه و دسترسی', 'icon' => 'clock' ),
	array( 'slug' => 'ratings', 'label' => 'امتیازهای من', 'icon' => 'star' ),
	array( 'slug' => 'profile', 'label' => 'پروفایل', 'icon' => 'user' ),
);
