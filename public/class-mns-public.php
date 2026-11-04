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
		MNS_Page_Templates::instance()->init();
		add_filter( 'document_title_parts', array( $this, 'filter_document_title' ) );
		add_action( 'wp_footer', array( $this, 'render_dev_tools' ), 40 );
	}

	/**
	 * @param array<string,string> $parts Title parts.
	 * @return array<string,string>
	 */
	public function filter_document_title( array $parts ): array {
		$panel = MNS_Pages::current_panel();
		if ( '' === $panel ) {
			$slug = get_page_template_slug();
			if ( 'mns-patient.php' === $slug ) {
				$panel = 'patient';
			} elseif ( 'mns-nurse.php' === $slug ) {
				$panel = 'nurse';
			}
		}
		if ( '' === $panel ) {
			return $parts;
		}
		$page  = MNS_Pages::current_page( $panel );
		$titles = MNS_UI::page_titles( $panel );
		if ( isset( $titles[ $page ] ) ) {
			$parts['title'] = $titles[ $page ];
		} elseif ( 'not-found' === $page ) {
			$parts['title'] = MNS_Copy::t( 'state.not_found_title' );
		}
		return $parts;
	}

	public function render_dev_tools(): void {
		if ( ! MNS_Dev::enabled() ) {
			return;
		}
		$panel = MNS_Pages::current_panel();
		if ( '' === $panel ) {
			$slug = get_page_template_slug();
			if ( 'mns-patient.php' === $slug ) {
				$panel = 'patient';
			} elseif ( 'mns-nurse.php' === $slug ) {
				$panel = 'nurse';
			} else {
				return;
			}
		}
		$page = MNS_Pages::current_page( $panel );
		MNS_Dev::render_switcher( $panel, $page );
	}
}
