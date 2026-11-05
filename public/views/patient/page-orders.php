<?php
/**
 * Patient orders — tabbed list items (UX Phase C).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data  = MNS_Static_Fixtures::get( 'patient/orders' );
$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
$tab   = isset( $_GET['mns_tab'] ) ? sanitize_key( wp_unslash( $_GET['mns_tab'] ) ) : 'active'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $tab, array( 'active', 'past', 'cancelled' ), true ) ) {
	$tab = 'active';
}
$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( MNS_Dev::is( 'empty' ) ) {
	$items = array();
}

$counts = array(
	'active'    => 0,
	'past'      => 0,
	'cancelled' => 0,
);
foreach ( $items as $row ) {
	$key = (string) ( $row['tab'] ?? '' );
	if ( isset( $counts[ $key ] ) ) {
		++$counts[ $key ];
	}
}

$filtered = array();
foreach ( $items as $row ) {
	if ( (string) ( $row['tab'] ?? '' ) !== $tab ) {
		continue;
	}
	if ( '' !== $q ) {
		$hay = (string) ( $row['tracking'] ?? '' ) . ' ' . (string) ( $row['service'] ?? '' ) . ' ' . (string) ( $row['patient'] ?? '' );
		if ( false === mb_stripos( $hay, $q ) ) {
			continue;
		}
	}
	$filtered[] = $row;
}

$tabs = array(
	'active'    => __( 'فعال', 'medyar-nursing-services' ),
	'past'      => __( 'گذشته', 'medyar-nursing-services' ),
	'cancelled' => __( 'لغوشده', 'medyar-nursing-services' ),
);

$empty_copy = array(
	'active'    => array( __( 'سفارش فعالی ندارید', 'medyar-nursing-services' ), __( 'یک خدمت جدید ثبت کنید تا اینجا دیده شود.', 'medyar-nursing-services' ) ),
	'past'      => array( __( 'هنوز سفارش تکمیل‌شده‌ای نیست', 'medyar-nursing-services' ), __( 'پس از پایان خدمت، اینجا می‌آید.', 'medyar-nursing-services' ) ),
	'cancelled' => array( __( 'سفارش لغوشده‌ای نیست', 'medyar-nursing-services' ), __( 'اگر سفارشی لغو شود، اینجا می‌ماند.', 'medyar-nursing-services' ) ),
);

MNS_UI::page_header(
	array(
		'title' => __( 'سفارش‌های من', 'medyar-nursing-services' ),
		'lead'  => __( 'وضعیت و اقدام بعدی هر درخواست.', 'medyar-nursing-services' ),
		'actions' => '<a class="mns-submit-btn mns-submit-btn--auto mns-page-header__cta-desktop" href="' . esc_url( MNS_Pages::panel_url( 'patient', 'new-order' ) ) . '">' . esc_html__( 'سفارش جدید', 'medyar-nursing-services' ) . '</a>',
	)
);
?>

<div class="mns-tabs" role="tablist" aria-label="<?php echo esc_attr__( 'وضعیت سفارش‌ها', 'medyar-nursing-services' ); ?>">
	<?php foreach ( $tabs as $key => $label ) : ?>
		<?php
		$url = add_query_arg(
			array(
				'mns_tab' => $key,
			),
			MNS_Pages::panel_url( 'patient', 'orders' )
		);
		if ( '' !== $q ) {
			$url = add_query_arg( 'q', $q, $url );
		}
		$count = (int) ( $counts[ $key ] ?? 0 );
		?>
		<a
			class="mns-tabs__btn<?php echo $tab === $key ? ' is-active' : ''; ?>"
			role="tab"
			aria-selected="<?php echo $tab === $key ? 'true' : 'false'; ?>"
			href="<?php echo esc_url( $url ); ?>"
		>
			<?php echo esc_html( $label ); ?>
			<span class="mns-tabs__count"><?php echo esc_html( MNS_Format::digits( (string) $count ) ); ?></span>
		</a>
	<?php endforeach; ?>
</div>

<form class="mns-search-bar" method="get" role="search" data-mns-live-search>
	<input type="hidden" name="mns_panel" value="patient">
	<input type="hidden" name="mns_page" value="orders">
	<input type="hidden" name="mns_tab" value="<?php echo esc_attr( $tab ); ?>">
	<label class="mns-field-block mns-search-bar__field">
		<span class="screen-reader-text"><?php echo esc_html__( 'جستجو با کد پیگیری یا خدمت', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control">
			<input
				type="search"
				name="q"
				value="<?php echo esc_attr( $q ); ?>"
				placeholder="<?php echo esc_attr__( 'جستجو با کد پیگیری یا خدمت…', 'medyar-nursing-services' ); ?>"
				maxlength="100"
				data-mns-live-search-input
				autocomplete="off"
			>
			<?php if ( '' !== $q ) : ?>
				<a class="mns-field-block__action mns-search-bar__clear" href="<?php echo esc_url( add_query_arg( 'mns_tab', $tab, MNS_Pages::panel_url( 'patient', 'orders' ) ) ); ?>" aria-label="<?php echo esc_attr__( 'پاک کردن جستجو', 'medyar-nursing-services' ); ?>">
					<?php echo mns_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php else : ?>
				<span class="mns-field-block__action" aria-hidden="true"><?php echo mns_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
		</span>
	</label>
</form>

<?php if ( empty( $filtered ) ) : ?>
	<?php
	$copy = $empty_copy[ $tab ] ?? array( MNS_Copy::t( 'state.empty_title' ), MNS_Copy::t( 'state.empty_body' ) );
	MNS_UI::empty_state(
		array(
			'title'     => $copy[0],
			'text'      => '' !== $q ? __( 'نتیجه‌ای برای این جستجو نیست. عبارت را عوض کنید یا پاک کنید.', 'medyar-nursing-services' ) : $copy[1],
			'cta_label' => ( 'active' === $tab && '' === $q ) ? __( 'ثبت سفارش', 'medyar-nursing-services' ) : ( '' !== $q ? __( 'پاک کردن جستجو', 'medyar-nursing-services' ) : '' ),
			'cta_url'   => ( 'active' === $tab && '' === $q ) ? MNS_Pages::panel_url( 'patient', 'new-order' ) : ( '' !== $q ? add_query_arg( 'mns_tab', $tab, MNS_Pages::panel_url( 'patient', 'orders' ) ) : '' ),
			'icon'      => 'list',
		)
	);
	?>
<?php else : ?>
	<ul class="mns-list" data-mns-live-search-list>
		<?php foreach ( $filtered as $row ) : ?>
			<?php
			ob_start();
			MNS_UI::status_badge( (string) ( $row['status'] ?? 'submitted' ) );
			$badge = (string) ob_get_clean();
			$meta  = (string) ( $row['patient'] ?? '' ) . ' · ' . MNS_Format::human( (string) ( $row['when'] ?? '' ) );
			$meta2 = ! empty( $row['nurse'] )
				? sprintf( /* translators: nurse */ __( 'پرستار: %s', 'medyar-nursing-services' ), (string) $row['nurse'] )
				: '';
			$action = (string) ( $row['next_action'] ?? '' );
			MNS_UI::list_item(
				array(
					'url'             => (string) ( $row['next_url'] ?? '#' ),
					'title'           => (string) ( $row['service'] ?? '' ),
					'meta'            => $meta,
					'meta2'           => $meta2,
					'code'            => (string) ( $row['tracking'] ?? '' ),
					'badge_html'      => $badge,
					'chip'            => ! empty( $row['recurring'] ) ? (string) ( $row['recurring_label'] ?? __( 'دوره‌ای', 'medyar-nursing-services' ) ) : '',
					'action_label'    => $action,
					'action_primary'  => in_array( $action, array( 'پرداخت', 'ثبت نظر' ), true ),
				)
			);
			?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
