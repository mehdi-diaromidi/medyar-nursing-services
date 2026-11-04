<?php
/**
 * Patient dashboard (static fixtures).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data    = MNS_Static_Fixtures::get( 'patient/dashboard' );
$metrics = isset( $data['metrics'] ) && is_array( $data['metrics'] ) ? $data['metrics'] : array();
$orders  = isset( $data['recent_orders'] ) && is_array( $data['recent_orders'] ) ? $data['recent_orders'] : array();
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'داشبورد', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'خلاصه سفارش‌ها و میان‌بر ثبت خدمت جدید.', 'medyar-nursing-services' ); ?></p>

<div class="mns-metrics" role="group" aria-label="<?php echo esc_attr__( 'شاخص‌ها', 'medyar-nursing-services' ); ?>">
	<div class="mns-metric mns-metric--hero">
		<span class="mns-metric__label"><?php echo esc_html__( 'سفارش‌های باز', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( isset( $metrics['open'] ) ? (string) $metrics['open'] : '۰' ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'تکمیل‌شده', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( isset( $metrics['completed'] ) ? (string) $metrics['completed'] : '۰' ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'در انتظار پرداخت', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( isset( $metrics['unpaid'] ) ? (string) $metrics['unpaid'] : '۰' ); ?></span>
	</div>
</div>

<div class="mns-actions-row">
	<a class="mns-submit-btn" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'new-order' ) ); ?>">
		<?php echo esc_html__( 'ثبت سفارش جدید', 'medyar-nursing-services' ); ?>
	</a>
</div>

<section class="mns-card" aria-labelledby="mns-recent-orders-title">
	<h2 id="mns-recent-orders-title" class="mns-card__title"><?php echo esc_html__( 'آخرین سفارش‌ها', 'medyar-nursing-services' ); ?></h2>
	<div class="mns-table-wrap">
		<table class="mns-table">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'کد', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'تاریخ', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $orders ) ) : ?>
					<tr><td colspan="4" class="mns-table__empty"><?php echo esc_html__( 'هنوز سفارشی ثبت نکرده‌اید.', 'medyar-nursing-services' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $orders as $row ) : ?>
						<tr>
							<td dir="ltr"><?php echo esc_html( (string) ( $row['tracking'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['date'] ?? '' ) ); ?></td>
							<td>
								<span class="mns-status mns-status--<?php echo esc_attr( (string) ( $row['tone'] ?? 'waiting' ) ); ?>">
									<?php echo esc_html( (string) ( $row['status_label'] ?? '' ) ); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<div class="mns-actions-row">
		<a class="mns-btn-outline" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'orders' ) ); ?>"><?php echo esc_html__( 'مشاهده همه', 'medyar-nursing-services' ); ?></a>
	</div>
</section>
