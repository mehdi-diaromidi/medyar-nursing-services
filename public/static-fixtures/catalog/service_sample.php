<?php
/**
 * Backward-compatible alias — prefer catalog/services.php.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all = require __DIR__ . '/services.php';
return is_array( $all ) && isset( $all['serum'] ) && is_array( $all['serum'] ) ? $all['serum'] : array();
