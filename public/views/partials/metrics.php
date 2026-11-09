<?php
/**
 * Metrics / KPI row partial.
 *
 * @var array<int,array{label?:string,value?:string,hero?:bool}> $mns_metrics
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = ( isset( $mns_metrics ) && is_array( $mns_metrics ) ) ? $mns_metrics : array();
if ( empty( $items ) ) {
	return;
}
?>
<div class="mns-metrics">
	<?php foreach ( $items as $item ) : ?>
		<?php
		$hero  = ! empty( $item['hero'] );
		$class = 'mns-metric' . ( $hero ? ' mns-metric--hero' : '' );
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<span class="mns-metric__label"><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></span>
			<span class="mns-metric__value"><?php echo esc_html( (string) ( $item['value'] ?? '' ) ); ?></span>
		</div>
	<?php endforeach; ?>
</div>
