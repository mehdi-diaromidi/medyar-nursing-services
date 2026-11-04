<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$r = MNS_Static_Fixtures::get( 'nurse/ratings' );
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'امتیازهای من', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'فقط میانگین‌ها نمایش داده می‌شود؛ متن نظرات بیماران دیده نمی‌شود.', 'medyar-nursing-services' ); ?></p>
<div class="mns-metrics">
	<div class="mns-metric mns-metric--hero"><span class="mns-metric__label"><?php echo esc_html__( 'میانگین کلی', 'medyar-nursing-services' ); ?></span><span class="mns-metric__value"><?php echo esc_html( (string) ( $r['overall'] ?? '—' ) ); ?></span></div>
	<div class="mns-metric"><span class="mns-metric__label"><?php echo esc_html__( 'علمی', 'medyar-nursing-services' ); ?></span><span class="mns-metric__value"><?php echo esc_html( (string) ( $r['science'] ?? '—' ) ); ?></span></div>
	<div class="mns-metric"><span class="mns-metric__label"><?php echo esc_html__( 'اخلاق / نظم', 'medyar-nursing-services' ); ?></span><span class="mns-metric__value" style="font-size:1.1rem"><?php echo esc_html( (string) ( $r['ethics'] ?? '—' ) ); ?> / <?php echo esc_html( (string) ( $r['order'] ?? '—' ) ); ?></span></div>
</div>
