<?php
/**
 * Single source of truth for order statuses in the UI.
 *
 * Views and fixtures pass only the status slug; tone, icon, labels,
 * human sentences and timeline stage all come from here.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Order_Status {

	public const SUBMITTED     = 'submitted';
	public const ASSIGNED      = 'assigned';
	public const IN_PROGRESS   = 'in_progress';
	public const MEDICATION    = 'medication';
	public const SERVICE_ENDED = 'service_ended';
	public const INVOICED      = 'invoiced';
	public const PAID          = 'paid';
	public const COMPLETED     = 'completed';
	public const CANCELLED     = 'cancelled';
	public const NEEDS_REVIEW  = 'needs_review';

	/**
	 * Compact 5-stage timeline: ثبت ← پرستار ← خدمت ← پرداخت ← تکمیل.
	 *
	 * @return string[]
	 */
	public static function stages(): array {
		return array(
			__( 'ثبت', 'medyar-nursing-services' ),
			__( 'پرستار', 'medyar-nursing-services' ),
			__( 'خدمت', 'medyar-nursing-services' ),
			__( 'پرداخت', 'medyar-nursing-services' ),
			__( 'تکمیل', 'medyar-nursing-services' ),
		);
	}

	/**
	 * tone: neutral|info|primary|warning|success|muted|danger.
	 * stage: index of the *current* stage in stages(); stages before it are done, 5 = all done,
	 * -1 = terminal (cancelled / needs review) where the timeline shows the stop point from data.
	 * action: true when the patient must act (drives badges / sticky bars).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function all(): array {
		return array(
			self::SUBMITTED     => array(
				'label'   => __( 'ثبت‌شده', 'medyar-nursing-services' ),
				'tone'    => 'neutral',
				'icon'    => 'clock',
				'stage'   => 1,
				'patient' => __( 'درخواست شما ثبت شد و در حال تخصیص پرستار است.', 'medyar-nursing-services' ),
				'nurse'   => __( 'این سفارش هنوز به پرستاری تخصیص نیافته است.', 'medyar-nursing-services' ),
			),
			self::ASSIGNED      => array(
				'label'   => __( 'تخصیص‌یافته', 'medyar-nursing-services' ),
				'tone'    => 'info',
				'icon'    => 'user-check',
				'stage'   => 2,
				'patient' => __( 'پرستار شما مشخص شد.', 'medyar-nursing-services' ),
				'nurse'   => __( 'این سفارش به شما تخصیص یافته است؛ در زمان مقرر خدمت را شروع کنید.', 'medyar-nursing-services' ),
			),
			self::IN_PROGRESS   => array(
				'label'   => __( 'در حال انجام', 'medyar-nursing-services' ),
				'tone'    => 'primary',
				'icon'    => 'activity',
				'stage'   => 2,
				'patient' => __( 'پرستار در حال انجام خدمت است.', 'medyar-nursing-services' ),
				'nurse'   => __( 'خدمت در جریان است؛ چک‌لیست را کامل کنید.', 'medyar-nursing-services' ),
			),
			self::MEDICATION    => array(
				'label'   => __( 'مرحلهٔ دارو', 'medyar-nursing-services' ),
				'tone'    => 'primary',
				'icon'    => 'pill',
				'stage'   => 2,
				'patient' => __( 'دارو در حال بررسی است؛ نتیجه در فاکتور اعمال می‌شود.', 'medyar-nursing-services' ),
				'nurse'   => __( 'اطلاعات دارو و نسخه را ثبت کنید.', 'medyar-nursing-services' ),
			),
			self::SERVICE_ENDED => array(
				'label'   => __( 'پایان خدمت', 'medyar-nursing-services' ),
				'tone'    => 'warning',
				'icon'    => 'flag',
				'stage'   => 3,
				'patient' => __( 'خدمت تمام شد؛ فاکتور به‌زودی صادر می‌شود.', 'medyar-nursing-services' ),
				'nurse'   => __( 'گزارش پایان مراجعه را ثبت کنید؛ تا ثبت گزارش، تسویه انجام نمی‌شود.', 'medyar-nursing-services' ),
			),
			self::INVOICED      => array(
				'label'   => __( 'فاکتور صادر شده', 'medyar-nursing-services' ),
				'tone'    => 'warning',
				'icon'    => 'invoice',
				'stage'   => 3,
				'action'  => true,
				'patient' => __( 'فاکتور آمادهٔ پرداخت است.', 'medyar-nursing-services' ),
				'nurse'   => __( 'فاکتور برای بیمار صادر شد؛ سهم شما پس از پرداخت بیمار واریز می‌شود.', 'medyar-nursing-services' ),
			),
			self::PAID          => array(
				'label'   => __( 'پرداخت‌شده', 'medyar-nursing-services' ),
				'tone'    => 'success',
				'icon'    => 'check-circle',
				'stage'   => 4,
				'patient' => __( 'پرداخت انجام شد.', 'medyar-nursing-services' ),
				'nurse'   => __( 'بیمار پرداخت کرد؛ سهم شما به کیف پول واریز می‌شود.', 'medyar-nursing-services' ),
			),
			self::COMPLETED     => array(
				'label'   => __( 'تکمیل‌شده', 'medyar-nursing-services' ),
				'tone'    => 'success',
				'icon'    => 'check-circle',
				'stage'   => 5,
				'patient' => __( 'سفارش کامل شد.', 'medyar-nursing-services' ),
				'nurse'   => __( 'سفارش کامل شد.', 'medyar-nursing-services' ),
			),
			self::CANCELLED     => array(
				'label'    => __( 'لغوشده', 'medyar-nursing-services' ),
				'tone'     => 'muted',
				'icon'     => 'x-circle',
				'stage'    => -1,
				'terminal' => true,
				'patient'  => __( 'این سفارش لغو شده است.', 'medyar-nursing-services' ),
				'nurse'    => __( 'این سفارش لغو شده است.', 'medyar-nursing-services' ),
			),
			self::NEEDS_REVIEW  => array(
				'label'    => __( 'نیازمند بررسی', 'medyar-nursing-services' ),
				'tone'     => 'danger',
				'icon'     => 'alert',
				'stage'    => -1,
				'terminal' => true,
				'patient'  => __( 'همکاران ما در حال بررسی این سفارش‌اند؛ با شما تماس می‌گیریم.', 'medyar-nursing-services' ),
				'nurse'    => __( 'این سفارش در حال بررسی همکاران مدیار است؛ فعلاً اقدامی لازم نیست.', 'medyar-nursing-services' ),
			),
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function get( string $slug ): array {
		$all = self::all();
		$row = $all[ $slug ] ?? $all[ self::SUBMITTED ];
		return array_merge(
			array(
				'slug'     => isset( $all[ $slug ] ) ? $slug : self::SUBMITTED,
				'action'   => false,
				'terminal' => false,
			),
			$row
		);
	}

	public static function is_valid( string $slug ): bool {
		return isset( self::all()[ $slug ] );
	}

	/**
	 * @param string $audience patient|nurse.
	 */
	public static function sentence( string $slug, string $audience = 'patient' ): string {
		$row = self::get( $slug );
		return (string) ( 'nurse' === $audience ? $row['nurse'] : $row['patient'] );
	}
}
