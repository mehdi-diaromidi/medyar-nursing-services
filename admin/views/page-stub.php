<?php
/**
 * Generic admin section stub with wireframe table (UX Phase D).
 *
 * @var string $mns_admin_title
 * @var string $mns_admin_lead
 * @var string $mns_admin_body
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = isset( $mns_admin_title ) ? (string) $mns_admin_title : __( 'خدمات پرستاری', 'medyar-nursing-services' );
$lead  = isset( $mns_admin_lead ) ? (string) $mns_admin_lead : '';
$body  = isset( $mns_admin_body ) ? (string) $mns_admin_body : __( 'این بخش در اسکلت استاتیک آماده است؛ اتصال دامنه در فاز بعدی.', 'medyar-nursing-services' );
?>
<div class="wrap mns-admin">
	<p class="mns-admin__crumb"><a href="<?php echo esc_url( admin_url( 'admin.php?page=mns-dashboard' ) ); ?>"><?php echo esc_html__( 'خدمات پرستاری', 'medyar-nursing-services' ); ?></a></p>
	<h1 class="mns-admin__title"><?php echo esc_html( $title ); ?></h1>
	<?php if ( '' !== $lead ) : ?>
		<p class="mns-admin__lead"><?php echo esc_html( $lead ); ?></p>
	<?php endif; ?>

	<div class="notice notice-info inline mns-admin-preview">
		<p><strong><?php echo esc_html__( 'پیش‌نمایش UI استاتیک', 'medyar-nursing-services' ); ?></strong> — <?php echo esc_html__( 'بدون ذخیره واقعی؛ جدول و فیلترها فقط اسکلت هستند.', 'medyar-nursing-services' ); ?></p>
	</div>

	<div class="mns-admin-card">
		<p><?php echo esc_html( $body ); ?></p>
	</div>

	<div class="mns-admin-card">
		<form class="mns-admin-filters" onsubmit="return false;">
			<label>
				<span class="screen-reader-text"><?php echo esc_html__( 'جستجو', 'medyar-nursing-services' ); ?></span>
				<input type="search" class="regular-text" placeholder="<?php echo esc_attr__( 'جستجو…', 'medyar-nursing-services' ); ?>" disabled>
			</label>
			<label>
				<span class="screen-reader-text"><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></span>
				<select disabled>
					<option><?php echo esc_html__( 'همه وضعیت‌ها', 'medyar-nursing-services' ); ?></option>
				</select>
			</label>
			<button type="button" class="button" disabled><?php echo esc_html__( 'اعمال فیلتر', 'medyar-nursing-services' ); ?></button>
		</form>

		<table class="widefat striped mns-admin-table">
			<thead>
				<tr>
					<th><?php echo esc_html__( 'شناسه', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'عنوان', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'به‌روزرسانی', 'medyar-nursing-services' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td dir="ltr">—</td>
					<td><?php echo esc_html__( 'ردیف نمونهٔ اسکلت', 'medyar-nursing-services' ); ?></td>
					<td><?php echo esc_html__( 'پیش‌نمایش', 'medyar-nursing-services' ); ?></td>
					<td>—</td>
				</tr>
				<tr>
					<td dir="ltr">—</td>
					<td><?php echo esc_html__( 'اتصال داده در فاز بعدی', 'medyar-nursing-services' ); ?></td>
					<td><?php echo esc_html__( 'پیش‌نمایش', 'medyar-nursing-services' ); ?></td>
					<td>—</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
