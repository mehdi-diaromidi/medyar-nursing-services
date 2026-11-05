<?php
/**
 * Patient invoices list (Phase 4).
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
		$url = add_query_arg(
			array(
				'mns_panel' => 'patient',
				'mns_page'  => 'invoices',
				'mns_tab'   => $key,
			),
			home_url( '/nursing-patient-panel/' )
		);
		?>
		<a class="mns-tabs__btn<?php echo $tab === $key ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo $tab === $key ? 'true' : 'false'; ?>" href="<?php echo esc_url( $url ); ?>">
			<?php echo esc_html( $label ); ?>
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
	<ul class="mns-invoice-cards">
		<?php foreach ( $filtered as $row ) : ?>
			<?php $st = (string) ( $row['status'] ?? 'unpaid' ); ?>
			<li class="mns-invoice-card">
				<a class="mns-invoice-card__main" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
					<span class="mns-invoice-card__top">
						<span class="mns-invoice-card__service"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></span>
						<span class="mns-status mns-status--<?php echo esc_attr( 'unpaid' === $st ? 'waiting' : ( 'paid' === $st ? 'done' : 'muted' ) ); ?>">
							<?php echo esc_html( $status_label( $st ) ); ?>
						</span>
					</span>
					<span class="mns-invoice-card__meta">
						<span dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $row['number'] ?? '' ) ) ); ?></span>
						· <?php echo esc_html( MNS_Format::human( (string) ( $row['when'] ?? '' ) ) ); ?>
					</span>
					<span class="mns-invoice-card__amount"><?php echo esc_html( MNS_Format::toman( $row['amount'] ?? 0 ) ); ?></span>
				</a>
				<?php if ( ! empty( $row['payable'] ) ) : ?>
					<a class="mns-invoice-card__cta mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
						<?php echo esc_html__( 'پرداخت', 'medyar-nursing-services' ); ?>
					</a>
				<?php else : ?>
					<a class="mns-invoice-card__cta mns-outline-btn" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
						<?php echo esc_html__( 'جزئیات', 'medyar-nursing-services' ); ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
