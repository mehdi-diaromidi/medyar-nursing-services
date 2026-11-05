<?php
/**
 * Section title row with optional nearby "view all" link.
 *
 * @var string $title
 * @var string $title_id   Optional id for aria-labelledby.
 * @var string $link_label
 * @var string $link_url
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title      = isset( $title ) ? (string) $title : '';
$title_id   = isset( $title_id ) ? (string) $title_id : '';
$link_label = isset( $link_label ) ? (string) $link_label : '';
$link_url   = isset( $link_url ) ? (string) $link_url : '';
?>
<div class="mns-section-head">
	<h2
		class="mns-card__title mns-section-head__title"
		<?php echo '' !== $title_id ? ' id="' . esc_attr( $title_id ) . '"' : ''; ?>
	><?php echo esc_html( $title ); ?></h2>
	<?php if ( '' !== $link_url && '' !== $link_label ) : ?>
		<a class="mns-section-head__link" href="<?php echo esc_url( $link_url ); ?>">
			<span><?php echo esc_html( $link_label ); ?></span>
			<span class="mns-section-head__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</a>
	<?php endif; ?>
</div>
