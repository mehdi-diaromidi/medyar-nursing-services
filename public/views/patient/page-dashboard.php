<?php
/**
 * Patient dashboard: what needs me now, where my order is, how to order again.
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

$new_url     = MNS_Pages::panel_url( 'patient', 'new-order' );
$action_type = (string) ( $action['type'] ?? '' );

/**
 * One upcoming order as a list row.
 *
 * @param array<string,mixed> $row Order row.
 */
$render_order = static function ( array $row ): void {
	ob_start();
	MNS_UI::status_badge( (string) ( $row['status'] ?? 'submitted' ) );
	$badge = (string) ob_get_clean();
	$meta  = implode(
		' · ',
		array_filter(
			array(
				MNS_Format::human( (string) ( $row['when'] ?? '' ) ),
				(string) ( $row['patient'] ?? '' ),
			)
		)
	);
	$meta2 = ! empty( $row['nurse'] )
		? sprintf( /* translators: nurse */ __( 'پرستار: %s', 'medyar-nursing-services' ), (string) $row['nurse'] )
		: '';
	$icon_map = array(
		'سرم‌تراپی'     => 'droplet',
		'پانسمان و زخم' => 'bandage',
		'تزریقات'       => 'pill',
	);
	$svc = (string) ( $row['service'] ?? '' );
	MNS_UI::list_item(
		array(
			'url'        => (string) ( $row['url'] ?? '#' ),
			'title'      => $svc,
			'meta'       => $meta,
			'meta2'      => $meta2,
			'code'       => (string) ( $row['tracking'] ?? '' ),
			'badge_html' => $badge,
			'icon'       => $icon_map[ $svc ] ?? 'list',
		)
	);
};

/**
 * "Where is my order?" The nearest order carries the 5-stage track; the rest are plain rows.
 */
$render_upcoming = static function () use ( $upcoming, $new_url, $render_order ): void {
	?>
	<section class="mns-card mns-pdash__up" aria-labelledby="mns-up-title">
		<?php
		MNS_UI::section_head(
			array(
				'title'      => __( 'سفارش‌های پیش‌رو', 'medyar-nursing-services' ),
				'title_id'   => 'mns-up-title',
				'link_label' => empty( $upcoming ) ? '' : MNS_Copy::t( 'action.view_all' ),
				'link_url'   => empty( $upcoming ) ? '' : MNS_Pages::panel_url( 'patient', 'orders' ),
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
			<?php
			$next = array_shift( $upcoming );
			?>
			<div class="mns-pdash__next">
				<ul class="mns-list mns-list--flush">
					<?php $render_order( $next ); ?>
				</ul>
				<div class="mns-pdash__track">
					<?php MNS_UI::order_timeline( (string) ( $next['status'] ?? 'submitted' ), array( 'variant' => 'compact' ) ); ?>
				</div>
			</div>
			<?php if ( ! empty( $upcoming ) ) : ?>
				<ul class="mns-list mns-list--flush">
					<?php
					foreach ( $upcoming as $row ) {
						$render_order( $row );
					}
					?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	</section>
	<?php
};

/**
 * "How do I order again?" Repeat shortcut + service categories.
 */
$render_quick = static function () use ( $last, $categories, $new_url ): void {
	?>
	<section class="mns-card mns-card--quick" aria-labelledby="mns-quick-title">
		<?php
		MNS_UI::section_head(
			array(
				'title'    => __( 'ثبت سریع سفارش', 'medyar-nursing-services' ),
				'title_id' => 'mns-quick-title',
			)
		);
		?>
		<?php if ( ! empty( $last['slug'] ) ) : ?>
			<?php
			$repeat_label = (string) ( $last['label'] ?? __( 'آخرین خدمت', 'medyar-nursing-services' ) );
			if ( 0 !== strpos( $repeat_label, 'تکرار' ) ) {
				$repeat_label = sprintf(
					/* translators: service name */
					__( 'تکرار %s', 'medyar-nursing-services' ),
					$repeat_label
				);
			}
			$repeat_sub = ! empty( $last['patient'] )
				? sprintf(
					/* translators: patient name */
					__( 'برای %s · اطلاعات از قبل پر می‌شود', 'medyar-nursing-services' ),
					(string) $last['patient']
				)
				: __( 'اطلاعات از قبل پر می‌شود', 'medyar-nursing-services' );
			?>
			<a class="mns-repeat-tile" href="<?php echo esc_url( add_query_arg( 'mns_service', (string) $last['slug'], $new_url ) ); ?>">
				<span class="mns-repeat-tile__icon" aria-hidden="true"><?php echo mns_icon( 'repeat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="mns-repeat-tile__text">
					<strong><?php echo esc_html( $repeat_label ); ?></strong>
					<small><?php echo esc_html( $repeat_sub ); ?></small>
				</span>
				<span class="mns-repeat-tile__go" aria-hidden="true"><?php echo mns_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
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
	<?php
};
?>
<div class="mns-pdash">
	<?php
	MNS_UI::page_header(
		array(
			'title' => __( 'داشبورد', 'medyar-nursing-services' ),
			'icon'  => 'home',
		)
	);

	if ( ! empty( $action ) ) {
		$has_amount = isset( $action['amount'] ) && is_numeric( $action['amount'] );
		$cta        = (string) ( $action['cta_label'] ?? '' );
		if ( $has_amount && ( '' === $cta || false === strpos( $cta, 'تومان' ) ) ) {
			$cta = sprintf(
				/* translators: %s: amount */
				__( 'پرداخت %s', 'medyar-nursing-services' ),
				MNS_Format::toman( $action['amount'] )
			);
		}
		$context = (string) ( $action['context'] ?? '' );
		/* The button already carries the amount and the context line says which order: no second amount, no filler sentence. */
		MNS_UI::alert_card(
			array(
				'tone'      => '' !== $action_type ? $action_type : 'info',
				'title'     => (string) ( $action['title'] ?? '' ),
				'text'      => '' !== $context ? '' : (string) ( $action['text'] ?? '' ),
				'context'   => $context,
				'cta_label' => $cta,
				'cta_url'   => (string) ( $action['cta_url'] ?? '' ),
				'icon'      => (string) ( $action['icon'] ?? 'alert' ),
			)
		);
	}

	/* Order of the three questions: what waits for me, where is my order, how do I order again. */
	if ( ! empty( $upcoming ) ) {
		$render_upcoming();
		$render_quick();
	} else {
		$render_quick();
		$render_upcoming();
	}
	?>

	<?php if ( ! empty( $loyalty ) ) : ?>
		<?php
		MNS_UI::stamp_card(
			array(
				'filled'     => (int) ( $loyalty['filled'] ?? 0 ),
				'total'      => (int) ( $loyalty['total'] ?? 5 ),
				'title'      => MNS_Copy::t( 'label.loyalty' ),
				'hint'       => (string) ( $loyalty['hint'] ?? '' ),
				'link_url'   => MNS_Pages::panel_url( 'patient', 'loyalty' ),
				'link_label' => __( 'جزئیات کارت و کدهای تخفیف', 'medyar-nursing-services' ),
			)
		);
		?>
	<?php endif; ?>

	<?php
	$unpaid_n = (int) ( $stats['unpaid_invoices'] ?? 0 );
	$pat_n    = (int) ( $stats['patients'] ?? 0 );
	/* A single unpaid invoice is already the alert card at the top; repeat it here only when there are more. */
	if ( 'unpaid' === $action_type && $unpaid_n <= 1 ) {
		$unpaid_n = 0;
	}
	if ( $unpaid_n > 0 || $pat_n > 0 ) :
		?>
		<section class="mns-stats-row<?php echo ( $unpaid_n > 0 && $pat_n > 0 ) ? '' : ' mns-stats-row--single'; ?>" aria-label="<?php echo esc_attr__( 'خلاصهٔ حساب', 'medyar-nursing-services' ); ?>">
			<?php if ( $unpaid_n > 0 ) : ?>
				<a class="mns-stat-card" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'invoices' ) ); ?>">
					<span class="mns-stat-card__icon mns-stat-card__icon--warn" aria-hidden="true"><?php echo mns_icon( 'invoice' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="mns-stat-card__value"><?php echo esc_html( MNS_Format::digits( (string) $unpaid_n ) ); ?></span>
					<span class="mns-stat-card__label"><?php echo esc_html__( 'فاکتور پرداخت‌نشده', 'medyar-nursing-services' ); ?></span>
					<span class="mns-stat-card__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $pat_n > 0 ) : ?>
				<a class="mns-stat-card" href="<?php echo esc_url( MNS_Pages::panel_url( 'patient', 'patients' ) ); ?>">
					<span class="mns-stat-card__icon" aria-hidden="true"><?php echo mns_icon( 'users' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="mns-stat-card__value"><?php echo esc_html( MNS_Format::digits( (string) $pat_n ) ); ?></span>
					<span class="mns-stat-card__label"><?php echo esc_html__( 'بیمار ذخیره‌شده', 'medyar-nursing-services' ); ?></span>
					<span class="mns-stat-card__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			<?php endif; ?>
		</section>
	<?php endif; ?>
</div>
