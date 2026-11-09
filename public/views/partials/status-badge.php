<?php
/**
 * Status badge: color + icon + short label. Never color alone.
 *
 * @var string               $slug
 * @var string               $size   sm|md
 * @var bool                 $show_icon
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$slug      = isset( $slug ) ? (string) $slug : MNS_Order_Status::SUBMITTED;
$size      = isset( $size ) ? (string) $size : 'md';
$show_icon = ! isset( $show_icon ) || (bool) $show_icon;
$info      = MNS_Order_Status::get( $slug );
$class     = 'mns-status mns-status--' . sanitize_html_class( (string) $info['tone'] );
if ( 'sm' === $size ) {
	$class .= ' mns-status--sm';
}
?>
<span class="<?php echo esc_attr( $class ); ?>">
	<?php if ( $show_icon ) : ?>
		<span class="mns-status__icon" aria-hidden="true"><?php echo mns_icon( (string) $info['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<?php endif; ?>
	<span class="mns-status__label"><?php echo esc_html( (string) $info['label'] ); ?></span>
</span>
