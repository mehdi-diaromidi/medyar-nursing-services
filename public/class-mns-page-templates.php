<?php
/**
 * Register and load MNS page templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Page_Templates {

	/** @var self|null */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		add_filter( 'theme_page_templates', array( $this, 'register_templates' ) );
		add_filter( 'template_include', array( $this, 'load_template' ) );
	}

	/**
	 * @param array<string,string> $templates Theme templates.
	 * @return array<string,string>
	 */
	public function register_templates( array $templates ): array {
		return array_merge( $templates, MNS_Pages::templates() );
	}

	/**
	 * @param string $template Current template path.
	 */
	public function load_template( string $template ): string {
		if ( ! is_singular( 'page' ) ) {
			return $template;
		}

		$slug = (string) get_page_template_slug();
		if ( ! MNS_Pages::is_mns_template( $slug ) ) {
			return $template;
		}

		$file = MNS_PLUGIN_DIR . 'public/templates/' . $slug;
		if ( is_readable( $file ) ) {
			MNS_Assets::enqueue_public();
			return $file;
		}

		return $template;
	}
}
