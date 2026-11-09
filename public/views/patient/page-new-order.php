<?php
/**
 * Patient new order — 4-step wizard + confirmation (static) — desgin/png/ثبت سفارش/*.png + desgin/ui doc/ثبت سفارش.txt.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data    = MNS_Static_Fixtures::get( 'patient/new_order' );
$service = isset( $data['service'] ) && is_array( $data['service'] ) ? $data['service'] : array();
$patients = isset( $data['patients'] ) && is_array( $data['patients'] ) ? $data['patients'] : array();
$cities   = isset( $data['cities'] ) && is_array( $data['cities'] ) ? $data['cities'] : array();
$time_slots = isset( $data['time_slots'] ) && is_array( $data['time_slots'] ) ? $data['time_slots'] : array();
$fields_cfg = isset( $data['service_fields'] ) && is_array( $data['service_fields'] ) ? $data['service_fields'] : array();
$availability = isset( $data['availability'] ) && is_array( $data['availability'] ) ? $data['availability'] : array();
$phone    = isset( $data['default_phone'] ) ? (string) $data['default_phone'] : '';
$prepatient = isset( $_GET['mns_patient'] ) ? sanitize_text_field( wp_unslash( $_GET['mns_patient'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$tz_now = new DateTimeImmutable( 'now', wp_timezone() );
$months = MNS_Format::month_names();
$date_slots = array();
for ( $i = 0; $i < 7; $i++ ) {
	$day = $tz_now->modify( '+' . $i . ' days' );
	$j   = MNS_Format::g2j( (int) $day->format( 'Y' ), (int) $day->format( 'n' ), (int) $day->format( 'j' ) );
	$rel = 0 === $i ? __( 'امروز', 'medyar-nursing-services' ) : ( 1 === $i ? __( 'فردا', 'medyar-nursing-services' ) : ( 2 === $i ? __( 'پس‌فردا', 'medyar-nursing-services' ) : '' ) );
	$date_slots[] = array(
		'id'       => $day->format( 'Y-m-d' ),
		'jy'       => $j[0],
		'jm'       => $j[1],
		'jd'       => $j[2],
		'rel'      => $rel,
		'day_name' => MNS_Format::digits( (string) $j[2] ) . ' ' . $months[ $j[1] ],
	);
}

$price_min = isset( $service['price_min'] ) ? (int) $service['price_min'] : 0;
$price_max = isset( $service['price_max'] ) ? (int) $service['price_max'] : 0;
$svc_name  = isset( $service['name'] ) ? (string) $service['name'] : __( 'سرم‌تراپی در منزل', 'medyar-nursing-services' );
$svc_id    = isset( $service['id'] ) ? (string) $service['id'] : '1';

$cities_json = wp_json_encode( $cities );
$slots_json  = wp_json_encode( $date_slots );
$times_json  = wp_json_encode( $time_slots );
$avail_json  = wp_json_encode( $availability );
if ( ! is_string( $cities_json ) ) {
	$cities_json = '[]';
}
if ( ! is_string( $slots_json ) ) {
	$slots_json = '[]';
}
if ( ! is_string( $times_json ) ) {
	$times_json = '[]';
}
if ( ! is_string( $avail_json ) ) {
	$avail_json = '{}';
}

$default_mode = ( '' !== $prepatient && 'self' !== $prepatient ) ? 'saved' : 'self';
$default_patient = ( 'saved' === $default_mode ) ? $prepatient : 'self';

$track_sample = 'MD-48215';
$order_track_url = add_query_arg( 'mns_order', $track_sample, MNS_Pages::panel_url( 'patient', 'order-view' ) );

if ( MNS_Dev::is( 'success' ) ) {
	MNS_UI::boot_icons();
	MNS_UI::page_header(
		array(
			'title' => __( 'ثبت درخواست پرستار', 'medyar-nursing-services' ),
			'lead'  => '',
		)
	);
	?>
	<div class="mns-owizard mns-owizard--done-only">
		<p class="mns-owizard__svc-chip">
			<span class="mns-owizard__svc-chip-label"><?php echo esc_html__( 'خدمت:', 'medyar-nursing-services' ); ?></span>
			<strong><?php echo esc_html( $svc_name ); ?></strong>
		</p>
		<section class="mns-oconfirm mns-card" data-mns-order-confirm>
			<div class="mns-oconfirm__hero">
				<span class="mns-oconfirm__check" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<h2 class="mns-oconfirm__title"><?php echo esc_html__( 'درخواست شما ثبت شد', 'medyar-nursing-services' ); ?></h2>
				<p class="mns-oconfirm__lead"><?php echo esc_html__( 'پیامک تأیید برایتان ارسال شد. پس از تخصیص پرستار، نام پرستار هم با پیامک اطلاع داده می‌شود.', 'medyar-nursing-services' ); ?></p>
			</div>
			<div class="mns-oconfirm__track">
				<span class="mns-oconfirm__track-label"><?php echo esc_html( MNS_Copy::t( 'label.tracking_code' ) ); ?></span>
				<strong class="mns-oconfirm__track-code" dir="ltr" data-mns-confirm-code><?php echo esc_html( MNS_Format::digits( $track_sample ) ); ?></strong>
				<button type="button" class="mns-oconfirm__copy" data-mns-copy="<?php echo esc_attr( $track_sample ); ?>">
					<?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( MNS_Copy::t( 'action.copy' ) ); ?>
				</button>
			</div>
			<ol class="mns-oconfirm__timeline">
				<li class="is-done"><span><?php echo esc_html__( 'ثبت‌شده', 'medyar-nursing-services' ); ?></span><small><?php echo esc_html__( 'همین الان', 'medyar-nursing-services' ); ?></small></li>
				<li><span><?php echo esc_html__( 'تخصیص‌یافته', 'medyar-nursing-services' ); ?></span><small><?php echo esc_html__( 'با پیامک اطلاع می‌دهیم', 'medyar-nursing-services' ); ?></small></li>
				<li><span><?php echo esc_html__( 'در حال انجام', 'medyar-nursing-services' ); ?></span></li>
				<li><span><?php echo esc_html__( 'تکمیل‌شده', 'medyar-nursing-services' ); ?></span></li>
			</ol>
			<div class="mns-oconfirm__mini" data-mns-confirm-mini></div>
			<div class="mns-oconfirm__actions">
				<a class="mns-submit-btn" href="<?php echo esc_url( $order_track_url ); ?>"><?php echo esc_html__( 'پیگیری سفارش', 'medyar-nursing-services' ); ?></a>
				<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
			</div>
		</section>
	</div>
	<?php
	return;
}

if ( MNS_Dev::is( 'no_nurse' ) ) {
	MNS_UI::page_header(
		array(
			'title' => __( 'پرستار آزاد نداریم', 'medyar-nursing-services' ),
			'lead'  => __( 'برای این زمان و منطقه فعلاً پرستار آزاد نیست.', 'medyar-nursing-services' ),
		)
	);
	?>
	<section class="mns-card">
		<?php
		MNS_UI::alert(
			array(
				'tone' => 'warning',
				'text' => __( 'زمان دیگری انتخاب کنید یا با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ),
			)
		);
		?>
		<div class="mns-actions-row">
			<a class="mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( remove_query_arg( 'mns_state' ) ); ?>"><?php echo esc_html__( 'تغییر زمان', 'medyar-nursing-services' ); ?></a>
			<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
		</div>
	</section>
	<?php
	return;
}

MNS_UI::boot_icons();

MNS_UI::page_header(
	array(
		'title' => __( 'ثبت درخواست پرستار', 'medyar-nursing-services' ),
		'lead'  => '',
	)
);
?>

<div class="mns-owizard" data-mns-owizard>
	<p class="mns-owizard__svc-chip">
		<span class="mns-owizard__svc-chip-ico" aria-hidden="true"><?php echo mns_icon( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="mns-owizard__svc-chip-label"><?php echo esc_html__( 'خدمت:', 'medyar-nursing-services' ); ?></span>
		<strong data-mns-svc-name><?php echo esc_html( $svc_name ); ?></strong>
		<a class="mns-owizard__svc-change" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'dashboard' ) ); ?>"><?php echo esc_html__( 'تغییر', 'medyar-nursing-services' ); ?></a>
	</p>

	<div class="mns-owizard__stepper-wrap" data-mns-stepper>
		<p class="mns-stepper__compact" data-mns-step-compact aria-live="polite"></p>
		<div class="mns-stepper__progress" aria-hidden="true"><span class="mns-stepper__progress-bar" data-mns-step-progress style="width:25%"></span></div>
		<nav class="mns-owizard__stepper mns-stepper" aria-label="<?php echo esc_attr__( 'مراحل ثبت سفارش', 'medyar-nursing-services' ); ?>">
			<button type="button" class="mns-stepper__item mns-owizard__step is-current" data-mns-step-goto="1" data-mns-step-label="<?php echo esc_attr__( 'بیمار', 'medyar-nursing-services' ); ?>" aria-current="step">
				<span class="mns-owizard__step-ico" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-stepper__label"><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span>
			</button>
			<button type="button" class="mns-stepper__item mns-owizard__step" data-mns-step-goto="2" data-mns-step-label="<?php echo esc_attr__( 'زمان و مکان', 'medyar-nursing-services' ); ?>">
				<span class="mns-owizard__step-ico" aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-stepper__label"><?php echo esc_html__( 'زمان و مکان', 'medyar-nursing-services' ); ?></span>
			</button>
			<button type="button" class="mns-stepper__item mns-owizard__step" data-mns-step-goto="3" data-mns-step-label="<?php echo esc_attr__( 'جزئیات خدمت', 'medyar-nursing-services' ); ?>">
				<span class="mns-owizard__step-ico" aria-hidden="true"><?php echo mns_icon( 'file-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-stepper__label"><?php echo esc_html__( 'جزئیات خدمت', 'medyar-nursing-services' ); ?></span>
			</button>
			<button type="button" class="mns-stepper__item mns-owizard__step" data-mns-step-goto="4" data-mns-step-label="<?php echo esc_attr__( 'بررسی و ثبت', 'medyar-nursing-services' ); ?>">
				<span class="mns-owizard__step-ico" aria-hidden="true"><?php echo mns_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-stepper__label"><?php echo esc_html__( 'بررسی و ثبت', 'medyar-nursing-services' ); ?></span>
			</button>
		</nav>
	</div>

	<div class="mns-owizard__draft mns-notice mns-notice--info" data-mns-order-draft-banner hidden>
		<p><?php echo esc_html__( 'پیش‌نویس سفارش قبلی پیدا شد.', 'medyar-nursing-services' ); ?></p>
		<button type="button" class="mns-link-btn" data-mns-order-draft-restore><?php echo esc_html__( 'ادامه سفارش', 'medyar-nursing-services' ); ?></button>
		<button type="button" class="mns-link-btn" data-mns-order-draft-dismiss><?php echo esc_html__( 'شروع تازه', 'medyar-nursing-services' ); ?></button>
	</div>

	<div class="mns-owizard__layout">
		<div class="mns-owizard__main">
			<form
				class="mns-form mns-card mns-owizard__form"
				method="post"
				data-mns-mock="ok"
				data-mns-order-form
				data-mns-cities="<?php echo esc_attr( $cities_json ); ?>"
				data-mns-date-slots="<?php echo esc_attr( $slots_json ); ?>"
				data-mns-time-slots="<?php echo esc_attr( $times_json ); ?>"
				data-mns-availability="<?php echo esc_attr( $avail_json ); ?>"
				data-mns-prepatient="<?php echo esc_attr( $prepatient ); ?>"
				data-mns-price-min="<?php echo esc_attr( (string) $price_min ); ?>"
				data-mns-price-max="<?php echo esc_attr( (string) $price_max ); ?>"
				data-mns-svc-label="<?php echo esc_attr( $svc_name ); ?>"
				data-mns-support-tel="<?php echo esc_attr( MNS_UI::support_tel_href() ); ?>"
			>
				<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>
				<input type="hidden" name="service_id" value="<?php echo esc_attr( $svc_id ); ?>" data-mns-service-id>
				<input type="hidden" name="patient_mode" value="<?php echo esc_attr( $default_mode ); ?>" data-mns-patient-mode>
				<input type="hidden" name="patient_id" value="<?php echo esc_attr( 'saved' === $default_mode ? $default_patient : '' ); ?>" data-mns-patient-id>
				<input type="hidden" name="new_age_group" value="" data-mns-age-group>
				<input type="hidden" name="new_patient_gender" value="" data-mns-patient-gender>
				<input type="hidden" name="preferred_date" value="" data-mns-date-value>
				<input type="hidden" name="preferred_time" value="" data-mns-time-value>
				<input type="hidden" name="nurse_gender" value="" data-mns-nurse-gender>
				<input type="hidden" name="serum_type" value="" data-mns-serum-type>
				<input type="hidden" name="vein_access" value="" data-mns-vein-access>
				<input type="hidden" name="allergy" value="" data-mns-allergy>
				<input type="hidden" name="serum_qty" value="2" data-mns-serum-qty>
				<input type="hidden" name="is_recurring" value="0" data-mns-is-recurring>

				<section class="mns-step is-active" data-mns-step="1" aria-labelledby="mns-ow-step-1">
					<h2 id="mns-ow-step-1" class="mns-step__title"><?php echo esc_html__( 'سرم‌تراپی برای چه کسی است؟', 'medyar-nursing-services' ); ?></h2>
					<div class="mns-patient-cards" role="radiogroup" aria-label="<?php echo esc_attr__( 'انتخاب بیمار', 'medyar-nursing-services' ); ?>" data-mns-patient-cards>
						<button type="button" class="mns-patient-cards__item" role="radio" aria-checked="false" data-mns-patient-card="new">
							<span class="mns-patient-cards__ico" aria-hidden="true"><?php echo mns_icon( 'user-plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<strong><?php echo esc_html__( 'افزودن بیمار جدید', 'medyar-nursing-services' ); ?></strong>
						</button>
						<?php foreach ( $patients as $p ) : ?>
							<?php
							if ( 'self' === ( $p['id'] ?? '' ) ) {
								continue;
							}
							$pid   = (string) ( $p['id'] ?? '' );
							$on    = 'saved' === $default_mode && $default_patient === $pid;
							?>
							<button
								type="button"
								class="mns-patient-cards__item<?php echo $on ? ' is-selected' : ''; ?>"
								role="radio"
								aria-checked="<?php echo $on ? 'true' : 'false'; ?>"
								data-mns-patient-card="saved"
								data-value="<?php echo esc_attr( $pid ); ?>"
							>
								<span class="mns-patient-cards__ico" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<strong><?php echo esc_html( (string) ( $p['label'] ?? $p['name'] ?? '' ) ); ?></strong>
								<?php if ( ! empty( $p['meta'] ) ) : ?>
									<span><?php echo esc_html( (string) $p['meta'] ); ?></span>
								<?php endif; ?>
							</button>
						<?php endforeach; ?>
						<?php
						$self_on = 'self' === $default_mode;
						$self_p  = null;
						foreach ( $patients as $p ) {
							if ( 'self' === ( $p['id'] ?? '' ) ) {
								$self_p = $p;
								break;
							}
						}
						?>
						<button
							type="button"
							class="mns-patient-cards__item<?php echo $self_on ? ' is-selected' : ''; ?>"
							role="radio"
							aria-checked="<?php echo $self_on ? 'true' : 'false'; ?>"
							data-mns-patient-card="self"
							data-value="self"
						>
							<span class="mns-patient-cards__ico" aria-hidden="true"><?php echo mns_icon( 'user-check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<strong><?php echo esc_html( $self_p ? (string) ( $self_p['label'] ?? '' ) : __( 'خودم', 'medyar-nursing-services' ) ); ?></strong>
						</button>
					</div>

					<div class="mns-patient-cards__new<?php echo 'new' === $default_mode ? '' : ' is-hidden'; ?>" data-mns-new-patient-panel <?php echo 'new' === $default_mode ? '' : 'hidden'; ?>>
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'نام و نام خانوادگی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
							<span class="mns-field-block__control"><input type="text" name="new_patient_name" maxlength="100" autocomplete="name" placeholder="<?php echo esc_attr__( 'مثال: علی محمدی', 'medyar-nursing-services' ); ?>"></span>
						</label>
						<p class="mns-field-block__label"><?php echo esc_html__( 'گروه سنی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
						<div class="mns-chip-row" role="radiogroup" data-mns-age-chips>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="child"><?php echo esc_html__( 'کودک', 'medyar-nursing-services' ); ?></button>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="adult"><?php echo esc_html__( 'بزرگسال', 'medyar-nursing-services' ); ?></button>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="elder"><?php echo esc_html__( 'سالمند', 'medyar-nursing-services' ); ?></button>
						</div>
						<p class="mns-field-block__label"><?php echo esc_html__( 'جنسیت', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
						<div class="mns-chip-row" role="radiogroup" data-mns-patient-gender-chips>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="female"><?php echo esc_html__( 'خانم', 'medyar-nursing-services' ); ?></button>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="male"><?php echo esc_html__( 'آقا', 'medyar-nursing-services' ); ?></button>
						</div>
					</div>

					<label class="mns-field-block">
						<span class="mns-field-block__label"><?php echo esc_html__( 'شماره تماس', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control mns-field-block__control--phone">
							<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo mns_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<input type="text" name="phone" data-mns-iran-mobile required inputmode="tel" dir="ltr" placeholder="۰۹۱۲ ۱۲۳ ۴۵۶۷" autocomplete="tel" value="<?php echo esc_attr( MNS_Format::mobile( $phone ) ); ?>">
						</span>
					</label>
					<p class="mns-field-hint"><?php echo esc_html__( 'پرستار و پشتیبانی مدیار با همین شماره تماس می‌گیرند.', 'medyar-nursing-services' ); ?></p>

					<div class="mns-step__nav">
						<button type="button" class="mns-submit-btn" data-mns-step-next data-mns-step-next-label="1"><?php echo esc_html__( 'ادامه، زمان و مکان', 'medyar-nursing-services' ); ?></button>
					</div>
				</section>

				<section class="mns-step is-hidden" data-mns-step="2" hidden aria-hidden="true" aria-labelledby="mns-ow-step-2">
					<h2 id="mns-ow-step-2" class="mns-step__title"><?php echo esc_html__( 'کجا، کی و با کدام پرستار؟', 'medyar-nursing-services' ); ?></h2>
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
						<span class="mns-field-block__label"><?php echo esc_html__( 'آدرس', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control mns-field-block__control--area">
							<textarea name="address" rows="3" required maxlength="400" placeholder="<?php echo esc_attr__( 'خیابان، پلاک، واحد، نشانه', 'medyar-nursing-services' ); ?>"></textarea>
						</span>
					</label>

					<p class="mns-field-block__label"><?php echo esc_html__( 'تاریخ مراجعه', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-date-rail" data-mns-date-rail role="radiogroup" aria-label="<?php echo esc_attr__( 'تاریخ', 'medyar-nursing-services' ); ?>">
						<?php foreach ( $date_slots as $slot ) : ?>
							<button type="button" class="mns-date-rail__chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) $slot['id'] ); ?>" data-label="<?php echo esc_attr( ( isset( $slot['rel'] ) && $slot['rel'] ? $slot['rel'] . '، ' : '' ) . $slot['day_name'] ); ?>">
								<?php if ( ! empty( $slot['rel'] ) ) : ?>
									<strong><?php echo esc_html( (string) $slot['rel'] ); ?></strong>
								<?php endif; ?>
								<span><?php echo esc_html( (string) $slot['day_name'] ); ?></span>
							</button>
						<?php endforeach; ?>
						<button type="button" class="mns-date-rail__chip mns-date-rail__chip--alt" data-mns-date-other>
							<span aria-hidden="true"><?php echo mns_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html__( 'انتخاب تاریخ دیگر', 'medyar-nursing-services' ); ?></span>
						</button>
					</div>
					<label class="mns-field-block is-hidden" data-mns-custom-date-wrap hidden>
						<span class="mns-field-block__label"><?php echo esc_html__( 'تاریخ شمسی', 'medyar-nursing-services' ); ?></span>
						<span class="mns-field-block__control"><input type="text" data-mns-custom-date placeholder="۱۴۰۳/۰۸/۲۰" autocomplete="off"></span>
					</label>

					<p class="mns-field-block__label"><?php echo esc_html__( 'بازه زمانی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-slot-chips" data-mns-slot-chips role="radiogroup">
						<?php foreach ( $time_slots as $t ) : ?>
							<button type="button" class="mns-slot-chips__item" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) ( $t['id'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $t['label'] ?? '' ) ); ?></button>
						<?php endforeach; ?>
					</div>

					<p class="mns-field-block__label"><?php echo esc_html__( 'جنسیت پرستار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-nurse-gender" role="radiogroup" data-mns-nurse-gender-cards>
						<button type="button" class="mns-nurse-gender__card" role="radio" aria-checked="false" data-value="female">
							<span class="mns-nurse-gender__ico" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<strong><?php echo esc_html__( 'پرستار خانم', 'medyar-nursing-services' ); ?></strong>
						</button>
						<button type="button" class="mns-nurse-gender__card" role="radio" aria-checked="false" data-value="male">
							<span class="mns-nurse-gender__ico" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<strong><?php echo esc_html__( 'پرستار آقا', 'medyar-nursing-services' ); ?></strong>
						</button>
					</div>
					<p class="mns-field-hint"><?php echo esc_html__( 'این انتخاب قطعی است و فقط پرستاری با همین جنسیت اعزام می‌شود.', 'medyar-nursing-services' ); ?></p>

					<div class="mns-avail" data-mns-avail hidden></div>

					<div class="mns-step__nav">
						<button type="button" class="mns-btn-outline" data-mns-step-prev><?php echo esc_html__( 'بازگشت', 'medyar-nursing-services' ); ?></button>
						<button type="button" class="mns-submit-btn" data-mns-step-next data-mns-step-2-next><?php echo esc_html__( 'ادامه، جزئیات خدمت', 'medyar-nursing-services' ); ?></button>
					</div>
				</section>

				<section class="mns-step is-hidden" data-mns-step="3" hidden aria-hidden="true" aria-labelledby="mns-ow-step-3">
					<h2 id="mns-ow-step-3" class="mns-step__title"><?php echo esc_html__( 'اطلاعاتی که پرستار برای سرم‌تراپی لازم دارد', 'medyar-nursing-services' ); ?></h2>
					<?php
					$serum_types = isset( $fields_cfg['serum_types'] ) && is_array( $fields_cfg['serum_types'] ) ? $fields_cfg['serum_types'] : array();
					$vein_opts   = isset( $fields_cfg['vein_access'] ) && is_array( $fields_cfg['vein_access'] ) ? $fields_cfg['vein_access'] : array();
					$allergy_opts = isset( $fields_cfg['allergy'] ) && is_array( $fields_cfg['allergy'] ) ? $fields_cfg['allergy'] : array();
					?>
					<p class="mns-field-block__label"><?php echo esc_html__( 'نوع سرم', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-chip-row" role="radiogroup" data-mns-serum-chips>
						<?php foreach ( $serum_types as $st ) : ?>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) $st ); ?>"><?php echo esc_html( (string) $st ); ?></button>
						<?php endforeach; ?>
					</div>

					<p class="mns-field-block__label"><?php echo esc_html__( 'تعداد سرم', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-qty" data-mns-qty>
						<button type="button" class="mns-qty__btn" data-mns-qty-minus aria-label="<?php echo esc_attr__( 'کاهش', 'medyar-nursing-services' ); ?>">−</button>
						<span class="mns-qty__val" data-mns-qty-display><?php echo esc_html( MNS_Format::digits( '2' ) ); ?></span>
						<button type="button" class="mns-qty__btn" data-mns-qty-plus aria-label="<?php echo esc_attr__( 'افزایش', 'medyar-nursing-services' ); ?>">+</button>
					</div>

					<p class="mns-field-block__label"><?php echo esc_html__( 'دسترسی رگ', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-chip-row" role="radiogroup" data-mns-vein-chips>
						<?php foreach ( $vein_opts as $vo ) : ?>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) ( $vo['value'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $vo['label'] ?? '' ) ); ?></button>
						<?php endforeach; ?>
					</div>

					<p class="mns-field-block__label"><?php echo esc_html__( 'سابقه حساسیت دارویی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-chip-row" role="radiogroup" data-mns-allergy-chips>
						<?php foreach ( $allergy_opts as $ao ) : ?>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) ( $ao['value'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $ao['label'] ?? '' ) ); ?></button>
						<?php endforeach; ?>
					</div>

					<div class="mns-upload-zone" data-mns-upload-zone>
						<span class="mns-upload-zone__ico" aria-hidden="true"><?php echo mns_icon( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<p class="mns-upload-zone__title"><?php echo esc_html__( 'عکس نسخه را اضافه کنید', 'medyar-nursing-services' ); ?> <span class="mns-upload-zone__opt"><?php echo esc_html__( 'اختیاری', 'medyar-nursing-services' ); ?></span></p>
						<p class="mns-upload-zone__hint"><?php echo esc_html__( 'اگر نسخه ندارید مشکلی نیست؛ پرستار هماهنگی لازم را انجام می‌دهد.', 'medyar-nursing-services' ); ?></p>
						<label class="mns-outline-btn mns-upload-zone__btn">
							<?php echo esc_html__( 'گرفتن عکس یا انتخاب فایل', 'medyar-nursing-services' ); ?>
							<input type="file" name="prescription" accept="image/*" class="screen-reader-text" data-mns-prescription-file>
						</label>
					</div>

					<label class="mns-field-block">
						<span class="mns-field-block__label"><?php echo esc_html__( 'توضیحات تکمیلی', 'medyar-nursing-services' ); ?> <span class="mns-field-block__opt"><?php echo esc_html__( 'اختیاری', 'medyar-nursing-services' ); ?></span></span>
						<span class="mns-field-block__control mns-field-block__control--area">
							<textarea name="notes" rows="3" maxlength="400" placeholder="<?php echo esc_attr__( 'مثلاً طبقه ۴ بدون آسانسور…', 'medyar-nursing-services' ); ?>"></textarea>
						</span>
					</label>

					<div class="mns-step__nav">
						<button type="button" class="mns-btn-outline" data-mns-step-prev><?php echo esc_html__( 'بازگشت', 'medyar-nursing-services' ); ?></button>
						<button type="button" class="mns-submit-btn" data-mns-step-next><?php echo esc_html__( 'ادامه، بررسی و ثبت', 'medyar-nursing-services' ); ?></button>
					</div>
				</section>

				<section class="mns-step is-hidden" data-mns-step="4" hidden aria-hidden="true" aria-labelledby="mns-ow-step-4">
					<h2 id="mns-ow-step-4" class="mns-step__title"><?php echo esc_html__( 'بررسی نهایی و ثبت', 'medyar-nursing-services' ); ?></h2>
					<div class="mns-review-edit" data-mns-review aria-live="polite"></div>

					<label class="mns-switch-row">
						<button type="button" class="mns-switch" data-mns-switch data-mns-recurring-switch aria-pressed="false" aria-label="<?php echo esc_attr__( 'تکرار خدمت', 'medyar-nursing-services' ); ?>"></button>
						<span><?php echo esc_html__( 'این خدمت تکرار شود', 'medyar-nursing-services' ); ?></span>
					</label>
					<div class="mns-form-row--2 is-hidden" data-mns-recurring-fields hidden>
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'تناوب', 'medyar-nursing-services' ); ?></span>
							<span class="mns-field-block__control mns-field-block__control--select">
								<select class="mns-select-native" name="recurring_pattern" data-mns-select>
									<option value="weekly"><?php echo esc_html__( 'هفتگی', 'medyar-nursing-services' ); ?></option>
									<option value="every_other"><?php echo esc_html__( 'یک‌روز در میان', 'medyar-nursing-services' ); ?></option>
									<option value="daily"><?php echo esc_html__( 'روزانه', 'medyar-nursing-services' ); ?></option>
								</select>
								<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
							</span>
						</label>
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'تاریخ پایان', 'medyar-nursing-services' ); ?></span>
							<span class="mns-field-block__control"><input type="text" name="recurring_until" placeholder="۱۴۰۳/۱۰/۰۱" autocomplete="off"></span>
						</label>
					</div>

					<div class="mns-otp">
						<p class="mns-otp__title"><?php echo esc_html__( 'شماره را با کد پیامکی تأیید کنید', 'medyar-nursing-services' ); ?></p>
						<p class="mns-otp__sent"><?php echo esc_html__( 'ارسال به', 'medyar-nursing-services' ); ?> <strong data-mns-otp-phone dir="ltr"></strong></p>
						<div class="mns-otp__boxes" data-mns-otp-boxes>
							<?php for ( $d = 0; $d < 4; $d++ ) : ?>
								<input type="text" class="mns-otp__digit" inputmode="numeric" maxlength="1" pattern="[0-9۰-۹]" autocomplete="one-time-code" data-mns-otp-digit aria-label="<?php echo esc_attr( sprintf( /* translators: %d: digit index */ __( 'رقم %d', 'medyar-nursing-services' ), $d + 1 ) ); ?>">
							<?php endfor; ?>
						</div>
					</div>

					<p class="mns-field-hint"><?php echo esc_html__( 'پرداخت بعد از انجام خدمت و از طریق لینک پیامکی است. مبلغ نهایی بر اساس خدمات انجام‌شده محاسبه می‌شود.', 'medyar-nursing-services' ); ?></p>

					<div class="mns-step__nav">
						<button type="button" class="mns-btn-outline" data-mns-step-prev><?php echo esc_html__( 'بازگشت', 'medyar-nursing-services' ); ?></button>
						<button type="submit" class="mns-submit-btn" data-mns-order-submit><?php echo esc_html__( 'ثبت درخواست', 'medyar-nursing-services' ); ?></button>
					</div>
				</section>
			</form>

			<section class="mns-oconfirm mns-card" data-mns-order-confirm hidden>
				<div class="mns-oconfirm__hero">
					<span class="mns-oconfirm__check" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h2 class="mns-oconfirm__title"><?php echo esc_html__( 'درخواست شما ثبت شد', 'medyar-nursing-services' ); ?></h2>
					<p class="mns-oconfirm__lead"><?php echo esc_html__( 'پیامک تأیید برایتان ارسال شد. پس از تخصیص پرستار، نام پرستار هم با پیامک اطلاع داده می‌شود.', 'medyar-nursing-services' ); ?></p>
				</div>
				<div class="mns-oconfirm__track">
					<span class="mns-oconfirm__track-label"><?php echo esc_html( MNS_Copy::t( 'label.tracking_code' ) ); ?></span>
					<strong class="mns-oconfirm__track-code" dir="ltr" data-mns-confirm-code></strong>
					<button type="button" class="mns-oconfirm__copy" data-mns-confirm-copy>
						<?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo esc_html( MNS_Copy::t( 'action.copy' ) ); ?>
					</button>
				</div>
				<ol class="mns-oconfirm__timeline">
					<li class="is-done"><span><?php echo esc_html__( 'ثبت‌شده', 'medyar-nursing-services' ); ?></span><small><?php echo esc_html__( 'همین الان', 'medyar-nursing-services' ); ?></small></li>
					<li><span><?php echo esc_html__( 'تخصیص‌یافته', 'medyar-nursing-services' ); ?></span><small><?php echo esc_html__( 'با پیامک اطلاع می‌دهیم', 'medyar-nursing-services' ); ?></small></li>
					<li><span><?php echo esc_html__( 'در حال انجام', 'medyar-nursing-services' ); ?></span></li>
					<li><span><?php echo esc_html__( 'تکمیل‌شده', 'medyar-nursing-services' ); ?></span></li>
				</ol>
				<div class="mns-oconfirm__mini" data-mns-confirm-mini></div>
				<div class="mns-oconfirm__actions">
					<a class="mns-submit-btn" data-mns-confirm-track href="<?php echo esc_url( $order_track_url ); ?>"><?php echo esc_html__( 'پیگیری سفارش', 'medyar-nursing-services' ); ?></a>
					<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
				</div>
			</section>
		</div>

		<aside class="mns-osum" data-mns-osum aria-live="polite">
			<div class="mns-osum__card mns-osum__card--primary">
				<span class="mns-osum__ico" aria-hidden="true"><?php echo mns_icon( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<h3 class="mns-osum__svc"><?php echo esc_html( $svc_name ); ?></h3>
				<p class="mns-osum__price" data-mns-sum-price>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: min price, 2: max price */
							__( '%1$s تا %2$s', 'medyar-nursing-services' ),
							MNS_Format::number( $price_min ),
							MNS_Format::toman( $price_max )
						)
					);
					?>
				</p>
				<ul class="mns-osum__list">
					<li><span class="mns-osum__row-ico" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span data-mns-sum-patient><?php echo esc_html__( 'در مرحله بعد', 'medyar-nursing-services' ); ?></span></li>
					<li><span class="mns-osum__row-ico" aria-hidden="true"><?php echo mns_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span data-mns-sum-time><?php echo esc_html__( 'در مرحله بعد', 'medyar-nursing-services' ); ?></span></li>
					<li><span class="mns-osum__row-ico" aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span data-mns-sum-place><?php echo esc_html__( 'در مرحله بعد', 'medyar-nursing-services' ); ?></span></li>
					<li><span class="mns-osum__row-ico" aria-hidden="true"><?php echo mns_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span data-mns-sum-nurse><?php echo esc_html__( 'در مرحله بعد', 'medyar-nursing-services' ); ?></span></li>
					<li class="is-hidden" hidden data-mns-sum-detail-row><span class="mns-osum__row-ico" aria-hidden="true"><?php echo mns_icon( 'file-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span data-mns-sum-detail></span></li>
				</ul>
				<p class="mns-osum__pay-note"><?php echo esc_html__( 'پرداخت پس از انجام خدمت یا از طریق لینک پیامکی.', 'medyar-nursing-services' ); ?></p>
			</div>
			<div class="mns-osum__card mns-osum__card--soft is-hidden" data-mns-sum-price-factors hidden>
				<ul class="mns-osum__factors">
					<li><?php echo esc_html__( 'تعداد و نوع سرم', 'medyar-nursing-services' ); ?></li>
					<li><?php echo esc_html__( 'فاصله تا آدرس', 'medyar-nursing-services' ); ?></li>
					<li><?php echo esc_html__( 'زمان اعزام', 'medyar-nursing-services' ); ?></li>
				</ul>
			</div>
			<div class="mns-osum__how mns-how">
				<h3 class="mns-how__title"><?php echo esc_html__( 'بعد از ثبت چه می‌شود؟', 'medyar-nursing-services' ); ?></h3>
				<ol class="mns-how__steps">
					<li><span class="mns-how__num">۱</span><span><?php echo esc_html__( 'ثبت درخواست و ارسال کد پیگیری با پیامک', 'medyar-nursing-services' ); ?></span></li>
					<li><span class="mns-how__num">۲</span><span><?php echo esc_html__( 'تخصیص پرستار و اطلاع نام با پیامک', 'medyar-nursing-services' ); ?></span></li>
					<li><span class="mns-how__num">۳</span><span><?php echo esc_html__( 'انجام خدمت و پرداخت مبلغ نهایی', 'medyar-nursing-services' ); ?></span></li>
				</ol>
			</div>
		</aside>
	</div>

	<div class="mns-owizard__mob-bar" data-mns-owizard-mob-bar>
		<div class="mns-owizard__mob-bar-meta">
			<strong data-mns-mob-svc><?php echo esc_html( $svc_name ); ?></strong>
			<span data-mns-mob-price><?php echo esc_html( MNS_Format::number( $price_min ) . ' – ' . MNS_Format::number( $price_max ) ); ?></span>
		</div>
		<button type="button" class="mns-submit-btn mns-owizard__mob-bar-btn" data-mns-mob-continue><?php echo esc_html__( 'ادامه', 'medyar-nursing-services' ); ?></button>
	</div>
</div>
