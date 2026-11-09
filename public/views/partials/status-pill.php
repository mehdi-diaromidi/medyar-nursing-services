<?php
/**
 * Status pill partial (maps tone → .mns-status--*).
 *
 * @var string $mns_pill_label
 * @var string $mns_pill_tone  waiting|progress|ok|warn|danger|muted
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label = isset( $mns_pill_label ) ? (string) $mns_pill_label : '';
$tone  = isset( $mns_pill_tone ) ? sanitize_key( (string) $mns_pill_tone ) : 'waiting';
if ( '' === $tone ) {
	$tone = 'waiting';
}
?>
<span class="mns-status mns-status--<?php echo esc_attr( $tone ); ?>"><?php echo esc_html( $label ); ?></span>
