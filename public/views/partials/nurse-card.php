<?php
/**
 * Compact nurse identity card.
 *
 * @var array{name?:string,specialty?:string,rating?:float|int,reviews?:int,avatar?:string,meta?:string} $nurse
 * @var bool   $show_call   Prefer support CTA over direct nurse phone.
 * @var string $href
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nurse     = ( isset( $nurse ) && is_array( $nurse ) ) ? $nurse : array();
$name      = (string) ( $nurse['name'] ?? '' );
$specialty = (string) ( $nurse['specialty'] ?? '' );
$rating    = isset( $nurse['rating'] ) ? (float) $nurse['rating'] : 0.0;
$reviews   = isset( $nurse['reviews'] ) ? (int) $nurse['reviews'] : 0;
$avatar    = (string) ( $nurse['avatar'] ?? '' );
$meta      = (string) ( $nurse['meta'] ?? '' );
$show_call = ! empty( $show_call );
$href      = isset( $href ) ? (string) $href : '';
$tag       = '' !== $href ? 'a' : 'div';
$attrs     = '' !== $href ? ' href="' . esc_url( $href ) . '"' : '';
?>
<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="mns-nurse-card"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="mns-nurse-card__avatar" aria-hidden="true">
		<?php if ( '' !== $avatar ) : ?>
			<img src="<?php echo esc_url( $avatar ); ?>" alt="" width="48" height="48" loading="lazy" />
		<?php else : ?>
			<?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</span>
	<span class="mns-nurse-card__body">
		<span class="mns-nurse-card__name"><?php echo esc_html( $name ); ?></span>
		<?php if ( '' !== $specialty ) : ?>
			<span class="mns-nurse-card__spec"><?php echo esc_html( $specialty ); ?></span>
		<?php endif; ?>
		<?php if ( $rating > 0 ) : ?>
			<span class="mns-nurse-card__rating">
				<?php echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( MNS_Format::digits( number_format( $rating, 1 ) ) ); ?>
				<?php if ( $reviews > 0 ) : ?>
					<span class="mns-nurse-card__reviews">(<?php echo esc_html( MNS_Format::digits( $reviews ) ); ?>)</span>
				<?php endif; ?>
			</span>
		<?php endif; ?>
		<?php if ( '' !== $meta ) : ?>
			<span class="mns-nurse-card__meta"><?php echo esc_html( $meta ); ?></span>
		<?php endif; ?>
	</span>
	<?php if ( $show_call ) : ?>
		<span class="mns-nurse-card__action">
			<?php MNS_UI::support_call( array( 'variant' => 'text', 'label' => MNS_Copy::t( 'action.support' ) ) ); ?>
		</span>
	<?php endif; ?>
</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
