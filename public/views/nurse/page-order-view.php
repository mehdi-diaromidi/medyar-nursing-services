<?php
/**
 * Nurse order workspace (Phase 5) — status-driven.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$pack    = MNS_Static_Fixtures::get( 'nurse/order_view' );
$by      = isset( $pack['by_status'] ) && is_array( $pack['by_status'] ) ? $pack['by_status'] : array();
$default = (string) ( $pack['default_status'] ?? MNS_Order_Status::ASSIGNED );

$status = $default;
if ( MNS_Dev::enabled() ) {
	$raw = MNS_Dev::state();
	if ( '' === $raw || 'default' === $raw ) {
		$raw = isset( $_GET['mns_state'] ) ? sanitize_key( wp_unslash( $_GET['mns_state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	if ( MNS_Order_Status::is_valid( $raw ) && isset( $by[ $raw ] ) ) {
		$status = $raw;
	}
}
$d      = isset( $by[ $status ] ) && is_array( $by[ $status ] ) ? $by[ $status ] : ( $by[ $default ] ?? array() );
$status = (string) ( $d['status'] ?? $status );
$list   = MNS_Pages::panel_url( 'nurse', 'orders' );

ob_start();
MNS_UI::status_badge( $status );
$badge = ob_get_clean();

MNS_UI::page_header(
	array(
		'title'      => (string) ( $d['service'] ?? __( 'جزئیات سفارش', 'medyar-nursing-services' ) ),
		'lead'       => MNS_Order_Status::sentence( $status, 'nurse' ),
		'back_url'   => $list,
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
	<h2 class="mns-card__title"><?php echo esc_html__( 'خلاصه مراجعه', 'medyar-nursing-services' ); ?></h2>
	<ul class="mns-kv">
		<li><span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['patient'] ?? '' ) ); ?> · <?php echo esc_html( (string) ( $d['patient_meta'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'زمان', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( MNS_Format::human( (string) ( $d['when'] ?? '' ) ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['area'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'آدرس', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['address'] ?? '' ) ); ?></strong></li>
		<?php if ( ! empty( $d['phone'] ) ) : ?>
			<li><span><?php echo esc_html__( 'تماس بیمار', 'medyar-nursing-services' ); ?></span><strong dir="ltr"><?php echo esc_html( MNS_Format::mobile( (string) $d['phone'] ) ); ?></strong></li>
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
	<?php if ( ! empty( $d['notes'] ) ) : ?>
		<p class="mns-field-hint"><?php echo esc_html( (string) $d['notes'] ); ?></p>
	<?php endif; ?>
</section>

<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

<div class="mns-order-actions mns-order-actions--nurse">
	<?php if ( ! empty( $d['can_start'] ) ) : ?>
		<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'شروع خدمت ثبت شد.', 'medyar-nursing-services' ); ?>">
			<?php echo esc_html__( 'شروع خدمت', 'medyar-nursing-services' ); ?>
		</button>
	<?php endif; ?>
	<?php if ( ! empty( $d['can_end'] ) ) : ?>
		<button type="button" class="mns-outline-btn" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'پایان خدمت ثبت شد.', 'medyar-nursing-services' ); ?>">
			<?php echo esc_html__( 'پایان خدمت', 'medyar-nursing-services' ); ?>
		</button>
	<?php endif; ?>
	<?php if ( ! empty( $d['can_urgent'] ) ) : ?>
		<button type="button" class="mns-btn-outline mns-btn-outline--urgent" data-mns-urgent-confirm data-mns-success="<?php echo esc_attr__( 'مورد فوری به ادمین اعلام شد.', 'medyar-nursing-services' ); ?>">
			<?php echo esc_html__( 'مورد فوری', 'medyar-nursing-services' ); ?>
		</button>
	<?php endif; ?>
	<?php if ( MNS_Order_Status::NEEDS_REVIEW === $status ) : ?>
		<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
	<?php endif; ?>
</div>

<?php if ( ! empty( $d['workspace'] ) ) : ?>
	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'چک‌لیست تخصصی', 'medyar-nursing-services' ); ?></h2>
		<p class="mns-field-hint"><?php echo esc_html__( 'مبالغ از تعرفه سیستم محاسبه می‌شود؛ مبلغ دستی وارد نکنید.', 'medyar-nursing-services' ); ?></p>
		<div class="mns-checklist">
			<?php foreach ( (array) ( $d['checklist'] ?? array() ) as $c ) : ?>
				<label class="mns-check">
					<input type="checkbox" name="checklist[]" value="<?php echo esc_attr( (string) ( $c['id'] ?? '' ) ); ?>" <?php disabled( ! empty( $d['report_locked'] ) ); ?>>
					<span><?php echo esc_html( (string) ( $c['label'] ?? '' ) ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'خدمات اضافه', 'medyar-nursing-services' ); ?></h2>
		<div class="mns-checklist">
			<?php foreach ( (array) ( $d['extras'] ?? array() ) as $e ) : ?>
				<label class="mns-check">
					<input type="checkbox" name="extras[]" value="<?php echo esc_attr( (string) ( $e['id'] ?? '' ) ); ?>" <?php disabled( ! empty( $d['report_locked'] ) ); ?>>
					<span>
						<?php echo esc_html( (string) ( $e['label'] ?? '' ) ); ?>
						<em class="mns-check__price"><?php echo esc_html( MNS_Format::toman( $e['amount'] ?? 0 ) ); ?></em>
					</span>
				</label>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'اقلام مصرفی', 'medyar-nursing-services' ); ?></h2>
		<div class="mns-checklist">
			<?php foreach ( (array) ( $d['consumables'] ?? array() ) as $k ) : ?>
				<label class="mns-check">
					<input type="checkbox" name="consumables[]" value="<?php echo esc_attr( (string) ( $k['id'] ?? '' ) ); ?>" <?php disabled( ! empty( $d['report_locked'] ) ); ?>>
					<span>
						<?php echo esc_html( (string) ( $k['label'] ?? '' ) ); ?>
						<em class="mns-check__price"><?php echo esc_html( MNS_Format::toman( $k['amount'] ?? 0 ) ); ?></em>
					</span>
				</label>
			<?php endforeach; ?>
		</div>
	</section>

	<?php if ( ! empty( $d['medication']['enabled'] ) ) : ?>
		<section class="mns-card mns-med-lane">
			<h2 class="mns-card__title"><?php echo esc_html__( 'خط دارو', 'medyar-nursing-services' ); ?></h2>
			<?php
			MNS_UI::alert(
				array(
					'tone' => 'info',
					'text' => (string) ( $d['medication']['label'] ?? '' ),
				)
			);
			?>
			<label class="mns-upload">
				<span class="mns-upload__label"><?php echo esc_html__( 'آپلود نسخه / کارت دارویی (UI)', 'medyar-nursing-services' ); ?></span>
				<input type="file" disabled accept="image/*,.pdf">
			</label>
		</section>
	<?php endif; ?>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'گزارش پایان مراجعه', 'medyar-nursing-services' ); ?></h2>
		<?php if ( ! empty( $d['report_locked'] ) ) : ?>
			<p class="mns-catalog__muted"><?php echo esc_html( (string) ( $d['report'] ?? '' ) ); ?></p>
			<p class="mns-field-hint">
				<?php echo esc_html__( 'ثبت نهایی شد — فقط‌خواندنی', 'medyar-nursing-services' ); ?>
				<?php if ( ! empty( $d['report_at'] ) ) : ?>
					· <?php echo esc_html( MNS_Format::human( (string) $d['report_at'] ) ); ?>
				<?php endif; ?>
			</p>
		<?php else : ?>
			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'شرح حال و وضعیت نهایی', 'medyar-nursing-services' ); ?></span>
				<span class="mns-field-block__control mns-field-block__control--area">
					<textarea name="report_text" rows="4" maxlength="1000" data-mns-report><?php echo esc_textarea( (string) ( $d['report'] ?? '' ) ); ?></textarea>
				</span>
			</label>
			<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-finalize-report data-mns-success="<?php echo esc_attr__( 'گزارش نهایی ثبت شد و دیگر قابل ویرایش نیست.', 'medyar-nursing-services' ); ?>">
				<?php echo esc_html__( 'ثبت نهایی گزارش', 'medyar-nursing-services' ); ?>
			</button>
		<?php endif; ?>
	</section>
<?php endif; ?>
