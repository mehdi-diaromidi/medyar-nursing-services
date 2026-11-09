<?php
/**
 * Shared admin UI helpers (tabs etc. — filled in later phases).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Admin_Ui {

	/**
	 * Render WP-style nav tabs.
	 *
	 * @param array<string,string> $tabs    slug => label.
	 * @param string               $current Current tab slug.
	 * @param string               $base_url Base admin URL with page arg.
	 */
	public static function tabs( array $tabs, string $current, string $base_url ): void {
		if ( empty( $tabs ) ) {
			return;
		}

		echo '<nav class="nav-tab-wrapper wp-clearfix" aria-label="' . esc_attr__( 'Secondary menu', 'medyar-nursing-services' ) . '">';
		foreach ( $tabs as $slug => $label ) {
			$url   = add_query_arg( 'tab', $slug, $base_url );
			$class = 'nav-tab' . ( $slug === $current ? ' nav-tab-active' : '' );
			printf(
				'<a href="%1$s" class="%2$s"%3$s>%4$s</a>',
				esc_url( $url ),
				esc_attr( $class ),
				$slug === $current ? ' aria-current="page"' : '',
				esc_html( $label )
			);
		}
		echo '</nav>';
	}
}
