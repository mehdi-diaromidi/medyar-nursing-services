<?php
/**
 * Nurse orders list.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data  = MNS_Static_Fixtures::get( 'nurse/orders' );
$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'سفارش‌های من', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'شروع/پایان خدمت، چک‌لیست و گزارش.', 'medyar-nursing-services' ); ?></p>
<?php if ( empty( $items ) ) : ?>
	<div class="mns-empty">
		<p class="mns-empty__title"><?php echo esc_html__( 'سفارش فعالی نیست', 'medyar-nursing-services' ); ?></p>
	</div>
<?php else : ?>
	<?php foreach ( $items as $row ) : ?>
		<article class="mns-card" style="margin-bottom:16px">
			<div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center">
				<div>
					<h2 class="mns-card__title" style="margin:0"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></h2>
					<p style="margin:6px 0 0;color:var(--mns-muted-strong);font-size:0.875rem"><?php echo esc_html( (string) ( $row['meta'] ?? '' ) ); ?></p>
				</div>
				<span class="mns-status mns-status--<?php echo esc_attr( (string) ( $row['tone'] ?? 'progress' ) ); ?>"><?php echo esc_html( (string) ( $row['status'] ?? '' ) ); ?></span>
			</div>
			<?php if ( ! empty( $row['view_url'] ) ) : ?>
				<p style="margin:14px 0 0">
					<a class="mns-submit-btn" style="width:auto;min-height:44px;padding:8px 18px;display:inline-flex" href="<?php echo esc_url( (string) $row['view_url'] ); ?>"><?php echo esc_html__( 'ورود به جزئیات و چک‌لیست', 'medyar-nursing-services' ); ?></a>
				</p>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
<?php endif; ?>
