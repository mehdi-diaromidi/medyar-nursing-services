<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'پرسشنامه', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'امتیاز علمی، اخلاق و نظم. پس از ارسال قابل ویرایش نیست.', 'medyar-nursing-services' ); ?></p>
<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>
<form class="mns-card mns-form" method="post" data-mns-mock="ok" data-mns-success="<?php echo esc_attr__( 'نظر شما ثبت شد. سپاس.', 'medyar-nursing-services' ); ?>" style="max-width:640px">
	<p class="mns-field-block__label"><?php echo esc_html__( 'علمی', 'medyar-nursing-services' ); ?></p>
	<div class="mns-stars" data-mns-stars data-value="0"><?php for ( $i = 0; $i < 5; $i++ ) : ?><button type="button" aria-label="<?php echo esc_attr( (string) ( $i + 1 ) ); ?>">★</button><?php endfor; ?></div>
	<p class="mns-field-block__label" style="margin-top:16px"><?php echo esc_html__( 'اخلاق و خوشرویی', 'medyar-nursing-services' ); ?></p>
	<div class="mns-stars" data-mns-stars data-value="0"><?php for ( $i = 0; $i < 5; $i++ ) : ?><button type="button" aria-label="<?php echo esc_attr( (string) ( $i + 1 ) ); ?>">★</button><?php endfor; ?></div>
	<p class="mns-field-block__label" style="margin-top:16px"><?php echo esc_html__( 'نظم در کار', 'medyar-nursing-services' ); ?></p>
	<div class="mns-stars" data-mns-stars data-value="0"><?php for ( $i = 0; $i < 5; $i++ ) : ?><button type="button" aria-label="<?php echo esc_attr( (string) ( $i + 1 ) ); ?>">★</button><?php endfor; ?></div>
	<label class="mns-field-block" style="margin-top:16px"><span class="mns-field-block__label"><?php echo esc_html__( 'توضیحات', 'medyar-nursing-services' ); ?></span><span class="mns-field-block__control" style="min-height:90px;align-items:flex-start"><textarea name="comment" maxlength="400" rows="3"></textarea></span></label>
	<button type="submit" class="mns-submit-btn"><?php echo esc_html__( 'ارسال پرسشنامه', 'medyar-nursing-services' ); ?></button>
</form>
