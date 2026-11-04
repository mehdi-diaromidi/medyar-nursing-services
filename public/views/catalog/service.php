<?php
/**
 * Single service page (static).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s = MNS_Static_Fixtures::get( 'catalog/service_sample' );
$patient_url = home_url( '/nursing-patient-panel/?mns_panel=patient&mns_page=new-order' );
?>
<div class="mns-catalog" dir="rtl">
	<section class="mns-service-hero">
		<h1 class="mns-dash__title" style="margin:0"><?php echo esc_html( (string) ( $s['title'] ?? '' ) ); ?></h1>
		<p class="mns-dash__lead" style="margin:12px 0 0"><?php echo esc_html( (string) ( $s['excerpt'] ?? '' ) ); ?></p>
		<p style="margin:16px 0 0;font-weight:700;color:var(--mns-primary)"><?php echo esc_html( (string) ( $s['price_label'] ?? '' ) ); ?></p>
		<p style="margin:20px 0 0"><a class="mns-submit-btn" style="max-width:280px;text-decoration:none" href="<?php echo esc_url( $patient_url ); ?>"><?php echo esc_html__( 'ثبت درخواست این خدمت', 'medyar-nursing-services' ); ?></a></p>
	</section>

	<div class="mns-form-grid">
		<section class="mns-card">
			<h2 class="mns-card__title"><?php echo esc_html__( 'شرایط دریافت خدمت', 'medyar-nursing-services' ); ?></h2>
			<ul style="margin:0;padding-inline-start:18px;color:var(--mns-muted-strong);line-height:1.8;font-size:0.875rem">
				<?php foreach ( (array) ( $s['conditions'] ?? array() ) as $c ) : ?>
					<li><?php echo esc_html( (string) $c ); ?></li>
				<?php endforeach; ?>
			</ul>
			<h2 class="mns-card__title" style="margin-top:20px"><?php echo esc_html__( 'نحوه محاسبه هزینه', 'medyar-nursing-services' ); ?></h2>
			<p style="margin:0;color:var(--mns-muted-strong);font-size:0.875rem;line-height:1.8"><?php echo esc_html( (string) ( $s['pricing_how'] ?? '' ) ); ?></p>
		</section>
		<aside>
			<section class="mns-card" style="margin-bottom:16px">
				<h2 class="mns-card__title"><?php echo esc_html__( 'اطلاعات ضروری', 'medyar-nursing-services' ); ?></h2>
				<ul style="margin:0;padding-inline-start:18px;color:var(--mns-muted-strong);font-size:0.875rem;line-height:1.8">
					<?php foreach ( (array) ( $s['essentials'] ?? array() ) as $e ) : ?>
						<li><?php echo esc_html( (string) $e ); ?></li>
					<?php endforeach; ?>
				</ul>
			</section>
			<section class="mns-how">
				<h2 class="mns-how__title"><?php echo esc_html__( 'شرایط خاص؟', 'medyar-nursing-services' ); ?></h2>
				<p style="margin:0;font-size:0.875rem;line-height:1.7;color:var(--mns-muted-strong)"><?php echo esc_html__( 'اگر مطمئن نیستید کدام خدمت مناسب است، با پشتیبانی مدیار تماس بگیرید.', 'medyar-nursing-services' ); ?></p>
				<p style="margin:12px 0 0"><a class="mns-btn-outline" href="tel:02188607015">02188607015</a></p>
			</section>
		</aside>
	</div>

	<section class="mns-card" style="margin-top:20px">
		<h2 class="mns-card__title"><?php echo esc_html__( 'نظرات مثبت بیماران', 'medyar-nursing-services' ); ?></h2>
		<?php foreach ( (array) ( $s['reviews'] ?? array() ) as $rev ) : ?>
			<p style="margin:0 0 12px;font-size:0.875rem;color:var(--mns-muted-strong);line-height:1.7">«<?php echo esc_html( (string) $rev ); ?>»</p>
		<?php endforeach; ?>
	</section>
</div>
