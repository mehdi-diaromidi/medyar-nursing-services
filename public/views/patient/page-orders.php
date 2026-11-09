<?php
/**
 * Patient «my orders» — desgin/png/سفارش های من/medyar-my-orders-{desktop,mobile}.png + desgin/ui doc/سفارش های من.txt.
 *
 * Active work (action + active) lives in the main card as single-line rows; history
 * (completed / cancelled) gets its own ARP-style table card with pagination. The
 * «فیلتر بیشتر» form reuses ARP's `.arp-refs-filters` markup + JS (pickers, custom
 * selects, auto-submit); JS refreshes the `[data-mns-swap]` regions in place.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data     = MNS_Static_Fixtures::get( 'patient/orders' );
$items    = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
$patients = isset( $data['patients'] ) && is_array( $data['patients'] ) ? $data['patients'] : array();
$services = isset( $data['services'] ) && is_array( $data['services'] ) ? $data['services'] : array();

if ( MNS_Dev::is( 'empty' ) ) {
	$items = array();
}

$names = array();
foreach ( $patients as $p ) {
	$names[ (string) $p['id'] ] = (string) $p['name'];
}
foreach ( $items as $i => $row ) {
	$items[ $i ]['patient_name'] = $names[ (string) ( $row['patient_id'] ?? '' ) ] ?? '';
}

$state = MNS_Patient_Orders::state( array_keys( $services ) );
if ( MNS_Dev::is( 'filter-empty' ) ) {
	$state['q'] = '00000';
}
if ( MNS_Dev::is( 'tab-empty' ) ) {
	$state['tab'] = 'unpaid';
	$items        = array_values(
		array_filter(
			$items,
			static function ( $row ) {
				return 'invoiced' !== ( $row['status'] ?? '' );
			}
		)
	);
}

$result      = MNS_Patient_Orders::query( $items, $state );
$counts      = $result['counts'];
$rows        = $result['rows'];
$tabs        = MNS_Patient_Orders::tabs();
$new_url     = MNS_Pages::panel_url( 'patient', 'new-order' );
$base_url    = MNS_Pages::panel_url( 'patient', 'orders' );
$no_orders   = empty( $items );
$has_filters = MNS_Patient_Orders::has_filters( $state );
$adv_filters = ! empty( $state['from'] ) || ! empty( $state['to'] ) || '' !== $state['service'] || $state['recurring'];
$clear_url   = MNS_Patient_Orders::url( $state, array( 'q' => null, 'from' => null, 'to' => null, 'service' => null, 'recurring' => null, 'mns_p' => null ) );

/* Split: main card = action + active; history card = done + cancelled (paged). */
$groups      = MNS_Patient_Orders::groups( $rows );
$history_tab = in_array( $state['tab'], array( 'done', 'cancelled' ), true );
$history     = ( 'all' === $state['tab'] || $history_tab ) ? $groups['history'] : array();
$paged       = MNS_Patient_Orders::paginate( $history, $state['page'] );
$page_url    = static function ( int $n ) use ( $state ): string {
	return MNS_Patient_Orders::url( $state, array( 'mns_p' => $n > 1 ? $n : null ) );
};

$sort_url = static function ( string $key ) use ( $state ): string {
	$order = ( $state['sort'] === $key && 'desc' === $state['order'] ) ? 'asc' : 'desc';
	return MNS_Patient_Orders::url( $state, array( 'sort' => $key, 'order' => $order, 'mns_p' => null ) );
};

$route_args = array();
$query_str  = (string) wp_parse_url( $base_url, PHP_URL_QUERY );
if ( '' !== $query_str ) {
	parse_str( $query_str, $route_args );
}
$form_action = strtok( $base_url, '?' );

$group_meta = array(
	'action' => __( 'نیازمند اقدام', 'medyar-nursing-services' ),
	'active' => __( 'فعال', 'medyar-nursing-services' ),
);

$tab_empty = array(
	'all'       => __( 'سفارش فعالی ندارید.', 'medyar-nursing-services' ),
	'active'    => __( 'سفارش فعالی ندارید.', 'medyar-nursing-services' ),
	'unpaid'    => __( 'فاکتور پرداخت‌نشده‌ای ندارید.', 'medyar-nursing-services' ),
	'done'      => __( 'هنوز سفارش تکمیل‌شده‌ای ندارید.', 'medyar-nursing-services' ),
	'cancelled' => __( 'سفارش لغوشده‌ای ندارید.', 'medyar-nursing-services' ),
);

$list_head = static function () use ( $sort_url, $state ) {
	?>
	<div class="mns-olist__head" aria-hidden="true">
		<span><?php echo esc_html__( 'کد پیگیری', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span>
		<a class="mns-olist__sort<?php echo 'date' === $state['sort'] ? ' is-sorted is-' . esc_attr( $state['order'] ) : ''; ?>" href="<?php echo esc_url( $sort_url( 'date' ) ); ?>" tabindex="-1"><?php echo esc_html__( 'تاریخ', 'medyar-nursing-services' ); ?><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		<span><?php echo esc_html__( 'پرستار', 'medyar-nursing-services' ); ?></span>
		<span><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></span>
		<a class="mns-olist__sort<?php echo 'amount' === $state['sort'] ? ' is-sorted is-' . esc_attr( $state['order'] ) : ''; ?>" href="<?php echo esc_url( $sort_url( 'amount' ) ); ?>" tabindex="-1"><?php echo esc_html__( 'مبلغ', 'medyar-nursing-services' ); ?><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		<span><?php echo esc_html__( 'اقدام', 'medyar-nursing-services' ); ?></span>
	</div>
	<?php
};

$chevron_svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flash after payment redirect.
$paid_flash = isset( $_GET['mns_paid'] ) && '1' === sanitize_key( wp_unslash( $_GET['mns_paid'] ) );
?>
<section class="mns-orders<?php echo $no_orders ? ' mns-orders--empty' : ''; ?>" data-mns-orders aria-labelledby="mns-orders-title">
	<header class="mns-orders__head">
		<div class="mns-orders__intro">
			<h1 class="mns-orders__title" id="mns-orders-title">
				<?php if ( ! $no_orders ) : ?>
					<span class="mns-orders__title-icon" aria-hidden="true"><?php echo mns_icon( 'doc-list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
				<span class="mns-orders__title-text"><?php echo esc_html__( 'سفارش‌های من', 'medyar-nursing-services' ); ?></span>
				<?php if ( ! $no_orders ) : ?>
					<span class="mns-orders__count" data-mns-swap="count"><?php echo esc_html( MNS_Format::digits( $counts['all'] ) ); ?></span>
				<?php endif; ?>
			</h1>
			<?php if ( ! $no_orders ) : ?>
				<p class="mns-orders__lead"><?php echo esc_html__( 'وضعیت هر سفارش را ببینید و همان‌جا پرداخت، ثبت نظر یا درخواست مجدد کنید.', 'medyar-nursing-services' ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( ! $no_orders ) : ?>
			<a class="mns-obtn mns-obtn--primary mns-orders__cta" href="<?php echo esc_url( $new_url ); ?>">
				<?php echo mns_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="mns-orders__cta-long"><?php echo esc_html__( 'ثبت درخواست جدید', 'medyar-nursing-services' ); ?></span>
				<span class="mns-orders__cta-short"><?php echo esc_html__( 'درخواست جدید', 'medyar-nursing-services' ); ?></span>
			</a>
		<?php endif; ?>
	</header>

	<p class="mns-notice mns-form-notice mns-orders__notice" data-mns-notice tabindex="-1"<?php echo $paid_flash ? ' role="status"' : ' hidden'; ?>><?php echo $paid_flash ? esc_html__( 'پرداخت ثبت شد', 'medyar-nursing-services' ) : ''; ?></p>

	<?php if ( $no_orders ) : ?>
		<div class="mns-orders-empty">
			<span class="mns-orders-empty__art" aria-hidden="true">
				<svg class="mns-orders-empty__leaf" viewBox="0 0 60 70" fill="currentColor" aria-hidden="true" focusable="false"><path d="M2 70C2 40 14 18 34 4c-6 22-10 44-32 66z" opacity=".55"/><path d="M14 70c4-22 18-36 42-44-10 18-20 34-42 44z" opacity=".35"/></svg>
				<?php echo mns_icon( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<h2 class="mns-orders-empty__title"><?php echo esc_html__( 'هنوز سفارشی ثبت نکرده‌اید', 'medyar-nursing-services' ); ?></h2>
			<p class="mns-orders-empty__text"><?php echo esc_html__( 'پرستار را با چند لمس به منزل بیاورید. پرداخت بعد از انجام خدمت است.', 'medyar-nursing-services' ); ?></p>
			<ul class="mns-orders-empty__cats">
				<?php foreach ( $services as $slug => $label ) : ?>
					<li><a class="mns-orders-empty__cat" href="<?php echo esc_url( add_query_arg( 'mns_service', $slug, $new_url ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php if ( ! empty( $data['first_order_discount'] ) ) : ?>
				<p class="mns-orders-empty__promo">
					<?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html__( 'تخفیف سفارش اول شما فعال است.', 'medyar-nursing-services' ); ?></span>
				</p>
			<?php endif; ?>
			<a class="mns-obtn mns-obtn--primary mns-orders-empty__cta" href="<?php echo esc_url( $new_url ); ?>"><?php echo esc_html__( 'ثبت اولین درخواست', 'medyar-nursing-services' ); ?></a>
		</div>
	<?php else : ?>

		<div class="mns-orders__card">
			<nav class="mns-otabs" aria-label="<?php echo esc_attr__( 'وضعیت سفارش‌ها', 'medyar-nursing-services' ); ?>" data-mns-swap="tabs">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<?php
					$active = $state['tab'] === $key;
					$count  = (int) ( $counts[ $key ] ?? 0 );
					$warn   = 'unpaid' === $key && $count > 0;
					?>
					<a
						class="mns-otabs__tab<?php echo $active ? ' is-active' : ''; ?><?php echo $warn ? ' is-warn' : ''; ?>"
						href="<?php echo esc_url( MNS_Patient_Orders::url( $state, array( 'mns_tab' => 'all' === $key ? null : $key, 'mns_p' => null ) ) ); ?>"
						data-mns-orders-nav
						<?php echo $active ? 'aria-current="page"' : ''; ?>
					>
						<?php if ( $warn ) : ?>
							<span class="mns-otabs__dot" aria-hidden="true"></span>
						<?php endif; ?>
						<span class="mns-otabs__label"><?php echo esc_html( $label ); ?></span>
						<span class="mns-otabs__count"><?php echo esc_html( MNS_Format::digits( $count ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="mns-otools__bar">
				<label class="mns-otools__search">
					<span class="screen-reader-text"><?php echo esc_html__( 'جستجو با کد پیگیری یا نام خدمت', 'medyar-nursing-services' ); ?></span>
					<span class="mns-otools__search-icon" aria-hidden="true"><?php echo mns_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<input
						type="search"
						name="q"
						form="mns-orders-filter"
						value="<?php echo esc_attr( $state['q'] ); ?>"
						placeholder="<?php echo esc_attr__( 'جستجو با کد پیگیری یا نام خدمت', 'medyar-nursing-services' ); ?>"
						data-mns-placeholder-mobile="<?php echo esc_attr__( 'کد پیگیری یا نام خدمت', 'medyar-nursing-services' ); ?>"
						maxlength="100"
						autocomplete="off"
						data-mns-orders-q
					>
				</label>
				<button type="button" class="mns-otools__more" data-mns-orders-filter-toggle aria-expanded="<?php echo $adv_filters ? 'true' : 'false'; ?>" aria-controls="mns-orders-filter">
					<?php echo mns_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="mns-otools__more-label"><?php echo esc_html__( 'فیلتر بیشتر', 'medyar-nursing-services' ); ?></span>
				</button>
			</div>

			<form
				class="arp-card arp-refs-filters arp-refs-filters--3 mns-ofilters"
				id="mns-orders-filter"
				method="get"
				action="<?php echo esc_url( $form_action ); ?>"
				role="search"
				data-mns-orders-form
				<?php echo $adv_filters ? '' : 'hidden'; ?>
			>
				<?php foreach ( $route_args as $k => $v ) : ?>
					<input type="hidden" name="<?php echo esc_attr( (string) $k ); ?>" value="<?php echo esc_attr( (string) $v ); ?>">
				<?php endforeach; ?>
				<span class="mns-ofilters__state" data-mns-swap="hidden">
					<?php if ( 'all' !== $state['tab'] ) : ?>
						<input type="hidden" name="mns_tab" value="<?php echo esc_attr( $state['tab'] ); ?>">
					<?php endif; ?>
					<?php if ( '' !== $state['sort'] ) : ?>
						<input type="hidden" name="sort" value="<?php echo esc_attr( $state['sort'] ); ?>">
						<input type="hidden" name="order" value="<?php echo esc_attr( $state['order'] ); ?>">
					<?php endif; ?>
				</span>

				<?php
				$ph = MNS_Format::g2j( (int) MNS_Patient_Invoices::now( 'Y' ), (int) MNS_Patient_Invoices::now( 'n' ), (int) MNS_Patient_Invoices::now( 'j' ) );
				?>
				<label class="arp-field-block">
					<span class="arp-field-block__label"><?php echo esc_html__( 'تاریخ', 'medyar-nursing-services' ); ?></span>
					<span class="arp-field-block__control arp-field-block__control--range">
						<span class="arp-field-block__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg></span>
						<input type="text" name="from" value="<?php echo esc_attr( MNS_Patient_Orders::jdate_input( (array) $state['from'] ) ); ?>" placeholder="<?php echo esc_attr( MNS_Format::digits( sprintf( '%04d/%02d/01', $ph[0], $ph[1] ) ) ); ?>" readonly autocomplete="off" data-arp-jdate aria-label="<?php echo esc_attr__( 'از تاریخ', 'medyar-nursing-services' ); ?>" />
						<span class="arp-field-block__sep"><?php echo esc_html__( 'تا', 'medyar-nursing-services' ); ?></span>
						<input type="text" name="to" value="<?php echo esc_attr( MNS_Patient_Orders::jdate_input( (array) $state['to'] ) ); ?>" placeholder="<?php echo esc_attr( MNS_Format::digits( sprintf( '%04d/%02d/%02d', $ph[0], $ph[1], $ph[2] ) ) ); ?>" readonly autocomplete="off" data-arp-jdate aria-label="<?php echo esc_attr__( 'تا تاریخ', 'medyar-nursing-services' ); ?>" />
					</span>
				</label>

				<label class="arp-field-block">
					<span class="arp-field-block__label"><?php echo esc_html__( 'نوع خدمت', 'medyar-nursing-services' ); ?></span>
					<span class="arp-field-block__control arp-field-block__control--select">
						<select name="service" class="arp-select-native" data-arp-select data-arp-autosubmit>
							<option value=""><?php echo esc_html__( 'همه خدمات', 'medyar-nursing-services' ); ?></option>
							<?php foreach ( $services as $slug => $label ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $state['service'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="arp-field-block__chevron" aria-hidden="true"><?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</label>

				<label class="arp-field-block">
					<span class="arp-field-block__label"><?php echo esc_html__( 'نوع سفارش', 'medyar-nursing-services' ); ?></span>
					<span class="arp-field-block__control arp-field-block__control--select">
						<select name="recurring" class="arp-select-native" data-arp-select data-arp-autosubmit>
							<option value=""><?php echo esc_html__( 'همه سفارش‌ها', 'medyar-nursing-services' ); ?></option>
							<option value="1" <?php selected( $state['recurring'] ); ?>><?php echo esc_html__( 'فقط دوره‌ای', 'medyar-nursing-services' ); ?></option>
						</select>
						<span class="arp-field-block__chevron" aria-hidden="true"><?php echo $chevron_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</label>

				<div class="arp-refs-filters__reset" data-mns-filters-reset <?php echo $has_filters ? '' : 'hidden'; ?>>
					<a href="<?php echo esc_url( $clear_url ); ?>" data-mns-orders-nav>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
						<?php echo esc_html__( 'حذف همه فیلترها', 'medyar-nursing-services' ); ?>
					</a>
				</div>
				<button type="submit" class="screen-reader-text"><?php echo esc_html__( 'اعمال فیلتر', 'medyar-nursing-services' ); ?></button>
			</form>

			<div class="mns-olist" data-mns-orders-list data-mns-swap="list">
				<?php if ( 0 === $result['filtered_total'] && $has_filters ) : ?>
					<div class="mns-olist__filter-empty" role="status">
						<span class="mns-olist__filter-empty-text"><?php echo esc_html__( 'سفارشی با این فیلتر پیدا نشد', 'medyar-nursing-services' ); ?></span>
						<a class="mns-olist__filter-empty-clear" href="<?php echo esc_url( $clear_url ); ?>" data-mns-orders-nav><?php echo esc_html__( 'پاک‌کردن فیلتر', 'medyar-nursing-services' ); ?></a>
					</div>
				<?php elseif ( ! $history_tab ) : ?>
					<?php if ( empty( $groups['action'] ) && empty( $groups['active'] ) ) : ?>
						<div class="mns-olist__tab-empty" role="status">
							<span class="mns-olist__tab-empty-icon" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html( $tab_empty[ $state['tab'] ] ?? $tab_empty['all'] ); ?></span>
						</div>
					<?php else : ?>
						<?php $list_head(); ?>
						<?php foreach ( array( 'action', 'active' ) as $gkey ) : ?>
							<?php
							if ( empty( $groups[ $gkey ] ) ) {
								continue;
							}
							?>
							<section class="mns-ogroup mns-ogroup--<?php echo esc_attr( $gkey ); ?>" aria-labelledby="mns-og-<?php echo esc_attr( $gkey ); ?>">
								<h2 class="mns-ogroup__title" id="mns-og-<?php echo esc_attr( $gkey ); ?>">
									<span class="mns-ogroup__dot" aria-hidden="true"></span>
									<span class="mns-ogroup__label"><?php echo esc_html( $group_meta[ $gkey ] ); ?></span>
									<span class="mns-ogroup__count">
										<?php
										/* translators: %s: count */
										echo esc_html( sprintf( __( '%s مورد', 'medyar-nursing-services' ), MNS_Format::digits( count( $groups[ $gkey ] ) ) ) );
										?>
									</span>
								</h2>
								<ul class="mns-olist__rows">
									<?php foreach ( $groups[ $gkey ] as $row ) : ?>
										<?php MNS_UI::partial( 'order-row', array( 'row' => $row, 'group' => $gkey ) ); ?>
									<?php endforeach; ?>
								</ul>
							</section>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="mns-ohistory" data-mns-swap="history">
			<?php if ( $result['filtered_total'] > 0 && ( $history_tab || ! empty( $history ) ) ) : ?>
				<section class="arp-card arp-card--table mns-ohistory__card" aria-labelledby="mns-ohistory-title">
					<header class="arp-card__head mns-ohistory__head">
						<h2 class="arp-card__title" id="mns-ohistory-title">
							<?php echo esc_html( $history_tab ? $tabs[ $state['tab'] ] : __( 'سوابق', 'medyar-nursing-services' ) ); ?>
						</h2>
					</header>
					<?php if ( empty( $paged['visible'] ) ) : ?>
						<p class="arp-table__empty mns-ohistory__empty"><?php echo esc_html( $tab_empty[ $state['tab'] ] ?? '' ); ?></p>
					<?php else : ?>
						<div class="arp-table-wrap mns-ohistory__table">
							<table class="arp-table mns-otable">
								<thead>
									<tr>
										<th><?php echo esc_html__( 'کد پیگیری', 'medyar-nursing-services' ); ?></th>
										<th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th>
										<th><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></th>
										<th><a class="mns-olist__sort<?php echo 'date' === $state['sort'] ? ' is-sorted is-' . esc_attr( $state['order'] ) : ''; ?>" href="<?php echo esc_url( $sort_url( 'date' ) ); ?>" data-mns-orders-nav><?php echo esc_html__( 'تاریخ', 'medyar-nursing-services' ); ?><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></th>
										<th><?php echo esc_html__( 'پرستار', 'medyar-nursing-services' ); ?></th>
										<th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th>
										<th class="arp-table__amount"><a class="mns-olist__sort<?php echo 'amount' === $state['sort'] ? ' is-sorted is-' . esc_attr( $state['order'] ) : ''; ?>" href="<?php echo esc_url( $sort_url( 'amount' ) ); ?>" data-mns-orders-nav><?php echo esc_html__( 'مبلغ', 'medyar-nursing-services' ); ?><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></th>
										<th><?php echo esc_html__( 'اقدام', 'medyar-nursing-services' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $paged['visible'] as $row ) : ?>
										<?php MNS_UI::partial( 'order-table-row', array( 'row' => $row ) ); ?>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
						<ul class="mns-olist__rows mns-ohistory__cards">
							<?php foreach ( $paged['visible'] as $row ) : ?>
								<?php MNS_UI::partial( 'order-row', array( 'row' => $row, 'group' => 'history', 'id_prefix' => 'mns-omh-' ) ); ?>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php MNS_UI::partial( 'orders-pager', $paged + array( 'page_url' => $page_url ) ); ?>
				</section>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>

<div class="mns-dialog mns-order-sheet" id="mns-order-sheet" hidden data-mns-dialog data-mns-order-sheet>
	<div class="mns-dialog__backdrop" data-mns-dialog-close tabindex="-1"></div>
	<div class="mns-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="mns-order-sheet-title" tabindex="-1">
		<span class="mns-order-sheet__handle" aria-hidden="true"></span>
		<h2 class="mns-order-sheet__title" id="mns-order-sheet-title" data-mns-order-sheet-title></h2>
		<p class="mns-order-sheet__sub"><?php echo esc_html__( 'اقدام‌های این سفارش', 'medyar-nursing-services' ); ?></p>
		<ul class="mns-order-sheet__list" role="menu" data-mns-order-sheet-list></ul>
	</div>
</div>

<div class="mns-dialog mns-order-cancel" id="mns-order-cancel" hidden data-mns-dialog data-mns-order-cancel-dialog>
	<div class="mns-dialog__backdrop" data-mns-dialog-close tabindex="-1"></div>
	<form class="mns-dialog__panel" role="alertdialog" aria-modal="true" aria-labelledby="mns-order-cancel-title" aria-describedby="mns-order-cancel-text" tabindex="-1" data-mns-order-cancel-form>
		<?php wp_nonce_field( 'mns_order_cancel', 'mns_cancel_nonce' ); ?>
		<input type="hidden" name="order" value="" data-mns-order-cancel-code>
		<h2 class="mns-order-cancel__title" id="mns-order-cancel-title"><?php echo esc_html__( 'سفارش لغو شود؟', 'medyar-nursing-services' ); ?></h2>
		<p class="mns-order-cancel__text" id="mns-order-cancel-text" data-mns-order-cancel-text></p>
		<label class="mns-order-cancel__field">
			<span class="screen-reader-text"><?php echo esc_html__( 'دلیل لغو (اختیاری)', 'medyar-nursing-services' ); ?></span>
			<textarea name="reason" rows="3" maxlength="500" placeholder="<?php echo esc_attr__( 'دلیل لغو (اختیاری)', 'medyar-nursing-services' ); ?>"></textarea>
		</label>
		<div class="mns-order-cancel__actions">
			<button type="submit" class="mns-obtn mns-obtn--danger"><?php echo esc_html__( 'بله، لغو شود', 'medyar-nursing-services' ); ?></button>
			<button type="button" class="mns-obtn mns-obtn--ghost" data-mns-dialog-close><?php echo esc_html__( 'انصراف', 'medyar-nursing-services' ); ?></button>
		</div>
	</form>
</div>
