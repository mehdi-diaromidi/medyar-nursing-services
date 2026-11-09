<?php
/**
 * Patient «invoices & payments» list — desgin/png/فاکتورها و پرداخت ها/medyar-invoices-{desktop,mobile}.png
 * + desgin/ui doc/فاکتورها و پرداخت ها.txt. Same shell as «my orders»: unpaid in the main
 * card as single-line rows, paid in an ARP table card with pagination, ARP filter form.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data     = MNS_Static_Fixtures::get( 'patient/invoices' );
$items    = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
$patients = isset( $data['patients'] ) && is_array( $data['patients'] ) ? $data['patients'] : array();
$services = isset( $data['services'] ) && is_array( $data['services'] ) ? $data['services'] : array();

if ( MNS_Dev::is( 'empty' ) ) {
	$items = array();
}
foreach ( $items as $i => $row ) {
	$items[ $i ]['patient_name'] = (string) ( $patients[ $row['patient_id'] ] ?? '' );
}

$state = MNS_Patient_Invoices::state( array_keys( $services ) );
if ( MNS_Dev::is( 'filter-empty' ) ) {
	$state['q'] = '00000';
}
if ( MNS_Dev::is( 'tab-empty' ) ) {
	$state['tab'] = 'unpaid';
	$items        = array_values(
		array_filter(
			$items,
			static function ( $row ) {
				return 'paid' === $row['status'];
			}
		)
	);
}

/* Unpaid summary is account-wide (not affected by filters). */
$unpaid_all = array_values(
	array_filter(
		$items,
		static function ( $row ) {
			return 'paid' !== $row['status'];
		}
	)
);
$unpaid_sum = array_sum( array_map( static function ( $r ) { return (int) $r['amount']; }, $unpaid_all ) );

$result      = MNS_Patient_Invoices::query( $items, $state );
$counts      = $result['counts'];
$tabs        = MNS_Patient_Invoices::tabs();
$no_items    = empty( $items );
$has_filters = MNS_Patient_Invoices::has_filters( $state );
$adv_filters = ! empty( $state['from'] ) || ! empty( $state['to'] ) || '' !== $state['service'];
$clear_url   = MNS_Patient_Invoices::url( $state, array( 'q' => null, 'from' => null, 'to' => null, 'service' => null, 'mns_p' => null ) );
$show_unpaid = 'paid' !== $state['tab'];
$show_paid   = 'unpaid' !== $state['tab'];
$paged       = MNS_Patient_Orders::paginate( $show_paid ? $result['paid'] : array(), $state['page'] );
$page_url    = static function ( int $n ) use ( $state ): string {
	return MNS_Patient_Invoices::url( $state, array( 'mns_p' => $n > 1 ? $n : null ) );
};
$sort_url    = static function ( string $key ) use ( $state ): string {
	$order = ( $state['sort'] === $key && 'desc' === $state['order'] ) ? 'asc' : 'desc';
	return MNS_Patient_Invoices::url( $state, array( 'sort' => $key, 'order' => $order, 'mns_p' => null ) );
};
$sort_link   = static function ( string $key, string $label ) use ( $state, $sort_url ): string {
	$cls = 'mns-olist__sort' . ( $state['sort'] === $key ? ' is-sorted is-' . $state['order'] : '' );
	return '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $sort_url( $key ) ) . '" data-mns-orders-nav>' . esc_html( $label ) . mns_icon( 'chevron-down' ) . '</a>';
};

$base_url   = MNS_Pages::panel_url( 'patient', 'invoices' );
$route_args = array();
parse_str( (string) wp_parse_url( $base_url, PHP_URL_QUERY ), $route_args );
$form_action = strtok( $base_url, '?' );
$ph          = MNS_Format::g2j( (int) MNS_Patient_Invoices::now( 'Y' ), (int) MNS_Patient_Invoices::now( 'n' ), (int) MNS_Patient_Invoices::now( 'j' ) );
$chevron_svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>';
?>
<section class="mns-orders mns-orders--invoices<?php echo $no_items ? ' mns-orders--empty' : ''; ?>" data-mns-orders aria-labelledby="mns-inv-title">
	<header class="mns-orders__head">
		<div class="mns-orders__intro">
			<h1 class="mns-orders__title" id="mns-inv-title">
				<span class="mns-orders__title-icon" aria-hidden="true"><?php echo mns_icon( 'wallet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-orders__title-text"><?php echo esc_html__( 'فاکتورها و پرداخت‌ها', 'medyar-nursing-services' ); ?></span>
				<?php if ( ! $no_items ) : ?>
					<span class="mns-orders__count" data-mns-swap="count"><?php echo esc_html( MNS_Format::digits( $counts['all'] ) ); ?></span>
				<?php endif; ?>
			</h1>
			<p class="mns-orders__lead"><?php echo esc_html__( 'فاکتور پرداخت‌نشده را بپردازید، به مبلغ اعتراض کنید یا فاکتور پرداخت‌شده را دانلود کنید.', 'medyar-nursing-services' ); ?></p>
		</div>
	</header>

	<p class="mns-notice mns-form-notice mns-orders__notice" data-mns-notice tabindex="-1" hidden></p>

	<?php if ( $no_items ) : ?>
		<div class="mns-olist__tab-empty mns-inv-empty" role="status">
			<span class="mns-olist__tab-empty-icon" aria-hidden="true"><?php echo mns_icon( 'invoice' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span><?php echo esc_html__( 'فاکتور شما بعد از پایان هر خدمت اینجا نمایش داده می‌شود', 'medyar-nursing-services' ); ?></span>
			<a class="mns-obtn mns-obtn--primary" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'new-order' ) ); ?>"><?php echo esc_html__( 'ثبت درخواست جدید', 'medyar-nursing-services' ); ?></a>
		</div>
	<?php else : ?>

		<?php if ( ! empty( $unpaid_all ) ) : ?>
			<div class="mns-inv-summary">
				<?php if ( ! empty( $unpaid_all ) ) : ?>
					<?php
					$one = 1 === count( $unpaid_all ) && 'unpaid' === $unpaid_all[0]['status'];
					?>
					<div class="mns-inv-due" role="status">
						<span class="mns-inv-due__icon" aria-hidden="true"><?php echo mns_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div class="mns-inv-due__text">
							<strong>
								<?php
								/* translators: %s: count */
								echo esc_html( sprintf( __( '%s فاکتور پرداخت‌نشده دارید', 'medyar-nursing-services' ), MNS_Format::digits( count( $unpaid_all ) ) ) );
								?>
							</strong>
							<span>
								<?php
								/* translators: %s: amount */
								echo esc_html( sprintf( __( 'مجموع: %s تومان (فقط جهت اطلاع). هر فاکتور جداگانه پرداخت می‌شود.', 'medyar-nursing-services' ), MNS_Format::number( $unpaid_sum ) ) );
								?>
							</span>
						</div>
						<?php if ( $one ) : ?>
							<a class="mns-obtn mns-obtn--pay mns-inv-due__btn" href="<?php echo esc_url( MNS_Patient_Invoices::view_url( (string) $unpaid_all[0]['number'] ) . '#mns-pay' ); ?>"><?php echo esc_html__( 'پرداخت', 'medyar-nursing-services' ); ?></a>
						<?php else : ?>
							<a class="mns-obtn mns-obtn--outline mns-inv-due__btn" href="<?php echo esc_url( MNS_Patient_Invoices::url( $state, array( 'mns_tab' => 'unpaid', 'mns_p' => null ) ) ); ?>" data-mns-orders-nav>
								<span><?php echo esc_html__( 'مشاهده پرداخت‌نشده‌ها', 'medyar-nursing-services' ); ?></span>
								<?php echo mns_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

			</div>
		<?php endif; ?>

		<div class="mns-orders__card">
			<nav class="mns-otabs" aria-label="<?php echo esc_attr__( 'وضعیت فاکتورها', 'medyar-nursing-services' ); ?>" data-mns-swap="tabs">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<?php
					$active = $state['tab'] === $key;
					$count  = (int) ( $counts[ $key ] ?? 0 );
					$warn   = 'unpaid' === $key && $count > 0;
					?>
					<a class="mns-otabs__tab<?php echo $active ? ' is-active' : ''; ?><?php echo $warn ? ' is-warn' : ''; ?>" href="<?php echo esc_url( MNS_Patient_Invoices::url( $state, array( 'mns_tab' => 'all' === $key ? null : $key, 'mns_p' => null ) ) ); ?>" data-mns-orders-nav<?php echo $active ? ' aria-current="page"' : ''; ?>>
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
					<span class="screen-reader-text"><?php echo esc_html__( 'جستجو با شماره فاکتور یا کد پیگیری سفارش', 'medyar-nursing-services' ); ?></span>
					<span class="mns-otools__search-icon" aria-hidden="true"><?php echo mns_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<input type="search" name="q" form="mns-orders-filter" value="<?php echo esc_attr( $state['q'] ); ?>" placeholder="<?php echo esc_attr__( 'جستجو با شماره فاکتور یا کد پیگیری سفارش', 'medyar-nursing-services' ); ?>" data-mns-placeholder-mobile="<?php echo esc_attr__( 'شماره فاکتور یا کد پیگیری', 'medyar-nursing-services' ); ?>" maxlength="100" autocomplete="off" data-mns-orders-q>
				</label>
				<button type="button" class="mns-otools__more" data-mns-orders-filter-toggle aria-expanded="<?php echo $adv_filters ? 'true' : 'false'; ?>" aria-controls="mns-orders-filter">
					<?php echo mns_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="mns-otools__more-label"><?php echo esc_html__( 'فیلتر بیشتر', 'medyar-nursing-services' ); ?></span>
				</button>
			</div>

			<form class="arp-card arp-refs-filters arp-refs-filters--3 mns-ofilters" id="mns-orders-filter" method="get" action="<?php echo esc_url( $form_action ); ?>" role="search" data-mns-orders-form <?php echo $adv_filters ? '' : 'hidden'; ?>>
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
				<label class="arp-field-block">
					<span class="arp-field-block__label"><?php echo esc_html__( 'تاریخ صدور', 'medyar-nursing-services' ); ?></span>
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
						<span class="mns-olist__filter-empty-text"><?php echo esc_html__( 'فاکتوری با این فیلتر پیدا نشد', 'medyar-nursing-services' ); ?></span>
						<a class="mns-olist__filter-empty-clear" href="<?php echo esc_url( $clear_url ); ?>" data-mns-orders-nav><?php echo esc_html__( 'پاک‌کردن فیلتر', 'medyar-nursing-services' ); ?></a>
					</div>
				<?php elseif ( $show_unpaid ) : ?>
					<?php if ( empty( $result['unpaid'] ) ) : ?>
						<div class="mns-olist__tab-empty" role="status">
							<span class="mns-olist__tab-empty-icon" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html__( 'فاکتور پرداخت‌نشده‌ای ندارید.', 'medyar-nursing-services' ); ?></span>
						</div>
					<?php else : ?>
						<div class="mns-olist__head" aria-hidden="true">
							<span><?php echo esc_html__( 'شماره فاکتور', 'medyar-nursing-services' ); ?></span>
							<span><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></span>
							<span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span>
							<?php echo $sort_link( 'date', __( 'تاریخ صدور', 'medyar-nursing-services' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo $sort_link( 'amount', __( 'مبلغ', 'medyar-nursing-services' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></span>
							<span><?php echo esc_html__( 'تاریخ و روش پرداخت', 'medyar-nursing-services' ); ?></span>
							<span><?php echo esc_html__( 'اقدام', 'medyar-nursing-services' ); ?></span>
						</div>
						<section class="mns-ogroup mns-ogroup--action" aria-labelledby="mns-ig-unpaid">
							<h2 class="mns-ogroup__title" id="mns-ig-unpaid">
								<span class="mns-ogroup__dot" aria-hidden="true"></span>
								<span class="mns-ogroup__label"><?php echo esc_html__( 'پرداخت‌نشده', 'medyar-nursing-services' ); ?></span>
								<span class="mns-ogroup__count">
									<?php
									/* translators: %s: count */
									echo esc_html( sprintf( __( '%s مورد', 'medyar-nursing-services' ), MNS_Format::digits( count( $result['unpaid'] ) ) ) );
									?>
								</span>
							</h2>
							<ul class="mns-olist__rows">
								<?php foreach ( $result['unpaid'] as $row ) : ?>
									<?php MNS_UI::partial( 'invoice-row', array( 'row' => $row, 'group' => 'action' ) ); ?>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="mns-ohistory" data-mns-swap="history">
			<?php if ( $show_paid && $result['filtered_total'] > 0 ) : ?>
				<section class="arp-card arp-card--table mns-ohistory__card" aria-labelledby="mns-ihistory-title">
					<header class="arp-card__head mns-ohistory__head">
						<h2 class="arp-card__title" id="mns-ihistory-title"><?php echo esc_html__( 'پرداخت‌شده', 'medyar-nursing-services' ); ?></h2>
					</header>
					<?php if ( empty( $paged['visible'] ) ) : ?>
						<p class="arp-table__empty mns-ohistory__empty"><?php echo esc_html__( 'هنوز فاکتور پرداخت‌شده‌ای ندارید.', 'medyar-nursing-services' ); ?></p>
					<?php else : ?>
						<div class="arp-table-wrap mns-ohistory__table">
							<table class="arp-table mns-otable">
								<thead>
									<tr>
										<th><?php echo esc_html__( 'شماره فاکتور', 'medyar-nursing-services' ); ?></th>
										<th><?php echo esc_html__( 'خدمت', 'medyar-nursing-services' ); ?></th>
										<th><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></th>
										<th><?php echo $sort_link( 'date', __( 'تاریخ صدور', 'medyar-nursing-services' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
										<th class="arp-table__amount"><?php echo $sort_link( 'amount', __( 'مبلغ', 'medyar-nursing-services' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
										<th><?php echo esc_html__( 'وضعیت', 'medyar-nursing-services' ); ?></th>
										<th><?php echo esc_html__( 'تاریخ و روش پرداخت', 'medyar-nursing-services' ); ?></th>
										<th><?php echo esc_html__( 'اقدام', 'medyar-nursing-services' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $paged['visible'] as $row ) : ?>
										<?php MNS_UI::partial( 'invoice-table-row', array( 'row' => $row ) ); ?>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
						<ul class="mns-olist__rows mns-ohistory__cards">
							<?php foreach ( $paged['visible'] as $row ) : ?>
								<?php MNS_UI::partial( 'invoice-row', array( 'row' => $row, 'group' => 'history', 'id_prefix' => 'mns-ivh-' ) ); ?>
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
		<p class="mns-order-sheet__sub"><?php echo esc_html__( 'اقدام‌های این فاکتور', 'medyar-nursing-services' ); ?></p>
		<ul class="mns-order-sheet__list" role="menu" data-mns-order-sheet-list></ul>
	</div>
</div>
