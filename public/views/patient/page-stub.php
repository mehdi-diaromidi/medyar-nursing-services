<?php
/**
 * Generic patient page stub until full static UI is built.
 *
 * @var string $page
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$titles = array(
	'new-order'  => 'ثبت سفارش',
	'orders'     => 'سفارش‌های من',
	'patients'   => 'بیماران من',
	'invoices'   => 'فاکتورها و پرداخت‌ها',
	'recurring'  => 'خدمات دوره‌ای',
	'loyalty'    => 'کارت وفاداری و تخفیف',
	'surveys'    => 'پرسشنامه‌ها',
);
$title = $titles[ $page ] ?? 'پنل بیمار';
?>
<h1 class="mns-dash__title"><?php echo esc_html( $title ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'اسکلت آماده است؛ محتوای کامل این صفحه در ادامه فاز ۳ با fixture پر می‌شود.', 'medyar-nursing-services' ); ?></p>
<div class="mns-card">
	<p style="margin:0;color:var(--mns-muted-strong);"><?php echo esc_html( sprintf( /* translators: %s: page slug */ __( 'slug: %s', 'medyar-nursing-services' ), $page ) ); ?></p>
</div>
