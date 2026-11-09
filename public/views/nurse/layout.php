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

$show_bottom    = ! in_array( $page, array( 'order-view', 'ui-kit' ), true );
$nav_active     = MNS_UI::nav_active( 'nurse', $page );
$settlement_url = class_exists( 'MTW_MyAccount_UI' )
	? MTW_MyAccount_UI::section_url( 'withdrawal' )
	: ( trailingslashit( $account ) . 'wallet/?section=withdrawal' );
?>
<div class="mns-dash<?php echo $show_bottom ? ' mns-dash--has-bottom' : ''; ?>" dir="rtl" data-mns-panel="nurse" data-mns-page="<?php echo esc_attr( $page ); ?>">
	<aside class="mns-dash__sidebar" data-mns-sidebar>
		<div class="mns-dash__sidebar-header">
			<span class="mns-dash__badge"><?php echo esc_html__( 'پنل پرستار', 'medyar-nursing-services' ); ?></span>
			<p class="mns-dash__name"><?php echo esc_html( $display ); ?></p>
			<?php if ( '' !== $phone_ui ) : ?>
				<p class="mns-dash__phone" dir="ltr"><?php echo esc_html( $phone_ui ); ?></p>
			<?php endif; ?>
		</div>
		<nav class="mns-dash__nav" aria-label="<?php echo esc_attr__( 'منوی پنل پرستار', 'medyar-nursing-services' ); ?>">
			<?php foreach ( $nav as $item ) : ?>
				<?php
				$slug   = isset( $item['slug'] ) ? (string) $item['slug'] : '';
				$label  = isset( $item['label'] ) ? (string) $item['label'] : '';
				$icon   = isset( $item['icon'] ) ? (string) $item['icon'] : 'list';
				$active = MNS_UI::nav_item_is_active( 'nurse', $page, $slug );
				$url    = MNS_Pages::panel_url( 'nurse', $slug );
				?>
				<a
					class="mns-dash__nav-link<?php echo $active ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $url ); ?>"
					title="<?php echo esc_attr( $label ); ?>"
					<?php echo $active ? ' aria-current="page"' : ''; ?>
				>
					<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><?php echo esc_html( $label ); ?></span>
				</a>
			<?php endforeach; ?>
			<div class="mns-dash__nav-sep" role="presentation"></div>
			<a class="mns-dash__nav-link mns-dash__nav-link--external" href="<?php echo esc_url( $settlement_url ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( 'wallet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'برداشت کیف پول', 'medyar-nursing-services' ); ?></span>
				<?php echo MNS_UI::external_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="screen-reader-text"><?php echo esc_html( MNS_Copy::t( 'label.external' ) ); ?></span>
			</a>
			<a class="mns-dash__nav-link mns-dash__nav-link--external" href="<?php echo esc_url( $account ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'پنل مدیار', 'medyar-nursing-services' ); ?></span>
				<?php echo MNS_UI::external_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="screen-reader-text"><?php echo esc_html( MNS_Copy::t( 'label.external' ) ); ?></span>
			</a>
			<a class="mns-dash__nav-link mns-dash__nav-link--logout" href="<?php echo esc_url( $logout ); ?>">
				<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( 'logout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'خروج از حساب', 'medyar-nursing-services' ); ?></span>
			</a>
		</nav>
	</aside>

	<main class="mns-dash__main" id="mns-main">
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
<button type="button" class="mns-dash__sidebar-mobile-expand" data-mns-sidebar-expand aria-expanded="false" aria-label="<?php echo esc_attr( MNS_Copy::t( 'action.open_menu' ) ); ?>" hidden></button>
<div class="mns-dash__backdrop" data-mns-sidebar-backdrop hidden></div>
