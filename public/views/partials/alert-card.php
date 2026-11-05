<?php
/**
 * Action alert card: icon + title + context + amount + primary CTA (grouped).
 *
 * @var string $tone       unpaid|info|success|warning|survey|new_user|active
 * @var string $title
 * @var string $text
 * @var string $context    e.g. "سرم‌تراپی · خودم · امروز ۱۰:۰۰"
 * @var string $amount_html Escaped amount line (optional).
 * @var string $cta_label
 * @var string $cta_url
 * @var string $icon
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tone        = isset( $tone ) ? sanitize_key( (string) $tone ) : 'info';
$title       = isset( $title ) ? (string) $title : '';
$text        = isset( $text ) ? (string) $text : '';
$context     = isset( $context ) ? (string) $context : '';
$amount_html = isset( $amount_html ) ? (string) $amount_html : '';
$cta_label   = isset( $cta_label ) ? (string) $cta_label : '';
$cta_url     = isset( $cta_url ) ? (string) $cta_url : '';
$icon        = isset( $icon ) ? (string) $icon : 'alert';

$tone_map = array(
	'unpaid'   => 'warning',
	'survey'   => 'info',
	'new_user' => 'success',
	'active'   => 'info',
	'none'     => 'info',
);
$mod = $tone_map[ $tone ] ?? $tone;
?>
<section class="mns-alert-card mns-alert-card--<?php echo esc_attr( $tone ); ?>" data-mns-alert-card>
	<span class="mns-alert-card__icon mns-alert-card__icon--<?php echo esc_attr( $mod ); ?>" aria-hidden="true">
		<?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</span>
	<div class="mns-alert-card__body">
		<?php if ( '' !== $title ) : ?>
			<h2 class="mns-alert-card__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $context ) : ?>
			<p class="mns-alert-card__context"><?php echo esc_html( $context ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $text ) : ?>
			<p class="mns-alert-card__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
		<div class="mns-alert-card__meta">
			<?php if ( '' !== $amount_html ) : ?>
				<p class="mns-alert-card__amount"><?php echo $amount_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>
			<?php if ( '' !== $cta_url && '' !== $cta_label ) : ?>
				<a class="mns-submit-btn mns-submit-btn--auto mns-alert-card__cta" href="<?php echo esc_url( $cta_url ); ?>">
					<?php echo esc_html( $cta_label ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
