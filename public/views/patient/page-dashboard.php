<?php
/**
 * Patient dashboard — priority action + shortcuts (UX Phase C).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$data     = MNS_Static_Fixtures::get( 'patient/dashboard' );
$variants = isset( $data['variants'] ) && is_array( $data['variants'] ) ? $data['variants'] : array();
$action   = isset( $data['primary_action'] ) && is_array( $data['primary_action'] ) ? $data['primary_action'] : array();

$state = MNS_Dev::state();
if ( isset( $variants[ $state ] ) && is_array( $variants[ $state ] ) ) {
	$action = $variants[ $state ];
} elseif ( MNS_Dev::is( 'empty' ) && isset( $variants['new_user'] ) ) {
	$action = $variants['new_user'];
}

$categories = isset( $data['categories'] ) && is_array( $data['categories'] ) ? $data['categories'] : array();
$upcoming   = isset( $data['upcoming'] ) && is_array( $data['upcoming'] ) ? $data['upcoming'] : array();
$loyalty    = isset( $data['loyalty'] ) && is_array( $data['loyalty'] ) ? $data['loyalty'] : array();
$last       = isset( $data['last_service'] ) && is_array( $data['last_service'] ) ? $data['last_service'] : array();
$stats      = isset( $data['stats'] ) && is_array( $data['stats'] ) ? $data['stats'] : array();

if ( MNS_Dev::is( 'empty' ) || ( isset( $action['type'] ) && 'new_user' === $action['type'] ) ) {
	$upcoming = array();
}

$new_url = MNS_Pages::panel_url( 'patient', 'new-order' );

MNS_UI::page_header(
	array(
		'title' => __( 'داشبورد', 'medyar-nursing-services' ),
		'lead'  => __( 'الان چه خبر است و قدم بعدی چیست؟', 'medyar-nursing-services' ),
	)
);

if ( ! empty( $action ) ) {
	$amount_html = '';
	if ( isset( $action['amount'] ) && is_numeric( $action['amount'] ) ) {
		$amount_html = '<strong>' . esc_html( MNS_Format::toman( $action['amount'] ) ) . '</strong>';
	}
	$cta = (string) ( $action['cta_label'] ?? '' );
	if ( '' !== $cta && isset( $action['amount'] ) && is_numeric( $action['amount'] ) && false === strpos( $cta, 'تومان' ) ) {
		$cta = sprintf(
			/* translators: %s: amount */
			__( 'پرداخت %s', 'medyar-nursing-services' ),
			MNS_Format::toman( $action['amount'] )
		);
	}
	MNS_UI::alert_card(
		array(
			'tone'        => (string) ( $action['type'] ?? 'info' ),
			'title'       => (string) ( $action['title'] ?? '' ),
			'text'        => (string) ( $action['text'] ?? '' ),
			'context'     => (string) ( $action['context'] ?? '' ),
			'amount_html' => $amount_html,
			'cta_label'   => $cta,
			'cta_url'     => (string) ( $action['cta_url'] ?? '' ),
			'icon'        => (string) ( $action['icon'] ?? 'alert' ),
		)
	);
}
?>

<section class="mns-card" aria-labelledby="mns-quick-title">
	<?php
	MNS_UI::section_head(
		array(
			'title'    => __( 'ثبت سریع سفارش', 'medyar-nursing-services' ),
			'title_id' => 'mns-quick-title',
		)
	);
	?>
	<?php if ( ! empty( $last['slug'] ) ) : ?>
		<a class="mns-repeat-tile" href="<?php echo esc_url( add_query_arg( 'mns_service', (string) $last['slug'], $new_url ) ); ?>">
			<span class="mns-repeat-tile__icon" aria-hidden="true"><?php echo mns_icon( 'repeat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="mns-repeat-tile__text">
				<strong><?php echo esc_html( (string) ( $last['label'] ?? __( 'تکرار آخرین خدمت', 'medyar-nursing-services' ) ) ); ?></strong>
				<?php if ( ! empty( $last['patient'] ) ) : ?>
					<small><?php echo esc_html( sprintf( /* translators: patient */ __( 'برای %s', 'medyar-nursing-services' ), (string) $last['patient'] ) ); ?></small>
				<?php endif; ?>
			</span>
			<span class="mns-list-item__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</a>
	<?php endif; ?>
	<div class="mns-quick-grid">
		<?php foreach ( $categories as $cat ) : ?>
			<?php $url = add_query_arg( 'mns_service', (string) ( $cat['slug'] ?? '' ), $new_url ); ?>
			<a class="mns-quick-chip" href="<?php echo esc_url( $url ); ?>">
				<span class="mns-quick-chip__icon" aria-hidden="true"><?php echo mns_icon( (string) ( $cat['icon'] ?? 'list' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html( (string) ( $cat['label'] ?? '' ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<section class="mns-card" aria-labelledby="mns-up-title">
	<?php
	MNS_UI::section_head(
		array(
			'title'      => __( 'سفارش‌های پیش‌رو', 'medyar-nursing-services' ),
			'title_id'   => 'mns-up-title',
			'link_label' => MNS_Copy::t( 'action.view_all' ),
			'link_url'   => MNS_Pages::panel_url( 'patient', 'orders' ),
		)
	);
	?>
	<?php if ( empty( $upcoming ) ) : ?>
		<?php
		MNS_UI::empty_state(
			array(
				'title'     => __( 'سفارش پیش‌رویی نیست', 'medyar-nursing-services' ),
				'text'      => __( 'وقتی سفارشی ثبت کنید، اینجا نمایش داده می‌شود.', 'medyar-nursing-services' ),
				'cta_label' => __( 'ثبت سفارش', 'medyar-nursing-services' ),
				'cta_url'   => $new_url,
				'icon'      => 'calendar',
			)
		);
		?>
	<?php else : ?>
		<ul class="mns-list">
			<?php foreach ( $upcoming as $row ) : ?>
				<?php
				ob_start();
				MNS_UI::status_badge( (string) ( $row['status'] ?? 'submitted' ) );
				$badge = (string) ob_get_clean();
				$meta  = MNS_Format::human( (string) ( $row['when'] ?? '' ) ) . ' · ' . (string) ( $row['patient'] ?? '' );
				$meta2 = ! empty( $row['nurse'] )
					? sprintf( /* translators: nurse */ __( 'پرستار: %s', 'medyar-nursing-services' ), (string) $row['nurse'] )
					: '';
				MNS_UI::list_item(
					array(
						'url'        => (string) ( $row['url'] ?? '#' ),
						'title'      => (string) ( $row['service'] ?? '' ),
						'meta'       => $meta,
						'meta2'      => $meta2,
						'code'       => (string) ( $row['tracking'] ?? '' ),
						'badge_html' => $badge,
					)
				);
				?>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>

<?php if ( ! empty( $loyalty ) ) : ?>
	<?php
	MNS_UI::stamp_card(
		array(
			'filled' => (int) ( $loyalty['filled'] ?? 0 ),
			'total'  => (int) ( $loyalty['total'] ?? 5 ),
			'title'  => MNS_Copy::t( 'label.loyalty' ),
			'hint'   => (string) ( $loyalty['hint'] ?? '' ),
		)
	);
	?>
	<p class="mns-actions-row mns-actions-row--tight">
		<a class="mns-link-btn" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'loyalty' ) ); ?>">
			<?php echo esc_html__( 'جزئیات کارت و کدهای تخفیف', 'medyar-nursing-services' ); ?>
		</a>
	</p>
<?php endif; ?>

<?php
$unpaid_n = (int) ( $stats['unpaid_invoices'] ?? 0 );
$pat_n    = (int) ( $stats['patients'] ?? 0 );
if ( $unpaid_n > 0 || $pat_n > 0 ) :
	?>
	<section class="mns-card mns-context-row" aria-label="<?php echo esc_attr__( 'خلاصهٔ حساب', 'medyar-nursing-services' ); ?>">
		<?php if ( $unpaid_n > 0 ) : ?>
			<a class="mns-context-link" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'invoices' ) ); ?>">
				<strong><?php echo esc_html( sprintf( /* translators: count */ _n( '%s فاکتور پرداخت‌نشده', '%s فاکتور پرداخت‌نشده', $unpaid_n, 'medyar-nursing-services' ), MNS_Format::digits( (string) $unpaid_n ) ) ); ?></strong>
				<small><?php echo esc_html__( 'فاکتورها و پرداخت‌ها', 'medyar-nursing-services' ); ?></small>
			</a>
		<?php endif; ?>
		<?php if ( $pat_n > 0 ) : ?>
			<a class="mns-context-link" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'patients' ) ); ?>">
				<strong><?php echo esc_html( sprintf( /* translators: count */ _n( '%s بیمار ذخیره‌شده', '%s بیمار ذخیره‌شده', $pat_n, 'medyar-nursing-services' ), MNS_Format::digits( (string) $pat_n ) ) ); ?></strong>
				<small><?php echo esc_html__( 'بیماران من', 'medyar-nursing-services' ); ?></small>
			</a>
		<?php endif; ?>
	</section>
<?php endif; ?>
