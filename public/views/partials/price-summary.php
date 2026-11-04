<?php
/**
 * Price summary block: base + modifiers + total.
 *
 * @var int|float|string $base
 * @var array<int,array{label:string,amount:int|float|string,tone?:string}> $lines
 * @var int|float|string $total
 * @var string           $note
 * @var bool             $urgent
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base   = isset( $base ) ? $base : 0;
$lines  = ( isset( $lines ) && is_array( $lines ) ) ? $lines : array();
$total  = isset( $total ) ? $total : $base;
$note   = isset( $note ) ? (string) $note : '';
$urgent = ! empty( $urgent );
?>
<div class="mns-price">
	<ul class="mns-price__list">
		<li class="mns-price__row">
			<span><?php echo esc_html( MNS_Copy::t( 'price.base' ) ); ?></span>
			<strong><?php echo esc_html( MNS_Format::toman( $base ) ); ?></strong>
		</li>
		<?php foreach ( $lines as $line ) : ?>
			<?php
			$tone  = (string) ( $line['tone'] ?? '' );
			$class = 'mns-price__row';
			if ( '' !== $tone ) {
				$class .= ' mns-price__row--' . sanitize_html_class( $tone );
			}
			?>
			<li class="<?php echo esc_attr( $class ); ?>">
				<span><?php echo esc_html( (string) ( $line['label'] ?? '' ) ); ?></span>
				<strong><?php echo esc_html( MNS_Format::toman( $line['amount'] ?? 0 ) ); ?></strong>
			</li>
		<?php endforeach; ?>
		<?php if ( $urgent ) : ?>
			<li class="mns-price__row mns-price__row--warn">
				<span><?php echo esc_html( MNS_Copy::t( 'price.urgent' ) ); ?></span>
				<strong><?php echo esc_html( MNS_Copy::t( 'price.urgent_hint' ) ); ?></strong>
			</li>
		<?php endif; ?>
		<li class="mns-price__row mns-price__row--total">
			<span><?php echo esc_html( MNS_Copy::t( 'price.total' ) ); ?></span>
			<strong><?php echo esc_html( MNS_Format::toman( $total ) ); ?></strong>
		</li>
	</ul>
	<?php if ( '' !== $note ) : ?>
		<p class="mns-price__note"><?php echo esc_html( $note ); ?></p>
	<?php endif; ?>
</div>
