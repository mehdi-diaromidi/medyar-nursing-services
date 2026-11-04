<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'خدمات دوره‌ای', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'الگوی تکرار و نوبت‌های مستقل هر دوره.', 'medyar-nursing-services' ); ?></p>
<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>
<form class="mns-card mns-form" method="post" data-mns-mock="ok" data-mns-success="<?php echo esc_attr__( 'الگوی دوره‌ای ذخیره شد.', 'medyar-nursing-services' ); ?>" style="max-width:640px">
	<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></span><span class="mns-field-block__control mns-field-block__control--select"><select class="mns-select-native" data-mns-select name="service"><option value="1">سرم‌تراپی</option><option value="2">تزریقات</option></select><span class="mns-field-block__chevron">▾</span></span></label>
	<div class="mns-form-row--2">
		<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'از تاریخ', 'medyar-nursing-services' ); ?></span><span class="mns-field-block__control"><input name="from" placeholder="۱۴۰۳/۰۸/۰۱"></span></label>
		<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'تا تاریخ', 'medyar-nursing-services' ); ?></span><span class="mns-field-block__control"><input name="to" placeholder="۱۴۰۳/۰۹/۰۱"></span></label>
	</div>
	<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'فاصله (روز)', 'medyar-nursing-services' ); ?></span><span class="mns-field-block__control"><input name="interval" value="۷" dir="ltr"></span></label>
	<button type="submit" class="mns-submit-btn"><?php echo esc_html__( 'ثبت الگوی دوره‌ای', 'medyar-nursing-services' ); ?></button>
</form>
<section class="mns-card" style="margin-top:20px">
	<h2 class="mns-card__title"><?php echo esc_html__( 'نوبت‌های نمونه', 'medyar-nursing-services' ); ?></h2>
	<p style="margin:0;color:var(--mns-muted-strong);font-size:0.875rem"><?php echo esc_html__( 'نوبت ۱: ۱۴۰۳/۰۸/۲۰ · نوبت ۲: ۱۴۰۳/۰۸/۲۷ · نوبت ۳: ۱۴۰۳/۰۹/۰۳', 'medyar-nursing-services' ); ?></p>
</section>
