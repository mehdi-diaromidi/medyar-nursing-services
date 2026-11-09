<?php
/**
 * Modal dialog / bottom sheet shell.
 *
 * @var string $id
 * @var string $title
 * @var string $body_html   Escaped/safe HTML.
 * @var string $footer_html Escaped/safe HTML.
 * @var string $variant     dialog|sheet
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id          = isset( $id ) ? (string) $id : 'mns-dialog';
$title       = isset( $title ) ? (string) $title : '';
$body_html   = isset( $body_html ) ? (string) $body_html : '';
$footer_html = isset( $footer_html ) ? (string) $footer_html : '';
$variant     = isset( $variant ) ? (string) $variant : 'dialog';
$class       = 'mns-dialog';
if ( 'sheet' === $variant ) {
	$class .= ' mns-dialog--sheet';
}
?>
<div class="<?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $id ); ?>" hidden data-mns-dialog>
	<div class="mns-dialog__backdrop" data-mns-dialog-close tabindex="-1"></div>
	<div class="mns-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title" tabindex="-1">
		<header class="mns-dialog__head">
			<h2 class="mns-dialog__title" id="<?php echo esc_attr( $id ); ?>-title"><?php echo esc_html( $title ); ?></h2>
			<button type="button" class="mns-dialog__close" data-mns-dialog-close aria-label="<?php echo esc_attr( MNS_Copy::t( 'action.close' ) ); ?>">
				<?php echo mns_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</header>
		<div class="mns-dialog__body"><?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php if ( '' !== $footer_html ) : ?>
			<footer class="mns-dialog__foot"><?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></footer>
		<?php endif; ?>
	</div>
</div>
