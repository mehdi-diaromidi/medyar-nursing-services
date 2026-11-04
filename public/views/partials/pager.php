<?php
/**
 * Mock pager partial.
 *
 * @var string $mns_pager_info
 * @var int    $mns_pager_page
 * @var int    $mns_pager_total
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$info  = isset( $mns_pager_info ) ? (string) $mns_pager_info : '';
$page  = isset( $mns_pager_page ) ? max( 1, (int) $mns_pager_page ) : 1;
$total = isset( $mns_pager_total ) ? max( 1, (int) $mns_pager_total ) : 1;
?>
<nav class="mns-pager" aria-label="<?php echo esc_attr__( 'صفحه‌بندی', 'medyar-nursing-services' ); ?>">
	<?php if ( '' !== $info ) : ?>
		<span class="mns-pager__info"><?php echo esc_html( $info ); ?></span>
	<?php else : ?>
		<span class="mns-pager__info"><?php echo esc_html( sprintf( /* translators: 1: page 2: total */ __( 'صفحه %1$s از %2$s', 'medyar-nursing-services' ), $page, $total ) ); ?></span>
	<?php endif; ?>
	<div class="mns-pager__nav">
		<button type="button" class="mns-pager__btn" <?php disabled( $page <= 1 ); ?>><?php echo esc_html__( 'قبل', 'medyar-nursing-services' ); ?></button>
		<button type="button" class="mns-pager__btn is-current" aria-current="page"><?php echo esc_html( (string) $page ); ?></button>
		<button type="button" class="mns-pager__btn" <?php disabled( $page >= $total ); ?>><?php echo esc_html__( 'بعد', 'medyar-nursing-services' ); ?></button>
	</div>
</nav>
