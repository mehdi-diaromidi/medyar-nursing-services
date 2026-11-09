<?php
/**
 * Patient panel shell (ARP-aligned sidebar).
 *
 * @var WP_User $user
 * @var string  $page
 * @var string  $panel
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$nav      = MNS_Static_Fixtures::get( 'patient/nav' );
$profile  = MNS_Static_Fixtures::get( 'patient/profile' );
$dash     = MNS_Static_Fixtures::get( 'patient/dashboard' );
$account  = function_exists( 'wc_get_page_permalink' ) ? (string) wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$display  = isset( $profile['display_name'] ) ? (string) $profile['display_name'] : ( $user->display_name ?: __( 'کاربر', 'medyar-nursing-services' ) );
$phone    = isset( $profile['phone'] ) ? (string) $profile['phone'] : '';
$phone_ui = '' !== $phone ? MNS_Format::mobile( $phone ) : '';
$logout   = wp_logout_url( home_url( '/' ) );
$unpaid_n = isset( $dash['stats']['unpaid_invoices'] ) ? (int) $dash['stats']['unpaid_invoices'] : 0;
$initial  = function_exists( 'mb_substr' ) ? mb_substr( $display, 0, 1 ) : substr( $display, 0, 1 );

if ( ! is_array( $nav ) || empty( $nav ) ) {
	$nav = array(
		array( 'slug' => 'dashboard', 'label' => 'داشبورد', 'icon' => 'home' ),
		array( 'slug' => 'new-order', 'label' => 'ثبت سفارش', 'icon' => 'plus' ),
		array( 'slug' => 'orders', 'label' => 'سفارش‌های من', 'icon' => 'list' ),
		array( 'slug' => 'patients', 'label' => 'بیماران من', 'icon' => 'users' ),
		array( 'slug' => 'invoices', 'label' => 'فاکتورها و پرداخت‌ها', 'icon' => 'invoice' ),
		array( 'slug' => 'recurring', 'label' => 'خدمات دوره‌ای', 'icon' => 'calendar' ),
		array( 'slug' => 'loyalty', 'label' => 'کارت وفاداری و تخفیف', 'icon' => 'heart' ),
		array( 'slug' => 'surveys', 'label' => 'پرسشنامه‌ها', 'icon' => 'star' ),
	);
}

$show_bottom  = ! in_array( $page, array( 'new-order', 'ui-kit' ), true );
$nav_active   = MNS_UI::nav_active( 'patient', $page );
$header_icons = defined( 'DRPLUS_URI' )
	? DRPLUS_URI . '/assets/images/science-group-icons.svg'
	: get_theme_file_uri( 'assets/images/science-group-icons.svg' );
$user_id      = ( $user instanceof WP_User ) ? (int) $user->ID : 0;

$show_wallet = false;
$wallet_text = '';
$balance_num = null;
if ( class_exists( 'Wallet_Manager' ) && $user_id > 0 ) {
	$balance_num = (int) Wallet_Manager::get_instance()->get_balance( $user_id );
	$show_wallet = true;
} elseif ( class_exists( '\Sheyda\Wallet\Utils\Settings' ) && class_exists( 'SheydaWalletUtils' ) && $user_id > 0 ) {
	$wallet_settings = \Sheyda\Wallet\Utils\Settings::get_settings();
	if ( ! empty( $wallet_settings['enable'] ) ) {
		$user_balance = SheydaWalletUtils::get_user_balance();
		$balance_num  = absint( intval( $user_balance->balance ) - intval( $user_balance->locked ) );
		$show_wallet  = true;
	}
} elseif ( isset( $profile['wallet_balance'] ) ) {
	$balance_num = (int) $profile['wallet_balance'];
	$show_wallet = true;
}
if ( $show_wallet && null !== $balance_num ) {
	$balance_label = class_exists( '\DrPlus\Utils\Formatters' )
		? \DrPlus\Utils\Formatters::price( $balance_num )
		: number_format_i18n( $balance_num );
	$currency    = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : 'تومان';
	$wallet_text = trim( $balance_label . ' ' . $currency );
}

$arrow_icon_class = is_rtl() ? 'drplus-icon-square-arrow-right' : 'drplus-icon-square-arrow-left';

if ( 'orders' === $page ) {
	add_filter(
		'mns_dev_states',
		static function ( $states, $dev_panel, $dev_page ) {
			if ( 'patient' === $dev_panel && 'orders' === $dev_page ) {
				$states['filter-empty'] = __( 'فیلتر بی‌نتیجه', 'medyar-nursing-services' );
				$states['tab-empty']    = __( 'تب خالی', 'medyar-nursing-services' );
			}
			return $states;
		},
		10,
		3
	);
}
?>
<div class="mns-dash<?php echo $show_bottom ? ' mns-dash--has-bottom' : ''; ?>" dir="rtl" data-mns-panel="patient" data-mns-page="<?php echo esc_attr( $page ); ?>">
	<aside class="mns-dash__sidebar" data-mns-sidebar aria-label="<?php echo esc_attr__( 'منوی پنل بیمار', 'medyar-nursing-services' ); ?>">
		<div class="mns-dash__sidebar-header">
			<button type="button" class="mns-dash__sidebar-close" data-mns-sidebar-close aria-label="<?php echo esc_attr__( 'بستن منوی پنل بیمار', 'medyar-nursing-services' ); ?>">
				<span aria-hidden="true"><?php echo mns_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</button>
			<span class="mns-dash__role-badge"><?php echo esc_html__( 'پنل بیمار', 'medyar-nursing-services' ); ?></span>
			<div class="mns-dash__user-avatar" data-mns-avatar="side">
				<?php if ( $user_id > 0 ) : ?>
					<?php echo get_avatar( $user_id, 64 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<span class="mns-dash__user-avatar-fallback" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
			</div>
			<div class="mns-dash__user-name" data-mns-bind="display_name"><?php echo esc_html( $display ); ?></div>
			<?php if ( '' !== $phone_ui ) : ?>
				<div class="mns-dash__user-phone" data-mns-bind="mobile_display" dir="ltr"><?php echo esc_html( $phone_ui ); ?></div>
			<?php endif; ?>
			<?php if ( $show_wallet ) : ?>
				<div class="mns-dash__user-wallet">
					<span class="mns-dash__user-wallet-label"><?php echo esc_html__( 'موجودی شما:', 'medyar-nursing-services' ); ?></span>
					<span class="mns-dash__user-wallet-value"><?php echo esc_html( $wallet_text ); ?></span>
				</div>
			<?php endif; ?>
			<div class="mns-dash__sidebar-header-icons">
				<img src="<?php echo esc_url( $header_icons ); ?>" alt="" />
			</div>
		</div>

		<div class="mns-dash__sidebar-body">
			<nav class="mns-dash__nav" aria-label="<?php echo esc_attr__( 'منوی پنل بیمار', 'medyar-nursing-services' ); ?>">
				<ul>
					<?php foreach ( $nav as $item ) : ?>
						<?php
						$slug   = isset( $item['slug'] ) ? (string) $item['slug'] : '';
						$label  = isset( $item['label'] ) ? (string) $item['label'] : '';
						$icon   = isset( $item['icon'] ) ? (string) $item['icon'] : 'list';
						$active = MNS_UI::nav_item_is_active( 'patient', $page, $slug );
						$url    = MNS_Pages::panel_url( 'patient', $slug );
						$badge  = ( 'invoices' === $slug && $unpaid_n > 0 ) ? MNS_Format::digits( (string) $unpaid_n ) : '';
						?>
						<li class="<?php echo $active ? 'is-active' : ''; ?>">
							<a
								href="<?php echo esc_url( $url ); ?>"
								title="<?php echo esc_attr( $label ); ?>"
								<?php echo $active ? ' aria-current="page"' : ''; ?>
							>
								<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="mns-dash__nav-label">
									<?php echo esc_html( $label ); ?>
									<?php if ( '' !== $badge ) : ?>
										<span class="mns-dash__nav-badge"><?php echo esc_html( $badge ); ?></span>
									<?php endif; ?>
								</span>
								<i class="mns-dash__nav-arrow <?php echo esc_attr( $arrow_icon_class ); ?>" aria-hidden="true"></i>
							</a>
						</li>
					<?php endforeach; ?>
					<li>
						<a href="<?php echo esc_url( $account ); ?>" title="<?php echo esc_attr__( 'پنل مدیار (حساب کاربری)', 'medyar-nursing-services' ); ?>" target="_self">
							<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mns-dash__nav-label"><?php echo esc_html__( 'پنل مدیار', 'medyar-nursing-services' ); ?></span>
							<i class="mns-dash__nav-arrow <?php echo esc_attr( $arrow_icon_class ); ?>" aria-hidden="true"></i>
						</a>
					</li>
					<li class="mns-dash__nav-logout">
						<a href="<?php echo esc_url( $logout ); ?>" title="<?php echo esc_attr__( 'خروج از حساب', 'medyar-nursing-services' ); ?>">
							<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( 'logout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mns-dash__nav-label"><?php echo esc_html__( 'خروج از حساب', 'medyar-nursing-services' ); ?></span>
							<i class="mns-dash__nav-arrow <?php echo esc_attr( $arrow_icon_class ); ?>" aria-hidden="true"></i>
						</a>
					</li>
				</ul>
			</nav>
		</div>
	</aside>

	<main class="mns-dash__main" id="mns-main">
		<section class="mns-mobile-profile" aria-label="<?php echo esc_attr__( 'پروفایل', 'medyar-nursing-services' ); ?>">
			<span class="mns-mobile-profile__badge"><?php echo esc_html__( 'پنل بیمار', 'medyar-nursing-services' ); ?></span>
			<div class="mns-mobile-profile__row">
				<span class="mns-mobile-profile__avatar" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<div class="mns-mobile-profile__text">
					<strong class="mns-mobile-profile__name"><?php echo esc_html( $display ); ?></strong>
					<?php if ( '' !== $phone_ui ) : ?>
						<span class="mns-mobile-profile__phone" dir="ltr"><?php echo esc_html( $phone_ui ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
		if ( MNS_Dev::is( 'loading' ) ) {
			MNS_UI::skeleton( array( 'variant' => 'orders' === $page ? 'card' : 'detail', 'rows' => 4 ) );
		} elseif ( MNS_Dev::is( 'error' ) ) {
			MNS_UI::error_state();
		} else {
			$body = MNS_PLUGIN_DIR . 'public/views/patient/page-' . $page . '.php';
			if ( ! is_readable( $body ) ) {
				$body = MNS_PLUGIN_DIR . 'public/views/patient/page-not-found.php';
			}
			if ( is_readable( $body ) ) {
				require $body;
			}
		}
		?>
	</main>
	<?php if ( $show_bottom ) : ?>
		<?php
		MNS_UI::bottom_nav(
			'patient',
			$nav_active,
			array(),
			array(
				'invoices' => $unpaid_n,
			)
		);
		?>
	<?php endif; ?>
</div>
<button type="button" class="mns-dash__sidebar-mobile-expand" data-mns-sidebar-expand aria-expanded="false" aria-label="<?php echo esc_attr__( 'باز کردن منوی پنل بیمار', 'medyar-nursing-services' ); ?>">
	<i class="drplus-icon-double-arrow-<?php echo is_rtl() ? 'left' : 'right'; ?>" aria-hidden="true"></i>
</button>
<div class="mns-dash__backdrop" data-mns-sidebar-backdrop aria-hidden="true"></div>
<?php
unset( $initial );
