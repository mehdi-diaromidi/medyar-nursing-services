<?php
/**
 * History table row (ARP `.arp-table` styling).
 *
 * @var array<string,mixed> $row
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$row   = isset( $row ) && is_array( $row ) ? $row : array();
$code  = (string) ( $row['code'] ?? '' );
$cells = MNS_Patient_Orders::cells( $row );
?>
<tr
	class="arp-table__row mns-otable__row"
	data-mns-order
	data-mns-order-status="<?php echo esc_attr( $cells['status'] ); ?>"
	data-mns-order-title="<?php echo esc_attr( $cells['sheet_title'] ); ?>"
	data-mns-cancel-text="<?php echo esc_attr( $cells['cancel_text'] ); ?>"
>
	<td class="mns-order__code">
		<span class="mns-order__code-text" dir="ltr"><?php echo esc_html( MNS_Patient_Orders::code_label( $code ) ); ?></span>
		<button type="button" class="mns-order__copy" data-mns-copy="<?php echo esc_attr( $code ); ?>" aria-label="<?php echo esc_attr__( 'کپی کد پیگیری', 'medyar-nursing-services' ); ?>">
			<?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</td>
	<td class="arp-table__name"><a class="mns-order__title" href="<?php echo esc_url( $cells['view_url'] ); ?>" data-mns-order-link><?php echo esc_html( $cells['service'] ); ?></a></td>
	<td><?php echo esc_html( $cells['patient'] ); ?></td>
	<td><?php echo esc_html( $cells['when_short'] ); ?></td>
	<?php MNS_UI::partial( 'order-cells', array( 'cells' => $cells, 'tag' => 'td' ) ); ?>
	<td class="mns-otable__actions">
		<?php MNS_UI::partial( 'order-actions', array( 'row' => $row, 'menu_id' => 'mns-omt-' . sanitize_html_class( $code ) ) ); ?>
	</td>
</tr>
