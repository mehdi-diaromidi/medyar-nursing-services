<?php
/**
 * Nurse "My profile" (P-05): the affiliate panel's profile page (identity card, personal-information form,
 * security banner) with nurse fields. Everything is editable except the mobile number (the login number).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$p  = MNS_Static_Fixtures::get( 'nurse/profile_full' );
$fa = static function ( $v ): string {
	return MNS_Format::digits( (string) $v );
};

$first      = (string) ( $p['first_name'] ?? '' );
$last       = (string) ( $p['last_name'] ?? '' );
$display    = (string) ( $p['display_name'] ?? '' );
$name       = trim( $first . ' ' . $last );
$name       = '' !== $name ? $name : $display;
$email      = (string) ( $p['email'] ?? '' );
$mobile     = (string) ( $p['mobile'] ?? '' );
$about      = (string) ( $p['about'] ?? '' );
$exp        = (string) ( $p['experience'] ?? '' );
$qual       = (string) ( $p['qualification'] ?? '' );
$qual_opts  = is_array( $p['qualification_options'] ?? null ) ? $p['qualification_options'] : array();
$qual_label = (string) ( $qual_opts[ $qual ] ?? $qual );
$exp_opts   = is_array( $p['experience_options'] ?? null ) ? $p['experience_options'] : array();
$support    = (string) ( $p['support_tel'] ?? '' );
$avatar_url = MNS_PLUGIN_URL . 'assets/img/profile/man-avatar.webp';

/** Inline icons, the same set as the affiliate panel's profile page. */
$svg = static function ( string $name ): string {
	$map = array(
		'person'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		'phone'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.68 2.35a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.73-1.27a2 2 0 0 1 2.11-.45c.75.32 1.54.55 2.35.68A2 2 0 0 1 22 16.92z"/></svg>',
		'mail'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>',
		'bag'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2M2 13h20"/></svg>',
		'check'    => '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/><path d="m7.5 12.5 3 3 6-6.5" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'camera'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>',
		'trash'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>',
		'pencil'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>',
		'save'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>',
		'chevron'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>',
		'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>',
		'id'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="9" cy="12" r="2.5"/><path d="M14 10h5M14 14h3"/></svg>',
		'shield'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
		'lock'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
		'cap'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/></svg>',
		'badge'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12z"/><path d="m8.5 14 -1.5 7 5-3 5 3-1.5-7"/></svg>',
		'clock'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
		'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 21v-4h6v4M8 7h2M14 7h2M8 11h2M14 11h2"/></svg>',
		'heart'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>',
	);
	return $map[ $name ] ?? '';
};

$security_items = array(
	array( 'icon' => 'lock', 'label' => __( 'رمزگذاری اطلاعات', 'medyar-nursing-services' ) ),
	array( 'icon' => 'shield', 'label' => __( 'پایش فعالیت‌ها', 'medyar-nursing-services' ) ),
	array( 'icon' => 'person', 'label' => __( 'تأیید هویت', 'medyar-nursing-services' ) ),
);

/** A locked (verified) field: same control as the others, read-only, with a lock on the end. */
$locked = static function ( string $label, string $icon, string $value, array $opts = array() ) use ( $svg ): void {
	$dir = ! empty( $opts['ltr'] ) ? ' dir="ltr"' : '';
	?>
	<label class="mns-field-block mns-pf-locked">
		<span class="mns-field-block__label"><?php echo esc_html( $label ); ?></span>
		<span class="mns-field-block__control">
			<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<input type="text" value="<?php echo esc_attr( $value ); ?>" readonly tabindex="0" aria-readonly="true"<?php echo $dir; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
			<span class="mns-pf-locked__lock" aria-hidden="true"><?php echo $svg( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</span>
		<?php if ( ! empty( $opts['hint'] ) ) : ?>
			<span class="mns-pf-hint"><?php echo esc_html( (string) $opts['hint'] ); ?> <a href="<?php echo esc_attr( (string) ( $opts['tel'] ?? '' ) ); ?>"><?php echo esc_html__( 'تماس با پشتیبانی', 'medyar-nursing-services' ); ?></a></span>
		<?php endif; ?>
	</label>
	<?php
};
?>
<section class="mns-pf" dir="rtl" data-mns-profile>

	<header class="mns-pf__head">
		<div class="mns-pf__intro">
			<h1 class="mns-pf__title">
				<span class="drplus-simple-icon-wrap icon-has-bg mns-pf__title-icon" aria-hidden="true">
					<i class="drplus-icon-user drplus-simple-icon"></i>
				</span>
				<span class="mns-pf__title-text"><?php echo esc_html__( 'پروفایل من', 'medyar-nursing-services' ); ?></span>
			</h1>
			<p class="mns-pf__lead"><?php echo esc_html__( 'مشاهده و ویرایش اطلاعات حساب کاربری شما', 'medyar-nursing-services' ); ?></p>
		</div>
	</header>

	<div class="mns-pf-card mns-pf-hero">
		<div class="mns-pf-hero__info">
			<h2 class="mns-pf-hero__name" data-mns-pf-bind="name"><?php echo esc_html( $name ); ?></h2>
			<span class="mns-pf-hero__status"><span aria-hidden="true"><?php echo $svg( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'تأییدشده', 'medyar-nursing-services' ); ?></span>
			<ul class="mns-pf-hero__meta">
				<li>
					<span class="mns-pf-hero__icon" aria-hidden="true"><?php echo $svg( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span dir="ltr"><?php echo '' !== $mobile ? esc_html( $fa( $mobile ) ) : '—'; ?></span>
				</li>
				<?php if ( '' !== $email ) : ?>
					<li>
						<span class="mns-pf-hero__icon" aria-hidden="true"><?php echo $svg( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mns-pf-hero__en" dir="ltr" data-mns-pf-bind="email"><?php echo esc_html( $email ); ?></span>
					</li>
				<?php endif; ?>
				<li>
					<span class="mns-pf-hero__icon" aria-hidden="true"><?php echo $svg( 'bag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><?php echo esc_html__( 'مدرک:', 'medyar-nursing-services' ); ?> <span data-mns-pf-bind="qualification"><?php echo esc_html( $qual_label ); ?></span></span>
				</li>
			</ul>
		</div>

		<div class="mns-pf-hero__side">
			<div class="mns-pf-hero__avatar" data-mns-pf-avatar data-default="<?php echo esc_url( $avatar_url ); ?>">
				<img class="mns-pf-hero__avatar-img" src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" width="128" height="128" />
				<label class="mns-pf-hero__camera" for="mns-pf-avatar-input" aria-label="<?php echo esc_attr__( 'تغییر تصویر پروفایل', 'medyar-nursing-services' ); ?>" title="<?php echo esc_attr__( 'آپلود تصویر پروفایل', 'medyar-nursing-services' ); ?>"><?php echo $svg( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
				<button type="button" class="mns-pf-hero__remove" data-mns-pf-avatar-remove aria-label="<?php echo esc_attr__( 'حذف تصویر پروفایل', 'medyar-nursing-services' ); ?>" title="<?php echo esc_attr__( 'حذف تصویر پروفایل', 'medyar-nursing-services' ); ?>" hidden><?php echo $svg( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</div>
			<a class="mns-pf-hero__edit" href="#mns-pf-form"><span aria-hidden="true"><?php echo $svg( 'pencil' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html__( 'ویرایش پروفایل', 'medyar-nursing-services' ); ?></a>
		</div>
	</div>

	<div class="mns-pf-card mns-pf-form-card" id="mns-pf-form">
		<h2 class="mns-pf-form-card__title">
			<span aria-hidden="true"><?php echo $svg( 'person' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php echo esc_html__( 'اطلاعات شخصی', 'medyar-nursing-services' ); ?>
		</h2>

		<form method="post" class="mns-pf-form" id="mns-pf-form-el" data-mns-pf-form novalidate>
			<div class="mns-pf-notice" role="status" aria-live="polite" tabindex="-1" data-mns-pf-notice hidden></div>
			<input type="file" name="avatar" id="mns-pf-avatar-input" class="mns-pf-avatar-input" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif" hidden />

			<div class="mns-form-row--2">
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'نام', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'person' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input type="text" name="first_name" required maxlength="100" value="<?php echo esc_attr( $first ); ?>" placeholder="نام" autocomplete="given-name" data-mns-pf-field />
					</span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'نام خانوادگی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'person' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input type="text" name="last_name" required maxlength="100" value="<?php echo esc_attr( $last ); ?>" placeholder="نام خانوادگی" autocomplete="family-name" data-mns-pf-field />
					</span>
				</label>
			</div>

			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'نام نمایشی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
				<span class="mns-field-block__control">
					<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'person' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<input type="text" name="display_name" required maxlength="191" value="<?php echo esc_attr( $display ); ?>" autocomplete="nickname" data-mns-pf-field />
				</span>
				<span class="mns-pf-hint"><?php echo esc_html__( 'به این صورت اسم شما برای بیمار و در حساب کاربری دیده خواهد شد.', 'medyar-nursing-services' ); ?></span>
			</label>

			<div class="mns-form-row--2">
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'ایمیل', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input type="email" name="email" required value="<?php echo esc_attr( $email ); ?>" placeholder="example@gmail.com" autocomplete="email" dir="ltr" data-mns-pf-email data-mns-pf-field />
					</span>
				</label>
				<?php $locked( __( 'شماره تماس', 'medyar-nursing-services' ), 'phone', $fa( $mobile ), array( 'ltr' => true, 'hint' => __( 'شمارهٔ ورود شما است. برای تغییر،', 'medyar-nursing-services' ), 'tel' => $support ) ); ?>
			</div>

			<div class="mns-form-row--2">
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'تاریخ تولد', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input type="text" name="birthday" required value="<?php echo esc_attr( (string) ( $p['birthday'] ?? '' ) ); ?>" readonly autocomplete="off" data-mns-jdate="birthday" aria-label="<?php echo esc_attr__( 'تاریخ تولد', 'medyar-nursing-services' ); ?>" placeholder="<?php echo esc_attr__( 'انتخاب تاریخ', 'medyar-nursing-services' ); ?>" data-mns-pf-field />
					</span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'کد ملی', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'id' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input type="text" name="nid" required value="<?php echo esc_attr( $fa( (string) ( $p['nid'] ?? '' ) ) ); ?>" placeholder="۰۰۱۲۳۴۵۶۷۸" inputmode="numeric" minlength="10" maxlength="10" dir="ltr" data-mns-pf-nid data-mns-pf-field />
					</span>
				</label>
			</div>

			<div class="mns-form-row--2">
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'مدرک پرستاری', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control mns-field-block__control--select">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'cap' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<select name="qualification" required class="mns-select-native" data-mns-select data-mns-pf-field data-mns-pf-qual>
							<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
							<?php foreach ( $qual_opts as $k => $label ) : ?>
								<option value="<?php echo esc_attr( (string) $k ); ?>" <?php selected( $qual, (string) $k ); ?>><?php echo esc_html( (string) $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="mns-field-block__chevron" aria-hidden="true"><?php echo $svg( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'شمارهٔ نظام پرستاری', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'badge' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input type="text" name="license_no" required maxlength="100" value="<?php echo esc_attr( (string) ( $p['license_no'] ?? '' ) ); ?>" placeholder="۱۲۳۴۵۶" inputmode="numeric" dir="ltr" data-mns-pf-field />
					</span>
				</label>
			</div>

			<div class="mns-form-row--2">
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'سابقهٔ کار', 'medyar-nursing-services' ); ?><span class="mns-req" aria-hidden="true">*</span></span>
					<span class="mns-field-block__control mns-field-block__control--select">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<select name="experience" required class="mns-select-native" data-mns-select data-mns-pf-field>
							<option value=""><?php echo esc_html__( 'انتخاب کنید', 'medyar-nursing-services' ); ?></option>
							<?php foreach ( $exp_opts as $k => $label ) : ?>
								<option value="<?php echo esc_attr( (string) $k ); ?>" <?php selected( $exp, (string) $k ); ?>><?php echo esc_html( (string) $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="mns-field-block__chevron" aria-hidden="true"><?php echo $svg( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</label>
				<label class="mns-field-block">
					<span class="mns-field-block__label"><?php echo esc_html__( 'محل کار فعلی', 'medyar-nursing-services' ); ?></span>
					<span class="mns-field-block__control">
						<span class="mns-field-block__lead-ico" aria-hidden="true"><?php echo $svg( 'building' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input type="text" name="workplace" maxlength="100" value="<?php echo esc_attr( (string) ( $p['workplace'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr__( 'مثلاً بیمارستان یا درمانگاه', 'medyar-nursing-services' ); ?>" data-mns-pf-field />
					</span>
				</label>
			</div>

			<label class="mns-field-block">
				<span class="mns-field-block__label"><?php echo esc_html__( 'درباره من', 'medyar-nursing-services' ); ?></span>
				<span class="mns-field-block__control mns-field-block__control--area">
					<textarea name="about" id="mns-pf-about" maxlength="200" rows="3" placeholder="<?php echo esc_attr__( 'توضیح کوتاهی درباره سابقه و تخصص خود بنویسید ...', 'medyar-nursing-services' ); ?>" data-mns-pf-field><?php echo esc_textarea( $about ); ?></textarea>
					<span class="mns-field-block__meta">
						<span class="mns-field-block__counter"><span id="mns-pf-about-count"><?php echo esc_html( $fa( function_exists( 'mb_strlen' ) ? mb_strlen( $about ) : strlen( $about ) ) ); ?></span>/۲۰۰</span>
					</span>
				</span>
			</label>

			<div class="mns-pf-form__foot">
				<button type="submit" class="mns-submit-btn mns-pf-form__btn">
					<span aria-hidden="true"><?php echo $svg( 'save' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php echo esc_html__( 'ذخیره اطلاعات', 'medyar-nursing-services' ); ?>
				</button>
			</div>
		</form>
	</div>

	<aside class="mns-pf-security">
		<div class="mns-pf-security__text">
			<strong><?php echo esc_html__( 'امنیت حساب کاربری', 'medyar-nursing-services' ); ?></strong>
			<span><?php echo esc_html__( 'اطلاعات حساب شما با بالاترین سطح امنیت محافظت می‌شود.', 'medyar-nursing-services' ); ?></span>
		</div>
		<ul class="mns-pf-security__items">
			<?php foreach ( $security_items as $item ) : ?>
				<li>
					<span class="mns-pf-security__icon" aria-hidden="true"><?php echo $svg( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><?php echo esc_html( $item['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</aside>

</section>
