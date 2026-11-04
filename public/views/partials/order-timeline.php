<?php
/**
 * Order timeline. Compact (horizontal 5 stages) or full (vertical).
 *
 * @var string               $slug
 * @var string               $variant   compact|full
 * @var string               $audience  patient|nurse
 * @var array<int,array{label?:string,at?:string,state?:string}> $events Optional full events.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$slug     = isset( $slug ) ? (string) $slug : MNS_Order_Status::SUBMITTED;
$variant  = isset( $variant ) ? (string) $variant : 'full';
$audience = isset( $audience ) ? (string) $audience : 'patient';
$info     = MNS_Order_Status::get( $slug );
$stages   = MNS_Order_Status::stages();
$current  = (int) $info['stage'];
$terminal = ! empty( $info['terminal'] );

if ( 'compact' === $variant ) :
	?>
	<ol class="mns-tl mns-tl--compact" aria-label="<?php echo esc_attr__( 'مراحل سفارش', 'medyar-nursing-services' ); ?>">
		<?php foreach ( $stages as $i => $label ) : ?>
			<?php
			$n     = $i + 1;
			$class = 'mns-tl__step';
			if ( $terminal ) {
				$class .= ' is-muted';
			} elseif ( $n < $current ) {
				$class .= ' is-done';
			} elseif ( $n === $current ) {
				$class .= ' is-current';
			}
			?>
			<li class="<?php echo esc_attr( $class ); ?>">
				<span class="mns-tl__dot" aria-hidden="true">
					<?php
					if ( ! $terminal && $n < $current ) {
						echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo esc_html( MNS_Format::digits( $n ) );
					}
					?>
				</span>
				<span class="mns-tl__label"><?php echo esc_html( $label ); ?></span>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php
	return;
endif;

$events = ( isset( $events ) && is_array( $events ) ) ? $events : array();
if ( empty( $events ) ) {
	foreach ( $stages as $i => $label ) {
		$n     = $i + 1;
		$state = 'todo';
		if ( $terminal ) {
			$state = 'muted';
		} elseif ( $n < $current ) {
			$state = 'done';
		} elseif ( $n === $current ) {
			$state = 'current';
		}
		$events[] = array(
			'label' => $label,
			'at'    => '',
			'state' => $state,
		);
	}
}
?>
<ol class="mns-tl mns-tl--full" aria-label="<?php echo esc_attr__( 'مسیر سفارش', 'medyar-nursing-services' ); ?>">
	<?php foreach ( $events as $item ) : ?>
		<?php
		$state = (string) ( $item['state'] ?? 'todo' );
		$class = 'mns-tl__item';
		if ( 'done' === $state ) {
			$class .= ' is-done';
		} elseif ( 'current' === $state ) {
			$class .= ' is-current';
		} elseif ( 'muted' === $state ) {
			$class .= ' is-muted';
		}
		?>
		<li class="<?php echo esc_attr( $class ); ?>">
			<span class="mns-tl__dot" aria-hidden="true">
				<?php
				if ( 'done' === $state ) {
					echo mns_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</span>
			<div class="mns-tl__body">
				<p class="mns-tl__label"><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></p>
				<?php if ( ! empty( $item['at'] ) ) : ?>
					<p class="mns-tl__at"><?php echo esc_html( (string) $item['at'] ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ol>
<?php if ( ! empty( $info[ $audience ] ) ) : ?>
	<p class="mns-tl__sentence"><?php echo esc_html( (string) $info[ $audience ] ); ?></p>
<?php endif; ?>
