<?php
/**
 * Empty state with optional illustration, title, body, CTA.
 *
 * @var string $title
 * @var string $text
 * @var string $cta_label
 * @var string $cta_url
 * @var string $icon   Icon key for the well.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title     = isset( $title ) ? (string) $title : MNS_Copy::t( 'state.empty_title' );
$text      = isset( $text ) ? (string) $text : MNS_Copy::t( 'state.empty_body' );
$cta_label = isset( $cta_label ) ? (string) $cta_label : '';
$cta_url   = isset( $cta_url ) ? (string) $cta_url : '';
$icon      = isset( $icon ) ? (string) $icon : 'list';
?>
<div class="mns-empty" role="status">
	<span class="mns-empty__well" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<p class="mns-empty__title"><?php echo esc_html( $title ); ?></p>
	<?php if ( '' !== $text ) : ?>
		<p class="mns-empty__text"><?php echo esc_html( $text ); ?></p>
	<?php endif; ?>
	<?php if ( '' !== $cta_label && '' !== $cta_url ) : ?>
		<a class="mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
	<?php endif; ?>
</div>
