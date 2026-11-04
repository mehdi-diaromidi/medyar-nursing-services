<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$data    = MNS_Static_Fixtures::get( 'nurse/dashboard' );
$profile = MNS_Static_Fixtures::get( 'nurse/profile' );
$metrics = isset( $data['metrics'] ) && is_array( $data['metrics'] ) ? $data['metrics'] : array();
$today   = isset( $data['today_orders'] ) && is_array( $data['today_orders'] ) ? $data['today_orders'] : array();
$name    = (string) ( $profile['display_name'] ?? 'پرستار' );
$initial = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );
?>
<div class="mns-profile-hero">
	<div class="mns-profile-hero__avatar" aria-hidden="true"><?php echo esc_html( $initial ); ?></div>
	<div>
		<p class="mns-profile-hero__name"><?php echo esc_html( $name ); ?></p>
		<p class="mns-profile-hero__meta"><?php echo esc_html__( 'پرستار تأییدشده · بیمارستان میلاد', 'medyar-nursing-services' ); ?></p>
	</div>
</div>

<div class="mns-metrics">
	<div class="mns-metric mns-metric--hero"><span class="mns-metric__label"><?php echo esc_html__( 'امروز', 'medyar-nursing-services' ); ?></span><span class="mns-metric__value"><?php echo esc_html( (string) ( $metrics['today'] ?? '۰' ) ); ?></span></div>
	<div class="mns-metric"><span class="mns-metric__label"><?php echo esc_html__( 'آینده', 'medyar-nursing-services' ); ?></span><span class="mns-metric__value"><?php echo esc_html( (string) ( $metrics['upcoming'] ?? '۰' ) ); ?></span></div>
	<div class="mns-metric"><span class="mns-metric__label"><?php echo esc_html__( 'میانگین امتیاز', 'medyar-nursing-services' ); ?></span><span class="mns-metric__value"><?php echo esc_html( (string) ( $metrics['rating_avg'] ?? '—' ) ); ?></span></div>
</div>

<section class="mns-card" aria-labelledby="mns-nurse-today-title">
	<h2 id="mns-nurse-today-title" class="mns-card__title"><?php echo esc_html__( 'نوبت‌های امروز', 'medyar-nursing-services' ); ?></h2>
	<div class="mns-table-wrap">
		<table class="mns-table">
			<thead><tr><th><?php echo esc_html__( 'ساعت', 'medyar-nursing-services' ); ?></th><th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th><th><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?></th><th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th></tr></thead>
			<tbody>
			<?php if ( empty( $today ) ) : ?>
				<tr><td colspan="4" class="mns-table__empty"><?php echo esc_html__( 'نوبتی برای امروز نیست.', 'medyar-nursing-services' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $today as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) ( $row['time'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $row['area'] ?? '' ) ); ?></td>
						<td><span class="mns-status mns-status--<?php echo esc_attr( (string) ( $row['tone'] ?? 'progress' ) ); ?>"><?php echo esc_html( (string) ( $row['status_label'] ?? '' ) ); ?></span></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</section>
