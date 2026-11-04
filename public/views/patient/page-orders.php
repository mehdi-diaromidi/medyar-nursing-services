<?php
/**
 * Patient orders — tabbed cards (Phase 3).
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
	'cancelled' => array( __( 'سفارش لغوشده‌ای نیست', 'medyar-nursing-services' ), '' ),
);

MNS_UI::page_header(
	array(
		'title'   => __( 'سفارش‌های من', 'medyar-nursing-services' ),
		'lead'    => __( 'وضعیت و اقدام بعدی هر درخواست.', 'medyar-nursing-services' ),
		'actions' => '<a class="mns-submit-btn mns-submit-btn--auto" href="' . esc_url( MNS_Pages::panel_url( 'patient', 'new-order' ) ) . '">' . esc_html__( 'سفارش جدید', 'medyar-nursing-services' ) . '</a>',
	)
);
?>

<div class="mns-tabs" role="tablist" aria-label="<?php echo esc_attr__( 'وضعیت سفارش‌ها', 'medyar-nursing-services' ); ?>">
	<?php foreach ( $tabs as $key => $label ) : ?>
		<?php
		$url = add_query_arg(
			array(
				'mns_panel' => 'patient',
				'mns_page'  => 'orders',
				'mns_tab'   => $key,
			),
			home_url( '/nursing-patient-panel/' )
		);
		if ( '' !== $q ) {
			$url = add_query_arg( 'q', $q, $url );
		}
		?>
		<a
			class="mns-tabs__btn<?php echo $tab === $key ? ' is-active' : ''; ?>"
			role="tab"
			aria-selected="<?php echo $tab === $key ? 'true' : 'false'; ?>"
			href="<?php echo esc_url( $url ); ?>"
		><?php echo esc_html( $label ); ?></a>
	<?php endforeach; ?>
</div>

<form class="mns-search-bar" method="get" role="search">
	<input type="hidden" name="mns_panel" value="patient">
	<input type="hidden" name="mns_page" value="orders">
	<input type="hidden" name="mns_tab" value="<?php echo esc_attr( $tab ); ?>">
	<label class="mns-field-block" style="margin:0;flex:1">
		<span class="screen-reader-text"><?php echo esc_html__( 'جستجوی کد پیگیری', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control">
			<input type="search" name="q" value="<?php echo esc_attr( $q ); ?>" placeholder="<?php echo esc_attr__( 'جستجو با کد پیگیری یا خدمت…', 'medyar-nursing-services' ); ?>" maxlength="100">
		</span>
	</label>
	<button type="submit" class="mns-outline-btn"><?php echo esc_html__( 'جستجو', 'medyar-nursing-services' ); ?></button>
</form>

<?php if ( empty( $filtered ) ) : ?>
	<?php
	$copy = $empty_copy[ $tab ] ?? array( MNS_Copy::t( 'state.empty_title' ), MNS_Copy::t( 'state.empty_body' ) );
	MNS_UI::empty_state(
		array(
			'title'     => $copy[0],
			'text'      => $copy[1],
			'cta_label' => 'active' === $tab ? __( 'ثبت سفارش', 'medyar-nursing-services' ) : '',
			'cta_url'   => 'active' === $tab ? MNS_Pages::panel_url( 'patient', 'new-order' ) : '',
			'icon'      => 'list',
		)
	);
	?>
<?php else : ?>
	<ul class="mns-order-cards">
		<?php foreach ( $filtered as $row ) : ?>
			<li class="mns-order-card mns-order-card--listed">
				<a class="mns-order-card__main" href="<?php echo esc_url( (string) ( $row['next_url'] ?? '#' ) ); ?>">
					<span class="mns-order-card__top">
						<span class="mns-order-card__service"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></span>
						<?php MNS_UI::status_badge( (string) ( $row['status'] ?? 'submitted' ), array( 'size' => 'sm' ) ); ?>
					</span>
					<span class="mns-order-card__meta">
						<?php echo esc_html( (string) ( $row['patient'] ?? '' ) ); ?>
						· <?php echo esc_html( MNS_Format::human( (string) ( $row['when'] ?? '' ) ) ); ?>
					</span>
					<?php if ( ! empty( $row['nurse'] ) ) : ?>
						<span class="mns-order-card__meta"><?php echo esc_html( sprintf( /* translators: nurse name */ __( 'پرستار: %s', 'medyar-nursing-services' ), (string) $row['nurse'] ) ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $row['recurring'] ) ) : ?>
						<span class="mns-order-card__badge"><?php echo esc_html( (string) ( $row['recurring_label'] ?? __( 'دوره‌ای', 'medyar-nursing-services' ) ) ); ?></span>
					<?php endif; ?>
					<span class="mns-order-card__code" dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $row['tracking'] ?? '' ) ) ); ?></span>
				</a>
				<?php if ( ! empty( $row['next_action'] ) && ! empty( $row['next_url'] ) ) : ?>
					<a class="mns-order-card__cta mns-outline-btn" href="<?php echo esc_url( (string) $row['next_url'] ); ?>">
						<?php echo esc_html( (string) $row['next_action'] ); ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
