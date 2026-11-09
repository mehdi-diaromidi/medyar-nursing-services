<?php
/**
 * Loyalty stamp card (N of M filled) — screenshot horizontal layout.
 *
 * @var int    $filled
 * @var int    $total
 * @var string $title
 * @var string $hint
 * @var string $reward
 * @var string $link_url
 * @var string $link_label
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filled     = isset( $filled ) ? max( 0, (int) $filled ) : 0;
$total      = isset( $total ) ? max( 1, (int) $total ) : 10;
$filled     = min( $filled, $total );
$title      = isset( $title ) ? (string) $title : MNS_Copy::t( 'label.loyalty' );
$hint       = isset( $hint ) ? (string) $hint : '';
$reward     = isset( $reward ) ? (string) $reward : '';
$link_url   = isset( $link_url ) ? (string) $link_url : '';
$link_label = isset( $link_label ) ? (string) $link_label : '';
?>
<section class="mns-stamp" aria-label="<?php echo esc_attr( $title ); ?>">
	<div class="mns-stamp__intro">
		<span class="mns-stamp__mark" aria-hidden="true"><?php echo mns_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<div class="mns-stamp__intro-text">
			<h2 class="mns-stamp__title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( '' !== $hint ) : ?>
				<p class="mns-stamp__hint"><?php echo esc_html( $hint ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="mns-stamp__progress">
		<p class="mns-stamp__count" dir="ltr"><?php echo esc_html( MNS_Format::digits( $filled ) . '/' . MNS_Format::digits( $total ) ); ?></p>
		<ol class="mns-stamp__grid">
			<?php for ( $i = 1; $i <= $total; $i++ ) : ?>
				<li class="mns-stamp__cell<?php echo $i <= $filled ? ' is-filled' : ''; ?>">
					<span class="screen-reader-text"><?php echo esc_html( MNS_Format::digits( $i ) ); ?></span>
					<?php if ( $i <= $filled ) : ?>
						<?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</li>
			<?php endfor; ?>
		</ol>
		<?php if ( '' !== $reward ) : ?>
			<p class="mns-stamp__reward"><?php echo esc_html( $reward ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( '' !== $link_url && '' !== $link_label ) : ?>
		<a class="mns-stamp__link" href="<?php echo esc_url( $link_url ); ?>">
			<span class="mns-stamp__link-chevron" aria-hidden="true"><?php echo mns_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span><?php echo esc_html( $link_label ); ?></span>
		</a>
	<?php endif; ?>
</section>
