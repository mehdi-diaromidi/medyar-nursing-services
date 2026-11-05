<?php
/**
 * Patient invoices — ListItem + tab counts (UX Phase D).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data  = MNS_Static_Fixtures::get( 'patient/invoices' );
$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
$tab   = isset( $_GET['mns_tab'] ) ? sanitize_key( wp_unslash( $_GET['mns_tab'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $tab, array( 'all', 'unpaid', 'paid' ), true ) ) {
	$tab = 'all';
}

if ( MNS_Dev::is( 'empty' ) ) {
	$items = array();
}

$status_label = static function ( string $status ): string {
	$map = array(
		'unpaid' => __( 'پرداخت‌نشده', 'medyar-nursing-services' ),
		'paid'   => __( 'پرداخت‌شده', 'medyar-nursing-services' ),
		'void'   => __( 'باطل', 'medyar-nursing-services' ),
		'failed' => __( 'ناموفق', 'medyar-nursing-services' ),
	);
	return $map[ $status ] ?? $status;
};

$counts = array(
	'all'    => count( $items ),
	'unpaid' => 0,
	'paid'   => 0,
);
foreach ( $items as $row ) {
	$st = (string) ( $row['status'] ?? '' );
	if ( 'unpaid' === $st ) {
		++$counts['unpaid'];
	}
	if ( 'paid' === $st ) {
		++$counts['paid'];
	}
}

$filtered = array();
foreach ( $items as $row ) {
	$st = (string) ( $row['status'] ?? '' );
	if ( 'unpaid' === $tab && 'unpaid' !== $st ) {
		continue;
	}
	if ( 'paid' === $tab && 'paid' !== $st ) {
		continue;
	}
	$filtered[] = $row;
}

$tabs = array(
	'all'    => __( 'همه', 'medyar-nursing-services' ),
	'unpaid' => __( 'پرداخت‌نشده', 'medyar-nursing-services' ),
	'paid'   => __( 'پرداخت‌شده', 'medyar-nursing-services' ),
);

MNS_UI::page_header(
	array(
		'title' => __( 'فاکتورها و پرداخت‌ها', 'medyar-nursing-services' ),
		'lead'  => __( 'جزئیات ردیف‌ها و وضعیت پرداخت.', 'medyar-nursing-services' ),
	)
);
?>

<div class="mns-tabs" role="tablist" aria-label="<?php echo esc_attr__( 'وضعیت فاکتورها', 'medyar-nursing-services' ); ?>">
	<?php foreach ( $tabs as $key => $label ) : ?>
		<?php
		$url   = add_query_arg( 'mns_tab', $key, MNS_Pages::panel_url( 'patient', 'invoices' ) );
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
			'title' => __( 'فاکتوری نیست', 'medyar-nursing-services' ),
			'text'  => __( 'پس از پایان خدمت، فاکتور اینجا نمایش داده می‌شود.', 'medyar-nursing-services' ),
			'icon'  => 'invoice',
		)
	);
	?>
<?php else : ?>
	<ul class="mns-list">
		<?php foreach ( $filtered as $row ) : ?>
			<?php
			$st = (string) ( $row['status'] ?? 'unpaid' );
			ob_start();
			echo '<span class="mns-status mns-status--' . esc_attr( 'unpaid' === $st ? 'waiting' : ( 'paid' === $st ? 'done' : 'muted' ) ) . '">';
			echo esc_html( $status_label( $st ) );
			echo '</span>';
			$badge = (string) ob_get_clean();
			$action = ! empty( $row['payable'] ) ? __( 'پرداخت', 'medyar-nursing-services' ) : __( 'جزئیات', 'medyar-nursing-services' );
			MNS_UI::list_item(
				array(
					'url'            => (string) ( $row['url'] ?? '#' ),
					'title'          => (string) ( $row['service'] ?? '' ),
					'meta'           => MNS_Format::human( (string) ( $row['when'] ?? '' ) ) . ' · ' . MNS_Format::toman( $row['amount'] ?? 0 ),
					'code'           => (string) ( $row['number'] ?? '' ),
					'badge_html'     => $badge,
					'action_label'   => $action,
					'action_primary' => ! empty( $row['payable'] ),
				)
			);
			?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
