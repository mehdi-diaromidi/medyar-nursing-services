<?php
/**
 * Template Name: MNS — کاتالوگ خدمات پرستاری
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$view = MNS_PLUGIN_DIR . 'public/views/catalog/archive.php';
if ( is_readable( $view ) ) {
	require $view;
}

get_footer();
