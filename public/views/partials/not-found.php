<?php
/**
 * Soft not-found inside a panel.
 *
 * @var string $panel
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$panel = isset( $panel ) ? (string) $panel : 'patient';
MNS_UI::error_state(
	array(
		'title'       => MNS_Copy::t( 'state.not_found_title' ),
		'text'        => MNS_Copy::t( 'state.not_found_body' ),
		'retry_label' => MNS_Copy::t( 'action.go_dashboard' ),
		'retry_url'   => MNS_Pages::panel_url( $panel, 'dashboard' ),
	)
);
