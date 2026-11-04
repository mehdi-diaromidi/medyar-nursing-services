<?php
/**
 * Opens a responsive table wrapper. Close with table-wrap-end.php or </div>.
 *
 * @var string $mns_table_label Optional aria-label
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label = isset( $mns_table_label ) ? (string) $mns_table_label : __( 'جدول', 'medyar-nursing-services' );
?>
<div class="mns-table-wrap" role="region" aria-label="<?php echo esc_attr( $label ); ?>" tabindex="0">
