<?php
/**
 * Public catalog archive (Phase 2 redesign).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$service = isset( $_GET['mns_service'] ) ? sanitize_key( wp_unslash( $_GET['mns_service'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( '' !== $service ) {
	require MNS_PLUGIN_DIR . 'public/views/catalog/service.php';
	return;
}

$catalog = MNS_Static_Fixtures::get( 'catalog/categories' );
$items   = isset( $catalog['items'] ) && is_array( $catalog['items'] ) ? $catalog['items'] : array();
$title   = (string) ( $catalog['title'] ?? __( 'خدمات پرستاری در منزل', 'medyar-nursing-services' ) );
$lead    = (string) ( $catalog['lead'] ?? '' );
$how     = isset( $catalog['how_steps'] ) && is_array( $catalog['how_steps'] ) ? $catalog['how_steps'] : array();
$reviews = isset( $catalog['reviews'] ) && is_array( $catalog['reviews'] ) ? $catalog['reviews'] : array();
$faqs    = isset( $catalog['faqs'] ) && is_array( $catalog['faqs'] ) ? $catalog['faqs'] : array();

if ( MNS_Dev::is( 'empty' ) ) {
	$items   = array();
	$reviews = array();
}
if ( MNS_Dev::is( 'long' ) && ! empty( $items ) ) {
	$items = array_merge( $items, $items );
}
?>
<div class="mns-catalog" dir="rtl" data-mns-panel="catalog" data-mns-page="archive">
	<?php if ( MNS_Dev::is( 'loading' ) ) : ?>
		<?php MNS_UI::skeleton( array( 'variant' => 'card', 'rows' => 6 ) ); ?>
	<?php elseif ( MNS_Dev::is( 'error' ) ) : ?>
		<?php MNS_UI::error_state( array( 'retry_url' => MNS_Pages::catalog_url() ) ); ?>
	<?php else : ?>

		<header class="mns-catalog__intro">
			<h1 class="mns-dash__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( '' !== $lead ) : ?>
				<p class="mns-dash__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<div class="mns-catalog__intro-actions">
				<?php MNS_UI::support_call( array( 'variant' => 'outline', 'label' => MNS_Copy::t( 'action.call_support' ) ) ); ?>
			</div>
		</header>

		<?php if ( empty( $items ) ) : ?>
			<?php
			MNS_UI::empty_state(
				array(
					'title'     => __( 'فعلاً خدمتی نمایش داده نمی‌شود', 'medyar-nursing-services' ),
					'text'      => __( 'برای هماهنگی خدمت با پشتیبانی مدیار تماس بگیرید.', 'medyar-nursing-services' ),
					'cta_label' => MNS_Copy::t( 'action.call_support' ),
					'cta_url'   => MNS_UI::support_tel_href(),
					'icon'      => 'list',
				)
			);
			?>
		<?php else : ?>
			<section class="mns-catalog__section" aria-label="<?php echo esc_attr__( 'دسته‌بندی خدمات', 'medyar-nursing-services' ); ?>">
				<div class="mns-catalog__grid<?php echo 1 === count( $items ) ? ' mns-catalog__grid--single' : ''; ?>">
					<?php foreach ( $items as $item ) : ?>
						<?php
						$slug  = (string) ( $item['slug'] ?? '' );
						$icon  = (string) ( $item['icon'] ?? 'list' );
						$label = (string) ( $item['price_label'] ?? '' );
						$from  = isset( $item['price_from'] ) ? (int) $item['price_from'] : 0;
						if ( '' === $label && $from > 0 ) {
							/* translators: %s: formatted toman amount */
							$label = sprintf( __( 'از %s', 'medyar-nursing-services' ), MNS_Format::toman( $from ) );
						}
						?>
						<a class="mns-catalog__card" href="<?php echo esc_url( (string) ( $item['url'] ?? '#' ) ); ?>">
							<span class="mns-catalog__card-icon" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mns-catalog__card-body">
								<span class="mns-catalog__card-title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></span>
								<span class="mns-catalog__card-excerpt"><?php echo esc_html( (string) ( $item['excerpt'] ?? '' ) ); ?></span>
								<?php if ( '' !== $label ) : ?>
									<span class="mns-catalog__card-price"><?php echo esc_html( $label ); ?></span>
								<?php endif; ?>
							</span>
							<span class="mns-catalog__card-chevron" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<section class="mns-catalog__help mns-card">
			<div class="mns-catalog__help-text">
				<h2 class="mns-card__title"><?php echo esc_html__( 'نمی‌دانید کدام خدمت لازم است؟', 'medyar-nursing-services' ); ?></h2>
				<p class="mns-catalog__muted"><?php echo esc_html__( 'شرایط بیمار را بگویید تا همکاران پشتیبانی مسیر مناسب را پیشنهاد دهند.', 'medyar-nursing-services' ); ?></p>
			</div>
			<?php MNS_UI::support_call( array( 'variant' => 'solid' ) ); ?>
		</section>

		<?php if ( ! empty( $how ) ) : ?>
			<section class="mns-catalog__section">
				<h2 class="mns-catalog__section-title"><?php echo esc_html__( 'سفارش چطور کار می‌کند', 'medyar-nursing-services' ); ?></h2>
				<ol class="mns-catalog__steps">
					<?php foreach ( $how as $i => $step ) : ?>
						<li class="mns-catalog__step">
							<span class="mns-catalog__step-num" aria-hidden="true"><?php echo esc_html( MNS_Format::digits( $i + 1 ) ); ?></span>
							<span class="mns-catalog__step-body">
								<strong><?php echo esc_html( (string) ( $step['title'] ?? '' ) ); ?></strong>
								<span><?php echo esc_html( (string) ( $step['text'] ?? '' ) ); ?></span>
							</span>
						</li>
					<?php endforeach; ?>
				</ol>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $reviews ) ) : ?>
			<section class="mns-catalog__section">
				<h2 class="mns-catalog__section-title"><?php echo esc_html__( 'نظر بیماران', 'medyar-nursing-services' ); ?></h2>
				<ul class="mns-catalog__reviews">
					<?php foreach ( $reviews as $rev ) : ?>
						<li class="mns-catalog__review">
							<span class="mns-catalog__review-stars" aria-hidden="true">
								<?php
								$stars = isset( $rev['stars'] ) ? max( 1, min( 5, (int) $rev['stars'] ) ) : 5;
								for ( $i = 0; $i < $stars; $i++ ) {
									echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
							</span>
							<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: star count */ __( '%s از ۵', 'medyar-nursing-services' ), MNS_Format::digits( $stars ) ) ); ?></span>
							<p class="mns-catalog__review-text"><?php echo esc_html( (string) ( $rev['text'] ?? '' ) ); ?></p>
							<p class="mns-catalog__review-meta"><?php echo esc_html__( 'بیمار مدیار · بدون نام', 'medyar-nursing-services' ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $faqs ) ) : ?>
			<section class="mns-catalog__section">
				<h2 class="mns-catalog__section-title"><?php echo esc_html__( 'سؤالات پرتکرار', 'medyar-nursing-services' ); ?></h2>
				<div class="mns-accordion">
					<?php foreach ( $faqs as $faq ) : ?>
						<details>
							<summary><?php echo esc_html( (string) ( $faq['q'] ?? '' ) ); ?></summary>
							<p><?php echo esc_html( (string) ( $faq['a'] ?? '' ) ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

	<?php endif; ?>
</div>
