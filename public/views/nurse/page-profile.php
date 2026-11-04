<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$p = MNS_Static_Fixtures::get( 'nurse/profile_full' );
?>
<div class="mns-profile-hero">
	<div class="mns-profile-hero__avatar" aria-hidden="true"><?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( (string) ( $p['name'] ?? 'پ' ), 0, 1 ) : 'پ' ); ?></div>
	<div>
		<p class="mns-profile-hero__name"><?php echo esc_html( (string) ( $p['name'] ?? '' ) ); ?></p>
		<p class="mns-profile-hero__meta"><?php echo esc_html( (string) ( $p['role'] ?? '' ) ); ?></p>
	</div>
</div>
<section class="mns-card" style="margin-bottom:16px">
	<h2 class="mns-card__title"><?php echo esc_html__( 'معرفی', 'medyar-nursing-services' ); ?></h2>
	<p style="margin:0;line-height:1.8;color:var(--mns-muted-strong);font-size:0.875rem"><?php echo esc_html( (string) ( $p['about'] ?? '' ) ); ?></p>
</section>
<section class="mns-card" style="margin-bottom:16px">
	<h2 class="mns-card__title"><?php echo esc_html__( 'خدمات مجاز', 'medyar-nursing-services' ); ?></h2>
	<ul style="margin:0;padding-inline-start:18px;color:var(--mns-muted-strong);font-size:0.875rem;line-height:1.8">
		<?php foreach ( (array) ( $p['services'] ?? array() ) as $s ) : ?>
			<li><?php echo esc_html( (string) $s ); ?></li>
		<?php endforeach; ?>
	</ul>
</section>
<section class="mns-accordion" aria-label="<?php echo esc_attr__( 'سؤالات پرتکرار', 'medyar-nursing-services' ); ?>">
	<details open><summary><?php echo esc_html__( 'چگونه سهم من پرداخت می‌شود؟', 'medyar-nursing-services' ); ?></summary><p style="margin:8px 0 0;font-size:0.875rem;color:var(--mns-muted-strong)"><?php echo esc_html__( 'پس از پرداخت بیمار، سهم شما یک‌بار به کیف پول تومان واریز می‌شود.', 'medyar-nursing-services' ); ?></p></details>
	<details><summary><?php echo esc_html__( 'مدارک من قابل مشاهده برای بیمار است؟', 'medyar-nursing-services' ); ?></summary><p style="margin:8px 0 0;font-size:0.875rem;color:var(--mns-muted-strong)"><?php echo esc_html__( 'خیر؛ مدارک فقط برای ادمین قابل مشاهده‌اند.', 'medyar-nursing-services' ); ?></p></details>
</section>
