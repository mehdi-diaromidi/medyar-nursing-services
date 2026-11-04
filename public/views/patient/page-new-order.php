<?php
/**
 * Patient new order — 4-step care path (static).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data       = MNS_Static_Fixtures::get( 'patient/new_order' );
$patients   = isset( $data['patients'] ) && is_array( $data['patients'] ) ? $data['patients'] : array();
$categories = isset( $data['categories'] ) && is_array( $data['categories'] ) ? $data['categories'] : array();
$services   = isset( $data['services'] ) && is_array( $data['services'] ) ? $data['services'] : array();
$cities     = isset( $data['cities'] ) && is_array( $data['cities'] ) ? $data['cities'] : array();
$times      = isset( $data['times'] ) && is_array( $data['times'] ) ? $data['times'] : array();
$phone      = isset( $data['default_phone'] ) ? (string) $data['default_phone'] : '';

$services_json = wp_json_encode( $services );
$cities_json   = wp_json_encode( $cities );
if ( ! is_string( $services_json ) ) {
	$services_json = '[]';
}
if ( ! is_string( $cities_json ) ) {
	$cities_json = '[]';
}
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'ثبت سفارش', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'مسیر مراقبت را گام‌به‌گام کامل کنید. مبلغ نهایی طبق تعرفه سیستم محاسبه می‌شود.', 'medyar-nursing-services' ); ?></p>

<nav class="mns-stepper" data-mns-stepper aria-label="<?php echo esc_attr__( 'مراحل ثبت سفارش', 'medyar-nursing-services' ); ?>">
	<button type="button" class="mns-stepper__item is-current" data-mns-step-goto="1" aria-current="step">
		<span class="mns-stepper__num">۱</span>
		<span class="mns-stepper__label"><?php echo esc_html__( 'بیمار و تماس', 'medyar-nursing-services' ); ?></span>
	</button>
	<button type="button" class="mns-stepper__item" data-mns-step-goto="2">
		<span class="mns-stepper__num">۲</span>
		<span class="mns-stepper__label"><?php echo esc_html__( 'مکان و زمان', 'medyar-nursing-services' ); ?></span>
	</button>
	<button type="button" class="mns-stepper__item" data-mns-step-goto="3">
		<span class="mns-stepper__num">۳</span>
		<span class="mns-stepper__label"><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></span>
	</button>
	<button type="button" class="mns-stepper__item" data-mns-step-goto="4">
		<span class="mns-stepper__num">۴</span>
		<span class="mns-stepper__label"><?php echo esc_html__( 'مرور و ثبت', 'medyar-nursing-services' ); ?></span>
	</button>
</nav>

<div class="mns-form-grid">
	<form
		class="mns-form mns-card"
		method="post"
		data-mns-mock="ok"
		data-mns-order-form
		data-mns-services="<?php echo esc_attr( $services_json ); ?>"
		data-mns-cities="<?php echo esc_attr( $cities_json ); ?>"
		data-mns-success="<?php echo esc_attr__( 'درخواست ثبت شد. کد پیگیری: MNS-1403-1102', 'medyar-nursing-services' ); ?>"
	>
		<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>
		<input type="hidden" name="patient_mode" value="self" data-mns-patient-mode>
		<input type="hidden" name="nurse_gender" value="" data-mns-nurse-gender>
		<input type="hidden" name="is_urgent" value="0" data-mns-is-urgent>
		<input type="hidden" name="category_id" value="" data-mns-category>

		<section class="mns-step is-active" data-mns-step="1" aria-labelledby="mns-step-1-title">
			<h2 id="mns-step-1-title" class="mns-step__title"><?php echo esc_html__( 'بیمار و تماس', 'medyar-nursing-services' ); ?></h2>

			<div class="mns-seg" role="radiogroup" aria-label="<?php echo esc_attr__( 'انتخاب بیمار', 'medyar-nursing-services' ); ?>" data-mns-seg="patient_mode">
				<button type="button" class="mns-seg__btn is-selected" role="radio" aria-checked="true" data-value="self"><?php echo esc_html__( 'خودم', 'medyar-nursing-services' ); ?></button>
				<button type="button" class="mns-seg__btn" role="radio" aria-checked="false" data-value="saved"><?php echo esc_html__( 'افراد ذخیره‌شده', 'medyar-nursing-services' ); ?></button>
				<button type="button" class="mns-seg__btn" role="radio" aria-checked="false" data-value="new"><?php echo esc_html__( 'بیمار جدید', 'medyar-nursing-services' ); ?></button>
			</div>

			<div data-mns-panel-mode="saved" class="is-hidden" hidden>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control mns-field-block__control--select">
						<select class="mns-select-native" name="patient_id" data-mns-select data-mns-patient-select>
							<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
							<?php foreach ( $patients as $p ) : ?>
								<option value="<?php echo esc_attr( (string) ( $p['id'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $p['label'] ?? $p['name'] ?? '' ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
					</span>
				</label>
			</div>

			<div data-mns-panel-mode="new" class="is-hidden" hidden>
				<div class="mns-form-row--2">
					<label class="mns-field-block">
						<span class="mns-field-block__label"><?php echo esc_html__( 'نام بیمار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control"><input type="text" name="new_patient_name" maxlength="100" autocomplete="name"></span>
					</label>
					<label class="mns-field-block">
						<span class="mns-field-block__label"><?php echo esc_html__( 'سن', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control"><input type="text" name="new_patient_age" inputmode="numeric" maxlength="3"></span>
					</label>
				</div>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'جنسیت بیمار', 'medyar-nursing-services' ); ?></span>
					<span class="mns-field-block__control mns-field-block__control--select">
						<select class="mns-select-native" name="new_patient_gender" data-mns-select>
							<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
							<option value="female"><?php echo esc_html__( 'خانم', 'medyar-nursing-services' ); ?></option>
							<option value="male"><?php echo esc_html__( 'آقا', 'medyar-nursing-services' ); ?></option>
						</select>
						<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
					</span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'بیماری‌های زمینه‌ای', 'medyar-nursing-services' ); ?></span>
					<span class="mns-field-block__control mns-field-block__control--area">
						<textarea name="new_patient_comorbidities" rows="2" maxlength="300" placeholder="<?php echo esc_attr__( 'اختیاری', 'medyar-nursing-services' ); ?>"></textarea>
					</span>
				</label>
			</div>

			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'شماره تماس', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
				<span class="mns-field-block__control">
					<input type="text" name="phone" data-mns-iran-mobile required placeholder="09123456789" autocomplete="tel" value="<?php echo esc_attr( $phone ); ?>">
				</span>
			</label>

			<div class="mns-step__nav">
				<button type="button" class="mns-submit-btn" data-mns-step-next><?php echo esc_html__( 'ادامه', 'medyar-nursing-services' ); ?></button>
			</div>
		</section>

		<section class="mns-step is-hidden" data-mns-step="2" hidden aria-hidden="true" aria-labelledby="mns-step-2-title">
			<h2 id="mns-step-2-title" class="mns-step__title"><?php echo esc_html__( 'مکان و زمان', 'medyar-nursing-services' ); ?></h2>

			<div class="mns-form-row--2">
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'شهر', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control mns-field-block__control--select">
						<select class="mns-select-native" name="city_id" data-mns-select data-mns-city required>
							<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
							<?php foreach ( $cities as $c ) : ?>
								<option value="<?php echo esc_attr( (string) ( $c['id'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $c['name'] ?? '' ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
					</span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control mns-field-block__control--select">
						<select class="mns-select-native" name="area_id" data-mns-select data-mns-area required>
							<option value=""><?php echo esc_html__( 'ابتدا شهر را انتخاب کنید', 'medyar-nursing-services' ); ?></option>
						</select>
						<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
					</span>
				</label>
			</div>

			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'آدرس دقیق', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
				<span class="mns-field-block__control">
					<input type="text" name="address" required maxlength="191" placeholder="<?php echo esc_attr__( 'خیابان، پلاک، واحد', 'medyar-nursing-services' ); ?>">
				</span>
			</label>

			<button type="button" class="mns-chip mns-chip--urgent" data-mns-urgent-chip aria-pressed="false">
				<?php echo esc_html__( 'سفارش فوری', 'medyar-nursing-services' ); ?>
			</button>
			<p class="mns-field-hint is-hidden" data-mns-urgent-hint hidden><?php echo esc_html__( 'اولویت تخصیص؛ ممکن است شرایط قیمت یا زمان متفاوت باشد.', 'medyar-nursing-services' ); ?></p>

			<div class="mns-form-row--2" data-mns-schedule-fields>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'تاریخ شمسی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control">
						<input type="text" name="preferred_date" placeholder="۱۴۰۳/۰۸/۲۰" autocomplete="off" data-mns-preferred-date>
					</span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'ساعت', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control mns-field-block__control--select">
						<select class="mns-select-native" name="preferred_time" data-mns-select data-mns-preferred-time>
							<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
							<?php foreach ( $times as $t ) : ?>
								<option value="<?php echo esc_attr( (string) $t ); ?>"><?php echo esc_html( (string) $t ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
					</span>
				</label>
			</div>

			<div class="mns-step__nav">
				<button type="button" class="mns-btn-outline" data-mns-step-prev><?php echo esc_html__( 'قبلی', 'medyar-nursing-services' ); ?></button>
				<button type="button" class="mns-submit-btn" data-mns-step-next><?php echo esc_html__( 'ادامه', 'medyar-nursing-services' ); ?></button>
			</div>
		</section>

		<section class="mns-step is-hidden" data-mns-step="3" hidden aria-hidden="true" aria-labelledby="mns-step-3-title">
			<h2 id="mns-step-3-title" class="mns-step__title"><?php echo esc_html__( 'خدمت و جزئیات', 'medyar-nursing-services' ); ?></h2>

			<p class="mns-field-block__label" style="margin-bottom:8px"><?php echo esc_html__( 'دسته خدمت', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
			<div class="mns-chip-row" role="radiogroup" aria-label="<?php echo esc_attr__( 'دسته خدمت', 'medyar-nursing-services' ); ?>" data-mns-category-chips>
				<?php foreach ( $categories as $cat ) : ?>
					<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) ( $cat['id'] ?? '' ) ); ?>">
						<?php echo esc_html( (string) ( $cat['name'] ?? '' ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'خدمت اصلی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
				<span class="mns-field-block__control mns-field-block__control--select">
					<select class="mns-select-native" name="service_id" data-mns-select data-mns-service required>
						<option value=""><?php echo esc_html__( 'ابتدا دسته را انتخاب کنید', 'medyar-nursing-services' ); ?></option>
					</select>
					<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
				</span>
			</label>

			<p class="mns-field-block__label" style="margin-bottom:8px"><?php echo esc_html__( 'جنسیت پرستار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
			<div class="mns-chip-row" role="radiogroup" aria-label="<?php echo esc_attr__( 'جنسیت پرستار', 'medyar-nursing-services' ); ?>" data-mns-gender-chips>
				<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="female"><?php echo esc_html__( 'زن', 'medyar-nursing-services' ); ?></button>
				<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="male"><?php echo esc_html__( 'مرد', 'medyar-nursing-services' ); ?></button>
				<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="any"><?php echo esc_html__( 'فرقی نمی‌کند', 'medyar-nursing-services' ); ?></button>
			</div>
			<p class="mns-field-hint"><?php echo esc_html__( 'این انتخاب در تخصیص پرستار لحاظ می‌شود.', 'medyar-nursing-services' ); ?></p>

			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'اطلاعات ضروری خدمت', 'medyar-nursing-services' ); ?></span>
				<span class="mns-field-block__control mns-field-block__control--area">
					<textarea name="service_notes" rows="3" maxlength="400" placeholder="<?php echo esc_attr__( 'علائم، وضعیت بیمار، توضیح پزشک…', 'medyar-nursing-services' ); ?>"></textarea>
				</span>
			</label>

			<label class="mns-switch-row">
				<button type="button" class="mns-switch" data-mns-switch data-mns-recurring-switch aria-pressed="false" aria-label="<?php echo esc_attr__( 'خدمات دوره‌ای', 'medyar-nursing-services' ); ?>"></button>
				<span><?php echo esc_html__( 'این سفارش بخشی از برنامه دوره‌ای است', 'medyar-nursing-services' ); ?></span>
			</label>
			<div class="mns-form-row--2 is-hidden" data-mns-recurring-fields hidden>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'تناوب', 'medyar-nursing-services' ); ?></span>
					<span class="mns-field-block__control mns-field-block__control--select">
						<select class="mns-select-native" name="recurring_pattern" data-mns-select>
							<option value="daily"><?php echo esc_html__( 'روزانه', 'medyar-nursing-services' ); ?></option>
							<option value="every_other"><?php echo esc_html__( 'یک‌روز در میان', 'medyar-nursing-services' ); ?></option>
							<option value="weekly"><?php echo esc_html__( 'هفتگی', 'medyar-nursing-services' ); ?></option>
						</select>
						<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
					</span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'تعداد نوبت', 'medyar-nursing-services' ); ?></span>
					<span class="mns-field-block__control"><input type="number" name="recurring_count" min="2" max="30" value="3"></span>
				</label>
			</div>

			<div class="mns-med-lane">
				<p class="mns-med-lane__title"><?php echo esc_html__( 'داروی نسخه‌دار (اختیاری)', 'medyar-nursing-services' ); ?></p>
				<label class="mns-upload">
					<span class="mns-upload__label"><?php echo esc_html__( 'آپلود عکس نسخه / کارت دارویی', 'medyar-nursing-services' ); ?></span>
					<input type="file" name="prescription_files[]" accept="image/*,.pdf" disabled>
					<span class="mns-upload__hint"><?php echo esc_html__( 'در فاز استاتیک فقط نمایش UI — آپلود واقعی بعداً.', 'medyar-nursing-services' ); ?></span>
				</label>
				<p class="mns-field-hint"><?php echo esc_html__( 'دارو فقط پس از تأیید پزشک مشاور به فاکتور اضافه می‌شود.', 'medyar-nursing-services' ); ?></p>
			</div>

			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'توضیحات تکمیلی', 'medyar-nursing-services' ); ?></span>
				<span class="mns-field-block__control mns-field-block__control--area">
					<textarea name="notes" rows="2" maxlength="400"></textarea>
				</span>
			</label>

			<div class="mns-step__nav">
				<button type="button" class="mns-btn-outline" data-mns-step-prev><?php echo esc_html__( 'قبلی', 'medyar-nursing-services' ); ?></button>
				<button type="button" class="mns-submit-btn" data-mns-step-next><?php echo esc_html__( 'مرور نهایی', 'medyar-nursing-services' ); ?></button>
			</div>
		</section>

		<section class="mns-step is-hidden" data-mns-step="4" hidden aria-hidden="true" aria-labelledby="mns-step-4-title">
			<h2 id="mns-step-4-title" class="mns-step__title"><?php echo esc_html__( 'مرور و ثبت', 'medyar-nursing-services' ); ?></h2>
			<div class="mns-review" data-mns-review aria-live="polite"></div>
			<div class="mns-step__nav">
				<button type="button" class="mns-btn-outline" data-mns-step-prev><?php echo esc_html__( 'قبلی', 'medyar-nursing-services' ); ?></button>
				<button type="submit" class="mns-submit-btn"><?php echo esc_html__( 'ثبت و ادامه', 'medyar-nursing-services' ); ?></button>
			</div>
			<p class="mns-field-hint" style="text-align:center"><?php echo esc_html__( 'پرداخت پس از صدور فاکتور از بخش فاکتورها انجام می‌شود.', 'medyar-nursing-services' ); ?></p>
		</section>
	</form>

	<aside class="mns-how" aria-labelledby="mns-how-title">
		<h2 id="mns-how-title" class="mns-how__title"><?php echo esc_html__( 'چطور پیش می‌رویم؟', 'medyar-nursing-services' ); ?></h2>
		<ol class="mns-how__steps">
			<li><span class="mns-how__num">۱</span><span><?php echo esc_html__( 'درخواست را ثبت می‌کنید.', 'medyar-nursing-services' ); ?></span></li>
			<li><span class="mns-how__num">۲</span><span><?php echo esc_html__( 'پرستار مناسب تخصیص داده می‌شود.', 'medyar-nursing-services' ); ?></span></li>
			<li><span class="mns-how__num">۳</span><span><?php echo esc_html__( 'خدمت انجام و گزارش ثبت می‌شود.', 'medyar-nursing-services' ); ?></span></li>
			<li><span class="mns-how__num">۴</span><span><?php echo esc_html__( 'فاکتور می‌آید و آنلاین پرداخت می‌کنید.', 'medyar-nursing-services' ); ?></span></li>
		</ol>
	</aside>
</div>
