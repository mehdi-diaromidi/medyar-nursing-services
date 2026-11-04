<?php
/**
 * Mobile bottom navigation (patient or nurse).
 *
 * @var string               $panel    patient|nurse
 * @var string               $current  Active page key.
 * @var array<int,array{key?:string,slug?:string,label:string,icon:string,url:string,primary?:bool,external?:bool}> $items
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$panel   = isset( $panel ) ? (string) $panel : 'patient';
$current = isset( $current ) ? (string) $current : '';
$items   = ( isset( $items ) && is_array( $items ) ) ? $items : array();

if ( empty( $items ) ) {
	$items = MNS_UI::default_bottom_nav_items( $panel );
}
?>
<nav class="mns-bottom-nav" aria-label="<?php echo esc_attr( MNS_Copy::t( 'nav.label' ) ); ?>">
	<ul class="mns-bottom-nav__list">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$key      = (string) ( $item['key'] ?? $item['slug'] ?? '' );
			$is_on    = $current === $key;
			$is_cta   = ! empty( $item['primary'] ) || 'new-order' === $key;
			$external = ! empty( $item['external'] );
			$class    = 'mns-bottom-nav__item';
			if ( $is_on ) {
				$class .= ' is-active';
			}
			if ( $is_cta ) {
				$class .= ' mns-bottom-nav__item--cta';
			}
			?>
			<li class="<?php echo esc_attr( $class ); ?>">
				<a
					class="mns-bottom-nav__link"
					href="<?php echo esc_url( (string) ( $item['url'] ?? '#' ) ); ?>"
					<?php echo $is_on ? ' aria-current="page"' : ''; ?>
					<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
				>
					<span class="mns-bottom-nav__icon" aria-hidden="true"><?php echo mns_icon( (string) ( $item['icon'] ?? 'list' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="mns-bottom-nav__label"><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></span>
					<?php if ( $external ) : ?>
						<span class="screen-reader-text"><?php echo esc_html( MNS_Copy::t( 'label.external' ) ); ?></span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
