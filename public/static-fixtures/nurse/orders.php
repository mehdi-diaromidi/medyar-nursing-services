<?php
/**
 * Nurse orders fixture (P-27): upcoming, work left to finish, and finished orders with the nurse's share.
 *
 * TODO(backend): the share is 90% of the stored invoice version and appears only once an invoice exists;
 * the rating exists only when the patient filled the survey of that session.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$today = ( new DateTimeImmutable( 'today', wp_timezone() ) );
$jy    = MNS_Format::g2j( (int) $today->format( 'Y' ), (int) $today->format( 'n' ), (int) $today->format( 'j' ) );
$view  = MNS_Pages::panel_url( 'nurse', 'order-view' );

/**
 * @param string $suffix  Tracking suffix.
 * @param string $day     Date modifier relative to today.
 * @param string $time    HH:MM.
 * @param array  $extra   Order fields.
 * @return array<string,mixed>
 */
$row = static function ( string $suffix, string $day, string $time, array $extra ) use ( $today, $jy, $view ): array {
	$date = 'today' === $day ? $today : $today->modify( $day );
	$code = 'MNS-' . $jy[0] . '-' . $suffix;
	return array_merge(
		array(
			'tracking'    => $code,
			'when'        => $date->setTime( (int) substr( $time, 0, 2 ), (int) substr( $time, 3, 2 ) )->format( 'Y-m-d H:i' ),
			'share'       => 0,
			'share_state' => 'none',
			'steps'       => null,
			'rating'      => null,
			'session'     => null,
			'phone'       => '09121112233',
			'map_url'     => 'https://www.google.com/maps/dir/?api=1&destination=35.7575,51.4098',
			'url'         => add_query_arg( 'mns_order', $code, $view ),
		),
		$extra
	);
};

$done_steps = array(
	'checklist' => true,
	'report'    => true,
);

return array(
	'summary' => array(
		'rating' => 4.7,
	),
	'items'   => array(
		/* upcoming */
		$row( '1002', 'today', '08:00', array( 'service' => 'تزریقات', 'patient' => 'سارا محمدی', 'area' => 'جردن', 'status' => 'in_progress', 'tab' => 'upcoming' ) ),
		$row( '1001', 'today', '12:00', array( 'service' => 'سرم‌تراپی', 'patient' => 'حسن محمدی', 'area' => 'ونک', 'status' => 'assigned', 'tab' => 'upcoming', 'session' => array( 3, 7 ) ) ),
		$row( '1005', 'today', '16:00', array( 'service' => 'پانسمان و زخم', 'patient' => 'زهرا احمدی', 'area' => 'سعادت‌آباد', 'status' => 'assigned', 'tab' => 'upcoming' ) ),
		$row( '1003', '+1 day', '16:00', array( 'service' => 'پانسمان', 'patient' => 'مریم محمدی', 'area' => 'نیاوران', 'status' => 'assigned', 'tab' => 'upcoming' ) ),
		/* work left to finish */
		$row(
			'0980',
			'-1 day',
			'16:00',
			array(
				'service' => 'پانسمان',
				'patient' => 'مریم محمدی',
				'area'    => 'نیاوران',
				'status'  => 'service_ended',
				'tab'     => 'pending',
				'steps'   => array( 'checklist' => true, 'report' => false ),
				'next'    => 'گزارش',
			)
		),
		/* finished */
		$row( '0975', '-2 days', '14:30', array( 'service' => 'تزریقات', 'patient' => 'سارا محمدی', 'area' => 'جردن', 'status' => 'paid', 'tab' => 'done', 'share' => 1125000, 'share_state' => 'paid', 'steps' => $done_steps, 'rating' => 5 ) ),
		$row( '0971', '-3 days', '10:00', array( 'service' => 'سرم‌تراپی', 'patient' => 'حسن محمدی', 'area' => 'ونک', 'status' => 'invoiced', 'tab' => 'done', 'share' => 1125000, 'share_state' => 'pending', 'steps' => $done_steps, 'session' => array( 2, 7 ) ) ),
		$row( '0968', '-4 days', '16:00', array( 'service' => 'پانسمان', 'patient' => 'مریم محمدی', 'area' => 'نیاوران', 'status' => 'needs_review', 'tab' => 'done', 'steps' => array( 'checklist' => true, 'report' => false ) ) ),
		$row( '0962', '-5 days', '08:00', array( 'service' => 'تزریقات', 'patient' => 'رضا کریمی', 'area' => 'ونک', 'status' => 'cancelled', 'tab' => 'done' ) ),
		$row( '0955', '-6 days', '12:00', array( 'service' => 'سرم‌تراپی', 'patient' => 'حسن محمدی', 'area' => 'ونک', 'status' => 'completed', 'tab' => 'done', 'share' => 1125000, 'share_state' => 'paid', 'steps' => $done_steps, 'rating' => 4.5, 'session' => array( 1, 7 ) ) ),
		$row( '0951', '-8 days', '16:00', array( 'service' => 'پایش علائم حیاتی', 'patient' => 'اکبر صادقی', 'area' => 'جردن', 'status' => 'completed', 'tab' => 'done', 'share' => 675000, 'share_state' => 'paid', 'steps' => $done_steps ) ),
		$row( '0947', '-9 days', '10:00', array( 'service' => 'تزریقات', 'patient' => 'زهرا احمدی', 'area' => 'سعادت‌آباد', 'status' => 'completed', 'tab' => 'done', 'share' => 900000, 'share_state' => 'paid', 'steps' => $done_steps, 'rating' => 5 ) ),
		$row( '0940', '-12 days', '14:00', array( 'service' => 'پانسمان و زخم', 'patient' => 'سارا محمدی', 'area' => 'جردن', 'status' => 'completed', 'tab' => 'done', 'share' => 1350000, 'share_state' => 'paid', 'steps' => $done_steps ) ),
		$row( '0936', '-14 days', '08:00', array( 'service' => 'سرم‌تراپی', 'patient' => 'رضا کریمی', 'area' => 'ونک', 'status' => 'completed', 'tab' => 'done', 'share' => 1125000, 'share_state' => 'paid', 'steps' => $done_steps, 'rating' => 4 ) ),
		$row( '0930', '-17 days', '16:00', array( 'service' => 'تزریقات', 'patient' => 'مریم محمدی', 'area' => 'نیاوران', 'status' => 'completed', 'tab' => 'done', 'share' => 900000, 'share_state' => 'paid', 'steps' => $done_steps ) ),
		$row( '0925', '-20 days', '12:00', array( 'service' => 'پایش علائم حیاتی', 'patient' => 'اکبر صادقی', 'area' => 'جردن', 'status' => 'completed', 'tab' => 'done', 'share' => 675000, 'share_state' => 'paid', 'steps' => $done_steps, 'rating' => 5 ) ),
		$row( '0918', '-24 days', '10:00', array( 'service' => 'سرم‌تراپی', 'patient' => 'حسن محمدی', 'area' => 'ونک', 'status' => 'completed', 'tab' => 'done', 'share' => 1125000, 'share_state' => 'paid', 'steps' => $done_steps ) ),
		$row( '0910', '-31 days', '14:30', array( 'service' => 'تزریقات', 'patient' => 'سارا محمدی', 'area' => 'جردن', 'status' => 'completed', 'tab' => 'done', 'share' => 900000, 'share_state' => 'paid', 'steps' => $done_steps ) ),
		$row( '0904', '-38 days', '16:00', array( 'service' => 'پانسمان', 'patient' => 'مریم محمدی', 'area' => 'نیاوران', 'status' => 'completed', 'tab' => 'done', 'share' => 1125000, 'share_state' => 'paid', 'steps' => $done_steps, 'rating' => 4.5 ) ),
	),
);
