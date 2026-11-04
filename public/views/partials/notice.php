<?php
/**
 * Notice / alert partial.
 *
 * @var string $mns_notice_text
 * @var string $mns_notice_type  info|error|success (default info)
 * @var bool   $mns_notice_hidden
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text   = isset( $mns_notice_text ) ? (string) $mns_notice_text : '';
$type   = isset( $mns_notice_type ) ? (string) $mns_notice_type : 'info';
$hidden = ! empty( $mns_notice_hidden );
$class  = 'mns-notice';
if ( 'error' === $type ) {
	$class .= ' mns-notice--error';
} elseif ( 'success' === $type ) {
	$class .= ' mns-notice--success';
}
$role = ( 'error' === $type ) ? 'alert' : 'status';
?>
<div
	class="<?php echo esc_attr( $class ); ?>"
	role="<?php echo esc_attr( $role ); ?>"
	<?php echo $hidden ? 'hidden' : ''; ?>
	tabindex="-1"
	data-mns-notice
><?php echo esc_html( $text ); ?></div>
