<?php
/**
 * Nurse order detail fixture.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'tracking'   => 'MNS-1403-1001',
	'service'    => 'سرم‌تراپی',
	'status'     => 'تخصیص‌یافته',
	'tone'       => 'progress',
	'patient'    => 'حسن محمدی · ۶۸ ساله',
	'area'       => 'ونک',
	'address'    => 'تهران، ونک، پلاک ۱۰',
	'scheduled'  => '۱۴۰۳/۰۸/۱۲ · ۱۰:۰۰',
	'notes'      => 'حساسیت پنی‌سیلین · سرم نرمال سالین',
	'checklist'  => array(
		array( 'id' => 'c1', 'label' => 'نوع سرم و دستور پزشک ثبت شد' ),
		array( 'id' => 'c2', 'label' => 'مدت حضور ثبت شد' ),
		array( 'id' => 'c3', 'label' => 'علائم حیاتی کنترل شد' ),
		array( 'id' => 'c4', 'label' => 'مشکلات احتمالی یادداشت شد' ),
	),
	'extras'     => array(
		array( 'id' => 'e1', 'label' => 'کنترل فشار خون', 'price' => '۸۰٬۰۰۰ تومان' ),
		array( 'id' => 'e2', 'label' => 'تزریق عضلانی اضافه', 'price' => '۱۲۰٬۰۰۰ تومان' ),
	),
	'consumables'=> array(
		array( 'id' => 'k1', 'label' => 'ست سرم', 'price' => '۴۵٬۰۰۰ تومان' ),
		array( 'id' => 'k2', 'label' => 'چسب و گاز', 'price' => '۲۵٬۰۰۰ تومان' ),
	),
	'medication' => array(
		'enabled' => true,
		'status'  => 'در انتظار استعلام مشاور',
	),
);
