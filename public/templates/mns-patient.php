<?php
/**
 * Template Name: MNS — پنل بیمار
 * Template for patient panel (static UI phase).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Printable A4 invoice (download = browser «Save as PDF»): standalone document, no site chrome. */
if ( 'invoice-view' === MNS_Pages::current_page( 'patient' ) && isset( $_GET['mns_print'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	require MNS_PLUGIN_DIR . 'public/views/patient/invoice-print.php';
	return;
}

get_header();

/* After get_header(): WP core reuses the global `$page` while setting up the post. */
$panel = 'patient';
$page  = MNS_Pages::current_page( $panel );
$user  = wp_get_current_user();

$layout = MNS_PLUGIN_DIR . 'public/views/patient/layout.php';
if ( is_readable( $layout ) ) {
	require $layout;
}

get_footer();
