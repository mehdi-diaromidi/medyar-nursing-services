<?php
/**
 * Nurse dashboard (Phase 5) — hero + accepting + today list.
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

if ( MNS_Dev::is( 'empty' ) ) {
	$today = array();
}
if ( MNS_Dev::is( 'offline' ) ) {
	$accepting = false;
}

MNS_UI::page_header(
	array(
		'title' => __( 'داشبورد', 'medyar-nursing-services' ),
		'lead'  => __( 'نوبت‌های امروز و وضعیت پذیرش.', 'medyar-nursing-services' ),
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
	<div class="mns-nurse-hero__accept">
		<span class="mns-nurse-hero__accept-label">
			<strong><?php echo esc_html__( 'آماده پذیرش درخواست', 'medyar-nursing-services' ); ?></strong>
			<small><?php echo $accepting ? esc_html__( 'آنلاین — در تخصیص پیشنهاد می‌شوید', 'medyar-nursing-services' ) : esc_html__( 'آفلاین — در تخصیص خودکار پیشنهاد نمی‌شوید.', 'medyar-nursing-services' ); ?></small>
		</span>
		<button
			type="button"
			class="mns-switch<?php echo $accepting ? ' is-on' : ''; ?>"
			data-mns-switch
			aria-pressed="<?php echo $accepting ? 'true' : 'false'; ?>"
			aria-label="<?php echo esc_attr__( 'پذیرش درخواست', 'medyar-nursing-services' ); ?>"
		></button>
	</div>
</section>

<div class="mns-metrics">
	<div class="mns-metric mns-metric--hero">
		<span class="mns-metric__label"><?php echo esc_html__( 'امروز', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( (string) ( $metrics['today'] ?? 0 ) ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'آینده', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( (string) ( $metrics['upcoming'] ?? 0 ) ) ); ?></span>
	</div>
	<div class="mns-metric">
		<span class="mns-metric__label"><?php echo esc_html__( 'میانگین امتیاز', 'medyar-nursing-services' ); ?></span>
		<span class="mns-metric__value"><?php echo esc_html( MNS_Format::digits( number_format( (float) ( $metrics['rating_avg'] ?? 0 ), 1 ) ) ); ?></span>
	</div>
</div>

<section class="mns-card">
	<div class="mns-card__head-row">
		<h2 class="mns-card__title"><?php echo esc_html__( 'نوبت‌های امروز', 'medyar-nursing-services' ); ?></h2>
		<a class="mns-link-btn" href="<?php echo esc_url( MNS_Pages::panel_url( 'nurse', 'orders' ) ); ?>"><?php echo esc_html__( 'همه', 'medyar-nursing-services' ); ?></a>
	</div>
	<?php if ( empty( $today ) ) : ?>
		<?php
		MNS_UI::empty_state(
			array(
				'title' => __( 'نوبتی برای امروز نیست', 'medyar-nursing-services' ),
				'text'  => __( 'وقتی سفارشی تخصیص یابد، اینجا می‌آید.', 'medyar-nursing-services' ),
				'icon'  => 'calendar',
			)
		);
		?>
	<?php else : ?>
		<ul class="mns-order-cards">
			<?php foreach ( $today as $row ) : ?>
				<li>
					<a class="mns-order-card" href="<?php echo esc_url( (string) ( $row['url'] ?? '#' ) ); ?>">
						<span class="mns-order-card__top">
							<span class="mns-order-card__service"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></span>
							<?php MNS_UI::status_badge( (string) ( $row['status'] ?? 'assigned' ), array( 'size' => 'sm' ) ); ?>
						</span>
						<span class="mns-order-card__meta">
							<?php echo esc_html( MNS_Format::digits( (string) ( $row['time'] ?? '' ) ) ); ?>
							· <?php echo esc_html( (string) ( $row['area'] ?? '' ) ); ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
