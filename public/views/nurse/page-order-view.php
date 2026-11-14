<?php
/**
 * Nurse order workspace (P-28): status-driven. Who and where first (call, address, essentials),
 * then the step cards (checklist, medication when the service has it, report), money after the invoice.
 *
 * The forms behind the step cards are separate pages (next round); the cards show their state.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$pack    = MNS_Static_Fixtures::get( 'nurse/order_view' );
$by      = isset( $pack['by_status'] ) && is_array( $pack['by_status'] ) ? $pack['by_status'] : array();
$default = (string) ( $pack['default_status'] ?? MNS_Order_Status::ASSIGNED );

$status  = $default;
$preview = MNS_Dev::enabled() || ( class_exists( 'MNS_Plugin' ) && MNS_Plugin::is_ui_static() );
if ( $preview ) {
	$raw = isset( $_GET['mns_state'] ) ? sanitize_key( wp_unslash( $_GET['mns_state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( MNS_Order_Status::is_valid( $raw ) && isset( $by[ $raw ] ) ) {
		$status = $raw;
	}
}
$d      = isset( $by[ $status ] ) && is_array( $by[ $status ] ) ? $by[ $status ] : ( $by[ $default ] ?? array() );
$status = (string) ( $d['status'] ?? $status );

$arr = static function ( $value ): array {
	return is_array( $value ) ? $value : array();
};

$list_url   = MNS_Pages::panel_url( 'nurse', 'orders' );
$view_url   = MNS_Pages::panel_url( 'nurse', 'order-view' );
$code       = (string) ( $d['tracking'] ?? '' );
$steps      = $arr( $d['steps'] ?? array() );
$needs_drug = ! empty( $d['needs_drug'] );
$restricted = ! empty( $d['restricted'] );
$finance    = $arr( $d['finance'] ?? array() );
$essentials = $arr( $d['essentials'] ?? array() );
$session    = $arr( $d['session'] ?? array() );
$timeline   = $arr( $d['timeline'] ?? array() );

$state_url = static function ( string $to ) use ( $view_url, $code ): string {
	return add_query_arg(
		array(
			'mns_order' => $code,
			'mns_state' => $to,
		),
		$view_url
	);
};

$phase_map     = array(
	'assigned'      => 'draft',
	'in_progress'   => 'draft',
	'medication'    => 'draft',
	'service_ended' => 'ended',
	'invoiced'      => 'submitted',
	'paid'          => 'submitted',
	'completed'     => 'submitted',
);
$checklist_url = add_query_arg(
	array(
		'mns_order'   => $code,
		'mns_phase'   => $phase_map[ $status ] ?? 'draft',
		'mns_service' => 'serum',
	),
	MNS_Pages::panel_url( 'nurse', 'checklist' )
);

$report_url = add_query_arg(
	array(
		'mns_order'   => $code,
		'mns_phase'   => in_array( $status, array( 'service_ended' ), true ) ? 'open' : ( in_array( $status, array( 'invoiced', 'paid', 'completed' ), true ) ? 'submitted' : 'early' ),
		'mns_service' => 'serum',
	),
	MNS_Pages::panel_url( 'nurse', 'report' )
);

$drug_url = add_query_arg(
	array(
		'mns_order' => $code,
		'mns_phase' => in_array( $status, array( 'in_progress', 'medication' ), true ) ? 'draft' : ( 'service_ended' === $status ? 'ended' : ( in_array( $status, array( 'invoiced', 'paid', 'completed' ), true ) ? 'submitted' : 'early' ) ),
	),
	MNS_Pages::panel_url( 'nurse', 'drug' )
);

$hh   = static function ( $h ): string {
	return MNS_Format::digits( sprintf( '%02d:00', (int) $h ) );
};
$slot = MNS_Format::human( (string) ( $d['date'] ?? '' ), false ) . ' ' . $hh( $d['from'] ?? 0 ) . ' ' . __( 'تا', 'medyar-nursing-services' ) . ' ' . $hh( $d['to'] ?? 0 );

$session_tag = '';
if ( count( $session ) >= 2 ) {
	/* translators: 1: session number, 2: total sessions */
	$session_tag = sprintf( __( 'نوبت %1$s از %2$s', 'medyar-nursing-services' ), MNS_Format::digits( (string) $session[0] ), MNS_Format::digits( (string) $session[1] ) );
}

$status_info = MNS_Order_Status::get( $status );
$step_names  = array(
	'checklist' => __( 'چک‌لیست', 'medyar-nursing-services' ),
	'drug'      => __( 'مرحلهٔ دارو', 'medyar-nursing-services' ),
	'report'    => __( 'گزارش', 'medyar-nursing-services' ),
);

/* Next required step after the service ended: first one that is not final yet. */
$next_step = '';
foreach ( array( 'checklist', 'drug', 'report' ) as $key ) {
	if ( 'drug' === $key && ! $needs_drug ) {
		continue;
	}
	if ( 'submitted' !== (string) ( $steps[ $key ]['state'] ?? 'locked' ) ) {
		$next_step = $key;
		break;
	}
}

/* The step track: start → service → checklist → (medication) → report. */
$track  = array();
$s_done = in_array( $status, array( 'in_progress', 'medication', 'service_ended', 'invoiced', 'paid', 'completed' ), true );
$e_done = in_array( $status, array( 'service_ended', 'invoiced', 'paid', 'completed' ), true );
$muted  = in_array( $status, array( 'cancelled', 'needs_review' ), true );
$track[] = array( 'label' => __( 'شروع', 'medyar-nursing-services' ), 'state' => $muted ? 'todo' : ( $s_done ? 'done' : 'current' ) );
$track[] = array( 'label' => __( 'خدمت', 'medyar-nursing-services' ), 'state' => $muted ? 'todo' : ( $e_done ? 'done' : ( $s_done ? 'current' : 'todo' ) ) );
foreach ( array( 'checklist', 'drug', 'report' ) as $key ) {
	if ( 'drug' === $key && ! $needs_drug ) {
		continue;
	}
	$st = 'todo';
	if ( ! $muted && $e_done ) {
		$st = 'submitted' === (string) ( $steps[ $key ]['state'] ?? '' ) ? 'done' : ( $key === $next_step ? 'current' : 'todo' );
	}
	$track[] = array( 'label' => $step_names[ $key ], 'state' => $st );
}

/* One primary action per status. */
$act = array( 'kind' => 'none' );
if ( 'assigned' === $status ) {
	$can    = ! empty( $d['can_start'] );
	$reason = $can ? '' : sprintf(
		/* translators: %s: time */
		__( 'شروع از ساعت %s امروز ممکن است.', 'medyar-nursing-services' ),
		MNS_Format::time( (int) ( $d['start_ts'] ?? 0 ) - HOUR_IN_SECONDS )
	);
	$act    = array(
		'kind'    => 'confirm',
		'label'   => __( 'شروع خدمت', 'medyar-nursing-services' ),
		'enabled' => $can,
		'reason'  => $reason,
		'q'       => __( 'با شروع خدمت، بیمار دیگر نمی‌تواند سفارش را لغو کند. خدمت شروع شود؟', 'medyar-nursing-services' ),
		'yes'     => __( 'بله، شروع شود', 'medyar-nursing-services' ),
		'go'      => $state_url( 'in_progress' ),
	);
} elseif ( in_array( $status, array( 'in_progress', 'medication' ), true ) ) {
	$act = array(
		'kind'    => 'confirm',
		'label'   => __( 'پایان خدمت', 'medyar-nursing-services' ),
		'enabled' => true,
		'reason'  => '',
		'q'       => __( 'پایان خدمت ثبت شود؟ بعد از آن چک‌لیست و گزارش را تکمیل می‌کنید.', 'medyar-nursing-services' ),
		'yes'     => __( 'بله، پایان خدمت', 'medyar-nursing-services' ),
		'go'      => $state_url( 'service_ended' ),
	);
} elseif ( 'service_ended' === $status && '' !== $next_step ) {
	$act = array(
		'kind'  => 'step',
		/* translators: %s: step name */
		'label' => sprintf( __( 'ادامهٔ %s', 'medyar-nursing-services' ), $step_names[ $next_step ] ),
		'href'  => 'checklist' === $next_step ? $checklist_url : ( 'report' === $next_step ? $report_url : ( 'drug' === $next_step ? $drug_url : '' ) ),
	);
} elseif ( in_array( $status, array( 'cancelled', 'needs_review' ), true ) ) {
	$act = array( 'kind' => 'support' );
}

$render_action = static function ( string $suffix, string $part = 'all' ) use ( $act ): void {
	if ( 'confirm' === $act['kind'] ) :
		$cid = 'mns-nv-confirm-' . $suffix;
		?>
		<?php if ( 'confirm' !== $part ) : ?>
		<button
			type="button"
			class="mns-submit-btn"
			data-mns-confirm-open="<?php echo esc_attr( $cid ); ?>"
			data-mns-needs-net
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $cid ); ?>"
			<?php echo $act['enabled'] ? '' : ' disabled aria-describedby="mns-nv-reason-' . esc_attr( $suffix ) . '"'; ?>
		><?php echo esc_html( $act['label'] ); ?></button>
		<?php if ( '' !== $act['reason'] ) : ?>
			<p class="mns-nview__reason" id="mns-nv-reason-<?php echo esc_attr( $suffix ); ?>"><?php echo esc_html( $act['reason'] ); ?></p>
		<?php endif; ?>
		<?php endif; ?>
		<?php if ( 'btn' !== $part ) : ?>
		<div class="mns-collapse" id="<?php echo esc_attr( $cid ); ?>" data-mns-confirm inert>
			<div class="mns-collapse__inner">
				<div class="mns-nview__confirm">
					<p class="mns-nview__confirm-q"><?php echo esc_html( $act['q'] ); ?></p>
					<p class="mns-nview__confirm-err" role="alert" data-mns-confirm-error></p>
					<div class="mns-nview__confirm-actions">
						<button type="button" class="mns-submit-btn" data-mns-confirm-ok data-mns-go="<?php echo esc_url( $act['go'] ); ?>"><span><?php echo esc_html( $act['yes'] ); ?></span></button>
						<button type="button" class="mns-nview__btn" data-mns-confirm-cancel><?php echo esc_html__( 'انصراف', 'medyar-nursing-services' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<?php
	elseif ( 'step' === $act['kind'] && '' !== $act['href'] ) :
		?>
		<a class="mns-submit-btn mns-nview__link" href="<?php echo esc_url( $act['href'] ); ?>"><?php echo esc_html( $act['label'] ); ?></a>
		<?php
	elseif ( 'step' === $act['kind'] ) :
		?>
		<button type="button" class="mns-submit-btn" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'فرم این مرحله در گام بعدی ساخته می‌شود.', 'medyar-nursing-services' ); ?>"><?php echo esc_html( $act['label'] ); ?></button>
		<?php
	elseif ( 'support' === $act['kind'] ) :
		MNS_UI::support_call( array( 'variant' => 'solid' ) );
	endif;
};

/**
 * One step card (checklist / medication / report).
 */
$render_step = static function ( string $key, string $icon, string $desc ) use ( $steps, $step_names, $status, $arr, $checklist_url, $report_url, $drug_url ): void {
	$st    = $arr( $steps[ $key ] ?? array() );
	$state = (string) ( $st['state'] ?? 'locked' );
	$chips = array(
		'locked'    => array( __( 'قفل', 'medyar-nursing-services' ), 'is-locked' ),
		'todo'      => array( __( 'شروع‌نشده', 'medyar-nursing-services' ), 'is-todo' ),
		'draft'     => array( __( 'پیش‌نویس', 'medyar-nursing-services' ), 'is-draft' ),
		'submitted' => array( __( 'ثبت‌شده', 'medyar-nursing-services' ), 'is-done' ),
	);
	$chip  = $chips[ $state ] ?? $chips['locked'];
	$lock_text = '';
	if ( 'locked' === $state ) {
		$lock_text = 'report' === $key
			? __( 'بعد از پایان خدمت فعال می‌شود.', 'medyar-nursing-services' )
			: __( 'بعد از شروع خدمت فعال می‌شود.', 'medyar-nursing-services' );
	}
	$btn = '';
	if ( 'todo' === $state ) {
		/* translators: %s: step name */
		$btn = sprintf( __( 'شروع %s', 'medyar-nursing-services' ), $step_names[ $key ] );
	} elseif ( 'draft' === $state ) {
		/* translators: %s: step name */
		$btn = sprintf( __( 'ادامهٔ %s', 'medyar-nursing-services' ), $step_names[ $key ] );
	} elseif ( 'submitted' === $state ) {
		$btn = __( 'مشاهده', 'medyar-nursing-services' );
	}
	$pct = 0;
	if ( 'checklist' === $key && 'draft' === $state && ! empty( $st['total'] ) ) {
		$pct = (int) round( ( (int) $st['done'] / (int) $st['total'] ) * 100 );
	}
	?>
	<section class="mns-card mns-nview__step is-<?php echo esc_attr( $state ); ?>" aria-labelledby="mns-nv-<?php echo esc_attr( $key ); ?>">
		<div class="mns-nview__step-head">
			<span class="mns-nview__ico" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h2 class="mns-nview__step-title" id="mns-nv-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $step_names[ $key ] ); ?></h2>
			<span class="mns-nview__chip <?php echo esc_attr( $chip[1] ); ?>"><?php echo esc_html( $chip[0] ); ?></span>
		</div>
		<p class="mns-nview__step-desc"><?php echo esc_html( '' !== $lock_text ? $lock_text : $desc ); ?></p>
		<?php if ( 'draft' === $state && 'checklist' === $key ) : ?>
			<div class="mns-nview__meter" aria-hidden="true"><span style="width:<?php echo esc_attr( (string) $pct ); ?>%"></span></div>
			<p class="mns-nview__step-meta">
				<?php
				/* translators: 1: done, 2: total */
				echo esc_html( sprintf( __( '%1$s از %2$s مورد الزامی · ذخیره شد %3$s', 'medyar-nursing-services' ), MNS_Format::digits( (string) (int) $st['done'] ), MNS_Format::digits( (string) (int) $st['total'] ), MNS_Format::digits( '۲۱:۴۲' ) ) );
				?>
			</p>
		<?php endif; ?>
		<?php if ( 'draft' === $state && 'in_progress' === $status && 'checklist' === $key ) : ?>
			<p class="mns-nview__step-meta"><?php echo esc_html__( 'ثبت نهایی بعد از پایان خدمت ممکن است.', 'medyar-nursing-services' ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $btn && 'checklist' === $key ) : ?>
			<a class="mns-nview__btn<?php echo 'submitted' === $state ? '' : ' mns-nview__btn--primary'; ?>" href="<?php echo esc_url( $checklist_url ); ?>"><?php echo esc_html( $btn ); ?></a>
		<?php elseif ( '' !== $btn && 'drug' === $key ) : ?>
			<a class="mns-nview__btn<?php echo 'submitted' === $state ? '' : ' mns-nview__btn--primary'; ?>" href="<?php echo esc_url( $drug_url ); ?>"><?php echo esc_html( $btn ); ?></a>
		<?php elseif ( '' !== $btn && 'report' === $key ) : ?>
			<a class="mns-nview__btn<?php echo 'submitted' === $state ? '' : ' mns-nview__btn--primary'; ?>" href="<?php echo esc_url( $report_url ); ?>"><?php echo esc_html( $btn ); ?></a>
		<?php elseif ( '' !== $btn ) : ?>
			<button type="button" class="mns-nview__btn<?php echo 'submitted' === $state ? '' : ' mns-nview__btn--primary'; ?>" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'فرم این مرحله در گام بعدی ساخته می‌شود.', 'medyar-nursing-services' ); ?>"><?php echo esc_html( $btn ); ?></button>
		<?php endif; ?>
	</section>
	<?php
};

$after_end = in_array( $status, array( 'service_ended', 'invoiced', 'paid', 'completed' ), true );
?>
<div class="mns-nview<?php echo $after_end ? ' is-after' : ''; ?>" data-mns-ndash>
	<p class="mns-breadcrumb mns-nview__back">
		<a href="<?php echo esc_url( $list_url ); ?>">
			<span class="mns-breadcrumb__icon" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php echo esc_html__( 'سفارش‌های من', 'medyar-nursing-services' ); ?>
		</a>
	</p>

	<?php if ( $preview ) : ?>
		<nav class="mns-nview__pv" aria-label="<?php echo esc_attr__( 'پیش‌نمایش وضعیت سفارش (فقط محیط توسعه)', 'medyar-nursing-services' ); ?>">
			<span class="mns-nview__pv-label"><?php echo esc_html__( 'پیش‌نمایش وضعیت', 'medyar-nursing-services' ); ?></span>
			<span class="mns-nview__pv-list">
				<?php foreach ( array_keys( $by ) as $slug ) : ?>
					<a
						class="mns-nview__pv-item<?php echo (string) $slug === $status ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( add_query_arg( array( 'mns_order' => $code, 'mns_state' => (string) $slug ), $view_url ) ); ?>"
						<?php echo (string) $slug === $status ? 'aria-current="true"' : ''; ?>
					><?php echo esc_html( (string) MNS_Order_Status::get( (string) $slug )['label'] ); ?></a>
				<?php endforeach; ?>
			</span>
		</nav>
	<?php endif; ?>

	<div class="mns-collapse mns-nview__net" data-mns-net inert>
		<div class="mns-collapse__inner">
			<p class="mns-nview__netmsg" role="status"><?php echo esc_html__( 'اتصال برقرار نیست؛ تا وصل شدن دوباره، ثبت شروع و پایان خدمت ممکن نیست.', 'medyar-nursing-services' ); ?></p>
		</div>
	</div>

	<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

	<section class="mns-card mns-nview__head" aria-labelledby="mns-nv-title">
		<div class="mns-nview__head-top">
			<span class="mns-nview__slot"><?php echo esc_html( $slot ); ?></span>
			<span class="mns-ostatus mns-ostatus--<?php echo esc_attr( $status ); ?>">
				<span class="mns-ostatus__dot" aria-hidden="true"></span>
				<span class="mns-ostatus__label"><?php echo esc_html( (string) $status_info['label'] ); ?></span>
			</span>
		</div>
		<h1 class="mns-nview__title" id="mns-nv-title"><?php echo esc_html( (string) ( $d['service'] ?? '' ) ); ?></h1>
		<div class="mns-nview__tags">
			<?php if ( '' !== $session_tag ) : ?>
				<span class="mns-nview__tag"><?php echo esc_html( $session_tag ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $d['urgent'] ) ) : ?>
				<span class="mns-nview__tag mns-nview__tag--urgent"><?php echo esc_html__( 'فوری', 'medyar-nursing-services' ); ?></span>
			<?php endif; ?>
			<button type="button" class="mns-nview__tag mns-nview__tag--code" dir="ltr" data-mns-copy="<?php echo esc_attr( $code ); ?>" aria-label="<?php echo esc_attr__( 'کپی کد پیگیری', 'medyar-nursing-services' ); ?>">
				<?php echo esc_html( MNS_Format::digits( $code ) ); ?>
				<span aria-hidden="true"><?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</button>
		</div>

		<?php if ( ! empty( $d['changed'] ) && ! empty( $d['time_changed'] ) ) : ?>
			<div class="mns-collapse is-open mns-nview__note-wrap" data-mns-note>
				<div class="mns-collapse__inner">
					<div class="mns-nview__note is-warn">
						<span class="mns-nview__note-ico" aria-hidden="true"><?php echo mns_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-nview__note-text">
							<b><?php echo esc_html__( 'زمان این سفارش تغییر کرد', 'medyar-nursing-services' ); ?></b>
							<small><?php echo esc_html( (string) $d['time_changed']['from'] ); ?> ← <?php echo esc_html( (string) $d['time_changed']['to'] ); ?></small>
						</span>
						<button type="button" class="mns-nview__btn mns-nview__btn--sm" data-mns-note-dismiss><?php echo esc_html__( 'دیدم', 'medyar-nursing-services' ); ?></button>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( 'cancelled' === $status ) : ?>
			<div class="mns-nview__note">
				<span class="mns-nview__note-ico" aria-hidden="true"><?php echo mns_icon( 'x-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-nview__note-text">
					<b>
						<?php
						/* translators: %s: who cancelled */
						echo esc_html( sprintf( __( 'این سفارش توسط %s لغو شد', 'medyar-nursing-services' ), (string) ( $d['cancelled_by'] ?? '' ) ) );
						?>
					</b>
					<small><?php echo esc_html__( 'اقدامی لازم نیست.', 'medyar-nursing-services' ); ?></small>
				</span>
			</div>
		<?php elseif ( 'needs_review' === $status ) : ?>
			<div class="mns-nview__note is-warn">
				<span class="mns-nview__note-ico" aria-hidden="true"><?php echo mns_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-nview__note-text">
					<b><?php echo esc_html__( 'این سفارش در حال بررسی همکاران مدیار است', 'medyar-nursing-services' ); ?></b>
					<small><?php echo esc_html( (string) ( $d['review_why'] ?? '' ) ); ?></small>
				</span>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $track ) ) : ?>
			<ol class="mns-nview__track" aria-label="<?php echo esc_attr__( 'مراحل این خدمت', 'medyar-nursing-services' ); ?>">
				<?php foreach ( $track as $t ) : ?>
					<li class="mns-nview__track-step is-<?php echo esc_attr( $t['state'] ); ?>"<?php echo 'current' === $t['state'] ? ' aria-current="step"' : ''; ?>>
						<span class="mns-nview__track-bar" aria-hidden="true"></span>
						<span class="mns-nview__track-label"><?php echo esc_html( $t['label'] ); ?></span>
						<?php if ( 'done' === $t['state'] ) : ?>
							<span class="screen-reader-text"><?php echo esc_html__( 'انجام شده', 'medyar-nursing-services' ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<?php if ( 'none' !== $act['kind'] ) : ?>
			<div class="mns-nview__action mns-nview__action--head<?php echo 'support' === $act['kind'] ? ' is-support' : ''; ?>"><?php $render_action( 'd', 'btn' ); ?></div>
			<?php if ( 'confirm' === $act['kind'] ) : ?>
				<div class="mns-nview__confirm-row"><?php $render_action( 'd', 'confirm' ); ?></div>
			<?php endif; ?>
		<?php endif; ?>
	</section>

	<div class="mns-nview__cols">
		<div class="mns-nview__side">
			<section class="mns-card mns-nview__person" aria-labelledby="mns-nv-person">
				<div class="mns-nview__person-head">
					<span class="mns-nview__ico" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<div class="mns-nview__person-text">
						<h2 class="mns-nview__person-name" id="mns-nv-person"><?php echo esc_html( (string) ( $d['patient'] ?? '' ) ); ?></h2>
						<span class="mns-nview__person-meta"><?php echo esc_html( ( $restricted ? '' : (string) ( $d['patient_meta'] ?? '' ) . ' · ' ) . (string) ( $d['area'] ?? '' ) ); ?></span>
					</div>
				</div>
				<?php if ( $restricted ) : ?>
					<p class="mns-nview__hint"><?php echo esc_html__( 'تلفن و آدرس بیمار بعد از تکمیل یا لغو سفارش حذف می‌شود.', 'medyar-nursing-services' ); ?></p>
				<?php else : ?>
					<?php if ( ! empty( $d['phone'] ) ) : ?>
						<p class="mns-nview__phone" dir="ltr"><?php echo esc_html( MNS_Format::mobile( (string) $d['phone'] ) ); ?></p>
					<?php endif; ?>
					<div class="mns-nview__addr">
						<span class="mns-nview__addr-ico" aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-nview__addr-text"><?php echo esc_html( (string) ( $d['address'] ?? '' ) ); ?></span>
						<button type="button" class="mns-nview__copy" data-mns-copy="<?php echo esc_attr( (string) ( $d['address'] ?? '' ) ); ?>" aria-label="<?php echo esc_attr__( 'کپی آدرس', 'medyar-nursing-services' ); ?>">
							<?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					</div>
					<div class="mns-nview__pair">
						<?php if ( ! empty( $d['phone'] ) ) : ?>
							<a class="mns-nview__btn mns-nview__btn--solid" href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', (string) $d['phone'] ) ); ?>">
								<span aria-hidden="true"><?php echo mns_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php echo esc_html__( 'تماس', 'medyar-nursing-services' ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $d['map_url'] ) ) : ?>
							<a class="mns-nview__btn" href="<?php echo esc_url( (string) $d['map_url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<span aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php echo esc_html__( 'مسیریابی', 'medyar-nursing-services' ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</section>

			<?php if ( ! $restricted && ( ! empty( $essentials ) || ! empty( $d['notes'] ) ) ) : ?>
				<section class="mns-card mns-nview__ess" aria-labelledby="mns-nv-ess">
					<h2 class="mns-nview__ess-title" id="mns-nv-ess">
						<span aria-hidden="true"><?php echo mns_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php echo esc_html__( 'قبل از رفتن بخوانید', 'medyar-nursing-services' ); ?>
					</h2>
					<?php if ( ! empty( $essentials ) ) : ?>
						<ul class="mns-nview__ess-chips">
							<?php foreach ( $essentials as $e ) : ?>
								<li class="mns-nview__ess-chip is-<?php echo esc_attr( (string) ( $e['tone'] ?? 'info' ) ); ?>"><?php echo esc_html( (string) ( $e['label'] ?? '' ) ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( ! empty( $d['notes'] ) ) : ?>
						<p class="mns-nview__ess-note">
							<b><?php echo esc_html__( 'توضیح بیمار:', 'medyar-nursing-services' ); ?></b>
							<?php echo esc_html( (string) $d['notes'] ); ?>
						</p>
					<?php endif; ?>
				</section>
			<?php endif; ?>
		</div>

		<div class="mns-nview__main">
			<?php if ( ! in_array( $status, array( 'cancelled', 'needs_review' ), true ) ) : ?>
				<?php
				$render_step( 'checklist', 'doc-list', __( 'خدمات انجام‌شده، علائم و اقلام مصرفی را ثبت کنید.', 'medyar-nursing-services' ) );
				if ( $needs_drug ) {
					$render_step( 'drug', 'pill', __( 'مشاوره با پزشک قبل از دادن دارو ثبت می‌شود.', 'medyar-nursing-services' ) );
				}
				$render_step( 'report', 'file-text', __( 'بعد از ثبت گزارش، فاکتور صادر و سهم شما محاسبه می‌شود.', 'medyar-nursing-services' ) );
				?>
			<?php endif; ?>

			<?php if ( ! empty( $finance ) ) : ?>
				<section class="mns-card mns-nview__money" aria-labelledby="mns-nv-money">
					<h2 class="mns-nview__step-title" id="mns-nv-money"><?php echo esc_html__( 'سهم شما', 'medyar-nursing-services' ); ?></h2>
					<p class="mns-nview__share"><?php echo esc_html( MNS_Format::toman( $finance['share'] ?? 0 ) ); ?></p>
					<p class="mns-nview__hint"><?php echo esc_html__( '۹۰٪ از مبلغ نهایی فاکتور', 'medyar-nursing-services' ); ?> · <?php echo esc_html( MNS_Format::toman( $finance['total'] ?? 0 ) ); ?></p>
					<?php if ( 'paid' === ( $finance['state'] ?? '' ) ) : ?>
						<p class="mns-nview__paid">
							<span aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php
							/* translators: 1: date, 2: transaction code */
							echo esc_html( sprintf( __( 'به کیف پول شما اضافه شد · %1$s · کد %2$s', 'medyar-nursing-services' ), (string) ( $finance['paid_at'] ?? '' ), MNS_Format::digits( (string) ( $finance['tx'] ?? '' ) ) ) );
							?>
						</p>
					<?php else : ?>
						<p class="mns-nview__wait">
							<span aria-hidden="true"><?php echo mns_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php echo esc_html__( 'در انتظار پرداخت بیمار؛ بعد از پرداخت به کیف پول شما اضافه می‌شود و تا آن موقع قابل برداشت نیست.', 'medyar-nursing-services' ); ?>
						</p>
					<?php endif; ?>
					<button type="button" class="mns-nview__fold" data-mns-fold aria-expanded="false" aria-controls="mns-nv-rows">
						<?php echo esc_html__( 'ردیف‌های فاکتور', 'medyar-nursing-services' ); ?>
						<span aria-hidden="true"><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</button>
					<div class="mns-collapse" id="mns-nv-rows" inert>
						<div class="mns-collapse__inner">
							<ul class="mns-nview__rows">
								<?php foreach ( $arr( $finance['rows'] ?? array() ) as $row ) : ?>
									<li><span><?php echo esc_html( (string) ( $row['label'] ?? '' ) ); ?></span><b><?php echo esc_html( MNS_Format::toman( $row['amount'] ?? 0 ) ); ?></b></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					<?php if ( 'paid' === ( $finance['state'] ?? '' ) ) : ?>
						<a class="mns-nview__btn mns-nview__btn--wide" href="<?php echo esc_url( class_exists( 'MTW_MyAccount_UI' ) ? MTW_MyAccount_UI::section_url( 'withdrawal' ) : '#' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'مشاهده در کیف پول', 'medyar-nursing-services' ); ?></a>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $timeline ) ) : ?>
				<section class="mns-card mns-nview__tl">
					<button type="button" class="mns-nview__fold" data-mns-fold aria-expanded="false" aria-controls="mns-nv-tl">
						<?php echo esc_html__( 'تایم‌لاین سفارش', 'medyar-nursing-services' ); ?>
						<span aria-hidden="true"><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</button>
					<div class="mns-collapse" id="mns-nv-tl" inert>
						<div class="mns-collapse__inner">
							<ol class="mns-nview__events">
								<?php foreach ( $timeline as $e ) : ?>
									<li><span><?php echo esc_html( (string) ( $e['label'] ?? '' ) ); ?></span><small><?php echo esc_html( MNS_Format::digits( (string) ( $e['at'] ?? '' ) ) ); ?></small></li>
								<?php endforeach; ?>
							</ol>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<section class="mns-card mns-nview__help">
				<h2 class="mns-nview__help-title"><?php echo esc_html__( 'مشکل دارم یا نیاز به تغییر', 'medyar-nursing-services' ); ?></h2>
				<p class="mns-nview__hint"><?php echo esc_html__( 'برای لغو، تغییر زمان، اصلاح اطلاعات ثبت‌شده یا هر مشکل دیگر با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ); ?></p>
				<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
			</section>
		</div>
	</div>

	<?php if ( in_array( $act['kind'], array( 'confirm', 'step' ), true ) ) : ?>
		<div class="mns-nview__bar">
			<?php $render_action( 'm' ); ?>
		</div>
	<?php endif; ?>
</div>
