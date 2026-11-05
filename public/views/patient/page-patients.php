<?php
/**
 * Patient — saved people (UX Phase D).
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

MNS_UI::page_header(
	array(
		'title'   => __( 'بیماران من', 'medyar-nursing-services' ),
		'lead'    => __( 'افرادی که می‌توانید برایشان سفارش ثبت کنید.', 'medyar-nursing-services' ),
		'actions' => $show_form
			? ''
			: '<a class="mns-submit-btn mns-submit-btn--auto mns-page-header__cta-desktop" href="' . esc_url( add_query_arg( 'mns_form', '1' ) ) . '">' . esc_html__( 'افزودن بیمار', 'medyar-nursing-services' ) . '</a>',
	)
);
?>
<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

<?php if ( $show_form ) : ?>
	<form class="mns-card mns-form" method="post" data-mns-mock="ok" data-mns-success="<?php echo esc_attr__( 'بیمار جدید ذخیره شد.', 'medyar-nursing-services' ); ?>">
		<h2 class="mns-card__title"><?php echo esc_html__( 'افزودن بیمار', 'medyar-nursing-services' ); ?></h2>
		<label class="mns-field-block">
			<span class="mns-field-block__label"><?php echo esc_html__( 'نام و نام خانوادگی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
			<span class="mns-field-block__control"><input name="name" required maxlength="191"></span>
		</label>
		<div class="mns-form-row--2">
			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'نسبت', 'medyar-nursing-services' ); ?></span>
				<span class="mns-field-block__control mns-field-block__control--select">
					<select class="mns-select-native" name="relation" data-mns-select>
						<option value="self"><?php echo esc_html__( 'خودم', 'medyar-nursing-services' ); ?></option>
						<option value="father"><?php echo esc_html__( 'پدر', 'medyar-nursing-services' ); ?></option>
						<option value="mother"><?php echo esc_html__( 'مادر', 'medyar-nursing-services' ); ?></option>
						<option value="other"><?php echo esc_html__( 'سایر', 'medyar-nursing-services' ); ?></option>
					</select>
					<span class="mns-field-block__chevron">▾</span>
				</span>
			</label>
			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'سن', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
				<span class="mns-field-block__control"><input name="age" type="number" min="0" max="120" required dir="ltr"></span>
			</label>
		</div>
		<label class="mns-field-block">
			<span class="mns-field-block__label"><?php echo esc_html__( 'جنسیت', 'medyar-nursing-services' ); ?></span>
			<span class="mns-field-block__control mns-field-block__control--select">
				<select class="mns-select-native" name="gender" data-mns-select>
					<option value="female"><?php echo esc_html__( 'خانم', 'medyar-nursing-services' ); ?></option>
					<option value="male"><?php echo esc_html__( 'آقا', 'medyar-nursing-services' ); ?></option>
				</select>
				<span class="mns-field-block__chevron">▾</span>
			</span>
		</label>
		<label class="mns-field-block">
			<span class="mns-field-block__label"><?php echo esc_html__( 'موبایل', 'medyar-nursing-services' ); ?></span>
			<span class="mns-field-block__control"><input name="phone" data-mns-iran-mobile dir="ltr" inputmode="tel"></span>
		</label>
		<label class="mns-field-block">
			<span class="mns-field-block__label"><?php echo esc_html__( 'بیماری‌های زمینه‌ای', 'medyar-nursing-services' ); ?></span>
			<span class="mns-field-block__control" style="min-height:88px;align-items:flex-start"><textarea name="comorbidities" rows="3" maxlength="400"></textarea></span>
		</label>
		<div class="mns-actions-row">
			<button class="mns-submit-btn mns-submit-btn--auto" type="submit"><?php echo esc_html__( 'ذخیره', 'medyar-nursing-services' ); ?></button>
			<a class="mns-outline-btn" href="<?php echo esc_url( remove_query_arg( 'mns_form' ) ); ?>"><?php echo esc_html( MNS_Copy::t( 'action.cancel' ) ); ?></a>
		</div>
	</form>
<?php endif; ?>

<?php if ( empty( $items ) ) : ?>
	<?php
	MNS_UI::empty_state(
		array(
			'title'     => __( 'هنوز بیماری ذخیره نشده', 'medyar-nursing-services' ),
			'text'      => __( 'خودتان یا فرد دیگری را اضافه کنید تا ثبت سفارش سریع‌تر شود.', 'medyar-nursing-services' ),
			'cta_label' => __( 'افزودن بیمار', 'medyar-nursing-services' ),
			'cta_url'   => add_query_arg( 'mns_form', '1' ),
			'icon'      => 'users',
		)
	);
	?>
<?php else : ?>
	<ul class="mns-list">
		<?php foreach ( $items as $row ) : ?>
			<?php
			$order_url = add_query_arg(
				array(
					'mns_patient' => (string) ( $row['id'] ?? '' ),
				),
				$new_order
			);
			$meta = (string) ( $row['relation'] ?? '' )
				. ' · ' . MNS_Format::digits( (string) ( $row['age'] ?? '' ) ) . ' ' . __( 'سال', 'medyar-nursing-services' )
				. ' · ' . (string) ( $row['gender_label'] ?? '' );
			if ( ! empty( $row['phone'] ) ) {
				$meta .= ' · ' . MNS_Format::mobile( (string) $row['phone'] );
			}
			MNS_UI::list_item(
				array(
					'url'          => $order_url,
					'title'        => (string) ( $row['name'] ?? '' ),
					'meta'         => $meta,
					'meta2'        => (string) ( $row['comorbidities'] ?? '' ),
					'action_label' => __( 'ثبت سفارش', 'medyar-nursing-services' ),
				)
			);
			?>
			<?php if ( empty( $row['is_self'] ) ) : ?>
				<li class="mns-list-item mns-list-item--aside">
					<button type="button" class="mns-link-btn" data-mns-dialog-open="mns-del-patient-<?php echo esc_attr( (string) ( $row['id'] ?? '' ) ); ?>">
						<?php echo esc_html__( 'حذف این بیمار', 'medyar-nursing-services' ); ?>
					</button>
				</li>
				<?php
				ob_start();
				echo '<p>' . esc_html__( 'این فرد از فهرست بیماران شما حذف می‌شود.', 'medyar-nursing-services' ) . '</p>';
				$body = ob_get_clean();
				ob_start();
				echo '<button type="button" class="mns-outline-btn" data-mns-dialog-close>' . esc_html( MNS_Copy::t( 'action.cancel' ) ) . '</button>';
				echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-mock-action data-mns-success="' . esc_attr__( 'بیمار حذف شد.', 'medyar-nursing-services' ) . '" data-mns-dialog-close>' . esc_html__( 'بله، حذف شود', 'medyar-nursing-services' ) . '</button>';
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
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
