<?php
/**
 * Nurse "My ratings" (P-32): the overall average, the three category averages and the positive badges.
 * Only averages; the text of a patient's feedback is never shown to the nurse.
 *
 * Static UI preview cases: ok, top, new, empty (`mns_case`).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data = MNS_Static_Fixtures::get( 'nurse/ratings' );
$fa   = static function ( $v ): string {
	return MNS_Format::digits( (string) $v );
};
$num  = static function ( $v ): string {
	return MNS_Format::digits( number_format( (float) $v, 1 ) );
};

$preview = MNS_Dev::enabled() || ( class_exists( 'MNS_Plugin' ) && MNS_Plugin::is_ui_static() );
$case    = 'ok';
// phpcs:disable WordPress.Security.NonceVerification.Recommended
if ( $preview && isset( $_GET['mns_case'] ) ) {
	$c = sanitize_key( wp_unslash( $_GET['mns_case'] ) );
	if ( in_array( $c, array( 'ok', 'top', 'new', 'empty' ), true ) ) {
		$case = $c;
	}
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$r = is_array( $data ) ? $data : array();
if ( 'top' === $case ) {
	$r['overall'] = 4.9;
	$r['science'] = 4.9;
	$r['ethics']  = 5.0;
	$r['order']   = 4.8;
} elseif ( 'new' === $case ) {
	$r['overall']  = 4.5;
	$r['science']  = 5.0;
	$r['ethics']   = 4.5;
	$r['order']    = 4.0;
	$r['sample']   = 2;
	$r['services'] = 3;
} elseif ( 'empty' === $case ) {
	$r = array();
}

$self_url = MNS_Pages::panel_url( 'nurse', 'ratings' );
$cases    = array(
	'ok'    => __( 'نمونه', 'medyar-nursing-services' ),
	'top'   => __( 'همهٔ نشان‌ها', 'medyar-nursing-services' ),
	'new'   => __( 'تازه‌کار', 'medyar-nursing-services' ),
	'empty' => __( 'بدون امتیاز', 'medyar-nursing-services' ),
);

$sample    = (int) ( $r['sample'] ?? 0 );
$services  = (int) ( $r['services'] ?? 0 );
$threshold = (float) ( $r['badge_threshold'] ?? 4.8 );
$min       = (int) ( $r['badge_min_sample'] ?? 5 );
$overall   = (float) ( $r['overall'] ?? 0 );
$few       = $sample > 0 && $sample < $min;

/** The three categories (survey order) and the badge each one can give. */
$cats = array(
	array(
		'key'   => 'science',
		'name'  => __( 'علمی', 'medyar-nursing-services' ),
		'note'  => __( 'مهارت و دانش شما در انجام خدمت', 'medyar-nursing-services' ),
		'icon'  => 'shield',
		'badge' => __( 'دقیق و مسلط', 'medyar-nursing-services' ),
	),
	array(
		'key'   => 'ethics',
		'name'  => __( 'اخلاق و خوشرویی', 'medyar-nursing-services' ),
		'note'  => __( 'رفتار شما با بیمار و خانواده', 'medyar-nursing-services' ),
		'icon'  => 'heart',
		'badge' => __( 'خوش‌برخورد', 'medyar-nursing-services' ),
	),
	array(
		'key'   => 'order',
		'name'  => __( 'نظم در کار', 'medyar-nursing-services' ),
		'note'  => __( 'وقت‌شناسی و آراستگی در مراجعه', 'medyar-nursing-services' ),
		'icon'  => 'clock',
		'badge' => __( 'منظم و وقت‌شناس', 'medyar-nursing-services' ),
	),
);

/** The three custom badge emblems (own silhouette, own glyph; colours come from the card's --b1/--b2). */
$emblem = static function ( string $key ): string {
	$g  = 'mns-nrt-g-' . $key;
	$o  = '<svg class="mns-nrt__emblem" viewBox="0 0 96 96" width="96" height="96" aria-hidden="true" focusable="false">';
	$o .= '<defs><linearGradient id="' . $g . '" x1="0" y1="0" x2="1" y2="1"><stop offset="0" style="stop-color:var(--b1)"/><stop offset="1" style="stop-color:var(--b2)"/></linearGradient></defs>';
	if ( 'science' === $key ) {
		/* precision: a hexagon medal with a crosshair around a medical plus */
		$o .= '<path d="M48 6 84 27v42L48 90 12 69V27Z" fill="url(#' . $g . ')" stroke="url(#' . $g . ')" stroke-width="6" stroke-linejoin="round"/>';
		$o .= '<path d="M48 17 74.5 32v32L48 79 21.5 64V32Z" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="1.5" stroke-linejoin="round"/>';
		$o .= '<g fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><circle cx="48" cy="48" r="15"/><path d="M48 24v7M48 65v7M24 48h7M65 48h7"/><path d="M48 42v12M42 48h12" stroke-width="3.4"/></g>';
	} elseif ( 'ethics' === $key ) {
		/* kindness: an eight-point seal with a heart and a spark */
		$o .= '<path d="M48.0 5.0 L61.4 15.7 L78.4 17.6 L80.3 34.6 L91.0 48.0 L80.3 61.4 L78.4 78.4 L61.4 80.3 L48.0 91.0 L34.6 80.3 L17.6 78.4 L15.7 61.4 L5.0 48.0 L15.7 34.6 L17.6 17.6 L34.6 15.7 Z" fill="url(#' . $g . ')" stroke="url(#' . $g . ')" stroke-width="5" stroke-linejoin="round"/>';
		$o .= '<path d="M48 66C30 54 30 39 40 37c4-.7 7 .9 8 4 1-3.1 4-4.7 8-4 10 2 10 17-8 29Z" fill="#fff"/>';
		$o .= '<path d="M64 23v9M59.5 27.5h9" stroke="#fff" stroke-width="2.6" stroke-linecap="round" fill="none"/><path d="M30 63v5M27.5 65.5h5" stroke="#fff" stroke-opacity=".8" stroke-width="2" stroke-linecap="round" fill="none"/>';
	} else {
		/* punctuality: a diamond with a dial of ticks around a stopwatch */
		$o .= '<path d="M48 5 91 48 48 91 5 48Z" fill="url(#' . $g . ')" stroke="url(#' . $g . ')" stroke-width="6" stroke-linejoin="round"/>';
		$o .= '<circle cx="48" cy="50" r="31" fill="none" stroke="#fff" stroke-opacity=".4" stroke-width="2.4" stroke-dasharray="1 5.1" stroke-linecap="round"/>';
		$o .= '<g fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="48" cy="52" r="16"/><path d="M43 31h10M48 31v5M48 52V43M48 52l7 5"/></g>';
	}
	return $o . '</svg>';
};

/** Five stars, the filled part clipped to the score. */
$stars = static function ( float $score, string $label ) use ( $num ): void {
	$pct = round( min( 100, max( 0, $score / 5 * 100 ) ) );
	?>
	<span class="mns-nrt__stars" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: score */ __( '%s از ۵', 'medyar-nursing-services' ), $num( $score ) ) . '، ' . $label ); ?>">
		<span class="mns-nrt__stars-row" aria-hidden="true">
			<?php for ( $i = 0; $i < 5; $i++ ) : ?>
				<?php echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endfor; ?>
		</span>
		<span class="mns-nrt__stars-fill" style="width:<?php echo esc_attr( (string) $pct ); ?>%" aria-hidden="true">
			<span class="mns-nrt__stars-row">
				<?php for ( $i = 0; $i < 5; $i++ ) : ?>
					<?php echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endfor; ?>
			</span>
		</span>
	</span>
	<?php
};
?>
<div class="mns-ncl mns-nrt">

	<?php if ( $preview ) : ?>
		<nav class="mns-ncl__pv" aria-label="<?php echo esc_attr__( 'پیش‌نمایش (فقط محیط توسعه)', 'medyar-nursing-services' ); ?>">
			<span class="mns-ncl__pv-list">
				<?php foreach ( $cases as $k => $label ) : ?>
					<a class="mns-ncl__pv-item<?php echo $k === $case ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'mns_case', $k, $self_url ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</span>
		</nav>
	<?php endif; ?>

	<header class="mns-ncl__top">
		<h1 class="mns-ncl__title"><?php echo esc_html__( 'امتیازهای من', 'medyar-nursing-services' ); ?></h1>
		<p class="mns-ncl__sub"><?php echo esc_html__( 'میانگین نظر بیماران دربارهٔ شما. متن نظرها برای شما نمایش داده نمی‌شود.', 'medyar-nursing-services' ); ?></p>
	</header>

	<?php if ( empty( $r ) || 0 === $sample ) : ?>
		<section class="mns-nrt__card mns-nrt__empty">
			<span class="mns-nrt__empty-ico" aria-hidden="true"><?php echo mns_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h2 class="mns-nrt__empty-title"><?php echo esc_html__( 'هنوز امتیازی ثبت نشده', 'medyar-nursing-services' ); ?></h2>
			<p class="mns-nrt__empty-text"><?php echo esc_html__( 'بعد از هر خدمت، بیمار پرسشنامه را پر می‌کند. با اولین پرسشنامه، میانگین شما همین‌جا نمایش داده می‌شود.', 'medyar-nursing-services' ); ?></p>
		</section>
		</div>
		<?php
		return;
	endif;
	?>

	<div class="mns-nrt__main">

		<?php /* Overall average. */ ?>
		<section class="mns-nrt__sec mns-nrt__sec--hero">
			<div class="mns-nrt__sh">
				<h2 class="mns-nrt__sh-title"><?php echo esc_html__( 'میانگین کلی', 'medyar-nursing-services' ); ?></h2>
			</div>
			<div class="mns-nrt__card mns-nrt__hero">
				<p class="mns-nrt__score">
					<b><?php echo esc_html( $num( $overall ) ); ?></b>
					<small><?php echo esc_html__( 'از ۵', 'medyar-nursing-services' ); ?></small>
				</p>
				<?php $stars( $overall, __( 'میانگین کلی', 'medyar-nursing-services' ) ); ?>
				<ul class="mns-nrt__facts">
					<li>
						<b><?php echo esc_html( $fa( $sample ) ); ?></b>
						<span><?php echo esc_html__( 'پرسشنامه', 'medyar-nursing-services' ); ?></span>
					</li>
					<li>
						<b><?php echo esc_html( $fa( $services ) ); ?></b>
						<span><?php echo esc_html__( 'خدمت انجام‌شده', 'medyar-nursing-services' ); ?></span>
					</li>
				</ul>
				<?php if ( $few ) : ?>
					<p class="mns-nrt__note"><?php echo esc_html( sprintf( /* translators: %s: survey count */ __( 'این میانگین فعلاً بر پایهٔ %s پرسشنامه است و با پرسشنامه‌های بعدی تغییر می‌کند.', 'medyar-nursing-services' ), $fa( $sample ) ) ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<?php /* The three categories. */ ?>
		<section class="mns-nrt__sec mns-nrt__sec--cats">
			<div class="mns-nrt__sh">
				<h2 class="mns-nrt__sh-title"><?php echo esc_html__( 'میانگین هر دسته', 'medyar-nursing-services' ); ?></h2>
			</div>
			<ul class="mns-nrt__card mns-nrt__cats">
				<?php foreach ( $cats as $c ) : ?>
					<?php $v = (float) ( $r[ $c['key'] ] ?? 0 ); ?>
					<li class="mns-nrt__cat">
						<span class="mns-nrt__cat-ico" aria-hidden="true"><?php echo mns_icon( $c['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-nrt__cat-text">
							<b><?php echo esc_html( $c['name'] ); ?></b>
							<small><?php echo esc_html( $c['note'] ); ?></small>
						</span>
						<span class="mns-nrt__cat-val"><?php echo esc_html( $num( $v ) ); ?><small><?php echo esc_html__( 'از ۵', 'medyar-nursing-services' ); ?></small></span>
						<span class="mns-nrt__meter" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: score */ __( '%s از ۵', 'medyar-nursing-services' ), $num( $v ) ) ); ?>"><span style="width:<?php echo esc_attr( (string) round( min( 100, max( 0, $v / 5 * 100 ) ) ) ); ?>%"></span></span>
					</li>
				<?php endforeach; ?>
				<li class="mns-nrt__cats-foot"><?php echo esc_html__( 'بیمار در پرسشنامه به هر دسته از ۱ تا ۵ ستاره می‌دهد.', 'medyar-nursing-services' ); ?></li>
			</ul>
		</section>
	</div>

	<?php /* Positive badges: earned and the ones still to come. */ ?>
	<section class="mns-nrt__sec mns-nrt__sec--badges">
		<div class="mns-nrt__sh">
			<h2 class="mns-nrt__sh-title"><?php echo esc_html__( 'نشان‌های من', 'medyar-nursing-services' ); ?></h2>
			<p class="mns-nrt__sh-sum">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: threshold, 2: minimum surveys */
						__( 'هر نشان با میانگین %1$s یا بیشتر در همان دسته، بعد از دست‌کم %2$s پرسشنامه به دست می‌آید.', 'medyar-nursing-services' ),
						$num( $threshold ),
						$fa( $min )
					)
				);
				?>
			</p>
		</div>
		<ul class="mns-nrt__badges">
			<?php foreach ( $cats as $c ) : ?>
				<?php
				$v      = (float) ( $r[ $c['key'] ] ?? 0 );
				$ready  = $sample >= $min;
				$earned = $ready && $v >= $threshold;
				$prog   = $ready ? ( $threshold > 0 ? $v / $threshold : 0 ) : ( $min > 0 ? $sample / $min : 0 );
				$prog   = round( min( 1, max( 0, $prog ) ) * 100 );
				?>
				<li class="mns-nrt__badge is-<?php echo esc_attr( $c['key'] ); ?><?php echo $earned ? ' is-earned' : ' is-locked'; ?>">
					<span class="mns-nrt__badge-art">
						<?php echo $emblem( $c['key'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="mns-nrt__badge-text">
						<b><?php echo esc_html( $c['badge'] ); ?></b>
						<small><?php echo esc_html( sprintf( /* translators: %s: category name */ __( 'دستهٔ «%s»', 'medyar-nursing-services' ), $c['name'] ) ); ?></small>
						<?php if ( $earned ) : ?>
							<span class="mns-nrt__got"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html__( 'دریافت شد', 'medyar-nursing-services' ); ?></span>
						<?php else : ?>
							<span class="mns-nrt__prog" role="img" aria-label="<?php echo esc_attr( $fa( $prog ) . '٪' ); ?>"><span style="width:<?php echo esc_attr( (string) $prog ); ?>%"></span></span>
							<span class="mns-nrt__need">
								<?php
								if ( ! $ready ) {
									echo esc_html( sprintf( /* translators: %s: remaining surveys */ __( '%s پرسشنامهٔ دیگر تا فعال‌شدن', 'medyar-nursing-services' ), $fa( $min - $sample ) ) );
								} else {
									echo esc_html( sprintf( /* translators: 1: current average, 2: threshold */ __( 'میانگین الان %1$s، برای نشان %2$s', 'medyar-nursing-services' ), $num( $v ), $num( $threshold ) ) );
								}
								?>
							</span>
						<?php endif; ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

</div>
