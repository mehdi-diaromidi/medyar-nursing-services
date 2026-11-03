<?php
/**
 * Public catalog archive (static).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$catalog = MNS_Static_Fixtures::get( 'catalog/categories' );
$items   = isset( $catalog['items'] ) && is_array( $catalog['items'] ) ? $catalog['items'] : array();
?>
<div class="mns-catalog" dir="rtl">
	<h1 class="mns-dash__title"><?php echo esc_html__( 'خدمات پرستاری در منزل', 'medyar-nursing-services' ); ?></h1>
	<p class="mns-dash__lead"><?php echo esc_html__( 'دسته‌بندی خدمات پرتقاضا. برای شرایط خاص با پشتیبانی مدیار تماس بگیرید.', 'medyar-nursing-services' ); ?></p>

	<?php if ( empty( $items ) ) : ?>
		<div class="mns-notice mns-notice--error" role="alert"><?php echo esc_html__( 'هنوز خدمتی تعریف نشده است.', 'medyar-nursing-services' ); ?></div>
	<?php else : ?>
		<div class="mns-catalog__grid">
			<?php foreach ( $items as $item ) : ?>
				<a class="mns-catalog__card" href="<?php echo esc_url( (string) ( $item['url'] ?? '#' ) ); ?>">
					<h2 class="mns-card__title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></h2>
					<p style="margin:0;color:var(--mns-muted-strong);font-size:0.875rem;line-height:1.7;">
						<?php echo esc_html( (string) ( $item['excerpt'] ?? '' ) ); ?>
					</p>
					<p style="margin:12px 0 0;color:var(--mns-primary);font-weight:600;font-size:0.875rem;">
						<?php echo esc_html( (string) ( $item['price_label'] ?? '' ) ); ?>
					</p>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
