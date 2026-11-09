<?php
/**
 * Table footer: range info + windowed pagination (same markup as ARP `partials/pager.php`).
 *
 * @var int      $total
 * @var int      $per
 * @var int      $page_num
 * @var int      $pages
 * @var int      $offset
 * @var callable $page_url fn( int $n ): string
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $total ) || $total < 1 || ! isset( $page_url ) || ! is_callable( $page_url ) ) {
	return;
}

$mns_pager_sets = array(
	'full'    => MNS_Patient_Orders::pager_items( $page_num, $pages, 2 ),
	'compact' => MNS_Patient_Orders::pager_items( $page_num, $pages, 1 ),
);
$arrow_prev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';
$arrow_next = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';
?>
<div class="arp-refs-pager">
	<p class="arp-refs-pager__info">
		<?php if ( $pages <= 1 ) : ?>
			<?php
			/* translators: %s: count */
			echo esc_html( sprintf( __( '%s مورد', 'medyar-nursing-services' ), MNS_Format::digits( $total ) ) );
			?>
		<?php else : ?>
			<?php
			/* translators: 1: from, 2: to, 3: total */
			echo esc_html( sprintf( __( 'نمایش %1$s تا %2$s از %3$s مورد', 'medyar-nursing-services' ), MNS_Format::digits( $offset + 1 ), MNS_Format::digits( min( $offset + $per, $total ) ), MNS_Format::digits( $total ) ) );
			?>
		<?php endif; ?>
	</p>
	<?php if ( $pages > 1 ) : ?>
		<nav class="arp-refs-pager__nav" aria-label="<?php echo esc_attr__( 'صفحه‌بندی', 'medyar-nursing-services' ); ?>">
			<?php if ( $page_num > 1 ) : ?>
				<a class="arp-refs-pager__btn arp-refs-pager__btn--arrow" href="<?php echo esc_url( $page_url( $page_num - 1 ) ); ?>" aria-label="<?php echo esc_attr__( 'صفحه قبل', 'medyar-nursing-services' ); ?>"><?php echo $arrow_prev; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<?php else : ?>
				<span class="arp-refs-pager__btn arp-refs-pager__btn--arrow is-disabled" aria-hidden="true"><?php echo $arrow_prev; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>

			<?php foreach ( $mns_pager_sets as $variant => $items ) : ?>
				<span class="arp-refs-pager__pages arp-refs-pager__pages--<?php echo esc_attr( $variant ); ?>">
					<?php foreach ( $items as $item ) : ?>
						<?php if ( '…' === $item ) : ?>
							<span class="arp-refs-pager__ellipsis" aria-hidden="true">…</span>
						<?php elseif ( (int) $item === $page_num ) : ?>
							<span class="arp-refs-pager__btn is-current" aria-current="page"><?php echo esc_html( MNS_Format::digits( $item ) ); ?></span>
						<?php else : ?>
							<a class="arp-refs-pager__btn" href="<?php echo esc_url( $page_url( (int) $item ) ); ?>"><?php echo esc_html( MNS_Format::digits( $item ) ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				</span>
			<?php endforeach; ?>

			<?php if ( $page_num < $pages ) : ?>
				<a class="arp-refs-pager__btn arp-refs-pager__btn--arrow" href="<?php echo esc_url( $page_url( $page_num + 1 ) ); ?>" aria-label="<?php echo esc_attr__( 'صفحه بعد', 'medyar-nursing-services' ); ?>"><?php echo $arrow_next; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			<?php else : ?>
				<span class="arp-refs-pager__btn arp-refs-pager__btn--arrow is-disabled" aria-hidden="true"><?php echo $arrow_next; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
</div>
