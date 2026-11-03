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

get_header();

$panel = 'patient';
$page  = MNS_Pages::current_page( $panel );
$user  = wp_get_current_user();

$layout = MNS_PLUGIN_DIR . 'public/views/patient/layout.php';
if ( is_readable( $layout ) ) {
	require $layout;
}

get_footer();
