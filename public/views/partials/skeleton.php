<?php
/**
 * Loading skeleton placeholders.
 *
 * @var string $variant  card|list|detail|form
 * @var int    $rows
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant = isset( $variant ) ? (string) $variant : 'list';
$rows    = isset( $rows ) ? max( 1, (int) $rows ) : 3;
?>
<div class="mns-skel mns-skel--<?php echo esc_attr( $variant ); ?>" aria-busy="true" aria-live="polite">
	<span class="screen-reader-text"><?php echo esc_html( MNS_Copy::t( 'state.loading' ) ); ?></span>
	<?php if ( 'card' === $variant ) : ?>
		<?php for ( $i = 0; $i < $rows; $i++ ) : ?>
			<div class="mns-skel-card">
				<span class="mns-skel-line mns-skel-line--lg"></span>
				<span class="mns-skel-line"></span>
				<span class="mns-skel-line mns-skel-line--sm"></span>
			</div>
		<?php endfor; ?>
	<?php elseif ( 'detail' === $variant ) : ?>
		<span class="mns-skel-line mns-skel-line--title"></span>
		<span class="mns-skel-line"></span>
		<span class="mns-skel-line mns-skel-line--sm"></span>
		<div class="mns-skel-card">
			<span class="mns-skel-line"></span>
			<span class="mns-skel-line"></span>
			<span class="mns-skel-line mns-skel-line--sm"></span>
		</div>
	<?php elseif ( 'form' === $variant ) : ?>
		<?php for ( $i = 0; $i < $rows; $i++ ) : ?>
			<span class="mns-skel-line mns-skel-line--label"></span>
			<span class="mns-skel-line mns-skel-line--field"></span>
		<?php endfor; ?>
	<?php else : ?>
		<?php for ( $i = 0; $i < $rows; $i++ ) : ?>
			<div class="mns-skel-row">
				<span class="mns-skel-avatar"></span>
				<span class="mns-skel-stack">
					<span class="mns-skel-line"></span>
					<span class="mns-skel-line mns-skel-line--sm"></span>
				</span>
			</div>
		<?php endfor; ?>
	<?php endif; ?>
</div>
