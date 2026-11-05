<?php
/**
 * Admin hub dashboard (Phase 6 static).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hub     = array();
$fixture = MNS_PLUGIN_DIR . 'admin/static-fixtures/hub.php';
if ( is_readable( $fixture ) ) {
	$loaded = require $fixture;
	if ( is_array( $loaded ) ) {
		$hub = $loaded;
	}
}
$bubbles = isset( $hub['bubbles'] ) && is_array( $hub['bubbles'] ) ? $hub['bubbles'] : array();
$stats   = isset( $hub['stats'] ) && is_array( $hub['stats'] ) ? $hub['stats'] : array();
$queue   = isset( $hub['queue'] ) && is_array( $hub['queue'] ) ? $hub['queue'] : array();
$nurses  = isset( $hub['nurses'] ) && is_array( $hub['nurses'] ) ? $hub['nurses'] : array();

$links = array(
	'monitor'  => admin_url( 'admin.php?page=mns-monitor' ),
	'assign'   => admin_url( 'admin.php?page=mns-assign' ),
	'nurses'   => admin_url( 'admin.php?page=mns-nurses' ),
	'manual'   => admin_url( 'admin.php?page=mns-manual-order' ),
	'finance'  => admin_url( 'admin.php?page=mns-finance' ),
	'settings' => admin_url( 'admin.php?page=mns-settings' ),
);
?>
<div class="wrap mns-admin">
	<p class="mns-admin__crumb"><?php echo esc_html__( 'خدمات پرستاری', 'medyar-nursing-services' ); ?></p>
	<h1 class="mns-admin__title"><?php echo esc_html__( 'خدمات پرستاری', 'medyar-nursing-services' ); ?></h1>
	<p class="mns-admin__lead">
		<?php echo esc_html__( 'مرکز عملیات — اسکلت استاتیک؛ دادهٔ واقعی در فازهای بعدی.', 'medyar-nursing-services' ); ?>
		<span class="mns-admin__ver">v<?php echo esc_html( MNS_VERSION ); ?></span>
	</p>
	<div class="notice notice-info inline mns-admin-preview">
		<p><strong><?php echo esc_html__( 'پیش‌نمایش UI استاتیک', 'medyar-nursing-services' ); ?></strong> — <?php echo esc_html__( 'اعداد و صف‌ها نمونه هستند؛ کلیک‌ها فقط به اسکلت صفحات می‌روند.', 'medyar-nursing-services' ); ?></p>
	</div>

	<div class="mns-admin-bubbles" aria-label="<?php echo esc_attr__( 'هشدارهای فوری', 'medyar-nursing-services' ); ?>">
		<a class="mns-admin-bubble" href="<?php echo esc_url( $links['nurses'] ); ?>">
			<strong><?php echo esc_html( (string) ( $bubbles['nurses_pending'] ?? 0 ) ); ?></strong>
			<span><?php echo esc_html__( 'عضویت در انتظار', 'medyar-nursing-services' ); ?></span>
		</a>
		<a class="mns-admin-bubble" href="<?php echo esc_url( $links['monitor'] ); ?>">
			<strong><?php echo esc_html( (string) ( $bubbles['needs_review'] ?? 0 ) ); ?></strong>
			<span><?php echo esc_html__( 'نیازمند بررسی', 'medyar-nursing-services' ); ?></span>
		</a>
		<a class="mns-admin-bubble mns-admin-bubble--urgent" href="<?php echo esc_url( $links['monitor'] ); ?>">
			<strong><?php echo esc_html( (string) ( $bubbles['urgent_open'] ?? 0 ) ); ?></strong>
			<span><?php echo esc_html__( 'فوری باز', 'medyar-nursing-services' ); ?></span>
		</a>
	</div>

	<div class="mns-admin-stats">
		<div class="mns-admin-stat"><span><?php echo esc_html__( 'سفارش امروز', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $stats['orders_today'] ?? 0 ) ); ?></strong></div>
		<div class="mns-admin-stat"><span><?php echo esc_html__( 'بدون تخصیص', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $stats['unassigned'] ?? 0 ) ); ?></strong></div>
		<div class="mns-admin-stat"><span><?php echo esc_html__( 'در حال انجام', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $stats['in_progress'] ?? 0 ) ); ?></strong></div>
		<div class="mns-admin-stat"><span><?php echo esc_html__( 'پرداخت‌نشده', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $stats['unpaid'] ?? 0 ) ); ?></strong></div>
		<div class="mns-admin-stat"><span><?php echo esc_html__( 'پرستار آنلاین', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $stats['nurses_online'] ?? 0 ) ); ?></strong></div>
		<div class="mns-admin-stat"><span><?php echo esc_html__( 'برداشت در صف', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $stats['withdrawals'] ?? 0 ) ); ?></strong></div>
	</div>

	<div class="mns-admin-grid">
		<section class="mns-admin-card">
			<div class="mns-admin-card__head">
				<h2><?php echo esc_html__( 'صف رصد', 'medyar-nursing-services' ); ?></h2>
				<a href="<?php echo esc_url( $links['monitor'] ); ?>"><?php echo esc_html__( 'رصد مرکزی', 'medyar-nursing-services' ); ?></a>
			</div>
			<table class="widefat striped mns-admin-table">
				<thead>
					<tr>
						<th><?php echo esc_html__( 'کد', 'medyar-nursing-services' ); ?></th>
						<th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th>
						<th><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?></th>
						<th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $queue as $row ) : ?>
						<tr class="<?php echo ! empty( $row['urgent'] ) ? 'mns-admin-row--urgent' : ''; ?>">
							<td dir="ltr">
								<a href="<?php echo esc_url( $links['monitor'] ); ?>"><?php echo esc_html( (string) ( $row['code'] ?? '' ) ); ?></a>
							</td>
							<td><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['city'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['label'] ?? '' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</section>

		<section class="mns-admin-card">
			<div class="mns-admin-card__head">
				<h2><?php echo esc_html__( 'عضویت‌های در انتظار', 'medyar-nursing-services' ); ?></h2>
				<a href="<?php echo esc_url( $links['nurses'] ); ?>"><?php echo esc_html__( 'همه', 'medyar-nursing-services' ); ?></a>
			</div>
			<ul class="mns-admin-list">
				<?php foreach ( $nurses as $n ) : ?>
					<li>
						<a href="<?php echo esc_url( $links['nurses'] ); ?>">
							<strong><?php echo esc_html( (string) ( $n['name'] ?? '' ) ); ?></strong>
							<span><?php echo esc_html( (string) ( $n['hospital'] ?? '' ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	</div>

	<nav class="mns-admin-nav-cards" aria-label="<?php echo esc_attr__( 'میان‌برهای ادمین', 'medyar-nursing-services' ); ?>">
		<a class="mns-admin-nav-card" href="<?php echo esc_url( $links['monitor'] ); ?>"><strong><?php echo esc_html__( 'رصد مرکزی', 'medyar-nursing-services' ); ?></strong><span><?php echo esc_html__( 'فیلتر و جزئیات سفارش', 'medyar-nursing-services' ); ?></span></a>
		<a class="mns-admin-nav-card" href="<?php echo esc_url( $links['assign'] ); ?>"><strong><?php echo esc_html__( 'تخصیص', 'medyar-nursing-services' ); ?></strong><span><?php echo esc_html__( 'خودکار / دستی', 'medyar-nursing-services' ); ?></span></a>
		<a class="mns-admin-nav-card" href="<?php echo esc_url( $links['nurses'] ); ?>"><strong><?php echo esc_html__( 'عضویت پرستاران', 'medyar-nursing-services' ); ?></strong><span><?php echo esc_html__( 'تأیید · رد · تعلیق', 'medyar-nursing-services' ); ?></span></a>
		<a class="mns-admin-nav-card" href="<?php echo esc_url( $links['manual'] ); ?>"><strong><?php echo esc_html__( 'سفارش تلفنی', 'medyar-nursing-services' ); ?></strong><span><?php echo esc_html__( 'ثبت دستی توسط ادمین', 'medyar-nursing-services' ); ?></span></a>
		<a class="mns-admin-nav-card" href="<?php echo esc_url( $links['finance'] ); ?>"><strong><?php echo esc_html__( 'مالی و گزارش', 'medyar-nursing-services' ); ?></strong><span><?php echo esc_html__( 'کیف پول و تعرفه', 'medyar-nursing-services' ); ?></span></a>
		<a class="mns-admin-nav-card" href="<?php echo esc_url( $links['settings'] ); ?>"><strong><?php echo esc_html__( 'تنظیمات', 'medyar-nursing-services' ); ?></strong><span><?php echo esc_html__( 'الگوریتم · SMS · وفاداری', 'medyar-nursing-services' ); ?></span></a>
	</nav>
</div>
