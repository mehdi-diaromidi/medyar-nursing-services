<?php
/**
 * Patient invoice detail (Phase 4) — §9 anatomy.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$pack    = MNS_Static_Fixtures::get( 'patient/invoices' );
$details = isset( $pack['details'] ) && is_array( $pack['details'] ) ? $pack['details'] : array();
$default = (string) ( $pack['default_number'] ?? 'INV-1001' );
$number  = isset( $_GET['mns_invoice'] ) ? sanitize_text_field( wp_unslash( $_GET['mns_invoice'] ) ) : $default; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! isset( $details[ $number ] ) ) {
	$number = $default;
}
$d      = isset( $details[ $number ] ) && is_array( $details[ $number ] ) ? $details[ $number ] : array();
$status = (string) ( $d['status'] ?? 'unpaid' );

if ( MNS_Dev::is( 'paid' ) ) {
	$status = 'paid';
} elseif ( MNS_Dev::is( 'failed' ) ) {
	$status = 'failed';
} elseif ( MNS_Dev::is( 'paying' ) ) {
	$status = 'paying';
}

$status_label = array(
	'unpaid' => __( 'پرداخت‌نشده', 'medyar-nursing-services' ),
	'paid'   => __( 'پرداخت‌شده', 'medyar-nursing-services' ),
	'void'   => __( 'باطل', 'medyar-nursing-services' ),
	'failed' => __( 'پرداخت ناموفق', 'medyar-nursing-services' ),
	'paying' => __( 'در حال پرداخت…', 'medyar-nursing-services' ),
);

$list_url = MNS_Pages::panel_url( 'patient', 'invoices' );

ob_start();
echo '<span class="mns-status mns-status--' . esc_attr( 'unpaid' === $status || 'failed' === $status ? 'waiting' : ( 'paid' === $status ? 'done' : 'muted' ) ) . '">';
echo esc_html( $status_label[ $status ] ?? $status );
echo '</span>';
$badge = ob_get_clean();

MNS_UI::page_header(
	array(
		'title'      => (string) ( $d['service'] ?? __( 'فاکتور', 'medyar-nursing-services' ) ),
		'lead'       => sprintf(
			/* translators: invoice number */
			__( 'شماره %s', 'medyar-nursing-services' ),
			MNS_Format::digits( (string) ( $d['number'] ?? $number ) )
		),
		'back_url'   => $list_url,
		'back_label' => __( 'فاکتورها', 'medyar-nursing-services' ),
		'badge_html' => $badge,
	)
);

if ( 'failed' === $status ) {
	MNS_UI::alert(
		array(
			'tone'  => 'danger',
			'title' => __( 'پرداخت انجام نشد', 'medyar-nursing-services' ),
			'text'  => __( 'مجدداً تلاش کنید یا با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ),
		)
	);
}
if ( 'void' === $status ) {
	MNS_UI::alert(
		array(
			'tone' => 'warning',
			'text' => (string) ( $d['note'] ?? __( 'این فاکتور باطل شده است.', 'medyar-nursing-services' ) ),
		)
	);
}
?>

<section class="mns-card mns-invoice">
	<ul class="mns-kv">
		<li><span><?php echo esc_html__( 'سفارش', 'medyar-nursing-services' ); ?></span><strong dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $d['order'] ?? '' ) ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['patient'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'تاریخ', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( MNS_Format::human( (string) ( $d['when'] ?? '' ) ) ); ?></strong></li>
		<?php if ( ! empty( $d['paid_at'] ) ) : ?>
			<li><span><?php echo esc_html__( 'زمان پرداخت', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( MNS_Format::human( (string) $d['paid_at'] ) ); ?></strong></li>
		<?php endif; ?>
	</ul>
	<?php
	$lines = isset( $d['lines'] ) && is_array( $d['lines'] ) ? $d['lines'] : array();
	if ( ! empty( $d['discount'] ) ) {
		$lines[] = array(
			'label'  => __( 'تخفیف / وفاداری', 'medyar-nursing-services' ),
			'amount' => -1 * (int) $d['discount'],
		);
	}
	MNS_UI::price_summary(
		array(
			'base'  => $d['base'] ?? 0,
			'lines' => $lines,
			'total' => $d['total'] ?? 0,
			'note'  => (string) ( $d['note'] ?? '' ),
		)
	);
	?>
</section>

<div class="mns-order-actions">
	<?php if ( in_array( $status, array( 'unpaid', 'failed' ), true ) ) : ?>
		<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'درگاه پرداخت باز شد (شبیه‌سازی).', 'medyar-nursing-services' ); ?>">
			<?php
			echo esc_html(
				sprintf(
					/* translators: amount */
					__( 'پرداخت آنلاین %s', 'medyar-nursing-services' ),
					MNS_Format::toman( $d['total'] ?? 0 )
				)
			);
			?>
		</button>
	<?php endif; ?>
	<button type="button" class="mns-outline-btn" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'دانلود PDF به‌زودی فعال می‌شود.', 'medyar-nursing-services' ); ?>">
		<?php echo esc_html__( 'دانلود PDF / چاپ', 'medyar-nursing-services' ); ?>
	</button>
	<?php if ( in_array( $status, array( 'unpaid', 'failed' ), true ) ) : ?>
		<?php MNS_UI::support_call( array( 'variant' => 'text' ) ); ?>
	<?php endif; ?>
</div>
