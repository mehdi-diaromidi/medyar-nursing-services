<?php
/**
 * Map-based autoloader for MNS_* classes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Autoloader {

	/**
	 * Class => relative path from plugin root.
	 *
	 * @var array<string,string>
	 */
	private static $map = array(
		'MNS_Plugin'     => 'includes/class-mns-plugin.php',
		'MNS_Activator'  => 'includes/class-mns-activator.php',
		'MNS_Roles'      => 'includes/class-mns-roles.php',
		'MNS_Config'     => 'includes/class-mns-config.php',
		'MNS_Admin'      => 'admin/class-mns-admin.php',
		'MNS_Admin_Menu' => 'admin/class-mns-admin-menu.php',
		'MNS_Admin_Ui'   => 'admin/class-mns-admin-ui.php',
		'MNS_Public'     => 'public/class-mns-public.php',
		'MNS_Assets'     => 'public/class-mns-assets.php',
		'MNS_Rewrite'    => 'public/class-mns-rewrite.php',
	);

	public static function register(): void {
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * @param string $class Class name.
	 */
	public static function load( string $class ): void {
		if ( ! isset( self::$map[ $class ] ) ) {
			return;
		}
		$file = MNS_PLUGIN_DIR . self::$map[ $class ];
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
