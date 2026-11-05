<?php
/**
 * Central microcopy. Every shared UI string lives here (translatable),
 * and the `js` group is handed to public.js via wp_localize_script.
 *
 * Page-specific copy is added under its own group when each page is redesigned.
 *
 * @package medyar-nursing-services
 * @since 0.1.6 nav.invoices / nav.more confirmed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Copy {

	/** @var array<string,array<string,string>>|null */
	private static $cache = null;

	/**
	 * @return array<string,array<string,string>>
	 */
	public static function all(): array {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		self::$cache = array(
			'action'  => array(
				'continue'      => __( 'ادامه', 'medyar-nursing-services' ),
				'back'          => __( 'بازگشت', 'medyar-nursing-services' ),
				'edit'          => __( 'ویرایش', 'medyar-nursing-services' ),
				'save'          => __( 'ذخیرهٔ تغییرات', 'medyar-nursing-services' ),
				'cancel'        => __( 'انصراف', 'medyar-nursing-services' ),
				'close'         => __( 'بستن', 'medyar-nursing-services' ),
				'retry'         => __( 'تلاش دوباره', 'medyar-nursing-services' ),
				'copy'          => __( 'کپی', 'medyar-nursing-services' ),
				'support'       => __( 'تماس با پشتیبانی', 'medyar-nursing-services' ),
				'call_support'  => __( 'تماس با پشتیبانی مدیار', 'medyar-nursing-services' ),
				'go_dashboard'  => __( 'بازگشت به داشبورد', 'medyar-nursing-services' ),
				'open_menu'     => __( 'باز کردن منو', 'medyar-nursing-services' ),
				'more'          => __( 'بیشتر', 'medyar-nursing-services' ),
				'show_details'  => __( 'مشاهده', 'medyar-nursing-services' ),
				'view_all'      => __( 'مشاهدهٔ همه', 'medyar-nursing-services' ),
			),
			'nav'     => array(
				'label'      => __( 'ناوبری سریع', 'medyar-nursing-services' ),
				'home'       => __( 'داشبورد', 'medyar-nursing-services' ),
				'dashboard'  => __( 'داشبورد', 'medyar-nursing-services' ),
				'orders'     => __( 'سفارش‌ها', 'medyar-nursing-services' ),
				'new'        => __( 'سفارش جدید', 'medyar-nursing-services' ),
				'invoices'   => __( 'فاکتورها', 'medyar-nursing-services' ),
				'more'       => __( 'بیشتر', 'medyar-nursing-services' ),
				'profile'    => __( 'پروفایل', 'medyar-nursing-services' ),
				'schedule'   => __( 'برنامه', 'medyar-nursing-services' ),
				'wallet'     => __( 'کیف پول', 'medyar-nursing-services' ),
			),
			'label'   => array(
				'required'      => __( 'الزامی', 'medyar-nursing-services' ),
				'optional'      => __( 'اختیاری', 'medyar-nursing-services' ),
				'external'      => __( '(در صفحهٔ دیگر باز می‌شود)', 'medyar-nursing-services' ),
				'verified'      => __( 'تأییدشدهٔ مدیار', 'medyar-nursing-services' ),
				'support_hint'  => __( 'پاسخگویی هفت روز هفته از ساعت ۸ تا ۲۱', 'medyar-nursing-services' ),
				'tracking_code' => __( 'کد پیگیری', 'medyar-nursing-services' ),
				'step_of'       => __( 'مرحلهٔ %1$s از %2$s — %3$s', 'medyar-nursing-services' ),
				'edited_by'     => __( 'اصلاح‌شده توسط پشتیبانی · نسخهٔ %s', 'medyar-nursing-services' ),
				'locked'        => __( 'فقط‌خواندنی', 'medyar-nursing-services' ),
				'loyalty'       => __( 'کارت وفاداری و تخفیف', 'medyar-nursing-services' ),
			),
			'state'   => array(
				'empty_title'     => __( 'فعلاً موردی نیست', 'medyar-nursing-services' ),
				'empty_body'      => __( 'وقتی موردی اضافه شود، اینجا نمایش داده می‌شود.', 'medyar-nursing-services' ),
				'error_title'     => __( 'این بخش بارگذاری نشد', 'medyar-nursing-services' ),
				'error_body'      => __( 'اینترنتتان را بررسی کنید و دوباره تلاش کنید. اگر مشکل ادامه داشت با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ),
				'loading'         => __( 'در حال بارگذاری…', 'medyar-nursing-services' ),
				'not_found_title' => __( 'این صفحه پیدا نشد', 'medyar-nursing-services' ),
				'not_found_body'  => __( 'ممکن است نشانی اشتباه باشد یا صفحه جابه‌جا شده باشد.', 'medyar-nursing-services' ),
			),
			'price'   => array(
				'base'         => __( 'هزینهٔ اصلی خدمت', 'medyar-nursing-services' ),
				'extras'       => __( 'خدمات اضافه', 'medyar-nursing-services' ),
				'supplies'     => __( 'اقلام مصرفی', 'medyar-nursing-services' ),
				'medicine'     => __( 'دارو / مشاوره', 'medyar-nursing-services' ),
				'discount'     => __( 'تخفیف', 'medyar-nursing-services' ),
				'total'        => __( 'مبلغ نهایی', 'medyar-nursing-services' ),
				'travel'       => __( 'هزینهٔ ایاب‌وذهاب در قیمت لحاظ شده است.', 'medyar-nursing-services' ),
				'urgent'       => __( 'اولویت فوری', 'medyar-nursing-services' ),
				'urgent_hint'  => __( 'طبق تعرفهٔ فوری', 'medyar-nursing-services' ),
			),
			'rating'  => array(
				'label' => __( 'امتیاز', 'medyar-nursing-services' ),
				'1'     => __( 'خیلی ضعیف', 'medyar-nursing-services' ),
				'2'     => __( 'ضعیف', 'medyar-nursing-services' ),
				'3'     => __( 'متوسط', 'medyar-nursing-services' ),
				'4'     => __( 'خوب', 'medyar-nursing-services' ),
				'5'     => __( 'عالی', 'medyar-nursing-services' ),
			),
			'upload'  => array(
				'choose'   => __( 'انتخاب فایل', 'medyar-nursing-services' ),
				'camera'   => __( 'گرفتن عکس', 'medyar-nursing-services' ),
				'replace'  => __( 'تعویض', 'medyar-nursing-services' ),
				'remove'   => __( 'حذف', 'medyar-nursing-services' ),
				'hint'     => __( 'JPG، PNG یا PDF تا %s مگابایت', 'medyar-nursing-services' ),
				'private'  => __( 'مدارک شما محرمانه است و فقط ادمین مدیار آن را می‌بیند.', 'medyar-nursing-services' ),
			),
			'dev'     => array(
				'title'  => __( 'حالت صفحه (فقط توسعه)', 'medyar-nursing-services' ),
				'ui_kit' => __( 'کیت کامپوننت‌ها', 'medyar-nursing-services' ),
			),
			'js'      => array(
				'select_placeholder' => __( 'انتخاب کنید', 'medyar-nursing-services' ),
				'saved'              => __( 'ذخیره شد', 'medyar-nursing-services' ),
				'saving'             => __( 'در حال ذخیره…', 'medyar-nursing-services' ),
				'copied'             => __( 'کپی شد', 'medyar-nursing-services' ),
				'copy_failed'        => __( 'کپی نشد؛ متن را دستی انتخاب کنید.', 'medyar-nursing-services' ),
				'done'               => __( 'با موفقیت ثبت شد.', 'medyar-nursing-services' ),
				'network_error'      => __( 'درخواست ثبت نشد. اینترنتتان را بررسی کنید و دوباره تلاش کنید.', 'medyar-nursing-services' ),
				'no_nurse'           => __( 'برای این زمان و منطقه پرستار آزاد نداریم. زمان دیگری انتخاب کنید یا با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ),
				'required'           => __( 'این بخش را کامل کنید.', 'medyar-nursing-services' ),
				'required_choice'    => __( 'یکی از گزینه‌ها را انتخاب کنید.', 'medyar-nursing-services' ),
				'invalid_mobile'     => __( 'شمارهٔ موبایل باید ۱۱ رقم باشد و با ۰۹ شروع شود.', 'medyar-nursing-services' ),
				'invalid_number'     => __( 'یک عدد معتبر وارد کنید.', 'medyar-nursing-services' ),
				'too_short'          => __( 'متن کوتاه است؛ کمی بیشتر توضیح دهید.', 'medyar-nursing-services' ),
				'form_has_errors'    => __( 'چند مورد نیاز به اصلاح دارد؛ موارد مشخص‌شده را بررسی کنید.', 'medyar-nursing-services' ),
				'confirm_title'      => __( 'مطمئن هستید؟', 'medyar-nursing-services' ),
				'confirm_ok'         => __( 'بله، ادامه بده', 'medyar-nursing-services' ),
				'confirm_cancel'     => __( 'انصراف', 'medyar-nursing-services' ),
				'close'              => __( 'بستن', 'medyar-nursing-services' ),
				'file_type'          => __( 'این نوع فایل پشتیبانی نمی‌شود. JPG، PNG یا PDF انتخاب کنید.', 'medyar-nursing-services' ),
				'file_size'          => __( 'حجم فایل بیشتر از حد مجاز است.', 'medyar-nursing-services' ),
				'uploading'          => __( 'در حال بارگذاری…', 'medyar-nursing-services' ),
				'uploaded'           => __( 'بارگذاری شد', 'medyar-nursing-services' ),
				'remove'             => __( 'حذف', 'medyar-nursing-services' ),
				'replace'            => __( 'تعویض', 'medyar-nursing-services' ),
				'today'              => __( 'امروز', 'medyar-nursing-services' ),
				'tomorrow'           => __( 'فردا', 'medyar-nursing-services' ),
				'other_date'         => __( 'تاریخ دیگر', 'medyar-nursing-services' ),
				'prev_month'         => __( 'ماه قبل', 'medyar-nursing-services' ),
				'next_month'         => __( 'ماه بعد', 'medyar-nursing-services' ),
				'pick_date'          => __( 'انتخاب تاریخ', 'medyar-nursing-services' ),
				'friday'             => __( 'جمعه (تعطیل)', 'medyar-nursing-services' ),
				'toman'              => __( 'تومان', 'medyar-nursing-services' ),
				'day_blocked'        => __( 'این روز برای شما بسته است.', 'medyar-nursing-services' ),
				'day_open'           => __( 'این روز طبق برنامهٔ هفتگی باز است.', 'medyar-nursing-services' ),
				'block_day'          => __( 'بستن این روز', 'medyar-nursing-services' ),
				'unblock_day'        => __( 'باز کردن این روز', 'medyar-nursing-services' ),
				'selected_day'       => __( 'روز انتخاب‌شده', 'medyar-nursing-services' ),
				'urgent_confirm'     => __( 'با ثبت مورد فوری، ادمین‌های مدیار همین حالا مطلع می‌شوند.', 'medyar-nursing-services' ),
				'urgent_ok'          => __( 'اعلام مورد فوری', 'medyar-nursing-services' ),
				'review_patient'     => __( 'بیمار', 'medyar-nursing-services' ),
				'review_phone'       => __( 'شمارهٔ تماس', 'medyar-nursing-services' ),
				'review_place'       => __( 'مکان', 'medyar-nursing-services' ),
				'review_address'     => __( 'آدرس', 'medyar-nursing-services' ),
				'review_time'        => __( 'زمان', 'medyar-nursing-services' ),
				'review_service'     => __( 'خدمت', 'medyar-nursing-services' ),
				'review_gender'      => __( 'جنسیت پرستار', 'medyar-nursing-services' ),
				'gender_female'      => __( 'خانم', 'medyar-nursing-services' ),
				'gender_male'        => __( 'آقا', 'medyar-nursing-services' ),
				'gender_any'         => __( 'فرقی ندارد', 'medyar-nursing-services' ),
				'self'               => __( 'خودم', 'medyar-nursing-services' ),
				'new_patient'        => __( 'بیمار جدید', 'medyar-nursing-services' ),
				'urgent'             => __( 'فوری', 'medyar-nursing-services' ),
				'steps_in_order'     => __( 'مراحل را به ترتیب کامل کنید.', 'medyar-nursing-services' ),
				'need_patient'       => __( 'بیمار را انتخاب کنید.', 'medyar-nursing-services' ),
				'need_new_patient'   => __( 'نام و سن بیمار جدید را وارد کنید.', 'medyar-nursing-services' ),
				'need_place'         => __( 'شهر و منطقه را انتخاب کنید.', 'medyar-nursing-services' ),
				'need_address'       => __( 'آدرس دقیق را وارد کنید.', 'medyar-nursing-services' ),
				'need_time'          => __( 'تاریخ و ساعت مراجعه را انتخاب کنید یا سفارش فوری را روشن کنید.', 'medyar-nursing-services' ),
				'need_service'       => __( 'دسته و خدمت اصلی را انتخاب کنید.', 'medyar-nursing-services' ),
				'need_gender'        => __( 'جنسیت پرستار را انتخاب کنید.', 'medyar-nursing-services' ),
				'report_final'       => __( 'گزارش نهایی ثبت شد و دیگر قابل ویرایش نیست.', 'medyar-nursing-services' ),
			),
		);

		return self::$cache;
	}

	/**
	 * @param string $key Dot key, e.g. `action.call_support`.
	 */
	public static function t( string $key ): string {
		$parts = explode( '.', $key, 2 );
		if ( 2 !== count( $parts ) ) {
			return $key;
		}
		$all = self::all();
		return isset( $all[ $parts[0] ][ $parts[1] ] ) ? (string) $all[ $parts[0] ][ $parts[1] ] : $key;
	}

	/**
	 * Strings exposed to public.js (plus rating labels for StarRating).
	 *
	 * @return array<string,mixed>
	 */
	public static function for_js(): array {
		$all = self::all();
		return array_merge(
			$all['js'],
			array( 'rating' => $all['rating'] )
		);
	}
}
