<?php
/**
 * Patient panel shell.
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

require_once MNS_PLUGIN_DIR . 'public/views/partials/icons.php';

$nav      = MNS_Static_Fixtures::get( 'patient/nav' );
$profile  = MNS_Static_Fixtures::get( 'patient/profile' );
$account  = function_exists( 'wc_get_page_permalink' ) ? (string) wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$display  = isset( $profile['display_name'] ) ? (string) $profile['display_name'] : ( $user->display_name ?: __( 'کاربر', 'medyar-nursing-services' ) );
$phone    = isset( $profile['phone'] ) ? (string) $profile['phone'] : '';
$logout   = wp_logout_url( home_url( '/' ) );

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
?>
<div class="mns-dash" dir="rtl" data-mns-panel="patient" data-mns-page="<?php echo esc_attr( $page ); ?>">
	<aside class="mns-dash__sidebar" data-mns-sidebar>
		<div class="mns-dash__sidebar-header">
			<span class="mns-dash__badge"><?php echo esc_html__( 'پنل بیمار', 'medyar-nursing-services' ); ?></span>
			<p class="mns-dash__name" data-mns-bind="display_name"><?php echo esc_html( $display ); ?></p>
			<?php if ( '' !== $phone ) : ?>
				<p class="mns-dash__phone" data-mns-bind="mobile_display"><?php echo esc_html( $phone ); ?></p>
			<?php endif; ?>
		</div>
		<nav class="mns-dash__nav" aria-label="<?php echo esc_attr__( 'منوی پنل بیمار', 'medyar-nursing-services' ); ?>">
			<?php foreach ( $nav as $item ) : ?>
				<?php
				$slug   = isset( $item['slug'] ) ? (string) $item['slug'] : '';
				$label  = isset( $item['label'] ) ? (string) $item['label'] : '';
				$icon   = isset( $item['icon'] ) ? (string) $item['icon'] : 'list';
				$active = ( $slug === $page );
				$url    = MNS_Pages::panel_url( 'patient', $slug );
				?>
				<a
					class="mns-dash__nav-link<?php echo $active ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( $url ); ?>"
					title="<?php echo esc_attr( $label ); ?>"
					<?php echo $active ? ' aria-current="page"' : ''; ?>
				>
					<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span>
					<span><?php echo esc_html( $label ); ?></span>
				</a>
			<?php endforeach; ?>
			<a class="mns-dash__nav-link" href="<?php echo esc_url( $account ); ?>" title="<?php echo esc_attr__( 'پنل مدیار', 'medyar-nursing-services' ); ?>">
				<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'پنل مدیار', 'medyar-nursing-services' ); ?></span>
			</a>
			<a class="mns-dash__nav-link mns-dash__nav-link--logout" href="<?php echo esc_url( $logout ); ?>" title="<?php echo esc_attr__( 'خروج از حساب', 'medyar-nursing-services' ); ?>">
				<span class="mns-dash__nav-icon" aria-hidden="true"><?php echo mns_icon( 'logout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php echo esc_html__( 'خروج از حساب', 'medyar-nursing-services' ); ?></span>
			</a>
		</nav>
	</aside>

	<main class="mns-dash__main" id="mns-main">
		<?php
		$body = MNS_PLUGIN_DIR . 'public/views/patient/page-' . $page . '.php';
		if ( ! is_readable( $body ) ) {
			$body = MNS_PLUGIN_DIR . 'public/views/patient/page-dashboard.php';
		}
		if ( is_readable( $body ) ) {
			require $body;
		}
		?>
	</main>
</div>
<button type="button" class="mns-dash__sidebar-mobile-expand" data-mns-sidebar-expand aria-expanded="false" aria-label="<?php echo esc_attr__( 'باز کردن منو', 'medyar-nursing-services' ); ?>" hidden></button>
<div class="mns-dash__backdrop" data-mns-sidebar-backdrop hidden></div>
