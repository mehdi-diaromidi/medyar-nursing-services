<?php
/**
 * Empty state partial.
 *
 * @var string $mns_empty_title
 * @var string $mns_empty_text
 * @var string $mns_empty_cta_label
 * @var string $mns_empty_cta_url
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = isset( $mns_empty_title ) ? (string) $mns_empty_title : __( 'موردی یافت نشد', 'medyar-nursing-services' );
$text  = isset( $mns_empty_text ) ? (string) $mns_empty_text : '';
$cta_l = isset( $mns_empty_cta_label ) ? (string) $mns_empty_cta_label : '';
$cta_u = isset( $mns_empty_cta_url ) ? (string) $mns_empty_cta_url : '';
?>
<div class="mns-empty" role="status">
	<p class="mns-empty__title"><?php echo esc_html( $title ); ?></p>
	<?php if ( '' !== $text ) : ?>
		<p class="mns-empty__text"><?php echo esc_html( $text ); ?></p>
	<?php endif; ?>
	<?php if ( '' !== $cta_l && '' !== $cta_u ) : ?>
		<a class="mns-btn" href="<?php echo esc_url( $cta_u ); ?>"><?php echo esc_html( $cta_l ); ?></a>
	<?php endif; ?>
</div>
