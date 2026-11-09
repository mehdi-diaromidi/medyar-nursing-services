<?php
/**
 * A4 printable invoice — desgin/png/فاکتورها و پرداخت ها/medyar-invoice-a4.png + ui doc §10.
 * Only the site logo as branding; no phone/address/legal footer; no patient address.
 * TODO(backend): server-side Persian PDF (embedded font) as the «download» target.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pack     = MNS_Static_Fixtures::get( 'patient/invoices' );
$items    = isset( $pack['items'] ) && is_array( $pack['items'] ) ? $pack['items'] : array();
$patients = isset( $pack['patients'] ) && is_array( $pack['patients'] ) ? $pack['patients'] : array();
$nurses   = isset( $pack['nurses'] ) && is_array( $pack['nurses'] ) ? $pack['nurses'] : array();
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$number    = isset( $_GET['mns_invoice'] ) ? sanitize_text_field( wp_unslash( $_GET['mns_invoice'] ) ) : (string) ( $pack['default_number'] ?? '' );
$autoprint = isset( $_GET['mns_autoprint'] );
// phpcs:enable
$inv = array();
foreach ( $items as $row ) {
	if ( (string) $row['number'] === $number ) {
		$inv = $row;
	}
}
if ( ! $inv ) {
	wp_die( esc_html__( 'فاکتور پیدا نشد.', 'medyar-nursing-services' ) );
}
// TODO(backend): ownership check (invoice belongs to the logged-in account).

$lines = $pack['lines'][ $number ] ?? array(
	array( 'kind' => 'base', 'title' => (string) $inv['service'], 'qty' => 1, 'unit' => (int) $inv['amount'] ),
);
$sum   = MNS_Patient_Invoices::totals( $lines );
$kinds = MNS_Patient_Invoices::line_kinds();
$paid  = 'paid' === $inv['status'];

$logo_id  = (int) get_theme_mod( 'custom_logo' );
$logo_url = $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
$service_at = (string) ( $pack['service_at'][ $number ] ?? ( (string) $inv['issued'] . ' 10:00' ) );
$font_css   = get_template_directory_uri() . '/assets/css/fonts/IRANYekanXFANum.min.css';
?><!doctype html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( sprintf( /* translators: %s: invoice number */ __( 'فاکتور %s', 'medyar-nursing-services' ), $number ) ); ?></title>
<link rel="stylesheet" href="<?php echo esc_url( $font_css ); ?>">
<style>
	@page { size: A4 portrait; margin: 14mm; }
	* { box-sizing: border-box; }
	body { margin: 0; background: #eef1f5; font-family: IRANYekanXFANum, IRANYekanX, Vazirmatn, Tahoma, sans-serif; color: #14212e; }
	.inv-tools { display: flex; justify-content: center; gap: 10px; padding: 14px; }
	.inv-tools button, .inv-tools a { font: inherit; font-size: 14px; font-weight: 800; padding: 10px 18px; border-radius: 12px; border: 1px solid #cfe0f0; background: #fff; color: #0272b1; cursor: pointer; text-decoration: none; }
	.inv-tools button { background: #0272b1; color: #fff; border-color: #0272b1; }
	.inv-sheet { width: 210mm; min-height: 297mm; margin: 0 auto 24px; padding: 18mm 16mm; background: #fff; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); }
	.inv-top { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 18px; border-bottom: 2px solid #0272b1; }
	.inv-top h1 { margin: 0 0 12px; font-size: 22px; font-weight: 800; }
	.inv-top p { margin: 0 0 6px; font-size: 12px; color: #6b7785; }
	.inv-chip { display: inline-block; margin-top: 6px; padding: 4px 12px; border-radius: 999px; background: #e3f6ea; color: #15803d; font-size: 12px; font-weight: 800; }
	.inv-chip.is-unpaid { background: #fff1d6; color: #a15c07; }
	.inv-logo img { max-height: 46px; width: auto; }
	.inv-logo span { font-size: 28px; color: #0272b1; font-family: Georgia, serif; }
	.inv-info { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 24px; margin: 24px 0; padding: 18px 20px; border-radius: 16px; background: #f4f7fb; }
	.inv-info dt { font-size: 11px; color: #6b7785; margin-bottom: 4px; }
	.inv-info dd { margin: 0; font-size: 13px; font-weight: 600; }
	table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
	thead th { padding: 11px 12px; background: #eaf3fc; color: #0272b1; font-weight: 800; text-align: center; }
	thead th:first-child { text-align: right; border-radius: 0 8px 8px 0; }
	thead th:last-child { text-align: left; border-radius: 8px 0 0 8px; }
	tbody td { padding: 12px; border-bottom: 1px solid #edf0f3; text-align: center; vertical-align: middle; }
	tbody td:first-child { text-align: right; }
	tbody td:last-child { text-align: left; }
	.inv-sub { display: block; margin-top: 3px; font-size: 10.5px; color: #6b7785; }
	.inv-neg { color: #14935a; }
	.inv-sum { width: 300px; margin: 20px auto 0 0; font-size: 13px; }
	.inv-sum div { display: flex; justify-content: space-between; padding: 7px 4px; }
	.inv-sum .inv-final { margin-top: 8px; padding: 14px 18px; border-radius: 14px; background: #0272b1; color: #fff; font-size: 15px; font-weight: 800; }
	.inv-pay { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 26px; padding: 14px 18px; border: 1px dashed #b9d3ea; border-radius: 14px; font-size: 12px; }
	.inv-pay dt { color: #6b7785; font-size: 11px; margin-bottom: 4px; }
	.inv-pay dd { margin: 0; font-weight: 600; }
	@media print {
		body { background: #fff; }
		.inv-tools { display: none; }
		.inv-sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
	}
</style>
</head>
<body>
	<div class="inv-tools">
		<button type="button" onclick="window.print()"><?php echo esc_html__( 'چاپ / ذخیره PDF', 'medyar-nursing-services' ); ?></button>
		<a href="<?php echo esc_url( MNS_Patient_Invoices::view_url( $number ) ); ?>"><?php echo esc_html__( 'بازگشت به فاکتور', 'medyar-nursing-services' ); ?></a>
	</div>
	<main class="inv-sheet">
		<header class="inv-top">
			<div>
				<h1><?php echo esc_html__( 'فاکتور خدمات پرستاری', 'medyar-nursing-services' ); ?></h1>
				<p><?php echo esc_html__( 'شماره فاکتور:', 'medyar-nursing-services' ); ?> <span dir="ltr"><?php echo esc_html( $number ); ?></span></p>
				<p><?php echo esc_html__( 'تاریخ صدور:', 'medyar-nursing-services' ); ?> <?php echo esc_html( MNS_Patient_Invoices::full_day( (string) $inv['issued'] ) ); ?></p>
				<?php if ( $paid ) : ?>
					<span class="inv-chip"><?php echo esc_html( __( 'پرداخت‌شده', 'medyar-nursing-services' ) . ' ' . MNS_Patient_Invoices::full_day( (string) $inv['paid_at'] ) ); ?></span>
				<?php else : ?>
					<span class="inv-chip is-unpaid"><?php echo esc_html( MNS_Patient_Invoices::status_label( (string) $inv['status'] ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="inv-logo">
				<?php if ( '' !== $logo_url ) : ?>
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php else : ?>
					<span>MEDYAR</span>
				<?php endif; ?>
			</div>
		</header>

		<dl class="inv-info">
			<div><dt><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( (string) ( $patients[ $inv['patient_id'] ] ?? '' ) ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'کد پیگیری سفارش', 'medyar-nursing-services' ); ?></dt><dd dir="ltr" style="text-align:right"><?php echo esc_html( MNS_Format::digits( (string) $inv['order'] ) ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( (string) $inv['service'] ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'تاریخ و ساعت خدمت', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( MNS_Patient_Invoices::full_day( $service_at ) . '، ' . __( 'ساعت', 'medyar-nursing-services' ) . ' ' . MNS_Patient_Orders::clock( new DateTimeImmutable( $service_at, wp_timezone() ) ) ); ?></dd></div>
			<div><dt><?php echo esc_html__( 'پرستار', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( (string) ( $nurses[ $inv['order'] ] ?? '—' ) ); ?></dd></div>
		</dl>

		<table>
			<thead>
				<tr>
					<th><?php echo esc_html__( 'شرح', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'تعداد', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'قیمت واحد', 'medyar-nursing-services' ); ?></th>
					<th><?php echo esc_html__( 'جمع (تومان)', 'medyar-nursing-services' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $sum['lines'] as $line ) : ?>
					<?php $neg = (int) $line['total'] < 0; ?>
					<tr>
						<td>
							<?php echo esc_html( (string) $line['title'] ); ?>
							<span class="inv-sub"><?php echo esc_html( ! empty( $line['sub'] ) ? (string) $line['sub'] : ( 'base' === $line['kind'] || $neg ? '' : ( $kinds[ $line['kind'] ] ?? '' ) ) ); ?></span>
						</td>
						<td><?php echo $neg ? '—' : esc_html( MNS_Format::digits( (int) $line['qty'] ) ); ?></td>
						<td><?php echo $neg ? '—' : esc_html( MNS_Format::number( (int) $line['unit'] ) ); ?></td>
						<td class="<?php echo $neg ? 'inv-neg' : ''; ?>" dir="ltr"><?php echo esc_html( MNS_Patient_Invoices::money( (int) $line['total'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class="inv-sum">
			<div><span><?php echo esc_html__( 'مجموع', 'medyar-nursing-services' ); ?></span><span><?php echo esc_html( MNS_Format::number( $sum['subtotal'] ) ); ?></span></div>
			<?php if ( $sum['discounts'] < 0 ) : ?>
				<div><span><?php echo esc_html__( 'تخفیف‌ها', 'medyar-nursing-services' ); ?></span><span class="inv-neg" dir="ltr"><?php echo esc_html( MNS_Patient_Invoices::money( $sum['discounts'] ) ); ?></span></div>
			<?php endif; ?>
			<div class="inv-final"><span><?php echo esc_html__( 'مبلغ نهایی', 'medyar-nursing-services' ); ?></span><span><?php echo esc_html( MNS_Format::toman( $sum['total'] ) ); ?></span></div>
		</div>

		<?php if ( $paid ) : ?>
			<dl class="inv-pay">
				<div><dt><?php echo esc_html__( 'روش پرداخت', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( 'manual' === ( $inv['pay_method'] ?? '' ) ? __( 'ثبت‌شده توسط پشتیبانی', 'medyar-nursing-services' ) : __( 'آنلاین', 'medyar-nursing-services' ) ); ?></dd></div>
				<div><dt><?php echo esc_html__( 'تاریخ پرداخت', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( MNS_Patient_Invoices::paid_label( (string) $inv['paid_at'], true ) ); ?></dd></div>
				<div><dt><?php echo esc_html__( 'شماره پیگیری تراکنش', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( MNS_Format::digits( (string) ( $inv['txn'] ?? '' ) ) ); ?></dd></div>
			</dl>
		<?php endif; ?>
	</main>
	<?php if ( $autoprint ) : ?>
		<script>window.addEventListener('load', function () { window.print(); });</script>
	<?php endif; ?>
</body>
</html>
