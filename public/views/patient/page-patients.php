<?php
/**
 * Patient: saved people. One card per person (who, details, order / delete), add form folds open in place.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data  = MNS_Static_Fixtures::get( 'patient/patients_list' );
$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();

if ( MNS_Dev::is( 'empty' ) ) {
	$items = array();
}

$show_form = MNS_Dev::is( 'form' ) || ( isset( $_GET['mns_form'] ) && '1' === (string) wp_unslash( $_GET['mns_form'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$new_order = MNS_Pages::panel_url( 'patient', 'new-order' );

/* Medical profile options (product suggestion; in the real backend this list is a setting). */
$conditions      = array(
	'diabetes'     => __( 'دیابت', 'medyar-nursing-services' ),
	'hypertension' => __( 'فشار خون', 'medyar-nursing-services' ),
	'heart'        => __( 'بیماری قلبی', 'medyar-nursing-services' ),
	'kidney'       => __( 'بیماری کلیوی', 'medyar-nursing-services' ),
	'respiratory'  => __( 'آسم و تنفسی', 'medyar-nursing-services' ),
	'epilepsy'     => __( 'صرع', 'medyar-nursing-services' ),
	'bleeding'     => __( 'رقیق‌کنندهٔ خون یا اختلال انعقاد', 'medyar-nursing-services' ),
	'pregnancy'    => __( 'بارداری', 'medyar-nursing-services' ),
);
$mobility_labels = array(
	'assisted'  => __( 'نیازمند کمک', 'medyar-nursing-services' ),
	'bedridden' => __( 'زمین‌گیر', 'medyar-nursing-services' ),
);

MNS_UI::page_header(
	array(
		'title' => __( 'بیماران من', 'medyar-nursing-services' ),
		'lead'  => __( 'افرادی که می‌توانید برایشان سفارش ثبت کنید.', 'medyar-nursing-services' ),
		'icon'  => 'users',
	)
);
?>
<div class="mns-pat">
	<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

	<div class="mns-pat__bar">
		<button type="button" class="mns-submit-btn mns-pat__add" data-mns-collapse-toggle aria-expanded="<?php echo $show_form ? 'true' : 'false'; ?>" aria-controls="mns-pat-form">
			<?php echo mns_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php echo esc_html__( 'افزودن بیمار', 'medyar-nursing-services' ); ?></span>
		</button>
	</div>

	<div class="mns-pat__collapse<?php echo $show_form ? ' is-open' : ''; ?>" id="mns-pat-form"<?php echo $show_form ? '' : ' inert'; ?>>
		<div class="mns-pat__collapse-inner">
			<form class="mns-pat__form" method="post" data-mns-mock="ok" data-mns-success="<?php echo esc_attr__( 'بیمار جدید ذخیره شد.', 'medyar-nursing-services' ); ?>">
				<h2 class="mns-pat__form-title"><?php echo esc_html__( 'افزودن بیمار', 'medyar-nursing-services' ); ?></h2>
				<div class="mns-pat__grid">
					<label class="mns-field-block mns-pat__f-name">
						<span class="mns-field-block__label"><?php echo esc_html__( 'نام و نام خانوادگی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control"><input name="name" required maxlength="191" autocomplete="off"></span>
					</label>
					<label class="mns-field-block mns-pat__f-phone">
						<span class="mns-field-block__label"><?php echo esc_html__( 'موبایل', 'medyar-nursing-services' ); ?><span class="mns-field-block__opt"><?php echo esc_html__( 'اختیاری', 'medyar-nursing-services' ); ?></span></span>
						<span class="mns-field-block__control"><input name="phone" data-mns-iran-mobile dir="ltr" inputmode="tel" autocomplete="off"></span>
					</label>
					<label class="mns-field-block mns-pat__f-rel">
						<span class="mns-field-block__label"><?php echo esc_html__( 'نسبت', 'medyar-nursing-services' ); ?></span>
						<span class="mns-field-block__control mns-field-block__control--select">
							<select class="mns-select-native" name="relation" data-mns-select>
								<option value="self"><?php echo esc_html__( 'خودم', 'medyar-nursing-services' ); ?></option>
								<option value="father"><?php echo esc_html__( 'پدر', 'medyar-nursing-services' ); ?></option>
								<option value="mother"><?php echo esc_html__( 'مادر', 'medyar-nursing-services' ); ?></option>
								<option value="spouse"><?php echo esc_html__( 'همسر', 'medyar-nursing-services' ); ?></option>
								<option value="child"><?php echo esc_html__( 'فرزند', 'medyar-nursing-services' ); ?></option>
								<option value="other"><?php echo esc_html__( 'سایر', 'medyar-nursing-services' ); ?></option>
							</select>
							<span class="mns-field-block__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</span>
					</label>
					<label class="mns-field-block mns-pat__f-age">
						<span class="mns-field-block__label"><?php echo esc_html__( 'سن', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
						<span class="mns-field-block__control"><input name="age" type="number" min="0" max="120" required inputmode="numeric"></span>
					</label>
					<label class="mns-field-block mns-pat__f-gender">
						<span class="mns-field-block__label"><?php echo esc_html__( 'جنسیت', 'medyar-nursing-services' ); ?></span>
						<span class="mns-field-block__control mns-field-block__control--select">
							<select class="mns-select-native" name="gender" data-mns-select>
								<option value="female"><?php echo esc_html__( 'خانم', 'medyar-nursing-services' ); ?></option>
								<option value="male"><?php echo esc_html__( 'آقا', 'medyar-nursing-services' ); ?></option>
							</select>
							<span class="mns-field-block__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</span>
					</label>
				</div>

				<?php /* Medical profile: optional and folded; the nurse sees it before every visit. */ ?>
				<button type="button" class="mns-pat__med-toggle" data-mns-collapse-toggle aria-expanded="false" aria-controls="mns-pat-med">
					<span class="mns-pat__med-ico" aria-hidden="true"><?php echo mns_icon( 'activity' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="mns-pat__med-text">
						<strong><?php echo esc_html__( 'اطلاعات پزشکی', 'medyar-nursing-services' ); ?></strong>
						<small><?php echo esc_html__( 'بیماری، حساسیت و داروها؛ پرستار قبل از مراجعه می‌بیند', 'medyar-nursing-services' ); ?></small>
					</span>
					<span class="mns-pat__med-chev" aria-hidden="true"><?php echo mns_icon( 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</button>
				<div class="mns-pat__fold" id="mns-pat-med" inert>
					<div class="mns-pat__fold-inner">
						<div class="mns-pat__med">
							<fieldset class="mns-pat__set">
								<legend><?php echo esc_html__( 'بیماری‌های زمینه‌ای', 'medyar-nursing-services' ); ?></legend>
								<div class="mns-pat__opts">
									<?php foreach ( $conditions as $cv => $cl ) : ?>
										<label class="mns-pat__opt"><input type="checkbox" name="conditions[]" value="<?php echo esc_attr( $cv ); ?>"><span><?php echo esc_html( $cl ); ?></span></label>
									<?php endforeach; ?>
								</div>
								<span class="mns-field-block__control mns-pat__other"><input name="conditions_other" maxlength="120" autocomplete="off" placeholder="<?php echo esc_attr__( 'مورد دیگر؛ مثلاً پارکینسون', 'medyar-nursing-services' ); ?>" aria-label="<?php echo esc_attr__( 'بیماری زمینه‌ای دیگر', 'medyar-nursing-services' ); ?>"></span>
							</fieldset>

							<fieldset class="mns-pat__set mns-pat__allergy">
								<legend><?php echo esc_html__( 'حساسیت دارویی', 'medyar-nursing-services' ); ?></legend>
								<div class="mns-pat__opts">
									<label class="mns-pat__opt"><input type="radio" name="allergy" value="no" checked><span><?php echo esc_html__( 'ندارد', 'medyar-nursing-services' ); ?></span></label>
									<label class="mns-pat__opt"><input type="radio" name="allergy" value="yes"><span><?php echo esc_html__( 'دارد', 'medyar-nursing-services' ); ?></span></label>
									<label class="mns-pat__opt"><input type="radio" name="allergy" value="unknown"><span><?php echo esc_html__( 'نمی‌دانم', 'medyar-nursing-services' ); ?></span></label>
								</div>
								<div class="mns-pat__allergy-detail">
									<div>
										<span class="mns-field-block__control mns-pat__other"><input name="allergy_detail" maxlength="120" autocomplete="off" placeholder="<?php echo esc_attr__( 'به چه دارو یا ماده‌ای؟ مثلاً پنی‌سیلین', 'medyar-nursing-services' ); ?>" aria-label="<?php echo esc_attr__( 'به چه دارو یا ماده‌ای حساسیت دارد؟', 'medyar-nursing-services' ); ?>"></span>
									</div>
								</div>
							</fieldset>

							<fieldset class="mns-pat__set">
								<legend><?php echo esc_html__( 'وضعیت حرکتی', 'medyar-nursing-services' ); ?></legend>
								<div class="mns-pat__opts">
									<label class="mns-pat__opt"><input type="radio" name="mobility" value="independent" checked><span><?php echo esc_html__( 'مستقل', 'medyar-nursing-services' ); ?></span></label>
									<label class="mns-pat__opt"><input type="radio" name="mobility" value="assisted"><span><?php echo esc_html__( 'نیازمند کمک', 'medyar-nursing-services' ); ?></span></label>
									<label class="mns-pat__opt"><input type="radio" name="mobility" value="bedridden"><span><?php echo esc_html__( 'زمین‌گیر', 'medyar-nursing-services' ); ?></span></label>
								</div>
							</fieldset>

							<label class="mns-field-block mns-pat__meds">
								<span class="mns-field-block__label"><?php echo esc_html__( 'داروهای مصرفی', 'medyar-nursing-services' ); ?></span>
								<span class="mns-field-block__control"><input name="medications" maxlength="200" autocomplete="off" placeholder="<?php echo esc_attr__( 'مثلاً متفورمین، آسپرین', 'medyar-nursing-services' ); ?>"></span>
							</label>
						</div>
					</div>
				</div>

				<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>
				<div class="mns-pat__form-actions">
					<button class="mns-outline-btn" type="button" data-mns-collapse-toggle aria-controls="mns-pat-form" aria-expanded="true"><?php echo esc_html( MNS_Copy::t( 'action.cancel' ) ); ?></button>
					<button class="mns-submit-btn" type="submit"><span><?php echo esc_html__( 'ذخیره', 'medyar-nursing-services' ); ?></span></button>
				</div>
			</form>
		</div>
	</div>

	<?php if ( empty( $items ) ) : ?>
		<?php
		MNS_UI::empty_state(
			array(
				'title' => __( 'هنوز بیماری ذخیره نشده', 'medyar-nursing-services' ),
				'text'  => __( 'خودتان یا فرد دیگری را اضافه کنید تا ثبت سفارش سریع‌تر شود.', 'medyar-nursing-services' ),
				'icon'  => 'users',
			)
		);
		?>
	<?php else : ?>
		<ul class="mns-pat__list">
			<?php foreach ( $items as $row ) : ?>
				<?php
				$pid       = (string) ( $row['id'] ?? '' );
				$name      = (string) ( $row['name'] ?? '' );
				$is_self   = ! empty( $row['is_self'] );
				$order_url = add_query_arg( 'mns_patient', $pid, $new_order );
				$initial   = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );
				$meta      = array_filter(
					array(
						'' !== (string) ( $row['age'] ?? '' ) ? MNS_Format::digits( (string) $row['age'] ) . ' ' . __( 'سال', 'medyar-nursing-services' ) : '',
						(string) ( $row['gender_label'] ?? '' ),
					)
				);
				?>
				<li class="mns-pat__item">
					<span class="mns-pat__avatar mns-pat__avatar--<?php echo esc_attr( 'male' === (string) ( $row['gender'] ?? '' ) ? 'male' : 'female' ); ?>" aria-hidden="true"><?php echo esc_html( $initial ); ?></span>
					<div class="mns-pat__body">
						<p class="mns-pat__name">
							<span><?php echo esc_html( $name ); ?></span>
							<?php if ( '' !== (string) ( $row['relation'] ?? '' ) ) : ?>
								<span class="mns-pat__chip<?php echo $is_self ? ' is-self' : ''; ?>"><?php echo esc_html( (string) $row['relation'] ); ?></span>
							<?php endif; ?>
						</p>
						<p class="mns-pat__meta">
							<span><?php echo esc_html( implode( ' · ', $meta ) ); ?></span>
							<?php if ( ! empty( $row['phone'] ) ) : ?>
								<span class="mns-pat__phone" dir="ltr"><?php echo esc_html( MNS_Format::mobile( (string) $row['phone'] ) ); ?></span>
							<?php endif; ?>
						</p>
						<?php
						$cond_labels = array();
						foreach ( (array) ( $row['conditions'] ?? array() ) as $cv ) {
							$cond_labels[] = $conditions[ $cv ] ?? (string) $cv;
						}
						if ( ! empty( $row['comorbidities'] ) ) {
							$cond_labels[] = (string) $row['comorbidities'];
						}
						$has_allergy = 'yes' === (string) ( $row['allergy'] ?? '' );
						$mob_label   = $mobility_labels[ (string) ( $row['mobility'] ?? '' ) ] ?? '';
						?>
						<?php if ( ! empty( $cond_labels ) || $has_allergy || '' !== $mob_label ) : ?>
							<p class="mns-pat__tags">
								<?php if ( $has_allergy ) : ?>
									<span class="mns-pat__tag is-allergy"><?php echo mns_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( __( 'حساسیت', 'medyar-nursing-services' ) . ( ! empty( $row['allergy_detail'] ) ? ': ' . (string) $row['allergy_detail'] : '' ) ); ?></span>
								<?php endif; ?>
								<?php foreach ( $cond_labels as $cl ) : ?>
									<span class="mns-pat__tag"><?php echo esc_html( $cl ); ?></span>
								<?php endforeach; ?>
								<?php if ( '' !== $mob_label ) : ?>
									<span class="mns-pat__tag is-mob"><?php echo esc_html( $mob_label ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
					<div class="mns-pat__actions">
						<a class="mns-outline-btn mns-pat__order" href="<?php echo esc_url( $order_url ); ?>">
							<span><?php echo esc_html__( 'ثبت سفارش', 'medyar-nursing-services' ); ?></span>
						</a>
						<?php if ( ! $is_self ) : ?>
							<button type="button" class="mns-pat__del" data-mns-dialog-open="mns-del-patient-<?php echo esc_attr( $pid ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: patient name */ __( 'حذف %s', 'medyar-nursing-services' ), $name ) ); ?>">
								<?php echo mns_icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</button>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ( $items as $row ) : ?>
			<?php
			if ( ! empty( $row['is_self'] ) ) {
				continue;
			}
			ob_start();
			echo '<p>' . esc_html( sprintf( /* translators: %s: patient name */ __( '«%s» از فهرست بیماران شما حذف می‌شود. سفارش‌های قبلی او باقی می‌مانند.', 'medyar-nursing-services' ), (string) ( $row['name'] ?? '' ) ) ) . '</p>';
			$body = ob_get_clean();
			ob_start();
			echo '<button type="button" class="mns-outline-btn" data-mns-dialog-close>' . esc_html( MNS_Copy::t( 'action.cancel' ) ) . '</button>';
			echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto mns-oview-danger" data-mns-mock-action data-mns-success="' . esc_attr__( 'بیمار حذف شد.', 'medyar-nursing-services' ) . '" data-mns-dialog-close>' . esc_html__( 'بله، حذف شود', 'medyar-nursing-services' ) . '</button>';
			$footer = ob_get_clean();
			MNS_UI::partial(
				'dialog',
				array(
					'id'          => 'mns-del-patient-' . (string) ( $row['id'] ?? '' ),
					'title'       => __( 'حذف بیمار؟', 'medyar-nursing-services' ),
					'body_html'   => $body,
					'footer_html' => $footer,
					'variant'     => 'sheet',
				)
			);
			?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
