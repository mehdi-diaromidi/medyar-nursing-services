<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$data = MNS_Static_Fixtures::get( 'patient/invoices' );
$items = isset( $data['items'] ) ? $data['items'] : array();
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'فاکتورها و پرداخت‌ها', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'جزئیات ردیف‌ها و وضعیت پرداخت.', 'medyar-nursing-services' ); ?></p>
<section class="mns-card">
	<div class="mns-table-wrap">
		<table class="mns-table">
			<thead><tr><th><?php echo esc_html__( 'شماره', 'medyar-nursing-services' ); ?></th><th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th><th><?php echo esc_html__( 'مبلغ', 'medyar-nursing-services' ); ?></th><th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th><th></th></tr></thead>
			<tbody>
			<?php foreach ( $items as $row ) : ?>
				<tr>
					<td dir="ltr"><?php echo esc_html( (string) ( $row['number'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( (string) ( $row['amount'] ?? '' ) ); ?></td>
					<td><span class="mns-status mns-status--<?php echo esc_attr( (string) ( $row['tone'] ?? 'waiting' ) ); ?>"><?php echo esc_html( (string) ( $row['status'] ?? '' ) ); ?></span></td>
					<td><?php if ( ! empty( $row['payable'] ) ) : ?><a class="mns-btn-outline" href="#"><?php echo esc_html__( 'پرداخت', 'medyar-nursing-services' ); ?></a><?php else : ?><a class="mns-btn-outline" href="#"><?php echo esc_html__( 'دانلود', 'medyar-nursing-services' ); ?></a><?php endif; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
