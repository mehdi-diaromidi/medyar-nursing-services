<?php
/**
 * Patient new order — 4-step wizard + confirmation (static) — desgin/png/ثبت سفارش/*.png + desgin/ui doc/ثبت سفارش.txt.
 *
 * Markup is data-attribute driven (see assets/js/public.js → initOrderForm); styling lives in the
 * "Order wizard (new-order)" block at the end of assets/css/public.css.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data         = MNS_Static_Fixtures::get( 'patient/new_order' );
$service      = isset( $data['service'] ) && is_array( $data['service'] ) ? $data['service'] : array();
$patients     = isset( $data['patients'] ) && is_array( $data['patients'] ) ? $data['patients'] : array();
$cities       = isset( $data['cities'] ) && is_array( $data['cities'] ) ? $data['cities'] : array();
$time_slots   = isset( $data['time_slots'] ) && is_array( $data['time_slots'] ) ? $data['time_slots'] : array();
$fields_cfg   = isset( $data['service_fields'] ) && is_array( $data['service_fields'] ) ? $data['service_fields'] : array();
$availability = isset( $data['availability'] ) && is_array( $data['availability'] ) ? $data['availability'] : array();
$phone        = isset( $data['default_phone'] ) ? (string) $data['default_phone'] : '';
$prepatient   = isset( $_GET['mns_patient'] ) ? sanitize_text_field( wp_unslash( $_GET['mns_patient'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$tz_now     = new DateTimeImmutable( 'now', wp_timezone() );
$months     = MNS_Format::month_names();
$weekdays   = MNS_Format::weekday_names();
$date_slots = array();
for ( $i = 0; $i < 7; $i++ ) {
	$day  = $tz_now->modify( '+' . $i . ' days' );
	$j    = MNS_Format::g2j( (int) $day->format( 'Y' ), (int) $day->format( 'n' ), (int) $day->format( 'j' ) );
	$wd   = $weekdays[ ( (int) $day->format( 'w' ) + 1 ) % 7 ];
	$rel  = 0 === $i ? __( 'امروز', 'medyar-nursing-services' ) : ( 1 === $i ? __( 'فردا', 'medyar-nursing-services' ) : $wd );
	$date_slots[] = array(
		'id'    => $day->format( 'Y-m-d' ),
		'rel'   => $rel,
		'num'   => MNS_Format::digits( (string) $j[2] ),
		'month' => $months[ $j[1] ],
		'label' => ( $i < 2 ? $rel : $wd ) . '، ' . MNS_Format::digits( (string) $j[2] ) . ' ' . $months[ $j[1] ],
	);
}

$price_min = isset( $service['price_min'] ) ? (int) $service['price_min'] : 0;
$price_max = isset( $service['price_max'] ) ? (int) $service['price_max'] : 0;
$svc_name  = isset( $service['name'] ) ? (string) $service['name'] : __( 'سرم‌تراپی در منزل', 'medyar-nursing-services' );
$svc_id    = isset( $service['id'] ) ? (string) $service['id'] : '1';
$price_txt = MNS_Format::number( $price_min ) . ' ' . __( 'تا', 'medyar-nursing-services' ) . ' ' . MNS_Format::number( $price_max );

$enc = static function ( $v, string $fallback ): string {
	$out = wp_json_encode( $v );
	return is_string( $out ) ? $out : $fallback;
};
$cities_json = $enc( $cities, '[]' );
$slots_json  = $enc( $date_slots, '[]' );
$times_json  = $enc( $time_slots, '[]' );
$avail_json  = $enc( $availability, '{}' );

$default_mode    = ( '' !== $prepatient && 'self' !== $prepatient ) ? 'saved' : 'self';
$default_patient = ( 'saved' === $default_mode ) ? $prepatient : 'self';

$track_sample    = 'MD-48215';
$order_track_url = add_query_arg( 'mns_order', $track_sample, MNS_Pages::panel_url( 'patient', 'order-view' ) );

$leads = array(
	1      => __( 'چند مرحله کوتاه تا اعزام پرستار به منزل. بیمار، آدرس و زمان را مشخص کنید؛ پیش از ثبت، دسترسی پرستار برای زمان و منطقه شما بررسی می‌شود.', 'medyar-nursing-services' ),
	2      => __( 'جزئیات خدمت را کامل کنید تا پرستار با وسایل درست اعزام شود.', 'medyar-nursing-services' ),
	3      => __( 'قبل از ثبت، همه اطلاعات را یک بار مرور کنید.', 'medyar-nursing-services' ),
	'done' => __( 'درخواست شما ثبت شد و پیگیری آن از همین‌جا ممکن است.', 'medyar-nursing-services' ),
);

$ico = static function ( string $name, string $class = '' ): string {
	return mns_icon( $name, $class );
};

/**
 * Header: title + lead on the start side, service chip on the end side.
 */
$render_head = static function ( string $lead ) use ( $ico, $svc_name ): void {
	?>
	<header class="mns-ow-head">
		<div class="mns-ow-head__intro">
			<h1 class="mns-ow-head__title">
				<span class="mns-ow-head__ico" aria-hidden="true"><?php echo $ico( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'ثبت درخواست پرستار', 'medyar-nursing-services' ); ?></span>
			</h1>
			<p class="mns-ow-head__lead" data-mns-ow-lead><?php echo esc_html( $lead ); ?></p>
		</div>
		<p class="mns-owizard__svc-chip">
			<span class="mns-owizard__svc-chip-ico" aria-hidden="true"><?php echo $ico( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="mns-owizard__svc-chip-label"><?php echo esc_html__( 'خدمت:', 'medyar-nursing-services' ); ?></span>
			<strong data-mns-svc-name><?php echo esc_html( $svc_name ); ?></strong>
			<a class="mns-owizard__svc-change" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'dashboard' ) ); ?>"><?php echo esc_html__( 'تغییر', 'medyar-nursing-services' ); ?></a>
		</p>
	</header>
	<?php
};

/**
 * Stepper. $current: 1..3, or 4 when everything is done.
 */
$render_stepper = static function ( int $current ) use ( $ico ): void {
	$steps = array(
		1 => array( 'user', __( 'بیمار و زمان', 'medyar-nursing-services' ) ),
		2 => array( 'file-text', __( 'جزئیات خدمت', 'medyar-nursing-services' ) ),
		3 => array( 'shield', __( 'بررسی و ثبت', 'medyar-nursing-services' ) ),
	);

	?>
	<div class="mns-owizard__stepper-wrap" data-mns-stepper>
		<nav class="mns-owizard__stepper mns-stepper" aria-label="<?php echo esc_attr__( 'مراحل ثبت سفارش', 'medyar-nursing-services' ); ?>">
			<?php foreach ( $steps as $n => $s ) : ?>
				<?php
				$cls = 'mns-stepper__item mns-owizard__step';
				if ( $n === $current ) {
					$cls .= ' is-current';
				} elseif ( $n < $current ) {
					$cls .= ' is-done';
				}
				?>
				<button type="button" class="<?php echo esc_attr( $cls ); ?>" data-mns-step-goto="<?php echo esc_attr( (string) $n ); ?>" data-mns-step-label="<?php echo esc_attr( $s[1] ); ?>"<?php echo $n === $current ? ' aria-current="step"' : ''; ?>>
					<span class="mns-owizard__step-ico" aria-hidden="true">
						<span class="mns-owizard__step-ico-main"><?php echo $ico( $s[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-owizard__step-ico-check"><?php echo $ico( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
					<span class="mns-stepper__label"><?php echo esc_html( $s[1] ); ?></span>
				</button>
			<?php endforeach; ?>
		</nav>
	</div>
	<?php
};

/**
 * Confirmation card (JS flow + ?mns_state=success dev state).
 */
$render_confirm = static function ( bool $hidden, string $code ) use ( $ico, $order_track_url ): void {
	?>
	<section class="mns-oconfirm" data-mns-order-confirm<?php echo $hidden ? ' hidden' : ''; ?>>
		<div class="mns-oconfirm__hero">
			<span class="mns-oconfirm__check" aria-hidden="true"><?php echo $ico( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h2 class="mns-oconfirm__title"><?php echo esc_html__( 'درخواست شما ثبت شد', 'medyar-nursing-services' ); ?></h2>
			<p class="mns-oconfirm__lead"><?php echo esc_html__( 'پیامک تأیید برایتان ارسال شد. نام پرستار تخصیص‌یافته هم با پیامک اطلاع داده می‌شود.', 'medyar-nursing-services' ); ?></p>
		</div>
		<div class="mns-oconfirm__track">
			<div class="mns-oconfirm__track-text">
				<span class="mns-oconfirm__track-label"><?php echo esc_html( MNS_Copy::t( 'label.tracking_code' ) ); ?></span>
				<strong class="mns-oconfirm__track-code" dir="ltr" data-mns-confirm-code><?php echo esc_html( $hidden ? '' : $code ); ?></strong>
			</div>
			<button type="button" class="mns-oconfirm__copy" <?php echo $hidden ? 'data-mns-confirm-copy' : 'data-mns-copy="' . esc_attr( $code ) . '"'; ?>>
				<?php echo $ico( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( MNS_Copy::t( 'action.copy' ) ); ?></span>
			</button>
		</div>
		<ol class="mns-oconfirm__timeline">
			<li class="is-done"><span class="mns-oconfirm__tl-ico" aria-hidden="true"><?php echo $ico( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="mns-oconfirm__tl-text"><strong><?php echo esc_html__( 'ثبت‌شده', 'medyar-nursing-services' ); ?></strong><small><?php echo esc_html__( 'همین حالا', 'medyar-nursing-services' ); ?></small></span></li>
			<li><span class="mns-oconfirm__tl-ico" aria-hidden="true"><?php echo $ico( 'user-plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="mns-oconfirm__tl-text"><strong><?php echo esc_html__( 'تخصیص‌یافته', 'medyar-nursing-services' ); ?></strong><small><?php echo esc_html__( 'با پیامک اطلاع می‌دهیم', 'medyar-nursing-services' ); ?></small></span></li>
			<li><span class="mns-oconfirm__tl-ico" aria-hidden="true"><?php echo $ico( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="mns-oconfirm__tl-text"><strong><?php echo esc_html__( 'در حال انجام', 'medyar-nursing-services' ); ?></strong></span></li>
			<li><span class="mns-oconfirm__tl-ico" aria-hidden="true"><?php echo $ico( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span class="mns-oconfirm__tl-text"><strong><?php echo esc_html__( 'تکمیل‌شده', 'medyar-nursing-services' ); ?></strong></span></li>
		</ol>
		<div class="mns-oconfirm__mini" data-mns-confirm-mini></div>
		<div class="mns-oconfirm__actions">
			<a class="mns-submit-btn" data-mns-confirm-track href="<?php echo esc_url( $order_track_url ); ?>">
				<span><?php echo esc_html__( 'پیگیری سفارش', 'medyar-nursing-services' ); ?></span>
				<span class="mns-btn-ico" aria-hidden="true"><?php echo $ico( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
			<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
		</div>
	</section>
	<?php
};

/* ---- Dev state: confirmation only ---- */
if ( MNS_Dev::is( 'success' ) ) {
	?>
	<div class="mns-owizard mns-owizard--done" data-mns-current-step="4">
		<?php $render_head( $leads['done'] ); ?>
		<?php $render_stepper( 4 ); ?>
		<div class="mns-owizard__done-card">
			<?php $render_confirm( false, $track_sample ); ?>
		</div>
	</div>
	<?php
	return;
}

/* ---- Dev state: no nurse ---- */
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

$serum_types  = isset( $fields_cfg['serum_types'] ) && is_array( $fields_cfg['serum_types'] ) ? $fields_cfg['serum_types'] : array();
$vein_opts    = isset( $fields_cfg['vein_access'] ) && is_array( $fields_cfg['vein_access'] ) ? $fields_cfg['vein_access'] : array();
$allergy_opts = isset( $fields_cfg['allergy'] ) && is_array( $fields_cfg['allergy'] ) ? $fields_cfg['allergy'] : array();

$self_p  = null;
$saved_p = array();
foreach ( $patients as $p ) {
	if ( 'self' === ( $p['id'] ?? '' ) ) {
		$self_p = $p;
	} else {
		$saved_p[] = $p;
	}
}
// Nobody on file yet: skip the "who is it for?" confirmation and open the add-patient form directly.
$has_known = null !== $self_p || ! empty( $saved_p );
$pick_name = '';
$pick_tag  = '';
if ( 'saved' === $default_mode ) {
	foreach ( $saved_p as $p ) {
		if ( (string) ( $p['id'] ?? '' ) === $default_patient ) {
			$pick_name = (string) ( $p['name'] ?? $p['label'] ?? '' );
		}
	}
	if ( '' === $pick_name ) { // Unknown ?mns_patient value: fall back to the account holder.
		$default_mode    = 'self';
		$default_patient = 'self';
	}
}
if ( 'self' === $default_mode && $self_p ) {
	$pick_name = (string) ( $self_p['name'] ?? $self_p['meta'] ?? '' );
	$pick_tag  = __( 'خودم', 'medyar-nursing-services' );
}
if ( ! $has_known ) {
	$default_mode    = 'new';
	$default_patient = '';
}
$self_on = 'self' === $default_mode;
$new_on  = 'new' === $default_mode;
?>

<div class="mns-owizard" data-mns-owizard data-mns-current-step="1" data-mns-leads="<?php echo esc_attr( $enc( $leads, '{}' ) ); ?>">
	<?php $render_head( $leads[1] ); ?>
	<?php $render_stepper( 1 ); ?>

	<div class="mns-owizard__draft mns-notice mns-notice--info" data-mns-order-draft-banner hidden>
		<p><?php echo esc_html__( 'پیش‌نویس سفارش قبلی پیدا شد.', 'medyar-nursing-services' ); ?></p>
		<button type="button" class="mns-link-btn" data-mns-order-draft-restore><?php echo esc_html__( 'ادامه سفارش', 'medyar-nursing-services' ); ?></button>
		<button type="button" class="mns-link-btn" data-mns-order-draft-dismiss><?php echo esc_html__( 'شروع تازه', 'medyar-nursing-services' ); ?></button>
	</div>

	<div class="mns-owizard__layout">
		<div class="mns-owizard__main">
			<form
				class="mns-form mns-owizard__form"
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

				<?php /* ============ Step 1 — patient, place, time, nurse ============ */ ?>
				<section class="mns-step is-active" data-mns-step="1" aria-labelledby="mns-ow-step-1">
					<header class="mns-step__head">
						<span class="mns-step__title-ico" aria-hidden="true"><?php echo $ico( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div>
							<h2 id="mns-ow-step-1" class="mns-step__title"><?php echo esc_html__( 'سرم‌تراپی برای چه کسی، کجا و کی؟', 'medyar-nursing-services' ); ?></h2>
							<p class="mns-step__sub"><?php echo esc_html__( 'بیمار، آدرس و زمان را مشخص کنید؛ دسترسی پرستار همان لحظه بررسی می‌شود.', 'medyar-nursing-services' ); ?></p>
						</div>
					</header>

					<h3 class="mns-step__group"><span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span></h3>
						<div class="mns-pick" data-mns-pick>
					<div class="mns-pick__summary-wrap" data-mns-pick-summary<?php echo $has_known ? '' : ' hidden'; ?>>
						<div class="mns-pick__summary">
							<span class="mns-pick__avatar" aria-hidden="true"><?php echo $ico( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mns-pick__text">
								<strong class="mns-pick__name"><span data-mns-pick-name><?php echo esc_html( $pick_name ); ?></span> <span class="mns-pick__tag" data-mns-pick-tag><?php echo '' !== $pick_tag ? esc_html( '· ' . $pick_tag ) : ''; ?></span></strong>
								<span class="mns-pick__phone" dir="ltr" data-mns-pick-phone><?php echo esc_html( MNS_Format::mobile( $phone ) ); ?></span>
							</span>
							<button type="button" class="mns-btn-outline mns-pick__change" data-mns-pick-change aria-expanded="false" aria-controls="mns-pick-edit">
								<span data-mns-pick-change-label><?php echo esc_html__( 'تغییر', 'medyar-nursing-services' ); ?></span>
									<?php echo $ico( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</button>
						</div>
						<p class="mns-field-hint"><span aria-hidden="true"><?php echo $ico( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'پرستار با همین شماره با شما تماس می‌گیرد.', 'medyar-nursing-services' ); ?></p>
					</div>

					<div class="mns-collapse mns-pick__edit<?php echo $has_known ? '' : ' is-open'; ?>" id="mns-pick-edit" data-mns-pick-edit>
						<div class="mns-collapse__inner">
					<div class="mns-patient-cards" role="radiogroup" aria-label="<?php echo esc_attr__( 'انتخاب بیمار', 'medyar-nursing-services' ); ?>" data-mns-patient-cards>
						<button type="button" class="mns-patient-cards__item<?php echo $self_on ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $self_on ? 'true' : 'false'; ?>" data-mns-patient-card="self" data-value="self">
							<span class="mns-patient-cards__ico" aria-hidden="true"><?php echo $ico( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<strong><?php echo esc_html( $self_p ? (string) ( $self_p['label'] ?? '' ) : __( 'خودم', 'medyar-nursing-services' ) ); ?></strong>
							<span class="mns-patient-cards__meta"><?php echo esc_html( $self_p ? (string) ( $self_p['meta'] ?? '' ) : '' ); ?></span>
						</button>
						<?php foreach ( $saved_p as $p ) : ?>
							<?php
							$pid = (string) ( $p['id'] ?? '' );
							$on  = 'saved' === $default_mode && $default_patient === $pid;
							?>
							<button type="button" class="mns-patient-cards__item<?php echo $on ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $on ? 'true' : 'false'; ?>" data-mns-patient-card="saved" data-value="<?php echo esc_attr( $pid ); ?>">
								<span class="mns-patient-cards__ico" aria-hidden="true"><?php echo $ico( 'users' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<strong><?php echo esc_html( (string) ( $p['label'] ?? $p['name'] ?? '' ) ); ?></strong>
								<span class="mns-patient-cards__meta"><?php echo esc_html( ! empty( $p['last_order'] ) ? sprintf( /* translators: %s: Jalali date */ __( 'آخرین سفارش: %s', 'medyar-nursing-services' ), $p['last_order'] ) : (string) ( $p['meta'] ?? '' ) ); ?></span>
							</button>
						<?php endforeach; ?>
						<button type="button" class="mns-patient-cards__item<?php echo $new_on ? ' is-selected' : ''; ?>" role="radio" aria-checked="<?php echo $new_on ? 'true' : 'false'; ?>" data-mns-patient-card="new">
							<span class="mns-patient-cards__ico" aria-hidden="true"><?php echo $ico( 'user-plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<strong><?php echo esc_html__( 'افزودن بیمار جدید', 'medyar-nursing-services' ); ?></strong>
							<span class="mns-patient-cards__meta"><?php echo esc_html__( 'بدون صفحه جدا', 'medyar-nursing-services' ); ?></span>
						</button>
					</div>

					<div class="mns-collapse<?php echo $new_on ? ' is-open' : ''; ?>" data-mns-new-collapse><div class="mns-collapse__inner">
					<div class="mns-patient-cards__new" data-mns-new-patient-panel>
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'نام و نام خانوادگی بیمار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
							<span class="mns-field-block__control">
								<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $ico( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<input type="text" name="new_patient_name" maxlength="100" autocomplete="name" placeholder="<?php echo esc_attr__( 'مثال: علی محمدی', 'medyar-nursing-services' ); ?>">
							</span>
						</label>
						<div class="mns-patient-cards__new-row">
							<div class="mns-field-group">
								<p class="mns-field-block__label"><?php echo esc_html__( 'گروه سنی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
								<div class="mns-chip-row" role="radiogroup" data-mns-age-chips>
									<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="child"><?php echo esc_html__( 'کودک', 'medyar-nursing-services' ); ?></button>
									<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="adult"><?php echo esc_html__( 'بزرگسال', 'medyar-nursing-services' ); ?></button>
									<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="elder"><?php echo esc_html__( 'سالمند', 'medyar-nursing-services' ); ?></button>
								</div>
							</div>
							<div class="mns-field-group">
								<p class="mns-field-block__label"><?php echo esc_html__( 'جنسیت بیمار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
								<div class="mns-chip-row" role="radiogroup" data-mns-patient-gender-chips>
									<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="female"><?php echo esc_html__( 'زن', 'medyar-nursing-services' ); ?></button>
									<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="male"><?php echo esc_html__( 'مرد', 'medyar-nursing-services' ); ?></button>
								</div>
							</div>
						</div>
					</div>

					</div></div>
					<div class="mns-ow-phone">
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'شماره تماس', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
							<span class="mns-field-block__control mns-field-block__control--phone">
								<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $ico( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<input type="text" name="phone" data-mns-iran-mobile required inputmode="tel" dir="ltr" placeholder="۰۹۱۲ ۱۲۳ ۴۵۶۷" autocomplete="tel" value="<?php echo esc_attr( MNS_Format::mobile( $phone ) ); ?>">
							</span>
						</label>
					</div>
					<p class="mns-field-hint"><span aria-hidden="true"><?php echo $ico( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'پرستار و ادمین با همین شماره با شما تماس می‌گیرند. شماره از حساب شما پر شده و قابل ویرایش است.', 'medyar-nursing-services' ); ?></p>
						</div>
					</div>
					</div>

					<h3 class="mns-step__group"><span><?php echo esc_html__( 'مکان و زمان', 'medyar-nursing-services' ); ?></span></h3>

					<div class="mns-form-row--2">
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'شهر', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
							<span class="mns-field-block__control mns-field-block__control--select">
								<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $ico( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<select class="mns-select-native" name="city_id" data-mns-select data-mns-city required>
									<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
									<?php foreach ( $cities as $c ) : ?>
										<option value="<?php echo esc_attr( (string) ( $c['id'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $c['name'] ?? '' ) ); ?></option>
									<?php endforeach; ?>
								</select>
								<span class="mns-field-block__chevron" aria-hidden="true"><?php echo $ico( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</span>
						</label>
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span><span class="mns-field-block__opt"><?php echo esc_html__( 'جستجو کنید', 'medyar-nursing-services' ); ?></span></span>
							<span class="mns-field-block__control mns-field-block__control--select">
								<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $ico( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<select class="mns-select-native" name="area_id" data-mns-select data-mns-area required>
									<option value=""><?php echo esc_html__( 'ابتدا شهر را انتخاب کنید', 'medyar-nursing-services' ); ?></option>
								</select>
								<span class="mns-field-block__chevron" aria-hidden="true"><?php echo $ico( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</span>
						</label>
					</div>

					<p class="mns-field-block__label"><?php echo esc_html__( 'تاریخ', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-date-rail" data-mns-date-rail role="radiogroup" aria-label="<?php echo esc_attr__( 'تاریخ', 'medyar-nursing-services' ); ?>">
						<?php foreach ( $date_slots as $slot ) : ?>
							<button type="button" class="mns-date-rail__chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) $slot['id'] ); ?>" data-label="<?php echo esc_attr( (string) $slot['label'] ); ?>">
								<small><?php echo esc_html( (string) $slot['rel'] ); ?></small>
								<strong><?php echo esc_html( (string) $slot['num'] ); ?></strong>
								<span><?php echo esc_html( (string) $slot['month'] ); ?></span>
							</button>
						<?php endforeach; ?>
						<button type="button" class="mns-date-rail__chip mns-date-rail__chip--alt" data-mns-date-other>
							<span class="mns-date-rail__alt-ico" aria-hidden="true"><?php echo $ico( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html__( 'تاریخ دیگر', 'medyar-nursing-services' ); ?></span>
						</button>
					</div>
					<label class="mns-field-block is-hidden" data-mns-custom-date-wrap hidden>
						<span class="mns-field-block__label"><?php echo esc_html__( 'تاریخ شمسی', 'medyar-nursing-services' ); ?></span>
						<span class="mns-field-block__control"><input type="text" data-mns-custom-date placeholder="۱۴۰۵/۰۸/۲۰" autocomplete="off"></span>
					</label>

					<p class="mns-field-block__label"><?php echo esc_html__( 'بازه ساعت', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-slot-chips" data-mns-slot-chips role="radiogroup">
						<?php foreach ( $time_slots as $t ) : ?>
							<button type="button" class="mns-slot-chips__item" role="radio" aria-checked="false"<?php echo ! empty( $t['disabled'] ) ? ' disabled aria-disabled="true" title="' . esc_attr__( 'پرستاری در این بازه موجود نیست', 'medyar-nursing-services' ) . '"' : ''; ?> data-value="<?php echo esc_attr( (string) ( $t['id'] ?? '' ) ); ?>"><span><?php echo esc_html( (string) ( $t['label'] ?? '' ) ); ?></span><?php echo ! empty( $t['disabled'] ) ? '<small class="mns-slot-chips__why">' . esc_html__( 'پرستار نیست', 'medyar-nursing-services' ) . '</small>' : ''; ?></button>
						<?php endforeach; ?>
					</div>

					<div class="mns-avail" data-mns-avail hidden></div>

					<p class="mns-field-block__label"><?php echo esc_html__( 'جنسیت پرستار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-nurse-gender" role="radiogroup" data-mns-nurse-gender-cards>
						<button type="button" class="mns-nurse-gender__card" role="radio" aria-checked="false" data-value="female">
							<span class="mns-nurse-gender__ico" aria-hidden="true"><?php echo $ico( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mns-nurse-gender__text"><strong><?php echo esc_html__( 'پرستار خانم', 'medyar-nursing-services' ); ?></strong><small><?php echo esc_html__( 'اعزام فقط با پرستار خانم', 'medyar-nursing-services' ); ?></small><em class="mns-nurse-gender__note" data-mns-gender-note hidden><?php echo esc_html__( 'فعلاً در این منطقه موجود نیست', 'medyar-nursing-services' ); ?></em></span>
						</button>
						<button type="button" class="mns-nurse-gender__card" role="radio" aria-checked="false" data-value="male">
							<span class="mns-nurse-gender__ico" aria-hidden="true"><?php echo $ico( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mns-nurse-gender__text"><strong><?php echo esc_html__( 'پرستار آقا', 'medyar-nursing-services' ); ?></strong><small><?php echo esc_html__( 'اعزام فقط با پرستار آقا', 'medyar-nursing-services' ); ?></small><em class="mns-nurse-gender__note" data-mns-gender-note hidden><?php echo esc_html__( 'فعلاً در این منطقه موجود نیست', 'medyar-nursing-services' ); ?></em></span>
						</button>
					</div>
					<p class="mns-field-hint mns-field-hint--warn"><span aria-hidden="true"><?php echo $ico( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'این انتخاب قطعی است و فقط پرستاری با همین جنسیت اعزام می‌شود.', 'medyar-nursing-services' ); ?></p>

					<div class="mns-avail" data-mns-avail-gender hidden></div>

					<label class="mns-field-block">
						<span class="mns-field-block__label"><?php echo esc_html__( 'آدرس دقیق', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control mns-field-block__control--area">
							<textarea name="address" rows="3" required maxlength="400" placeholder="<?php echo esc_attr__( 'خیابان، کوچه، پلاک، واحد', 'medyar-nursing-services' ); ?>"></textarea>
						</span>
					</label>
					<p class="mns-field-hint mns-field-hint--plain"><?php echo esc_html__( 'پلاک، واحد و یک نشانه بنویسید تا پرستار سریع‌تر پیدا کند.', 'medyar-nursing-services' ); ?></p>

					<div class="mns-step__nav">
						<button type="button" class="mns-submit-btn" data-mns-step-next data-mns-step-1-next>
							<span><?php echo esc_html__( 'ادامه، جزئیات خدمت', 'medyar-nursing-services' ); ?></span>
							<span class="mns-btn-ico" aria-hidden="true"><?php echo $ico( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</button>
					</div>
				</section>

				<?php /* ============ Step 2 — service details ============ */ ?>
				<section class="mns-step is-hidden" data-mns-step="2" hidden aria-hidden="true" aria-labelledby="mns-ow-step-2">
					<header class="mns-step__head">
						<span class="mns-step__title-ico" aria-hidden="true"><?php echo $ico( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div>
							<h2 id="mns-ow-step-2" class="mns-step__title"><?php echo esc_html__( 'اطلاعاتی که پرستار برای سرم‌تراپی لازم دارد', 'medyar-nursing-services' ); ?></h2>
							<p class="mns-step__sub"><?php echo esc_html__( 'فقط موارد ضروری پرسیده می‌شود. هر چه را نمی‌دانید می‌توانید خالی بگذارید.', 'medyar-nursing-services' ); ?></p>
						</div>
					</header>

					<p class="mns-field-block__label"><?php echo esc_html__( 'نوع سرم', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-chip-row mns-chip-row--check" role="radiogroup" data-mns-serum-chips>
						<?php foreach ( $serum_types as $st ) : ?>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) $st ); ?>"><?php echo esc_html( (string) $st ); ?></button>
						<?php endforeach; ?>
					</div>

					<div class="mns-ow-row2">
						<div class="mns-field-group">
							<p class="mns-field-block__label"><?php echo esc_html__( 'تعداد سرم', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
							<div class="mns-qty" data-mns-qty>
								<button type="button" class="mns-qty__btn" data-mns-qty-plus aria-label="<?php echo esc_attr__( 'افزایش', 'medyar-nursing-services' ); ?>"><?php echo $ico( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
								<span class="mns-qty__val" data-mns-qty-display><?php echo esc_html( MNS_Format::digits( '2' ) ); ?></span>
								<button type="button" class="mns-qty__btn mns-qty__btn--minus" data-mns-qty-minus aria-label="<?php echo esc_attr__( 'کاهش', 'medyar-nursing-services' ); ?>"><?php echo $ico( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							</div>
						</div>
						<div class="mns-field-group">
							<p class="mns-field-block__label"><?php echo esc_html__( 'دسترسی رگ', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
							<div class="mns-chip-row" role="radiogroup" data-mns-vein-chips>
								<?php foreach ( $vein_opts as $vo ) : ?>
									<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) ( $vo['value'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $vo['label'] ?? '' ) ); ?></button>
								<?php endforeach; ?>
							</div>
						</div>
					</div>

					<p class="mns-field-block__label"><?php echo esc_html__( 'سابقه حساسیت دارویی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></p>
					<div class="mns-chip-row" role="radiogroup" data-mns-allergy-chips>
						<?php foreach ( $allergy_opts as $ao ) : ?>
							<button type="button" class="mns-chip" role="radio" aria-checked="false" data-value="<?php echo esc_attr( (string) ( $ao['value'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $ao['label'] ?? '' ) ); ?></button>
						<?php endforeach; ?>
					</div>

					<p class="mns-field-block__label"><?php echo esc_html__( 'عکس نسخه پزشک', 'medyar-nursing-services' ); ?><span class="mns-field-block__opt"><?php echo esc_html__( 'اختیاری', 'medyar-nursing-services' ); ?></span></p>
					<div class="mns-upload-zone" data-mns-upload-zone>
						<span class="mns-upload-zone__ico" aria-hidden="true"><?php echo $ico( 'file-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div class="mns-upload-zone__text">
							<p class="mns-upload-zone__title"><?php echo esc_html__( 'عکس نسخه را اضافه کنید', 'medyar-nursing-services' ); ?></p>
							<p class="mns-upload-zone__hint"><?php echo esc_html__( 'اگر نسخه ندارید مشکلی نیست؛ پرستار هماهنگی لازم را انجام می‌دهد.', 'medyar-nursing-services' ); ?></p>
						</div>
						<label class="mns-upload-zone__btn">
							<?php echo $ico( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html__( 'گرفتن عکس یا انتخاب فایل', 'medyar-nursing-services' ); ?></span>
							<input type="file" name="prescription" accept="image/*" class="screen-reader-text" data-mns-prescription-file>
						</label>
					</div>

					<label class="mns-field-block">
						<span class="mns-field-block__label"><?php echo esc_html__( 'توضیحات تکمیلی', 'medyar-nursing-services' ); ?><span class="mns-field-block__opt"><?php echo esc_html__( 'اختیاری', 'medyar-nursing-services' ); ?></span></span>
						<span class="mns-field-block__control mns-field-block__control--area">
							<textarea name="notes" rows="4" maxlength="400" placeholder="<?php echo esc_attr__( 'مثلاً: طبقه چهار است و آسانسور ندارد.', 'medyar-nursing-services' ); ?>"></textarea>
						</span>
					</label>

					<div class="mns-step__nav">
						<button type="button" class="mns-btn-outline mns-btn-outline--back" data-mns-step-prev>
							<span class="mns-btn-ico mns-btn-ico--back" aria-hidden="true"><?php echo $ico( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html__( 'بازگشت', 'medyar-nursing-services' ); ?></span>
						</button>
						<button type="button" class="mns-submit-btn" data-mns-step-next>
							<span><?php echo esc_html__( 'ادامه، بررسی و ثبت', 'medyar-nursing-services' ); ?></span>
							<span class="mns-btn-ico" aria-hidden="true"><?php echo $ico( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</button>
					</div>
				</section>

				<?php /* ============ Step 3 — review + confirm ============ */ ?>
				<section class="mns-step is-hidden" data-mns-step="3" hidden aria-hidden="true" aria-labelledby="mns-ow-step-3">
					<header class="mns-step__head">
						<span class="mns-step__title-ico" aria-hidden="true"><?php echo $ico( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div>
							<h2 id="mns-ow-step-3" class="mns-step__title"><?php echo esc_html__( 'یک بار همه‌چیز را ببینید', 'medyar-nursing-services' ); ?></h2>
							<p class="mns-step__sub"><?php echo esc_html__( 'هر بخش را با دکمه ویرایش می‌توانید عوض کنید.', 'medyar-nursing-services' ); ?></p>
						</div>
					</header>

					<div class="mns-review-edit" data-mns-review aria-live="polite">
						<h3 class="mns-review-edit__title"><?php echo esc_html__( 'خلاصه درخواست', 'medyar-nursing-services' ); ?></h3>
						<?php
						$review_rows = array(
							array( 'patient', 'user', __( 'بیمار', 'medyar-nursing-services' ), 1 ),
							array( 'service', 'droplet', __( 'خدمت', 'medyar-nursing-services' ), 2 ),
							array( 'time', 'calendar', __( 'زمان', 'medyar-nursing-services' ), 1 ),
							array( 'address', 'map-pin', __( 'آدرس', 'medyar-nursing-services' ), 1 ),
							array( 'nurse', 'shield', __( 'جنسیت پرستار', 'medyar-nursing-services' ), 1 ),
						);
						foreach ( $review_rows as $rr ) :
							?>
							<div class="mns-review-edit__row">
								<span class="mns-review-edit__ico" aria-hidden="true"><?php echo $ico( $rr[1] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="mns-review-edit__main">
									<span class="mns-review-edit__label"><?php echo esc_html( $rr[2] ); ?></span>
									<strong data-mns-review-val="<?php echo esc_attr( $rr[0] ); ?>">—</strong>
								</span>
								<button type="button" class="mns-review-edit__link" data-mns-review-edit="<?php echo esc_attr( (string) $rr[3] ); ?>">
									<?php echo $ico( 'edit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span><?php echo esc_html__( 'ویرایش', 'medyar-nursing-services' ); ?></span>
								</button>
							</div>
						<?php endforeach; ?>
					</div>

					<p class="mns-ow-paynote" role="note">
						<span aria-hidden="true"><?php echo $ico( 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span><?php echo wp_kses( __( '<strong>پرداخت بعد از انجام خدمت</strong> و با لینک پیامکی است. مبلغ نهایی بر اساس خدمات انجام‌شده محاسبه می‌شود و قبل از پرداخت می‌توانید آن را ببینید.', 'medyar-nursing-services' ), array( 'strong' => array() ) ); ?></span>
					</p>

					<div class="mns-switch-row">
						<span class="mns-switch-row__ico" aria-hidden="true"><?php echo $ico( 'repeat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-switch-row__text"><strong><?php echo esc_html__( 'این خدمت تکرار شود', 'medyar-nursing-services' ); ?></strong><small><?php echo esc_html__( 'با روشن کردن، دوره و تاریخ پایان را انتخاب می‌کنید.', 'medyar-nursing-services' ); ?></small></span>
						<button type="button" class="mns-switch" data-mns-switch data-mns-recurring-switch aria-pressed="false" aria-label="<?php echo esc_attr__( 'تکرار خدمت', 'medyar-nursing-services' ); ?>"></button>
					</div>
					<div class="mns-form-row--2 is-hidden" data-mns-recurring-fields hidden>
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'تناوب', 'medyar-nursing-services' ); ?></span>
							<span class="mns-field-block__control mns-field-block__control--select">
								<select class="mns-select-native" name="recurring_pattern" data-mns-select>
									<option value="weekly"><?php echo esc_html__( 'هفتگی', 'medyar-nursing-services' ); ?></option>
									<option value="every_other"><?php echo esc_html__( 'یک‌روز در میان', 'medyar-nursing-services' ); ?></option>
									<option value="daily"><?php echo esc_html__( 'روزانه', 'medyar-nursing-services' ); ?></option>
								</select>
								<span class="mns-field-block__chevron" aria-hidden="true"><?php echo $ico( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</span>
						</label>
						<label class="mns-field-block">
							<span class="mns-field-block__label"><?php echo esc_html__( 'تاریخ پایان', 'medyar-nursing-services' ); ?></span>
							<span class="mns-field-block__control"><input type="text" name="recurring_until" placeholder="۱۴۰۵/۱۰/۰۱" autocomplete="off"></span>
						</label>
					</div>

					<div class="mns-otp">
						<span class="mns-otp__ico" aria-hidden="true"><?php echo $ico( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div class="mns-otp__text">
							<p class="mns-otp__title"><?php echo esc_html__( 'شماره‌تان را با کد پیامکی تأیید کنید', 'medyar-nursing-services' ); ?></p>
							<p class="mns-otp__sent"><?php echo esc_html__( 'کد چهار رقمی به', 'medyar-nursing-services' ); ?> <strong data-mns-otp-phone dir="ltr"></strong> <?php echo esc_html__( 'فرستاده می‌شود. اطلاعات فرم حفظ می‌شود.', 'medyar-nursing-services' ); ?></p>
						</div>
						<div class="mns-otp__boxes" data-mns-otp-boxes dir="ltr">
							<?php for ( $d = 0; $d < 4; $d++ ) : ?>
								<input type="text" class="mns-otp__digit" inputmode="numeric" maxlength="1" pattern="[0-9۰-۹]" autocomplete="one-time-code" data-mns-otp-digit aria-label="<?php echo esc_attr( sprintf( /* translators: %d: digit index */ __( 'رقم %d', 'medyar-nursing-services' ), $d + 1 ) ); ?>">
							<?php endfor; ?>
						</div>
					</div>

					<div class="mns-step__nav">
						<button type="button" class="mns-btn-outline mns-btn-outline--back" data-mns-step-prev>
							<span class="mns-btn-ico mns-btn-ico--back" aria-hidden="true"><?php echo $ico( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html__( 'بازگشت', 'medyar-nursing-services' ); ?></span>
						</button>
						<button type="submit" class="mns-submit-btn" data-mns-order-submit>
							<span class="mns-btn-ico mns-btn-ico--lead" aria-hidden="true"><?php echo $ico( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html__( 'ثبت درخواست', 'medyar-nursing-services' ); ?></span>
						</button>
					</div>
				</section>
			</form>

			<div class="mns-owizard__done-card" data-mns-done-card hidden>
				<?php $render_confirm( true, $track_sample ); ?>
			</div>
		</div>

		<aside class="mns-osum" data-mns-osum aria-live="polite">
			<div class="mns-osum__card mns-osum__card--primary">
				<div class="mns-osum__head">
					<span class="mns-osum__ico" aria-hidden="true"><?php echo $ico( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<div>
						<small><?php echo esc_html__( 'خدمت انتخابی', 'medyar-nursing-services' ); ?></small>
						<h3 class="mns-osum__svc"><?php echo esc_html( $svc_name ); ?></h3>
					</div>
				</div>
				<div class="mns-osum__pricebox">
					<small><?php echo esc_html__( 'بازه قیمت تقریبی', 'medyar-nursing-services' ); ?></small>
					<strong class="mns-osum__price" data-mns-sum-price><?php echo esc_html( $price_txt ); ?></strong>
					<small><?php echo esc_html__( 'تومان', 'medyar-nursing-services' ); ?></small>
				</div>
				<ul class="mns-osum__list">
					<?php
					$sum_rows = array(
						array( 'patient', 'user', __( 'بیمار', 'medyar-nursing-services' ) ),
						array( 'time', 'calendar', __( 'زمان', 'medyar-nursing-services' ) ),
						array( 'place', 'map-pin', __( 'منطقه', 'medyar-nursing-services' ) ),
						array( 'nurse', 'shield', __( 'پرستار', 'medyar-nursing-services' ) ),
					);
					foreach ( $sum_rows as $sr ) :
						?>
						<li>
							<span class="mns-osum__row-ico" aria-hidden="true"><?php echo $ico( $sr[1] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mns-osum__row-text"><small><?php echo esc_html( $sr[2] ); ?></small><span data-mns-sum-<?php echo esc_attr( $sr[0] ); ?>><?php echo esc_html__( 'در مرحله بعد', 'medyar-nursing-services' ); ?></span></span>
						</li>
					<?php endforeach; ?>
					<li class="is-hidden" hidden data-mns-sum-detail-row>
						<span class="mns-osum__row-ico" aria-hidden="true"><?php echo $ico( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-osum__row-text"><small><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></small><span data-mns-sum-detail></span></span>
					</li>
				</ul>
				<p class="mns-osum__pay-note"><span aria-hidden="true"><?php echo $ico( 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'پرداخت بعد از انجام خدمت و با لینک پیامکی است.', 'medyar-nursing-services' ); ?></p>
				<ul class="mns-osum__factors is-hidden" hidden data-mns-sum-price-factors>
					<li><?php echo $ico( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html__( 'تعداد و نوع سرم', 'medyar-nursing-services' ); ?></span></li>
					<li><?php echo $ico( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html__( 'مسافت تا آدرس', 'medyar-nursing-services' ); ?></span></li>
					<li><?php echo $ico( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html__( 'ساعت اعزام', 'medyar-nursing-services' ); ?></span></li>
				</ul>
			</div>

			<p class="mns-osum__paycard">
				<span aria-hidden="true"><?php echo $ico( 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo wp_kses( __( '<strong>پرداخت بعد از انجام خدمت</strong> و با لینک پیامکی است. مبلغ نهایی بر اساس خدمات انجام‌شده محاسبه می‌شود و قبل از پرداخت می‌توانید آن را ببینید.', 'medyar-nursing-services' ), array( 'strong' => array() ) ); ?></span>
			</p>

			<div class="mns-osum__how mns-how">
				<h3 class="mns-how__title"><?php echo esc_html__( 'بعد از ثبت چه می‌شود؟', 'medyar-nursing-services' ); ?></h3>
				<ol class="mns-how__steps">
					<li><span class="mns-how__num" aria-hidden="true"><?php echo $ico( 'file-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><strong><?php echo esc_html__( 'ثبت درخواست', 'medyar-nursing-services' ); ?></strong><small><?php echo esc_html__( 'کد پیگیری برایتان پیامک می‌شود.', 'medyar-nursing-services' ); ?></small></span></li>
					<li><span class="mns-how__num" aria-hidden="true"><?php echo $ico( 'user-plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><strong><?php echo esc_html__( 'تخصیص پرستار', 'medyar-nursing-services' ); ?></strong><small><?php echo esc_html__( 'نام پرستار را با پیامک می‌گیرید.', 'medyar-nursing-services' ); ?></small></span></li>
					<li class="mns-how__step--pay"><span class="mns-how__num" aria-hidden="true"><?php echo $ico( 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><strong><?php echo esc_html__( 'انجام خدمت و پرداخت', 'medyar-nursing-services' ); ?></strong><small><?php echo esc_html__( 'مبلغ نهایی را قبل از پرداخت می‌بینید.', 'medyar-nursing-services' ); ?></small></span></li>
				</ol>
			</div>
		</aside>
	</div>

	<div class="mns-owizard__mob-bar" data-mns-owizard-mob-bar>
		<div class="mns-owizard__mob-bar-meta">
			<small data-mns-mob-svc><?php echo esc_html( $svc_name ); ?></small>
			<strong data-mns-mob-price><?php echo esc_html( $price_txt . ' ' . __( 'تومان', 'medyar-nursing-services' ) ); ?></strong>
		</div>
		<button type="button" class="mns-submit-btn mns-owizard__mob-bar-btn" data-mns-mob-continue>
			<span data-mns-mob-label><?php echo esc_html__( 'ادامه', 'medyar-nursing-services' ); ?></span>
			<span class="mns-btn-ico" aria-hidden="true"><?php echo $ico( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</button>
	</div>
</div>
