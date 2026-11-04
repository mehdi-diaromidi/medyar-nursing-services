<?php
/**
 * Nurse signup form (static states via ?mns_state=).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$state = isset( $_GET['mns_state'] ) ? sanitize_key( wp_unslash( $_GET['mns_state'] ) ) : 'form'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="mns-catalog" dir="rtl">
	<h1 class="mns-dash__title"><?php echo esc_html__( 'عضویت پرستار', 'medyar-nursing-services' ); ?></h1>

	<?php if ( 'pending' === $state ) : ?>
		<div class="mns-notice" role="status"><?php echo esc_html__( 'درخواست شما در صف بررسی ادمین است. نتیجه با پیامک اعلام می‌شود.', 'medyar-nursing-services' ); ?></div>
	<?php elseif ( 'suspended' === $state ) : ?>
		<div class="mns-notice mns-notice--error" role="alert"><?php echo esc_html__( 'حساب شما تعلیق شده است. با پشتیبانی مدیار تماس بگیرید.', 'medyar-nursing-services' ); ?></div>
	<?php elseif ( 'rejected' === $state ) : ?>
		<div class="mns-notice mns-notice--error" role="alert"><?php echo esc_html__( 'درخواست رد شد: مدارک ناقص. می‌توانید دوباره ارسال کنید.', 'medyar-nursing-services' ); ?></div>
	<?php endif; ?>

	<?php if ( in_array( $state, array( 'form', 'rejected' ), true ) ) : ?>
		<p class="mns-dash__lead"><?php echo esc_html__( 'همه اطلاعات را یکجا وارد کنید. مدارک فقط برای ادمین قابل مشاهده است.', 'medyar-nursing-services' ); ?></p>
		<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>
		<div class="mns-form-grid">
			<form class="mns-card mns-form" method="post" data-mns-mock="ok" data-mns-success="<?php echo esc_attr__( 'درخواست عضویت ثبت شد و در حال بررسی است.', 'medyar-nursing-services' ); ?>" enctype="multipart/form-data">
				<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'نام و نام خانوادگی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span><span class="mns-field-block__control"><input name="full_name" required maxlength="191"></span></label>
				<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'جنسیت', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span><span class="mns-field-block__control mns-field-block__control--select"><select class="mns-select-native" data-mns-select name="gender" required><option value="">انتخاب کنید</option><option value="female">خانم</option><option value="male">آقا</option></select><span class="mns-field-block__chevron">▾</span></span></label>
				<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'کارت ملی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span><span class="mns-field-block__control"><input type="file" name="nid" accept=".jpg,.jpeg,.png,.pdf" required></span></label>
				<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'کارت نظام پرستاری', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span><span class="mns-field-block__control"><input type="file" name="nurse_card" accept=".jpg,.jpeg,.png,.pdf" required></span></label>
				<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'پایان خدمت / معافیت (فقط آقایان)', 'medyar-nursing-services' ); ?></span><span class="mns-field-block__control"><input type="file" name="military" accept=".jpg,.jpeg,.png,.pdf"></span></label>
				<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'بیمارستان محل کار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span><span class="mns-field-block__control"><input name="hospital" required maxlength="191"></span></label>
				<label class="mns-check"><input type="checkbox" name="contract" required> <span><?php echo esc_html__( 'متن قرارداد را می‌پذیرم (نسخه ذخیره می‌شود).', 'medyar-nursing-services' ); ?></span></label>
				<button type="submit" class="mns-submit-btn" style="margin-top:16px"><?php echo esc_html__( 'ارسال درخواست عضویت', 'medyar-nursing-services' ); ?></button>
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
