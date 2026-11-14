<?php
/**
 * Surveys: open queue, rating form (three axes), sent history, complaint path.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data       = MNS_Static_Fixtures::get( 'patient/surveys' );
$open       = isset( $data['open'] ) && is_array( $data['open'] ) ? $data['open'] : array();
$done       = isset( $data['done'] ) && is_array( $data['done'] ) ? $data['done'] : array();
$complaints = isset( $data['complaints'] ) && is_array( $data['complaints'] ) ? $data['complaints'] : array();

$mode = isset( $_GET['mns_tab'] ) ? sanitize_key( wp_unslash( $_GET['mns_tab'] ) ) : 'open'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $mode, array( 'open', 'done', 'complaint' ), true ) ) {
	$mode = 'open';
}

if ( MNS_Dev::is( 'empty' ) ) {
	$open = array();
}
if ( MNS_Dev::is( 'submitted' ) ) {
	$mode = 'done';
}

$active  = isset( $_GET['mns_survey'] ) ? sanitize_key( wp_unslash( $_GET['mns_survey'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current = null;
foreach ( $open as $row ) {
	if ( (string) ( $row['id'] ?? '' ) === $active ) {
		$current = $row;
		break;
	}
}

$counts   = array(
	'open'      => count( $open ),
	'done'      => count( $done ),
	'complaint' => count( $complaints ),
);
$base_url = MNS_Pages::panel_url( 'patient', 'surveys' );
$new_url  = MNS_Pages::panel_url( 'patient', 'new-order' );
$svc_icons = array(
	'سرم'     => 'droplet',
	'پانسمان' => 'bandage',
	'تزریق'   => 'pill',
);
$svc_icon  = static function ( string $svc ) use ( $svc_icons ): string {
	foreach ( $svc_icons as $needle => $name ) {
		if ( false !== strpos( $svc, $needle ) ) {
			return $name;
		}
	}
	return 'list';
};
$words = array(
	__( 'خیلی ضعیف', 'medyar-nursing-services' ),
	__( 'ضعیف', 'medyar-nursing-services' ),
	__( 'متوسط', 'medyar-nursing-services' ),
	__( 'خوب', 'medyar-nursing-services' ),
	__( 'عالی', 'medyar-nursing-services' ),
);

MNS_UI::page_header(
	array(
		'title' => __( 'پرسشنامه‌ها', 'medyar-nursing-services' ),
		'icon'  => 'star',
	)
);
?>
<div class="mns-sv">
	<nav class="mns-sv__tabs" aria-label="<?php echo esc_attr__( 'وضعیت پرسشنامه‌ها', 'medyar-nursing-services' ); ?>">
		<?php
		$tabs = array(
			'open'      => __( 'باز', 'medyar-nursing-services' ),
			'done'      => __( 'ارسال‌شده', 'medyar-nursing-services' ),
			'complaint' => __( 'نارضایتی', 'medyar-nursing-services' ),
		);
		foreach ( $tabs as $key => $label ) :
			?>
			<a class="mns-sv__tab<?php echo $mode === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'mns_tab', $key, $base_url ) ); ?>"<?php echo $mode === $key ? ' aria-current="page"' : ''; ?>>
				<span><?php echo esc_html( $label ); ?></span>
				<span class="mns-sv__count"><?php echo esc_html( MNS_Format::digits( (string) ( $counts[ $key ] ?? 0 ) ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

	<?php if ( 'open' === $mode ) : ?>
		<?php if ( $current ) : ?>
			<?php
			$reasons = array(
					'science' => array(
						'wrong'   => __( 'کار را درست انجام نداد', 'medyar-nursing-services' ),
						'hygiene' => __( 'بهداشت رعایت نشد', 'medyar-nursing-services' ),
						'explain' => __( 'توضیح لازم را نداد', 'medyar-nursing-services' ),
						'pain'    => __( 'درد یا آسیب بیش از انتظار', 'medyar-nursing-services' ),
					),
					'ethics'  => array(
						'rude'      => __( 'برخورد نامناسب', 'medyar-nursing-services' ),
						'impatient' => __( 'بی‌حوصلگی یا عجله', 'medyar-nursing-services' ),
						'privacy'   => __( 'رعایت حریم خصوصی نشد', 'medyar-nursing-services' ),
						'pay'       => __( 'درخواست پول یا هدیه', 'medyar-nursing-services' ),
					),
					'orderly' => array(
						'late'       => __( 'تأخیر در رسیدن', 'medyar-nursing-services' ),
						'noshow'     => __( 'بدقولی یا نیامدن', 'medyar-nursing-services' ),
						'tools'      => __( 'وسایل ناقص یا نامرتب', 'medyar-nursing-services' ),
						'appearance' => __( 'ظاهر و آراستگی نامناسب', 'medyar-nursing-services' ),
					),
				);
				$axes = array(
				'science' => array( __( 'علمی', 'medyar-nursing-services' ), __( 'دانش و مهارت پرستار', 'medyar-nursing-services' ), 'activity' ),
				'ethics'  => array( __( 'اخلاق و خوشرویی', 'medyar-nursing-services' ), __( 'رفتار و برخورد با بیمار', 'medyar-nursing-services' ), 'heart' ),
				'orderly' => array( __( 'نظم در کار', 'medyar-nursing-services' ), __( 'وقت‌شناسی و آراستگی', 'medyar-nursing-services' ), 'clock' ),
			);
			?>
			<form class="mns-sv__card mns-sv__form" method="post" data-mns-mock="ok" data-mns-survey-form data-mns-complaint-url="<?php echo esc_url( add_query_arg( 'mns_tab', 'complaint', $base_url ) ); ?>" data-mns-success-negative="<?php echo esc_attr__( 'نظر شما ثبت شد و به‌عنوان نارضایتی برای پیگیری پشتیبانی ثبت شد.', 'medyar-nursing-services' ); ?>" data-mns-success="<?php echo esc_attr__( 'نظر شما ثبت شد. سپاس.', 'medyar-nursing-services' ); ?>">
				<header class="mns-sv__order">
					<span class="mns-sv__ico" aria-hidden="true"><?php echo mns_icon( $svc_icon( (string) ( $current['service'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<div class="mns-sv__order-text">
						<h2 class="mns-sv__title"><?php echo esc_html( (string) ( $current['service'] ?? '' ) ); ?></h2>
						<p class="mns-sv__meta"><?php echo esc_html( (string) ( $current['nurse'] ?? '' ) ); ?> · <?php echo esc_html( MNS_Format::human( (string) ( $current['when'] ?? '' ) ) ); ?></p>
					</div>
					<span class="mns-sv__code" dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $current['order'] ?? '' ) ) ); ?></span>
				</header>

				<div class="mns-sv__axes">
					<?php foreach ( $axes as $axis => $meta ) : ?>
						<div class="mns-sv__block">
						<div class="mns-sv__axis">
							<div class="mns-sv__axis-text">
								<span class="mns-sv__axis-ico" aria-hidden="true"><?php echo mns_icon( $meta[2] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<div>
									<strong><?php echo esc_html( $meta[0] ); ?></strong>
									<small><?php echo esc_html( $meta[1] ); ?></small>
								</div>
							</div>
							<div class="mns-sv__rate">
								<div class="mns-stars mns-sv__stars" data-mns-stars data-value="0" data-name="<?php echo esc_attr( $axis ); ?>" role="radiogroup" aria-label="<?php echo esc_attr( $meta[0] ); ?>">
									<?php for ( $i = 0; $i < 5; $i++ ) : ?>
										<button type="button" role="radio" aria-checked="false" aria-label="<?php echo esc_attr( MNS_Format::digits( (string) ( $i + 1 ) ) . ' - ' . $words[ $i ] ); ?>" data-word="<?php echo esc_attr( $words[ $i ] ); ?>">
											<?php echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</button>
									<?php endfor; ?>
								</div>
								<span class="mns-sv__word" data-mns-star-word aria-live="polite"></span>
							</div>
							<input type="hidden" name="<?php echo esc_attr( $axis ); ?>" value="0" data-mns-star-value="<?php echo esc_attr( $axis ); ?>">
						</div>
						<?php /* A low score (1 or 2) must say what went wrong; the panel folds open right under that axis. */ ?>
						<div class="mns-sv__why" data-mns-why="<?php echo esc_attr( $axis ); ?>" inert>
							<div class="mns-sv__why-inner">
								<div class="mns-sv__why-box">
									<p class="mns-sv__why-q"><?php echo mns_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( sprintf( /* translators: %s: axis name */ __( 'در «%s» چه مشکلی پیش آمد؟', 'medyar-nursing-services' ), $meta[0] ) ); ?> <b aria-hidden="true">*</b></span></p>
									<div class="mns-sv__reasons" role="group" aria-label="<?php echo esc_attr( $meta[0] ); ?>">
										<?php foreach ( $reasons[ $axis ] as $rk => $rl ) : ?>
											<label class="mns-sv__reason"><input type="checkbox" name="reasons[<?php echo esc_attr( $axis ); ?>][]" value="<?php echo esc_attr( $rk ); ?>"><span><?php echo esc_html( $rl ); ?></span></label>
										<?php endforeach; ?>
									</div>
									<span class="mns-field-block__control mns-sv__why-text"><input name="why_<?php echo esc_attr( $axis ); ?>" maxlength="160" autocomplete="off" placeholder="<?php echo esc_attr__( 'توضیح کوتاه (اختیاری اگر یکی از موارد بالا را زدید)', 'medyar-nursing-services' ); ?>" aria-label="<?php echo esc_attr( $meta[0] ); ?>"></span>
								</div>
							</div>
						</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="mns-sv__neg" data-mns-neg inert>
					<div class="mns-sv__neg-inner">
						<p class="mns-sv__neg-box"><?php echo mns_icon( 'flag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html__( 'متأسفیم که تجربهٔ خوبی نداشتید. این نظر به‌عنوان نارضایتی ثبت می‌شود و پشتیبانی پیگیری می‌کند.', 'medyar-nursing-services' ); ?></span></p>
					</div>
				</div>

				<label class="mns-field-block mns-sv__comment">
					<span class="mns-field-block__label"><?php echo esc_html__( 'توضیحات', 'medyar-nursing-services' ); ?><span class="mns-field-block__opt"><?php echo esc_html__( 'اختیاری', 'medyar-nursing-services' ); ?></span></span>
					<span class="mns-field-block__control mns-field-block__control--area"><textarea name="comment" maxlength="400" rows="3"></textarea></span>
				</label>

				<p class="mns-sv__note"><?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html__( 'پس از ارسال، نظر قابل ویرایش نیست.', 'medyar-nursing-services' ); ?></span></p>

				<div class="mns-sv__actions">
					<a class="mns-outline-btn" href="<?php echo esc_url( add_query_arg( 'mns_tab', 'open', $base_url ) ); ?>"><?php echo esc_html( MNS_Copy::t( 'action.back' ) ); ?></a>
					<button type="submit" class="mns-submit-btn"><span data-mns-submit-label data-default="<?php echo esc_attr__( 'ارسال پرسشنامه', 'medyar-nursing-services' ); ?>" data-negative="<?php echo esc_attr__( 'ثبت نظر و نارضایتی', 'medyar-nursing-services' ); ?>"><?php echo esc_html__( 'ارسال پرسشنامه', 'medyar-nursing-services' ); ?></span></button>
				</div>
			</form>
		<?php elseif ( empty( $open ) ) : ?>
			<?php
			MNS_UI::empty_state(
				array(
					'title' => __( 'پرسشنامهٔ بازی نیست', 'medyar-nursing-services' ),
					'text'  => __( 'پس از تکمیل خدمت، پرسشنامه اینجا می‌آید.', 'medyar-nursing-services' ),
					'icon'  => 'star',
				)
			);
			?>
		<?php else : ?>
			<ul class="mns-sv__list">
				<?php foreach ( $open as $row ) : ?>
					<li class="mns-sv__open">
						<div class="mns-sv__done-top">
							<span class="mns-sv__ico" aria-hidden="true"><?php echo mns_icon( $svc_icon( (string) ( $row['service'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<div class="mns-sv__done-text">
								<div class="mns-sv__done-title">
									<h2 class="mns-sv__title"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></h2>
									<span class="mns-sv__chip mns-sv__chip--amber"><?php echo esc_html__( 'در انتظار نظر', 'medyar-nursing-services' ); ?></span>
								</div>
								<p class="mns-sv__done-nurse">
									<span class="mns-sv__avatar" aria-hidden="true"><?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( (string) ( $row['nurse'] ?? '' ), 0, 1 ) : substr( (string) ( $row['nurse'] ?? '' ), 0, 1 ) ); ?></span>
									<span><?php echo esc_html( (string) ( $row['nurse'] ?? '' ) ); ?></span>
									<span>· <?php echo esc_html( MNS_Format::human( (string) ( $row['when'] ?? '' ), false ) ); ?></span>
								</p>
							</div>
							<span class="mns-sv__code" dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $row['order'] ?? '' ) ) ); ?></span>
						</div>
						<div class="mns-sv__done-foot mns-sv__open-foot">
							<a class="mns-submit-btn mns-sv__go" href="<?php echo esc_url( add_query_arg( array( 'mns_tab' => 'open', 'mns_survey' => (string) ( $row['id'] ?? '' ) ), $base_url ) ); ?>">
								<span><?php echo esc_html__( 'ثبت نظر', 'medyar-nursing-services' ); ?></span>
							</a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

	<?php elseif ( 'done' === $mode ) : ?>
		<?php if ( empty( $done ) ) : ?>
			<?php
			MNS_UI::empty_state(
				array(
					'title' => __( 'هنوز نظری ارسال نشده', 'medyar-nursing-services' ),
					'text'  => __( 'نظرهایی که ثبت کنید اینجا می‌ماند.', 'medyar-nursing-services' ),
					'icon'  => 'star',
				)
			);
			?>
		<?php else : ?>
			<ul class="mns-sv__list">
				<?php foreach ( $done as $row ) : ?>
					<?php
					$sc      = array(
						'science' => max( 0, min( 5, (int) ( $row['science'] ?? 0 ) ) ),
						'ethics'  => max( 0, min( 5, (int) ( $row['ethics'] ?? 0 ) ) ),
						'orderly' => max( 0, min( 5, (int) ( $row['orderly'] ?? 0 ) ) ),
					);
					$avg     = array_sum( $sc ) / 3;
					$avg_txt = str_replace( '.', '٫', MNS_Format::digits( number_format( $avg, 1 ) ) );
					$circ    = 2 * M_PI * 32;
					$dash    = round( $circ * $avg / 5 );
					$nurse   = (string) ( $row['nurse'] ?? '' );
					$initial = function_exists( 'mb_substr' ) ? mb_substr( $nurse, 0, 1 ) : substr( $nurse, 0, 1 );
					$labels  = array(
						'science' => __( 'علمی', 'medyar-nursing-services' ),
						'ethics'  => __( 'اخلاق و خوشرویی', 'medyar-nursing-services' ),
						'orderly' => __( 'نظم در کار', 'medyar-nursing-services' ),
					);
					?>
					<li class="mns-sv__done">
						<div class="mns-sv__done-top">
							<div class="mns-sv__ring" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: average */ __( 'میانگین %s از ۵', 'medyar-nursing-services' ), $avg_txt ) ); ?>">
								<svg viewBox="0 0 76 76" aria-hidden="true" focusable="false">
									<circle cx="38" cy="38" r="32" class="mns-sv__ring-bg"></circle>
									<circle cx="38" cy="38" r="32" class="mns-sv__ring-fg" stroke-dasharray="<?php echo esc_attr( $dash . ' ' . round( $circ ) ); ?>" transform="rotate(-90 38 38)"></circle>
								</svg>
								<span class="mns-sv__ring-num"><strong><?php echo esc_html( $avg_txt ); ?></strong><small><?php echo esc_html__( 'از ۵', 'medyar-nursing-services' ); ?></small></span>
							</div>
							<div class="mns-sv__done-text">
								<div class="mns-sv__done-title">
									<h2 class="mns-sv__title"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></h2>
									<span class="mns-sv__chip" title="<?php echo esc_attr__( 'ثبت‌شده و غیرقابل ویرایش', 'medyar-nursing-services' ); ?>"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html__( 'ثبت‌شده', 'medyar-nursing-services' ); ?></span>
								</div>
								<p class="mns-sv__done-nurse">
									<span class="mns-sv__avatar" aria-hidden="true"><?php echo esc_html( $initial ); ?></span>
									<span><?php echo esc_html( $nurse ); ?></span>
									<?php if ( ! empty( $row['when'] ) ) : ?>
										<span>· <?php echo esc_html( MNS_Format::human( (string) $row['when'], false ) ); ?></span>
									<?php endif; ?>
								</p>
							</div>
							<span class="mns-sv__code" dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $row['order'] ?? '' ) ) ); ?></span>
						</div>

						<div class="mns-sv__bars">
							<?php foreach ( $labels as $k => $l ) : ?>
								<div class="mns-sv__bar" role="img" aria-label="<?php echo esc_attr( $l . ': ' . MNS_Format::digits( (string) $sc[ $k ] ) ); ?>">
									<span class="mns-sv__bar-label"><?php echo esc_html( $l ); ?></span>
									<span class="mns-sv__bar-track" aria-hidden="true"><span style="width: <?php echo esc_attr( (string) ( $sc[ $k ] * 20 ) ); ?>%"></span></span>
									<strong aria-hidden="true"><?php echo esc_html( MNS_Format::digits( (string) $sc[ $k ] ) ); ?></strong>
								</div>
							<?php endforeach; ?>
						</div>

						<div class="mns-sv__done-foot">
							<?php if ( ! empty( $row['comment'] ) ) : ?>
								<p class="mns-sv__bubble"><?php echo mns_icon( 'file-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( (string) $row['comment'] ); ?></span></p>
							<?php endif; ?>
							<a class="mns-sv__again" href="<?php echo esc_url( $new_url ); ?>"><?php echo esc_html__( 'درخواست مجدد با همین پرستار', 'medyar-nursing-services' ); ?></a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

	<?php else : ?>
		<?php if ( empty( $complaints ) ) : ?>
			<?php
			MNS_UI::empty_state(
				array(
					'title' => __( 'نارضایتی ثبت‌شده‌ای ندارید', 'medyar-nursing-services' ),
					'text'  => __( 'اگر مشکلی در یک خدمت پیش آمد، از پشتیبانی بخواهید ثبتش کند.', 'medyar-nursing-services' ),
					'icon'  => 'flag',
				)
			);
			?>
		<?php else : ?>
			<ul class="mns-sv__list">
				<?php foreach ( $complaints as $c ) : ?>
					<li class="mns-sv__open mns-sv__track">
						<div class="mns-sv__done-top">
							<span class="mns-sv__ico mns-sv__ico--amber" aria-hidden="true"><?php echo mns_icon( 'flag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<div class="mns-sv__done-text">
								<div class="mns-sv__done-title">
									<h3 class="mns-sv__title"><?php echo esc_html( (string) ( $c['subject'] ?? '' ) ); ?></h3>
									<span class="mns-sv__state"><?php echo esc_html( (string) ( $c['label'] ?? '' ) ); ?></span>
								</div>
								<p class="mns-sv__done-nurse"><?php echo esc_html( MNS_Format::human( (string) ( $c['when'] ?? '' ), false ) ); ?></p>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	<?php endif; ?>
</div>
