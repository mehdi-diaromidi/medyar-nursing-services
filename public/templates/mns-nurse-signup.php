<?php
/**
 * Template Name: MNS — عضویت پرستار
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$view = MNS_PLUGIN_DIR . 'public/views/nurse/signup-stub.php';
if ( is_readable( $view ) ) {
	require $view;
}

get_footer();
