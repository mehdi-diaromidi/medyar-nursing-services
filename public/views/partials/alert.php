<?php
/**
 * Inline alert / toast-style notice.
 *
 * @var string $tone     info|success|warning|danger
 * @var string $title
 * @var string $text
 * @var bool   $dismiss
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tone    = isset( $tone ) ? (string) $tone : 'info';
$title   = isset( $title ) ? (string) $title : '';
$text    = isset( $text ) ? (string) $text : '';
$dismiss = ! empty( $dismiss );
$icons   = array(
	'info'    => 'info',
	'success' => 'check-circle',
	'warning' => 'alert',
	'danger'  => 'alert',
);
$icon    = $icons[ $tone ] ?? 'info';
?>
<div class="mns-alert mns-alert--<?php echo esc_attr( $tone ); ?>" role="status"<?php echo $dismiss ? ' data-mns-alert' : ''; ?>>
	<span class="mns-alert__icon" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<div class="mns-alert__body">
		<?php if ( '' !== $title ) : ?>
			<p class="mns-alert__title"><?php echo esc_html( $title ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $text ) : ?>
			<p class="mns-alert__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $dismiss ) : ?>
		<button type="button" class="mns-alert__close" data-mns-alert-close aria-label="<?php echo esc_attr( MNS_Copy::t( 'action.close' ) ); ?>">
			<?php echo mns_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	<?php endif; ?>
</div>
