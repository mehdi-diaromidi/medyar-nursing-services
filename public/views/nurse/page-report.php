<?php
/**
 * Nurse end-of-visit report (P-31): a list of sections (any order), one tap for the usual visit.
 *
 * Reads the checklist's answers instead of asking them again; sections complete themselves as their required
 * fields are answered; an always-reachable urgent notice; locked after the final submit. Only after the service ended.
 * Phases (static UI preview): open, normal, problem, submitted, early (service not ended yet).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data     = MNS_Static_Fixtures::get( 'nurse/report' );
$services = isset( $data['services'] ) && is_array( $data['services'] ) ? $data['services'] : array();

$arr = static function ( $value ): array {
	return is_array( $value ) ? $value : array();
};

$preview = MNS_Dev::enabled() || ( class_exists( 'MNS_Plugin' ) && MNS_Plugin::is_ui_static() );
$phase   = 'open';
$service = 'serum';
$code    = '';
// phpcs:disable WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET['mns_order'] ) ) {
	$code = sanitize_text_field( wp_unslash( $_GET['mns_order'] ) );
}
if ( $preview ) {
	$p = isset( $_GET['mns_phase'] ) ? sanitize_key( wp_unslash( $_GET['mns_phase'] ) ) : '';
	$s = isset( $_GET['mns_service'] ) ? sanitize_key( wp_unslash( $_GET['mns_service'] ) ) : '';
	if ( in_array( $p, array( 'open', 'normal', 'problem', 'submitted', 'early' ), true ) ) {
		$phase = $p;
	}
	if ( isset( $services[ $s ] ) ) {
		$service = $s;
	}
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended
if ( '' === $code ) {
	$now  = new DateTimeImmutable( 'now', wp_timezone() );
	$code = 'MNS-' . MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) )[0] . '-1001';
}
$svc    = $arr( $services[ $service ] ?? array() );
$vals   = $arr( $arr( $data['values'] ?? array() )[ $phase ] ?? array() );
$locked = 'submitted' === $phase;
$early  = 'early' === $phase;

$view_url = MNS_Pages::panel_url( 'nurse', 'order-view' );
$self_url = MNS_Pages::panel_url( 'nurse', 'report' );
$back_url = add_query_arg(
	array(
		'mns_order' => $code,
		'mns_state' => $early ? 'in_progress' : ( $locked ? 'invoiced' : 'service_ended' ),
	),
	$view_url
);
$phase_url = static function ( string $ph, string $sv ) use ( $self_url, $code ): string {
	return add_query_arg(
		array(
			'mns_order'   => $code,
			'mns_phase'   => $ph,
			'mns_service' => $sv,
		),
		$self_url
	);
};

$fa = static function ( $v ): string {
	return MNS_Format::digits( (string) $v );
};

/* Sections. `vis`: pt = visible to the patient, mgmt = management only. `showif`: "key=value|value". */
$extra = $arr( $svc['extra'] ?? array() );

$sections = array(
	array(
		'key'      => 'result',
		'title'    => __( 'نتیجه مراجعه', 'medyar-nursing-services' ),
		'hint'     => __( 'کامل، ناقص یا انجام‌نشده', 'medyar-nursing-services' ),
		'required' => true,
		'fields'   => array(
			array( 'key' => 'result', 'type' => 'chips', 'label' => 'نتیجه مراجعه', 'vis' => 'pt', 'required' => true, 'options' => array( 'کامل انجام شد', 'ناقص انجام شد', 'انجام نشد' ) ),
			array( 'key' => 'reason', 'type' => 'chips', 'label' => 'علت', 'vis' => 'pt', 'required' => true, 'showif' => 'result=ناقص انجام شد|انجام نشد', 'options' => array( 'بیمار موافقت نکرد', 'شرایط بیمار اجازه نداد', 'کمبود تجهیزات یا دارو', 'دسترسی به بیمار ممکن نشد', 'سایر' ) ),
			array( 'key' => 'reason_text', 'type' => 'text', 'label' => 'توضیح علت', 'vis' => 'mgmt', 'required' => true, 'showif' => 'reason=سایر', 'max' => 500, 'placeholder' => 'علت را کوتاه بنویسید' ),
		),
	),
	array(
		'key'      => 'problem',
		'title'    => __( 'مشکل یا شرایط غیرعادی', 'medyar-nursing-services' ),
		'hint'     => __( 'واکنش، دشواری، تجهیزات…', 'medyar-nursing-services' ),
		'required' => true,
		'fields'   => array(
			array( 'key' => 'problem', 'type' => 'chips', 'label' => 'مشکل یا شرایط غیرعادی داشتید؟', 'vis' => 'mgmt', 'required' => true, 'options' => array( 'خیر', 'بله' ) ),
			array( 'key' => 'problem_type', 'type' => 'chips', 'label' => 'نوع مشکل', 'vis' => 'mgmt', 'required' => true, 'showif' => 'problem=بله', 'options' => array( 'واکنش یا عارضه حین خدمت', 'دشواری در انجام اقدام', 'مشکل تجهیزات', 'مشکل هماهنگی با بیمار یا همراه', 'سایر' ) ),
			array( 'key' => 'problem_desc', 'type' => 'text', 'label' => 'شرح', 'vis' => 'mgmt', 'required' => true, 'showif' => 'problem=بله', 'max' => 800, 'placeholder' => 'چه شد و چه کردید؟' ),
		),
	),
	array(
		'key'      => 'patient',
		'title'    => __( 'وضعیت بیمار و پیگیری', 'medyar-nursing-services' ),
		'hint'     => __( 'پایدار یا نیازمند پیگیری', 'medyar-nursing-services' ),
		'required' => true,
		'fields'   => array(
			array( 'key' => 'patient_status', 'type' => 'chips', 'label' => 'وضعیت بیمار در پایان', 'vis' => 'pt', 'required' => true, 'options' => array( 'پایدار', 'نیازمند پیگیری' ) ),
			array( 'key' => 'follow', 'type' => 'chips', 'label' => 'نیاز به پیگیری بعدی؟', 'vis' => 'pt', 'required' => true, 'options' => array( 'نیازی نیست', 'بله' ) ),
			array( 'key' => 'follow_type', 'type' => 'chips', 'label' => 'نوع پیگیری', 'vis' => 'pt', 'required' => true, 'showif' => 'follow=بله', 'options' => array( 'مراجعه مجدد پرستار', 'ویزیت پزشک', 'آزمایش یا تصویربرداری', 'پیگیری تلفنی', 'سایر' ) ),
			array( 'key' => 'follow_desc', 'type' => 'text', 'label' => 'شرح پیگیری (اختیاری)', 'vis' => 'pt', 'required' => false, 'showif' => 'follow=بله', 'max' => 500, 'placeholder' => 'اگر نکته‌ای برای پیگیری هست بنویسید' ),
		),
	),
	array(
		'key'      => 'svc',
		'title'    => sprintf( /* translators: %s: service */ __( 'جزئیات %s', 'medyar-nursing-services' ), (string) ( $svc['label'] ?? '' ) ),
		'hint'     => (string) ( $svc['extra_h'] ?? '' ),
		'required' => false,
		'fields'   => array(
			array( 'key' => (string) ( $extra['key'] ?? 'stay' ), 'type' => 'number', 'label' => (string) ( $extra['label'] ?? '' ), 'vis' => 'pt', 'required' => false, 'unit' => (string) ( $extra['unit'] ?? '' ), 'short' => '', 'min' => 0, 'max' => 600 ),
		),
	),
	array(
		'key'      => 'notes',
		'title'    => __( 'توضیحات و عکس', 'medyar-nursing-services' ),
		'hint'     => __( 'اختیاری', 'medyar-nursing-services' ),
		'required' => false,
		'fields'   => array(
			array( 'key' => 'notes', 'type' => 'text', 'label' => 'توضیحات تکمیلی', 'vis' => 'pt', 'required' => false, 'max' => 1500, 'rows' => 4, 'placeholder' => 'اگر نکتهٔ دیگری هست بنویسید' ),
		),
	),
);

/** One field. */
$render_field = static function ( array $f ) use ( $vals, $locked, $fa ): void {
	$key   = (string) $f['key'];
	$type  = (string) $f['type'];
	$req   = ! empty( $f['required'] );
	$val   = isset( $vals[ $key ] ) ? (string) $vals[ $key ] : '';
	$dis   = $locked ? ' disabled' : '';
	$label = (string) $f['label'];
	$attrs = ' data-mns-rp-field="' . esc_attr( $key ) . '" data-type="' . esc_attr( $type ) . '" data-label="' . esc_attr( $label ) . '" data-vis="' . esc_attr( (string) ( $f['vis'] ?? 'pt' ) ) . '"';
	$attrs .= $req ? ' data-required' : '';
	$attrs .= ! empty( $f['showif'] ) ? ' data-showif="' . esc_attr( (string) $f['showif'] ) . '" hidden' : '';
	$attrs .= ! empty( $f['unit'] ) ? ' data-unit="' . esc_attr( (string) $f['unit'] ) . '"' : '';
	$vis    = 'mgmt' === ( $f['vis'] ?? 'pt' )
		? '<span class="mns-nrp__vis is-mgmt">' . esc_html__( 'فقط مدیریت', 'medyar-nursing-services' ) . '</span>'
		: '<span class="mns-nrp__vis is-pt">' . esc_html__( 'بیمار می‌بیند', 'medyar-nursing-services' ) . '</span>';
	if ( 'chips' === $type ) :
		?>
		<fieldset class="mns-ncl__field"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<legend class="mns-ncl__label"><?php echo esc_html( $label ); ?> <?php echo $vis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></legend>
			<div class="mns-ncl__chips">
				<?php foreach ( $f['options'] as $opt ) : ?>
					<label class="mns-ncl__chip"><input type="radio" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( (string) $opt ); ?>"<?php checked( $val, (string) $opt ); ?><?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><span><?php echo esc_html( (string) $opt ); ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<?php
	elseif ( 'text' === $type ) :
		?>
		<div class="mns-ncl__field"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-max="<?php echo esc_attr( (string) ( $f['max'] ?? 500 ) ); ?>">
			<label class="mns-ncl__label" for="mns-rp-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?> <?php echo $vis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
			<textarea id="mns-rp-<?php echo esc_attr( $key ); ?>" class="mns-nrp__area" name="<?php echo esc_attr( $key ); ?>" rows="<?php echo esc_attr( (string) ( $f['rows'] ?? 3 ) ); ?>" maxlength="<?php echo esc_attr( (string) ( $f['max'] ?? 500 ) ); ?>" placeholder="<?php echo esc_attr( (string) ( $f['placeholder'] ?? '' ) ); ?>"<?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( $val ); ?></textarea>
		</div>
		<?php
	elseif ( 'number' === $type ) :
		?>
		<div class="mns-ncl__field mns-ncl__field--num"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<label class="mns-ncl__label" for="mns-rp-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			<span class="mns-ncl__num">
				<input id="mns-rp-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="text" inputmode="numeric" autocomplete="off" value="<?php echo esc_attr( $fa( $val ) ); ?>" data-min="<?php echo esc_attr( (string) ( $f['min'] ?? 0 ) ); ?>" data-maxv="<?php echo esc_attr( (string) ( $f['max'] ?? 999 ) ); ?>"<?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php if ( ! empty( $f['unit'] ) ) : ?>
					<span class="mns-ncl__unit"><?php echo esc_html( (string) $f['unit'] ); ?></span>
				<?php endif; ?>
			</span>
			<p class="mns-ncl__msg" data-mns-rp-msg role="status"></p>
		</div>
		<?php
	endif;
};

$required_total = 0;
foreach ( $sections as $sec ) {
	if ( ! empty( $sec['required'] ) ) {
		++$required_total;
	}
}
$config = array(
	'normal' => $arr( $arr( $data['values'] ?? array() )['normal'] ?? array() ),
);
?>
<div
	class="mns-ncl mns-nrp<?php echo $locked ? ' is-locked' : ''; ?>"
	data-mns-nrp
	data-mns-ndash
	data-phase="<?php echo esc_attr( $phase ); ?>"
	data-key="<?php echo esc_attr( 'mns-nrp-' . $code . '-' . $service . '-' . $phase ); ?>"
	data-config="<?php echo esc_attr( (string) wp_json_encode( $config ) ); ?>"
	data-done-url="<?php echo esc_url( $phase_url( 'submitted', $service ) ); ?>"
>
	<span hidden data-mns-rp-trash><?php echo mns_icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

	<p class="mns-breadcrumb mns-ncl__back">
		<a href="<?php echo esc_url( $back_url ); ?>">
			<span class="mns-breadcrumb__icon" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php echo esc_html__( 'بازگشت به سفارش', 'medyar-nursing-services' ); ?>
		</a>
	</p>

	<?php if ( $preview ) : ?>
		<nav class="mns-ncl__pv" aria-label="<?php echo esc_attr__( 'پیش‌نمایش (فقط محیط توسعه)', 'medyar-nursing-services' ); ?>">
			<span class="mns-ncl__pv-list">
				<?php
				$phases = array(
					'open'      => __( 'خالی', 'medyar-nursing-services' ),
					'normal'    => __( 'عادی', 'medyar-nursing-services' ),
					'problem'   => __( 'مشکل‌دار', 'medyar-nursing-services' ),
					'submitted' => __( 'ثبت‌شده', 'medyar-nursing-services' ),
					'early'     => __( 'قبل از پایان', 'medyar-nursing-services' ),
				);
				foreach ( $phases as $k => $label ) :
					?>
					<a class="mns-ncl__pv-item<?php echo $k === $phase ? ' is-active' : ''; ?>" href="<?php echo esc_url( $phase_url( $k, $service ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
				<span class="mns-ncl__pv-sep" aria-hidden="true"></span>
				<?php foreach ( $services as $k => $s ) : ?>
					<a class="mns-ncl__pv-item<?php echo (string) $k === $service ? ' is-active' : ''; ?>" href="<?php echo esc_url( $phase_url( $phase, (string) $k ) ); ?>"><?php echo esc_html( (string) $s['label'] ); ?></a>
				<?php endforeach; ?>
			</span>
		</nav>
	<?php endif; ?>

	<div class="mns-collapse mns-ncl__net" data-mns-net inert>
		<div class="mns-collapse__inner">
			<p class="mns-ncl__netmsg" role="status"><?php echo esc_html__( 'اتصال برقرار نیست؛ پیش‌نویس روی دستگاه شما نگه داشته می‌شود و بعداً ارسال می‌شود.', 'medyar-nursing-services' ); ?></p>
		</div>
	</div>

	<header class="mns-ncl__top">
		<div class="mns-ncl__top-row">
			<h1 class="mns-ncl__title" id="mns-rp-title"><?php echo esc_html__( 'گزارش پایان مراجعه', 'medyar-nursing-services' ); ?></h1>
			<?php if ( ! $locked && ! $early ) : ?>
				<button type="button" class="mns-nrp__urgent-btn" data-mns-rp-urgent-toggle aria-expanded="false" aria-controls="mns-rp-urgent">
					<span aria-hidden="true"><?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span data-mns-rp-urgent-label><?php echo esc_html__( 'پیام به پشتیبانی', 'medyar-nursing-services' ); ?></span>
				</button>
			<?php endif; ?>
		</div>
		<p class="mns-ncl__sub"><?php echo esc_html( sprintf( /* translators: %s: service */ __( 'حسن محمدی · %s', 'medyar-nursing-services' ), (string) ( $svc['label'] ?? '' ) ) ); ?></p>
		<?php if ( $locked ) : ?>
			<p class="mns-ncl__lock">
				<span aria-hidden="true"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'گزارش قابل ویرایش نیست. اگر اشتباهی شده، با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ); ?></span>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( $early ) : ?>
		<section class="mns-card mns-nrp__early">
			<span class="mns-nrp__early-ico" aria-hidden="true"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div>
				<p class="mns-ncl__note-t"><?php echo esc_html__( 'گزارش بعد از پایان خدمت باز می‌شود', 'medyar-nursing-services' ); ?></p>
				<p class="mns-ncl__note-s"><?php echo esc_html__( 'اول خدمت را به پایان برسانید و چک‌لیست را ثبت کنید؛ بعد گزارش اینجا در دسترس است.', 'medyar-nursing-services' ); ?></p>
				<a class="mns-ncl__btn mns-nrp__early-btn" href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html__( 'بازگشت به سفارش', 'medyar-nursing-services' ); ?></a>
			</div>
		</section>
	<?php else : ?>
	<div class="mns-collapse is-open mns-ncl__view" data-mns-rp-formview>
		<div class="mns-collapse__inner">
			<form class="mns-ncl__form" data-mns-rp-form novalidate onsubmit="return false;">

				<?php if ( ! $locked ) : ?>
					<div class="mns-collapse mns-ncl__wrap" id="mns-rp-urgent" data-mns-rp-urgent inert>
						<div class="mns-collapse__inner">
							<section class="mns-nrp__urgent" aria-label="<?php echo esc_attr__( 'پیام به پشتیبانی', 'medyar-nursing-services' ); ?>">
								<div class="mns-ncl__field" data-mns-rp-urgent-field>
									<label class="mns-ncl__label" for="mns-rp-urgent-text"><?php echo esc_html__( 'پیام برای پشتیبانی', 'medyar-nursing-services' ); ?> <span class="mns-nrp__vis is-mgmt"><?php echo esc_html__( 'فقط مدیریت', 'medyar-nursing-services' ); ?></span></label>
									<textarea id="mns-rp-urgent-text" class="mns-nrp__area" rows="3" maxlength="600" data-mns-rp-urgent-text placeholder="<?php echo esc_attr__( 'چه می‌خواهید به پشتیبانی بگویید؟', 'medyar-nursing-services' ); ?>"></textarea>
								</div>
								<p class="mns-ncl__reason" data-mns-rp-urgent-reason role="alert"></p>
								<p class="mns-ncl__lead"><?php echo esc_html__( 'این پیام همراه گزارش برای پشتیبانی ارسال می‌شود. اگر همین الان پاسخ لازم دارید، تماس بگیرید.', 'medyar-nursing-services' ); ?></p>
								<div class="mns-nrp__urgent-actions">
									<a class="mns-ncl__btn" href="tel:02188607015"><?php echo esc_html__( 'تماس با پشتیبانی', 'medyar-nursing-services' ); ?></a>
									<button type="button" class="mns-nrp__urgent-set" data-mns-rp-urgent-set><?php echo esc_html__( 'ثبت پیام', 'medyar-nursing-services' ); ?></button>
								</div>
							</section>
						</div>
					</div>

					<div class="mns-collapse mns-ncl__wrap" data-mns-rp-quick inert>
						<div class="mns-collapse__inner">
							<section class="mns-card mns-nrp__quick" aria-labelledby="mns-rp-quick-t">
								<h2 class="mns-nrp__quick-t" id="mns-rp-quick-t"><?php echo esc_html__( 'مراجعه عادی تمام شد؟', 'medyar-nursing-services' ); ?></h2>
								<div class="mns-nrp__auto">
									<p class="mns-nrp__auto-h"><?php echo esc_html__( 'از چک‌لیست شما برداشته شد', 'medyar-nursing-services' ); ?></p>
									<?php foreach ( $arr( $svc['auto'] ?? array() ) as $row ) : ?>
										<p class="mns-nrp__auto-row"><span><?php echo esc_html( (string) $row['label'] ); ?></span><b><?php echo esc_html( (string) $row['value'] ); ?></b></p>
									<?php endforeach; ?>
								</div>
								<button type="button" class="mns-ncl__normal" data-mns-rp-normal>
									<span aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'بله، کامل انجام شد · بدون مشکل · بیمار پایدار', 'medyar-nursing-services' ); ?>
								</button>
								<button type="button" class="mns-ncl__btn" data-mns-rp-problem><?php echo esc_html__( 'نه، مشکلی بود', 'medyar-nursing-services' ); ?></button>
							</section>
						</div>
					</div>

					<div class="mns-collapse mns-ncl__wrap" data-mns-rp-normalrow inert>
						<div class="mns-collapse__inner">
							<div class="mns-ncl__note">
								<span class="mns-ncl__note-ico" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<div class="mns-nrp__normal-body">
									<p class="mns-ncl__note-t"><?php echo esc_html__( 'عادی تمام شد', 'medyar-nursing-services' ); ?></p>
									<p class="mns-ncl__note-s"><?php echo esc_html__( 'کامل انجام شد · بدون مشکل · بیمار پایدار · بدون پیگیری', 'medyar-nursing-services' ); ?></p>
								</div>
								<button type="button" class="mns-nrp__link" data-mns-rp-change><?php echo esc_html__( 'تغییر', 'medyar-nursing-services' ); ?></button>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<ol class="mns-ncl__steps">
					<?php
					foreach ( $sections as $i => $sec ) :
						$skey = (string) $sec['key'];
						?>
						<li class="mns-ncl__step" data-mns-sec="<?php echo esc_attr( $skey ); ?>"<?php echo ! empty( $sec['required'] ) ? ' data-required' : ''; ?> data-hint="<?php echo esc_attr( (string) $sec['hint'] ); ?>">
							<button type="button" class="mns-ncl__sh" data-mns-sec-toggle aria-expanded="false" aria-controls="mns-rp-b-<?php echo esc_attr( $skey ); ?>">
								<span class="mns-ncl__badge" aria-hidden="true">
									<b><?php echo esc_html( $fa( $i + 1 ) ); ?></b>
									<span class="mns-ncl__badge-ok"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</span>
								<span class="mns-ncl__st">
									<span class="mns-ncl__st-title"><?php echo esc_html( (string) $sec['title'] ); ?><?php echo empty( $sec['required'] ) ? ' <small class="mns-nrp__opt">' . esc_html__( 'اختیاری', 'medyar-nursing-services' ) . '</small>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<span class="mns-ncl__st-sum" data-mns-sec-sum></span>
								</span>
								<span class="mns-ncl__st-act" aria-hidden="true"><?php echo esc_html( $locked ? __( 'مشاهده', 'medyar-nursing-services' ) : __( 'ویرایش', 'medyar-nursing-services' ) ); ?></span>
							</button>
							<div class="mns-collapse" id="mns-rp-b-<?php echo esc_attr( $skey ); ?>" inert>
								<div class="mns-collapse__inner">
									<div class="mns-ncl__body">
										<?php if ( 'svc' === $skey ) : ?>
											<div class="mns-nrp__auto">
												<p class="mns-nrp__auto-h"><?php echo esc_html__( 'از چک‌لیست (فقط‌خواندنی)', 'medyar-nursing-services' ); ?></p>
												<?php foreach ( $arr( $svc['auto'] ?? array() ) as $row ) : ?>
													<p class="mns-nrp__auto-row"><span><?php echo esc_html( (string) $row['label'] ); ?></span><b><?php echo esc_html( (string) $row['value'] ); ?></b></p>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
										<?php
										foreach ( $sec['fields'] as $f ) {
											$render_field( $f );
										}
										?>
										<?php if ( 'notes' === $skey && ! $locked ) : ?>
											<div class="mns-nrp__photos">
												<p class="mns-ncl__label"><?php echo esc_html__( 'عکس', 'medyar-nursing-services' ); ?> <small class="mns-nrp__opt"><?php echo esc_html__( 'اختیاری', 'medyar-nursing-services' ); ?></small> <span class="mns-nrp__vis is-mgmt"><?php echo esc_html__( 'فقط مدیریت', 'medyar-nursing-services' ); ?></span></p>
												<label class="mns-ncl__tick">
													<input type="checkbox" data-mns-rp-consent>
													<span class="mns-ncl__box" aria-hidden="true"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
													<span class="mns-ncl__tick-text"><?php echo esc_html__( 'رضایت بیمار برای گرفتن عکس را گرفتم', 'medyar-nursing-services' ); ?></span>
												</label>
												<p class="mns-ncl__lead"><?php echo esc_html__( 'فقط ناحیهٔ مرتبط؛ چهره نباید دیده شود. حداکثر ۱۰ عکس.', 'medyar-nursing-services' ); ?></p>
												<label class="mns-nrp__pick is-off" data-mns-rp-pick>
													<input type="file" accept="image/*" multiple data-mns-rp-file disabled>
													<span><span aria-hidden="true"><?php echo mns_icon( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'انتخاب یا گرفتن عکس', 'medyar-nursing-services' ); ?></span>
												</label>
												<ul class="mns-nrp__thumbs" data-mns-rp-thumbs></ul>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>

				<?php if ( ! $locked ) : ?>
					<div class="mns-ncl__finish" data-mns-rp-finish>
						<div class="mns-ncl__finish-btns">
							<button type="button" class="mns-submit-btn" data-mns-rp-review><span><?php echo esc_html__( 'بازبینی و ثبت نهایی', 'medyar-nursing-services' ); ?></span></button>
						</div>
					</div>
				<?php endif; ?>
			</form>
		</div>
	</div>

	<div class="mns-collapse mns-ncl__view" data-mns-rp-reviewview inert>
		<div class="mns-collapse__inner">
			<div class="mns-ncl__review">
				<p class="mns-nrp__banner is-review" data-mns-rp-banner-review hidden><span aria-hidden="true"><?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><?php echo esc_html__( 'چون مراجعه کامل نبود، این سفارش برای بررسی به پشتیبانی می‌رود و فاکتور بعد از بررسی صادر می‌شود.', 'medyar-nursing-services' ); ?></span></p>
				<p class="mns-nrp__banner is-urgent" data-mns-rp-banner-urgent hidden><span aria-hidden="true"><?php echo mns_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><?php echo esc_html__( 'پیام شما برای پشتیبانی همراه گزارش ارسال می‌شود.', 'medyar-nursing-services' ); ?></span></p>
				<section class="mns-card mns-ncl__rv" aria-labelledby="mns-rp-rv">
					<h2 class="mns-ncl__rv-title" id="mns-rp-rv"><?php echo esc_html__( 'خلاصهٔ گزارش', 'medyar-nursing-services' ); ?></h2>
					<div data-mns-rp-summary></div>
				</section>
				<p class="mns-ncl__warnbox"><?php echo esc_html__( 'بعد از ثبت نهایی، گزارش قابل ویرایش نیست و الحاقیه هم ندارد.', 'medyar-nursing-services' ); ?></p>
				<div class="mns-collapse" data-mns-confirm id="mns-rp-confirm" inert>
					<div class="mns-collapse__inner">
						<div class="mns-ncl__confirm">
							<p class="mns-ncl__confirm-q"><?php echo esc_html__( 'گزارش ثبت نهایی شود؟', 'medyar-nursing-services' ); ?></p>
							<p class="mns-ncl__confirm-err" role="alert" data-mns-confirm-error></p>
							<div class="mns-ncl__confirm-actions">
								<button type="button" class="mns-submit-btn" data-mns-confirm-ok data-mns-go="<?php echo esc_url( $phase_url( 'submitted', $service ) ); ?>"><span><?php echo esc_html__( 'بله، ثبت نهایی', 'medyar-nursing-services' ); ?></span></button>
								<button type="button" class="mns-ncl__btn" data-mns-confirm-cancel><?php echo esc_html__( 'انصراف', 'medyar-nursing-services' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<div class="mns-ncl__rv-actions">
					<button type="button" class="mns-ncl__btn" data-mns-rp-edit><?php echo esc_html__( 'بازگشت به ویرایش', 'medyar-nursing-services' ); ?></button>
					<button type="button" class="mns-submit-btn" data-mns-rp-final><span><?php echo esc_html__( 'ثبت نهایی گزارش', 'medyar-nursing-services' ); ?></span></button>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
