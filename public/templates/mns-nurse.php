<?php
/**
 * Template Name: MNS — پنل پرستار
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$panel = 'nurse';
$page  = MNS_Pages::current_page( $panel );
$user  = wp_get_current_user();

$layout = MNS_PLUGIN_DIR . 'public/views/nurse/layout.php';
if ( is_readable( $layout ) ) {
	require $layout;
}

get_footer();
