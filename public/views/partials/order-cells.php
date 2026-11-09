<?php
/**
 * Nurse / status / amount cells for a «my orders» row (div in grid rows, td in the history table).
 *
 * @var array<string,mixed> $cells From MNS_Patient_Orders::cells().
 * @var string              $tag   div|td.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cells  = isset( $cells ) && is_array( $cells ) ? $cells : array();
$tag    = isset( $tag ) && 'td' === $tag ? 'td' : 'div';
$nurse  = $cells['nurse'] ?? null;
$amount = $cells['amount'] ?? null;
$status = (string) ( $cells['status'] ?? '' );
?>
<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted. ?> class="mns-order__nurse<?php echo $nurse ? '' : ' is-pending'; ?>">
	<?php if ( $nurse ) : ?>
		<span class="mns-order__avatar" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="mns-order__nurse-name"><?php echo esc_html( (string) ( $nurse['name'] ?? '' ) ); ?></span>
	<?php else : ?>
		<span class="mns-order__pending-short"><?php echo esc_html__( 'در انتظار تخصیص', 'medyar-nursing-services' ); ?></span>
		<span class="mns-order__pending-long"><?php echo esc_html__( 'در انتظار تخصیص پرستار', 'medyar-nursing-services' ); ?></span>
	<?php endif; ?>
</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="mns-order__status">
	<span class="mns-ostatus mns-ostatus--<?php echo esc_attr( $status ); ?>" data-mns-order-badge>
		<span class="mns-ostatus__dot" aria-hidden="true"></span>
		<span class="mns-ostatus__label"><?php echo esc_html( (string) ( $cells['status_label'] ?? '' ) ); ?></span>
	</span>
</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="mns-order__amount">
	<?php if ( null !== $amount ) : ?>
		<span class="mns-order__price"><?php echo esc_html( MNS_Format::number( $amount ) ); ?></span>
		<span class="mns-order__currency"><?php echo esc_html__( 'تومان', 'medyar-nursing-services' ); ?></span>
	<?php elseif ( MNS_Order_Status::CANCELLED === $status ) : ?>
		<span class="mns-order__none">–</span>
	<?php else : ?>
		<span class="mns-order__tbd"><span><?php echo esc_html__( 'پس از خدمت', 'medyar-nursing-services' ); ?></span> <span><?php echo esc_html__( 'محاسبه می‌شود', 'medyar-nursing-services' ); ?></span></span>
	<?php endif; ?>
</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
