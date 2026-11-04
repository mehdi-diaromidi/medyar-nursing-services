<?php
/**
 * Dev-only component kit. Loaded from patient/nurse page-ui-kit.php.
 *
 * @var string $panel
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! MNS_Dev::enabled() ) {
	require MNS_PLUGIN_DIR . 'public/views/partials/not-found.php';
	return;
}

$panel = isset( $panel ) ? (string) $panel : 'patient';
$statuses = array_keys( MNS_Order_Status::all() );
?>
<section class="mns-ui-kit">
	<?php
	MNS_UI::page_header(
		array(
			'title'     => MNS_Copy::t( 'dev.ui_kit' ),
			'lead'      => __( 'نمایش زندهٔ کامپوننت‌های مشترک فاز ۱. فقط در حالت توسعه دیده می‌شود.', 'medyar-nursing-services' ),
			'back_url'  => MNS_Pages::panel_url( $panel, 'dashboard' ),
			'back_label'=> MNS_Copy::t( 'action.go_dashboard' ),
		)
	);
	?>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'وضعیت سفارش', 'medyar-nursing-services' ); ?></h2>
		<div class="mns-ui-kit__row">
			<?php foreach ( $statuses as $slug ) : ?>
				<?php MNS_UI::status_badge( $slug ); ?>
			<?php endforeach; ?>
		</div>
		<div class="mns-stack mns-stack--sm" style="margin-top:var(--mns-space-5)">
			<?php MNS_UI::order_timeline( MNS_Order_Status::ASSIGNED, array( 'variant' => 'compact', 'audience' => $panel ) ); ?>
			<?php MNS_UI::order_timeline( MNS_Order_Status::INVOICED, array( 'variant' => 'full', 'audience' => $panel ) ); ?>
		</div>
	</section>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'هشدار و حالت‌ها', 'medyar-nursing-services' ); ?></h2>
		<div class="mns-stack mns-stack--sm">
			<?php MNS_UI::alert( array( 'tone' => 'info', 'title' => 'اطلاع', 'text' => 'متن نمونهٔ اطلاع‌رسانی.', 'dismiss' => true ) ); ?>
			<?php MNS_UI::alert( array( 'tone' => 'success', 'text' => 'عملیات با موفقیت انجام شد.' ) ); ?>
			<?php MNS_UI::alert( array( 'tone' => 'warning', 'text' => 'یک مورد نیاز به توجه دارد.' ) ); ?>
			<?php MNS_UI::alert( array( 'tone' => 'danger', 'title' => 'خطا', 'text' => 'درخواست ثبت نشد.' ) ); ?>
		</div>
	</section>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'خالی / خطا / اسکلت', 'medyar-nursing-services' ); ?></h2>
		<?php MNS_UI::empty_state( array( 'cta_label' => MNS_Copy::t( 'nav.new' ), 'cta_url' => MNS_Pages::panel_url( 'patient', 'new-order' ) ) ); ?>
		<hr class="mns-ui-kit__hr" />
		<?php MNS_UI::error_state( array( 'retry_url' => MNS_Pages::panel_url( $panel, 'dashboard' ) ) ); ?>
		<hr class="mns-ui-kit__hr" />
		<?php MNS_UI::skeleton( array( 'variant' => 'list', 'rows' => 3 ) ); ?>
	</section>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'قیمت، پرستار، مهر، امتیاز', 'medyar-nursing-services' ); ?></h2>
		<?php
		MNS_UI::price_summary(
			array(
				'base'   => 450000,
				'lines'  => array(
					array( 'label' => MNS_Copy::t( 'price.extras' ), 'amount' => 80000 ),
					array( 'label' => MNS_Copy::t( 'price.discount' ), 'amount' => -50000, 'tone' => 'success' ),
				),
				'total'  => 480000,
				'note'   => MNS_Copy::t( 'price.travel' ),
				'urgent' => true,
			)
		);
		?>
		<div style="margin-top:var(--mns-space-5)">
			<?php
			MNS_UI::nurse_card(
				array(
					'nurse'     => array(
						'name'      => 'سارا احمدی',
						'specialty' => 'پرستار ارشد',
						'rating'    => 4.8,
						'reviews'   => 126,
						'meta'      => MNS_Copy::t( 'label.verified' ),
					),
					'show_call' => true,
				)
			);
			?>
		</div>
		<div style="margin-top:var(--mns-space-5)">
			<?php
			MNS_UI::stamp_card(
				array(
					'filled' => 3,
					'total'  => 5,
					'hint'   => 'دو مهر دیگر تا تخفیف بعدی',
					'reward' => 'پاداش: ۲۰٪ تخفیف خدمت بعدی',
				)
			);
			?>
		</div>
		<div style="margin-top:var(--mns-space-5)">
			<?php MNS_UI::star_rating( array( 'value' => 4.5 ) ); ?>
			<?php MNS_UI::star_rating( array( 'interactive' => true, 'value' => 0, 'name' => 'kit_rating' ) ); ?>
		</div>
	</section>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'پشتیبانی و دیالوگ', 'medyar-nursing-services' ); ?></h2>
		<div class="mns-actions-row">
			<?php MNS_UI::support_call( array( 'variant' => 'solid' ) ); ?>
			<?php MNS_UI::support_call( array( 'variant' => 'outline' ) ); ?>
			<button type="button" class="mns-outline-btn" data-mns-dialog-open="mns-kit-dialog"><?php echo esc_html__( 'باز کردن دیالوگ', 'medyar-nursing-services' ); ?></button>
		</div>
		<?php
		ob_start();
		echo '<p>' . esc_html__( 'این یک دیالوگ نمونه است؛ با Escape یا پس‌زمینه بسته می‌شود.', 'medyar-nursing-services' ) . '</p>';
		$body = ob_get_clean();
		ob_start();
		echo '<button type="button" class="mns-submit-btn mns-submit-btn--auto" data-mns-dialog-close>' . esc_html( MNS_Copy::t( 'action.close' ) ) . '</button>';
		$footer = ob_get_clean();
		MNS_UI::partial(
			'dialog',
			array(
				'id'          => 'mns-kit-dialog',
				'title'       => __( 'دیالوگ نمونه', 'medyar-nursing-services' ),
				'body_html'   => $body,
				'footer_html' => $footer,
				'variant'     => 'sheet',
			)
		);
		?>
	</section>

	<section class="mns-card">
		<h2 class="mns-card__title"><?php echo esc_html__( 'فرمت نمایش', 'medyar-nursing-services' ); ?></h2>
		<ul class="mns-kv">
			<li><span>تومان</span><strong><?php echo esc_html( MNS_Format::toman( 1250000 ) ); ?></strong></li>
			<li><span>موبایل</span><strong dir="ltr"><?php echo esc_html( MNS_Format::mobile( '09121234567' ) ); ?></strong></li>
			<li><span>تاریخ</span><strong><?php echo esc_html( MNS_Format::jdate( 'now' ) ); ?></strong></li>
			<li><span>انسانی</span><strong><?php echo esc_html( MNS_Format::human( MNS_Format::rel( '+1 day', '16:30' ) ) ); ?></strong></li>
		</ul>
	</section>
</section>
