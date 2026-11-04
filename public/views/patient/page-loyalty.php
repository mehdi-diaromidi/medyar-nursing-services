<?php
/**
 * Loyalty stamp card.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d        = MNS_Static_Fixtures::get( 'patient/loyalty' );
$filled   = isset( $d['filled'] ) ? max( 0, (int) $d['filled'] ) : 0;
$required = isset( $d['required'] ) ? max( 1, (int) $d['required'] ) : 5;
$codes    = isset( $d['codes'] ) && is_array( $d['codes'] ) ? $d['codes'] : array();
?>
<h1 class="mns-dash__title"><?php echo esc_html__( 'کارت وفاداری و تخفیف', 'medyar-nursing-services' ); ?></h1>
<p class="mns-dash__lead"><?php echo esc_html( (string) ( $d['progress'] ?? '' ) ); ?></p>

<section class="mns-card mns-stamp-card" aria-labelledby="mns-stamp-title">
	<h2 id="mns-stamp-title" class="mns-card__title"><?php echo esc_html__( 'کارت مهر', 'medyar-nursing-services' ); ?></h2>
	<ul class="mns-stamps" role="list" aria-label="<?php echo esc_attr( sprintf( /* translators: 1 filled 2 required */ __( '%1$d از %2$d مهر', 'medyar-nursing-services' ), $filled, $required ) ); ?>">
		<?php for ( $i = 1; $i <= $required; $i++ ) : ?>
			<li class="mns-stamps__slot<?php echo ( $i <= $filled ) ? ' is-filled' : ''; ?>" aria-hidden="true">
				<span class="mns-stamps__mark"><?php echo ( $i <= $filled ) ? '✓' : (string) $i; ?></span>
			</li>
		<?php endfor; ?>
	</ul>
	<p class="mns-field-hint"><?php echo esc_html( (string) ( $d['rules'] ?? '' ) ); ?></p>
</section>

<section class="mns-card">
	<h2 class="mns-card__title"><?php echo esc_html__( 'کدهای تخفیف', 'medyar-nursing-services' ); ?></h2>
	<?php if ( empty( $codes ) ) : ?>
		<div class="mns-empty">
			<p class="mns-empty__title"><?php echo esc_html__( 'کد فعالی ندارید', 'medyar-nursing-services' ); ?></p>
			<p class="mns-empty__text"><?php echo esc_html__( 'پس از تکمیل کارت مهر، کد تخفیف اینجا نمایش داده می‌شود.', 'medyar-nursing-services' ); ?></p>
		</div>
	<?php else : ?>
		<ul class="mns-code-list">
			<?php foreach ( $codes as $code ) : ?>
				<li class="mns-code-list__item">
					<code dir="ltr"><?php echo esc_html( (string) ( $code['code'] ?? '' ) ); ?></code>
					<span><?php echo esc_html( (string) ( $code['label'] ?? '' ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<p class="mns-field-hint"><?php echo esc_html__( 'تخفیف سفارش اول:', 'medyar-nursing-services' ); ?> <?php echo esc_html( (string) ( $d['first_order'] ?? '—' ) ); ?></p>
</section>
