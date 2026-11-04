<?php
/**
 * Date-range filter UI (static / mock).
 *
 * @var string $mns_dr_from
 * @var string $mns_dr_to
 * @var string $mns_dr_name_from
 * @var string $mns_dr_name_to
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$from      = isset( $mns_dr_from ) ? (string) $mns_dr_from : '';
$to        = isset( $mns_dr_to ) ? (string) $mns_dr_to : '';
$name_from = isset( $mns_dr_name_from ) ? (string) $mns_dr_name_from : 'date_from';
$name_to   = isset( $mns_dr_name_to ) ? (string) $mns_dr_name_to : 'date_to';
?>
<div class="mns-date-range" role="group" aria-label="<?php echo esc_attr__( 'بازه تاریخ', 'medyar-nursing-services' ); ?>">
	<label class="mns-field-block mns-date-range__field">
		<span class="mns-field-block__label"><?php echo esc_html__( 'از تاریخ', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control">
			<input class="mns-input" type="text" name="<?php echo esc_attr( $name_from ); ?>" value="<?php echo esc_attr( $from ); ?>" inputmode="numeric" placeholder="۱۴۰۳/۰۸/۰۱" data-mns-jdate autocomplete="off" />
		</span>
	</label>
	<label class="mns-field-block mns-date-range__field">
		<span class="mns-field-block__label"><?php echo esc_html__( 'تا تاریخ', 'medyar-nursing-services' ); ?></span>
		<span class="mns-field-block__control">
			<input class="mns-input" type="text" name="<?php echo esc_attr( $name_to ); ?>" value="<?php echo esc_attr( $to ); ?>" inputmode="numeric" placeholder="۱۴۰۳/۰۸/۳۰" data-mns-jdate autocomplete="off" />
		</span>
	</label>
</div>
