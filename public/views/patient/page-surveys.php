<?php
/**
 * Surveys queue + complaint path (Phase 4).
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

$active = isset( $_GET['mns_survey'] ) ? sanitize_key( wp_unslash( $_GET['mns_survey'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current = null;
foreach ( $open as $row ) {
	if ( (string) ( $row['id'] ?? '' ) === $active ) {
		$current = $row;
		break;
	}
}
if ( null === $current && ! empty( $open ) && 'open' === $mode && '' === $active ) {
	// list mode
}

MNS_UI::page_header(
	array(
		'title' => __( 'پرسشنامه‌ها', 'medyar-nursing-services' ),
		'lead'  => __( 'امتیاز علمی، اخلاق و نظم. پس از ارسال قابل ویرایش نیست.', 'medyar-nursing-services' ),
	)
);
?>

<div class="mns-tabs" role="tablist">
	<?php
	$tabs = array(
		'open'      => __( 'باز', 'medyar-nursing-services' ),
		'done'      => __( 'ارسال‌شده', 'medyar-nursing-services' ),
		'complaint' => __( 'نارضایتی', 'medyar-nursing-services' ),
	);
	foreach ( $tabs as $key => $label ) :
		$url = add_query_arg(
			array(
				'mns_panel' => 'patient',
				'mns_page'  => 'surveys',
				'mns_tab'   => $key,
			),
			home_url( '/nursing-patient-panel/' )
		);
		?>
		<a class="mns-tabs__btn<?php echo $mode === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
	<?php endforeach; ?>
</div>

<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

<?php if ( 'open' === $mode ) : ?>
	<?php if ( $current ) : ?>
		<form class="mns-card mns-form" method="post" data-mns-mock="ok" data-mns-success="<?php echo esc_attr__( 'نظر شما ثبت شد. سپاس.', 'medyar-nursing-services' ); ?>">
			<p class="mns-field-hint">
				<?php echo esc_html( (string) ( $current['service'] ?? '' ) ); ?>
				· <?php echo esc_html( (string) ( $current['nurse'] ?? '' ) ); ?>
				· <span dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $current['order'] ?? '' ) ) ); ?></span>
			</p>
			<?php
			$axes = array(
				'science' => __( 'علمی', 'medyar-nursing-services' ),
				'ethics'  => __( 'اخلاق و خوشرویی', 'medyar-nursing-services' ),
				'orderly' => __( 'نظم در کار', 'medyar-nursing-services' ),
			);
			foreach ( $axes as $axis => $axis_label ) :
				?>
				<p class="mns-field-block__label" style="margin-top:12px"><?php echo esc_html( $axis_label ); ?></p>
				<div class="mns-stars" data-mns-stars data-value="0" data-name="<?php echo esc_attr( $axis ); ?>">
					<?php for ( $i = 0; $i < 5; $i++ ) : ?>
						<button type="button" aria-label="<?php echo esc_attr( MNS_Format::digits( (string) ( $i + 1 ) ) ); ?>">★</button>
					<?php endfor; ?>
				</div>
				<input type="hidden" name="<?php echo esc_attr( $axis ); ?>" value="0" data-mns-star-value="<?php echo esc_attr( $axis ); ?>">
			<?php endforeach; ?>
			<label class="mns-field-block" style="margin-top:16px">
				<span class="mns-field-block__label"><?php echo esc_html__( 'توضیحات (اختیاری)', 'medyar-nursing-services' ); ?></span>
				<span class="mns-field-block__control" style="min-height:90px;align-items:flex-start"><textarea name="comment" maxlength="400" rows="3"></textarea></span>
			</label>
			<div class="mns-actions-row">
				<button type="submit" class="mns-submit-btn mns-submit-btn--auto"><?php echo esc_html__( 'ارسال پرسشنامه', 'medyar-nursing-services' ); ?></button>
				<a class="mns-outline-btn" href="<?php echo esc_url( remove_query_arg( 'mns_survey' ) ); ?>"><?php echo esc_html( MNS_Copy::t( 'action.back' ) ); ?></a>
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
		<ul class="mns-order-cards">
			<?php foreach ( $open as $row ) : ?>
				<li>
					<a class="mns-order-card" href="<?php echo esc_url( add_query_arg( 'mns_survey', (string) ( $row['id'] ?? '' ) ) ); ?>">
						<span class="mns-order-card__top">
							<span class="mns-order-card__service"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></span>
							<span class="mns-status mns-status--waiting"><?php echo esc_html__( 'در انتظار پاسخ', 'medyar-nursing-services' ); ?></span>
						</span>
						<span class="mns-order-card__meta">
							<?php echo esc_html( (string) ( $row['nurse'] ?? '' ) ); ?>
							· <?php echo esc_html( MNS_Format::human( (string) ( $row['when'] ?? '' ) ) ); ?>
						</span>
						<span class="mns-order-card__code" dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $row['order'] ?? '' ) ) ); ?></span>
					</a>
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
				'text'  => '',
				'icon'  => 'star',
			)
		);
		?>
	<?php else : ?>
		<?php foreach ( $done as $row ) : ?>
			<section class="mns-card">
				<h2 class="mns-card__title"><?php echo esc_html( (string) ( $row['service'] ?? '' ) ); ?></h2>
				<p class="mns-field-hint"><?php echo esc_html( (string) ( $row['nurse'] ?? '' ) ); ?> · <span dir="ltr"><?php echo esc_html( MNS_Format::digits( (string) ( $row['order'] ?? '' ) ) ); ?></span></p>
				<?php
				MNS_UI::star_rating( array( 'value' => $row['science'] ?? 0, 'label' => __( 'علمی', 'medyar-nursing-services' ) ) );
				MNS_UI::star_rating( array( 'value' => $row['ethics'] ?? 0, 'label' => __( 'اخلاق', 'medyar-nursing-services' ) ) );
				MNS_UI::star_rating( array( 'value' => $row['orderly'] ?? 0, 'label' => __( 'نظم', 'medyar-nursing-services' ) ) );
				?>
				<?php if ( ! empty( $row['comment'] ) ) : ?>
					<p class="mns-catalog__muted"><?php echo esc_html( (string) $row['comment'] ); ?></p>
				<?php endif; ?>
				<p class="mns-field-hint"><?php echo esc_html__( 'ثبت‌شده — فقط‌خواندنی', 'medyar-nursing-services' ); ?></p>
			</section>
		<?php endforeach; ?>
	<?php endif; ?>
<?php else : ?>
	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'ثبت نارضایتی', 'medyar-nursing-services' ); ?></h2>
		<form class="mns-form" method="post" data-mns-mock="ok" data-mns-success="<?php echo esc_attr__( 'نارضایتی ثبت شد و پیگیری می‌شود.', 'medyar-nursing-services' ); ?>">
			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'موضوع', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
				<span class="mns-field-block__control"><input name="subject" required maxlength="120"></span>
			</label>
			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'شرح', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
				<span class="mns-field-block__control" style="min-height:100px;align-items:flex-start"><textarea name="body" required rows="4" maxlength="800"></textarea></span>
			</label>
			<button type="submit" class="mns-submit-btn mns-submit-btn--auto"><?php echo esc_html__( 'ارسال نارضایتی', 'medyar-nursing-services' ); ?></button>
		</form>
	</section>
	<?php if ( ! empty( $complaints ) ) : ?>
		<section class="mns-card">
			<h2 class="mns-card__title"><?php echo esc_html__( 'پیگیری', 'medyar-nursing-services' ); ?></h2>
			<ul class="mns-order-cards">
				<?php foreach ( $complaints as $c ) : ?>
					<li class="mns-order-card mns-order-card--listed">
						<span class="mns-order-card__main">
							<span class="mns-order-card__top">
								<span class="mns-order-card__service"><?php echo esc_html( (string) ( $c['subject'] ?? '' ) ); ?></span>
								<span class="mns-status mns-status--waiting"><?php echo esc_html( (string) ( $c['label'] ?? '' ) ); ?></span>
							</span>
							<span class="mns-order-card__meta"><?php echo esc_html( MNS_Format::human( (string) ( $c['when'] ?? '' ) ) ); ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
<?php endif; ?>
