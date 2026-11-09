<?php
/**
 * Nurse ratings averages (UX Phase D).
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

$sample = isset( $r['sample'] ) ? (int) $r['sample'] : 0;

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

$fmt = static function ( $v ): string {
	return MNS_Format::digits( number_format( (float) $v, 1 ) );
};
?>

<div class="mns-metrics">
	<div class="mns-metric mns-metric--hero">
		<span class="mns-metric__label"><?php echo esc_html__( 'میانگین کلی', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( $fmt( $r['overall'] ?? 0 ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'علمی', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( $fmt( $r['science'] ?? 0 ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'اخلاق', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( $fmt( $r['ethics'] ?? 0 ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'نظم', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( $fmt( $r['order'] ?? 0 ) ); ?></span>
	</div>
</div>

<?php if ( $sample > 0 ) : ?>
	<p class="mns-field-hint" style="text-align:center;margin-top:12px">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: sample count */
				__( 'بر اساس %s پرسشنامه', 'medyar-nursing-services' ),
				MNS_Format::digits( (string) $sample )
			)
		);
		?>
	</p>
<?php endif; ?>

<section class="mns-card">
	<?php
	MNS_UI::star_rating( array( 'value' => (float) ( $r['overall'] ?? 0 ), 'label' => __( 'میانگین کلی', 'medyar-nursing-services' ) ) );
	MNS_UI::star_rating( array( 'value' => (float) ( $r['science'] ?? 0 ), 'label' => __( 'علمی', 'medyar-nursing-services' ) ) );
	MNS_UI::star_rating( array( 'value' => (float) ( $r['ethics'] ?? 0 ), 'label' => __( 'اخلاق', 'medyar-nursing-services' ) ) );
	MNS_UI::star_rating( array( 'value' => (float) ( $r['order'] ?? 0 ), 'label' => __( 'نظم', 'medyar-nursing-services' ) ) );
	?>
</section>
