<?php
/**
 * Sticky bottom action bar for forms / confirm flows.
 *
 * @var string $primary_label
 * @var string $primary_attrs  Extra attrs for primary button (type/href already handled).
 * @var string $secondary_label
 * @var string $secondary_url
 * @var string $hint
 * @var bool   $as_link        Render primary as <a>.
 * @var string $primary_url
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$primary_label   = isset( $primary_label ) ? (string) $primary_label : MNS_Copy::t( 'action.continue' );
$primary_attrs   = isset( $primary_attrs ) ? (string) $primary_attrs : '';
$secondary_label = isset( $secondary_label ) ? (string) $secondary_label : '';
$secondary_url   = isset( $secondary_url ) ? (string) $secondary_url : '';
$hint            = isset( $hint ) ? (string) $hint : '';
$as_link         = ! empty( $as_link );
$primary_url     = isset( $primary_url ) ? (string) $primary_url : '#';
?>
<div class="mns-sticky-action">
	<?php if ( '' !== $hint ) : ?>
		<p class="mns-sticky-action__hint"><?php echo esc_html( $hint ); ?></p>
	<?php endif; ?>
	<div class="mns-sticky-action__row">
		<?php if ( '' !== $secondary_label ) : ?>
			<?php if ( '' !== $secondary_url ) : ?>
				<a class="mns-outline-btn" href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( $secondary_label ); ?></a>
			<?php else : ?>
				<button type="button" class="mns-outline-btn" data-mns-sticky-secondary><?php echo esc_html( $secondary_label ); ?></button>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( $as_link ) : ?>
			<a class="mns-submit-btn" href="<?php echo esc_url( $primary_url ); ?>" <?php echo $primary_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $primary_label ); ?></a>
		<?php else : ?>
			<button type="submit" class="mns-submit-btn" <?php echo $primary_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $primary_label ); ?></button>
		<?php endif; ?>
	</div>
</div>
