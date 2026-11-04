<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$items = MNS_Static_Fixtures::get( 'patient/patients_list' );
$rows  = isset( $items['items'] ) ? $items['items'] : array();
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'بیماران من', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'افرادی که می‌توانید برایشان سفارش ثبت کنید.', 'medyar-nursing-services' ); ?></p>
<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>
<div class="mns-form-grid">
	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'فهرست', 'medyar-nursing-services' ); ?></h2>
		<div class="mns-table-wrap">
			<table class="mns-table">
				<thead><tr><th><?php echo esc_html__( 'نام', 'medyar-nursing-services' ); ?></th><th><?php echo esc_html__( 'نسبت', 'medyar-nursing-services' ); ?></th><th><?php echo esc_html__( 'تلفن', 'medyar-nursing-services' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><?php echo esc_html( (string) ( $r['name'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $r['relation'] ?? '' ) ); ?></td>
						<td dir="ltr"><?php echo esc_html( (string) ( $r['phone'] ?? '' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>
	<form class="mns-card mns-form" method="post" data-mns-mock="ok" data-mns-success="<?php echo esc_attr__( 'بیمار جدید ذخیره شد.', 'medyar-nursing-services' ); ?>">
		<h2 class="mns-card__title"><?php echo esc_html__( 'افزودن بیمار', 'medyar-nursing-services' ); ?></h2>
		<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'نام و نام خانوادگی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span><span class="mns-field-block__control"><input name="name" required maxlength="191"></span></label>
		<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'نسبت', 'medyar-nursing-services' ); ?></span><span class="mns-field-block__control"><input name="relation" maxlength="100"></span></label>
		<label class="mns-field-block"><span class="mns-field-block__label"><?php echo esc_html__( 'موبایل', 'medyar-nursing-services' ); ?></span><span class="mns-field-block__control"><input name="phone" data-mns-iran-mobile></span></label>
		<button class="mns-submit-btn" type="submit"><?php echo esc_html__( 'ذخیره', 'medyar-nursing-services' ); ?></button>
	</form>
</div>
