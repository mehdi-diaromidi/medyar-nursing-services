<?php
/**
 * Nurse orders (P-27): same anatomy as the patient «my orders» page (header, tabs, search + more filters,
 * grouped rows, history table with pager) with the nurse's content: time, area, status, share, actions.
 *
 * Tabs, filters, search and paging work on the server-rendered rows (static UI); the real page will ask
 * the server for the same shapes.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data  = MNS_Static_Fixtures::get( 'nurse/orders' );
$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
$rate  = (float) ( $data['summary']['rating'] ?? 0 );

if ( MNS_Dev::is( 'empty' ) ) {
	$items = array();
}

$months_fa = MNS_Format::month_names();
$today_ymd = ( new DateTimeImmutable( 'today', wp_timezone() ) )->format( 'Y-m-d' );

foreach ( $items as $i => $it ) {
	$dt = MNS_Format::to_datetime( (string) ( $it['when'] ?? '' ) );
	$items[ $i ]['id']          = (string) $i;
	$items[ $i ]['ymd']         = $dt ? $dt->format( 'Y-m-d' ) : '';
	$items[ $i ]['month_key']   = '';
	$items[ $i ]['month_label'] = '';
	if ( $dt ) {
		$jd                         = MNS_Format::g2j( (int) $dt->format( 'Y' ), (int) $dt->format( 'n' ), (int) $dt->format( 'j' ) );
		$items[ $i ]['month_key']   = $jd[0] . '-' . sprintf( '%02d', $jd[1] );
		$items[ $i ]['month_label'] = ( $months_fa[ $jd[1] ] ?? '' ) . ' ' . MNS_Format::digits( (string) $jd[0] );
		$items[ $i ]['day_short']   = MNS_Format::digits( (string) $jd[2] ) . ' ' . ( $months_fa[ $jd[1] ] ?? '' );
		$items[ $i ]['time']        = MNS_Format::time( $dt->getTimestamp() );
	}
}

$counts = array(
	'upcoming' => 0,
	'pending'  => 0,
	'done'     => 0,
);
$months = array();
foreach ( $items as $it ) {
	$tab = (string) ( $it['tab'] ?? 'upcoming' );
	if ( isset( $counts[ $tab ] ) ) {
		++$counts[ $tab ];
	}
	if ( 'done' === $tab && '' !== $it['month_key'] ) {
		$months[ $it['month_key'] ] = $it['month_label'];
	}
}
krsort( $months );
$cur_month = (string) ( array_key_first( $months ) ?? '' );

/* Summary of the current month: money that arrived, money waiting for the patient, services done. */
$paid_sum    = 0;
$pending_sum = 0;
$pending_n   = 0;
$done_n      = 0;
foreach ( $items as $it ) {
	if ( 'done' !== ( $it['tab'] ?? '' ) || $it['month_key'] !== $cur_month ) {
		continue;
	}
	if ( in_array( (string) $it['status'], array( 'paid', 'completed' ), true ) ) {
		++$done_n;
	}
	if ( 'paid' === ( $it['share_state'] ?? '' ) ) {
		$paid_sum += (int) $it['share'];
	} elseif ( 'pending' === ( $it['share_state'] ?? '' ) ) {
		$pending_sum += (int) $it['share'];
		++$pending_n;
	}
}

$tabs = array(
	'upcoming' => __( 'پیش‌رو', 'medyar-nursing-services' ),
	'pending'  => __( 'کار مانده', 'medyar-nursing-services' ),
	'done'     => __( 'تمام‌شده', 'medyar-nursing-services' ),
);
$first_tab = 'upcoming';
if ( 0 === $counts['upcoming'] && $counts['pending'] > 0 ) {
	$first_tab = 'pending';
} elseif ( 0 === $counts['upcoming'] && 0 === $counts['pending'] && $counts['done'] > 0 ) {
	$first_tab = 'done';
}

$status_opts = array(
	''             => __( 'همه وضعیت‌ها', 'medyar-nursing-services' ),
	'completed'    => __( 'تکمیل‌شده', 'medyar-nursing-services' ),
	'invoiced'     => __( 'در انتظار پرداخت', 'medyar-nursing-services' ),
	'needs_review' => __( 'نیازمند بررسی', 'medyar-nursing-services' ),
	'cancelled'    => __( 'لغوشده', 'medyar-nursing-services' ),
);
$status_label = array(
	'assigned'      => __( 'تخصیص‌یافته', 'medyar-nursing-services' ),
	'in_progress'   => __( 'در حال انجام', 'medyar-nursing-services' ),
	'service_ended' => __( 'پایان خدمت', 'medyar-nursing-services' ),
	'invoiced'      => __( 'در انتظار پرداخت', 'medyar-nursing-services' ),
	'paid'          => __( 'تکمیل‌شده', 'medyar-nursing-services' ),
	'completed'     => __( 'تکمیل‌شده', 'medyar-nursing-services' ),
	'cancelled'     => __( 'لغوشده', 'medyar-nursing-services' ),
	'needs_review'  => __( 'نیازمند بررسی', 'medyar-nursing-services' ),
);

$support_tel = MNS_UI::support_tel_href();
$chevron_svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>';
$avail_url   = MNS_Pages::panel_url( 'nurse', 'availability' );

/**
 * Primary button + «⋯» menu of a row (the shape the patient page's order-actions partial takes as "resolved").
 *
 * @param array<string,mixed> $it Order.
 * @return array{primary:array<string,string>,menu:array<int,array<string,mixed>>,cancel_note:bool}
 */
$actions_for = static function ( array $it ) use ( $support_tel ): array {
	$status = (string) ( $it['status'] ?? 'assigned' );
	$url    = (string) ( $it['url'] ?? '#' );
	$menu   = array();
	if ( in_array( $status, array( 'assigned', 'in_progress' ), true ) ) {
		if ( ! empty( $it['map_url'] ) ) {
			$menu[] = array( 'url' => (string) $it['map_url'], 'icon' => 'map-pin', 'label' => __( 'مسیریابی', 'medyar-nursing-services' ), 'variant' => '', 'attrs' => array( 'target' => '_blank', 'rel' => 'noopener noreferrer' ) );
		}
		if ( ! empty( $it['phone'] ) ) {
			$menu[] = array( 'url' => 'tel:' . preg_replace( '/\D+/', '', (string) $it['phone'] ), 'icon' => 'phone', 'label' => __( 'تماس با بیمار', 'medyar-nursing-services' ), 'variant' => '', 'attrs' => array() );
		}
	}
	$menu[] = array( 'url' => $support_tel, 'icon' => 'headphones', 'label' => __( 'مشکل دارم / نیاز به تغییر', 'medyar-nursing-services' ), 'variant' => '', 'attrs' => array() );

	if ( 'in_progress' === $status ) {
		$primary = array( 'variant' => 'primary', 'url' => $url, 'icon' => '', 'label' => __( 'پایان خدمت', 'medyar-nursing-services' ) );
	} elseif ( 'service_ended' === $status ) {
		$primary = array(
			'variant' => 'pay',
			'url'     => $url,
			'icon'    => '',
			/* translators: %s: step name */
			'label'   => sprintf( __( 'تکمیل %s', 'medyar-nursing-services' ), (string) ( $it['next'] ?? '' ) ),
		);
	} else {
		$primary = array( 'variant' => 'outline', 'url' => $url, 'icon' => '', 'label' => __( 'جزئیات', 'medyar-nursing-services' ) );
	}
	return array(
		'primary'     => $primary,
		'menu'        => $menu,
		'cancel_note' => false,
	);
};

/**
 * Cells shared by the list row, the table row and the history card.
 *
 * @param array<string,mixed> $it Order.
 * @return array<string,mixed>
 */
$cells_for = static function ( array $it ) use ( $status_label, $today_ymd ): array {
	$ymd = (string) ( $it['ymd'] ?? '' );
	$rel = MNS_Format::human( (string) ( $it['when'] ?? '' ), false );
	if ( $ymd !== $today_ymd && ! in_array( $rel, array( __( 'فردا', 'medyar-nursing-services' ), __( 'دیروز', 'medyar-nursing-services' ) ), true ) ) {
		$rel = (string) ( $it['day_short'] ?? $rel );
	}
	$status  = (string) ( $it['status'] ?? 'assigned' );
	$session = '';
	if ( ! empty( $it['session'] ) && is_array( $it['session'] ) && count( $it['session'] ) >= 2 ) {
		/* translators: 1: session number, 2: total sessions */
		$session = sprintf( __( 'نوبت %1$s از %2$s', 'medyar-nursing-services' ), MNS_Format::digits( (string) $it['session'][0] ), MNS_Format::digits( (string) $it['session'][1] ) );
	}
	return array(
		'code'       => (string) ( $it['tracking'] ?? '' ),
		'service'    => (string) ( $it['service'] ?? '' ),
		'patient'    => (string) ( $it['patient'] ?? '' ),
		'area'       => (string) ( $it['area'] ?? '' ),
		'when_long'  => trim( $rel . ' · ' . (string) ( $it['time'] ?? '' ), ' ·' ),
		'when_short' => trim( $rel . ' ' . (string) ( $it['time'] ?? '' ) ),
		'status'     => $status,
		'label'      => (string) ( $status_label[ $status ] ?? '' ),
		'session'    => $session,
		'url'        => (string) ( $it['url'] ?? '#' ),
	);
};

/** Share cell: amount + a quiet hint line (paid / waiting for the patient) + the patient's rating. */
$render_amount = static function ( array $it, string $tag ): void {
	$state = (string) ( $it['share_state'] ?? 'none' );
	$tag   = 'td' === $tag ? 'td' : 'div';
	?>
	<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted. ?> class="mns-order__amount">
		<?php if ( 'none' === $state ) : ?>
			<span class="mns-order__none">–</span>
		<?php else : ?>
			<span class="mns-order__money<?php echo 'pending' === $state ? ' is-wait' : ''; ?>">
				<span class="mns-order__price"><?php echo esc_html( MNS_Format::number( (int) $it['share'] ) ); ?></span>
				<span class="mns-order__currency"><?php echo esc_html__( 'تومان', 'medyar-nursing-services' ); ?></span>
			</span>
			<span class="mns-order__hint<?php echo 'pending' === $state ? ' is-wait' : ' is-paid'; ?>">
				<?php echo 'pending' === $state ? esc_html__( 'پس از پرداخت بیمار', 'medyar-nursing-services' ) : esc_html__( 'واریز شد', 'medyar-nursing-services' ); ?>
				<?php if ( ! empty( $it['rating'] ) ) : ?>
					· <?php echo esc_html( MNS_Format::digits( rtrim( rtrim( number_format( (float) $it['rating'], 1 ), '0' ), '.' ) ) ); ?> ★
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
};

/** Area cell (the nurse's counterpart of the patient page's "nurse" column). */
$render_area = static function ( array $c, string $tag ): void {
	$tag = 'td' === $tag ? 'td' : 'div';
	?>
	<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted. ?> class="mns-order__nurse">
		<span class="mns-order__avatar" aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="mns-order__nurse-name"><?php echo esc_html( $c['area'] ); ?></span>
	</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
};

$render_status = static function ( array $c, string $tag ): void {
	$tag = 'td' === $tag ? 'td' : 'div';
	?>
	<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted. ?> class="mns-order__status">
		<span class="mns-ostatus mns-ostatus--<?php echo esc_attr( $c['status'] ); ?>">
			<span class="mns-ostatus__dot" aria-hidden="true"></span>
			<span class="mns-ostatus__label"><?php echo esc_html( $c['label'] ); ?></span>
		</span>
	</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
};

$row_attrs = static function ( array $it ): string {
	$group    = in_array( (string) $it['status'], array( 'paid', 'completed' ), true ) ? 'completed' : (string) $it['status'];
	$haystack = mb_strtolower( (string) ( $it['patient'] ?? '' ) . ' ' . (string) ( $it['service'] ?? '' ) . ' ' . (string) ( $it['area'] ?? '' ) . ' ' . (string) ( $it['tracking'] ?? '' ) );
	return sprintf(
		' data-mns-nord-row data-id="%1$s" data-tab="%2$s" data-status="%3$s" data-month="%4$s" data-recurring="%5$s" data-share="%6$s" data-text="%7$s" data-href="%8$s"',
		esc_attr( (string) $it['id'] ),
		esc_attr( (string) ( $it['tab'] ?? 'upcoming' ) ),
		esc_attr( $group ),
		esc_attr( (string) $it['month_key'] ),
		! empty( $it['session'] ) ? '1' : '0',
		esc_attr( 'none' === ( $it['share_state'] ?? 'none' ) ? '0' : (string) (int) ( $it['share'] ?? 0 ) ),
		esc_attr( $haystack ),
		esc_url( (string) ( $it['url'] ?? '#' ) )
	);
};

/**
 * One order as a list row / card (same markup as the patient page).
 *
 * @param array<string,mixed> $it    Order.
 * @param string              $group action|active|history.
 * @param string              $prefix Menu id prefix.
 */
$render_li = static function ( array $it, string $group, string $prefix ) use ( $cells_for, $actions_for, $render_amount, $render_area, $render_status, $row_attrs ): void {
	$c = $cells_for( $it );
	?>
	<li class="mns-order mns-order--<?php echo esc_attr( $group ); ?>"<?php echo $row_attrs( $it ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="mns-order__code">
			<span class="mns-order__code-text" dir="ltr"><?php echo esc_html( MNS_Format::digits( $c['code'] ) ); ?></span>
			<button type="button" class="mns-order__copy" data-mns-copy="<?php echo esc_attr( $c['code'] ); ?>" aria-label="<?php echo esc_attr__( 'کپی کد پیگیری', 'medyar-nursing-services' ); ?>">
				<?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
		<div class="mns-order__service">
			<a class="mns-order__title" href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['service'] ); ?></a>
		</div>
		<div class="mns-order__tags">
			<span class="mns-order__patient"><?php echo esc_html( $c['patient'] ); ?></span>
			<?php if ( '' !== $c['session'] ) : ?>
				<span class="mns-order__patient mns-order__session"><?php echo esc_html( $c['session'] ); ?></span>
			<?php endif; ?>
		</div>
		<div class="mns-order__when">
			<span class="mns-order__when-icon" aria-hidden="true"><?php echo mns_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="mns-order__when-short"><?php echo esc_html( $c['when_short'] ); ?></span>
			<span class="mns-order__when-long"><?php echo esc_html( $c['when_long'] ); ?></span>
		</div>
		<?php $render_area( $c, 'div' ); ?>
		<?php $render_status( $c, 'div' ); ?>
		<?php $render_amount( $it, 'div' ); ?>
		<?php MNS_UI::partial( 'order-actions', array( 'row' => $it, 'resolved' => $actions_for( $it ), 'menu_id' => $prefix . sanitize_html_class( $c['code'] ) ) ); ?>
	</li>
	<?php
};

/** Table row of the history card. */
$render_tr = static function ( array $it ) use ( $cells_for, $actions_for, $render_amount, $render_area, $render_status, $row_attrs ): void {
	$c = $cells_for( $it );
	?>
	<tr class="arp-table__row mns-otable__row"<?php echo $row_attrs( $it ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<td class="mns-order__code">
			<span class="mns-order__code-text" dir="ltr"><?php echo esc_html( MNS_Format::digits( $c['code'] ) ); ?></span>
		</td>
		<td class="arp-table__name">
			<a class="mns-order__title" href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['service'] ); ?></a>
			<span class="mns-otable__when"><?php echo esc_html( $c['when_long'] ); ?></span>
		</td>
		<td>
			<?php echo esc_html( $c['patient'] ); ?>
			<?php if ( '' !== $c['session'] ) : ?>
				<span class="mns-otable__when"><?php echo esc_html( $c['session'] ); ?></span>
			<?php endif; ?>
		</td>
		<?php $render_area( $c, 'td' ); ?>
		<?php $render_status( $c, 'td' ); ?>
		<?php $render_amount( $it, 'td' ); ?>
		<td class="mns-otable__actions">
			<?php MNS_UI::partial( 'order-actions', array( 'row' => $it, 'resolved' => $actions_for( $it ), 'menu_id' => 'mns-nt-' . sanitize_html_class( $c['code'] ) ) ); ?>
		</td>
	</tr>
	<?php
};

$list_head = static function (): void {
	?>
	<div class="mns-olist__head" aria-hidden="true">
		<span><?php echo esc_html__( 'کد پیگیری', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'سهم شما', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'اقدام', 'medyar-nursing-services' ); ?></span>
	</div>
	<?php
};

/* Groups of the main list: the pending tab is one "needs action" group, the upcoming tab one group per day. */
$upcoming_groups = array();
$pending_rows    = array();
$done_rows       = array();
foreach ( $items as $it ) {
	$tab = (string) ( $it['tab'] ?? 'upcoming' );
	if ( 'upcoming' === $tab ) {
		$upcoming_groups[ (string) $it['ymd'] ][] = $it;
	} elseif ( 'pending' === $tab ) {
		$pending_rows[] = $it;
	} else {
		$done_rows[] = $it;
	}
}
ksort( $upcoming_groups );
$day_title = static function ( string $ymd ): string {
	$h = MNS_Format::human( $ymd, false );
	$j = MNS_Format::jdate( $ymd, false );
	return $h === $j ? $j : $h . ' · ' . $j;
};

$no_orders = empty( $items );
?>
<section class="mns-orders mns-orders--nurse<?php echo $no_orders ? ' mns-orders--empty' : ''; ?>" data-mns-nord data-tab="<?php echo esc_attr( $first_tab ); ?>" aria-labelledby="mns-orders-title">
	<header class="mns-orders__head">
		<div class="mns-orders__intro">
			<h1 class="mns-orders__title" id="mns-orders-title">
				<span class="mns-orders__title-icon" aria-hidden="true"><?php echo mns_icon( 'doc-list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-orders__title-text"><?php echo esc_html__( 'سفارش‌های من', 'medyar-nursing-services' ); ?></span>
				<?php if ( ! $no_orders ) : ?>
					<span class="mns-orders__count"><?php echo esc_html( MNS_Format::digits( (string) count( $items ) ) ); ?></span>
				<?php endif; ?>
			</h1>
			<p class="mns-orders__lead"><?php echo esc_html__( 'سفارش‌های پیش‌رو، کار مانده و سهم خود را ببینید و همان‌جا ادامه دهید.', 'medyar-nursing-services' ); ?></p>
		</div>
		<a class="mns-obtn mns-obtn--primary mns-orders__cta" href="<?php echo esc_url( $avail_url ); ?>">
			<?php echo mns_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="mns-orders__cta-long"><?php echo esc_html__( 'برنامه و دسترسی', 'medyar-nursing-services' ); ?></span>
			<span class="mns-orders__cta-short"><?php echo esc_html__( 'برنامه', 'medyar-nursing-services' ); ?></span>
		</a>
	</header>

	<?php if ( $no_orders ) : ?>
		<div class="mns-orders-empty">
			<span class="mns-orders-empty__art" aria-hidden="true"><?php echo mns_icon( 'doc-list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h2 class="mns-orders-empty__title"><?php echo esc_html__( 'هنوز سفارشی ندارید', 'medyar-nursing-services' ); ?></h2>
			<p class="mns-orders-empty__text"><?php echo esc_html__( 'وقتی سفارشی به شما تخصیص یابد، اینجا می‌آید.', 'medyar-nursing-services' ); ?></p>
		</div>
	<?php else : ?>

		<section class="mns-nord__stats" aria-label="<?php echo esc_attr__( 'خلاصهٔ این ماه', 'medyar-nursing-services' ); ?>">
			<div class="mns-nord__stat mns-nord__stat--paid">
				<span class="mns-nord__stat-label"><?php echo esc_html__( 'واریز شده این ماه', 'medyar-nursing-services' ); ?></span>
				<span class="mns-nord__stat-value"><?php echo esc_html( MNS_Format::toman( $paid_sum ) ); ?></span>
			</div>
			<div class="mns-nord__stat mns-nord__stat--wait">
				<span class="mns-nord__stat-label"><?php echo esc_html__( 'در انتظار پرداخت بیمار', 'medyar-nursing-services' ); ?></span>
				<span class="mns-nord__stat-value">
					<?php echo esc_html( MNS_Format::toman( $pending_sum ) ); ?>
					<?php if ( $pending_n > 0 ) : ?>
						<small>
							<?php
							/* translators: %s: number of orders */
							echo esc_html( '· ' . sprintf( __( '%s سفارش', 'medyar-nursing-services' ), MNS_Format::digits( (string) $pending_n ) ) );
							?>
						</small>
					<?php endif; ?>
				</span>
			</div>
			<div class="mns-nord__stat mns-nord__stat--month">
				<span class="mns-nord__stat-label"><?php echo esc_html__( 'این ماه', 'medyar-nursing-services' ); ?></span>
				<span class="mns-nord__stat-value">
					<?php
					/* translators: %s: number of services */
					echo esc_html( sprintf( __( '%s خدمت', 'medyar-nursing-services' ), MNS_Format::digits( (string) $done_n ) ) );
					?>
					<?php if ( $rate > 0 ) : ?>
						<small>· <?php echo esc_html( MNS_Format::digits( number_format( $rate, 1 ) ) ); ?> ★</small>
					<?php endif; ?>
				</span>
			</div>
		</section>

		<div class="mns-orders__card">
			<nav class="mns-otabs" aria-label="<?php echo esc_attr__( 'دسته‌بندی سفارش‌ها', 'medyar-nursing-services' ); ?>">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<?php
					$n      = (int) $counts[ $key ];
					$active = $key === $first_tab;
					$warn   = 'pending' === $key && $n > 0;
					?>
					<a
						class="mns-otabs__tab<?php echo $active ? ' is-active' : ''; ?><?php echo $warn ? ' is-warn' : ''; ?>"
						href="#<?php echo esc_attr( $key ); ?>"
						data-mns-nord-tab="<?php echo esc_attr( $key ); ?>"
						<?php echo $active ? 'aria-current="page"' : ''; ?>
					>
						<?php if ( $warn ) : ?>
							<span class="mns-otabs__dot" aria-hidden="true"></span>
						<?php endif; ?>
						<span class="mns-otabs__label"><?php echo esc_html( $label ); ?></span>
						<span class="mns-otabs__count"><?php echo esc_html( MNS_Format::digits( (string) $n ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="mns-otools__bar">
				<label class="mns-otools__search">
					<span class="screen-reader-text"><?php echo esc_html__( 'جستجو با نام بیمار، خدمت یا کد پیگیری', 'medyar-nursing-services' ); ?></span>
					<span class="mns-otools__search-icon" aria-hidden="true"><?php echo mns_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<input type="search" placeholder="<?php echo esc_attr__( 'جستجو با نام بیمار، خدمت یا کد پیگیری', 'medyar-nursing-services' ); ?>" maxlength="100" autocomplete="off" data-mns-nord-search>
				</label>
				<button type="button" class="mns-otools__more" data-mns-nord-filter-toggle aria-expanded="false" aria-controls="mns-nord-filters">
					<?php echo mns_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="mns-otools__more-label"><?php echo esc_html__( 'فیلتر بیشتر', 'medyar-nursing-services' ); ?></span>
				</button>
			</div>

			<form class="arp-card arp-refs-filters arp-refs-filters--3 mns-ofilters" id="mns-nord-filters" role="search" data-mns-nord-form hidden>
				<label class="arp-field-block">
					<span class="arp-field-block__label"><?php echo esc_html__( 'ماه (سوابق)', 'medyar-nursing-services' ); ?></span>
					<span class="arp-field-block__control arp-field-block__control--select">
						<select name="month" class="arp-select-native" data-arp-select data-mns-nord-month>
							<option value=""><?php echo esc_html__( 'همه ماه‌ها', 'medyar-nursing-services' ); ?></option>
							<?php foreach ( $months as $key => $label ) : ?>
								<option value="<?php echo esc_attr( (string) $key ); ?>"><?php echo esc_html( (string) $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="arp-field-block__chevron" aria-hidden="true"><?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</label>
				<label class="arp-field-block">
					<span class="arp-field-block__label"><?php echo esc_html__( 'وضعیت (سوابق)', 'medyar-nursing-services' ); ?></span>
					<span class="arp-field-block__control arp-field-block__control--select">
						<select name="status" class="arp-select-native" data-arp-select data-mns-nord-status>
							<?php foreach ( $status_opts as $key => $label ) : ?>
								<option value="<?php echo esc_attr( (string) $key ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="arp-field-block__chevron" aria-hidden="true"><?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</label>
				<label class="arp-field-block">
					<span class="arp-field-block__label"><?php echo esc_html__( 'نوع سفارش', 'medyar-nursing-services' ); ?></span>
					<span class="arp-field-block__control arp-field-block__control--select">
						<select name="recurring" class="arp-select-native" data-arp-select data-mns-nord-recurring>
							<option value=""><?php echo esc_html__( 'همه سفارش‌ها', 'medyar-nursing-services' ); ?></option>
							<option value="1"><?php echo esc_html__( 'فقط دوره‌ای', 'medyar-nursing-services' ); ?></option>
						</select>
						<span class="arp-field-block__chevron" aria-hidden="true"><?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</label>
				<div class="arp-refs-filters__reset" data-mns-nord-reset-box hidden>
					<a href="#" data-mns-nord-reset>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
						<?php echo esc_html__( 'حذف همه فیلترها', 'medyar-nursing-services' ); ?>
					</a>
				</div>
			</form>

			<div class="mns-olist" data-mns-nord-list<?php echo 'done' === $first_tab ? ' hidden' : ''; ?>>
				<div data-mns-nord-pane="upcoming"<?php echo 'upcoming' === $first_tab ? '' : ' hidden'; ?>>
					<?php if ( empty( $upcoming_groups ) ) : ?>
						<div class="mns-olist__tab-empty" role="status">
							<span class="mns-olist__tab-empty-icon" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html__( 'سفارش پیش‌رویی ندارید.', 'medyar-nursing-services' ); ?></span>
						</div>
					<?php else : ?>
						<?php $list_head(); ?>
						<?php foreach ( $upcoming_groups as $ymd => $rows ) : ?>
							<section class="mns-ogroup mns-ogroup--active" data-mns-nord-group>
								<h2 class="mns-ogroup__title">
									<span class="mns-ogroup__dot" aria-hidden="true"></span>
									<span class="mns-ogroup__label"><?php echo esc_html( $day_title( (string) $ymd ) ); ?></span>
									<span class="mns-ogroup__count">
										<?php
										/* translators: %s: count */
										echo esc_html( sprintf( __( '%s مورد', 'medyar-nursing-services' ), MNS_Format::digits( (string) count( $rows ) ) ) );
										?>
									</span>
								</h2>
								<ul class="mns-olist__rows">
									<?php
									foreach ( $rows as $it ) {
										$render_li( $it, 'active', 'mns-nu-' );
									}
									?>
								</ul>
							</section>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<div data-mns-nord-pane="pending"<?php echo 'pending' === $first_tab ? '' : ' hidden'; ?>>
					<?php if ( empty( $pending_rows ) ) : ?>
						<div class="mns-olist__tab-empty" role="status">
							<span class="mns-olist__tab-empty-icon" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html__( 'همهٔ کارهایتان تکمیل است.', 'medyar-nursing-services' ); ?></span>
						</div>
					<?php else : ?>
						<?php $list_head(); ?>
						<section class="mns-ogroup mns-ogroup--action" data-mns-nord-group>
							<h2 class="mns-ogroup__title">
								<span class="mns-ogroup__dot" aria-hidden="true"></span>
								<span class="mns-ogroup__label"><?php echo esc_html__( 'نیازمند اقدام', 'medyar-nursing-services' ); ?></span>
								<span class="mns-ogroup__count">
									<?php
									/* translators: %s: count */
									echo esc_html( sprintf( __( '%s مورد', 'medyar-nursing-services' ), MNS_Format::digits( (string) count( $pending_rows ) ) ) );
									?>
								</span>
							</h2>
							<ul class="mns-olist__rows">
								<?php
								foreach ( $pending_rows as $it ) {
									$render_li( $it, 'action', 'mns-np-' );
								}
								?>
							</ul>
						</section>
					<?php endif; ?>
				</div>
				<div class="mns-olist__filter-empty" role="status" data-mns-nord-empty hidden>
					<span class="mns-olist__filter-empty-text"><?php echo esc_html__( 'سفارشی با این فیلتر پیدا نشد', 'medyar-nursing-services' ); ?></span>
					<a class="mns-olist__filter-empty-clear" href="#" data-mns-nord-reset><?php echo esc_html__( 'پاک‌کردن فیلتر', 'medyar-nursing-services' ); ?></a>
				</div>
			</div>
		</div>

		<div class="mns-ohistory" data-mns-nord-history<?php echo 'done' === $first_tab ? '' : ' hidden'; ?>>
			<section class="arp-card arp-card--table mns-ohistory__card" aria-labelledby="mns-ohistory-title">
				<header class="arp-card__head mns-ohistory__head">
					<h2 class="arp-card__title" id="mns-ohistory-title"><?php echo esc_html__( 'تمام‌شده', 'medyar-nursing-services' ); ?></h2>
					<span class="mns-nord__sum" data-mns-nord-sum></span>
				</header>
				<p class="arp-table__empty mns-ohistory__empty" data-mns-nord-history-empty hidden><?php echo esc_html__( 'سفارشی با این فیلتر پیدا نشد.', 'medyar-nursing-services' ); ?></p>
				<div class="arp-table-wrap mns-ohistory__table">
					<table class="arp-table mns-otable">
						<thead>
							<tr>
								<th><?php echo esc_html__( 'کد پیگیری', 'medyar-nursing-services' ); ?></th>
								<th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th>
								<th><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></th>
								<th><?php echo esc_html__( 'منطقه', 'medyar-nursing-services' ); ?></th>
								<th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th>
								<th class="arp-table__amount"><?php echo esc_html__( 'سهم شما', 'medyar-nursing-services' ); ?></th>
								<th><?php echo esc_html__( 'اقدام', 'medyar-nursing-services' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $done_rows as $it ) {
								$render_tr( $it );
							}
							?>
						</tbody>
					</table>
				</div>
				<ul class="mns-olist__rows mns-ohistory__cards">
					<?php
					foreach ( $done_rows as $it ) {
						$render_li( $it, 'history', 'mns-nh-' );
					}
					?>
				</ul>
				<div class="arp-refs-pager" data-mns-nord-pager></div>
			</section>
		</div>
	<?php endif; ?>
</section>
