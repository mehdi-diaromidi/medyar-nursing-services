<?php
/**
 * Nurse ratings averages (Phase 5) — no patient comments.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$r = MNS_Static_Fixtures::get( 'nurse/ratings' );

if ( MNS_Dev::is( 'empty' ) ) {
	$r = array();
}

MNS_UI::page_header(
	array(
		'title' => __( 'امتیازهای من', 'medyar-nursing-services' ),
		'lead'  => __( 'فقط میانگین‌ها؛ متن نظرات بیماران نمایش داده نمی‌شود.', 'medyar-nursing-services' ),
	)
);

if ( empty( $r ) ) {
	MNS_UI::empty_state(
		array(
			'title' => __( 'هنوز امتیازی ثبت نشده', 'medyar-nursing-services' ),
			'text'  => __( 'پس از تکمیل سفارش‌ها و پاسخ بیمار، میانگین‌ها اینجا می‌آید.', 'medyar-nursing-services' ),
			'icon'  => 'star',
		)
	);
	return;
}
?>

<div class="mns-metrics">
	<div class="mns-metric mns-metric--hero">
		<span class="mns-metric__label"><?php echo esc_html__( 'میانگین کلی', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( (string) ( $r['overall'] ?? '—' ) ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'علمی', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( (string) ( $r['science'] ?? '—' ) ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'اخلاق', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( (string) ( $r['ethics'] ?? '—' ) ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'نظم', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( (string) ( $r['order'] ?? '—' ) ) ); ?></span>
	</div>
</div>
