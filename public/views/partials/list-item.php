<?php
/**
 * Compact clickable list row (orders, invoices, surveys…).
 *
 * @var string $url
 * @var string $title
 * @var string $meta
 * @var string $meta2
 * @var string $code
 * @var string $badge_html   Raw HTML (status badge).
 * @var string $chip         Optional secondary chip text (e.g. recurring).
 * @var string $action_label Next action; empty = chevron-only row.
 * @var bool   $action_primary Prefer filled look for pay/survey.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url            = isset( $url ) ? (string) $url : '#';
$title          = isset( $title ) ? (string) $title : '';
$meta           = isset( $meta ) ? (string) $meta : '';
$meta2          = isset( $meta2 ) ? (string) $meta2 : '';
$code           = isset( $code ) ? (string) $code : '';
$badge_html     = isset( $badge_html ) ? (string) $badge_html : '';
$chip           = isset( $chip ) ? (string) $chip : '';
$action_label   = isset( $action_label ) ? (string) $action_label : '';
$action_primary = ! empty( $action_primary );
$detail_only    = in_array( $action_label, array( __( 'جزئیات', 'medyar-nursing-services' ), __( 'مشاهده', 'medyar-nursing-services' ), __( 'پیگیری', 'medyar-nursing-services' ) ), true );
?>
<li class="mns-list-item">
	<a class="mns-list-item__link" href="<?php echo esc_url( $url ); ?>">
		<span class="mns-list-item__main">
			<span class="mns-list-item__top">
				<span class="mns-list-item__title"><?php echo esc_html( $title ); ?></span>
				<?php if ( '' !== $badge_html ) : ?>
					<span class="mns-list-item__badge"><?php echo $badge_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php endif; ?>
			</span>
			<?php if ( '' !== $meta ) : ?>
				<span class="mns-list-item__meta"><?php echo esc_html( $meta ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $meta2 ) : ?>
				<span class="mns-list-item__meta"><?php echo esc_html( $meta2 ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $chip ) : ?>
				<span class="mns-list-item__chip"><?php echo esc_html( $chip ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $code ) : ?>
				<span class="mns-list-item__code" dir="ltr"><?php echo esc_html( MNS_Format::digits( $code ) ); ?></span>
			<?php endif; ?>
		</span>
		<span class="mns-list-item__trail">
			<?php if ( '' !== $action_label && ! $detail_only ) : ?>
				<span class="mns-list-item__action<?php echo $action_primary ? ' mns-list-item__action--primary' : ''; ?>">
					<?php echo esc_html( $action_label ); ?>
				</span>
			<?php endif; ?>
			<span class="mns-list-item__chevron" aria-hidden="true"><?php echo mns_icon( 'chevron', 'mns-icon--dir' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</span>
	</a>
</li>
