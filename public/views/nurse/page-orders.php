<?php
/**
 * Nurse orders list (Phase 5).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data  = MNS_Static_Fixtures::get( 'nurse/orders' );
$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
$tab   = isset( $_GET['mns_tab'] ) ? sanitize_key( wp_unslash( $_GET['mns_tab'] ) ) : 'active'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $tab, array( 'active', 'past' ), true ) ) {
	$tab = 'active';
}

if ( MNS_Dev::is( 'empty' ) ) {
	$items = array();
}

$filtered = array();
foreach ( $items as $row ) {
	if ( (string) ( $row['tab'] ?? 'active' ) === $tab ) {
		$filtered[] = $row;
	}
}

$tabs = array(
	'active' => __( 'فعال', 'medyar-nursing-services' ),
	'past'   => __( 'گذشته', 'medyar-nursing-services' ),
);

MNS_UI::page_header(
	array(
		'title' => __( 'سفارش‌های من', 'medyar-nursing-services' ),
		'lead'  => __( 'شروع/پایان خدمت، چک‌لیست و گزارش.', 'medyar-nursing-services' ),
	)
);
?>

<div class="mns-tabs" role="tablist">
	<?php foreach ( $tabs as $key => $label ) : ?>
		<?php
		$url = add_query_arg(
			array(
				'mns_panel' => 'nurse',
				'mns_page'  => 'orders',
				'mns_tab'   => $key,
			),
			home_url( '/nursing-nurse-panel/' )
		);
		?>
		<a class="mns-tabs__btn<?php echo $tab === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
	<?php endforeach; ?>
</div>

<?php if ( empty( $filtered ) ) : ?>
	<?php
	MNS_UI::empty_state(
		array(
			'title' => __( 'سفارشی در این بخش نیست', 'medyar-nursing-services' ),
			'text'  => '',
			'icon'  => 'list',
		)
	);
	?>
<?php else : ?>
	<ul class="mns-order-cards">
		<?php foreach ( $filtered as $row ) : ?>
			<li class="mns-order-card mns-order-card--listed">
				<a class="mns-order-card__main" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
					<span class="mns-order-card__top">
						<span class="mns-order-card__service"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></span>
						<?php MNS_UI::status_badge( (string) ( $row['status'] ?? 'assigned' ), array( 'size' => 'sm' ) ); ?>
					</span>
					<span class="mns-order-card__meta">
						<?php echo esc_html( (string) ( $row['patient'] ?? '' ) ); ?>
						· <?php echo esc_html( (string) ( $row['area'] ?? '' ) ); ?>
						· <?php echo esc_html( MNS_Format::human( (string) ( $row['when'] ?? '' ) ) ); ?>
					</span>
					<span class="mns-order-card__code" dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $row['tracking'] ?? '' ) ) ); ?></span>
				</a>
				<a class="mns-order-card__cta mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
					<?php echo esc_html__( 'ورود به جزئیات', 'medyar-nursing-services' ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
