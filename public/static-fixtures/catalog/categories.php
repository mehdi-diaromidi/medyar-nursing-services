<?php
/**
 * Catalog archive fixture — 6 service groups + supporting blocks.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base = home_url( '/nursing-services/' );

return array(
	'title' => 'خدمات پرستاری در منزل',
	'lead'  => 'خدمت مناسب را انتخاب کنید؛ پرستار مجرب مدیار در زمان توافق‌شده به منزل می‌آید.',
	'items' => array(
		array(
			'slug'       => 'serum',
			'icon'       => 'pill',
			'title'      => 'سرم‌تراپی',
			'excerpt'    => 'تزریق سرم و مراقبت حین انفوزیون در منزل.',
			'price_from' => 850000,
			'url'        => add_query_arg( 'mns_service', 'serum', $base ),
		),
		array(
			'slug'       => 'injection',
			'icon'       => 'activity',
			'title'      => 'تزریقات',
			'excerpt'    => 'تزریق عضلانی و وریدی طبق دستور پزشک.',
			'price_from' => 350000,
			'url'        => add_query_arg( 'mns_service', 'injection', $base ),
		),
		array(
			'slug'       => 'dressing',
			'icon'       => 'heart',
			'title'      => 'پانسمان',
			'excerpt'    => 'تعویض پانسمان زخم‌های ساده و تخصصی.',
			'price_from' => 400000,
			'url'        => add_query_arg( 'mns_service', 'dressing', $base ),
		),
		array(
			'slug'       => 'catheter',
			'icon'       => 'shield',
			'title'      => 'سوندگذاری و خدمات مرتبط',
			'excerpt'    => 'سوند ادراری و مراقبت‌های مرتبط در منزل.',
			'price_from' => 600000,
			'url'        => add_query_arg( 'mns_service', 'catheter', $base ),
		),
		array(
			'slug'       => 'care',
			'icon'       => 'users',
			'title'      => 'مراقبت از بیمار',
			'excerpt'    => 'مراقبت حضوری کوتاه‌مدت در منزل.',
			'price_from' => 500000,
			'url'        => add_query_arg( 'mns_service', 'care', $base ),
		),
		array(
			'slug'       => 'other',
			'icon'       => 'more',
			'title'      => 'سایر خدمات پرستاری',
			'excerpt'    => 'خدماتی که در دسته‌های بالا نیست؛ با پشتیبانی هماهنگ کنید.',
			'price_from' => 0,
			'price_label'=> 'پس از ارزیابی',
			'url'        => add_query_arg( 'mns_service', 'other', $base ),
		),
	),
	'how_steps' => array(
		array(
			'title' => 'انتخاب خدمت',
			'text'  => 'دسته و جزئیات لازم را مشخص کنید.',
		),
		array(
			'title' => 'ثبت درخواست',
			'text'  => 'زمان، مکان و ترجیح جنسیت پرستار را وارد کنید.',
		),
		array(
			'title' => 'تخصیص پرستار',
			'text'  => 'مدیار نزدیک‌ترین پرستار مناسب را هماهنگ می‌کند.',
		),
		array(
			'title' => 'انجام و پرداخت',
			'text'  => 'پس از خدمت، فاکتور صادر و پرداخت می‌شود.',
		),
	),
	'reviews' => array(
		array(
			'stars' => 5,
			'text'  => 'هماهنگی سریع بود و پرستار دقیق کار کرد.',
		),
		array(
			'stars' => 5,
			'text'  => 'از منزل خارج نشدیم؛ همه چیز مرتب و محترمانه پیش رفت.',
		),
		array(
			'stars' => 4,
			'text'  => 'قیمت از قبل شفاف توضیح داده شد و فاکتور مطابق همان بود.',
		),
	),
	'faqs' => array(
		array(
			'q' => 'آیا ایاب‌وذهاب جداگانه حساب می‌شود؟',
			'a' => 'خیر. هزینهٔ ایاب‌وذهاب داخل قیمت خدمت است و ردیف جدا ندارد.',
		),
		array(
			'q' => 'مبلغ نهایی کی مشخص می‌شود؟',
			'a' => 'بازهٔ تقریبی را اینجا می‌بینید؛ مبلغ نهایی بعد از انجام خدمت و طبق تعرفهٔ ثبت‌شده محاسبه می‌شود.',
		),
		array(
			'q' => 'اگر مطمئن نباشم کدام خدمت لازم است؟',
			'a' => 'با پشتیبانی مدیار تماس بگیرید تا بر اساس شرایط بیمار راهنمایی شوید.',
		),
		array(
			'q' => 'آیا نسخهٔ پزشک لازم است؟',
			'a' => 'برای برخی خدمات مثل سرم‌تراپی و تزریقات، دستور معتبر پزشک لازم است؛ در صفحهٔ هر خدمت جزئیات آمده است.',
		),
	),
);
