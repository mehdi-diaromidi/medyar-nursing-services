<?php
/**
 * Patient order detail: one status card (what is happening + the 5-stage track),
 * then nurse, invoice, visit result, and the order details folded away.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$pack     = MNS_Static_Fixtures::get( 'patient/order_view' );
$by       = isset( $pack['by_status'] ) && is_array( $pack['by_status'] ) ? $pack['by_status'] : array();
$by_track = isset( $pack['by_tracking'] ) && is_array( $pack['by_tracking'] ) ? $pack['by_tracking'] : array();
$default  = (string) ( $pack['default_status'] ?? MNS_Order_Status::ASSIGNED );
$track_q  = isset( $_GET['mns_order'] ) ? sanitize_text_field( wp_unslash( $_GET['mns_order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/* The same order must read the same here as in «my orders»: take status, service, patient, time and nurse from that list. */
$list_row = null;
if ( '' !== $track_q ) {
	$list      = MNS_Static_Fixtures::get( 'patient/orders' );
	$list_pats = array();
	foreach ( (array) ( $list['patients'] ?? array() ) as $p ) {
		$list_pats[ (string) ( $p['id'] ?? '' ) ] = (string) ( $p['name'] ?? '' );
	}
	foreach ( (array) ( $list['items'] ?? array() ) as $item ) {
		if ( (string) ( $item['code'] ?? '' ) === $track_q ) {
			$list_row            = $item;
			$list_row['patient'] = $list_pats[ (string) ( $item['patient_id'] ?? '' ) ] ?? '';
			break;
		}
	}
}

$status = $default;
if ( $list_row && MNS_Order_Status::is_valid( (string) ( $list_row['status'] ?? '' ) ) ) {
	$status = (string) $list_row['status'];
} elseif ( '' !== $track_q && isset( $by_track[ $track_q ] ) ) {
	$status = (string) $by_track[ $track_q ];
} elseif ( MNS_Dev::enabled() ) {
	$raw = MNS_Dev::state();
	if ( MNS_Order_Status::is_valid( $raw ) && isset( $by[ $raw ] ) ) {
		$status = $raw;
	}
}

$d = isset( $by[ $status ] ) && is_array( $by[ $status ] ) ? $by[ $status ] : ( $by[ $default ] ?? array() );
if ( $list_row ) {
	$d['status']  = $status;
	$d['service'] = (string) ( $list_row['service'] ?? ( $d['service'] ?? '' ) );
	$d['when']    = (string) ( $list_row['when'] ?? ( $d['when'] ?? '' ) );
	if ( '' !== (string) $list_row['patient'] ) {
		$d['patient'] = (string) $list_row['patient'];
	}
	if ( empty( $list_row['nurse'] ) ) {
		$d['nurse'] = null;
	} elseif ( isset( $d['nurse'] ) && is_array( $d['nurse'] ) ) {
		$d['nurse']['name'] = (string) ( $list_row['nurse']['name'] ?? $d['nurse']['name'] );
	}
	if ( ! empty( $list_row['recurring'] ) && is_array( $list_row['recurring'] ) ) {
		$d['recurring'] = $list_row['recurring'];
	}
	$d['can_cancel'] = in_array( $status, array( MNS_Order_Status::SUBMITTED, MNS_Order_Status::ASSIGNED ), true );
}
$status = (string) ( $d['status'] ?? $status );
if ( '' !== $track_q ) {
	$d['tracking'] = $track_q;
}

$orders_url = MNS_Pages::panel_url( 'patient', 'orders' );
$new_url    = MNS_Pages::panel_url( 'patient', 'new-order' );
$nurse      = isset( $d['nurse'] ) && is_array( $d['nurse'] ) ? $d['nurse'] : null;
$invoice    = isset( $d['invoice'] ) && is_array( $d['invoice'] ) ? $d['invoice'] : null;
$tracking   = (string) ( $d['tracking'] ?? '' );
$is_review  = MNS_Order_Status::NEEDS_REVIEW === $status;
$is_cancel  = MNS_Order_Status::CANCELLED === $status;
$is_done    = MNS_Order_Status::COMPLETED === $status;
$before     = in_array( $status, array( MNS_Order_Status::SUBMITTED, MNS_Order_Status::ASSIGNED ), true );
$pay_needed = MNS_Order_Status::INVOICED === $status && ! empty( $invoice );
$invoice_url = (string) ( $d['invoice_url'] ?? MNS_Pages::panel_url( 'patient', 'invoice-view' ) );
$can_cancel = ! empty( $d['can_cancel'] );
$can_survey = ! empty( $d['can_survey'] ) && ! empty( $d['survey_url'] );
$sentence   = MNS_Order_Status::sentence( $status, 'patient' );

/* Amounts follow the list too: an issued invoice totals the list amount; a finished order shows what was paid. */
$list_amount = ( $list_row && isset( $list_row['amount'] ) && is_numeric( $list_row['amount'] ) ) ? (int) $list_row['amount'] : 0;
if ( $invoice && $list_amount > 0 ) {
	$extra = 0;
	foreach ( (array) ( $invoice['lines'] ?? array() ) as $line ) {
		$extra += (int) ( $line['amount'] ?? 0 );
	}
	$invoice['total'] = $list_amount;
	$invoice['base']  = max( 0, $list_amount - $extra );
}
$paid_amount = ( ! $invoice && $list_amount > 0 && in_array( $status, array( MNS_Order_Status::PAID, MNS_Order_Status::COMPLETED ), true ) ) ? $list_amount : 0;

$icon_map = array(
	'سرم'     => 'droplet',
	'پانسمان' => 'bandage',
	'تزریق'   => 'pill',
);
$svc      = (string) ( $d['service'] ?? __( 'جزئیات سفارش', 'medyar-nursing-services' ) );
$svc_icon = 'list';
foreach ( $icon_map as $needle => $icon_name ) {
	if ( false !== strpos( $svc, $needle ) ) {
		$svc_icon = $icon_name;
		break;
	}
}

$meta = array_filter(
	array(
		MNS_Format::human( (string) ( $d['when'] ?? '' ) ),
		(string) ( $d['patient'] ?? '' ),
	)
);
?>
<div class="mns-oview" data-mns-oview>
	<p class="mns-notice mns-form-notice" data-mns-notice tabindex="-1" hidden></p>
	<p class="mns-breadcrumb mns-oview__back">
		<a href="<?php echo esc_url( $orders_url ); ?>">
			<span class="mns-breadcrumb__icon" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php echo esc_html__( 'سفارش‌های من', 'medyar-nursing-services' ); ?>
		</a>
	</p>

	<?php /* ---- Status card: what, when, for whom, where it stands ---- */ ?>
	<section class="mns-oview__card mns-oview__status" aria-labelledby="mns-oview-title">
		<header class="mns-oview__head">
			<span class="mns-oview__ico" aria-hidden="true"><?php echo mns_icon( $svc_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div class="mns-oview__head-text">
				<h1 class="mns-oview__title" id="mns-oview-title"><?php echo esc_html( $svc ); ?></h1>
				<p class="mns-oview__meta">
					<span><?php echo esc_html( implode( ' · ', $meta ) ); ?></span>
					<?php if ( ! empty( $d['recurring']['n'] ) ) : ?>
						<span class="mns-oview__chip">
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: visit number, 2: total visits */
									__( 'دوره‌ای · نوبت %1$s از %2$s', 'medyar-nursing-services' ),
									MNS_Format::digits( (string) $d['recurring']['n'] ),
									MNS_Format::digits( (string) ( $d['recurring']['of'] ?? '' ) )
								)
							);
							?>
						</span>
					<?php endif; ?>
				</p>
			</div>
			<span class="mns-oview__badge"><?php MNS_UI::status_badge( $status ); ?></span>
		</header>

		<div class="mns-oview__track" id="mns-timeline">
			<?php MNS_UI::order_timeline( $status, array( 'variant' => 'compact' ) ); ?>
		</div>

		<div class="mns-oview__foot">
			<?php if ( '' !== $sentence ) : ?>
				<p class="mns-oview__sentence<?php echo $is_review ? ' is-warn' : ''; ?>"><?php echo esc_html( $sentence ); ?></p>
			<?php endif; ?>
			<p class="mns-oview__code">
				<span><?php echo esc_html( MNS_Copy::t( 'label.tracking_code' ) ); ?></span>
				<strong dir="ltr"><?php echo esc_html( MNS_Format::digits( $tracking ) ); ?></strong>
				<button type="button" class="mns-order__copy" data-mns-copy="<?php echo esc_attr( $tracking ); ?>" aria-label="<?php echo esc_attr__( 'کپی کد پیگیری', 'medyar-nursing-services' ); ?>">
					<?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</p>
		</div>
	</section>

	<?php /* ---- Nurse ---- */ ?>
	<?php if ( ! $is_cancel ) : ?>
		<section class="mns-oview__card mns-oview__nurse" aria-label="<?php echo esc_attr__( 'پرستار', 'medyar-nursing-services' ); ?>">
			<div class="mns-oview__nurse-main">
				<?php if ( $nurse ) : ?>
					<?php MNS_UI::nurse_card( array( 'nurse' => $nurse, 'show_call' => false ) ); ?>
				<?php else : ?>
					<div class="mns-oview__pending">
						<span class="mns-oview__pending-ico" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-oview__pending-text">
							<strong><?php echo esc_html__( 'پرستار هنوز تعیین نشده است', 'medyar-nursing-services' ); ?></strong>
							<small><?php echo esc_html__( 'نام پرستار برایتان پیامک می‌شود.', 'medyar-nursing-services' ); ?></small>
						</span>
					</div>
				<?php endif; ?>
			</div>
			<div class="mns-oview__nurse-side">
				<?php MNS_UI::support_call( array( 'variant' => 'outline', 'label' => __( 'تماس با پشتیبانی', 'medyar-nursing-services' ) ) ); ?>
				<?php if ( $nurse ) : ?>
					<small><?php echo esc_html__( 'هماهنگی با پرستار از طریق پشتیبانی است.', 'medyar-nursing-services' ); ?></small>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---- Medication step ---- */ ?>
	<?php if ( ! empty( $d['medication']['enabled'] ) ) : ?>
		<section class="mns-oview__card">
			<h2 class="mns-oview__h"><?php echo esc_html__( 'مرحلهٔ دارو', 'medyar-nursing-services' ); ?></h2>
			<p class="mns-oview__text"><?php echo esc_html( (string) ( $d['medication']['hint'] ?? '' ) ); ?></p>
		</section>
	<?php endif; ?>

	<?php /* ---- Visit result ---- */ ?>
	<?php if ( ! empty( $d['report'] ) ) : ?>
		<section class="mns-oview__card">
			<h2 class="mns-oview__h"><?php echo esc_html__( 'نتیجهٔ مراجعه', 'medyar-nursing-services' ); ?></h2>
			<p class="mns-oview__text"><?php echo esc_html( (string) $d['report'] ); ?></p>
		</section>
	<?php endif; ?>

	<?php /* ---- Invoice ---- */ ?>
	<?php if ( $invoice ) : ?>
		<section class="mns-oview__card mns-oview__invoice">
			<div class="mns-oview__h-row">
				<h2 class="mns-oview__h"><?php echo esc_html__( 'فاکتور', 'medyar-nursing-services' ); ?></h2>
				<a class="mns-oview__link" href="<?php echo esc_url( $invoice_url ); ?>"><?php echo esc_html__( 'مشاهده فاکتور', 'medyar-nursing-services' ); ?></a>
			</div>
			<?php
			MNS_UI::price_summary(
				array(
					'base'  => $invoice['base'] ?? 0,
					'lines' => $invoice['lines'] ?? array(),
					'total' => $invoice['total'] ?? 0,
				)
			);
			?>
			<?php if ( $pay_needed ) : ?>
				<a class="mns-submit-btn mns-oview__pay" href="<?php echo esc_url( $invoice_url ); ?>">
					<span>
						<?php
						/* translators: %s: amount */
						echo esc_html( sprintf( __( 'پرداخت %s', 'medyar-nursing-services' ), MNS_Format::toman( $invoice['total'] ?? 0 ) ) );
						?>
					</span>
				</a>
			<?php endif; ?>
		</section>
	<?php elseif ( $paid_amount > 0 ) : ?>
		<section class="mns-oview__card mns-oview__invoice">
			<div class="mns-oview__h-row">
				<h2 class="mns-oview__h"><?php echo esc_html__( 'فاکتور', 'medyar-nursing-services' ); ?></h2>
				<span class="mns-oview__range"><?php echo esc_html( MNS_Format::toman( $paid_amount ) ); ?></span>
			</div>
			<p class="mns-oview__text">
				<?php echo esc_html__( 'پرداخت شده.', 'medyar-nursing-services' ); ?>
				<a class="mns-oview__link" href="<?php echo esc_url( $invoice_url ); ?>"><?php echo esc_html__( 'مشاهده فاکتور', 'medyar-nursing-services' ); ?></a>
			</p>
		</section>
	<?php elseif ( ! $is_cancel && ! $is_done ) : ?>
		<section class="mns-oview__card mns-oview__invoice">
			<div class="mns-oview__h-row">
				<h2 class="mns-oview__h"><?php echo esc_html__( 'فاکتور', 'medyar-nursing-services' ); ?></h2>
				<?php if ( ! empty( $d['price_min'] ) && ! empty( $d['price_max'] ) ) : ?>
					<span class="mns-oview__range">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: min, 2: max */
								__( '%1$s تا %2$s', 'medyar-nursing-services' ),
								MNS_Format::number( $d['price_min'] ),
								MNS_Format::toman( $d['price_max'] )
							)
						);
						?>
					</span>
				<?php endif; ?>
			</div>
			<p class="mns-oview__text"><?php echo esc_html__( 'فاکتور بعد از انجام خدمت صادر می‌شود و با لینک پیامکی پرداخت می‌کنید.', 'medyar-nursing-services' ); ?></p>
		</section>
	<?php endif; ?>

	<?php /* ---- Order details (folded) ---- */ ?>
	<section class="mns-oview__card mns-oview__info">
		<button type="button" class="mns-oview__toggle" data-mns-collapse-toggle aria-expanded="false" aria-controls="mns-oview-info">
			<span class="mns-oview__h"><?php echo esc_html__( 'اطلاعات سفارش', 'medyar-nursing-services' ); ?></span>
			<span class="mns-oview__toggle-hint"><?php echo esc_html__( 'آدرس و جزئیات خدمت', 'medyar-nursing-services' ); ?></span>
			<?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<div class="mns-oview__collapse" id="mns-oview-info" inert>
			<div class="mns-oview__collapse-inner">
				<ul class="mns-oview__kv">
					<li><span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['patient'] ?? '' ) ); ?></strong></li>
					<li><span><?php echo esc_html__( 'زمان', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( MNS_Format::human( (string) ( $d['when'] ?? '' ) ) ); ?></strong></li>
					<li><span><?php echo esc_html__( 'آدرس', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['address'] ?? '' ) ); ?></strong></li>
					<?php if ( ! empty( $d['phone'] ) ) : ?>
						<li><span><?php echo esc_html__( 'تماس', 'medyar-nursing-services' ); ?></span><strong dir="ltr"><?php echo esc_html( MNS_Format::mobile( (string) $d['phone'] ) ); ?></strong></li>
					<?php endif; ?>
					<?php if ( ! empty( $d['essentials'] ) && is_array( $d['essentials'] ) ) : ?>
						<?php foreach ( $d['essentials'] as $line ) : ?>
							<?php $parts = array_map( 'trim', explode( ':', (string) $line, 2 ) ); ?>
							<li>
								<span><?php echo esc_html( $parts[0] ); ?></span>
								<strong><?php echo esc_html( $parts[1] ?? '' ); ?></strong>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</section>

	<?php /* ---- Actions: one row, primary first; cancel is never the primary ---- */ ?>
	<?php if ( $before || $can_cancel || $is_done || $is_cancel || $can_survey || $is_review ) : ?>
		<div class="mns-oview__actions">
			<?php if ( $can_survey ) : ?>
				<a class="mns-submit-btn" href="<?php echo esc_url( (string) $d['survey_url'] ); ?>"><span><?php echo esc_html__( 'ثبت نظر', 'medyar-nursing-services' ); ?></span></a>
			<?php endif; ?>
			<?php if ( $is_cancel ) : ?>
				<a class="mns-submit-btn" href="<?php echo esc_url( $new_url ); ?>"><span><?php echo esc_html__( 'ثبت درخواست جدید', 'medyar-nursing-services' ); ?></span></a>
			<?php endif; ?>
			<?php if ( $is_done && ! empty( $d['reorder_url'] ) ) : ?>
				<a class="mns-outline-btn" href="<?php echo esc_url( (string) $d['reorder_url'] ); ?>">
					<?php echo ! empty( $d['can_repick'] ) ? esc_html__( 'درخواست مجدد با همین پرستار', 'medyar-nursing-services' ) : esc_html__( 'درخواست مجدد', 'medyar-nursing-services' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $before ) : ?>
				<button type="button" class="mns-outline-btn" data-mns-dialog-open="mns-edit-order"><?php echo esc_html__( 'ویرایش توضیحات', 'medyar-nursing-services' ); ?></button>
			<?php endif; ?>
			<?php if ( $can_cancel ) : ?>
				<button type="button" class="mns-outline-btn mns-oview__cancel" data-mns-dialog-open="mns-cancel-order"><?php echo esc_html__( 'لغو سفارش', 'medyar-nursing-services' ); ?></button>
			<?php endif; ?>
		</div>
		<?php if ( $before ) : ?>
			<p class="mns-oview__note"><?php echo esc_html__( 'برای تغییر زمان، آدرس یا خدمت، سفارش را لغو کنید یا با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>

<?php if ( $pay_needed ) : ?>
	<?php
	MNS_UI::sticky_action(
		array(
			'primary_label' => sprintf(
				/* translators: %s: amount */
				__( 'پرداخت %s', 'medyar-nursing-services' ),
				MNS_Format::toman( $invoice['total'] ?? 0 )
			),
			'as_link'       => true,
			'primary_url'   => $invoice_url,
		)
	);
	?>
<?php endif; ?>

<?php
if ( $can_cancel ) {
	ob_start();
	echo '<p>' . esc_html__( 'بعد از لغو، درخواست از صف تخصیص خارج می‌شود و اگر پرستاری تعیین شده باشد به او اطلاع داده می‌شود.', 'medyar-nursing-services' ) . '</p>';
	echo '<label class="mns-oview-field"><span class="screen-reader-text">' . esc_html__( 'دلیل لغو (اختیاری)', 'medyar-nursing-services' ) . '</span><textarea name="reason" rows="3" maxlength="500" placeholder="' . esc_attr__( 'دلیل لغو (اختیاری)', 'medyar-nursing-services' ) . '"></textarea></label>';
	$body = ob_get_clean();
	ob_start();
	echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto mns-oview-danger" data-mns-mock-action data-mns-success="' . esc_attr__( 'سفارش لغو شد.', 'medyar-nursing-services' ) . '" data-mns-dialog-close>' . esc_html__( 'بله، لغو شود', 'medyar-nursing-services' ) . '</button>';
	echo '<button type="button" class="mns-outline-btn" data-mns-dialog-close>' . esc_html__( 'انصراف', 'medyar-nursing-services' ) . '</button>';
	$footer = ob_get_clean();
	MNS_UI::partial(
		'dialog',
		array(
			'id'          => 'mns-cancel-order',
			'title'       => __( 'سفارش لغو شود؟', 'medyar-nursing-services' ),
			'body_html'   => $body,
			'footer_html' => $footer,
		)
	);
}

if ( $before ) {
	$notes = implode( "\n", array_map( 'strval', (array) ( $d['essentials'] ?? array() ) ) );
	ob_start();
	echo '<label class="mns-oview-field"><span class="screen-reader-text">' . esc_html__( 'توضیحات و اطلاعات ضروری', 'medyar-nursing-services' ) . '</span><textarea name="notes" rows="4" maxlength="400">' . esc_textarea( $notes ) . '</textarea></label>';
	echo '<p class="mns-oview__note">' . esc_html__( 'پرستار از تغییر شما مطلع می‌شود.', 'medyar-nursing-services' ) . '</p>';
	$body = ob_get_clean();
	ob_start();
	echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-mock-action data-mns-success="' . esc_attr__( 'تغییرات ثبت شد. پرستار از آن مطلع می‌شود.', 'medyar-nursing-services' ) . '" data-mns-dialog-close>' . esc_html__( 'ثبت تغییرات', 'medyar-nursing-services' ) . '</button>';
	echo '<button type="button" class="mns-outline-btn" data-mns-dialog-close>' . esc_html__( 'انصراف', 'medyar-nursing-services' ) . '</button>';
	$footer = ob_get_clean();
	MNS_UI::partial(
		'dialog',
		array(
			'id'          => 'mns-edit-order',
			'title'       => __( 'ویرایش توضیحات', 'medyar-nursing-services' ),
			'body_html'   => $body,
			'footer_html' => $footer,
		)
	);
}
