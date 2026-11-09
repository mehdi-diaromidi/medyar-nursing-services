<?php
/**
 * Uninstall handler. Destructive cleanup is gated until phase 11.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( ! defined( 'MNS_DELETE_DATA_ON_UNINSTALL' ) || ! MNS_DELETE_DATA_ON_UNINSTALL ) {
	return;
}

// Phase 11: delete options, custom tables, and caps when explicitly enabled.
