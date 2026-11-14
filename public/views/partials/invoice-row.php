<?php
/**
 * Invoice row: single-line grid row (wide list) or card (narrow list). Shares `.mns-order` styles.
 *
 * @var array<string,mixed> $row
 * @var string              $group action|history
 * @var string              $id_prefix
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$row       = isset( $row ) && is_array( $row ) ? $row : array();
$group     = isset( $group ) ? (string) $group : 'history';
$id_prefix = isset( $id_prefix ) ? (string) $id_prefix : 'mns-iv-';
$number    = (string) $row['number'];
$status    = (string) $row['status'];
$types     = MNS_Patient_Invoices::types();
?>
<li
	class="mns-order mns-inv mns-order--<?php echo esc_attr( $group ); ?><?php echo in_array( $status, array( 'paid', 'dispute' ), true ) ? '' : ' mns-inv--solo'; ?>"
	data-mns-order
	data-mns-order-title="<?php echo esc_attr( (string) $row['service'] . ' · ' . $number ); ?>"
>
	<div class="mns-order__code">
		<span class="mns-order__code-text" dir="ltr"><?php echo esc_html( $number ); ?></span>
	</div>

	<div class="mns-order__service">
		<a class="mns-order__title" href="<?php echo esc_url( MNS_Patient_Invoices::view_url( $number ) ); ?>" data-mns-order-link><?php echo esc_html( (string) $row['service'] ); ?></a>
		<?php if ( 'normal' !== $row['type'] || ! empty( $row['revised'] ) ) : ?>
			<span class="mns-inv__chips">
				<?php if ( 'normal' !== $row['type'] ) : ?>
					<span class="mns-inv__chip mns-inv__chip--<?php echo esc_attr( (string) $row['type'] ); ?>"><?php echo esc_html( $types[ $row['type'] ] ?? '' ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $row['revised'] ) ) : ?>
					<span class="mns-inv__chip mns-inv__chip--revised"><?php echo esc_html__( 'اصلاح‌شده', 'medyar-nursing-services' ); ?></span>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="mns-order__tags">
		<span class="mns-order__patient"><?php echo esc_html( (string) ( $row['patient_name'] ?? '' ) ); ?></span>
	</div>

	<div class="mns-order__when">
		<span class="mns-order__when-icon" aria-hidden="true"><?php echo mns_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="mns-order__when-short"><?php echo esc_html( MNS_Patient_Invoices::day_label( (string) $row['issued'] ) ); ?></span>
		<span class="mns-order__when-long">
			<?php
			/* translators: %s: issue date */
			echo esc_html( sprintf( __( 'صدور: %s', 'medyar-nursing-services' ), MNS_Patient_Invoices::day_label( (string) $row['issued'] ) ) );
			if ( 'paid' === $status && ! empty( $row['paid_at'] ) ) {
				/* translators: %s: payment date */
				echo esc_html( ' · ' . sprintf( __( 'پرداخت: %s', 'medyar-nursing-services' ), MNS_Patient_Invoices::day_label( (string) $row['paid_at'] ) ) );
			}
			?>
		</span>
	</div>

	<div class="mns-order__amount">
		<span class="mns-order__price"><?php echo esc_html( MNS_Format::number( (int) $row['amount'] ) ); ?></span>
		<span class="mns-order__currency"><?php echo esc_html__( 'تومان', 'medyar-nursing-services' ); ?></span>
	</div>

	<div class="mns-order__status">
		<span class="mns-ostatus mns-ostatus--inv-<?php echo esc_attr( $status ); ?>">
			<span class="mns-ostatus__dot" aria-hidden="true"></span>
			<span class="mns-ostatus__label"><?php echo esc_html( MNS_Patient_Invoices::status_label( $status ) ); ?></span>
		</span>
	</div>

	<div class="mns-inv__pay<?php echo 'paid' === $status ? '' : ' is-empty'; ?>">
		<?php if ( 'paid' === $status ) : ?>
			<span class="mns-inv__pay-at"><?php echo esc_html( MNS_Patient_Invoices::paid_label( (string) $row['paid_at'] ) ); ?></span>
			<span class="mns-inv__pay-method"><?php echo esc_html( MNS_Patient_Invoices::method_label( (string) ( $row['pay_method'] ?? '' ) ) ); ?></span>
		<?php elseif ( 'dispute' === $status ) : ?>
			<span class="mns-inv__lock"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html__( 'پرداخت قفل است', 'medyar-nursing-services' ); ?></span>
		<?php else : ?>
			<span class="mns-order__none">–</span>
		<?php endif; ?>
	</div>

	<?php
	MNS_UI::partial(
		'order-actions',
		array(
			'row'      => $row,
			'menu_id'  => $id_prefix . sanitize_html_class( $number ),
			'resolved' => MNS_Patient_Invoices::actions( $row ),
		)
	);
	?>
</li>
