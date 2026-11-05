<?php
/**
 * Nurse profile (Phase 5) — read-only.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$p         = MNS_Static_Fixtures::get( 'nurse/profile_full' );
$name      = (string) ( $p['name'] ?? __( 'پرستار', 'medyar-nursing-services' ) );
$initial   = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );
$avail_url = MNS_Pages::panel_url( 'nurse', 'availability' );

MNS_UI::page_header(
	array(
		'title' => __( 'پروفایل', 'medyar-nursing-services' ),
		'lead'  => __( 'اطلاعات تأییدشده — ویرایش مدارک فقط از مسیر پشتیبانی.', 'medyar-nursing-services' ),
	)
);
?>

<section class="mns-profile-hero">
	<div class="mns-profile-hero__avatar" aria-hidden="true"><?php echo esc_html( $initial ); ?></div>
	<div>
		<p class="mns-profile-hero__name"><?php echo esc_html( $name ); ?></p>
		<p class="mns-profile-hero__meta"><?php echo esc_html( (string) ( $p['role'] ?? '' ) ); ?></p>
	</div>
</section>

<section class="mns-card">
	<h2 class="mns-card__title"><?php echo esc_html__( 'معرفی', 'medyar-nursing-services' ); ?></h2>
	<p class="mns-catalog__muted"><?php echo esc_html( (string) ( $p['about'] ?? '' ) ); ?></p>
</section>

<section class="mns-card">
	<h2 class="mns-card__title"><?php echo esc_html__( 'خدمات مجاز', 'medyar-nursing-services' ); ?></h2>
	<ul class="mns-service-list">
		<?php foreach ( (array) ( $p['services'] ?? array() ) as $s ) : ?>
			<li><?php echo esc_html( (string) $s ); ?></li>
		<?php endforeach; ?>
	</ul>
</section>

<section class="mns-card">
	<div class="mns-card__head-row">
		<h2 class="mns-card__title"><?php echo esc_html__( 'برنامه هفتگی', 'medyar-nursing-services' ); ?></h2>
		<a class="mns-link-btn" href="<?php echo esc_url( $avail_url ); ?>"><?php echo esc_html__( 'ویرایش برنامه', 'medyar-nursing-services' ); ?></a>
	</div>
	<p class="mns-field-hint"><?php echo esc_html__( 'برای تغییر روزهای فعال یا لغو روز خاص به برنامه و دسترسی بروید.', 'medyar-nursing-services' ); ?></p>
</section>

<section class="mns-accordion" aria-label="<?php echo esc_attr__( 'سؤالات پرتکرار', 'medyar-nursing-services' ); ?>">
	<details open>
		<summary><?php echo esc_html__( 'چگونه سهم من پرداخت می‌شود؟', 'medyar-nursing-services' ); ?></summary>
		<p class="mns-field-hint"><?php echo esc_html__( 'پس از پرداخت بیمار، سهم شما یک‌بار به کیف پول تومان واریز می‌شود.', 'medyar-nursing-services' ); ?></p>
	</details>
	<details>
		<summary><?php echo esc_html__( 'مدارک من برای بیمار قابل مشاهده است؟', 'medyar-nursing-services' ); ?></summary>
		<p class="mns-field-hint"><?php echo esc_html__( 'خیر؛ مدارک فقط برای ادمین قابل مشاهده‌اند.', 'medyar-nursing-services' ); ?></p>
	</details>
	<details>
		<summary><?php echo esc_html__( 'چطور مدارک یا مناطق را به‌روز کنم؟', 'medyar-nursing-services' ); ?></summary>
		<p class="mns-field-hint"><?php echo esc_html__( 'از پشتیبانی مدیار درخواست دهید؛ ویرایش مستقیم در پنل پرستار نیست.', 'medyar-nursing-services' ); ?></p>
	</details>
</section>
