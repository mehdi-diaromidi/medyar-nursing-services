<?php
/**
 * Catalog categories fixture (brief 6 groups).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'items' => array(
		array(
			'title'       => 'سرم‌تراپی',
			'excerpt'     => 'تزریق سرم و مراقبت حین انفوزیون در منزل.',
			'price_label' => 'از ۸۵۰٬۰۰۰ تومان',
			'url'         => home_url( '/nursing-services/?mns_service=serum' ),
		),
		array(
			'title'       => 'تزریقات',
			'excerpt'     => 'تزریق عضلانی و وریدی طبق دستور پزشک.',
			'price_label' => 'از ۳۵۰٬۰۰۰ تومان',
			'url'         => home_url( '/nursing-services/?mns_service=injection' ),
		),
		array(
			'title'       => 'پانسمان',
			'excerpt'     => 'تعویض پانسمان زخم‌های ساده و تخصصی.',
			'price_label' => 'بازه ۴۰۰٬۰۰۰ تا ۹۰۰٬۰۰۰',
			'url'         => home_url( '/nursing-services/?mns_service=dressing' ),
		),
		array(
			'title'       => 'سوندگذاری و خدمات مرتبط',
			'excerpt'     => 'سوند ادراری و مراقبت‌های مرتبط.',
			'price_label' => 'از ۶۰۰٬۰۰۰ تومان',
			'url'         => home_url( '/nursing-services/?mns_service=catheter' ),
		),
		array(
			'title'       => 'مراقبت از بیمار',
			'excerpt'     => 'مراقبت حضوری کوتاه‌مدت در منزل.',
			'price_label' => 'ساعتی / توافقی طبق تعرفه',
			'url'         => home_url( '/nursing-services/?mns_service=care' ),
		),
		array(
			'title'       => 'سایر خدمات پرستاری',
			'excerpt'     => 'خدماتی که در دسته‌های بالا نیست؛ با پشتیبانی هماهنگ کنید.',
			'price_label' => 'پس از ارزیابی',
			'url'         => home_url( '/nursing-services/?mns_service=other' ),
		),
	),
);
