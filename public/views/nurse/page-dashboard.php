<?php
/**
 * Nurse dashboard (P-26): what do I do now, what is next, how am I doing.
 *
 * Shares the `.mns-pdash` base with the patient dashboard; nurse-only blocks live under `.mns-ndash`.
 * One "now" card covers the whole life of a service: start → service → checklist → report.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data  = MNS_Static_Fixtures::get( 'nurse/dashboard' );
$state = MNS_Dev::state();
$arr   = static function ( $value ): array {
	return is_array( $value ) ? $value : array();
};

$first_name  = (string) ( $data['first_name'] ?? '' );
$accepting   = ! empty( $data['accepting'] );
$now_ts      = (int) ( $data['now_ts'] ?? time() );
$in_progress = $arr( $data['in_progress'] ?? array() );
$next        = $arr( $data['next'] ?? array() );
$unfinished  = array_values( $arr( $data['unfinished'] ?? array() ) );
$news        = $arr( $data['news'] ?? array() );
$agenda      = $arr( $data['agenda'] ?? array() );
$wallet      = $arr( $data['wallet'] ?? array() );
$rating      = $arr( $data['rating'] ?? array() );
$month       = $arr( $data['month'] ?? array() );
$week        = $arr( $data['week'] ?? array() );
$onboarding  = $arr( $data['onboarding'] ?? array() );
$done_today  = (int) ( $data['done_today'] ?? 0 );

$is_new       = 'new_nurse' === $state;
$is_suspended = 'suspended' === $state;

if ( 'ended' === $state ) {
	$in_progress = array();
}
if ( 'next' === $state ) {
	$in_progress = array();
	$unfinished  = array();
}
if ( 'offline' === $state ) {
	$accepting = false;
}
if ( 'empty' === $state || $is_new ) {
	$in_progress = array();
	$next        = array();
	$unfinished  = array();
	$news        = array();
	$agenda      = array();
	$done_today  = 0;
}
if ( $is_new ) {
	$accepting = false;
	$week      = array();
}

$orders_url  = MNS_Pages::panel_url( 'nurse', 'orders' );
$avail_url   = MNS_Pages::panel_url( 'nurse', 'availability' );
$ratings_url = MNS_Pages::panel_url( 'nurse', 'ratings' );
$account_url = function_exists( 'wc_get_page_permalink' ) ? (string) wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$wallet_url  = trailingslashit( $account_url ) . 'wallet/';
$withdraw    = class_exists( 'MTW_MyAccount_UI' ) ? MTW_MyAccount_UI::section_url( 'withdrawal' ) : $wallet_url . '?section=withdrawal';

/* Not wp_date(): the site's Jalali date plugin filters it, and these values are compared with machine dates. */
$now_dt    = ( new DateTimeImmutable( '@' . $now_ts ) )->setTimezone( wp_timezone() );
$today_ymd = $now_dt->format( 'Y-m-d' );

$hhmm = static function ( $hour ): string {
	return MNS_Format::digits( sprintf( '%02d:00', (int) $hour ) );
};
$slot = static function ( array $o ) use ( $hhmm ): string {
	/* translators: 1: window start time, 2: window end time */
	return sprintf( __( '%1$s تا %2$s', 'medyar-nursing-services' ), $hhmm( $o['from'] ?? 0 ), $hhmm( $o['to'] ?? 0 ) );
};
$who = static function ( array $o ): string {
	return implode( ' · ', array_filter( array( (string) ( $o['patient'] ?? '' ), (string) ( $o['area'] ?? '' ) ) ) );
};
$session_tag = static function ( array $o ): string {
	if ( empty( $o['session'] ) || ! is_array( $o['session'] ) || count( $o['session'] ) < 2 ) {
		return '';
	}
	/* translators: 1: session number, 2: total sessions */
	return sprintf( __( 'نوبت %1$s از %2$s', 'medyar-nursing-services' ), MNS_Format::digits( (string) $o['session'][0] ), MNS_Format::digits( (string) $o['session'][1] ) );
};

/* "Now": in progress → service ended with steps left → next service → nothing. */
$kind = 'none';
if ( ! empty( $in_progress ) ) {
	$kind = 'live';
} elseif ( ! empty( $unfinished ) ) {
	$kind = 'ended';
} elseif ( ! empty( $next ) ) {
	$kind = 'next';
}

/* Day plan: every pending order, ordered by start, max 5 rows. */
$plan = $agenda;
if ( ! empty( $in_progress ) ) {
	$plan[] = $in_progress;
}
if ( ! empty( $next ) ) {
	$plan[] = $next;
}
usort(
	$plan,
	static function ( $a, $b ): int {
		return (int) ( $a['start'] ?? 0 ) <=> (int) ( $b['start'] ?? 0 );
	}
);
$plan_all = $plan;
$plan     = array_slice( $plan, 0, 5 );

$day_counts = array();
foreach ( $plan as $row ) {
	$k                = (string) ( $row['date'] ?? '' );
	$day_counts[ $k ] = ( $day_counts[ $k ] ?? 0 ) + 1;
}
/* Cell state of the weekly grid: what is booked in which day + window. */
$busy_map = array();
foreach ( $plan_all as $row ) {
	$k = (string) ( $row['date'] ?? '' ) . '|' . (int) ( $row['from'] ?? 0 );
	if ( ! isset( $busy_map[ $k ] ) ) {
		$busy_map[ $k ] = array( 'n' => 0, 'status' => 'assigned' );
	}
	++$busy_map[ $k ]['n'];
	if ( 'in_progress' === ( $row['status'] ?? '' ) ) {
		$busy_map[ $k ]['status'] = 'in_progress';
	}
}

$today_n = 0;
foreach ( $plan as $row ) {
	if ( ( $row['date'] ?? '' ) === $today_ymd ) {
		++$today_n;
	}
}
$today_total = $today_n + $done_today;

if ( 0 === $today_total ) {
	$hello = __( 'امروز سفارشی ندارید.', 'medyar-nursing-services' );
} else {
	/* translators: %s: number of today's orders */
	$hello = sprintf( __( 'امروز %s سفارش دارید.', 'medyar-nursing-services' ), MNS_Format::digits( (string) $today_total ) );
}
if ( '' !== $first_name ) {
	/* translators: 1: first name, 2: sentence about today */
	$hello = sprintf( __( 'سلام %1$s، %2$s', 'medyar-nursing-services' ), $first_name, $hello );
}
$sub = '';
if ( $done_today > 0 ) {
	/* translators: %s: number of finished orders today */
	$sub = sprintf( __( '%s انجام شده', 'medyar-nursing-services' ), MNS_Format::digits( (string) $done_today ) );
}

$none_on  = __( 'سفارش جدید که تخصیص پیدا کند، همین‌جا می‌آید.', 'medyar-nursing-services' );
$none_off = __( 'پذیرش درخواست خاموش است؛ تا روشن نشود سفارش جدید نمی‌گیرید.', 'medyar-nursing-services' );

/**
 * Four-step track at the bottom of the "now" card: start → service → checklist (→ medication) → report.
 *
 * @param array<int,array{label:string,state:string}> $steps done|current|todo.
 */
$render_track = static function ( array $steps ): void {
	?>
	<ol class="mns-ndash__track" aria-label="<?php echo esc_attr__( 'مراحل این خدمت', 'medyar-nursing-services' ); ?>">
		<?php foreach ( $steps as $step ) : ?>
			<li class="mns-ndash__track-step is-<?php echo esc_attr( $step['state'] ); ?>"<?php echo 'current' === $step['state'] ? ' aria-current="step"' : ''; ?>>
				<span class="mns-ndash__track-bar" aria-hidden="true"></span>
				<span class="mns-ndash__track-label"><?php echo esc_html( $step['label'] ); ?></span>
				<?php if ( 'done' === $step['state'] ) : ?>
					<span class="screen-reader-text"><?php echo esc_html__( 'انجام شده', 'medyar-nursing-services' ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php
};

/**
 * Inline confirm that expands inside the card (no modal): question, error line, yes / cancel.
 */
$render_confirm = static function ( string $id, string $question, string $yes, string $go ): void {
	?>
	<div class="mns-collapse" id="<?php echo esc_attr( $id ); ?>" data-mns-confirm inert>
		<div class="mns-collapse__inner">
			<div class="mns-ndash__confirm">
				<p class="mns-ndash__confirm-q"><?php echo esc_html( $question ); ?></p>
				<p class="mns-ndash__confirm-err" role="alert" data-mns-confirm-error></p>
				<div class="mns-ndash__confirm-actions">
					<button type="button" class="mns-submit-btn" data-mns-confirm-ok data-mns-go="<?php echo esc_url( $go ); ?>">
						<span><?php echo esc_html( $yes ); ?></span>
					</button>
					<button type="button" class="mns-ndash__btn" data-mns-confirm-cancel>
						<?php echo esc_html__( 'انصراف', 'medyar-nursing-services' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php
};
?>
<div class="mns-pdash mns-ndash" data-mns-ndash>
	<?php
	MNS_UI::page_header(
		array(
			'title' => __( 'داشبورد', 'medyar-nursing-services' ),
			'icon'  => 'home',
		)
	);
	?>

	<?php if ( $is_suspended ) : ?>
		<section class="mns-card mns-ndash__now mns-ndash__now--none">
			<span class="mns-ndash__ico mns-ndash__ico--warn" aria-hidden="true"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div class="mns-ndash__none-text">
				<h2 class="mns-ndash__none-title"><?php echo esc_html__( 'حساب شما موقتاً غیرفعال است', 'medyar-nursing-services' ); ?></h2>
				<p><?php echo esc_html__( 'برای پیگیری با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ); ?></p>
			</div>
			<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
		</section>
	</div>
		<?php
		return;
	endif;
	?>

	<div class="mns-collapse mns-ndash__net" data-mns-net inert>
		<div class="mns-collapse__inner">
			<p class="mns-ndash__netmsg" role="status">
				<span aria-hidden="true"><?php echo mns_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php echo esc_html__( 'اتصال برقرار نیست؛ تا وصل شدن دوباره، ثبت شروع و پایان خدمت ممکن نیست.', 'medyar-nursing-services' ); ?>
			</p>
		</div>
	</div>

	<?php /* 1) My status: today in a line + the accept switch, changed right here (A-10). */ ?>
	<section class="mns-ndash__me" aria-label="<?php echo esc_attr__( 'وضعیت من', 'medyar-nursing-services' ); ?>">
		<div class="mns-ndash__hello-wrap">
			<p class="mns-ndash__hello"><?php echo esc_html( $hello ); ?></p>
			<?php if ( '' !== $sub ) : ?>
				<p class="mns-ndash__sub"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
		</div>
		<button
			type="button"
			class="mns-ndash__accept<?php echo $accepting ? ' is-on' : ''; ?>"
			role="switch"
			aria-checked="<?php echo $accepting ? 'true' : 'false'; ?>"
			data-mns-accept
			data-on-text="<?php echo esc_attr__( 'در دسترس هستم', 'medyar-nursing-services' ); ?>"
			data-off-text="<?php echo esc_attr__( 'درخواست جدید نمی‌گیرم', 'medyar-nursing-services' ); ?>"
			data-fail-text="<?php echo esc_attr__( 'ثبت نشد، دوباره تلاش کنید.', 'medyar-nursing-services' ); ?>"
		>
			<span class="mns-ndash__accept-text">
				<span class="mns-ndash__accept-label"><?php echo esc_html__( 'پذیرش درخواست جدید', 'medyar-nursing-services' ); ?></span>
				<span class="mns-ndash__accept-state" data-mns-accept-state>
					<?php echo $accepting ? esc_html__( 'در دسترس هستم', 'medyar-nursing-services' ) : esc_html__( 'درخواست جدید نمی‌گیرم', 'medyar-nursing-services' ); ?>
				</span>
			</span>
			<span class="mns-ndash__switch" aria-hidden="true"></span>
		</button>
		<div class="mns-collapse mns-ndash__accept-more<?php echo $accepting ? '' : ' is-open'; ?>" data-mns-accept-hint<?php echo $accepting ? ' inert' : ''; ?>>
			<div class="mns-collapse__inner">
				<p class="mns-ndash__hint" data-mns-accept-hint-text data-default="<?php echo esc_attr__( 'سفارش‌های قبلی شما تغییری نمی‌کند.', 'medyar-nursing-services' ); ?>">
					<?php echo esc_html__( 'سفارش‌های قبلی شما تغییری نمی‌کند.', 'medyar-nursing-services' ); ?>
				</p>
			</div>
		</div>
	</section>

	<?php /* 2) Now: one card for the whole life of a service. One primary action. */ ?>
	<?php if ( 'live' === $kind ) : ?>
		<?php
		$steps = array(
			array( 'label' => __( 'شروع', 'medyar-nursing-services' ), 'state' => 'done' ),
			array( 'label' => __( 'خدمت', 'medyar-nursing-services' ), 'state' => 'current' ),
			array( 'label' => __( 'چک‌لیست', 'medyar-nursing-services' ), 'state' => 'todo' ),
		);
		if ( ! empty( $in_progress['needs_med'] ) ) {
			$steps[] = array( 'label' => __( 'دارو', 'medyar-nursing-services' ), 'state' => 'todo' );
		}
		$steps[] = array( 'label' => __( 'گزارش', 'medyar-nursing-services' ), 'state' => 'todo' );
		?>
		<section class="mns-card mns-ndash__now mns-ndash__now--navy" aria-labelledby="mns-now-title">
			<div class="mns-ndash__now-head">
				<?php MNS_UI::status_badge( 'in_progress' ); ?>
				<span class="mns-ndash__slotline"><?php echo esc_html( $slot( $in_progress ) ); ?></span>
			</div>
			<h2 class="mns-ndash__big" id="mns-now-title"><?php echo esc_html( (string) ( $in_progress['service'] ?? '' ) ); ?></h2>
			<p class="mns-ndash__who"><?php echo esc_html( $who( $in_progress ) ); ?></p>

			<?php if ( ! empty( $in_progress['needs_med'] ) ) : ?>
				<a class="mns-ndash__flag" href="<?php echo esc_url( (string) ( $in_progress['url'] ?? '#' ) ); ?>">
					<span aria-hidden="true"><?php echo mns_icon( 'pill' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><?php echo esc_html__( 'قبل از دادن دارو، مرحلهٔ دارو را تکمیل کنید.', 'medyar-nursing-services' ); ?></span>
				</a>
			<?php endif; ?>

			<?php $render_track( $steps ); ?>

			<div class="mns-ndash__actions mns-ndash__actions--two">
				<button type="button" class="mns-submit-btn" data-mns-confirm-open="mns-confirm-end" data-mns-needs-net aria-expanded="false" aria-controls="mns-confirm-end">
					<?php echo esc_html__( 'پایان خدمت', 'medyar-nursing-services' ); ?>
				</button>
				<a class="mns-ndash__btn" href="<?php echo esc_url( (string) ( $in_progress['url'] ?? '#' ) ); ?>">
					<?php echo esc_html__( 'جزئیات سفارش', 'medyar-nursing-services' ); ?>
				</a>
			</div>
			<?php
			$render_confirm(
				'mns-confirm-end',
				__( 'پایان خدمت ثبت شود؟ بعد از آن چک‌لیست و گزارش را تکمیل می‌کنید.', 'medyar-nursing-services' ),
				__( 'بله، پایان خدمت', 'medyar-nursing-services' ),
				(string) ( $in_progress['end_url'] ?? ( $in_progress['url'] ?? '#' ) )
			);
			?>
		</section>
	<?php elseif ( 'ended' === $kind ) : ?>
		<?php
		$row   = $unfinished[0];
		$steps = array(
			array( 'label' => __( 'شروع', 'medyar-nursing-services' ), 'state' => 'done' ),
			array( 'label' => __( 'خدمت', 'medyar-nursing-services' ), 'state' => 'done' ),
		);
		$found = false;
		foreach ( $arr( $row['steps'] ?? array() ) as $step ) {
			$st = 'done';
			if ( empty( $step['done'] ) ) {
				$st    = $found ? 'todo' : 'current';
				$found = true;
			}
			$steps[] = array( 'label' => (string) ( $step['label'] ?? '' ), 'state' => $st );
		}
		$more = count( $unfinished ) - 1;
		?>
		<section class="mns-card mns-ndash__now mns-ndash__now--navy" aria-labelledby="mns-now-title">
			<div class="mns-ndash__now-head">
				<span class="mns-status mns-status--warning">
					<span class="mns-status__icon" aria-hidden="true"><?php echo mns_icon( 'flag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="mns-status__label"><?php echo esc_html__( 'خدمت تمام شد', 'medyar-nursing-services' ); ?></span>
				</span>
				<?php if ( ! empty( $row['ended_at'] ) ) : ?>
					<span class="mns-ndash__slotline"><?php echo esc_html( MNS_Format::human( (string) $row['ended_at'] ) ); ?></span>
				<?php endif; ?>
			</div>
			<h2 class="mns-ndash__big" id="mns-now-title"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></h2>
			<p class="mns-ndash__who"><?php echo esc_html( (string) ( $row['patient'] ?? '' ) ); ?></p>
			<?php $render_track( $steps ); ?>
			<p class="mns-ndash__hint"><?php echo esc_html__( 'تا این مراحل تکمیل نشود، فاکتور و سهم شما صادر نمی‌شود.', 'medyar-nursing-services' ); ?></p>
			<div class="mns-ndash__actions mns-ndash__actions--two">
				<a class="mns-submit-btn mns-ndash__link" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
					<?php
					/* translators: %s: step name */
					echo esc_html( sprintf( __( 'تکمیل %s', 'medyar-nursing-services' ), (string) ( $row['next_step'] ?? '' ) ) );
					?>
				</a>
				<?php if ( $more > 0 ) : ?>
					<a class="mns-ndash__btn" href="<?php echo esc_url( $orders_url ); ?>">
						<?php
						/* translators: %s: number of other orders */
						echo esc_html( sprintf( __( '%s سفارش دیگر هم مانده', 'medyar-nursing-services' ), MNS_Format::digits( (string) $more ) ) );
						?>
					</a>
				<?php endif; ?>
			</div>
		</section>
	<?php elseif ( 'next' === $kind ) : ?>
		<?php
		$is_today  = ( $next['date'] ?? '' ) === $today_ymd;
		$open_ts   = (int) ( $next['start'] ?? 0 ) - HOUR_IN_SECONDS;
		$can_start = $is_today && $now_ts >= $open_ts;
		$reason    = '';
		if ( ! $can_start ) {
			$reason = $is_today
				/* translators: %s: time */
				? sprintf( __( 'شروع از ساعت %s امروز ممکن است.', 'medyar-nursing-services' ), MNS_Format::time( $open_ts ) )
				: __( 'شروع در روز خدمت ممکن است.', 'medyar-nursing-services' );
		}
		$tag = $session_tag( $next );
		?>
		<section class="mns-card mns-ndash__now mns-ndash__now--navy" aria-labelledby="mns-now-title">
			<div class="mns-ndash__now-head">
				<span class="mns-ndash__eyebrow"><?php echo esc_html__( 'خدمت بعدی', 'medyar-nursing-services' ); ?></span>
				<?php if ( '' !== $tag ) : ?>
					<span class="mns-ndash__tag"><?php echo esc_html( $tag ); ?></span>
				<?php endif; ?>
			</div>
			<h2 class="mns-ndash__big" id="mns-now-title">
				<?php echo esc_html( MNS_Format::human( (string) ( $next['date'] ?? '' ), false ) . ' ' . $slot( $next ) ); ?>
			</h2>
			<p class="mns-ndash__who"><?php echo esc_html( implode( ' · ', array_filter( array( (string) ( $next['service'] ?? '' ), $who( $next ) ) ) ) ); ?></p>
			<?php if ( ! empty( $next['address'] ) ) : ?>
				<p class="mns-ndash__addr">
					<span aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><?php echo esc_html( (string) $next['address'] ); ?></span>
				</p>
			<?php endif; ?>

			<div class="mns-ndash__actions mns-ndash__actions--four">
				<?php if ( ! empty( $next['map_url'] ) ) : ?>
					<a class="mns-ndash__btn" href="<?php echo esc_url( (string) $next['map_url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<span aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php echo esc_html__( 'مسیریابی', 'medyar-nursing-services' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $next['phone'] ) ) : ?>
					<a class="mns-ndash__btn" href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', (string) $next['phone'] ) ); ?>">
						<span aria-hidden="true"><?php echo mns_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php echo esc_html__( 'تماس با بیمار', 'medyar-nursing-services' ); ?>
					</a>
				<?php endif; ?>
				<a class="mns-ndash__btn" href="<?php echo esc_url( (string) ( $next['url'] ?? '#' ) ); ?>">
					<?php echo esc_html__( 'جزئیات سفارش', 'medyar-nursing-services' ); ?>
				</a>
				<button
					type="button"
					class="mns-submit-btn"
					data-mns-confirm-open="mns-confirm-start"
					data-mns-needs-net
					aria-expanded="false"
					aria-controls="mns-confirm-start"
					<?php echo $can_start ? '' : ' disabled aria-describedby="mns-start-reason"'; ?>
				>
					<?php echo esc_html__( 'شروع خدمت', 'medyar-nursing-services' ); ?>
				</button>
			</div>
			<?php if ( '' !== $reason ) : ?>
				<p class="mns-ndash__hint mns-ndash__reason" id="mns-start-reason"><?php echo esc_html( $reason ); ?></p>
			<?php endif; ?>
			<?php
			$render_confirm(
				'mns-confirm-start',
				__( 'با شروع خدمت، بیمار دیگر نمی‌تواند سفارش را لغو کند. خدمت شروع شود؟', 'medyar-nursing-services' ),
				__( 'بله، شروع شود', 'medyar-nursing-services' ),
				(string) ( $next['start_url'] ?? ( $next['url'] ?? '#' ) )
			);
			?>
		</section>
	<?php elseif ( ! $is_new ) : ?>
		<?php
		$tomorrow = $arr( $data['tomorrow'] ?? array() );
		$left_msg = $none_on;
		?>
		<section class="mns-card mns-ndash__now mns-ndash__now--none" aria-labelledby="mns-now-title">
			<span class="mns-ndash__ico mns-ndash__ico--ok" aria-hidden="true"><?php echo mns_icon( $done_today > 0 ? 'check-circle' : 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<div class="mns-ndash__none-text">
				<h2 class="mns-ndash__none-title" id="mns-now-title">
					<?php echo $done_today > 0 ? esc_html__( 'همهٔ سفارش‌های امروز تمام شد', 'medyar-nursing-services' ) : esc_html__( 'در حال حاضر سفارشی ندارید', 'medyar-nursing-services' ); ?>
				</h2>
				<?php if ( ! empty( $tomorrow['text'] ) ) : ?>
					<p><?php echo esc_html( (string) $tomorrow['text'] ); ?></p>
				<?php else : ?>
					<p data-mns-none-text data-on="<?php echo esc_attr( $none_on ); ?>" data-off="<?php echo esc_attr( $none_off ); ?>"><?php echo esc_html( $accepting ? $left_msg : $none_off ); ?></p>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php /* New nurse: three steps before the first order can arrive. */ ?>
	<?php if ( $is_new && ! empty( $onboarding ) ) : ?>
		<section class="mns-card mns-ndash__start" aria-labelledby="mns-start-title">
			<?php
			MNS_UI::section_head(
				array(
					'title'    => __( 'سه قدم تا اولین سفارش', 'medyar-nursing-services' ),
					'title_id' => 'mns-start-title',
				)
			);
			?>
			<ol class="mns-ndash__guide">
				<?php foreach ( $onboarding as $i => $step ) : ?>
					<?php $done = ! empty( $step['done'] ); ?>
					<li class="mns-ndash__guide-step<?php echo $done ? ' is-done' : ''; ?>">
						<span class="mns-ndash__guide-dot" aria-hidden="true">
							<?php
							if ( $done ) {
								echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								echo esc_html( MNS_Format::digits( (string) ( $i + 1 ) ) );
							}
							?>
						</span>
						<span class="mns-ndash__row-main">
							<span class="mns-ndash__row-title"><?php echo esc_html( (string) ( $step['title'] ?? '' ) ); ?></span>
							<?php if ( ! empty( $step['note'] ) ) : ?>
								<span class="mns-ndash__row-meta"><?php echo esc_html( (string) $step['note'] ); ?></span>
							<?php endif; ?>
						</span>
						<?php if ( ! empty( $step['url'] ) ) : ?>
							<a class="mns-ndash__btn mns-ndash__btn--sm" href="<?php echo esc_url( (string) $step['url'] ); ?>">
								<?php echo $done ? esc_html__( 'ویرایش', 'medyar-nursing-services' ) : esc_html__( 'تنظیم', 'medyar-nursing-services' ); ?>
							</a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php /* 3) Change notices: slim rows, "seen" folds the row away. */ ?>
	<?php if ( ! empty( $news ) ) : ?>
		<section class="mns-collapse is-open mns-ndash__news" aria-label="<?php echo esc_attr__( 'تغییرهای اخیر', 'medyar-nursing-services' ); ?>" data-mns-news>
			<div class="mns-collapse__inner">
				<ul class="mns-card mns-ndash__news-list">
					<?php foreach ( $news as $item ) : ?>
						<li class="mns-collapse is-open" data-mns-news-item>
							<div class="mns-collapse__inner">
								<div class="mns-ndash__news-row">
									<span class="mns-ndash__ico mns-ndash__ico--sm mns-ndash__ico--warm" aria-hidden="true"><?php echo mns_icon( (string) ( $item['icon'] ?? 'info' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<a class="mns-ndash__news-text" href="<?php echo esc_url( (string) ( $item['url'] ?? '#' ) ); ?>">
										<?php echo esc_html( (string) ( $item['text'] ?? '' ) ); ?>
										<small><?php echo esc_html( (string) ( $item['ago'] ?? '' ) ); ?></small>
									</a>
									<button type="button" class="mns-ndash__btn mns-ndash__btn--sm" data-mns-news-dismiss="<?php echo esc_attr( (string) ( $item['id'] ?? '' ) ); ?>">
										<?php echo esc_html__( 'دیدم', 'medyar-nursing-services' ); ?>
									</button>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php /* 4) Day plan: a chip per day, a coloured side bar per order (green = in progress, amber = still ahead today). */ ?>
	<?php if ( ! empty( $plan ) ) : ?>
		<section class="mns-card mns-ndash__agenda" aria-labelledby="mns-agenda-title">
			<?php
			MNS_UI::section_head(
				array(
					'title'      => __( 'برنامهٔ پیش‌رو', 'medyar-nursing-services' ),
					'title_id'   => 'mns-agenda-title',
					'link_label' => MNS_Copy::t( 'action.view_all' ),
					'link_url'   => $orders_url,
				)
			);
			$open_day = '';
			foreach ( $plan as $row ) :
				$day = (string) ( $row['date'] ?? '' );
				if ( $day !== $open_day ) :
					if ( '' !== $open_day ) {
						echo '</ul>';
					}
					$open_day = $day;
					$chip     = MNS_Format::human( $day, false );
					$full     = MNS_Format::jdate( $day, false );
					$count    = MNS_Format::digits( (string) (int) ( $day_counts[ $day ] ?? 0 ) );
					/* translators: %s: number of orders */
					$count_t = sprintf( __( '%s سفارش', 'medyar-nursing-services' ), $count );
					$meta    = $chip === $full ? $count_t : $full . ' · ' . $count_t;
					?>
					<div class="mns-ndash__day">
						<span class="mns-ndash__day-chip<?php echo $day === $today_ymd ? ' is-today' : ''; ?>"><?php echo esc_html( $chip ); ?></span>
						<span class="mns-ndash__day-meta"><?php echo esc_html( $meta ); ?></span>
					</div>
					<ul class="mns-ndash__rows">
					<?php
				endif;
				$tag  = $session_tag( $row );
				$live = 'in_progress' === ( $row['status'] ?? '' );
				$mod  = $live ? ' is-live' : ( $day === $today_ymd ? ' is-today' : '' );
				?>
				<li>
					<a class="mns-ndash__row<?php echo esc_attr( $mod ); ?>" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
						<span class="mns-ndash__slot">
							<b><?php echo esc_html( $hhmm( $row['from'] ?? 0 ) ); ?></b>
							<small><?php echo esc_html( sprintf( /* translators: %s: end time */ __( 'تا %s', 'medyar-nursing-services' ), $hhmm( $row['to'] ?? 0 ) ) ); ?></small>
						</span>
						<span class="mns-ndash__row-main">
							<span class="mns-ndash__row-title">
								<?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?>
								<?php if ( $live ) : ?>
									<span class="mns-ndash__tag mns-ndash__tag--live"><?php echo esc_html__( 'در حال انجام', 'medyar-nursing-services' ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $row['is_new'] ) ) : ?>
									<span class="mns-ndash__tag mns-ndash__tag--new"><?php echo esc_html__( 'جدید', 'medyar-nursing-services' ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $tag ) : ?>
									<span class="mns-ndash__tag"><?php echo esc_html( $tag ); ?></span>
								<?php endif; ?>
							</span>
							<span class="mns-ndash__row-meta">
								<?php if ( ! empty( $row['patient'] ) ) : ?>
									<span class="mns-ndash__meta-i"><span aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( (string) $row['patient'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $row['area'] ) ) : ?>
									<span class="mns-ndash__meta-i"><span aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( (string) $row['area'] ); ?></span>
								<?php endif; ?>
							</span>
						</span>
						<span class="mns-ndash__row-go" aria-hidden="true"><?php echo mns_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</a>
				</li>
				<?php
			endforeach;
			echo '</ul>';
			?>
		</section>
	<?php endif; ?>

	<?php /* 5) Wallet (full row), rating and month (two equal cards): one colour each. */ ?>
	<?php if ( ! $is_new && ( ! empty( $wallet ) || ! empty( $rating ) || ! empty( $month ) ) ) : ?>
		<section class="mns-ndash__stats" aria-label="<?php echo esc_attr__( 'خلاصهٔ عملکرد', 'medyar-nursing-services' ); ?>">
			<?php if ( ! empty( $wallet ) ) : ?>
				<?php $pend_n = (int) ( $wallet['pending_orders'] ?? 0 ); ?>
				<div class="mns-ndash__stat mns-ndash__stat--wallet">
					<span class="mns-ndash__stat-ico" aria-hidden="true"><?php echo mns_icon( 'wallet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="mns-ndash__stat-body">
						<span class="mns-ndash__stat-label"><?php echo esc_html__( 'قابل برداشت', 'medyar-nursing-services' ); ?></span>
						<span class="mns-ndash__stat-value"><?php echo esc_html( MNS_Format::toman( $wallet['available'] ?? 0 ) ); ?></span>
						<?php if ( $pend_n > 0 ) : ?>
							<span class="mns-ndash__stat-note">
								<?php
								/* translators: 1: amount, 2: number of orders */
								echo esc_html( sprintf( __( '+ %1$s در انتظار پرداخت بیمار (%2$s سفارش)', 'medyar-nursing-services' ), MNS_Format::toman( $wallet['pending_amount'] ?? 0 ), MNS_Format::digits( (string) $pend_n ) ) );
								?>
							</span>
						<?php endif; ?>
					</span>
					<a class="mns-ndash__stat-btn" href="<?php echo esc_url( $withdraw ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'برداشت', 'medyar-nursing-services' ); ?></a>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $rating ) ) : ?>
				<?php $score = (float) ( $rating['overall'] ?? 0 ); ?>
				<a class="mns-ndash__stat mns-ndash__stat--rating" href="<?php echo esc_url( $ratings_url ); ?>">
					<span class="mns-ndash__stat-head">
						<span class="mns-ndash__stat-ico" aria-hidden="true"><?php echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-ndash__stat-label"><?php echo esc_html__( 'امتیاز من', 'medyar-nursing-services' ); ?></span>
					</span>
					<span class="mns-ndash__stat-value">
						<?php echo esc_html( MNS_Format::digits( number_format( $score, 1 ) ) ); ?>
						<small><?php echo esc_html__( 'از ۵', 'medyar-nursing-services' ); ?></small>
					</span>
					<span class="mns-ndash__meter" aria-hidden="true"><span style="width:<?php echo esc_attr( (string) round( min( 100, max( 0, $score / 5 * 100 ) ) ) ); ?>%"></span></span>
					<span class="mns-ndash__stat-note">
						<?php
						/* translators: %s: number of services */
						echo esc_html( sprintf( __( '%s خدمت', 'medyar-nursing-services' ), MNS_Format::digits( (string) (int) ( $rating['count'] ?? 0 ) ) ) );
						?>
					</span>
				</a>
			<?php endif; ?>
			<?php if ( ! empty( $month ) ) : ?>
				<div class="mns-ndash__stat mns-ndash__stat--month">
					<span class="mns-ndash__stat-head">
						<span class="mns-ndash__stat-ico" aria-hidden="true"><?php echo mns_icon( 'activity' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-ndash__stat-label"><?php echo esc_html__( 'این ماه', 'medyar-nursing-services' ); ?></span>
					</span>
					<span class="mns-ndash__stat-value">
						<?php echo esc_html( MNS_Format::digits( (string) (int) ( $month['services'] ?? 0 ) ) ); ?>
						<small><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></small>
					</span>
					<span class="mns-ndash__stat-note">
						<?php
						/* translators: %s: cancel rate percent */
						echo esc_html( sprintf( __( 'نرخ لغو %s٪ · لغو کمتر، سفارش بیشتر', 'medyar-nursing-services' ), MNS_Format::digits( (string) (int) ( $month['cancel_rate'] ?? 0 ) ) ) );
						?>
					</span>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php /* 6) Weekly plan, read-only (set by support, decision 23): a day x window grid of this week. */ ?>
	<?php if ( ! $is_new ) : ?>
		<section class="mns-card mns-ndash__week" aria-labelledby="mns-week-title" data-mns-week>
			<?php
			MNS_UI::section_head(
				array(
					'title'      => __( 'برنامهٔ هفتگی', 'medyar-nursing-services' ),
					'title_id'   => 'mns-week-title',
					'link_label' => empty( $week ) ? '' : __( 'مشاهده', 'medyar-nursing-services' ),
					'link_url'   => empty( $week ) ? '' : $avail_url,
				)
			);
			?>
			<?php if ( empty( $week ) ) : ?>
				<p class="mns-ndash__hint"><?php echo esc_html__( 'برنامهٔ هفتگی شما هنوز تنظیم نشده است؛ تا پشتیبانی آن را تنظیم نکند، سفارش جدید نمی‌گیرید.', 'medyar-nursing-services' ); ?></p>
			<?php else : ?>
				<?php
				$today_i = ( (int) $now_dt->format( 'w' ) + 1 ) % 7;
				$sat     = $now_dt->setTime( 0, 0 )->modify( '-' . $today_i . ' days' );
				$months  = MNS_Format::month_names();
				$active  = 0;
				$h0      = 8;
				$h1      = 20;
				foreach ( $week as $d ) {
					foreach ( $arr( $d['slots'] ?? array() ) as $w ) {
						$h0 = min( $h0, (int) $w[0] );
						$h1 = max( $h1, (int) $w[1] );
						++$active;
					}
				}
				foreach ( $plan_all as $row ) {
					$h0 = min( $h0, (int) ( $row['from'] ?? 8 ) );
					$h1 = max( $h1, (int) ( $row['to'] ?? 20 ) );
				}
				$span  = max( 4, $h1 - $h0 );
				$ticks = range( $h0, $h1, 4 );
				$pct   = static function ( int $hour ) use ( $h0, $span ): string {
					return (string) round( ( $hour - $h0 ) / $span * 100, 3 );
				};
				$days_view = array();
				foreach ( $week as $i => $d ) {
					$date  = $sat->modify( '+' . $i . ' days' );
					$ymd   = $date->format( 'Y-m-d' );
					$jd    = MNS_Format::g2j( (int) $date->format( 'Y' ), (int) $date->format( 'n' ), (int) $date->format( 'j' ) );
					$items = array();
					foreach ( $plan_all as $row ) {
						if ( ( $row['date'] ?? '' ) === $ymd ) {
							$items[] = $row;
						}
					}
					$days_view[ $i ] = array(
						'name'  => (string) ( $d['day'] ?? '' ),
						'short' => (string) ( $d['short'] ?? '' ),
						'num'   => MNS_Format::digits( (string) $jd[2] ),
						'month' => (string) ( $months[ $jd[1] ] ?? '' ),
						'slots' => $arr( $d['slots'] ?? array() ),
						'items' => $items,
					);
				}
				?>
				<div class="mns-ndash__strip" role="group" aria-label="<?php echo esc_attr__( 'روزهای هفته', 'medyar-nursing-services' ); ?>">
					<?php foreach ( $days_view as $i => $v ) : ?>
						<?php
						$cls = 'mns-ndash__sd';
						if ( $i === $today_i ) {
							$cls .= ' is-today';
						}
						if ( $i === $today_i ) {
							$cls .= ' is-selected';
						}
						if ( empty( $v['slots'] ) && empty( $v['items'] ) ) {
							$cls .= ' is-off';
						}
						$dot = ! empty( $v['items'] ) ? 'is-busy' : ( ! empty( $v['slots'] ) ? 'is-open' : '' );
						?>
						<button type="button" class="<?php echo esc_attr( $cls ); ?>" aria-pressed="<?php echo $i === $today_i ? 'true' : 'false'; ?>" data-mns-week-day="<?php echo esc_attr( (string) $i ); ?>">
							<span class="mns-ndash__sd-w" aria-hidden="true"><?php echo esc_html( $v['short'] ); ?></span>
							<span class="mns-ndash__sd-n"><?php echo esc_html( $v['num'] ); ?></span>
							<span class="mns-ndash__sd-dot <?php echo esc_attr( $dot ); ?>" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php echo esc_html( $v['name'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="mns-ndash__dvs">
					<?php foreach ( $days_view as $i => $v ) : ?>
						<?php
						$sel   = $i === $today_i;
						$n_ord = count( $v['items'] );
						$title = ( $sel ? __( 'امروز', 'medyar-nursing-services' ) . '، ' : '' ) . $v['name'] . ' ' . $v['num'] . ' ' . $v['month'];
						if ( empty( $v['slots'] ) ) {
							$subt = $n_ord > 0
								? __( 'خارج از ساعت کاری', 'medyar-nursing-services' )
								: __( 'تعطیل', 'medyar-nursing-services' );
						} else {
							/* translators: %s: number of active windows */
							$subt = sprintf( __( '%s بازهٔ فعال', 'medyar-nursing-services' ), MNS_Format::digits( (string) count( $v['slots'] ) ) );
							if ( $n_ord > 0 ) {
								/* translators: %s: number of orders */
								$subt .= ' · ' . sprintf( __( '%s سفارش', 'medyar-nursing-services' ), MNS_Format::digits( (string) $n_ord ) );
							}
						}
						?>
						<div class="mns-ndash__dv<?php echo $sel ? ' is-selected' : ''; ?>" data-mns-week-panel="<?php echo esc_attr( (string) $i ); ?>"<?php echo $sel ? '' : ' inert'; ?>>
							<div class="mns-ndash__dv-head">
								<span class="mns-ndash__dv-title"><?php echo esc_html( $title ); ?></span>
								<span class="mns-ndash__dv-sub"><?php echo esc_html( $subt ); ?></span>
							</div>
							<?php if ( empty( $v['slots'] ) && empty( $v['items'] ) ) : ?>
								<p class="mns-ndash__dv-off"><?php echo esc_html__( 'این روز برنامه‌ای ندارید.', 'medyar-nursing-services' ); ?></p>
							<?php else : ?>
								<div class="mns-ndash__tl" style="--h:<?php echo esc_attr( (string) max( 144, $span * 18 ) ); ?>px">
									<?php foreach ( $ticks as $t ) : ?>
										<span class="mns-ndash__tl-line" style="--a:<?php echo esc_attr( $pct( (int) $t ) ); ?>%"></span>
										<span class="mns-ndash__tl-hour" style="--a:<?php echo esc_attr( $pct( (int) $t ) ); ?>%"><?php echo esc_html( MNS_Format::digits( sprintf( '%02d', $t ) ) ); ?></span>
									<?php endforeach; ?>
									<?php foreach ( $v['slots'] as $w ) : ?>
										<span class="mns-ndash__tl-win" style="--a:<?php echo esc_attr( $pct( (int) $w[0] ) ); ?>%;--s:<?php echo esc_attr( (string) round( ( (int) $w[1] - (int) $w[0] ) / $span * 100, 3 ) ); ?>%"></span>
									<?php endforeach; ?>
									<?php foreach ( $v['items'] as $row ) : ?>
										<?php $live = 'in_progress' === ( $row['status'] ?? '' ); ?>
										<a
											class="mns-ndash__tl-ev<?php echo $live ? ' is-live' : ''; ?>"
											href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>"
											style="--a:<?php echo esc_attr( $pct( (int) ( $row['from'] ?? 0 ) ) ); ?>%;--s:<?php echo esc_attr( (string) round( ( (int) ( $row['to'] ?? 0 ) - (int) ( $row['from'] ?? 0 ) ) / $span * 100, 3 ) ); ?>%"
										>
											<b><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?><?php echo $live ? ' · ' . esc_html__( 'در حال انجام', 'medyar-nursing-services' ) : ''; ?></b>
											<span><?php echo esc_html( $who( $row ) ); ?></span>
											<small><?php echo esc_html( $slot( $row ) ); ?></small>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<ul class="mns-ndash__legend" aria-hidden="true">
					<li><i class="is-open"></i><?php echo esc_html__( 'ساعت کاری', 'medyar-nursing-services' ); ?></li>
					<li><i class="is-busy"></i><?php echo esc_html__( 'سفارش', 'medyar-nursing-services' ); ?></li>
					<li><i class="is-live"></i><?php echo esc_html__( 'در حال انجام', 'medyar-nursing-services' ); ?></li>
				</ul>
				<p class="mns-ndash__hint mns-ndash__weeksum">
					<?php
					/* translators: %s: number of active windows */
					echo esc_html( sprintf( __( '%s بازهٔ فعال در این هفته', 'medyar-nursing-services' ), MNS_Format::digits( (string) $active ) ) );
					?>
					· <?php echo esc_html__( 'برنامه را پشتیبانی تنظیم می‌کند.', 'medyar-nursing-services' ); ?>
				</p>
			<?php endif; ?>
		</section>
	<?php endif; ?>
</div>
