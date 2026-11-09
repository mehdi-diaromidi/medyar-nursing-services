<?php
/**
 * «My orders» row: one markup, rendered as a single-line grid row (wide list) or a card (narrow list).
 *
 * @var array<string,mixed> $row
 * @var string              $group action|active|history
 * @var string              $id_prefix Prefix for the menu id (keeps ids unique per list).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$row       = isset( $row ) && is_array( $row ) ? $row : array();
$group     = isset( $group ) ? (string) $group : 'history';
$id_prefix = isset( $id_prefix ) ? (string) $id_prefix : 'mns-om-';
$code      = (string) ( $row['code'] ?? '' );
$cells     = MNS_Patient_Orders::cells( $row );
?>
<li
	class="mns-order mns-order--<?php echo esc_attr( $group ); ?>"
	data-mns-order
	data-mns-order-status="<?php echo esc_attr( $cells['status'] ); ?>"
	data-mns-order-title="<?php echo esc_attr( $cells['sheet_title'] ); ?>"
	data-mns-cancel-text="<?php echo esc_attr( $cells['cancel_text'] ); ?>"
>
	<div class="mns-order__code">
		<span class="mns-order__code-text" dir="ltr"><?php echo esc_html( MNS_Patient_Orders::code_label( $code ) ); ?></span>
		<button type="button" class="mns-order__copy" data-mns-copy="<?php echo esc_attr( $code ); ?>" aria-label="<?php echo esc_attr__( 'کپی کد پیگیری', 'medyar-nursing-services' ); ?>">
			<?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<?php if ( ! empty( $row['ticket'] ) ) : ?>
			<span class="mns-order__ticket" title="<?php echo esc_attr__( 'پیگیری باز', 'medyar-nursing-services' ); ?>"><?php echo mns_icon( 'headphones' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php echo esc_html__( 'پیگیری باز', 'medyar-nursing-services' ); ?></span></span>
		<?php endif; ?>
	</div>

	<div class="mns-order__service">
		<a class="mns-order__title" href="<?php echo esc_url( $cells['view_url'] ); ?>" data-mns-order-link><?php echo esc_html( $cells['service'] ); ?></a>
	</div>

	<div class="mns-order__tags">
		<span class="mns-order__patient"><?php echo esc_html( $cells['patient'] ); ?></span>
	</div>

	<div class="mns-order__when">
		<span class="mns-order__when-icon" aria-hidden="true"><?php echo mns_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="mns-order__when-short"><?php echo esc_html( $cells['when_short'] ); ?></span>
		<span class="mns-order__when-long"><?php echo esc_html( $cells['when_long'] ); ?></span>
	</div>

	<?php MNS_UI::partial( 'order-cells', array( 'cells' => $cells, 'tag' => 'div' ) ); ?>

	<?php MNS_UI::partial( 'order-actions', array( 'row' => $row, 'menu_id' => $id_prefix . sanitize_html_class( $code ) ) ); ?>
</li>
