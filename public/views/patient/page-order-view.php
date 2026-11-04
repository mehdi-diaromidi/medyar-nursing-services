<?php
/**
 * Patient order detail — timeline + nurse + actions.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d         = MNS_Static_Fixtures::get( 'patient/order_view' );
$timeline  = isset( $d['timeline'] ) && is_array( $d['timeline'] ) ? $d['timeline'] : array();
$nurse     = isset( $d['nurse'] ) && is_array( $d['nurse'] ) ? $d['nurse'] : array();
$med       = isset( $d['medication'] ) && is_array( $d['medication'] ) ? $d['medication'] : array();
$orders_url = MNS_Pages::panel_url( 'patient', 'orders' );
?>
<p class="mns-breadcrumb"><a href="<?php echo esc_url( $orders_url ); ?>"><?php echo esc_html__( 'سفارش‌های من', 'medyar-nursing-services' ); ?></a></p>

<header class="mns-order-head">
	<div>
		<h1 class="mns-dash__title" style="margin-bottom:8px"><?php echo esc_html( (string) ( $d['service'] ?? __( 'جزئیات سفارش', 'medyar-nursing-services' ) ) ); ?></h1>
		<p class="mns-dash__lead" style="margin:0" dir="ltr"><?php echo esc_html( (string) ( $d['tracking'] ?? '' ) ); ?></p>
	</div>
	<span class="mns-status mns-status--<?php echo esc_attr( (string) ( $d['tone'] ?? 'waiting' ) ); ?>"><?php echo esc_html( (string) ( $d['status_label'] ?? '' ) ); ?></span>
</header>

<section class="mns-card" aria-labelledby="mns-tl-title">
	<h2 id="mns-tl-title" class="mns-card__title"><?php echo esc_html__( 'مسیر سفارش', 'medyar-nursing-services' ); ?></h2>
	<ol class="mns-timeline">
		<?php foreach ( $timeline as $item ) : ?>
			<?php
			$state = (string) ( $item['state'] ?? 'todo' );
			$class = 'mns-timeline__item';
			if ( 'done' === $state ) {
				$class .= ' is-done';
			} elseif ( 'current' === $state ) {
				$class .= ' is-current';
			}
			?>
			<li class="<?php echo esc_attr( $class ); ?>">
				<span class="mns-timeline__dot" aria-hidden="true"></span>
				<div>
					<p class="mns-timeline__label"><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></p>
					<p class="mns-timeline__at"><?php echo esc_html( (string) ( $item['at'] ?? '' ) ); ?></p>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
</section>

<?php if ( ! empty( $nurse ) ) : ?>
	<section class="mns-card mns-nurse-card" aria-labelledby="mns-nurse-title">
		<h2 id="mns-nurse-title" class="mns-card__title"><?php echo esc_html__( 'پرستار تخصیص‌یافته', 'medyar-nursing-services' ); ?></h2>
		<p class="mns-nurse-card__name"><?php echo esc_html( (string) ( $nurse['name'] ?? '' ) ); ?></p>
		<p class="mns-nurse-card__meta"><?php echo esc_html( sprintf( /* translators: rating */ __( 'میانگین امتیاز %s', 'medyar-nursing-services' ), (string) ( $nurse['rating'] ?? '—' ) ) ); ?> · <?php echo esc_html( (string) ( $d['nurse_gender'] ?? '' ) ); ?></p>
		<?php if ( ! empty( $nurse['phone'] ) ) : ?>
			<a class="mns-btn-outline" href="<?php echo esc_url( 'tel:' . preg_replace( '/\D+/', '', (string) $nurse['phone'] ) ); ?>"><?php echo esc_html__( 'تماس', 'medyar-nursing-services' ); ?></a>
		<?php endif; ?>
	</section>
<?php endif; ?>

<section class="mns-card">
	<h2 class="mns-card__title"><?php echo esc_html__( 'خلاصه مراجعه', 'medyar-nursing-services' ); ?></h2>
	<ul class="mns-kv">
		<li><span><?php echo esc_html__( 'بیمار', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['patient'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'زمان', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['scheduled'] ?? '' ) ); ?></strong></li>
		<li><span><?php echo esc_html__( 'آدرس', 'medyar-nursing-services' ); ?></span><strong><?php echo esc_html( (string) ( $d['address'] ?? '' ) ); ?></strong></li>
	</ul>
</section>

<?php if ( ! empty( $med['enabled'] ) ) : ?>
	<section class="mns-card mns-med-lane" aria-labelledby="mns-med-title">
		<h2 id="mns-med-title" class="mns-card__title"><?php echo esc_html__( 'خط دارو', 'medyar-nursing-services' ); ?></h2>
		<p><span class="mns-status mns-status--progress"><?php echo esc_html( (string) ( $med['status'] ?? '' ) ); ?></span></p>
		<p class="mns-field-hint"><?php echo esc_html( (string) ( $med['hint'] ?? '' ) ); ?></p>
	</section>
<?php endif; ?>

<div class="mns-order-actions">
	<?php if ( ! empty( $d['invoice_url'] ) ) : ?>
		<a class="mns-btn-outline" href="<?php echo esc_url( (string) $d['invoice_url'] ); ?>"><?php echo esc_html__( 'مشاهده فاکتور', 'medyar-nursing-services' ); ?></a>
	<?php endif; ?>
	<?php if ( ! empty( $d['can_repick'] ) && ! empty( $d['reorder_url'] ) ) : ?>
		<a class="mns-btn-outline" href="<?php echo esc_url( (string) $d['reorder_url'] ); ?>"><?php echo esc_html__( 'همان پرستار', 'medyar-nursing-services' ); ?></a>
	<?php endif; ?>
	<?php if ( ! empty( $d['reorder_url'] ) ) : ?>
		<a class="mns-submit-btn" style="width:auto;min-height:44px;padding:10px 20px" href="<?php echo esc_url( (string) $d['reorder_url'] ); ?>"><?php echo esc_html__( 'درخواست مجدد', 'medyar-nursing-services' ); ?></a>
	<?php endif; ?>
</div>
