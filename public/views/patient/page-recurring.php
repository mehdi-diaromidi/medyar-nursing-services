<?php
/**
 * Patient recurring programs: one card per program (pattern, progress, next visit, remaining visits).
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

$svc_icons = array(
	'سرم'     => 'droplet',
	'پانسمان' => 'bandage',
	'تزریق'   => 'pill',
);
$slot_chip = array(
	'submitted' => __( 'ثبت‌شده', 'medyar-nursing-services' ),
	'planned'   => __( 'برنامه‌ریزی‌شده', 'medyar-nursing-services' ),
);

MNS_UI::page_header(
	array(
		'title' => __( 'خدمات دوره‌ای', 'medyar-nursing-services' ),
		'lead'  => __( 'برنامه‌های تکرارشونده و نوبت‌های آینده.', 'medyar-nursing-services' ),
		'icon'  => 'repeat',
	)
);
?>
<div class="mns-rec">
	<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

	<div class="mns-rec__bar">
		<a class="mns-submit-btn mns-rec__new" href="<?php echo esc_url( $new_url ); ?>">
			<?php echo mns_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php echo esc_html__( 'برنامه جدید', 'medyar-nursing-services' ); ?></span>
		</a>
	</div>

	<?php if ( empty( $programs ) ) : ?>
		<?php
		MNS_UI::empty_state(
			array(
				'title' => __( 'برنامه دوره‌ای ندارید', 'medyar-nursing-services' ),
				'text'  => __( 'هنگام ثبت سفارش می‌توانید برنامهٔ تکرار را روشن کنید.', 'medyar-nursing-services' ),
				'icon'  => 'repeat',
			)
		);
		?>
	<?php else : ?>
		<?php foreach ( $programs as $prog ) : ?>
			<?php
			$pid     = (string) ( $prog['id'] ?? 'r' );
			$svc     = (string) ( $prog['service'] ?? '' );
			$paused  = 'paused' === (string) ( $prog['status'] ?? '' );
			$done    = max( 0, (int) ( $prog['done'] ?? 0 ) );
			$total   = max( 1, (int) ( $prog['total'] ?? 1 ) );
			$percent = min( 100, (int) round( $done / $total * 100 ) );
			$slots   = array_values( (array) ( $prog['slots'] ?? array() ) );
			$next    = ( ! $paused && ! empty( $slots ) ) ? $slots[0] : null;
			$rest    = $next ? array_slice( $slots, 1 ) : array();
			$icon    = 'repeat';
			foreach ( $svc_icons as $needle => $icon_name ) {
				if ( false !== strpos( $svc, $needle ) ) {
					$icon = $icon_name;
					break;
				}
			}
			?>
			<section class="mns-rec__card" aria-label="<?php echo esc_attr( $svc ); ?>">
				<header class="mns-rec__head">
					<span class="mns-rec__ico" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<div class="mns-rec__head-text">
						<h2 class="mns-rec__title"><?php echo esc_html( $svc ); ?></h2>
						<p class="mns-rec__meta">
							<span><?php echo esc_html( (string) ( $prog['patient'] ?? '' ) ); ?></span>
							<span><?php echo esc_html( trim( (string) ( $prog['pattern'] ?? '' ) . ' · ' . (string) ( $prog['pattern_detail'] ?? '' ), ' ·' ) ); ?></span>
							<?php if ( ! empty( $prog['preferred_nurse'] ) ) : ?>
								<span><?php echo esc_html( sprintf( /* translators: %s: nurse name */ __( 'پرستار ترجیحی: %s', 'medyar-nursing-services' ), (string) $prog['preferred_nurse'] ) ); ?></span>
							<?php endif; ?>
						</p>
					</div>
					<span class="mns-rec__state<?php echo $paused ? ' is-paused' : ''; ?>"><?php echo esc_html( (string) ( $prog['status_label'] ?? '' ) ); ?></span>
				</header>

				<div class="mns-rec__progress">
					<div class="mns-rec__progress-text">
						<strong>
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: done, 2: total */
									__( '%1$s از %2$s نوبت انجام شده', 'medyar-nursing-services' ),
									MNS_Format::digits( (string) $done ),
									MNS_Format::digits( (string) $total )
								)
							);
							?>
						</strong>
					</div>
					<div class="mns-rec__bar-track" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( (string) $total ); ?>" aria-valuenow="<?php echo esc_attr( (string) $done ); ?>">
						<span style="width: <?php echo esc_attr( (string) $percent ); ?>%"></span>
					</div>
				</div>

				<?php if ( $next ) : ?>
					<div class="mns-rec__next">
						<span class="mns-rec__next-label"><?php echo esc_html__( 'نوبت بعدی', 'medyar-nursing-services' ); ?></span>
						<div class="mns-rec__next-main">
							<strong><?php echo esc_html( MNS_Format::human( (string) ( $next['when'] ?? '' ) ) ); ?></strong>
							<span class="mns-rec__chip"><?php echo esc_html( $slot_chip[ (string) ( $next['status'] ?? '' ) ] ?? '' ); ?></span>
						</div>
						<div class="mns-rec__next-actions">
							<?php if ( ! empty( $next['url'] ) ) : ?>
								<a class="mns-outline-btn" href="<?php echo esc_url( (string) $next['url'] ); ?>"><?php echo esc_html__( 'جزئیات', 'medyar-nursing-services' ); ?></a>
							<?php else : ?>
								<button type="button" class="mns-outline-btn" data-mns-dialog-open="mns-skip-<?php echo esc_attr( $pid ); ?>-0"><?php echo esc_html__( 'رد این نوبت', 'medyar-nursing-services' ); ?></button>
							<?php endif; ?>
						</div>
					</div>
				<?php elseif ( $paused ) : ?>
					<p class="mns-rec__note"><?php echo esc_html__( 'برنامه متوقف است و نوبت جدیدی ساخته نمی‌شود.', 'medyar-nursing-services' ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $rest ) ) : ?>
					<ul class="mns-rec__slots" aria-label="<?php echo esc_attr__( 'نوبت‌های آینده', 'medyar-nursing-services' ); ?>">
						<?php foreach ( $rest as $k => $slot ) : ?>
							<li class="mns-rec__slot">
								<span class="mns-rec__slot-no"><?php echo esc_html( (string) ( $slot['label'] ?? '' ) ); ?></span>
								<span class="mns-rec__slot-when"><?php echo esc_html( MNS_Format::human( (string) ( $slot['when'] ?? '' ) ) ); ?></span>
								<span class="mns-rec__chip"><?php echo esc_html( $slot_chip[ (string) ( $slot['status'] ?? '' ) ] ?? '' ); ?></span>
								<?php if ( ! empty( $slot['url'] ) ) : ?>
									<a class="mns-rec__slot-act" href="<?php echo esc_url( (string) $slot['url'] ); ?>"><?php echo esc_html__( 'جزئیات', 'medyar-nursing-services' ); ?></a>
								<?php else : ?>
									<button type="button" class="mns-rec__slot-act" data-mns-dialog-open="mns-skip-<?php echo esc_attr( $pid . '-' . ( $k + 1 ) ); ?>"><?php echo esc_html__( 'رد کردن', 'medyar-nursing-services' ); ?></button>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<footer class="mns-rec__foot">
					<?php if ( ! $paused ) : ?>
						<p class="mns-rec__rule"><?php echo esc_html__( '۳ روز قبل از هر نوبت، سفارش آن ثبت می‌شود و پرستار تعیین می‌شود.', 'medyar-nursing-services' ); ?></p>
						<button type="button" class="mns-rec__stop" data-mns-dialog-open="mns-stop-<?php echo esc_attr( $pid ); ?>"><?php echo esc_html__( 'توقف برنامه', 'medyar-nursing-services' ); ?></button>
					<?php else : ?>
						<button type="button" class="mns-submit-btn mns-rec__resume" data-mns-mock-action data-mns-success="<?php echo esc_attr__( 'برنامه ادامه پیدا کرد.', 'medyar-nursing-services' ); ?>"><span><?php echo esc_html__( 'ادامه برنامه', 'medyar-nursing-services' ); ?></span></button>
					<?php endif; ?>
				</footer>
			</section>
		<?php endforeach; ?>

		<?php
		foreach ( $programs as $prog ) {
			$pid = (string) ( $prog['id'] ?? 'r' );
			$ds  = array_values( (array) ( $prog['slots'] ?? array() ) );
			foreach ( $ds as $k => $slot ) {
				if ( ! empty( $slot['url'] ) ) {
					continue;
				}
				ob_start();
				echo '<p>' . esc_html__( 'فقط این نوبت رد می‌شود؛ بقیهٔ برنامه ادامه دارد.', 'medyar-nursing-services' ) . '</p>';
				$body = ob_get_clean();
				ob_start();
				echo '<button type="button" class="mns-outline-btn" data-mns-dialog-close>' . esc_html( MNS_Copy::t( 'action.cancel' ) ) . '</button>';
				echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto mns-oview-danger" data-mns-mock-action data-mns-success="' . esc_attr__( 'نوبت رد شد.', 'medyar-nursing-services' ) . '" data-mns-dialog-close>' . esc_html__( 'بله، رد شود', 'medyar-nursing-services' ) . '</button>';
				$footer = ob_get_clean();
				MNS_UI::partial(
					'dialog',
					array(
						'id'          => 'mns-skip-' . $pid . '-' . $k,
						'title'       => __( 'این نوبت رد شود؟', 'medyar-nursing-services' ),
						'body_html'   => $body,
						'footer_html' => $footer,
						'variant'     => 'sheet',
					)
				);
			}
			if ( 'paused' !== (string) ( $prog['status'] ?? '' ) ) {
				ob_start();
				echo '<p>' . esc_html__( 'نوبت جدیدی ساخته نمی‌شود. نوبت‌هایی که سفارششان ثبت شده ادامه دارند و بعداً می‌توانید برنامه را ادامه دهید.', 'medyar-nursing-services' ) . '</p>';
				$body = ob_get_clean();
				ob_start();
				echo '<button type="button" class="mns-outline-btn" data-mns-dialog-close>' . esc_html( MNS_Copy::t( 'action.cancel' ) ) . '</button>';
				echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto mns-oview-danger" data-mns-mock-action data-mns-success="' . esc_attr__( 'برنامه متوقف شد.', 'medyar-nursing-services' ) . '" data-mns-dialog-close>' . esc_html__( 'بله، متوقف شود', 'medyar-nursing-services' ) . '</button>';
				$footer = ob_get_clean();
				MNS_UI::partial(
					'dialog',
					array(
						'id'          => 'mns-stop-' . $pid,
						'title'       => __( 'برنامه متوقف شود؟', 'medyar-nursing-services' ),
						'body_html'   => $body,
						'footer_html' => $footer,
						'variant'     => 'sheet',
					)
				);
			}
		}
		?>
	<?php endif; ?>
</div>
