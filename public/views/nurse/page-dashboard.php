<?php
/**
 * Nurse dashboard (static fixtures).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data    = MNS_Static_Fixtures::get( 'nurse/dashboard' );
$metrics = isset( $data['metrics'] ) && is_array( $data['metrics'] ) ? $data['metrics'] : array();
$today   = isset( $data['today_orders'] ) && is_array( $data['today_orders'] ) ? $data['today_orders'] : array();
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'داشبورد پرستار', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'سفارش‌های امروز و میانگین امتیاز شما.', 'medyar-nursing-services' ); ?></p>

<div class="mns-metrics">
	<div class="mns-metric mns-metric--hero">
		<span class="mns-metric__label"><?php echo esc_html__( 'امروز', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( (string) ( $metrics['today'] ?? '۰' ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'آینده', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( (string) ( $metrics['upcoming'] ?? '۰' ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'میانگین امتیاز', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( (string) ( $metrics['rating_avg'] ?? '—' ) ); ?></span>
	</div>
</div>

<section class="mns-card" aria-labelledby="mns-nurse-today-title">
	<h2 id="mns-nurse-today-title" class="mns-card__title"><?php echo esc_html__( 'نوبت‌های امروز', 'medyar-nursing-services' ); ?></h2>
	<div class="mns-table-wrap">
		<table class="mns-table">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'ساعت', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $today ) ) : ?>
					<tr><td colspan="4" class="mns-table__empty"><?php echo esc_html__( 'نوبتی برای امروز نیست.', 'medyar-nursing-services' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $today as $row ) : ?>
						<tr>
							<td><?php echo esc_html( (string) ( $row['time'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['area'] ?? '' ) ); ?></td>
							<td>
								<span class="mns-status mns-status--<?php echo esc_attr( (string) ( $row['tone'] ?? 'progress' ) ); ?>">
									<?php echo esc_html( (string) ( $row['status_label'] ?? '' ) ); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</section>
