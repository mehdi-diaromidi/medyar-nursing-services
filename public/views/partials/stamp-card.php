<?php
/**
 * Loyalty stamp card (N of M filled).
 *
 * @var int    $filled
 * @var int    $total
 * @var string $title
 * @var string $hint
 * @var string $reward
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filled = isset( $filled ) ? max( 0, (int) $filled ) : 0;
$total  = isset( $total ) ? max( 1, (int) $total ) : 10;
$filled = min( $filled, $total );
$title  = isset( $title ) ? (string) $title : MNS_Copy::t( 'label.loyalty' );
$hint   = isset( $hint ) ? (string) $hint : '';
$reward = isset( $reward ) ? (string) $reward : '';
?>
<section class="mns-stamp" aria-label="<?php echo esc_attr( $title ); ?>">
	<header class="mns-stamp__head">
		<h2 class="mns-stamp__title"><?php echo esc_html( $title ); ?></h2>
		<p class="mns-stamp__count"><?php echo esc_html( MNS_Format::digits( $filled ) . ' / ' . MNS_Format::digits( $total ) ); ?></p>
	</header>
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
	<?php if ( '' !== $hint ) : ?>
		<p class="mns-stamp__hint"><?php echo esc_html( $hint ); ?></p>
	<?php endif; ?>
	<?php if ( '' !== $reward ) : ?>
		<p class="mns-stamp__reward"><?php echo esc_html( $reward ); ?></p>
	<?php endif; ?>
</section>
