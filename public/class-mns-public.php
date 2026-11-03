<?php
/**
 * Front-end bootstrap (panels/templates land in phase 3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Public {

	/** @var self|null */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void {
		// Phase 3: page templates, shortcodes, panel gates.
	}
}
