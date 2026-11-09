<?php
/**
 * Support call CTA. Prefer over nurse phone until client decision.
 *
 * @var string $variant  solid|outline|text|fab
 * @var string $label
 * @var string $phone
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant = isset( $variant ) ? (string) $variant : 'outline';
$label   = isset( $label ) ? (string) $label : MNS_Copy::t( 'action.call_support' );
$phone   = isset( $phone ) ? (string) $phone : MNS_UI::support_phone();
$tel     = preg_replace( '/\D+/', '', $phone );
$class   = 'mns-support';
if ( 'solid' === $variant ) {
	$class .= ' mns-submit-btn mns-submit-btn--auto';
} elseif ( 'text' === $variant ) {
	$class .= ' mns-link-btn';
} elseif ( 'fab' === $variant ) {
	$class .= ' mns-support--fab';
} else {
	$class .= ' mns-outline-btn';
}
?>
<a class="<?php echo esc_attr( $class ); ?>" href="tel:<?php echo esc_attr( $tel ); ?>">
	<span class="mns-support__icon" aria-hidden="true"><?php echo mns_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<span class="mns-support__label"><?php echo esc_html( $label ); ?></span>
</a>
