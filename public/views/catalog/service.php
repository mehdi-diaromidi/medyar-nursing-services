<?php
/**
 * Single service page — one data-driven template for all 6 services.
 *
 * @var string $service Sanitized slug from archive router.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$slug = isset( $service ) ? sanitize_key( (string) $service ) : '';
if ( '' === $slug && isset( $_GET['mns_service'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$slug = sanitize_key( wp_unslash( $_GET['mns_service'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$s = MNS_Static_Fixtures::catalog_service( $slug );
if ( empty( $s ) ) {
	?>
	<div class="mns-catalog" dir="rtl" data-mns-panel="catalog" data-mns-page="service">
		<?php
		MNS_UI::error_state(
			array(
				'title'       => MNS_Copy::t( 'state.not_found_title' ),
				'text'        => __( 'این خدمت پیدا نشد یا دیگر فعال نیست.', 'medyar-nursing-services' ),
				'retry_label' => __( 'بازگشت به فهرست خدمات', 'medyar-nursing-services' ),
				'retry_url'   => MNS_Pages::catalog_url(),
			)
		);
		?>
	</div>
	<?php
	return;
}

if ( MNS_Dev::is( 'loading' ) ) {
	?>
	<div class="mns-catalog" dir="rtl" data-mns-panel="catalog" data-mns-page="service">
		<?php MNS_UI::skeleton( array( 'variant' => 'detail', 'rows' => 5 ) ); ?>
	</div>
	<?php
	return;
}
if ( MNS_Dev::is( 'error' ) ) {
	?>
	<div class="mns-catalog" dir="rtl" data-mns-panel="catalog" data-mns-page="service">
		<?php MNS_UI::error_state( array( 'retry_url' => MNS_Pages::catalog_service_url( $slug ) ) ); ?>
	</div>
	<?php
	return;
}

$order_url = add_query_arg(
	'mns_service',
	$slug,
	MNS_Pages::panel_url( 'patient', 'new-order' )
);
/* Prefer patient panel permalink when current request is catalog. */
$patient_page = get_page_by_path( 'nursing-patient-panel' );
if ( $patient_page instanceof WP_Post ) {
	$plink = get_permalink( $patient_page );
	if ( is_string( $plink ) && '' !== $plink ) {
		$order_url = add_query_arg(
			array(
				'mns_panel'   => 'patient',
				'mns_page'    => 'new-order',
				'mns_service' => $slug,
			),
			$plink
		);
	}
}

$price_label = (string) ( $s['price_label'] ?? '' );
$from        = isset( $s['price_from'] ) ? (int) $s['price_from'] : 0;
$to          = isset( $s['price_to'] ) ? (int) $s['price_to'] : 0;
if ( '' === $price_label ) {
	if ( $from > 0 && $to > $from ) {
		/* translators: 1: from amount, 2: to amount */
		$price_label = sprintf( __( 'بازه %1$s تا %2$s', 'medyar-nursing-services' ), MNS_Format::toman( $from ), MNS_Format::toman( $to ) );
	} elseif ( $from > 0 ) {
		/* translators: %s: formatted toman */
		$price_label = sprintf( __( 'از %s', 'medyar-nursing-services' ), MNS_Format::toman( $from ) );
	}
}

$reviews = isset( $s['reviews'] ) && is_array( $s['reviews'] ) ? $s['reviews'] : array();
if ( MNS_Dev::is( 'empty' ) ) {
	$reviews = array();
}

$related_slugs = isset( $s['related'] ) && is_array( $s['related'] ) ? $s['related'] : array();
$all_services  = MNS_Static_Fixtures::catalog_services();
$cta_label     = __( 'ثبت سفارش این خدمت', 'medyar-nursing-services' );
?>
<div class="mns-catalog mns-catalog--service" dir="rtl" data-mns-panel="catalog" data-mns-page="service" data-mns-service="<?php echo esc_attr( $slug ); ?>">
	<p class="mns-breadcrumb">
		<a href="<?php echo esc_url( MNS_Pages::catalog_url() ); ?>">
			<span class="mns-breadcrumb__icon" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php echo esc_html__( 'همهٔ خدمات', 'medyar-nursing-services' ); ?>
		</a>
	</p>

	<header class="mns-service-hero">
		<span class="mns-service-hero__icon" aria-hidden="true"><?php echo mns_icon( (string) ( $s['icon'] ?? 'list' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<h1 class="mns-dash__title"><?php echo esc_html( (string) ( $s['title'] ?? '' ) ); ?></h1>
		<p class="mns-dash__lead"><?php echo esc_html( (string) ( $s['excerpt'] ?? '' ) ); ?></p>
		<?php if ( '' !== $price_label ) : ?>
			<p class="mns-service-hero__price"><?php echo esc_html( $price_label ); ?></p>
		<?php endif; ?>
		<p class="mns-service-hero__cta mns-service-hero__cta--desktop">
			<a class="mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( $order_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
		</p>
	</header>

	<div class="mns-service-layout">
		<div class="mns-service-layout__main mns-stack">
			<?php if ( ! empty( $s['for_whom'] ) ) : ?>
				<section class="mns-card">
					<h2 class="mns-card__title"><?php echo esc_html__( 'این خدمت برای چه کسانی است', 'medyar-nursing-services' ); ?></h2>
					<ul class="mns-service-list">
						<?php foreach ( (array) $s['for_whom'] as $line ) : ?>
							<li><?php echo esc_html( (string) $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $s['includes'] ) ) : ?>
				<section class="mns-card">
					<h2 class="mns-card__title"><?php echo esc_html__( 'چه چیزی شامل می‌شود', 'medyar-nursing-services' ); ?></h2>
					<ul class="mns-service-list mns-service-list--check">
						<?php foreach ( (array) $s['includes'] as $line ) : ?>
							<li><span aria-hidden="true"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( (string) $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $s['conditions'] ) ) : ?>
				<section class="mns-card">
					<h2 class="mns-card__title"><?php echo esc_html__( 'شرایط دریافت خدمت', 'medyar-nursing-services' ); ?></h2>
					<ul class="mns-service-list">
						<?php foreach ( (array) $s['conditions'] as $line ) : ?>
							<li><?php echo esc_html( (string) $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $s['prepare'] ) ) : ?>
				<section class="mns-card">
					<h2 class="mns-card__title"><?php echo esc_html__( 'قبل از سفارش آماده کنید', 'medyar-nursing-services' ); ?></h2>
					<ul class="mns-service-checklist">
						<?php foreach ( (array) $s['prepare'] as $line ) : ?>
							<li><?php echo esc_html( (string) $line ); ?></li>
						<?php endforeach; ?>
					</ul>
					<?php if ( ! empty( $s['needs_prescription'] ) ) : ?>
						<?php
						MNS_UI::alert(
							array(
								'tone'  => 'warning',
								'title' => __( 'نیاز به نسخه / دستور پزشک', 'medyar-nursing-services' ),
								'text'  => (string) ( $s['prescription_note'] ?? '' ),
							)
						);
						?>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<section class="mns-card">
				<h2 class="mns-card__title"><?php echo esc_html__( 'هزینه چطور محاسبه می‌شود', 'medyar-nursing-services' ); ?></h2>
				<ul class="mns-service-factors">
					<?php foreach ( (array) ( $s['pricing_factors'] ?? array() ) as $factor ) : ?>
						<li>
							<strong><?php echo esc_html( (string) ( $factor['label'] ?? '' ) ); ?></strong>
							<span><?php echo esc_html( (string) ( $factor['text'] ?? '' ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php if ( ! empty( $s['pricing_note'] ) ) : ?>
					<p class="mns-catalog__muted"><?php echo esc_html( (string) $s['pricing_note'] ); ?></p>
				<?php endif; ?>
			</section>

			<?php if ( ! empty( $s['faqs'] ) ) : ?>
				<section class="mns-card">
					<h2 class="mns-card__title"><?php echo esc_html__( 'سؤالات پرتکرار', 'medyar-nursing-services' ); ?></h2>
					<div class="mns-accordion">
						<?php foreach ( (array) $s['faqs'] as $faq ) : ?>
							<details>
								<summary><?php echo esc_html( (string) ( $faq['q'] ?? '' ) ); ?></summary>
								<p><?php echo esc_html( (string) ( $faq['a'] ?? '' ) ); ?></p>
							</details>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $reviews ) ) : ?>
				<section class="mns-card">
					<h2 class="mns-card__title"><?php echo esc_html__( 'نظرات مثبت بیماران', 'medyar-nursing-services' ); ?></h2>
					<ul class="mns-catalog__reviews">
						<?php foreach ( $reviews as $rev ) : ?>
							<li class="mns-catalog__review">
								<p class="mns-catalog__review-text">«<?php echo esc_html( (string) ( $rev['text'] ?? '' ) ); ?>»</p>
								<p class="mns-catalog__review-meta"><?php echo esc_html__( 'بدون نام', 'medyar-nursing-services' ); ?></p>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</div>

		<aside class="mns-service-layout__side mns-stack">
			<section class="mns-card mns-service-side-cta">
				<?php if ( '' !== $price_label ) : ?>
					<p class="mns-service-side-cta__price"><?php echo esc_html( $price_label ); ?></p>
				<?php else : ?>
					<p class="mns-catalog__muted"><?php echo esc_html__( 'مبلغ نهایی پس از ارزیابی در سفارش مشخص می‌شود.', 'medyar-nursing-services' ); ?></p>
				<?php endif; ?>
				<a class="mns-submit-btn" href="<?php echo esc_url( $order_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
				<p class="mns-catalog__muted"><?php echo esc_html__( 'برای ثبت سفارش وارد حساب مدیار می‌شوید.', 'medyar-nursing-services' ); ?></p>
			</section>

			<section class="mns-card">
				<h2 class="mns-card__title"><?php echo esc_html__( 'نیاز به راهنمایی؟', 'medyar-nursing-services' ); ?></h2>
				<p class="mns-catalog__muted"><?php echo esc_html( MNS_Copy::t( 'label.support_hint' ) ); ?></p>
				<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
			</section>

			<?php if ( ! empty( $related_slugs ) ) : ?>
				<section class="mns-card">
					<h2 class="mns-card__title"><?php echo esc_html__( 'خدمات مرتبط', 'medyar-nursing-services' ); ?></h2>
					<ul class="mns-service-related">
						<?php foreach ( $related_slugs as $rel ) : ?>
							<?php
							$rel = sanitize_key( (string) $rel );
							if ( ! isset( $all_services[ $rel ] ) ) {
								continue;
							}
							$row = $all_services[ $rel ];
							$url = MNS_Pages::catalog_service_url( $rel );
							?>
							<li>
								<a href="<?php echo esc_url( $url ); ?>">
									<span aria-hidden="true"><?php echo mns_icon( (string) ( $row['icon'] ?? 'list' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<?php echo esc_html( (string) ( $row['title'] ?? $rel ) ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</aside>
	</div>

	<div class="mns-service-sticky" data-mns-service-sticky>
		<div class="mns-service-sticky__inner">
			<?php if ( '' !== $price_label ) : ?>
				<span class="mns-service-sticky__price"><?php echo esc_html( $price_label ); ?></span>
			<?php else : ?>
				<span class="mns-service-sticky__price mns-service-sticky__price--muted"><?php echo esc_html__( 'پس از ارزیابی', 'medyar-nursing-services' ); ?></span>
			<?php endif; ?>
			<a class="mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( $order_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
		</div>
	</div>
</div>
