<?php
/**
 * Nurse dashboard fixture (P-26): "what now, what is unfinished, how am I doing".
 *
 * Shape mirrors the single AJAX payload the backend will return.
 * TODO(backend): address/phone only come from the server response for the assigned nurse
 * (never cached HTML); start window, ownership and share (90% of the stored invoice version)
 * are computed server-side.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz    = wp_timezone();
$now   = new DateTimeImmutable( 'now', $tz );
$today = $now->setTime( 0, 0 );
$view  = MNS_Pages::panel_url( 'nurse', 'order-view' );
$jy    = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );

$order_url = static function ( string $suffix, string $state = '' ) use ( $view, $jy ): string {
	$args = array( 'mns_order' => 'MNS-' . $jy[0] . '-' . $suffix );
	if ( '' !== $state ) {
		$args['mns_state'] = $state;
	}
	return add_query_arg( $args, $view );
};

/**
 * One order in a 4-hour window (decision 21). $day is a strtotime modifier relative to today.
 *
 * @return array<string,mixed>
 */
$order = static function ( string $suffix, string $day, int $from, int $to, array $extra ) use ( $today, $order_url ): array {
	$date = 'today' === $day ? $today : $today->modify( $day );
	return array_merge(
		array(
			'date'  => $date->format( 'Y-m-d' ),
			'from'  => $from,
			'to'    => $to,
			'start' => $date->setTime( $from, 0 )->getTimestamp(),
			'url'   => $order_url( $suffix ),
		),
		$extra
	);
};

$in_progress = array_merge(
	$order(
		'1002',
		'today',
		8,
		12,
		array(
			'service' => 'تزریقات',
			'patient' => 'سارا محمدی',
			'area'    => 'تهران، جردن',
			'status'  => 'in_progress',
		)
	),
	array(
		'needs_med'  => true,
		'end_url'    => $order_url( '1002', 'service_ended' ),
	)
);

$next = array_merge(
	$order(
		'1001',
		'today',
		12,
		16,
		array(
			'service' => 'سرم‌تراپی',
			'patient' => 'حسن محمدی',
			'area'    => 'تهران، ونک',
			'status'  => 'assigned',
		)
	),
	array(
		'address'   => 'ونک، خیابان ملاصدرا، کوچهٔ شیراز جنوبی، پلاک ۱۰، واحد ۴',
		'phone'     => '09121112233',
		'map_url'   => 'https://www.google.com/maps/dir/?api=1&destination=35.7575,51.4098',
		'session'   => array( 3, 7 ),
		'start_url' => $order_url( '1001', 'in_progress' ),
	)
);

return array(
	'first_name' => 'علی',
	'accepting'  => true,
	'on_leave'   => false,
	'now_ts'     => $now->getTimestamp(),
	'done_today' => 1,

	/* "Now" block, by priority: in_progress → next → none. */
	'in_progress' => $in_progress,
	'next'        => $next,

	/* Service ended but steps are still open: no invoice and no share until they are done. */
	'unfinished' => array(
		array(
			'service'   => 'پانسمان',
			'patient'   => 'مریم محمدی',
			'ended_at'  => $today->modify( '-1 day' )->setTime( 17, 20 )->format( 'Y-m-d H:i' ),
			'steps'     => array(
				array( 'label' => 'چک‌لیست', 'done' => true ),
				array( 'label' => 'گزارش', 'done' => false ),
			),
			'next_step' => 'گزارش',
			'url'       => $order_url( '0980', 'service_ended' ),
		),
		array(
			'service'   => 'سرم‌تراپی',
			'patient'   => 'رضا کریمی',
			'ended_at'  => $today->modify( '-2 days' )->setTime( 11, 5 )->format( 'Y-m-d H:i' ),
			'steps'     => array(
				array( 'label' => 'چک‌لیست', 'done' => true ),
				array( 'label' => 'مرحله دارو', 'done' => false ),
				array( 'label' => 'گزارش', 'done' => false ),
			),
			'next_step' => 'مرحله دارو',
			'url'       => $order_url( '0971', 'service_ended' ),
		),
	),

	/* Change notices: "seen" button, auto-removed after 48h (server). */
	'news' => array(
		array(
			'id'   => 'n-2041',
			'icon' => 'clock',
			'text' => 'زمان سفارش پانسمان (مریم محمدی) به فردا ۱۶:۰۰ تا ۲۰:۰۰ تغییر کرد.',
			'ago'  => '۲ ساعت پیش',
			'url'  => $order_url( '1003' ),
		),
		array(
			'id'   => 'n-2038',
			'icon' => 'edit',
			'text' => 'بیمار اطلاعات سفارش سرم‌تراپی (حسن محمدی) را ویرایش کرد.',
			'ago'  => 'دیروز',
			'url'  => $order_url( '1001' ),
		),
	),

	/* Next 7 days, max 5 rows (the "now" order is not repeated here). */
	'agenda' => array(
		$order(
			'1005',
			'today',
			16,
			20,
			array(
				'service' => 'پانسمان و زخم',
				'patient' => 'زهرا احمدی',
				'area'    => 'تهران، سعادت‌آباد',
				'status'  => 'assigned',
				'is_new'  => true,
			)
		),
		$order(
			'1003',
			'+1 day',
			16,
			20,
			array(
				'service' => 'پانسمان',
				'patient' => 'مریم محمدی',
				'area'    => 'تهران، نیاوران',
				'status'  => 'assigned',
			)
		),
		$order(
			'1008',
			'+3 days',
			8,
			12,
			array(
				'service' => 'سرم‌تراپی',
				'patient' => 'حسن محمدی',
				'area'    => 'تهران، ونک',
				'status'  => 'assigned',
				'session' => array( 4, 7 ),
			)
		),
		$order(
			'1011',
			'+3 days',
			12,
			16,
			array(
				'service' => 'پایش علائم حیاتی',
				'patient' => 'اکبر صادقی',
				'area'    => 'تهران، جردن',
				'status'  => 'assigned',
			)
		),
	),

	'wallet' => array(
		'available'      => 3450000,
		'pending_orders' => 2,
		'pending_amount' => 2250000,
	),
	'rating' => array(
		'overall' => 4.7,
		'count'   => 126,
		'parts'   => array(
			array( 'label' => 'علمی', 'value' => 4.8 ),
			array( 'label' => 'اخلاق و خوشرویی', 'value' => 4.7 ),
			array( 'label' => 'نظم', 'value' => 4.6 ),
		),
	),
	'month'  => array(
		'services'    => 18,
		'cancel_rate' => 2,
	),

	/* Weekly plan is set by support only (decision 23): read-only here. Saturday first. */
	'week' => array(
		array( 'day' => 'شنبه', 'short' => 'ش', 'slots' => array( array( 8, 12 ), array( 12, 16 ) ) ),
		array( 'day' => 'یکشنبه', 'short' => 'ی', 'slots' => array( array( 8, 12 ), array( 12, 16 ), array( 16, 20 ) ) ),
		array( 'day' => 'دوشنبه', 'short' => 'د', 'slots' => array( array( 8, 12 ) ) ),
		array( 'day' => 'سه‌شنبه', 'short' => 'س', 'slots' => array( array( 8, 12 ), array( 12, 16 ), array( 16, 20 ) ) ),
		array( 'day' => 'چهارشنبه', 'short' => 'چ', 'slots' => array( array( 8, 12 ), array( 12, 16 ), array( 16, 20 ) ) ),
		array( 'day' => 'پنجشنبه', 'short' => 'پ', 'slots' => array( array( 8, 12 ) ) ),
		array( 'day' => 'جمعه', 'short' => 'ج', 'slots' => array() ),
	),

	/* Newly approved nurse: three steps before the first order can arrive. */
	'onboarding' => array(
		array(
			'title' => 'مناطق فعالیت را تنظیم کنید',
			'done'  => true,
			'note'  => 'ونک، جردن، سعادت‌آباد',
			'url'   => MNS_Pages::panel_url( 'nurse', 'availability' ),
		),
		array(
			'title' => 'برنامهٔ هفتگی را پشتیبانی تنظیم می‌کند',
			'done'  => false,
			'note'  => 'در انتظار پشتیبانی',
			'url'   => '',
		),
		array(
			'title'  => 'پذیرش درخواست را روشن کنید',
			'done'   => false,
			'note'   => 'بعد از تنظیم برنامه، با همین کلید بالای صفحه',
			'url'    => '',
		),
	),
);
