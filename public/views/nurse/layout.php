<?php
/**
 * Nurse panel shell.
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

$nav      = MNS_Static_Fixtures::get( 'nurse/nav' );
$profile  = MNS_Static_Fixtures::get( 'nurse/profile' );
$account  = function_exists( 'wc_get_page_permalink' ) ? (string) wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$display  = isset( $profile['display_name'] ) ? (string) $profile['display_name'] : ( $user->display_name ?: __( 'پرستار', 'medyar-nursing-services' ) );
$phone    = isset( $profile['phone'] ) ? (string) $profile['phone'] : '';
$phone_ui = '' !== $phone ? MNS_Format::mobile( $phone ) : '';
$logout   = wp_logout_url( home_url( '/' ) );

if ( empty( $nav ) ) {
	$nav = array(
		array( 'slug' => 'dashboard', 'label' => 'داشبورد', 'icon' => 'home' ),
		array( 'slug' => 'orders', 'label' => 'سفارش‌های من', 'icon' => 'list' ),
		array( 'slug' => 'availability', 'label' => 'برنامه و دسترسی', 'icon' => 'clock' ),
		array( 'slug' => 'ratings', 'label' => 'امتیازهای من', 'icon' => 'star' ),
		array( 'slug' => 'profile', 'label' => 'پروفایل', 'icon' => 'user' ),
	);
}

$arrow_icon_class = is_rtl() ? 'drplus-icon-square-arrow-right' : 'drplus-icon-square-arrow-left';
$header_icons     = defined( 'DRPLUS_URI' )
	? DRPLUS_URI . '/assets/images/science-group-icons.svg'
	: get_theme_file_uri( 'assets/images/science-group-icons.svg' );
$show_bottom    = ! in_array( $page, array( 'order-view', 'checklist', 'report', 'drug', 'ui-kit' ), true );
$nav_active     = MNS_UI::nav_active( 'nurse', $page );
$settlement_url = class_exists( 'MTW_MyAccount_UI' )
	? MTW_MyAccount_UI::section_url( 'withdrawal' )
	: ( trailingslashit( $account ) . 'wallet/?section=withdrawal' );
?>
<div class="mns-dash<?php echo $show_bottom ? ' mns-dash--has-bottom' : ''; ?>" dir="rtl" data-mns-panel="nurse" data-mns-page="<?php echo esc_attr( $page ); ?>">
	<aside class="mns-dash__sidebar" data-mns-sidebar aria-label="<?php echo esc_attr__( 'منوی پنل پرستار', 'medyar-nursing-services' ); ?>">
		<div class="mns-dash__sidebar-header">
			<button type="button" class="mns-dash__sidebar-close" data-mns-sidebar-close aria-label="<?php echo esc_attr__( 'بستن منوی پنل پرستار', 'medyar-nursing-services' ); ?>">
				<span aria-hidden="true"><?php echo mns_icon( 'x' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</button>
			<span class="mns-dash__role-badge"><?php echo esc_html__( 'پنل پرستار', 'medyar-nursing-services' ); ?></span>
			<div class="mns-dash__user-avatar" data-mns-avatar="side">
				<span class="mns-dash__user-avatar-fallback" aria-hidden="true"><?php echo mns_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</div>
			<div class="mns-dash__user-name"><?php echo esc_html( $display ); ?></div>
			<?php if ( '' !== $phone_ui ) : ?>
				<div class="mns-dash__user-phone" dir="ltr"><?php echo esc_html( $phone_ui ); ?></div>
			<?php endif; ?>
			<div class="mns-dash__sidebar-header-icons">
				<img src="<?php echo esc_url( $header_icons ); ?>" alt="" />
			</div>
		</div>

		<div class="mns-dash__sidebar-body">
			<nav class="mns-dash__nav" aria-label="<?php echo esc_attr__( 'منوی پنل پرستار', 'medyar-nursing-services' ); ?>">
				<ul>
					<?php foreach ( $nav as $item ) : ?>
						<?php
						$slug   = isset( $item['slug'] ) ? (string) $item['slug'] : '';
						$label  = isset( $item['label'] ) ? (string) $item['label'] : '';
						$icon   = isset( $item['icon'] ) ? (string) $item['icon'] : 'list';
						$active = MNS_UI::nav_item_is_active( 'nurse', $page, $slug );
						$url    = MNS_Pages::panel_url( 'nurse', $slug );
						?>
						<li class="<?php echo $active ? 'is-active' : ''; ?>">
							<a href="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( $label ); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
								<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="mns-dash__nav-label"><?php echo esc_html( $label ); ?></span>
								<i class="mns-dash__nav-arrow <?php echo esc_attr( $arrow_icon_class ); ?>" aria-hidden="true"></i>
							</a>
						</li>
					<?php endforeach; ?>
					<li>
						<a href="<?php echo esc_url( $settlement_url ); ?>" title="<?php echo esc_attr__( 'برداشت کیف پول', 'medyar-nursing-services' ); ?>" target="_blank" rel="noopener noreferrer">
							<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( 'wallet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mns-dash__nav-label"><?php echo esc_html__( 'برداشت کیف پول', 'medyar-nursing-services' ); ?></span>
							<i class="mns-dash__nav-arrow <?php echo esc_attr( $arrow_icon_class ); ?>" aria-hidden="true"></i>
							<span class="screen-reader-text"><?php echo esc_html( MNS_Copy::t( 'label.external' ) ); ?></span>
						</a>
					</li>
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
			<span class="mns-mobile-profile__badge"><?php echo esc_html__( 'پنل پرستار', 'medyar-nursing-services' ); ?></span>
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
			MNS_UI::skeleton( array( 'variant' => 'list', 'rows' => 4 ) );
		} elseif ( MNS_Dev::is( 'error' ) ) {
			MNS_UI::error_state();
		} else {
			$body = MNS_PLUGIN_DIR . 'public/views/nurse/page-' . $page . '.php';
			if ( ! is_readable( $body ) ) {
				$body = MNS_PLUGIN_DIR . 'public/views/nurse/page-not-found.php';
			}
			if ( is_readable( $body ) ) {
				require $body;
			}
		}
		?>
	</main>
	<?php if ( $show_bottom ) : ?>
		<?php MNS_UI::bottom_nav( 'nurse', $nav_active ); ?>
	<?php endif; ?>
</div>
<button type="button" class="mns-dash__sidebar-mobile-expand" data-mns-sidebar-expand aria-expanded="false" aria-label="<?php echo esc_attr__( 'باز کردن منوی پنل پرستار', 'medyar-nursing-services' ); ?>">
	<i class="drplus-icon-double-arrow-<?php echo is_rtl() ? 'left' : 'right'; ?>" aria-hidden="true"></i>
</button>
<div class="mns-dash__backdrop" data-mns-sidebar-backdrop aria-hidden="true"></div>
