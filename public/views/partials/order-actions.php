<?php
/**
 * «My orders» row actions: primary button + «⋯» menu (popover on desktop, sheet on mobile).
 *
 * @var array<string,mixed> $row
 * @var string              $menu_id Unique id for the menu element.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$row     = isset( $row ) && is_array( $row ) ? $row : array();
$menu_id = isset( $menu_id ) ? (string) $menu_id : 'mns-om';
/* Callers may pass resolved actions ({primary, menu[], cancel_note}); default = order actions. */
if ( isset( $resolved ) && is_array( $resolved ) ) {
	$actions = array(
		'menu'        => $resolved['menu'],
		'cancel_note' => ! empty( $resolved['cancel_note'] ),
	);
	$primary = $resolved['primary'];
} else {
	$actions = MNS_Order_Status::actions( $row );
	$primary = MNS_Patient_Orders::action( $actions['primary'], $row );
}

$attrs_html = static function ( array $attrs ): string {
	$out = '';
	foreach ( $attrs as $k => $v ) {
		$out .= ' ' . esc_attr( (string) $k ) . '="' . esc_attr( (string) $v ) . '"';
	}
	return $out;
};
?>
<div class="mns-order__actions">
	<a class="mns-obtn mns-obtn--<?php echo esc_attr( $primary['variant'] ); ?> mns-order__primary" href="<?php echo esc_url( $primary['url'] ); ?>">
		<?php if ( '' !== $primary['icon'] ) : ?>
			<?php echo mns_icon( $primary['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
		<span><?php echo esc_html( $primary['label'] ); ?></span>
	</a>
	<button
		type="button"
		class="mns-obtn mns-obtn--icon mns-order__more"
		data-mns-order-more
		aria-haspopup="menu"
		aria-expanded="false"
		aria-controls="<?php echo esc_attr( $menu_id ); ?>"
		aria-label="<?php echo esc_attr__( 'اقدام‌های بیشتر', 'medyar-nursing-services' ); ?>"
	><?php echo mns_icon( 'more' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
	<ul class="mns-order-menu" id="<?php echo esc_attr( $menu_id ); ?>" role="menu" hidden data-mns-order-menu>
		<?php foreach ( $actions['menu'] as $key ) : ?>
			<?php $act = is_array( $key ) ? $key : MNS_Patient_Orders::action( $key, $row ); ?>
			<li role="none">
				<?php if ( '' === $act['url'] ) : ?>
					<button type="button" role="menuitem" class="mns-order-menu__item<?php echo 'danger' === $act['variant'] ? ' is-danger' : ''; ?>"<?php echo $attrs_html( $act['attrs'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<span class="mns-order-menu__icon" aria-hidden="true"><?php echo mns_icon( $act['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-order-menu__label"><?php echo esc_html( $act['label'] ); ?></span>
					</button>
				<?php else : ?>
					<a role="menuitem" class="mns-order-menu__item" href="<?php echo esc_url( $act['url'] ); ?>"<?php echo $attrs_html( $act['attrs'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<span class="mns-order-menu__icon" aria-hidden="true"><?php echo mns_icon( '' !== $act['icon'] ? $act['icon'] : 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-order-menu__label"><?php echo esc_html( $act['label'] ); ?></span>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
		<?php if ( $actions['cancel_note'] ) : ?>
			<li role="none" class="mns-order-menu__note"><?php echo esc_html__( 'برای لغو با پشتیبانی تماس بگیرید', 'medyar-nursing-services' ); ?></li>
		<?php endif; ?>
	</ul>
</div>
