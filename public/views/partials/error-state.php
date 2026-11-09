<?php
/**
 * Recoverable error state.
 *
 * @var string $title
 * @var string $text
 * @var string $retry_label
 * @var string $retry_url   Optional; when empty a JS retry button is rendered.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title       = isset( $title ) ? (string) $title : MNS_Copy::t( 'state.error_title' );
$text        = isset( $text ) ? (string) $text : MNS_Copy::t( 'state.error_body' );
$retry_label = isset( $retry_label ) ? (string) $retry_label : MNS_Copy::t( 'action.retry' );
$retry_url   = isset( $retry_url ) ? (string) $retry_url : '';
?>
<div class="mns-empty mns-empty--error" role="alert">
	<span class="mns-empty__well mns-empty__well--danger" aria-hidden="true"><?php echo mns_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<p class="mns-empty__title"><?php echo esc_html( $title ); ?></p>
	<p class="mns-empty__text"><?php echo esc_html( $text ); ?></p>
	<div class="mns-actions-row">
		<?php if ( '' !== $retry_url ) : ?>
			<a class="mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( $retry_url ); ?>"><?php echo esc_html( $retry_label ); ?></a>
		<?php else : ?>
			<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-retry><?php echo esc_html( $retry_label ); ?></button>
		<?php endif; ?>
		<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
	</div>
</div>
