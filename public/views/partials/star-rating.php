<?php
/**
 * Star rating display or input.
 *
 * @var float|int $value
 * @var int       $max
 * @var bool      $interactive
 * @var string    $name
 * @var string    $label
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$value       = isset( $value ) ? (float) $value : 0.0;
$max         = isset( $max ) ? max( 1, (int) $max ) : 5;
$interactive = ! empty( $interactive );
$name        = isset( $name ) ? (string) $name : 'rating';
$label       = isset( $label ) ? (string) $label : MNS_Copy::t( 'rating.label' );
$rounded     = (int) round( $value );
?>
<div class="mns-stars<?php echo $interactive ? ' mns-stars--input' : ''; ?>"<?php echo $interactive ? '' : ' aria-label="' . esc_attr( $label . ': ' . MNS_Format::digits( number_format( $value, 1 ) ) ) . '"'; ?>>
	<?php if ( $interactive ) : ?>
		<span class="mns-stars__label"><?php echo esc_html( $label ); ?></span>
		<div class="mns-stars__row" role="radiogroup" aria-label="<?php echo esc_attr( $label ); ?>">
			<?php for ( $i = $max; $i >= 1; $i-- ) : ?>
				<input
					class="mns-stars__input"
					type="radio"
					id="<?php echo esc_attr( $name . '-' . $i ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( (string) $i ); ?>"
					<?php checked( $rounded, $i ); ?>
				/>
				<label class="mns-stars__star" for="<?php echo esc_attr( $name . '-' . $i ); ?>" title="<?php echo esc_attr( MNS_Format::digits( $i ) ); ?>">
					<span class="screen-reader-text"><?php echo esc_html( MNS_Format::digits( $i ) ); ?></span>
					<?php echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</label>
			<?php endfor; ?>
		</div>
	<?php else : ?>
		<span class="mns-stars__row" aria-hidden="true">
			<?php for ( $i = 1; $i <= $max; $i++ ) : ?>
				<span class="mns-stars__star<?php echo $i <= $rounded ? ' is-on' : ''; ?>">
					<?php echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			<?php endfor; ?>
		</span>
		<span class="mns-stars__value"><?php echo esc_html( MNS_Format::digits( number_format( $value, 1 ) ) ); ?></span>
	<?php endif; ?>
</div>
