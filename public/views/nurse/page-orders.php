<?php
/**
 * Nurse orders list — ListItem + counts (UX Phase D).
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

$counts = array(
	'active' => 0,
	'past'   => 0,
);
foreach ( $items as $row ) {
	$key = (string) ( $row['tab'] ?? 'active' );
	if ( isset( $counts[ $key ] ) ) {
		++$counts[ $key ];
	}
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

<div class="mns-tabs" role="tablist" aria-label="<?php echo esc_attr__( 'وضعیت سفارش‌ها', 'medyar-nursing-services' ); ?>">
	<?php foreach ( $tabs as $key => $label ) : ?>
		<?php
		$url   = add_query_arg( 'mns_tab', $key, MNS_Pages::panel_url( 'nurse', 'orders' ) );
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
	<ul class="mns-list">
		<?php foreach ( $filtered as $row ) : ?>
			<?php
			ob_start();
			MNS_UI::status_badge( (string) ( $row['status'] ?? 'assigned' ) );
			$badge = (string) ob_get_clean();
			MNS_UI::list_item(
				array(
					'url'          => (string) ( $row['url'] ?? '#' ),
					'title'        => (string) ( $row['service'] ?? '' ),
					'meta'         => (string) ( $row['patient'] ?? '' ) . ' · ' . (string) ( $row['area'] ?? '' ) . ' · ' . MNS_Format::human( (string) ( $row['when'] ?? '' ) ),
					'code'         => (string) ( $row['tracking'] ?? '' ),
					'badge_html'   => $badge,
					'action_label' => __( 'جزئیات', 'medyar-nursing-services' ),
				)
			);
			?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
