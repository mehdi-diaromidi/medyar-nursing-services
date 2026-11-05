<?php
/**
 * Front-end bootstrap (panels/templates land in phase 3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Public {

	/** @var self|null */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		MNS_Page_Templates::instance()->init();
		add_filter( 'document_title_parts', array( $this, 'filter_document_title' ) );
		add_action( 'wp_footer', array( $this, 'render_dev_tools' ), 40 );
		add_filter( 'mns_dev_states', array( $this, 'filter_dev_states' ), 10, 3 );
	}

	/**
	 * @param array<string,string> $states States.
	 * @return array<string,string>
	 */
	public function filter_dev_states( array $states, string $panel, string $page ): array {
		if ( 'patient' === $panel && 'dashboard' === $page ) {
			return array_merge(
				$states,
				array(
					'unpaid'   => __( 'فاکتور پرداخت‌نشده', 'medyar-nursing-services' ),
					'active'   => __( 'سفارش فعال', 'medyar-nursing-services' ),
					'survey'   => __( 'پرسشنامه باز', 'medyar-nursing-services' ),
					'new_user' => __( 'کاربر جدید', 'medyar-nursing-services' ),
				)
			);
		}
		if ( 'patient' === $panel && 'order-view' === $page ) {
			$out = array(
				'default' => __( 'عادی (تخصیص‌یافته)', 'medyar-nursing-services' ),
				'loading' => $states['loading'] ?? __( 'در حال بارگذاری', 'medyar-nursing-services' ),
				'error'   => $states['error'] ?? __( 'خطا', 'medyar-nursing-services' ),
			);
			foreach ( MNS_Order_Status::all() as $slug => $row ) {
				$out[ $slug ] = (string) ( $row['label'] ?? $slug );
			}
			return $out;
		}
		if ( 'patient' === $panel && 'new-order' === $page ) {
			return array_merge(
				$states,
				array(
					'success'  => __( 'ثبت موفق', 'medyar-nursing-services' ),
					'no_nurse' => __( 'پرستار موجود نیست', 'medyar-nursing-services' ),
				)
			);
		}
		if ( 'patient' === $panel && 'patients' === $page ) {
			return array_merge( $states, array( 'form' => __( 'فرم افزودن', 'medyar-nursing-services' ) ) );
		}
		if ( 'patient' === $panel && 'invoice-view' === $page ) {
			return array_merge(
				$states,
				array(
					'paid'   => __( 'پرداخت‌شده', 'medyar-nursing-services' ),
					'failed' => __( 'پرداخت ناموفق', 'medyar-nursing-services' ),
					'paying' => __( 'در حال پرداخت', 'medyar-nursing-services' ),
				)
			);
		}
		if ( 'patient' === $panel && 'loyalty' === $page ) {
			return array_merge( $states, array( 'full' => __( 'کارت کامل', 'medyar-nursing-services' ) ) );
		}
		if ( 'patient' === $panel && 'surveys' === $page ) {
			return array_merge( $states, array( 'submitted' => __( 'ارسال‌شده', 'medyar-nursing-services' ) ) );
		}
		if ( 'nurse' === $panel && 'dashboard' === $page ) {
			return array_merge( $states, array( 'offline' => __( 'آفلاین / عدم پذیرش', 'medyar-nursing-services' ) ) );
		}
		if ( 'nurse' === $panel && 'order-view' === $page ) {
			$out = array(
				'default' => __( 'عادی (تخصیص‌یافته)', 'medyar-nursing-services' ),
				'loading' => $states['loading'] ?? __( 'در حال بارگذاری', 'medyar-nursing-services' ),
				'error'   => $states['error'] ?? __( 'خطا', 'medyar-nursing-services' ),
			);
			foreach ( array( MNS_Order_Status::ASSIGNED, MNS_Order_Status::IN_PROGRESS, MNS_Order_Status::SERVICE_ENDED, MNS_Order_Status::NEEDS_REVIEW ) as $slug ) {
				$row = MNS_Order_Status::get( $slug );
				$out[ $slug ] = (string) ( $row['label'] ?? $slug );
			}
			return $out;
		}
		return $states;
	}

	/**
	 * @param array<string,string> $parts Title parts.
	 * @return array<string,string>
	 */
	public function filter_document_title( array $parts ): array {
		$ctx = self::front_context();
		if ( null === $ctx ) {
			return $parts;
		}

		if ( 'catalog' === $ctx['panel'] ) {
			if ( 'service' === $ctx['page'] && '' !== $ctx['service'] ) {
				$svc = MNS_Static_Fixtures::catalog_service( $ctx['service'] );
				$parts['title'] = ! empty( $svc['title'] )
					? (string) $svc['title']
					: __( 'خدمت پرستاری', 'medyar-nursing-services' );
			} else {
				$parts['title'] = __( 'خدمات پرستاری در منزل', 'medyar-nursing-services' );
			}
			return $parts;
		}

		$titles = MNS_UI::page_titles( $ctx['panel'] );
		if ( isset( $titles[ $ctx['page'] ] ) ) {
			$parts['title'] = $titles[ $ctx['page'] ];
		} elseif ( 'not-found' === $ctx['page'] ) {
			$parts['title'] = MNS_Copy::t( 'state.not_found_title' );
		}
		return $parts;
	}

	public function render_dev_tools(): void {
		if ( ! MNS_Dev::enabled() ) {
			return;
		}
		$ctx = self::front_context();
		if ( null === $ctx ) {
			return;
		}
		MNS_Dev::render_switcher( $ctx['panel'], $ctx['page'] );
	}

	/**
	 * @return array{panel:string,page:string,service:string}|null
	 */
	private static function front_context(): ?array {
		$panel   = MNS_Pages::current_panel();
		$page    = '';
		$service = isset( $_GET['mns_service'] ) ? sanitize_key( wp_unslash( $_GET['mns_service'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tpl     = get_page_template_slug();

		if ( '' === $panel ) {
			if ( 'mns-patient.php' === $tpl ) {
				$panel = 'patient';
			} elseif ( 'mns-nurse.php' === $tpl ) {
				$panel = 'nurse';
			} elseif ( 'mns-catalog.php' === $tpl ) {
				$panel = 'catalog';
			}
		}

		if ( '' === $panel ) {
			return null;
		}

		if ( 'catalog' === $panel ) {
			$page = '' !== $service ? 'service' : 'archive';
		} else {
			$page = MNS_Pages::current_page( $panel );
		}

		return array(
			'panel'   => $panel,
			'page'    => $page,
			'service' => $service,
		);
	}
}
