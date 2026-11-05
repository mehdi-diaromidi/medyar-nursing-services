<?php
/**
 * Nurse dashboard — metrics + today's orders (UX Phase D).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data      = MNS_Static_Fixtures::get( 'nurse/dashboard' );
$profile   = MNS_Static_Fixtures::get( 'nurse/profile' );
$metrics   = isset( $data['metrics'] ) && is_array( $data['metrics'] ) ? $data['metrics'] : array();
$today     = isset( $data['today_orders'] ) && is_array( $data['today_orders'] ) ? $data['today_orders'] : array();
$accepting = ! empty( $data['accepting'] );
$name      = (string) ( $profile['display_name'] ?? __( 'پرستار', 'medyar-nursing-services' ) );
$initial   = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );
$hospital  = (string) ( $data['hospital'] ?? '' );
$avail_url = MNS_Pages::panel_url( 'nurse', 'availability' );
$ratings_u = MNS_Pages::panel_url( 'nurse', 'ratings' );

if ( MNS_Dev::is( 'empty' ) ) {
	$today = array();
}
if ( MNS_Dev::is( 'offline' ) ) {
	$accepting = false;
}

MNS_UI::page_header(
	array(
		'title' => __( 'داشبورد', 'medyar-nursing-services' ),
		'lead'  => __( 'سفارش‌های امروز و وضعیت پذیرش.', 'medyar-nursing-services' ),
	)
);
?>

<section class="mns-profile-hero mns-nurse-hero">
	<div class="mns-profile-hero__avatar" aria-hidden="true"><?php echo esc_html( $initial ); ?></div>
	<div class="mns-nurse-hero__text">
		<p class="mns-profile-hero__name"><?php echo esc_html( $name ); ?></p>
		<p class="mns-profile-hero__meta">
			<?php echo esc_html__( 'تأییدشده', 'medyar-nursing-services' ); ?>
			<?php if ( '' !== $hospital ) : ?>
				· <?php echo esc_html( $hospital ); ?>
			<?php endif; ?>
		</p>
	</div>
</section>

<?php
MNS_UI::alert_card(
	array(
		'tone'      => $accepting ? 'success' : 'warning',
		'title'     => $accepting ? __( 'آماده پذیرش درخواست', 'medyar-nursing-services' ) : __( 'پذیرش خاموش است', 'medyar-nursing-services' ),
		'text'      => $accepting
			? __( 'در تخصیص خودکار پیشنهاد می‌شوید.', 'medyar-nursing-services' )
			: __( 'تا روشن نشود در تخصیص خودکار پیشنهاد نمی‌شوید.', 'medyar-nursing-services' ),
		'cta_label' => __( 'مدیریت پذیرش و برنامه', 'medyar-nursing-services' ),
		'cta_url'   => $avail_url,
	)
);
?>

<div class="mns-metrics">
	<div class="mns-metric mns-metric--hero">
		<span class="mns-metric__label"><?php echo esc_html__( 'امروز', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( (string) ( $metrics['today'] ?? 0 ) ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'آینده', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( (string) ( $metrics['upcoming'] ?? 0 ) ) ); ?></span>
	</div>
	<a class="mns-metric" href="<?php echo esc_url( $ratings_u ); ?>">
		<span class="mns-metric__label"><?php echo esc_html__( 'میانگین امتیاز', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( number_format( (float) ( $metrics['rating_avg'] ?? 0 ), 1 ) ) ); ?></span>
	</a>
</div>

<section class="mns-card">
	<?php
	MNS_UI::section_head(
		array(
			'title'      => __( 'سفارش‌های امروز', 'medyar-nursing-services' ),
			'link_label' => __( 'همه', 'medyar-nursing-services' ),
			'link_url'   => MNS_Pages::panel_url( 'nurse', 'orders' ),
		)
	);
	?>
	<?php if ( empty( $today ) ) : ?>
		<?php
		MNS_UI::empty_state(
			array(
				'title' => __( 'سفارشی برای امروز نیست', 'medyar-nursing-services' ),
				'text'  => __( 'وقتی سفارشی تخصیص یابد، اینجا می‌آید.', 'medyar-nursing-services' ),
				'icon'  => 'calendar',
			)
		);
		?>
	<?php else : ?>
		<ul class="mns-list">
			<?php foreach ( $today as $row ) : ?>
				<?php
				ob_start();
				MNS_UI::status_badge( (string) ( $row['status'] ?? 'assigned' ) );
				$badge = (string) ob_get_clean();
				MNS_UI::list_item(
					array(
						'url'        => (string) ( $row['url'] ?? '#' ),
						'title'      => (string) ( $row['service'] ?? '' ),
						'meta'       => MNS_Format::digits( (string) ( $row['time'] ?? '' ) ) . ' · ' . (string) ( $row['area'] ?? '' ),
						'badge_html' => $badge,
					)
				);
				?>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
