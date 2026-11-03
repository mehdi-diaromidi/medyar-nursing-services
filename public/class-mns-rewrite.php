<?php
/**
 * Rewrite rules stub (real panel URLs in phase 3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Rewrite {

	/** @var self|null */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Placeholder for query vars mns_panel / mns_page.
	 */
	public function register(): void {
		// Intentionally empty in phase 0.
	}
}
