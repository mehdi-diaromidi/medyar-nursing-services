<?php
/**
 * Admin menu registration (Phase 6 hub + section stubs).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Admin_Menu {

	public const SLUG_DASHBOARD = 'mns-dashboard';
	public const SLUG_MONITOR   = 'mns-monitor';
	public const SLUG_ASSIGN    = 'mns-assign';
	public const SLUG_NURSES    = 'mns-nurses';
	public const SLUG_MANUAL    = 'mns-manual-order';
	public const SLUG_FINANCE   = 'mns-finance';
	public const SLUG_SETTINGS  = 'mns-settings';

	/** @var self|null */
	private static $instance = null;

	/** @var array<string,string> slug => hook suffix */
	private $hooks = array();

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_filter( 'add_menu_classes', array( $this, 'menu_bubble_classes' ) );
	}

	/**
	 * @return string[]
	 */
	public function hooks(): array {
		return array_values( $this->hooks );
	}

	public function is_mns_screen( string $hook_suffix ): bool {
		if ( in_array( $hook_suffix, $this->hooks(), true ) ) {
			return true;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return is_string( $page ) && 0 === strpos( $page, 'mns-' );
	}

	/**
	 * Pending counts for menu bubbles (static fixture).
	 *
	 * @return array{nurses_pending:int,needs_review:int,urgent_open:int,total:int}
	 */
	public function bubble_counts(): array {
		$hub = array();
		$file = MNS_PLUGIN_DIR . 'admin/static-fixtures/hub.php';
		if ( is_readable( $file ) ) {
			$loaded = require $file;
			if ( is_array( $loaded ) && isset( $loaded['bubbles'] ) && is_array( $loaded['bubbles'] ) ) {
				$hub = $loaded['bubbles'];
			}
		}
		$n = (int) ( $hub['nurses_pending'] ?? 0 );
		$r = (int) ( $hub['needs_review'] ?? 0 );
		$u = (int) ( $hub['urgent_open'] ?? 0 );
		return array(
			'nurses_pending' => $n,
			'needs_review'   => $r,
			'urgent_open'    => $u,
			'total'          => $n + $r + $u,
		);
	}

	public function register_menus(): void {
		$cap     = MNS_Roles::CAP_MANAGE;
		$bubbles = $this->bubble_counts();
		$root    = __( 'خدمات پرستاری', 'medyar-nursing-services' );
		if ( $bubbles['total'] > 0 ) {
			$root = sprintf(
				/* translators: 1: menu label 2: count */
				'%1$s <span class="awaiting-mod">%2$d</span>',
				$root,
				$bubbles['total']
			);
		}

		$hook = add_menu_page(
			__( 'خدمات پرستاری', 'medyar-nursing-services' ),
			$root,
			$cap,
			self::SLUG_DASHBOARD,
			array( $this, 'render_dashboard' ),
			'dashicons-heart',
			27
		);
		if ( is_string( $hook ) ) {
			$this->hooks[ self::SLUG_DASHBOARD ] = $hook;
		}

		$sections = array(
			self::SLUG_DASHBOARD => array( __( 'پیشخوان', 'medyar-nursing-services' ), array( $this, 'render_dashboard' ) ),
			self::SLUG_MONITOR   => array( __( 'رصد مرکزی', 'medyar-nursing-services' ), array( $this, 'render_monitor' ) ),
			self::SLUG_ASSIGN    => array( __( 'تخصیص', 'medyar-nursing-services' ), array( $this, 'render_assign' ) ),
			self::SLUG_NURSES    => array( __( 'عضویت پرستاران', 'medyar-nursing-services' ), array( $this, 'render_nurses' ) ),
			self::SLUG_MANUAL    => array( __( 'سفارش تلفنی', 'medyar-nursing-services' ), array( $this, 'render_manual' ) ),
			self::SLUG_FINANCE   => array( __( 'مالی و گزارش', 'medyar-nursing-services' ), array( $this, 'render_finance' ) ),
			self::SLUG_SETTINGS  => array( __( 'تنظیمات', 'medyar-nursing-services' ), array( $this, 'render_settings' ) ),
		);

		foreach ( $sections as $slug => $row ) {
			$label = $row[0];
			if ( self::SLUG_NURSES === $slug && $bubbles['nurses_pending'] > 0 ) {
				$label = sprintf( '%s <span class="awaiting-mod">%d</span>', $label, $bubbles['nurses_pending'] );
			}
			if ( self::SLUG_MONITOR === $slug && ( $bubbles['needs_review'] + $bubbles['urgent_open'] ) > 0 ) {
				$label = sprintf( '%s <span class="awaiting-mod">%d</span>', $label, $bubbles['needs_review'] + $bubbles['urgent_open'] );
			}
			$sub = add_submenu_page(
				self::SLUG_DASHBOARD,
				wp_strip_all_tags( $row[0] ),
				$label,
				$cap,
				$slug,
				$row[1]
			);
			if ( is_string( $sub ) ) {
				$this->hooks[ $slug ] = $sub;
			}
		}
	}

	/**
	 * @param array<int,mixed> $menu Menu.
	 * @return array<int,mixed>
	 */
	public function menu_bubble_classes( array $menu ): array {
		return $menu;
	}

	public function render_dashboard(): void {
		$this->guard();
		$view = MNS_PLUGIN_DIR . 'admin/views/dashboard-stub.php';
		if ( is_readable( $view ) ) {
			require $view;
		}
	}

	public function render_monitor(): void {
		$this->render_stub(
			__( 'رصد مرکزی', 'medyar-nursing-services' ),
			__( 'فیلتر وضعیت · شهر · بازه · پرستار · بیمار/موبایل', 'medyar-nursing-services' ),
			__( 'جدول سفارش‌ها، تایم‌لاین کامل، چک‌لیست/گزارش/دارو/فاکتور و یادداشت داخلی staff اینجا می‌آید.', 'medyar-nursing-services' )
		);
	}

	public function render_assign(): void {
		$this->render_stub(
			__( 'تخصیص خودکار / دستی', 'medyar-nursing-services' ),
			__( 'صف در انتظار تخصیص با پیشنهاد سیستم و override دستی', 'medyar-nursing-services' ),
			__( 'رتبه‌بندی وزن‌دار (پرستار قبلی · امتیاز · عدالت توزیع · سابقه لغو) در فاز دامنه فعال می‌شود.', 'medyar-nursing-services' )
		);
	}

	public function render_nurses(): void {
		$this->render_stub(
			__( 'عضویت پرستاران', 'medyar-nursing-services' ),
			__( 'تأیید · رد (+ دلیل) · تعلیق · مشاهده مدارک خصوصی', 'medyar-nursing-services' ),
			__( 'پس از تأیید، نقش پرستار و دسترسی پنل فعال می‌شود.', 'medyar-nursing-services' )
		);
	}

	public function render_manual(): void {
		$this->render_stub(
			__( 'سفارش تلفنی', 'medyar-nursing-services' ),
			__( 'جستجوی موبایل · بیمار · خدمت · زمان · صدور فاکتور دستی', 'medyar-nursing-services' ),
			__( 'پرداخت نقدی / کارت‌به‌کارت بدون الزام زرین‌پال پشتیبانی می‌شود.', 'medyar-nursing-services' )
		);
	}

	public function render_finance(): void {
		$this->render_stub(
			__( 'مالی و گزارش', 'medyar-nursing-services' ),
			__( 'گزارش مالی · کیف پول · صف برداشت · تعرفه و شهربندی', 'medyar-nursing-services' ),
			__( 'تعرفه، دسته خدمت، مصرفی و خدمات اضافه از اینجا مدیریت می‌شوند.', 'medyar-nursing-services' )
		);
	}

	public function render_settings(): void {
		$this->render_stub(
			__( 'تنظیمات', 'medyar-nursing-services' ),
			__( 'وزن الگوریتم · وفاداری · SMS · ادمین فوری · پزشک مشاور', 'medyar-nursing-services' ),
			__( 'تنظیمات عملیاتی سامانه پرستاری در این صفحه جمع می‌شوند.', 'medyar-nursing-services' )
		);
	}

	private function guard(): void {
		if ( ! current_user_can( MNS_Roles::CAP_MANAGE ) ) {
			wp_die( esc_html__( 'شما اجازه دسترسی به این صفحه را ندارید.', 'medyar-nursing-services' ) );
		}
	}

	private function render_stub( string $title, string $lead, string $body ): void {
		$this->guard();
		$mns_admin_title = $title;
		$mns_admin_lead  = $lead;
		$mns_admin_body  = $body;
		$view            = MNS_PLUGIN_DIR . 'admin/views/page-stub.php';
		if ( is_readable( $view ) ) {
			require $view;
		}
	}
}
