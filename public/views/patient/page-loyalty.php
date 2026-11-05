<?php
/**
 * Loyalty stamp card + codes (Phase 4).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

MNS_UI::boot_icons();

$d       = MNS_Static_Fixtures::get( 'patient/loyalty' );
$filled  = isset( $d['filled'] ) ? max( 0, (int) $d['filled'] ) : 0;
$required = isset( $d['required'] ) ? max( 1, (int) $d['required'] ) : 5;
$codes   = isset( $d['codes'] ) && is_array( $d['codes'] ) ? $d['codes'] : array();

if ( MNS_Dev::is( 'empty' ) ) {
	$filled = 0;
	$codes  = array();
}
if ( MNS_Dev::is( 'full' ) ) {
	$filled = $required;
}

MNS_UI::page_header(
	array(
		'title' => __( 'کارت وفاداری و تخفیف', 'medyar-nursing-services' ),
		'lead'  => (string) ( $d['progress'] ?? '' ),
	)
);

MNS_UI::stamp_card(
	array(
		'filled' => $filled,
		'total'  => $required,
		'hint'   => (string) ( $d['rules'] ?? '' ),
	)
);
?>

<details class="mns-details mns-card">
	<summary class="mns-details__summary"><?php echo esc_html__( 'قوانین کارت مهر', 'medyar-nursing-services' ); ?></summary>
	<p class="mns-field-hint" style="margin:0"><?php echo esc_html( (string) ( $d['rules'] ?? '' ) ); ?></p>
</details>

<section class="mns-card">
	<h2 class="mns-card__title"><?php echo esc_html__( 'کدهای تخفیف فعال', 'medyar-nursing-services' ); ?></h2>
	<?php if ( empty( $codes ) ) : ?>
		<?php
		MNS_UI::empty_state(
			array(
				'title' => __( 'کد فعالی ندارید', 'medyar-nursing-services' ),
				'text'  => __( 'پس از تکمیل کارت مهر، کد تخفیف اینجا نمایش داده می‌شود.', 'medyar-nursing-services' ),
				'icon'  => 'heart',
			)
		);
		?>
	<?php else : ?>
		<ul class="mns-code-list">
			<?php foreach ( $codes as $code ) : ?>
				<li class="mns-code-list__item">
					<div>
						<code dir="ltr"><?php echo esc_html( (string) ( $code['code'] ?? '' ) ); ?></code>
						<span><?php echo esc_html( (string) ( $code['label'] ?? '' ) ); ?></span>
						<?php if ( ! empty( $code['expires'] ) ) : ?>
							<small><?php echo esc_html__( 'انقضا:', 'medyar-nursing-services' ); ?> <?php echo esc_html( MNS_Format::human( (string) $code['expires'] ) ); ?></small>
						<?php endif; ?>
					</div>
					<button type="button" class="mns-outline-btn" data-mns-copy="<?php echo esc_attr( (string) ( $code['code'] ?? '' ) ); ?>">
						<?php echo esc_html( MNS_Copy::t( 'action.copy' ) ); ?>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<p class="mns-field-hint">
		<?php echo esc_html__( 'تخفیف سفارش اول:', 'medyar-nursing-services' ); ?>
		<?php echo esc_html( (string) ( $d['first_order'] ?? '—' ) ); ?>
	</p>
</section>
