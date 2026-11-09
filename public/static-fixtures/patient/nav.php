<?php
/**
 * Patient sidebar nav fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array( 'slug' => 'dashboard', 'label' => 'داشبورد', 'icon' => 'home' ),
	array( 'slug' => 'new-order', 'label' => 'ثبت سفارش', 'icon' => 'plus' ),
	array( 'slug' => 'orders', 'label' => 'سفارش‌های من', 'icon' => 'list' ),
	array( 'slug' => 'patients', 'label' => 'بیماران من', 'icon' => 'users' ),
	array( 'slug' => 'invoices', 'label' => 'فاکتورها و پرداخت‌ها', 'icon' => 'invoice' ),
	array( 'slug' => 'recurring', 'label' => 'خدمات دوره‌ای', 'icon' => 'calendar' ),
	array( 'slug' => 'loyalty', 'label' => 'کارت وفاداری و تخفیف', 'icon' => 'heart' ),
	array( 'slug' => 'surveys', 'label' => 'پرسشنامه‌ها', 'icon' => 'star' ),
);
