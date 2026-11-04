<?php
/**
 * Patient order detail (Phase 3) — status-driven from fixtures.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$pack    = MNS_Static_Fixtures::get( 'patient/order_view' );
$by      = isset( $pack['by_status'] ) && is_array( $pack['by_status'] ) ? $pack['by_status'] : array();
$default = (string) ( $pack['default_status'] ?? MNS_Order_Status::ASSIGNED );

$status = $default;
if ( MNS_Dev::enabled() ) {
	$raw = MNS_Dev::state();
	if ( MNS_Order_Status::is_valid( $raw ) && isset( $by[ $raw ] ) ) {
		$status = $raw;
	}
}
$d      = isset( $by[ $status ] ) && is_array( $by[ $status ] ) ? $by[ $status ] : ( $by[ $default ] ?? array() );
$status = (string) ( $d['status'] ?? $status );

$orders_url = MNS_Pages::panel_url( 'patient', 'orders' );
$info       = MNS_Order_Status::get( $status );
$nurse      = isset( $d['nurse'] ) && is_array( $d['nurse'] ) ? $d['nurse'] : null;
$invoice    = isset( $d['invoice'] ) && is_array( $d['invoice'] ) ? $d['invoice'] : null;
$pay_needed = MNS_Order_Status::INVOICED === $status && ! empty( $invoice );

ob_start();
MNS_UI::status_badge( $status );
$badge = ob_get_clean();

MNS_UI::page_header(
	array(
		'title'      => (string) ( $d['service'] ?? __( 'جزئیات سفارش', 'medyar-nursing-services' ) ),
		'lead'       => MNS_Order_Status::sentence( $status, 'patient' ),
		'back_url'   => $orders_url,
		'back_label' => __( 'سفارش‌های من', 'medyar-nursing-services' ),
		'badge_html' => $badge,
	)
);
?>

<p class="mns-tracking-row">
	<span><?php echo esc_html( MNS_Copy::t( 'label.tracking_code' ) ); ?></span>
	<strong dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $d['tracking'] ?? '' ) ) ); ?></strong>
	<button type="button" class="mns-link-btn" data-mns-copy="<?php echo esc_attr( (string) ( $d['tracking'] ?? '' ) ); ?>">
		<?php echo esc_html( MNS_Copy::t( 'action.copy' ) ); ?>
	</button>
</p>

<section class="mns-card">
	<h2 class="mns-card__title"><?php echo esc_html__( 'مسیر سفارش', 'medyar-nursing-services' ); ?></h2>
	<?php MNS_UI::order_timeline( $status, array( 'variant' => 'full', 'audience' => 'patient' ) ); ?>
</section>

<?php if ( $nurse ) : ?>
	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'پرستار تخصیص‌یافته', 'medyar-nursing-services' ); ?></h2>
		<?php MNS_UI::nurse_card( array( 'nurse' => $nurse, 'show_call' => true ) ); ?>
		<p class="mns-field-hint"><?php echo esc_html__( 'برای هماهنگی فوری با پشتیبانی مدیار تماس بگیرید.', 'medyar-nursing-services' ); ?> <?php echo esc_html__( 'TODO(client-decision): نمایش مستقیم شماره پرستار.', 'medyar-nursing-services' ); ?></p>
	</section>
<?php endif; ?>

<section class="mns-card">
	<h2 class="mns-card__title"><?php echo esc_html__( 'اطلاعات مراجعه', 'medyar-nursing-services' ); ?></h2>
	<ul class="mns-kv">
		<li><span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['patient'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'زمان', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( MNS_Format::human( (string) ( $d['when'] ?? '' ) ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'آدرس', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['address'] ?? '' ) ); ?></strong></li>
		<?php if ( ! empty( $d['phone'] ) ) : ?>
			<li><span><?php echo esc_html__( 'تماس', 'medyar-nursing-services' ); ?></span><strong dir="ltr"><?php echo esc_html( MNS_Format::mobile( (string) $d['phone'] ) ); ?></strong></li>
		<?php endif; ?>
	</ul>
	<?php if ( ! empty( $d['essentials'] ) && is_array( $d['essentials'] ) ) : ?>
		<h3 class="mns-card__subtitle"><?php echo esc_html__( 'اطلاعات ضروری', 'medyar-nursing-services' ); ?></h3>
		<ul class="mns-service-list">
			<?php foreach ( $d['essentials'] as $line ) : ?>
				<li><?php echo esc_html( (string) $line ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>

<?php if ( ! empty( $d['medication']['enabled'] ) ) : ?>
	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'مرحلهٔ دارو', 'medyar-nursing-services' ); ?></h2>
		<?php
		MNS_UI::alert(
			array(
				'tone' => 'info',
				'text' => (string) ( $d['medication']['hint'] ?? '' ),
			)
		);
		?>
	</section>
<?php endif; ?>

<?php if ( ! empty( $d['report'] ) ) : ?>
	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'نتیجهٔ مراجعه', 'medyar-nursing-services' ); ?></h2>
		<p class="mns-catalog__muted"><?php echo esc_html( (string) $d['report'] ); ?></p>
	</section>
<?php endif; ?>

<?php if ( $invoice ) : ?>
	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'فاکتور', 'medyar-nursing-services' ); ?></h2>
		<?php
		MNS_UI::price_summary(
			array(
				'base'  => $invoice['base'] ?? 0,
				'lines' => $invoice['lines'] ?? array(),
				'total' => $invoice['total'] ?? 0,
				'note'  => MNS_Copy::t( 'price.travel' ),
			)
		);
		?>
	</section>
<?php endif; ?>

<?php if ( MNS_Order_Status::NEEDS_REVIEW === $status ) : ?>
	<?php
	MNS_UI::alert(
		array(
			'tone'  => 'warning',
			'title' => __( 'در حال بررسی', 'medyar-nursing-services' ),
			'text'  => MNS_Order_Status::sentence( $status, 'patient' ),
		)
	);
	?>
<?php endif; ?>

<div class="mns-order-actions">
	<?php if ( ! empty( $d['can_survey'] ) && ! empty( $d['survey_url'] ) ) : ?>
		<a class="mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( (string) $d['survey_url'] ); ?>"><?php echo esc_html__( 'ثبت نظر', 'medyar-nursing-services' ); ?></a>
	<?php endif; ?>
	<?php if ( ! empty( $d['can_repick'] ) && ! empty( $d['reorder_url'] ) ) : ?>
		<a class="mns-outline-btn" href="<?php echo esc_url( (string) $d['reorder_url'] ); ?>"><?php echo esc_html__( 'همان پرستار قبلی', 'medyar-nursing-services' ); ?></a>
	<?php endif; ?>
	<?php if ( ! empty( $d['reorder_url'] ) ) : ?>
		<a class="mns-outline-btn" href="<?php echo esc_url( (string) $d['reorder_url'] ); ?>"><?php echo esc_html__( 'درخواست مجدد', 'medyar-nursing-services' ); ?></a>
	<?php endif; ?>
	<?php if ( ! empty( $d['can_cancel'] ) ) : ?>
		<button type="button" class="mns-link-btn" data-mns-dialog-open="mns-cancel-order"><?php echo esc_html__( 'لغو سفارش', 'medyar-nursing-services' ); ?></button>
	<?php endif; ?>
	<?php if ( MNS_Order_Status::NEEDS_REVIEW === $status || MNS_Order_Status::CANCELLED === $status ) : ?>
		<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
	<?php endif; ?>
</div>

<?php if ( $pay_needed ) : ?>
	<?php
	MNS_UI::sticky_action(
		array(
			'primary_label' => sprintf(
				/* translators: %s: amount */
				__( 'پرداخت %s', 'medyar-nursing-services' ),
				MNS_Format::toman( $invoice['total'] ?? 0 )
			),
			'as_link'       => true,
			'primary_url'   => (string) ( $d['invoice_url'] ?? MNS_Pages::panel_url( 'patient', 'invoices' ) ),
			'hint'          => __( 'مهلت پرداخت همین حالا است.', 'medyar-nursing-services' ),
		)
	);
	?>
<?php endif; ?>

<?php
if ( ! empty( $d['can_cancel'] ) ) {
	ob_start();
	echo '<p>' . esc_html__( 'با لغو، درخواست از صف تخصیص خارج می‌شود. TODO(client-decision): قاعدهٔ دقیق لغو.', 'medyar-nursing-services' ) . '</p>';
	$body = ob_get_clean();
	ob_start();
	echo '<button type="button" class="mns-outline-btn" data-mns-dialog-close>' . esc_html( MNS_Copy::t( 'action.cancel' ) ) . '</button>';
	echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-mock-action data-mns-success="' . esc_attr__( 'درخواست لغو ثبت شد.', 'medyar-nursing-services' ) . '" data-mns-dialog-close>' . esc_html__( 'بله، لغو شود', 'medyar-nursing-services' ) . '</button>';
	$footer = ob_get_clean();
	MNS_UI::partial(
		'dialog',
		array(
			'id'          => 'mns-cancel-order',
			'title'       => __( 'لغو سفارش؟', 'medyar-nursing-services' ),
			'body_html'   => $body,
			'footer_html' => $footer,
			'variant'     => 'sheet',
		)
	);
}
