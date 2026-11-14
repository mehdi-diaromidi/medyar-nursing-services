<?php
/**
 * Nurse "schedule and access" (P-06 / P-07): status + accept switch, the nurse's own monthly schedule
 * (day by day, slot by slot: "I'm not available that day / those slots"), and the working regions across Iran
 * (a province dropdown + a city dropdown, both searchable; add city by city or a whole province).
 *
 * Static UI preview cases: ok, off, away, none (`mns_case`).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data  = MNS_Static_Fixtures::get( 'nurse/availability' );
$arr   = static function ( $value ): array {
	return is_array( $value ) ? $value : array();
};
$fa    = static function ( $v ): string {
	return MNS_Format::digits( (string) $v );
};
$slots = $arr( $data['slots'] ?? array() );

$preview = MNS_Dev::enabled() || ( class_exists( 'MNS_Plugin' ) && MNS_Plugin::is_ui_static() );
$case    = 'ok';
// phpcs:disable WordPress.Security.NonceVerification.Recommended
if ( $preview && isset( $_GET['mns_case'] ) ) {
	$c = sanitize_key( wp_unslash( $_GET['mns_case'] ) );
	if ( in_array( $c, array( 'ok', 'off', 'away', 'none' ), true ) ) {
		$case = $c;
	}
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$tz    = wp_timezone();
$today = new DateTimeImmutable( 'today', $tz );

$base      = $arr( $data['base_week'] ?? array() );
$overrides = $arr( $data['overrides'] ?? array() );
$orders    = $arr( $data['order_slots'] ?? array() );
$regions   = $arr( $data['regions'] ?? array() );
if ( 'none' === $case ) {
	$base      = array( array(), array(), array(), array(), array(), array(), array() );
	$overrides = array();
	$regions   = array();
}
if ( 'away' === $case ) {
	foreach ( array( 0, 1, 2 ) as $off_days ) {
		$overrides[ $today->modify( '+' . $off_days . ' days' )->format( 'Y-m-d' ) ] = array();
	}
}

/* The days the calendar knows: from the first of this Jalali month to +max days (Saturday first). */
$jt     = MNS_Format::g2j( (int) $today->format( 'Y' ), (int) $today->format( 'n' ), (int) $today->format( 'j' ) );
$start  = $today->modify( '-' . ( (int) $jt[2] - 1 ) . ' days' );
$end    = $today->modify( '+' . (int) ( $data['max_days'] ?? 90 ) . ' days' );
$days   = array();
$cursor = $start;
while ( $cursor <= $end ) {
	$iso    = $cursor->format( 'Y-m-d' );
	$dow    = ( (int) $cursor->format( 'N' ) + 1 ) % 7;
	$j      = MNS_Format::g2j( (int) $cursor->format( 'Y' ), (int) $cursor->format( 'n' ), (int) $cursor->format( 'j' ) );
	$starts = $arr( $base[ $dow ] ?? array() );
	$days[] = array(
		'iso'    => $iso,
		'y'      => (int) $j[0],
		'm'      => (int) $j[1],
		'd'      => (int) $j[2],
		'dow'    => $dow,
		'past'   => $cursor < $today,
		'today'  => $iso === $today->format( 'Y-m-d' ),
		'base'   => array_values( $starts ),
		'on'     => array_values( array_key_exists( $iso, $overrides ) ? $arr( $overrides[ $iso ] ) : $starts ),
		'orders' => array_values( $arr( $orders[ $iso ] ?? array() ) ),
	);
	$cursor = $cursor->modify( '+1 day' );
}

/* Status line for the case (the accept switch can still turn it into "paused"). */
$statuses  = array(
	'ok'   => array( 'tone' => 'ok', 'text' => __( 'در دسترس', 'medyar-nursing-services' ), 'sub' => __( 'الان در بازهٔ کاری شما هستید.', 'medyar-nursing-services' ) ),
	'off'  => array( 'tone' => 'warn', 'text' => __( 'خارج از بازهٔ کاری', 'medyar-nursing-services' ), 'sub' => __( 'نزدیک‌ترین بازهٔ بعدی: امروز، ۱۶ تا ۲۰.', 'medyar-nursing-services' ) ),
	'away' => array( 'tone' => 'warn', 'text' => __( 'امروز در دسترس نیستید', 'medyar-nursing-services' ), 'sub' => __( 'بازه‌های این چند روز را خاموش کرده‌اید.', 'medyar-nursing-services' ) ),
	'none' => array( 'tone' => 'warn', 'text' => __( 'برنامه یا منطقه ندارید', 'medyar-nursing-services' ), 'sub' => __( 'تا بازه و منطقه‌ای مشخص نکنید، سفارش جدید نمی‌گیرید.', 'medyar-nursing-services' ) ),
);
$status    = $statuses[ $case ];
$accepting = ! empty( $data['accepting'] );

$loc_file  = MNS_PLUGIN_DIR . 'assets/data/iran-locations.json';
$loc_data  = is_readable( $loc_file ) ? json_decode( (string) file_get_contents( $loc_file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
$provinces = isset( $loc_data['provinces'] ) && is_array( $loc_data['provinces'] ) ? $loc_data['provinces'] : array();
if ( class_exists( 'Collator' ) ) {
	$collator = new Collator( 'fa' );
	usort(
		$provinces,
		static function ( $a, $b ) use ( $collator ) {
			return $collator->compare( (string) $a['name'], (string) $b['name'] );
		}
	);
}

$config = array(
	'case'         => $case,
	'statuses'     => $statuses,
	'pausedText'   => __( 'پذیرش درخواست خاموش است', 'medyar-nursing-services' ),
	'pausedSub'    => __( 'تا روشن نکنید سفارش جدید نمی‌گیرید.', 'medyar-nursing-services' ),
	'slots'        => array_values(
		array_filter(
			$slots,
			static function ( $s ) {
				return empty( $s['closed'] );
			}
		)
	),
	'days'         => $days,
	'monthNames'   => array_values( MNS_Format::month_names() ),
	'regions'      => $regions,
	'districts'    => $arr( $data['districts'] ?? array() ),
	'regionOrders' => $arr( $data['region_orders'] ?? array() ),
	'locationsUrl' => MNS_PLUGIN_URL . 'assets/data/iran-locations.json?v=' . (int) @filemtime( $loc_file ), // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
);

$self_url = MNS_Pages::panel_url( 'nurse', 'availability' );
$cases    = array(
	'ok'   => __( 'در دسترس', 'medyar-nursing-services' ),
	'off'  => __( 'خارج از بازه', 'medyar-nursing-services' ),
	'away' => __( 'امروز نیستم', 'medyar-nursing-services' ),
	'none' => __( 'بدون برنامه و منطقه', 'medyar-nursing-services' ),
);

/** A numbered section header (same look as the order forms). */
$head = static function ( int $n, string $title, string $hint ) use ( $fa ): void {
	?>
	<div class="mns-ncl__sh">
		<span class="mns-ncl__badge" aria-hidden="true"><b><?php echo esc_html( $fa( $n ) ); ?></b></span>
		<span class="mns-ncl__st">
			<span class="mns-ncl__st-title"><?php echo esc_html( $title ); ?></span>
			<span class="mns-ncl__st-sum"><?php echo esc_html( $hint ); ?></span>
		</span>
	</div>
	<?php
};
?>
<div
	class="mns-ncl mns-ncl--form mns-nrp mns-ndr mns-nvl"
	data-mns-ndash
	data-mns-nvl
	data-config="<?php echo esc_attr( (string) wp_json_encode( $config ) ); ?>"
>
	<span hidden data-mns-nvl-trash><?php echo mns_icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<span hidden data-mns-nvl-x><?php echo mns_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<span hidden data-mns-nvl-lock><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

	<?php if ( $preview ) : ?>
		<nav class="mns-ncl__pv" aria-label="<?php echo esc_attr__( 'پیش‌نمایش (فقط محیط توسعه)', 'medyar-nursing-services' ); ?>">
			<span class="mns-ncl__pv-list">
				<?php foreach ( $cases as $k => $label ) : ?>
					<a class="mns-ncl__pv-item<?php echo $k === $case ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'mns_case', $k, $self_url ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</span>
		</nav>
	<?php endif; ?>

	<div class="mns-collapse mns-ncl__net" data-mns-net inert>
		<div class="mns-collapse__inner">
			<p class="mns-ncl__netmsg" role="status"><?php echo esc_html__( 'اتصال برقرار نیست؛ تغییرات بدون اینترنت ثبت نمی‌شود.', 'medyar-nursing-services' ); ?></p>
		</div>
	</div>

	<header class="mns-ncl__top">
		<h1 class="mns-ncl__title"><?php echo esc_html__( 'برنامه و دسترسی', 'medyar-nursing-services' ); ?></h1>
		<p class="mns-ncl__sub"><?php echo esc_html__( 'چه موقع و کجا سفارش می‌گیرید.', 'medyar-nursing-services' ); ?></p>
	</header>

	<ol class="mns-ncl__steps">

		<?php /* 1) Status + accept switch: one row. */ ?>
		<li class="mns-ncl__step is-open" data-mns-sec="status">
			<?php $head( 1, __( 'وضعیت و پذیرش', 'medyar-nursing-services' ), __( 'الان سفارش جدید می‌گیرید؟', 'medyar-nursing-services' ) ); ?>
			<div class="mns-collapse is-open">
				<div class="mns-collapse__inner">
					<div class="mns-ncl__body">
						<div class="mns-nvl__now">
							<div class="mns-ndash mns-nvl__accept">
								<button
									type="button"
									class="mns-ndash__accept<?php echo $accepting ? ' is-on' : ''; ?>"
									role="switch"
									aria-checked="<?php echo $accepting ? 'true' : 'false'; ?>"
									data-mns-accept
									data-on-text="<?php echo esc_attr__( 'در دسترس هستم', 'medyar-nursing-services' ); ?>"
									data-off-text="<?php echo esc_attr__( 'درخواست جدید نمی‌گیرم', 'medyar-nursing-services' ); ?>"
									data-fail-text="<?php echo esc_attr__( 'ثبت نشد، دوباره تلاش کنید.', 'medyar-nursing-services' ); ?>"
								>
									<span class="mns-ndash__accept-text">
										<span class="mns-ndash__accept-label"><?php echo esc_html__( 'پذیرش درخواست جدید', 'medyar-nursing-services' ); ?></span>
										<span class="mns-ndash__accept-state" data-mns-accept-state><?php echo $accepting ? esc_html__( 'در دسترس هستم', 'medyar-nursing-services' ) : esc_html__( 'درخواست جدید نمی‌گیرم', 'medyar-nursing-services' ); ?></span>
									</span>
									<span class="mns-ndash__switch" aria-hidden="true"></span>
								</button>
								<div class="mns-collapse mns-ndash__accept-more<?php echo $accepting ? '' : ' is-open'; ?>" data-mns-accept-hint<?php echo $accepting ? ' inert' : ''; ?>>
									<div class="mns-collapse__inner">
										<p class="mns-ndash__hint" data-mns-accept-hint-text data-default="<?php echo esc_attr__( 'سفارش‌های قبلی شما تغییری نمی‌کند.', 'medyar-nursing-services' ); ?>"><?php echo esc_html__( 'سفارش‌های قبلی شما تغییری نمی‌کند.', 'medyar-nursing-services' ); ?></p>
									</div>
								</div>
							</div>
							<div class="mns-nvl__state">
								<p class="mns-nvl__status is-<?php echo esc_attr( $status['tone'] ); ?>" data-mns-nvl-status><span class="mns-nvl__dot" aria-hidden="true"></span><span data-mns-nvl-status-text><?php echo esc_html( $status['text'] ); ?></span></p>
								<p class="mns-nvl__sub" data-mns-nvl-status-sub><?php echo esc_html( $status['sub'] ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</li>

		<?php /* 2) The nurse's own monthly schedule. */ ?>
		<li class="mns-ncl__step is-open" data-mns-sec="schedule">
			<?php $head( 2, __( 'برنامهٔ ماهانهٔ من', 'medyar-nursing-services' ), __( 'هر روز و هر بازه‌ای که نیستید را خودتان مشخص کنید', 'medyar-nursing-services' ) ); ?>
			<div class="mns-collapse is-open">
				<div class="mns-collapse__inner">
					<div class="mns-ncl__body" data-mns-sc>
						<div class="mns-nvl__sched">
						<div class="mns-nvl__cal">
							<div class="mns-nvl__calhead">
								<button type="button" class="mns-nvl__nav" data-mns-sc-prev aria-label="<?php echo esc_attr__( 'ماه قبل', 'medyar-nursing-services' ); ?>"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
								<b data-mns-sc-title></b>
								<button type="button" class="mns-nvl__nav is-next" data-mns-sc-next aria-label="<?php echo esc_attr__( 'ماه بعد', 'medyar-nursing-services' ); ?>"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							</div>
							<div class="mns-nvl__dow" aria-hidden="true"><span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span></div>
							<div class="mns-nvl__grid" data-mns-sc-grid></div>
							<ul class="mns-nvl__legend" aria-hidden="true">
								<li><i class="is-on"></i><?php echo esc_html__( 'بازهٔ در دسترس', 'medyar-nursing-services' ); ?></li>
								<li><i class="is-off"></i><?php echo esc_html__( 'نیستم', 'medyar-nursing-services' ); ?></li>
								<li><i class="is-order"></i><?php echo esc_html__( 'سفارش دارد', 'medyar-nursing-services' ); ?></li>
								<li><i class="is-changed"></i><?php echo esc_html__( 'تغییرکرده', 'medyar-nursing-services' ); ?></li>
							</ul>
						</div>

						<div class="mns-nvl__side">
						<div class="mns-collapse" data-mns-sc-panel inert>
							<div class="mns-collapse__inner">
								<div class="mns-nvl__panel">
									<div class="mns-nvl__panelhead">
										<b data-mns-sc-ptitle></b>
										<button type="button" class="mns-nvl__link" data-mns-sc-close><?php echo esc_html__( 'بستن', 'medyar-nursing-services' ); ?></button>
									</div>
									<div class="mns-nvl__slots" data-mns-sc-slots></div>
									<p class="mns-ncl__lead" data-mns-sc-lock hidden><?php echo esc_html__( 'بازه‌ای که سفارش دارد روشن می‌ماند و خاموش نمی‌شود.', 'medyar-nursing-services' ); ?></p>
									<div class="mns-nvl__quick">
										<button type="button" class="mns-nvl__btn" data-mns-sc-allon><?php echo esc_html__( 'همهٔ بازه‌ها در دسترس', 'medyar-nursing-services' ); ?></button>
										<button type="button" class="mns-nvl__btn" data-mns-sc-alloff><?php echo esc_html__( 'کل روز نیستم', 'medyar-nursing-services' ); ?></button>
										<button type="button" class="mns-nvl__btn" data-mns-sc-reset><?php echo esc_html__( 'برگشت به برنامهٔ پایه', 'medyar-nursing-services' ); ?></button>
									</div>
								</div>
							</div>
						</div>

						<p class="mns-nvl__tip" data-mns-sc-hint><?php echo esc_html__( 'یک روز را بزنید، یا برای چند روز، روز شروع و روز پایان را انتخاب کنید.', 'medyar-nursing-services' ); ?></p>
						</div>
						</div>

						<div class="mns-nvl__savebar" data-mns-sc-bar hidden>
							<p class="mns-nvl__savetext" data-mns-sc-bartext></p>
							<div class="mns-nvl__saveactions">
								<button type="button" class="mns-ncl__btn" data-mns-sc-undo><?php echo esc_html__( 'لغو تغییرها', 'medyar-nursing-services' ); ?></button>
								<button type="button" class="mns-submit-btn" data-mns-sc-save><span><?php echo esc_html__( 'ذخیرهٔ برنامه', 'medyar-nursing-services' ); ?></span></button>
							</div>
						</div>
						<p class="mns-ncl__reason" data-mns-sc-error role="alert"></p>
						<p class="mns-nvl__done" data-mns-sc-done hidden><?php echo esc_html__( 'برنامه ذخیره شد و از همین حالا برای سفارش‌های جدید اعمال می‌شود.', 'medyar-nursing-services' ); ?></p>

						<p class="mns-nvl__note"><?php echo esc_html__( 'روزهای هر ماه از برنامهٔ پایهٔ هفتگی شروع می‌شوند که پشتیبانی تنظیم کرده است. برای عوض کردن خود برنامهٔ پایه با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ); ?>
							<a class="mns-nvl__link" href="<?php echo esc_attr( (string) ( $data['support_tel'] ?? '' ) ); ?>" data-mns-needs-net><?php echo esc_html__( 'تماس با پشتیبانی', 'medyar-nursing-services' ); ?></a>
						</p>
					</div>
				</div>
			</div>
		</li>

		<?php /* 3) Regions across Iran: two searchable dropdowns. */ ?>
		<li class="mns-ncl__step is-open" data-mns-sec="regions">
			<?php $head( 3, __( 'مناطق فعالیت', 'medyar-nursing-services' ), __( 'فقط سفارش‌های همین شهرها و مناطق به شما می‌رسد', 'medyar-nursing-services' ) ); ?>
			<div class="mns-collapse is-open">
				<div class="mns-collapse__inner">
					<div class="mns-ncl__body" data-mns-rg>
						<div class="mns-nvl__addbox">
							<div class="mns-form-row--2">
								<label class="mns-field-block">
									<span class="mns-field-block__label"><?php echo esc_html__( 'استان', 'medyar-nursing-services' ); ?><span class="mns-field-block__opt"><?php echo esc_html__( 'جستجو کنید', 'medyar-nursing-services' ); ?></span></span>
									<span class="mns-field-block__control mns-field-block__control--select">
										<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo mns_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<select class="mns-select-native" data-mns-select data-mns-rg-prov>
											<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
											<?php foreach ( $provinces as $p ) : ?>
												<option value="<?php echo esc_attr( (string) ( $p['code'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $p['name'] ?? '' ) ); ?></option>
											<?php endforeach; ?>
										</select>
										<span class="mns-field-block__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</span>
								</label>
								<label class="mns-field-block">
									<span class="mns-field-block__label"><?php echo esc_html__( 'شهر', 'medyar-nursing-services' ); ?><span class="mns-field-block__opt"><?php echo esc_html__( 'جستجو کنید', 'medyar-nursing-services' ); ?></span></span>
									<span class="mns-field-block__control mns-field-block__control--select">
										<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo mns_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<select class="mns-select-native" data-mns-select data-mns-rg-city disabled>
											<option value=""><?php echo esc_html__( 'ابتدا استان را انتخاب کنید', 'medyar-nursing-services' ); ?></option>
										</select>
										<span class="mns-field-block__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</span>
								</label>
							</div>
							<div class="mns-nvl__addrow">
								<button type="button" class="mns-nvl__btn is-primary" data-mns-rg-add><?php echo esc_html__( 'افزودن شهر', 'medyar-nursing-services' ); ?></button>
								<button type="button" class="mns-nvl__btn" data-mns-rg-addall><?php echo esc_html__( 'افزودن همهٔ شهرهای این استان', 'medyar-nursing-services' ); ?></button>
							</div>
							<p class="mns-ncl__reason" data-mns-rg-addreason role="alert"></p>
						</div>

						<div class="mns-nvl__sel" data-mns-rg-sel>
							<p class="mns-nvl__sel-h" data-mns-rg-sel-h></p>
							<div class="mns-nvl__groups" data-mns-rg-groups></div>
						</div>

						<div class="mns-nvl__districts" data-mns-rg-districts></div>

						<div class="mns-nrp__banner is-review" data-mns-rg-none hidden><span aria-hidden="true"><?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><?php echo esc_html__( 'تا منطقه‌ای اضافه نکنید، سفارش جدید نمی‌گیرید.', 'medyar-nursing-services' ); ?></span></div>
						<div class="mns-nrp__banner is-review" data-mns-rg-warn hidden><span aria-hidden="true"><?php echo mns_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span data-mns-rg-warn-text></span></div>
						<p class="mns-ncl__reason" data-mns-rg-reason role="alert"></p>
						<div class="mns-nvl__savebar mns-nvl__savebar--plain">
							<button type="button" class="mns-submit-btn" data-mns-rg-save hidden><span><?php echo esc_html__( 'ذخیرهٔ مناطق', 'medyar-nursing-services' ); ?></span></button>
							<p class="mns-nvl__done" data-mns-rg-done hidden><?php echo esc_html__( 'ذخیره شد. از همین حالا برای سفارش‌های جدید اعمال می‌شود.', 'medyar-nursing-services' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</li>
	</ol>
</div>
