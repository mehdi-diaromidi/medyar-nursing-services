<?php
/**
 * Nurse order workspace — start/end, urgent, checklist, extras, report.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d          = MNS_Static_Fixtures::get( 'nurse/order_view' );
$checklist  = isset( $d['checklist'] ) && is_array( $d['checklist'] ) ? $d['checklist'] : array();
$extras     = isset( $d['extras'] ) && is_array( $d['extras'] ) ? $d['extras'] : array();
$consumables = isset( $d['consumables'] ) && is_array( $d['consumables'] ) ? $d['consumables'] : array();
$med        = isset( $d['medication'] ) && is_array( $d['medication'] ) ? $d['medication'] : array();
$list_url   = MNS_Pages::panel_url( 'nurse', 'orders' );
?>
<p class="mns-breadcrumb"><a href="<?php echo esc_url( $list_url ); ?>"><?php echo esc_html__( 'سفارش‌های من', 'medyar-nursing-services' ); ?></a></p>

<header class="mns-order-head">
	<div>
		<h1 class="mns-dash__title" style="margin-bottom:8px"><?php echo esc_html( (string) ( $d['service'] ?? '' ) ); ?></h1>
		<p class="mns-dash__lead" style="margin:0" dir="ltr"><?php echo esc_html( (string) ( $d['tracking'] ?? '' ) ); ?></p>
	</div>
	<span class="mns-status mns-status--<?php echo esc_attr( (string) ( $d['tone'] ?? 'progress' ) ); ?>"><?php echo esc_html( (string) ( $d['status'] ?? '' ) ); ?></span>
</header>

<section class="mns-card">
	<ul class="mns-kv">
		<li><span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['patient'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'زمان', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['scheduled'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['area'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'آدرس', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['address'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'یادداشت', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['notes'] ?? '' ) ); ?></strong></li>
	</ul>
</section>

<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

<div class="mns-order-actions mns-order-actions--nurse">
	<button type="button" class="mns-submit-btn" style="width:auto;min-height:44px;padding:10px 20px" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'شروع خدمت ثبت شد.', 'medyar-nursing-services' ); ?>"><?php echo esc_html__( 'شروع خدمت', 'medyar-nursing-services' ); ?></button>
	<button type="button" class="mns-btn-outline" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'پایان خدمت ثبت شد.', 'medyar-nursing-services' ); ?>"><?php echo esc_html__( 'پایان خدمت', 'medyar-nursing-services' ); ?></button>
	<button type="button" class="mns-btn-outline mns-btn-outline--urgent" data-mns-urgent-confirm data-mns-success="<?php echo esc_attr__( 'مورد فوری به ادمین اعلام شد.', 'medyar-nursing-services' ); ?>"><?php echo esc_html__( 'مورد فوری', 'medyar-nursing-services' ); ?></button>
</div>

<section class="mns-card" aria-labelledby="mns-check-title">
	<h2 id="mns-check-title" class="mns-card__title"><?php echo esc_html__( 'چک‌لیست تخصصی', 'medyar-nursing-services' ); ?></h2>
	<p class="mns-field-hint"><?php echo esc_html__( 'مبالغ از تعرفه سیستم محاسبه می‌شود؛ مبلغ دستی وارد نکنید.', 'medyar-nursing-services' ); ?></p>
	<div class="mns-checklist">
		<?php foreach ( $checklist as $c ) : ?>
			<label class="mns-check">
				<input type="checkbox" name="checklist[]" value="<?php echo esc_attr( (string) ( $c['id'] ?? '' ) ); ?>">
				<span><?php echo esc_html( (string) ( $c['label'] ?? '' ) ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>
</section>

<section class="mns-card" aria-labelledby="mns-extra-title">
	<h2 id="mns-extra-title" class="mns-card__title"><?php echo esc_html__( 'خدمات اضافه', 'medyar-nursing-services' ); ?></h2>
	<div class="mns-checklist">
		<?php foreach ( $extras as $e ) : ?>
			<label class="mns-check">
				<input type="checkbox" name="extras[]" value="<?php echo esc_attr( (string) ( $e['id'] ?? '' ) ); ?>">
				<span><?php echo esc_html( (string) ( $e['label'] ?? '' ) ); ?> <em class="mns-check__price"><?php echo esc_html( (string) ( $e['price'] ?? '' ) ); ?></em></span>
			</label>
		<?php endforeach; ?>
	</div>
</section>

<section class="mns-card" aria-labelledby="mns-cons-title">
	<h2 id="mns-cons-title" class="mns-card__title"><?php echo esc_html__( 'اقلام مصرفی', 'medyar-nursing-services' ); ?></h2>
	<div class="mns-checklist">
		<?php foreach ( $consumables as $k ) : ?>
			<label class="mns-check">
				<input type="checkbox" name="consumables[]" value="<?php echo esc_attr( (string) ( $k['id'] ?? '' ) ); ?>">
				<span><?php echo esc_html( (string) ( $k['label'] ?? '' ) ); ?> <em class="mns-check__price"><?php echo esc_html( (string) ( $k['price'] ?? '' ) ); ?></em></span>
			</label>
		<?php endforeach; ?>
	</div>
</section>

<?php if ( ! empty( $med['enabled'] ) ) : ?>
	<section class="mns-card mns-med-lane">
		<h2 class="mns-card__title"><?php echo esc_html__( 'خط دارو', 'medyar-nursing-services' ); ?></h2>
		<p><span class="mns-status mns-status--progress"><?php echo esc_html( (string) ( $med['status'] ?? '' ) ); ?></span></p>
		<label class="mns-upload">
			<span class="mns-upload__label"><?php echo esc_html__( 'آپلود نسخه (UI)', 'medyar-nursing-services' ); ?></span>
			<input type="file" disabled accept="image/*,.pdf">
		</label>
	</section>
<?php endif; ?>

<section class="mns-card" aria-labelledby="mns-report-title">
	<h2 id="mns-report-title" class="mns-card__title"><?php echo esc_html__( 'گزارش پایان مراجعه', 'medyar-nursing-services' ); ?></h2>
	<label class="mns-field-block">
		<span class="mns-field-block__label"><?php echo esc_html__( 'شرح حال و وضعیت نهایی', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control mns-field-block__control--area">
			<textarea name="report_text" rows="4" maxlength="1000" data-mns-report></textarea>
		</span>
	</label>
	<button type="button" class="mns-submit-btn" data-mns-finalize-report data-mns-success="<?php echo esc_attr__( 'گزارش نهایی ثبت شد و دیگر قابل ویرایش نیست.', 'medyar-nursing-services' ); ?>"><?php echo esc_html__( 'ثبت نهایی گزارش', 'medyar-nursing-services' ); ?></button>
</section>
