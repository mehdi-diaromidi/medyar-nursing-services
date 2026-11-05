<?php
/**
 * Generic admin section stub (Phase 6).
 *
 * @var string $mns_admin_title
 * @var string $mns_admin_lead
 * @var string $mns_admin_body
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = isset( $mns_admin_title ) ? (string) $mns_admin_title : __( 'خدمات پرستاری', 'medyar-nursing-services' );
$lead  = isset( $mns_admin_lead ) ? (string) $mns_admin_lead : '';
$body  = isset( $mns_admin_body ) ? (string) $mns_admin_body : __( 'این بخش در اسکلت استاتیک آماده است؛ اتصال دامنه در فاز بعدی.', 'medyar-nursing-services' );
?>
<div class="wrap mns-admin">
	<p class="mns-admin__crumb"><a href="<?php echo esc_url( admin_url( 'admin.php?page=mns-dashboard' ) ); ?>"><?php echo esc_html__( 'خدمات پرستاری', 'medyar-nursing-services' ); ?></a></p>
	<h1 class="mns-admin__title"><?php echo esc_html( $title ); ?></h1>
	<?php if ( '' !== $lead ) : ?>
		<p class="mns-admin__lead"><?php echo esc_html( $lead ); ?></p>
	<?php endif; ?>
	<div class="mns-admin-card">
		<p><?php echo esc_html( $body ); ?></p>
		<p class="description"><?php echo esc_html__( 'حالت UI استاتیک — بدون ذخیره واقعی.', 'medyar-nursing-services' ); ?></p>
	</div>
</div>
