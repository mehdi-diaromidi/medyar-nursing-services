<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$data = MNS_Static_Fixtures::get( 'nurse/availability' );
$week = isset( $data['week'] ) && is_array( $data['week'] ) ? $data['week'] : array();
$areas = isset( $data['areas'] ) ? (string) $data['areas'] : '';
$services = isset( $data['services'] ) ? (string) $data['services'] : '';
$accepting = ! empty( $data['accepting'] );
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'برنامه و دسترسی', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html__( 'برنامه هفتگی ثابت، لغو روزهای خاص و پذیرش درخواست.', 'medyar-nursing-services' ); ?></p>

<div class="mns-card" style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
	<div>
		<strong><?php echo esc_html__( 'پذیرش درخواست', 'medyar-nursing-services' ); ?></strong>
		<p style="margin:4px 0 0;color:var(--mns-muted-strong);font-size:0.875rem"><?php echo esc_html__( 'اگر خاموش باشد در تخصیص خودکار پیشنهاد نمی‌شوید.', 'medyar-nursing-services' ); ?></p>
	</div>
	<button type="button" class="mns-switch<?php echo $accepting ? ' is-on' : ''; ?>" data-mns-switch aria-pressed="<?php echo $accepting ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr__( 'پذیرش درخواست', 'medyar-nursing-services' ); ?>"></button>
</div>

<div class="mns-availability">
	<section class="mns-availability-calendar" data-mns-availability-calendar data-blocked='{"2026-11-12":1}' aria-label="<?php echo esc_attr__( 'تقویم دسترسی', 'medyar-nursing-services' ); ?>">
		<div class="mns-availability-calendar__head">
			<button type="button" class="mns-availability-calendar__nav" data-mns-cal-prev aria-label="<?php echo esc_attr__( 'ماه قبل', 'medyar-nursing-services' ); ?>">›</button>
			<div class="mns-availability-calendar__title" data-mns-cal-title></div>
			<button type="button" class="mns-availability-calendar__nav" data-mns-cal-next aria-label="<?php echo esc_attr__( 'ماه بعد', 'medyar-nursing-services' ); ?>">‹</button>
		</div>
		<div class="mns-availability-calendar__dow" aria-hidden="true">
			<span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
		</div>
		<div class="mns-availability-calendar__grid" data-mns-cal-grid></div>
		<div data-mns-cal-detail hidden style="margin-top:14px"></div>
	</section>

	<div>
		<section class="mns-card" style="margin-bottom:16px">
			<h2 class="mns-card__title"><?php echo esc_html__( 'برنامه هفتگی', 'medyar-nursing-services' ); ?></h2>
			<div class="mns-week-schedule">
				<?php foreach ( $week as $row ) : ?>
					<div class="mns-week-schedule__row">
						<strong><?php echo esc_html( (string) ( $row['day'] ?? '' ) ); ?></strong>
						<span><?php echo esc_html( (string) ( $row['hours'] ?? '' ) ); ?></span>
						<button type="button" class="mns-switch<?php echo ! empty( $row['on'] ) ? ' is-on' : ''; ?>" data-mns-switch aria-pressed="<?php echo ! empty( $row['on'] ) ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( (string) ( $row['day'] ?? '' ) ); ?>"></button>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<section class="mns-card">
			<h2 class="mns-card__title"><?php echo esc_html__( 'مناطق و صلاحیت', 'medyar-nursing-services' ); ?></h2>
			<p style="margin:0 0 8px;font-size:0.875rem;color:var(--mns-muted-strong)"><strong><?php echo esc_html__( 'مناطق:', 'medyar-nursing-services' ); ?></strong> <?php echo esc_html( $areas ); ?></p>
			<p style="margin:0;font-size:0.875rem;color:var(--mns-muted-strong)"><strong><?php echo esc_html__( 'خدمات مجاز:', 'medyar-nursing-services' ); ?></strong> <?php echo esc_html( $services ); ?></p>
		</section>
	</div>
</div>
