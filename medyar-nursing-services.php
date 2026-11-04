<?php
/**
 * Plugin Name: Medyar Nursing Services
 * Description: سامانه سفارش، تخصیص و انجام خدمات پرستاری در منزل (مدیار)
 * Version: 0.1.5
 * Author: Medyar
 * Text Domain: medyar-nursing-services
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MNS_VERSION', '0.1.5' );
define( 'MNS_DB_VERSION', '0.0.0' );
define( 'MNS_PLUGIN_FILE', __FILE__ );
define( 'MNS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MNS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MNS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'MNS_UI_STATIC' ) ) {
	define( 'MNS_UI_STATIC', true );
}

require_once MNS_PLUGIN_DIR . 'includes/class-mns-autoloader.php';
MNS_Autoloader::register();

register_activation_hook( __FILE__, array( 'MNS_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MNS_Activator', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		MNS_Plugin::instance()->init();
	}
);
