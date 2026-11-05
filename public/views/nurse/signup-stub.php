<?php
/**
 * Nurse signup — form + pending/rejected/suspended/approved (UX Phase D).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$allowed = array( 'form', 'pending', 'rejected', 'suspended', 'approved' );
$state   = 'form';
if ( class_exists( 'MNS_Dev' ) && MNS_Dev::enabled() ) {
	$raw = MNS_Dev::state();
	if ( in_array( $raw, $allowed, true ) ) {
		$state = $raw;
	} elseif ( isset( $_GET['mns_state'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cand = sanitize_key( wp_unslash( $_GET['mns_state'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( in_array( $cand, $allowed, true ) ) {
			$state = $cand;
		}
	}
}

$tz    = wp_timezone();
$now   = new DateTimeImmutable( 'now', $tz );
$jy    = MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) );
$track = 'MNS-APP-' . $jy[0] . '-2201';
$note  = __( 'مدارک ناقص یا ناخوانا. می‌توانید دوباره ارسال کنید؛ فرم از قبل پر شده است.', 'medyar-nursing-services' );
$panel = MNS_Pages::panel_url( 'nurse', 'dashboard' );
?>
<div class="mns-signup" dir="rtl">
	<?php if ( class_exists( 'MNS_Dev' ) && MNS_Dev::enabled() ) : ?>
		<p class="mns-preview-banner"><?php echo esc_html__( 'نمایشی استاتیک — وضعیت فقط برای پیش‌نمایش سوئیچ می‌شود.', 'medyar-nursing-services' ); ?></p>
	<?php endif; ?>

	<?php if ( 'pending' === $state ) : ?>
		<?php
		MNS_UI::page_header(
			array(
				'title' => __( 'درخواست در صف بررسی', 'medyar-nursing-services' ),
				'lead'  => __( 'نتیجه با پیامک اعلام می‌شود. معمولاً تا ۴۸ ساعت کاری.', 'medyar-nursing-services' ),
			)
		);
		MNS_UI::alert(
			array(
				'tone'  => 'info',
				'title' => __( 'نیازی به ارسال مجدد نیست', 'medyar-nursing-services' ),
				'text'  => __( 'تا اعلام نتیجه صبر کنید. در صورت نقص مدارک، پیامک جداگانه می‌آید.', 'medyar-nursing-services' ),
			)
		);
		?>
		<section class="mns-card mns-result-card">
			<span class="mns-result-card__icon" aria-hidden="true"><?php echo mns_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<p class="mns-result-card__code">
				<?php echo esc_html( MNS_Copy::t( 'label.tracking_code' ) ); ?>
				<strong dir="ltr"><?php echo esc_html( MNS_Format::digits( $track ) ); ?></strong>
				<button type="button" class="mns-link-btn" data-mns-copy="<?php echo esc_attr( $track ); ?>"><?php echo esc_html( MNS_Copy::t( 'action.copy' ) ); ?></button>
			</p>
			<p class="mns-field-hint"><?php echo esc_html__( 'کد نمونهٔ نمایشی برای پیش‌نمایش استاتیک.', 'medyar-nursing-services' ); ?></p>
			<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
		</section>
	<?php elseif ( 'approved' === $state ) : ?>
		<?php
		MNS_UI::page_header(
			array(
				'title' => __( 'عضویت تأیید شد', 'medyar-nursing-services' ),
				'lead'  => __( 'نقش پرستار فعال است. برنامه و پذیرش را تنظیم کنید.', 'medyar-nursing-services' ),
			)
		);
		?>
		<section class="mns-card mns-result-card">
			<span class="mns-result-card__icon" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div class="mns-actions-row">
				<a class="mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( $panel ); ?>"><?php echo esc_html__( 'ورود به پنل پرستار', 'medyar-nursing-services' ); ?></a>
			</div>
		</section>
	<?php elseif ( 'suspended' === $state ) : ?>
		<?php
		MNS_UI::page_header(
			array(
				'title' => __( 'حساب تعلیق شده', 'medyar-nursing-services' ),
				'lead'  => __( 'دسترسی پنل پرستار فعلاً غیرفعال است.', 'medyar-nursing-services' ),
			)
		);
		MNS_UI::alert(
			array(
				'tone'  => 'danger',
				'title' => __( 'تعلیق دسترسی', 'medyar-nursing-services' ),
				'text'  => __( 'با پشتیبانی مدیار تماس بگیرید تا وضعیت بررسی شود.', 'medyar-nursing-services' ),
			)
		);
		?>
		<section class="mns-card">
			<?php MNS_UI::support_call( array( 'variant' => 'solid' ) ); ?>
		</section>
	<?php elseif ( 'rejected' === $state ) : ?>
		<?php
		MNS_UI::page_header(
			array(
				'title' => __( 'درخواست رد شد', 'medyar-nursing-services' ),
				'lead'  => __( 'می‌توانید پس از رفع نقص دوباره ارسال کنید.', 'medyar-nursing-services' ),
			)
		);
		MNS_UI::alert(
			array(
				'tone'  => 'warning',
				'title' => __( 'یادداشت بررسی', 'medyar-nursing-services' ),
				'text'  => $note,
			)
		);
		?>
	<?php endif; ?>

	<?php if ( in_array( $state, array( 'form', 'rejected' ), true ) ) : ?>
		<?php if ( 'form' === $state ) : ?>
			<?php
			MNS_UI::page_header(
				array(
					'title' => __( 'عضویت پرستار', 'medyar-nursing-services' ),
					'lead'  => __( 'همه اطلاعات را یکجا وارد کنید. مدارک فقط برای ادمین قابل مشاهده است.', 'medyar-nursing-services' ),
				)
			);
			?>
		<?php endif; ?>

		<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

		<div class="mns-form-grid">
			<form
				class="mns-card mns-form"
				method="post"
				enctype="multipart/form-data"
				data-mns-mock="ok"
				data-mns-success="<?php echo esc_attr__( 'درخواست عضویت ثبت شد و در حال بررسی است.', 'medyar-nursing-services' ); ?>"
			>
				<h2 class="mns-card__title"><?php echo esc_html__( 'اطلاعات فردی', 'medyar-nursing-services' ); ?></h2>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'نام و نام خانوادگی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control"><input name="full_name" required maxlength="191" value="<?php echo 'rejected' === $state ? esc_attr( 'علی رضایی' ) : ''; ?>"></span>
				</label>
				<div class="mns-form-row--2">
					<label class="mns-field-block">
						<span class="mns-field-block__label"><?php echo esc_html__( 'کد ملی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control"><input name="national_id" required maxlength="10" inputmode="numeric" dir="ltr" pattern="[0-9]{10}" value="<?php echo 'rejected' === $state ? esc_attr( '0012345678' ) : ''; ?>"></span>
					</label>
					<label class="mns-field-block">
						<span class="mns-field-block__label"><?php echo esc_html__( 'جنسیت', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control mns-field-block__control--select">
							<select class="mns-select-native" data-mns-select name="gender" required data-mns-signup-gender>
								<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
								<option value="female"><?php echo esc_html__( 'خانم', 'medyar-nursing-services' ); ?></option>
								<option value="male" <?php selected( 'rejected' === $state ); ?>><?php echo esc_html__( 'آقا', 'medyar-nursing-services' ); ?></option>
							</select>
							<span class="mns-field-block__chevron">▾</span>
						</span>
					</label>
				</div>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'موبایل', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control"><input name="mobile" required data-mns-iran-mobile dir="ltr" inputmode="tel" value="<?php echo 'rejected' === $state ? esc_attr( '09129876543' ) : ''; ?>"></span>
				</label>

				<h2 class="mns-card__title"><?php echo esc_html__( 'مدارک', 'medyar-nursing-services' ); ?></h2>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'کارت ملی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control"><input type="file" name="nid" accept=".jpg,.jpeg,.png,.pdf" required></span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'کارت نظام پرستاری', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control"><input type="file" name="nurse_card" accept=".jpg,.jpeg,.png,.pdf" required></span>
				</label>
				<label class="mns-field-block" data-mns-military-field <?php echo 'rejected' === $state ? '' : 'hidden'; ?>>
					<span class="mns-field-block__label"><?php echo esc_html__( 'پایان خدمت / معافیت (آقایان)', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control"><input type="file" name="military" accept=".jpg,.jpeg,.png,.pdf"></span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'بیمارستان محل کار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control"><input name="hospital" required maxlength="191" value="<?php echo 'rejected' === $state ? esc_attr( 'بیمارستان میلاد' ) : ''; ?>"></span>
				</label>

				<h2 class="mns-card__title"><?php echo esc_html__( 'قرارداد همکاری', 'medyar-nursing-services' ); ?></h2>
				<div class="mns-contract" tabindex="0">
					<p><?php echo esc_html__( 'پرستار متعهد می‌شود خدمات را طبق پروتکل مدیار، تعرفه سیستم و زمان‌بندی توافق‌شده انجام دهد. مبلغ دستی خارج از تعرفه ممنوع است. اطلاعات بیمار محرمانه است و فقط برای انجام خدمت استفاده می‌شود.', 'medyar-nursing-services' ); ?></p>
					<p><?php echo esc_html__( 'مدیار حق تعلیق یا لغو عضویت در صورت تخلف حرفه‌ای یا شکایت موجه را دارد. سهم پرستار پس از پرداخت بیمار به کیف پول واریز می‌شود.', 'medyar-nursing-services' ); ?></p>
					<p><?php echo esc_html__( 'نسخهٔ قرارداد در پروندهٔ پرستار ذخیره می‌شود و قابل ارجاع بعدی است.', 'medyar-nursing-services' ); ?></p>
				</div>
				<label class="mns-check">
					<input type="checkbox" name="contract" required>
					<span><?php echo esc_html__( 'متن قرارداد را خواندم و می‌پذیرم.', 'medyar-nursing-services' ); ?></span>
				</label>

				<button type="submit" class="mns-submit-btn" style="margin-top:var(--mns-space-5)">
					<?php echo esc_html__( 'ارسال درخواست عضویت', 'medyar-nursing-services' ); ?>
				</button>
			</form>

			<aside class="mns-how">
				<h2 class="mns-how__title"><?php echo esc_html__( 'مراحل تأیید', 'medyar-nursing-services' ); ?></h2>
				<ol class="mns-how__steps">
					<li><span class="mns-how__num">۱</span><span><?php echo esc_html__( 'ارسال مدارک', 'medyar-nursing-services' ); ?></span></li>
					<li><span class="mns-how__num">۲</span><span><?php echo esc_html__( 'بررسی ادمین', 'medyar-nursing-services' ); ?></span></li>
					<li><span class="mns-how__num">۳</span><span><?php echo esc_html__( 'فعال‌سازی نقش پرستار', 'medyar-nursing-services' ); ?></span></li>
					<li><span class="mns-how__num">۴</span><span><?php echo esc_html__( 'تنظیم برنامه و پذیرش', 'medyar-nursing-services' ); ?></span></li>
				</ol>
			</aside>
		</div>
	<?php endif; ?>
</div>
<?php if ( in_array( $state, array( 'form', 'rejected' ), true ) ) : ?>
<script>
(function () {
	var sel = document.querySelector('[data-mns-signup-gender]');
	var mil = document.querySelector('[data-mns-military-field]');
	if (!sel || !mil) return;
	function sync() {
		var show = sel.value === 'male';
		mil.hidden = !show;
		mil.classList.toggle('is-hidden', !show);
		var input = mil.querySelector('input');
		if (input) input.required = show;
	}
	sel.addEventListener('change', sync);
	sync();
})();
</script>
<?php endif; ?>
