<?php
/**
 * Page header: title + lead + primary action.
 *
 * @var string $title
 * @var string $lead
 * @var string $actions   Raw HTML for actions (already escaped by caller).
 * @var string $back_url
 * @var string $back_label
 * @var string $badge_html Raw HTML for a status badge.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title      = isset( $title ) ? (string) $title : '';
$lead       = isset( $lead ) ? (string) $lead : '';
$actions    = isset( $actions ) ? (string) $actions : '';
$back_url   = isset( $back_url ) ? (string) $back_url : '';
$back_label = isset( $back_label ) ? (string) $back_label : MNS_Copy::t( 'action.back' );
$badge_html = isset( $badge_html ) ? (string) $badge_html : '';
?>
<header class="mns-page-header">
	<?php if ( '' !== $back_url ) : ?>
		<p class="mns-breadcrumb">
			<a href="<?php echo esc_url( $back_url ); ?>">
				<span class="mns-breadcrumb__icon" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php echo esc_html( $back_label ); ?>
			</a>
		</p>
	<?php endif; ?>
	<div class="mns-page-header__row">
		<div class="mns-page-header__text">
			<?php if ( '' !== $badge_html ) : ?>
				<div class="mns-page-header__badge"><?php echo $badge_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
			<h1 class="mns-dash__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( '' !== $lead ) : ?>
				<p class="mns-dash__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $actions ) : ?>
			<div class="mns-page-header__actions"><?php echo $actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>
	</div>
</header>
