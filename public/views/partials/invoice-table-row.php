<?php
/**
 * Paid-invoices table row (ARP `.arp-table`).
 *
 * @var array<string,mixed> $row
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$row    = isset( $row ) && is_array( $row ) ? $row : array();
$number = (string) $row['number'];
$types  = MNS_Patient_Invoices::types();
?>
<tr class="arp-table__row mns-otable__row" data-mns-order data-mns-order-title="<?php echo esc_attr( (string) $row['service'] . ' · ' . $number ); ?>">
	<td class="mns-otable__id">
		<span class="mns-otable__num" dir="ltr"><?php echo esc_html( $number ); ?></span>
		<span class="mns-otable__sub"><?php echo esc_html( MNS_Patient_Invoices::day_label( (string) $row['issued'] ) ); ?></span>
	</td>
	<td class="arp-table__name">
		<span class="mns-otable__line">
			<a class="mns-order__title" href="<?php echo esc_url( MNS_Patient_Invoices::view_url( $number ) ); ?>" data-mns-order-link><?php echo esc_html( (string) $row['service'] ); ?></a>
			<?php if ( 'normal' !== $row['type'] ) : ?>
				<span class="mns-inv__chip mns-inv__chip--<?php echo esc_attr( (string) $row['type'] ); ?>"><?php echo esc_html( $types[ $row['type'] ] ?? '' ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $row['revised'] ) ) : ?>
				<span class="mns-inv__chip mns-inv__chip--revised"><?php echo esc_html__( 'اصلاح‌شده', 'medyar-nursing-services' ); ?></span>
			<?php endif; ?>
		</span>
		<span class="mns-otable__sub"><?php echo esc_html( (string) ( $row['patient_name'] ?? '' ) ); ?></span>
	</td>
	<td class="arp-table__amount"><strong><?php echo esc_html( MNS_Format::number( (int) $row['amount'] ) ); ?></strong><span><?php echo esc_html__( 'تومان', 'medyar-nursing-services' ); ?></span></td>
	<td class="mns-otable__state">
		<span class="mns-ostatus mns-ostatus--inv-paid">
			<span class="mns-ostatus__dot" aria-hidden="true"></span>
			<span class="mns-ostatus__label"><?php echo esc_html( MNS_Patient_Invoices::status_label( 'paid' ) ); ?></span>
		</span>
		<span class="mns-otable__sub"><?php echo esc_html( MNS_Patient_Invoices::method_label( (string) ( $row['pay_method'] ?? '' ) ) ); ?></span>
	</td>
	<td class="mns-otable__actions">
		<?php
		MNS_UI::partial(
			'order-actions',
			array(
				'row'      => $row,
				'menu_id'  => 'mns-ivt-' . sanitize_html_class( $number ),
				'resolved' => MNS_Patient_Invoices::actions( $row ),
			)
		);
		?>
	</td>
</tr>
