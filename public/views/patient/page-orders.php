<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data   = MNS_Static_Fixtures::get( 'patient/orders' );
$orders = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'سفارش‌های من', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'پیگیری وضعیت و جزئیات هر درخواست.', 'medyar-nursing-services' ); ?></p>

<form class="mns-card mns-filters" method="get" role="search">
	<input type="hidden" name="mns_panel" value="patient">
	<input type="hidden" name="mns_page" value="orders">
	<label class="mns-field-block">
		<span class="mns-field-block__label"><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control mns-field-block__control--select">
			<select class="mns-select-native" name="status" data-mns-select>
				<option value=""><?php echo esc_html__( 'همه', 'medyar-nursing-services' ); ?></option>
				<option value="assigned"><?php echo esc_html__( 'تخصیص‌یافته', 'medyar-nursing-services' ); ?></option>
				<option value="completed"><?php echo esc_html__( 'تکمیل‌شده', 'medyar-nursing-services' ); ?></option>
			</select>
			<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
		</span>
	</label>
	<label class="mns-field-block">
		<span class="mns-field-block__label"><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control mns-field-block__control--select">
			<select class="mns-select-native" name="service" data-mns-select>
				<option value=""><?php echo esc_html__( 'همه', 'medyar-nursing-services' ); ?></option>
				<option value="1"><?php echo esc_html__( 'سرم‌تراپی', 'medyar-nursing-services' ); ?></option>
			</select>
			<span class="mns-field-block__chevron" aria-hidden="true">▾</span>
		</span>
	</label>
	<label class="mns-field-block">
		<span class="mns-field-block__label"><?php echo esc_html__( 'از تاریخ', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control"><input type="text" name="from" placeholder="۱۴۰۳/۰۸/۰۱" autocomplete="off"></span>
	</label>
	<label class="mns-field-block">
		<span class="mns-field-block__label"><?php echo esc_html__( 'جستجو', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control"><input type="search" name="q" placeholder="<?php echo esc_attr__( 'جستجو...', 'medyar-nursing-services' ); ?>" maxlength="100"></span>
	</label>
</form>

<section class="mns-card">
	<div class="mns-table-wrap">
		<table class="mns-table mns-table--expandable">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'کد', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'تاریخ', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th>
					<th><span class="screen-reader-text"><?php echo esc_html__( 'جزئیات', 'medyar-nursing-services' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $orders ) ) : ?>
					<tr><td colspan="5" class="mns-table__empty"><?php echo esc_html__( 'هنوز سفارشی ثبت نکرده‌اید.', 'medyar-nursing-services' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $orders as $i => $row ) : ?>
						<?php $rid = 'mns-ord-details-' . $i; ?>
						<tr class="mns-table__row">
							<td dir="ltr"><?php echo esc_html( (string) ( $row['tracking'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['date'] ?? '' ) ); ?></td>
							<td><span class="mns-status mns-status--<?php echo esc_attr( (string) ( $row['tone'] ?? 'waiting' ) ); ?>"><?php echo esc_html( (string) ( $row['status_label'] ?? '' ) ); ?></span></td>
							<td>
								<button type="button" class="mns-row-toggle mns-btn-outline" aria-expanded="false" aria-controls="<?php echo esc_attr( $rid ); ?>" aria-label="<?php echo esc_attr__( 'نمایش توضیحات', 'medyar-nursing-services' ); ?>">▾</button>
							</td>
						</tr>
						<tr id="<?php echo esc_attr( $rid ); ?>" hidden>
							<td colspan="5" style="text-align:right;white-space:normal;padding:16px;">
								<?php echo esc_html( (string) ( $row['details'] ?? '—' ) ); ?>
								<?php if ( ! empty( $row['view_url'] ) ) : ?>
									<p style="margin:12px 0 0"><a class="mns-btn-outline" href="<?php echo esc_url( (string) $row['view_url'] ); ?>"><?php echo esc_html__( 'جزئیات کامل', 'medyar-nursing-services' ); ?></a></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<nav class="mns-pager" aria-label="<?php echo esc_attr__( 'صفحه‌بندی', 'medyar-nursing-services' ); ?>">
		<span class="mns-pager__info"><?php echo esc_html__( 'نمایش ۱ تا ۳ از ۳ مورد', 'medyar-nursing-services' ); ?></span>
		<div class="mns-pager__nav">
			<button type="button" class="mns-pager__btn" disabled><?php echo esc_html__( 'قبل', 'medyar-nursing-services' ); ?></button>
			<button type="button" class="mns-pager__btn is-current" aria-current="page">۱</button>
			<button type="button" class="mns-pager__btn" disabled><?php echo esc_html__( 'بعد', 'medyar-nursing-services' ); ?></button>
		</div>
	</nav>
</section>
