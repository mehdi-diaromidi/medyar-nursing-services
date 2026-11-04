<?php
/**
 * Patient dashboard — priority action + shortcuts (Phase 3).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data = MNS_Static_Fixtures::get( 'patient/dashboard' );
$variants = isset( $data['variants'] ) && is_array( $data['variants'] ) ? $data['variants'] : array();
$action   = isset( $data['primary_action'] ) && is_array( $data['primary_action'] ) ? $data['primary_action'] : array();

$state = MNS_Dev::state();
if ( isset( $variants[ $state ] ) && is_array( $variants[ $state ] ) ) {
	$action = $variants[ $state ];
} elseif ( MNS_Dev::is( 'empty' ) && isset( $variants['new_user'] ) ) {
	$action = $variants['new_user'];
}

$categories = isset( $data['categories'] ) && is_array( $data['categories'] ) ? $data['categories'] : array();
$upcoming   = isset( $data['upcoming'] ) && is_array( $data['upcoming'] ) ? $data['upcoming'] : array();
$loyalty    = isset( $data['loyalty'] ) && is_array( $data['loyalty'] ) ? $data['loyalty'] : array();
$last       = isset( $data['last_service'] ) && is_array( $data['last_service'] ) ? $data['last_service'] : array();

if ( MNS_Dev::is( 'empty' ) || ( isset( $action['type'] ) && 'new_user' === $action['type'] ) ) {
	$upcoming = array();
}

$new_url = MNS_Pages::panel_url( 'patient', 'new-order' );
?>
<?php
MNS_UI::page_header(
	array(
		'title' => __( 'خانه', 'medyar-nursing-services' ),
		'lead'  => __( 'الان چه خبر است و قدم بعدی چیست؟', 'medyar-nursing-services' ),
	)
);
?>

<?php if ( ! empty( $action ) ) : ?>
	<section class="mns-priority-card mns-priority-card--<?php echo esc_attr( (string) ( $action['type'] ?? 'none' ) ); ?>">
		<div class="mns-priority-card__body">
			<h2 class="mns-priority-card__title"><?php echo esc_html( (string) ( $action['title'] ?? '' ) ); ?></h2>
			<p class="mns-priority-card__text"><?php echo esc_html( (string) ( $action['text'] ?? '' ) ); ?></p>
		</div>
		<?php if ( ! empty( $action['cta_url'] ) && ! empty( $action['cta_label'] ) ) : ?>
			<a class="mns-submit-btn mns-submit-btn--auto" href="<?php echo esc_url( (string) $action['cta_url'] ); ?>">
				<?php echo esc_html( (string) $action['cta_label'] ); ?>
			</a>
		<?php endif; ?>
	</section>
<?php endif; ?>

<section class="mns-card" aria-labelledby="mns-quick-title">
	<h2 id="mns-quick-title" class="mns-card__title"><?php echo esc_html__( 'ثبت سریع سفارش', 'medyar-nursing-services' ); ?></h2>
	<div class="mns-quick-grid">
		<?php foreach ( $categories as $cat ) : ?>
			<?php
			$url = add_query_arg( 'mns_service', (string) ( $cat['slug'] ?? '' ), $new_url );
			?>
			<a class="mns-quick-chip" href="<?php echo esc_url( $url ); ?>">
				<span class="mns-quick-chip__icon" aria-hidden="true"><?php echo mns_icon( (string) ( $cat['icon'] ?? 'list' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html( (string) ( $cat['label'] ?? '' ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php if ( ! empty( $last['slug'] ) ) : ?>
		<p class="mns-actions-row" style="margin-top:var(--mns-space-4)">
			<a class="mns-outline-btn" href="<?php echo esc_url( add_query_arg( 'mns_service', (string) $last['slug'], $new_url ) ); ?>">
				<?php echo esc_html( (string) ( $last['label'] ?? __( 'تکرار آخرین خدمت', 'medyar-nursing-services' ) ) ); ?>
			</a>
		</p>
	<?php endif; ?>
</section>

<section class="mns-card" aria-labelledby="mns-up-title">
	<div class="mns-card__head-row">
		<h2 id="mns-up-title" class="mns-card__title"><?php echo esc_html__( 'سفارش‌های پیش‌رو', 'medyar-nursing-services' ); ?></h2>
		<a class="mns-link-btn" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'orders' ) ); ?>"><?php echo esc_html__( 'همه', 'medyar-nursing-services' ); ?></a>
	</div>
	<?php if ( empty( $upcoming ) ) : ?>
		<?php
		MNS_UI::empty_state(
			array(
				'title'     => __( 'سفارش پیش‌رویی نیست', 'medyar-nursing-services' ),
				'text'      => __( 'وقتی سفارشی ثبت کنید، اینجا نمایش داده می‌شود.', 'medyar-nursing-services' ),
				'cta_label' => __( 'ثبت سفارش', 'medyar-nursing-services' ),
				'cta_url'   => $new_url,
				'icon'      => 'calendar',
			)
		);
		?>
	<?php else : ?>
		<ul class="mns-order-cards">
			<?php foreach ( $upcoming as $row ) : ?>
				<li>
					<a class="mns-order-card" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
						<span class="mns-order-card__top">
							<span class="mns-order-card__service"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></span>
							<?php MNS_UI::status_badge( (string) ( $row['status'] ?? 'submitted' ), array( 'size' => 'sm' ) ); ?>
						</span>
						<span class="mns-order-card__meta">
							<?php echo esc_html( MNS_Format::human( (string) ( $row['when'] ?? '' ) ) ); ?>
							· <?php echo esc_html( (string) ( $row['patient'] ?? '' ) ); ?>
						</span>
						<span class="mns-order-card__code" dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $row['tracking'] ?? '' ) ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>

<?php if ( ! empty( $loyalty ) ) : ?>
	<?php
	MNS_UI::stamp_card(
		array(
			'filled' => (int) ( $loyalty['filled'] ?? 0 ),
			'total'  => (int) ( $loyalty['total'] ?? 5 ),
			'hint'   => (string) ( $loyalty['hint'] ?? '' ),
		)
	);
	?>
	<p class="mns-actions-row">
		<a class="mns-link-btn" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'loyalty' ) ); ?>"><?php echo esc_html__( 'کارت وفاداری و تخفیف', 'medyar-nursing-services' ); ?></a>
	</p>
<?php endif; ?>

<section class="mns-card mns-shortcut-row">
	<a class="mns-shortcut" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'patients' ) ); ?>">
		<span class="mns-shortcut__icon" aria-hidden="true"><?php echo mns_icon( 'users' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span>
			<strong><?php echo esc_html__( 'بیماران من', 'medyar-nursing-services' ); ?></strong>
			<small><?php echo esc_html__( 'مدیریت افراد ذخیره‌شده', 'medyar-nursing-services' ); ?></small>
		</span>
	</a>
	<a class="mns-shortcut" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'invoices' ) ); ?>">
		<span class="mns-shortcut__icon" aria-hidden="true"><?php echo mns_icon( 'invoice' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span>
			<strong><?php echo esc_html__( 'فاکتورها', 'medyar-nursing-services' ); ?></strong>
			<small><?php echo esc_html__( 'پرداخت و رسیدها', 'medyar-nursing-services' ); ?></small>
		</span>
	</a>
</section>
