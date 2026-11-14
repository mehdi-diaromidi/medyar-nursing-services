<?php
/**
 * Loyalty: stamp card with the way to the next reward, active discount codes, first-order discount.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$d        = MNS_Static_Fixtures::get( 'patient/loyalty' );
$filled   = isset( $d['filled'] ) ? max( 0, (int) $d['filled'] ) : 0;
$required = isset( $d['required'] ) ? max( 1, (int) $d['required'] ) : 5;
$codes    = isset( $d['codes'] ) && is_array( $d['codes'] ) ? $d['codes'] : array();

if ( MNS_Dev::is( 'empty' ) ) {
	$filled = 0;
	$codes  = array();
}
if ( MNS_Dev::is( 'full' ) ) {
	$filled = $required;
}
$filled    = min( $filled, $required );
$left      = $required - $filled;
$complete  = 0 === $left;
$percent   = (int) round( $filled / $required * 100 );
$new_url   = MNS_Pages::panel_url( 'patient', 'new-order' );
$first     = (string) ( $d['first_order'] ?? '' );
$first_used = false !== strpos( $first, 'استفاده' );

MNS_UI::page_header(
	array(
		'title' => __( 'کارت وفاداری و تخفیف', 'medyar-nursing-services' ),
		'icon'  => 'gift',
	)
);
?>
<div class="mns-loy">
	<div class="mns-form-notice mns-notice" data-mns-notice hidden tabindex="-1"></div>

	<?php /* ---- Stamp card ---- */ ?>
	<section class="mns-loy__card mns-loy__hero" aria-labelledby="mns-loy-title">
		<div class="mns-loy__hero-top">
			<div>
				<h2 class="mns-loy__title" id="mns-loy-title">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: stamps, 2: required stamps */
							__( '%1$s از %2$s مهر', 'medyar-nursing-services' ),
							MNS_Format::digits( (string) $filled ),
							MNS_Format::digits( (string) $required )
						)
					);
					?>
				</h2>
				<p class="mns-loy__sub">
					<?php
					echo $complete
						? esc_html__( 'کارت کامل شد و کد تخفیف شما صادر شد.', 'medyar-nursing-services' )
						: esc_html(
							sprintf(
								/* translators: %s: remaining stamps */
								__( '%s مهر دیگر تا تخفیف بعدی', 'medyar-nursing-services' ),
								MNS_Format::digits( (string) $left )
							)
						);
					?>
				</p>
			</div>
			<a class="mns-submit-btn mns-loy__cta" href="<?php echo esc_url( $new_url ); ?>">
				<span><?php echo esc_html__( 'ثبت سفارش', 'medyar-nursing-services' ); ?></span>
			</a>
		</div>

		<ol class="mns-loy__stamps" aria-label="<?php echo esc_attr__( 'مهرها', 'medyar-nursing-services' ); ?>">
			<?php for ( $i = 1; $i <= $required; $i++ ) : ?>
				<li class="mns-loy__stamp<?php echo $i <= $filled ? ' is-filled' : ''; ?><?php echo $i === $required ? ' is-reward' : ''; ?>">
					<span class="mns-loy__dot" aria-hidden="true">
						<?php
						if ( $i === $required ) {
							echo mns_icon( 'gift' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} elseif ( $i <= $filled ) {
							echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							echo esc_html( MNS_Format::digits( (string) $i ) );
						}
						?>
					</span>
					<span class="screen-reader-text"><?php echo esc_html( MNS_Format::digits( (string) $i ) ); ?></span>
				</li>
			<?php endfor; ?>
		</ol>

		<p class="mns-loy__rule"><?php echo esc_html( (string) ( $d['rules'] ?? '' ) ); ?></p>
	</section>

	<?php /* ---- Codes ---- */ ?>
	<section class="mns-loy__section" aria-labelledby="mns-loy-codes">
		<h2 class="mns-loy__h" id="mns-loy-codes"><?php echo esc_html__( 'کدهای تخفیف فعال', 'medyar-nursing-services' ); ?></h2>
		<?php if ( empty( $codes ) ) : ?>
			<div class="mns-loy__empty">
				<span class="mns-loy__empty-ico" aria-hidden="true"><?php echo mns_icon( 'gift' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<p><?php echo esc_html__( 'کد فعالی ندارید. با تکمیل کارت مهر، کد تخفیف اینجا نمایش داده می‌شود.', 'medyar-nursing-services' ); ?></p>
			</div>
		<?php else : ?>
			<ul class="mns-loy__codes">
				<?php foreach ( $codes as $code ) : ?>
					<?php
					$label = (string) ( $code['label'] ?? '' );
					$value = '';
					if ( preg_match( '/([۰-۹0-9]+)\s*٪/u', $label, $m ) ) {
						$value = $m[1] . '٪';
					}
					?>
					<li class="mns-loy__coupon">
						<div class="mns-loy__coupon-stub">
							<strong><?php echo esc_html( '' !== $value ? MNS_Format::digits( $value ) : '٪' ); ?></strong>
							<small><?php echo esc_html__( 'تخفیف', 'medyar-nursing-services' ); ?></small>
						</div>
						<div class="mns-loy__coupon-body">
							<p class="mns-loy__coupon-label"><?php echo esc_html( $label ); ?></p>
							<code class="mns-loy__coupon-code" dir="ltr"><?php echo esc_html( (string) ( $code['code'] ?? '' ) ); ?></code>
							<?php if ( ! empty( $code['expires'] ) ) : ?>
								<p class="mns-loy__coupon-exp"><?php echo esc_html__( 'انقضا:', 'medyar-nursing-services' ); ?> <?php echo esc_html( MNS_Format::human( (string) $code['expires'], false ) ); ?></p>
							<?php endif; ?>
						</div>
						<div class="mns-loy__coupon-act">
							<button type="button" class="mns-outline-btn mns-loy__copy" data-mns-copy="<?php echo esc_attr( (string) ( $code['code'] ?? '' ) ); ?>">
								<?php echo mns_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php echo esc_html( MNS_Copy::t( 'action.copy' ) ); ?></span>
							</button>
							<a class="mns-loy__use" href="<?php echo esc_url( $new_url ); ?>"><?php echo esc_html__( 'استفاده در سفارش', 'medyar-nursing-services' ); ?></a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>

	<?php /* ---- First-order discount ---- */ ?>
	<?php if ( '' !== $first ) : ?>
		<section class="mns-loy__card mns-loy__first">
			<span class="mns-loy__first-ico" aria-hidden="true"><?php echo mns_icon( 'check-circle' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="mns-loy__first-text">
				<strong><?php echo esc_html__( 'تخفیف سفارش اول', 'medyar-nursing-services' ); ?></strong>
				<small><?php echo esc_html__( 'یک بار برای هر شمارهٔ موبایل تأییدشده', 'medyar-nursing-services' ); ?></small>
			</span>
			<span class="mns-loy__chip<?php echo $first_used ? ' is-used' : ''; ?>"><?php echo esc_html( $first ); ?></span>
		</section>
	<?php endif; ?>
</div>
