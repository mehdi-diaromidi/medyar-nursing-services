<?php
/**
 * Nurse checklist (P-29/30): a guided list of steps, one open at a time.
 *
 * Finished steps fold into a one-line summary; order-known values are prefilled; end-of-service answers have a
 * one-tap "all normal" shortcut. Autosaved draft while the service runs; final submit only after the service
 * ended; read-only afterwards. Phases (static UI preview): draft (in service), ended (can submit), submitted.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data     = MNS_Static_Fixtures::get( 'nurse/checklist' );
$services = isset( $data['services'] ) && is_array( $data['services'] ) ? $data['services'] : array();
$catalog  = array();
foreach ( (array) ( $data['catalog'] ?? array() ) as $item ) {
	$catalog[ (string) $item['id'] ] = $item;
}

$arr = static function ( $value ): array {
	return is_array( $value ) ? $value : array();
};

$preview = MNS_Dev::enabled() || ( class_exists( 'MNS_Plugin' ) && MNS_Plugin::is_ui_static() );
$phase   = 'draft';
$service = 'serum';
$code    = '';
// phpcs:disable WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET['mns_order'] ) ) {
	$code = sanitize_text_field( wp_unslash( $_GET['mns_order'] ) );
}
if ( $preview ) {
	$p = isset( $_GET['mns_phase'] ) ? sanitize_key( wp_unslash( $_GET['mns_phase'] ) ) : '';
	$s = isset( $_GET['mns_service'] ) ? sanitize_key( wp_unslash( $_GET['mns_service'] ) ) : '';
	if ( in_array( $p, array( 'draft', 'ended', 'submitted' ), true ) ) {
		$phase = $p;
	}
	if ( isset( $services[ $s ] ) ) {
		$service = $s;
	}
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended
if ( '' === $code ) {
	$now  = new DateTimeImmutable( 'now', wp_timezone() );
	$code = 'MNS-' . MNS_Format::g2j( (int) $now->format( 'Y' ), (int) $now->format( 'n' ), (int) $now->format( 'j' ) )[0] . '-1001';
}
$svc    = $arr( $services[ $service ] ?? array() );
$steps  = $arr( $svc['steps'] ?? array() );
$vals   = $arr( $arr( $svc['values'] ?? array() )[ $phase ] ?? array() );
$rows   = $arr( $arr( $svc['rows'] ?? array() )[ $phase ] ?? array() );
$locked = 'submitted' === $phase;

$view_url = MNS_Pages::panel_url( 'nurse', 'order-view' );
$self_url = MNS_Pages::panel_url( 'nurse', 'checklist' );
$back_url = add_query_arg(
	array(
		'mns_order' => $code,
		'mns_state' => 'draft' === $phase ? 'in_progress' : ( 'ended' === $phase ? 'service_ended' : 'invoiced' ),
	),
	$view_url
);
$phase_url = static function ( string $ph, string $sv ) use ( $self_url, $code ): string {
	return add_query_arg(
		array(
			'mns_order'   => $code,
			'mns_phase'   => $ph,
			'mns_service' => $sv,
		),
		$self_url
	);
};

$fa = static function ( $v ): string {
	return MNS_Format::digits( (string) $v );
};

/* The last step is always "items and extra services"; it is never locked and always valid. */
$steps[] = array(
	'key'    => 'extras',
	'title'  => __( 'اقلام و خدمات اضافه', 'medyar-nursing-services' ),
	'hint'   => __( 'آنچه مصرف یا اضافه انجام شد', 'medyar-nursing-services' ),
	'fields' => array(),
);

/* Config for the script. */
$config = array(
	'numbers' => array(),
	'catalog' => array(),
	'rows'    => $rows,
);
foreach ( $steps as $st ) {
	foreach ( $arr( $st['fields'] ?? array() ) as $f ) {
		if ( 'number' === ( $f['type'] ?? '' ) ) {
			$config['numbers'][ (string) $f['key'] ] = array(
				'min'      => $f['min'] ?? null,
				'max'      => $f['max'] ?? null,
				'warn_min' => $f['warn_min'] ?? null,
				'warn_max' => $f['warn_max'] ?? null,
			);
		}
	}
}
foreach ( $arr( $svc['catalog'] ?? array() ) as $id ) {
	if ( isset( $catalog[ $id ] ) ) {
		$config['catalog'][] = array(
			'id'    => (string) $id,
			'label' => (string) $catalog[ $id ]['label'],
			'cat'   => (string) $catalog[ $id ]['cat'],
		);
	}
}
// Rows the order's service uses must resolve to a label, even when they are not in the picker list.
foreach ( array_keys( $rows ) as $rid ) {
	if ( isset( $catalog[ $rid ] ) && ! in_array( $rid, wp_list_pluck( $config['catalog'], 'id' ), true ) ) {
		$config['catalog'][] = array(
			'id'    => (string) $rid,
			'label' => (string) $catalog[ $rid ]['label'],
			'cat'   => (string) $catalog[ $rid ]['cat'],
		);
	}
}

/** One field. */
$render_field = static function ( array $f ) use ( $vals, $locked, $fa ): void {
	$key   = (string) $f['key'];
	$type  = (string) $f['type'];
	$req   = ! empty( $f['required'] );
	$val   = isset( $vals[ $key ] ) ? (string) $vals[ $key ] : '';
	$dis   = $locked ? ' disabled' : '';
	$label = (string) $f['label'];
	$attrs = ' data-mns-ncl-field="' . esc_attr( $key ) . '" data-type="' . esc_attr( $type ) . '" data-label="' . esc_attr( $label ) . '"';
	$attrs .= $req ? ' data-required' : '';
	$attrs .= ! empty( $f['short'] ) ? ' data-short="' . esc_attr( (string) $f['short'] ) . '"' : '';
	$attrs .= ! empty( $f['normal'] ) ? ' data-normal="' . esc_attr( (string) $f['normal'] ) . '"' : '';
	$attrs .= ! empty( $f['join'] ) ? ' data-join="' . esc_attr( (string) $f['join'] ) . '"' : '';
	$attrs .= ! empty( $f['skip'] ) ? ' data-skip' : '';
	$attrs .= ! empty( $f['unit'] ) ? ' data-unit="' . esc_attr( (string) $f['unit'] ) . '"' : '';
	$attrs .= ! empty( $f['sumunit'] ) ? ' data-sumunit' : '';
	$src    = ! empty( $f['from_order'] ) ? '<small class="mns-ncl__src">' . esc_html__( 'از سفارش', 'medyar-nursing-services' ) . '</small>' : '';
	if ( 'check' === $type ) :
		?>
		<label class="mns-ncl__tick"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="yes"<?php checked( $val, 'yes' ); ?><?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<span class="mns-ncl__box" aria-hidden="true"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="mns-ncl__tick-text"><?php echo esc_html( $label ); ?></span>
		</label>
		<?php
	elseif ( 'chips' === $type ) :
		?>
		<fieldset class="mns-ncl__field"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<legend class="mns-ncl__label"><?php echo esc_html( $label ); ?> <?php echo $src; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></legend>
			<div class="mns-ncl__chips">
				<?php foreach ( $f['options'] as $opt ) : ?>
					<label class="mns-ncl__chip"><input type="radio" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( (string) $opt ); ?>"<?php checked( $val, (string) $opt ); ?><?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><span><?php echo esc_html( (string) $opt ); ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<?php
	elseif ( 'number' === $type ) :
		?>
		<div class="mns-ncl__field mns-ncl__field--num"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<label class="mns-ncl__label" for="mns-ncl-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?> <?php echo $src; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
			<span class="mns-ncl__num">
				<input id="mns-ncl-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="text" inputmode="decimal" autocomplete="off" value="<?php echo esc_attr( $fa( $val ) ); ?>"<?php echo $dis; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php if ( ! empty( $f['unit'] ) ) : ?>
					<span class="mns-ncl__unit"><?php echo esc_html( (string) $f['unit'] ); ?></span>
				<?php endif; ?>
			</span>
			<p class="mns-ncl__msg" data-mns-ncl-msg role="status"></p>
		</div>
		<?php
	endif;
};

?>
<div
	class="mns-ncl mns-ncl--form<?php echo $locked ? ' is-locked' : ''; ?>"
	data-mns-ncl
	data-mns-ndash
	data-phase="<?php echo esc_attr( $phase ); ?>"
	data-key="<?php echo esc_attr( 'mns-ncl2-' . $code . '-' . $service . '-' . $phase ); ?>"
	data-config="<?php echo esc_attr( (string) wp_json_encode( $config ) ); ?>"
	data-done-url="<?php echo esc_url( $phase_url( 'submitted', $service ) ); ?>"
>
	<span hidden data-mns-ncl-trash><?php echo mns_icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

	<p class="mns-breadcrumb mns-ncl__back">
		<a href="<?php echo esc_url( $back_url ); ?>">
			<span class="mns-breadcrumb__icon" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php echo esc_html__( 'بازگشت به سفارش', 'medyar-nursing-services' ); ?>
		</a>
	</p>

	<?php if ( $preview ) : ?>
		<nav class="mns-ncl__pv" aria-label="<?php echo esc_attr__( 'پیش‌نمایش (فقط محیط توسعه)', 'medyar-nursing-services' ); ?>">
			<span class="mns-ncl__pv-list">
				<?php
				$phases = array(
					'draft'     => __( 'حین خدمت', 'medyar-nursing-services' ),
					'ended'     => __( 'بعد از پایان', 'medyar-nursing-services' ),
					'submitted' => __( 'ثبت‌شده', 'medyar-nursing-services' ),
				);
				foreach ( $phases as $k => $label ) :
					?>
					<a class="mns-ncl__pv-item<?php echo $k === $phase ? ' is-active' : ''; ?>" href="<?php echo esc_url( $phase_url( $k, $service ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
				<span class="mns-ncl__pv-sep" aria-hidden="true"></span>
				<?php foreach ( $services as $k => $s ) : ?>
					<a class="mns-ncl__pv-item<?php echo (string) $k === $service ? ' is-active' : ''; ?>" href="<?php echo esc_url( $phase_url( $phase, (string) $k ) ); ?>"><?php echo esc_html( (string) $s['label'] ); ?></a>
				<?php endforeach; ?>
			</span>
		</nav>
	<?php endif; ?>

	<div class="mns-collapse mns-ncl__net" data-mns-net inert>
		<div class="mns-collapse__inner">
			<p class="mns-ncl__netmsg" role="status"><?php echo esc_html__( 'اتصال برقرار نیست؛ پیش‌نویس روی دستگاه شما نگه داشته می‌شود و بعداً ارسال می‌شود.', 'medyar-nursing-services' ); ?></p>
		</div>
	</div>

	<header class="mns-ncl__top">
		<h1 class="mns-ncl__title" id="mns-ncl-title">
			<?php
			/* translators: %s: service name */
			echo esc_html( sprintf( __( 'چک‌لیست %s', 'medyar-nursing-services' ), (string) ( $svc['label'] ?? '' ) ) );
			?>
		</h1>
		<p class="mns-ncl__sub"><?php echo esc_html__( 'حسن محمدی · ونک', 'medyar-nursing-services' ); ?></p>
		<?php if ( $locked ) : ?>
			<p class="mns-ncl__lock">
				<span aria-hidden="true"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'این چک‌لیست ثبت نهایی شده و فقط‌خواندنی است. برای اصلاح با پشتیبانی تماس بگیرید.', 'medyar-nursing-services' ); ?></span>
			</p>
		<?php endif; ?>
	</header>

	<div class="mns-collapse is-open mns-ncl__view" data-mns-ncl-formview>
		<div class="mns-collapse__inner">
			<form class="mns-ncl__form" data-mns-ncl-form novalidate onsubmit="return false;">
				<ol class="mns-ncl__steps">
					<?php
					foreach ( $steps as $i => $st ) :
						$skey     = (string) $st['key'];
						$needs    = (string) ( $st['needs'] ?? '' );
						$gate     = 'ended' === $needs && 'draft' === $phase;
						$fields_s = $arr( $st['fields'] ?? array() );
						?>
						<li
							class="mns-ncl__step"
							data-mns-step="<?php echo esc_attr( $skey ); ?>"
							data-hint="<?php echo esc_attr( (string) ( $st['hint'] ?? '' ) ); ?>"
							<?php echo $gate ? 'data-gated data-locked-hint="' . esc_attr( (string) ( $st['locked_hint'] ?? '' ) ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						>
							<div class="mns-ncl__sh">
								<span class="mns-ncl__badge" aria-hidden="true">
									<b><?php echo esc_html( $fa( $i + 1 ) ); ?></b>
									<span class="mns-ncl__badge-ok"><?php echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<span class="mns-ncl__badge-lock"><?php echo mns_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</span>
								<span class="mns-ncl__st">
									<span class="mns-ncl__st-title"><?php echo esc_html( (string) $st['title'] ); ?><?php echo 'extras' === $skey ? ' <small class="mns-ncl__opt">' . esc_html__( 'اختیاری', 'medyar-nursing-services' ) . '</small>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<span class="mns-ncl__st-sum" data-mns-step-sum></span>
								</span>
							</div>
							<div class="mns-collapse<?php echo $gate ? '' : ' is-open'; ?>" id="mns-ncl-b-<?php echo esc_attr( $skey ); ?>"<?php echo $gate ? ' inert' : ''; ?>>
								<div class="mns-collapse__inner">
									<div class="mns-ncl__body">
										<?php if ( 'extras' === $skey ) : ?>
											<p class="mns-ncl__lead"><?php echo esc_html__( 'اقلام معمول این خدمت از قبل انتخاب شده‌اند؛ تعداد را اصلاح کنید یا مورد دیگری اضافه کنید.', 'medyar-nursing-services' ); ?></p>
											<div data-mns-ncl-items>
												<ul class="mns-ncl__chosen" data-mns-ncl-chosen></ul>
												<p class="mns-ncl__empty" data-mns-ncl-empty><?php echo esc_html__( 'هنوز قلمی اضافه نشده است.', 'medyar-nursing-services' ); ?></p>
												<?php if ( ! $locked ) : ?>
													<button type="button" class="mns-ncl__add" data-mns-fold aria-expanded="false" aria-controls="mns-ncl-pick">
														<span aria-hidden="true"><?php echo mns_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'قلم یا خدمت دیگر', 'medyar-nursing-services' ); ?>
													</button>
													<div class="mns-collapse" id="mns-ncl-pick" inert>
														<div class="mns-collapse__inner">
															<div class="mns-ncl__pick">
																<label class="mns-ncl__find">
																	<span class="screen-reader-text"><?php echo esc_html__( 'جستجوی قلم', 'medyar-nursing-services' ); ?></span>
																	<input type="search" placeholder="<?php echo esc_attr__( 'جستجوی قلم یا خدمت', 'medyar-nursing-services' ); ?>" autocomplete="off" data-mns-ncl-find>
																</label>
																<ul class="mns-ncl__catalog" data-mns-ncl-catalog></ul>
															</div>
														</div>
													</div>
												<?php endif; ?>
											</div>
										<?php else : ?>
											<?php if ( 'vitals' === $skey ) : ?>
												<div class="mns-ncl__grid">
													<?php
													foreach ( $fields_s as $f ) {
														$render_field( $f );
													}
													?>
												</div>
											<?php else : ?>
												<?php
												foreach ( $fields_s as $f ) {
													$render_field( $f );
												}
												?>
											<?php endif; ?>
										<?php endif; ?>

										<?php if ( ! $locked ) : ?>
											<p class="mns-ncl__reason" data-mns-step-reason role="alert"></p>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>

				<?php if ( ! $locked ) : ?>
					<div class="mns-collapse mns-ncl__wrap" data-mns-ncl-wait inert>
						<div class="mns-collapse__inner">
							<div class="mns-ncl__note">
								<span class="mns-ncl__note-ico" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<div>
									<p class="mns-ncl__note-t"><?php echo esc_html__( 'تا اینجا همه‌چیز ثبت شد', 'medyar-nursing-services' ); ?></p>
									<p class="mns-ncl__note-s"><?php echo esc_html__( 'بعد از پایان خدمت، بخش «پایان خدمت» را تکمیل کنید. بعد از آن «ثبت نهایی» فعال می‌شود.', 'medyar-nursing-services' ); ?></p>
								</div>
							</div>
						</div>
					</div>

					<div class="mns-ncl__more">
						<button type="button" class="mns-ncl__morebtn" data-mns-fold aria-expanded="false" aria-controls="mns-ncl-notes-c">
							<span><span aria-hidden="true"><?php echo mns_icon( 'edit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'یادداشت (اختیاری)', 'medyar-nursing-services' ); ?></span>
							<span class="mns-ncl__fold-ico" aria-hidden="true"><?php echo mns_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</button>
						<div class="mns-collapse" id="mns-ncl-notes-c" inert>
							<div class="mns-collapse__inner">
								<label class="screen-reader-text" for="mns-ncl-notes"><?php echo esc_html__( 'یادداشت', 'medyar-nursing-services' ); ?></label>
								<textarea id="mns-ncl-notes" class="mns-ncl__notes" rows="3" maxlength="1000" data-mns-ncl-notes placeholder="<?php echo esc_attr__( 'اگر نکته‌ای دربارهٔ این مراجعه هست بنویسید (جدا از گزارش پایان مراجعه).', 'medyar-nursing-services' ); ?>"></textarea>
								<p class="mns-ncl__counter" data-mns-ncl-counter>۰ از ۱٬۰۰۰ نویسه</p>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( 'ended' === $phase ) : ?>
					<div class="mns-ncl__finish" data-mns-ncl-finish>
						<div class="mns-ncl__finish-btns">
							<button type="button" class="mns-submit-btn" data-mns-ncl-review><span><?php echo esc_html__( 'بازبینی و ثبت نهایی', 'medyar-nursing-services' ); ?></span></button>
						</div>
					</div>
				<?php endif; ?>
			</form>
		</div>
	</div>

	<div class="mns-collapse mns-ncl__view" data-mns-ncl-reviewview inert>
		<div class="mns-collapse__inner">
			<div class="mns-ncl__review">
				<section class="mns-card mns-ncl__rv" aria-labelledby="mns-ncl-rv">
					<h2 class="mns-ncl__rv-title" id="mns-ncl-rv"><?php echo esc_html__( 'خلاصهٔ چک‌لیست', 'medyar-nursing-services' ); ?></h2>
					<div data-mns-ncl-summary></div>
				</section>
				<p class="mns-ncl__warnbox"><?php echo esc_html__( 'بعد از ثبت نهایی، چک‌لیست قفل می‌شود و فقط پشتیبانی می‌تواند برای اصلاح آن را باز کند. فاکتور بعد از ثبت گزارش پایان مراجعه هم صادر می‌شود.', 'medyar-nursing-services' ); ?></p>
				<div class="mns-collapse" data-mns-confirm id="mns-ncl-confirm" inert>
					<div class="mns-collapse__inner">
						<div class="mns-ncl__confirm">
							<p class="mns-ncl__confirm-q"><?php echo esc_html__( 'ثبت نهایی شود؟ بعد از آن چک‌لیست قفل می‌شود.', 'medyar-nursing-services' ); ?></p>
							<p class="mns-ncl__confirm-err" role="alert" data-mns-confirm-error></p>
							<div class="mns-ncl__confirm-actions">
								<button type="button" class="mns-submit-btn" data-mns-confirm-ok data-mns-go="<?php echo esc_url( $phase_url( 'submitted', $service ) ); ?>"><span><?php echo esc_html__( 'بله، ثبت نهایی', 'medyar-nursing-services' ); ?></span></button>
								<button type="button" class="mns-ncl__btn" data-mns-confirm-cancel><?php echo esc_html__( 'انصراف', 'medyar-nursing-services' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<div class="mns-ncl__rv-actions">
					<button type="button" class="mns-ncl__btn" data-mns-ncl-edit><?php echo esc_html__( 'بازگشت به ویرایش', 'medyar-nursing-services' ); ?></button>
					<button type="button" class="mns-submit-btn" data-mns-ncl-final><span><?php echo esc_html__( 'ثبت نهایی چک‌لیست', 'medyar-nursing-services' ); ?></span></button>
				</div>
			</div>
		</div>
	</div>
</div>
