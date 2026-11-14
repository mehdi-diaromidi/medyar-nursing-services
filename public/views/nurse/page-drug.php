<?php
/**
 * Nurse medication step (P-33/34): one form, all sections open.
 *
 * Path (prescription / no prescription), a consult result that is recorded at once and then locked, drug cards
 * with their own photo. Nothing is saved in between; only the final submit (after the service ended).
 * Phases (static UI preview): early (service not started), draft (in service), ended, submitted.
 * Cases: none, rx, ok, denied, noanswer.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data = MNS_Static_Fixtures::get( 'nurse/drug' );
$arr  = static function ( $value ): array {
	return is_array( $value ) ? $value : array();
};

$preview = MNS_Dev::enabled() || ( class_exists( 'MNS_Plugin' ) && MNS_Plugin::is_ui_static() );
$phase   = 'draft';
$case    = 'none';
$code    = '';
$cases   = $arr( $data['cases'] ?? array() );
// phpcs:disable WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET['mns_order'] ) ) {
	$code = sanitize_text_field( wp_unslash( $_GET['mns_order'] ) );
}
if ( $preview ) {
	$p = isset( $_GET['mns_phase'] ) ? sanitize_key( wp_unslash( $_GET['mns_phase'] ) ) : '';
	$c = isset( $_GET['mns_case'] ) ? sanitize_key( wp_unslash( $_GET['mns_case'] ) ) : '';
	if ( in_array( $p, array( 'early', 'draft', 'ended', 'submitted' ), true ) ) {
		$phase = $p;
	}
	if ( isset( $cases[ $c ] ) ) {
		$case = $c;
	}
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended
if ( 'submitted' === $phase && 'none' === $case ) {
	$case = 'ok';
}
if ( '' === $code ) {
	$now  = new DateTimeImmutable( 'now', wp_timezone() );
	$code = 'MNS-' . MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) )[0] . '-1001';
}
$locked = 'submitted' === $phase;
$early  = 'early' === $phase;
$values = $arr( $cases[ $case ] ?? array() );

$view_url = MNS_Pages::panel_url( 'nurse', 'order-view' );
$self_url = MNS_Pages::panel_url( 'nurse', 'drug' );
$back_url = add_query_arg(
	array(
		'mns_order' => $code,
		'mns_state' => 'early' === $phase ? 'assigned' : ( 'draft' === $phase ? 'in_progress' : ( 'ended' === $phase ? 'service_ended' : 'invoiced' ) ),
	),
	$view_url
);
$phase_url = static function ( string $ph, string $cs ) use ( $self_url, $code ): string {
	return add_query_arg(
		array(
			'mns_order' => $code,
			'mns_phase' => $ph,
			'mns_case'  => $cs,
		),
		$self_url
	);
};

$config = array(
	'path'      => (string) ( $values['path'] ?? '' ),
	'rx_photos' => $arr( $values['rx_photos'] ?? array() ),
	'rx_doctor' => (string) ( $values['rx_doctor'] ?? '' ),
	'consult'   => $arr( $values['consult'] ?? array() ),
	'drugs'     => $arr( $values['drugs'] ?? array() ),
	'routes'    => $arr( $data['routes'] ?? array() ),
);
$dis = $locked ? ' disabled' : '';
?>
<div
	class="mns-ncl mns-ncl--form mns-nrp mns-ndr<?php echo $locked ? ' is-locked' : ''; ?>"
	data-mns-ndr
	data-mns-ndash
	data-phase="<?php echo esc_attr( $phase ); ?>"
	data-config="<?php echo esc_attr( (string) wp_json_encode( $config ) ); ?>"
	data-done-url="<?php echo esc_url( $phase_url( 'submitted', 'none' === $case ? 'ok' : $case ) ); ?>"
>
	<span hidden data-mns-nd-trash><?php echo mns_icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<span hidden data-mns-nd-cam><?php echo mns_icon( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

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
					'early'     => __( 'قبل از شروع', 'medyar-nursing-services' ),
					'draft'     => __( 'حین خدمت', 'medyar-nursing-services' ),
					'ended'     => __( 'بعد از پایان', 'medyar-nursing-services' ),
					'submitted' => __( 'ثبت‌شده', 'medyar-nursing-services' ),
				);
				foreach ( $phases as $k => $label ) :
					?>
					<a class="mns-ncl__pv-item<?php echo $k === $phase ? ' is-active' : ''; ?>" href="<?php echo esc_url( $phase_url( $k, $case ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
				<span class="mns-ncl__pv-sep" aria-hidden="true"></span>
				<?php
				$case_labels = array(
					'none'     => __( 'بدون انتخاب', 'medyar-nursing-services' ),
					'otc'      => __( 'بدون نسخه', 'medyar-nursing-services' ),
					'rx'       => __( 'نسخه دارد', 'medyar-nursing-services' ),
					'ok'       => __( 'تأیید شد', 'medyar-nursing-services' ),
					'denied'   => __( 'تأیید نشد', 'medyar-nursing-services' ),
					'noanswer' => __( 'پاسخ نداد', 'medyar-nursing-services' ),
				);
				foreach ( $case_labels as $k => $label ) :
					?>
					<a class="mns-ncl__pv-item<?php echo $k === $case ? ' is-active' : ''; ?>" href="<?php echo esc_url( $phase_url( $phase, $k ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</span>
		</nav>
	<?php endif; ?>

	<div class="mns-collapse mns-ncl__net" data-mns-net inert>
		<div class="mns-collapse__inner">
			<p class="mns-ncl__netmsg" role="status"><?php echo esc_html__( 'اتصال برقرار نیست؛ ثبت نتیجهٔ مشاوره و ثبت نهایی بدون اینترنت ممکن نیست.', 'medyar-nursing-services' ); ?></p>
		</div>
	</div>

	<header class="mns-ncl__top">
		<h1 class="mns-ncl__title" id="mns-nd-title"><?php echo esc_html__( 'مرحلهٔ دارو', 'medyar-nursing-services' ); ?></h1>
		<p class="mns-ncl__sub"><?php echo esc_html__( 'حسن محمدی · سرم‌تراپی', 'medyar-nursing-services' ); ?></p>
		<?php if ( $locked ) : ?>
			<p class="mns-ncl__lock">
				<span aria-hidden="true"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'مرحلهٔ دارو ثبت نهایی شده و قابل ویرایش نیست. اگر اشتباهی شده، با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ); ?></span>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( $early ) : ?>
		<section class="mns-card mns-nrp__early">
			<span class="mns-nrp__early-ico" aria-hidden="true"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div>
				<p class="mns-ncl__note-t"><?php echo esc_html__( 'مرحلهٔ دارو بعد از شروع خدمت باز می‌شود', 'medyar-nursing-services' ); ?></p>
				<p class="mns-ncl__note-s"><?php echo esc_html__( 'اگر قرار است دارو داده شود، بعد از شروع خدمت و پیش از دادن دارو اینجا ثبت کنید. اگر دارو نسخه یا تأیید پزشک می‌خواهد، آن را هم مشخص کنید.', 'medyar-nursing-services' ); ?></p>
				<a class="mns-ncl__btn mns-nrp__early-btn" href="<?php echo esc_url( $back_url ); ?>"><?php echo esc_html__( 'بازگشت به سفارش', 'medyar-nursing-services' ); ?></a>
			</div>
		</section>
	<?php else : ?>

	<p class="mns-ndr__info"><span aria-hidden="true"><?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><?php echo esc_html( sprintf( /* translators: %s: patient info */ __( 'اطلاعات ضروری بیمار: %s', 'medyar-nursing-services' ), (string) ( $data['info'] ?? '' ) ) ); ?></span></p>

	<p class="mns-nrp__banner is-review" data-mns-nd-banner hidden><span aria-hidden="true"><?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><?php echo esc_html__( 'این سفارش در حال بررسی توسط پشتیبانی است.', 'medyar-nursing-services' ); ?></span></p>

	<div class="mns-collapse is-open mns-ncl__view" data-mns-nd-formview>
		<div class="mns-collapse__inner">
			<form class="mns-ncl__form" data-mns-nd-form novalidate onsubmit="return false;">
				<ol class="mns-ncl__steps">

					<li class="mns-ncl__step is-open" data-mns-sec="drugs">
						<div class="mns-ncl__sh">
							<span class="mns-ncl__badge" aria-hidden="true"><b>۱</b><span class="mns-ncl__badge-ok"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
							<span class="mns-ncl__st">
								<span class="mns-ncl__st-title"><?php echo esc_html__( 'داروها', 'medyar-nursing-services' ); ?></span>
								<span class="mns-ncl__st-sum"><?php echo esc_html__( 'هر دارو با عکس و وضعیت خودش', 'medyar-nursing-services' ); ?></span>
							</span>
						</div>
						<div class="mns-collapse is-open">
							<div class="mns-collapse__inner">
								<div class="mns-ncl__body">
									<div class="mns-ndr__list" data-mns-nd-listwrap>
										<div class="mns-ndr__cards" data-mns-nd-list></div>
										<p class="mns-ncl__empty" data-mns-nd-empty><?php echo esc_html__( 'هنوز دارویی اضافه نشده است.', 'medyar-nursing-services' ); ?></p>
										<?php if ( ! $locked ) : ?>
											<button type="button" class="mns-ncl__add" data-mns-nd-add>
												<span aria-hidden="true"><?php echo mns_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'افزودن دارو', 'medyar-nursing-services' ); ?>
											</button>
										<?php endif; ?>
									</div>
									<p class="mns-ncl__reason" data-mns-nd-reason="drugs" role="alert"></p>
								</div>
							</div>
						</div>
					</li>
					<li class="mns-ncl__step is-open" data-mns-sec="path" hidden>
						<div class="mns-ncl__sh">
							<span class="mns-ncl__badge" aria-hidden="true"><b>۲</b><span class="mns-ncl__badge-ok"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
							<span class="mns-ncl__st">
								<span class="mns-ncl__st-title"><?php echo esc_html__( 'تأیید پزشک', 'medyar-nursing-services' ); ?></span>
								<span class="mns-ncl__st-sum"><?php echo esc_html__( 'نسخه یا مشاورهٔ پزشک برای داروهای نیازمند نسخه', 'medyar-nursing-services' ); ?></span>
							</span>
						</div>
						<div class="mns-collapse is-open">
							<div class="mns-collapse__inner">
								<div class="mns-ncl__body">
									<p class="mns-ncl__lead" data-mns-nd-needs-list></p>
									<fieldset class="mns-ncl__field" data-mns-nd-field="path">
										<legend class="mns-ncl__label"><?php echo esc_html__( 'بیمار نسخهٔ این داروها را دارد؟', 'medyar-nursing-services' ); ?></legend>
										<div class="mns-ncl__chips">
											<label class="mns-ncl__chip"><input type="radio" name="path" value="rx"<?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><span><?php echo esc_html__( 'بله، نسخه دارد', 'medyar-nursing-services' ); ?></span></label>
											<label class="mns-ncl__chip"><input type="radio" name="path" value="consult"<?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><span><?php echo esc_html__( 'خیر، نسخه ندارد', 'medyar-nursing-services' ); ?></span></label>
										</div>
									</fieldset>

									<div class="mns-ndr__block" data-mns-nd-rx hidden>
										<p class="mns-ncl__label"><?php echo esc_html__( 'عکس نسخه', 'medyar-nursing-services' ); ?> <small class="mns-nrp__opt"><?php echo esc_html__( 'از یک تا پنج عکس', 'medyar-nursing-services' ); ?></small></p>
										<div class="mns-ndr__photos" data-mns-nd-rxphotos data-max="5">
											<?php if ( ! $locked ) : ?>
												<label class="mns-nrp__pick" data-mns-nd-pick><input type="file" accept="image/*" multiple data-mns-nd-file><span><span aria-hidden="true"><?php echo mns_icon( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'عکس نسخه را بگیرید یا انتخاب کنید', 'medyar-nursing-services' ); ?></span></label>
											<?php endif; ?>
											<ul class="mns-nrp__thumbs" data-mns-nd-thumbs></ul>
										</div>
										<p class="mns-ncl__lead"><?php echo esc_html__( 'فقط نسخه را عکس بگیرید؛ عکس بیمار لازم نیست.', 'medyar-nursing-services' ); ?></p>
										<label class="mns-ndr__txt">
											<span><?php echo esc_html__( 'نام پزشک (اختیاری)', 'medyar-nursing-services' ); ?></span>
											<input type="text" autocomplete="off" data-mns-nd-doctor<?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										</label>
									</div>

									<div class="mns-ndr__block" data-mns-nd-consult hidden>
										<div class="mns-ndr__consult-head">
											<p class="mns-ncl__label"><?php echo esc_html__( 'مشاورهٔ پزشک', 'medyar-nursing-services' ); ?></p>
											<?php if ( ! $locked ) : ?>
												<a class="mns-ndr__call" href="tel:<?php echo esc_attr( (string) ( $data['phone'] ?? '' ) ); ?>" data-mns-needs-net>
													<span aria-hidden="true"><?php echo mns_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'تماس با پشتیبانی پزشکی', 'medyar-nursing-services' ); ?>
												</a>
											<?php endif; ?>
										</div>

										<div class="mns-nrp__auto" data-mns-nd-attempts hidden>
											<p class="mns-nrp__auto-h"><?php echo esc_html__( 'تلاش‌های بدون پاسخ', 'medyar-nursing-services' ); ?></p>
											<div data-mns-nd-attempts-list></div>
										</div>

										<div class="mns-ndr__consult-form" data-mns-nd-consult-form>
											<fieldset class="mns-ncl__field" data-mns-nd-field="consult_result">
												<legend class="mns-ncl__label"><?php echo esc_html__( 'نتیجهٔ مشاوره', 'medyar-nursing-services' ); ?> <span class="mns-nrp__vis is-pt"><?php echo esc_html__( 'زمان ثبت: خودکار', 'medyar-nursing-services' ); ?></span></legend>
												<div class="mns-ncl__chips">
													<label class="mns-ncl__chip"><input type="radio" name="consult_result" value="ok"><span><?php echo esc_html__( 'تأیید شد', 'medyar-nursing-services' ); ?></span></label>
													<label class="mns-ncl__chip"><input type="radio" name="consult_result" value="denied"><span><?php echo esc_html__( 'تأیید نشد', 'medyar-nursing-services' ); ?></span></label>
													<label class="mns-ncl__chip"><input type="radio" name="consult_result" value="none"><span><?php echo esc_html__( 'پاسخ نداد', 'medyar-nursing-services' ); ?></span></label>
												</div>
											</fieldset>
											<label class="mns-ndr__txt">
												<span><?php echo esc_html__( 'توضیح پزشک (اختیاری)', 'medyar-nursing-services' ); ?></span>
												<input type="text" autocomplete="off" maxlength="300" data-mns-nd-cnote>
											</label>
											<p class="mns-ncl__reason" data-mns-nd-consult-reason role="alert"></p>
											<button type="button" class="mns-submit-btn mns-ndr__reg" data-mns-nd-register data-mns-needs-net><span><?php echo esc_html__( 'ثبت نتیجهٔ مشاوره', 'medyar-nursing-services' ); ?></span></button>
											<p class="mns-ncl__lead"><?php echo esc_html__( 'بعد از ثبت «تأیید شد» یا «تأیید نشد»، نتیجه قفل می‌شود و قابل تغییر نیست. «پاسخ نداد» فقط یک تلاش ثبت می‌کند.', 'medyar-nursing-services' ); ?></p>
										</div>

										<div class="mns-ndr__final" data-mns-nd-final hidden>
											<div class="mns-nrp__auto" data-mns-nd-final-rows></div>
											<p class="mns-ndr__deny" data-mns-nd-deny hidden><?php echo esc_html__( 'دارو نباید داده شود. پشتیبانی به‌زودی با شما تماس می‌گیرد.', 'medyar-nursing-services' ); ?></p>
										</div>
									</div>

									<p class="mns-ncl__reason" data-mns-nd-reason="path" role="alert"></p>
								</div>
							</div>
						</div>
					</li>

				</ol>

				<?php if ( 'ended' === $phase ) : ?>
					<div class="mns-ncl__finish" data-mns-nd-finish>
						<div class="mns-ncl__finish-btns">
							<button type="button" class="mns-submit-btn" data-mns-nd-review><span><?php echo esc_html__( 'بازبینی و ثبت نهایی', 'medyar-nursing-services' ); ?></span></button>
						</div>
					</div>
				<?php endif; ?>
			</form>
		</div>
	</div>

	<div class="mns-collapse mns-ncl__view" data-mns-nd-reviewview inert>
		<div class="mns-collapse__inner">
			<div class="mns-ncl__review">
				<p class="mns-nrp__banner is-review" data-mns-nd-banner-review hidden><span aria-hidden="true"><?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><?php echo esc_html__( 'چون مشاوره تأیید نشد، فاکتور بعد از بررسی پشتیبانی صادر می‌شود.', 'medyar-nursing-services' ); ?></span></p>
				<section class="mns-card mns-ncl__rv" aria-labelledby="mns-nd-rv">
					<h2 class="mns-ncl__rv-title" id="mns-nd-rv"><?php echo esc_html__( 'خلاصهٔ مرحلهٔ دارو', 'medyar-nursing-services' ); ?></h2>
					<div data-mns-nd-summary></div>
				</section>
				<p class="mns-ncl__warnbox"><?php echo esc_html__( 'بعد از ثبت نهایی، مرحلهٔ دارو قفل می‌شود و فقط پشتیبانی می‌تواند برای اصلاح آن را باز کند.', 'medyar-nursing-services' ); ?></p>
				<div class="mns-collapse" data-mns-confirm id="mns-nd-confirm" inert>
					<div class="mns-collapse__inner">
						<div class="mns-ncl__confirm">
							<p class="mns-ncl__confirm-q"><?php echo esc_html__( 'مرحلهٔ دارو ثبت نهایی شود؟', 'medyar-nursing-services' ); ?></p>
							<p class="mns-ncl__confirm-err" role="alert" data-mns-confirm-error></p>
							<div class="mns-ncl__confirm-actions">
								<button type="button" class="mns-submit-btn" data-mns-confirm-ok data-mns-go="<?php echo esc_url( $phase_url( 'submitted', 'none' === $case ? 'ok' : $case ) ); ?>"><span><?php echo esc_html__( 'بله، ثبت نهایی', 'medyar-nursing-services' ); ?></span></button>
								<button type="button" class="mns-ncl__btn" data-mns-confirm-cancel><?php echo esc_html__( 'انصراف', 'medyar-nursing-services' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<div class="mns-ncl__rv-actions">
					<button type="button" class="mns-ncl__btn" data-mns-nd-edit><?php echo esc_html__( 'بازگشت به ویرایش', 'medyar-nursing-services' ); ?></button>
					<button type="button" class="mns-submit-btn" data-mns-nd-final><span><?php echo esc_html__( 'ثبت نهایی مرحلهٔ دارو', 'medyar-nursing-services' ); ?></span></button>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
