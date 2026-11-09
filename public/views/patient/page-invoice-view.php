<?php
/**
 * Patient invoice detail + payment result — desgin/png/فاکتورها و پرداخت ها/medyar-invoice-detail-desktop.png,
 * medyar-invoices-mobile.png (phones 2–4 + «سایر حالت‌ها»), ui doc §7–§9.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$pack     = MNS_Static_Fixtures::get( 'patient/invoices' );
$items    = isset( $pack['items'] ) && is_array( $pack['items'] ) ? $pack['items'] : array();
$patients = isset( $pack['patients'] ) && is_array( $pack['patients'] ) ? $pack['patients'] : array();
$default  = (string) ( $pack['default_number'] ?? 'INV-2031' );
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view params.
$number  = isset( $_GET['mns_invoice'] ) ? sanitize_text_field( wp_unslash( $_GET['mns_invoice'] ) ) : $default;
$result  = isset( $_GET['mns_result'] ) ? sanitize_key( wp_unslash( $_GET['mns_result'] ) ) : '';
$dispute = isset( $_GET['mns_dispute'] );
// phpcs:enable

$by_number = array();
foreach ( $items as $row ) {
	$by_number[ (string) $row['number'] ] = $row;
}
if ( ! isset( $by_number[ $number ] ) ) {
	$number = $default;
}
$inv    = $by_number[ $number ] ?? array();
$status = (string) ( $inv['status'] ?? 'unpaid' );
foreach ( array( 'dispute', 'paid' ) as $dev ) {
	if ( MNS_Dev::is( $dev ) ) {
		$status = $dev;
	}
}
foreach ( array( 'success', 'failed', 'pending', 'guest', 'not_owner' ) as $dev ) {
	if ( MNS_Dev::is( $dev ) ) {
		$result = $dev;
	}
}
if ( 'paid' === $status && empty( $inv['paid_at'] ) ) {
	$inv['paid_at']    = MNS_Patient_Invoices::now( 'Y-m-d H:i' );
	$inv['pay_method'] = 'online';
	$inv['txn']        = '842196375';
}

$lines_all = isset( $pack['lines'] ) && is_array( $pack['lines'] ) ? $pack['lines'] : array();
$lines     = $lines_all[ $number ] ?? array(
	array( 'kind' => 'base', 'title' => (string) ( $inv['service'] ?? '' ), 'qty' => 1, 'unit' => (int) ( $inv['amount'] ?? 0 ) ),
);
$sum      = MNS_Patient_Invoices::totals( $lines );
$total    = $sum['total'];
$kinds    = MNS_Patient_Invoices::line_kinds();
$types    = MNS_Patient_Invoices::types();
$list_url = MNS_Pages::panel_url( 'patient', 'invoices' );
$order    = (string) ( $inv['order'] ?? '' );
$patient  = (string) ( $patients[ $inv['patient_id'] ?? '' ] ?? '' );

$status_chip = static function ( string $st ): string {
	return '<span class="mns-ostatus mns-ostatus--inv-' . esc_attr( $st ) . '"><span class="mns-ostatus__dot" aria-hidden="true"></span><span class="mns-ostatus__label">' . esc_html( MNS_Patient_Invoices::status_label( $st ) ) . '</span></span>';
};

/* —— Payment result / access states (ui doc §9) —— */
if ( '' !== $result ) :
	$back = MNS_Pages::panel_url( 'patient', 'orders' );
	?>
	<section class="mns-payres mns-payres--<?php echo esc_attr( $result ); ?>">
		<?php if ( 'success' === $result ) : ?>
			<span class="mns-payres__tick" aria-hidden="true"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h1 class="mns-payres__title"><?php echo esc_html__( 'پرداخت با موفقیت انجام شد', 'medyar-nursing-services' ); ?></h1>
			<p class="mns-payres__amount"><strong><?php echo esc_html( MNS_Format::number( $total ) ); ?></strong> <?php echo esc_html__( 'تومان', 'medyar-nursing-services' ); ?></p>
			<ul class="mns-payres__facts">
				<li><span><?php echo esc_html__( 'شماره پیگیری تراکنش', 'medyar-nursing-services' ); ?></span><strong dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $inv['txn'] ?? '842196375' ) ) ); ?></strong></li>
				<li><span><?php echo esc_html__( 'تاریخ و ساعت', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( MNS_Patient_Invoices::paid_label( MNS_Patient_Invoices::now( 'Y-m-d H:i' ), true ) ); ?></strong></li>
				<li><span><?php echo esc_html__( 'شماره فاکتور', 'medyar-nursing-services' ); ?></span><strong dir="ltr"><?php echo esc_html( $number ); ?></strong></li>
			</ul>
			<div class="mns-payres__actions">
				<a class="mns-obtn mns-obtn--primary" href="<?php echo esc_url( MNS_Patient_Invoices::print_url( $number ) ); ?>" target="_blank" rel="noopener"><?php echo mns_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html__( 'دانلود فاکتور', 'medyar-nursing-services' ); ?></span></a>
				<a class="mns-obtn mns-obtn--outline" href="<?php echo esc_url( $back ); ?>"><?php echo esc_html__( 'بازگشت به سفارش‌ها', 'medyar-nursing-services' ); ?></a>
			</div>
		<?php else : ?>
			<?php
			$map = array(
				'failed'    => array( 'alert', __( 'پرداخت انجام نشد', 'medyar-nursing-services' ), __( 'اگر مبلغ از حساب شما کسر شده، طبق شرایط درگاه و بانک برگشت داده می‌شود.', 'medyar-nursing-services' ), __( 'تلاش دوباره', 'medyar-nursing-services' ), MNS_Patient_Invoices::view_url( $number ) . '#mns-pay' ),
				'pending'   => array( 'clock', __( 'در حال بررسی پرداخت', 'medyar-nursing-services' ), __( 'نتیجه هنوز از درگاه نرسیده است. لطفاً پرداخت را تکرار نکنید.', 'medyar-nursing-services' ), __( 'به‌روزرسانی وضعیت', 'medyar-nursing-services' ), add_query_arg( array() ) ),
				'guest'     => array( 'phone', __( 'برای مشاهده و پرداخت فاکتور وارد شوید', 'medyar-nursing-services' ), __( 'بعد از ورود با کد پیامکی، به همین فاکتور برمی‌گردید.', 'medyar-nursing-services' ), __( 'ورود با کد پیامکی', 'medyar-nursing-services' ), wp_login_url( MNS_Patient_Invoices::view_url( $number ) ) ),
				'not_owner' => array( 'lock', __( 'این فاکتور متعلق به حساب شما نیست', 'medyar-nursing-services' ), __( 'اگر فکر می‌کنید اشتباهی رخ داده، با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ), MNS_Copy::t( 'action.support' ), MNS_UI::support_tel_href() ),
			);
			$m = $map[ $result ] ?? $map['failed'];
			?>
			<div class="mns-payres__card" role="<?php echo 'failed' === $result ? 'alert' : 'status'; ?>">
				<span class="mns-payres__icon" aria-hidden="true"><?php echo mns_icon( $m[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<div class="mns-payres__body">
					<h1 class="mns-payres__card-title"><?php echo esc_html( $m[1] ); ?></h1>
					<p><?php echo esc_html( $m[2] ); ?></p>
					<a class="mns-obtn mns-payres__cta" href="<?php echo esc_url( $m[4] ); ?>"><?php echo esc_html( $m[3] ); ?></a>
				</div>
			</div>
		<?php endif; ?>
	</section>
	<?php
	return;
endif;

$revised = isset( $inv['revised'] ) && is_array( $inv['revised'] ) ? $inv['revised'] : null;
?>
<section class="mns-invd mns-invd--<?php echo esc_attr( $status ); ?>" data-mns-invoice data-mns-invoice-total="<?php echo esc_attr( (string) $total ); ?>">
	<a class="mns-invd__back" href="<?php echo esc_url( $list_url ); ?>"><?php echo mns_icon( 'chevron', 'mns-icon--flip' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html__( 'بازگشت به فاکتورها', 'medyar-nursing-services' ); ?></span></a>

	<header class="mns-invd__head">
		<h1 class="mns-orders__title">
			<span class="mns-orders__title-icon" aria-hidden="true"><?php echo mns_icon( 'file-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="mns-orders__title-text"><?php echo esc_html__( 'فاکتور', 'medyar-nursing-services' ); ?> <span dir="ltr"><?php echo esc_html( $number ); ?></span></span>
			<span data-mns-invoice-status><?php echo $status_chip( $status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php if ( 'normal' !== ( $inv['type'] ?? 'normal' ) ) : ?>
				<span class="mns-inv__chip mns-inv__chip--<?php echo esc_attr( (string) $inv['type'] ); ?>"><?php echo esc_html( $types[ $inv['type'] ] ?? '' ); ?></span>
			<?php endif; ?>
		</h1>
		<p class="mns-invd__issued">
			<?php
			/* translators: %s: issue date */
			echo esc_html( sprintf( __( 'صادرشده در %s', 'medyar-nursing-services' ), MNS_Patient_Invoices::full_day( (string) ( $inv['issued'] ?? '' ) ) ) );
			?>
		</p>
	</header>

	<?php if ( $revised ) : ?>
		<p class="mns-invd__revised"><?php echo mns_icon( 'edit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php
			/* translators: 1: version, 2: date */
			echo esc_html( sprintf( __( 'این فاکتور اصلاح شده است (نسخه %1$s) · %2$s', 'medyar-nursing-services' ), MNS_Format::digits( (int) $revised['version'] ), MNS_Patient_Invoices::full_day( (string) $revised['at'] ) ) );
			?>
		</p>
	<?php endif; ?>

	<p class="mns-invd__dispute-note" data-mns-dispute-note <?php echo 'dispute' === $status ? '' : 'hidden'; ?>>
		<?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo esc_html__( 'اعتراض شما ثبت شد. پشتیبانی بررسی می‌کند و نتیجه با پیامک اطلاع داده می‌شود.', 'medyar-nursing-services' ); ?>
	</p>

	<div class="mns-invd__grid">
		<div class="mns-invd__main">
			<section class="mns-invd__card">
				<dl class="mns-invd__meta">
					<div><dt><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( $patient ); ?></dd></div>
					<div><dt><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></dt><dd><?php echo esc_html( (string) ( $inv['service'] ?? '' ) ); ?></dd></div>
					<div><dt><?php echo esc_html__( 'کد پیگیری سفارش', 'medyar-nursing-services' ); ?></dt><dd><a href="<?php echo esc_url( MNS_Patient_Invoices::order_url( $order ) ); ?>"><span dir="ltr"><?php echo esc_html( MNS_Format::digits( $order ) ); ?></span><?php echo mns_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></dd></div>
				</dl>

				<div class="arp-table-wrap">
					<table class="arp-table mns-invd__table">
						<thead>
							<tr>
								<th class="mns-invd__desc"><?php echo esc_html__( 'شرح', 'medyar-nursing-services' ); ?></th>
								<th><?php echo esc_html__( 'تعداد', 'medyar-nursing-services' ); ?></th>
								<th><?php echo esc_html__( 'قیمت واحد', 'medyar-nursing-services' ); ?></th>
								<th><?php echo esc_html__( 'جمع (تومان)', 'medyar-nursing-services' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $sum['lines'] as $line ) : ?>
								<?php $neg = (int) $line['total'] < 0; ?>
								<tr class="<?php echo $neg ? 'is-discount' : ''; ?>">
									<td class="mns-invd__desc">
										<span class="mns-invd__kind"><?php echo esc_html( $kinds[ $line['kind'] ] ?? '' ); ?></span>
										<span class="mns-invd__line"><?php echo esc_html( (string) $line['title'] ); ?></span>
										<?php if ( ! empty( $line['sub'] ) ) : ?>
											<span class="mns-invd__sub"><?php echo esc_html( (string) $line['sub'] ); ?></span>
										<?php endif; ?>
									</td>
									<td><?php echo $neg ? '—' : esc_html( MNS_Format::digits( (int) $line['qty'] ) ); ?></td>
									<td><?php echo $neg ? '—' : esc_html( MNS_Format::number( (int) $line['unit'] ) ); ?></td>
									<td class="mns-invd__sum" dir="ltr"><?php echo esc_html( MNS_Patient_Invoices::money( (int) $line['total'] ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="mns-invd__total">
					<span><?php echo 'paid' === $status ? esc_html__( 'مبلغ نهایی', 'medyar-nursing-services' ) : esc_html__( 'مبلغ نهایی قابل پرداخت', 'medyar-nursing-services' ); ?></span>
					<strong><?php echo esc_html( MNS_Format::number( $total ) ); ?> <small><?php echo esc_html__( 'تومان', 'medyar-nursing-services' ); ?></small></strong>
				</div>
			</section>
		</div>

		<aside class="mns-invd__side">
			<?php if ( 'paid' === $status ) : ?>
				<section class="mns-invd__card mns-invd__paid">
					<h2 class="mns-invd__card-title"><span class="mns-invd__card-icon is-ok" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'اطلاعات پرداخت', 'medyar-nursing-services' ); ?></h2>
					<ul class="mns-payres__facts">
						<li><span><?php echo esc_html__( 'تاریخ و ساعت', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( MNS_Patient_Invoices::paid_label( (string) $inv['paid_at'], true ) ); ?></strong></li>
						<li><span><?php echo esc_html__( 'روش پرداخت', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( MNS_Patient_Invoices::method_label( (string) ( $inv['pay_method'] ?? '' ) ) ); ?></strong></li>
						<li><span><?php echo esc_html__( 'شماره پیگیری تراکنش', 'medyar-nursing-services' ); ?></span><strong dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $inv['txn'] ?? '' ) ) ); ?></strong></li>
					</ul>
					<div class="mns-invd__btns">
						<a class="mns-obtn mns-obtn--primary" href="<?php echo esc_url( MNS_Patient_Invoices::print_url( $number ) ); ?>" target="_blank" rel="noopener"><?php echo mns_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html__( 'دانلود فاکتور', 'medyar-nursing-services' ); ?></span></a>
						<a class="mns-obtn mns-obtn--outline" href="<?php echo esc_url( add_query_arg( 'mns_autoprint', '1', MNS_Patient_Invoices::print_url( $number ) ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html__( 'چاپ', 'medyar-nursing-services' ); ?></a>
					</div>
				</section>
			<?php else : ?>
				<section class="mns-invd__card mns-invd__pay" id="mns-pay">
					<h2 class="mns-invd__card-title"><span class="mns-invd__card-icon is-warn" aria-hidden="true"><?php echo mns_icon( 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'پرداخت فاکتور', 'medyar-nursing-services' ); ?></h2>
					<?php // TODO(backend): server re-checks unpaid + no dispute + same version before redirecting to Zarinpal. ?>
					<button type="button" class="mns-obtn mns-obtn--primary mns-invd__paybtn" data-mns-invoice-pay data-mns-pay-url="<?php echo esc_url( MNS_Patient_Invoices::view_url( $number, array( 'mns_result' => 'success' ) ) ); ?>" <?php disabled( 'dispute' === $status ); ?>>
						<?php echo mns_icon( 'dispute' === $status ? 'lock' : 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span data-mns-pay-label>
							<?php
							/* translators: %s: amount */
							echo esc_html( sprintf( __( 'پرداخت آنلاین %s تومان', 'medyar-nursing-services' ), MNS_Format::number( $total ) ) );
							?>
						</span>
					</button>
					<p class="mns-invd__locked" data-mns-pay-locked <?php echo 'dispute' === $status ? '' : 'hidden'; ?>><?php echo esc_html__( 'بعد از بررسی، لینک پرداخت جدید پیامک می‌شود.', 'medyar-nursing-services' ); ?></p>
					<?php if ( 'dispute' !== $status ) : ?>
						<button type="button" class="mns-invd__dispute" data-mns-dialog-open="mns-dispute-dialog" data-mns-dispute-open><?php echo esc_html__( 'اعتراض به مبلغ', 'medyar-nursing-services' ); ?></button>
					<?php endif; ?>
					<p class="mns-invd__gateway"><?php echo esc_html__( 'پرداخت از طریق درگاه زرین‌پال انجام می‌شود.', 'medyar-nursing-services' ); ?></p>
				</section>
			<?php endif; ?>

			<section class="mns-invd__card mns-invd__links">
				<a class="mns-obtn mns-obtn--outline" href="<?php echo esc_url( MNS_UI::support_tel_href() ); ?>"><?php echo mns_icon( 'headphones' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( MNS_Copy::t( 'action.support' ) ); ?></span></a>
				<a class="mns-obtn mns-obtn--outline" href="<?php echo esc_url( MNS_Patient_Invoices::order_url( $order ) ); ?>"><?php echo esc_html__( 'مشاهده سفارش', 'medyar-nursing-services' ); ?></a>
			</section>
		</aside>
	</div>

	<?php if ( 'paid' !== $status ) : ?>
		<div class="mns-invd__paybar" data-mns-paybar>
			<button type="button" class="mns-obtn mns-obtn--primary mns-invd__paybtn" data-mns-invoice-pay data-mns-pay-url="<?php echo esc_url( MNS_Patient_Invoices::view_url( $number, array( 'mns_result' => 'success' ) ) ); ?>" <?php disabled( 'dispute' === $status ); ?>>
				<span data-mns-pay-label>
					<?php
					/* translators: %s: amount */
					echo esc_html( sprintf( __( 'پرداخت آنلاین %s تومان', 'medyar-nursing-services' ), MNS_Format::number( $total ) ) );
					?>
				</span>
			</button>
		</div>
	<?php endif; ?>
</section>

<?php if ( 'paid' !== $status ) : ?>
	<div class="mns-dialog mns-order-sheet mns-dispute" id="mns-dispute-dialog" hidden data-mns-dialog <?php echo $dispute ? 'data-mns-autoopen' : ''; ?>>
		<div class="mns-dialog__backdrop" data-mns-dialog-close tabindex="-1"></div>
		<form class="mns-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="mns-dispute-title" tabindex="-1" data-mns-dispute-form>
			<?php wp_nonce_field( 'mns_invoice_dispute', 'mns_dispute_nonce' ); ?>
			<input type="hidden" name="invoice" value="<?php echo esc_attr( $number ); ?>">
			<span class="mns-order-sheet__handle" aria-hidden="true"></span>
			<h2 class="mns-order-sheet__title" id="mns-dispute-title"><?php echo esc_html__( 'اعتراض به مبلغ', 'medyar-nursing-services' ); ?></h2>
			<p class="mns-order-sheet__sub"><?php echo esc_html__( 'کدام ردیف را درست نمی‌دانید؟ (اختیاری، ولی بررسی را سریع‌تر می‌کند)', 'medyar-nursing-services' ); ?></p>
			<div class="mns-dispute__chips">
				<?php foreach ( $sum['lines'] as $i => $line ) : ?>
					<?php
					if ( (int) $line['total'] < 0 ) {
						continue;
					}
					?>
					<label class="mns-dispute__chip"><input type="checkbox" name="lines[]" value="<?php echo esc_attr( (string) $i ); ?>"><span><?php echo esc_html( (string) $line['title'] ); ?></span></label>
				<?php endforeach; ?>
			</div>
			<label class="mns-order-cancel__field">
				<span class="screen-reader-text"><?php echo esc_html__( 'توضیح اعتراض', 'medyar-nursing-services' ); ?></span>
				<textarea name="reason" rows="3" maxlength="500" required placeholder="<?php echo esc_attr__( 'چرا مبلغ را درست نمی‌دانید؟', 'medyar-nursing-services' ); ?>"></textarea>
			</label>
			<button type="submit" class="mns-obtn mns-obtn--primary mns-dispute__submit"><?php echo esc_html__( 'ثبت اعتراض', 'medyar-nursing-services' ); ?></button>
			<p class="mns-dispute__hint"><?php echo esc_html__( 'پس از ثبت، دکمه پرداخت قفل می‌شود و نتیجه پیامک می‌شود.', 'medyar-nursing-services' ); ?></p>
		</form>
	</div>
<?php endif; ?>
