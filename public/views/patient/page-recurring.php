<?php
/**
 * Patient recurring programs (Phase 4).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data     = MNS_Static_Fixtures::get( 'patient/recurring' );
$programs = isset( $data['programs'] ) && is_array( $data['programs'] ) ? $data['programs'] : array();

if ( MNS_Dev::is( 'empty' ) ) {
	$programs = array();
}

$new_url = add_query_arg( 'mns_recurring', '1', MNS_Pages::panel_url( 'patient', 'new-order' ) );

MNS_UI::page_header(
	array(
		'title'   => __( 'خدمات دوره‌ای', 'medyar-nursing-services' ),
		'lead'    => __( 'برنامه‌های تکرارشونده و نوبت‌های آینده.', 'medyar-nursing-services' ),
		'actions' => '<a class="mns-submit-btn mns-submit-btn--auto" href="' . esc_url( $new_url ) . '">' . esc_html__( 'برنامه جدید', 'medyar-nursing-services' ) . '</a>',
	)
);
?>

<?php if ( empty( $programs ) ) : ?>
	<?php
	MNS_UI::empty_state(
		array(
			'title'     => __( 'برنامه دوره‌ای ندارید', 'medyar-nursing-services' ),
			'text'      => __( 'هنگام ثبت سفارش می‌توانید برنامهٔ تکرار را روشن کنید.', 'medyar-nursing-services' ),
			'cta_label' => __( 'ثبت سفارش دوره‌ای', 'medyar-nursing-services' ),
			'cta_url'   => $new_url,
			'icon'      => 'calendar',
		)
	);
	?>
<?php else : ?>
	<?php foreach ( $programs as $prog ) : ?>
		<section class="mns-card mns-recurring-card">
			<div class="mns-card__head-row">
				<h2 class="mns-card__title"><?php echo esc_html( (string) ( $prog['service'] ?? '' ) ); ?></h2>
				<span class="mns-status mns-status--done"><?php echo esc_html( (string) ( $prog['status_label'] ?? '' ) ); ?></span>
			</div>
			<ul class="mns-kv">
				<li><span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $prog['patient'] ?? '' ) ); ?></strong></li>
				<li><span><?php echo esc_html__( 'تناوب', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $prog['pattern'] ?? '' ) ); ?> · <?php echo esc_html( (string) ( $prog['pattern_detail'] ?? '' ) ); ?></strong></li>
				<?php if ( ! empty( $prog['preferred_nurse'] ) ) : ?>
					<li><span><?php echo esc_html__( 'پرستار ترجیحی', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) $prog['preferred_nurse'] ); ?></strong></li>
				<?php endif; ?>
			</ul>
			<h3 class="mns-card__subtitle"><?php echo esc_html__( 'نوبت‌های آینده', 'medyar-nursing-services' ); ?></h3>
			<ul class="mns-slot-list">
				<?php foreach ( (array) ( $prog['slots'] ?? array() ) as $i => $slot ) : ?>
					<li class="mns-slot-list__item">
						<div>
							<strong><?php echo esc_html( (string) ( $slot['label'] ?? '' ) ); ?></strong>
							<span class="mns-slot-list__when"><?php echo esc_html( MNS_Format::human( (string) ( $slot['when'] ?? '' ) ) ); ?></span>
						</div>
						<div class="mns-slot-list__actions">
							<?php if ( ! empty( $slot['url'] ) ) : ?>
								<a class="mns-outline-btn" href="<?php echo esc_url( (string) $slot['url'] ); ?>"><?php echo esc_html__( 'مشاهده', 'medyar-nursing-services' ); ?></a>
							<?php else : ?>
								<button type="button" class="mns-outline-btn" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'ویرایش زمان به‌زودی.', 'medyar-nursing-services' ); ?>"><?php echo esc_html__( 'ویرایش زمان', 'medyar-nursing-services' ); ?></button>
								<button type="button" class="mns-link-btn" data-mns-dialog-open="mns-cancel-slot-<?php echo esc_attr( (string) ( $prog['id'] ?? 'r' ) . '-' . $i ); ?>"><?php echo esc_html__( 'لغو نوبت', 'medyar-nursing-services' ); ?></button>
							<?php endif; ?>
						</div>
					</li>
					<?php
					if ( empty( $slot['url'] ) ) {
						ob_start();
						echo '<p>' . esc_html__( 'فقط این نوبت لغو می‌شود؛ بقیهٔ برنامه ادامه دارد.', 'medyar-nursing-services' ) . '</p>';
						$body = ob_get_clean();
						ob_start();
						echo '<button type="button" class="mns-outline-btn" data-mns-dialog-close>' . esc_html( MNS_Copy::t( 'action.cancel' ) ) . '</button>';
						echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-mock-action data-mns-success="' . esc_attr__( 'نوبت لغو شد.', 'medyar-nursing-services' ) . '" data-mns-dialog-close>' . esc_html__( 'بله، لغو شود', 'medyar-nursing-services' ) . '</button>';
						$footer = ob_get_clean();
						MNS_UI::partial(
							'dialog',
							array(
								'id'          => 'mns-cancel-slot-' . (string) ( $prog['id'] ?? 'r' ) . '-' . $i,
								'title'       => __( 'لغو این نوبت؟', 'medyar-nursing-services' ),
								'body_html'   => $body,
								'footer_html' => $footer,
								'variant'     => 'sheet',
							)
						);
					}
					?>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>
<?php endif; ?>
