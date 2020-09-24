<?php
/*
* Plugin Name: Waashero
* Plugin URI: https://waashero.com
* Description: High performance one click implementation of object cache and Google Cloud CDN for Waashero cloud platform users.
* Version: 1.0.0
* Author: Waashero
* Author URI: https://waashero.com
*/

defined('ABSPATH') OR exit;


/* constants */

define( 'WAASHERO_DIR', dirname(__FILE__) );
define('WAASHERO_BASE', plugin_basename(__FILE__) );
define('WAASHERO_MIN_WP', '4.0');
if( !defined( 'WAASHERO_CDN_HOSTNAME' ) ) {
	define( 'WAASHERO_CDN_HOSTNAME', "xyz.com" );
}

if( !defined( 'WAASHERO_CLIENT_API_KEY' ) ) {
	define( 'WAASHERO_CLIENT_API_KEY', "xyzabc" );
}

/* loader */
add_action(
	'plugins_loaded',
	array(
		'Waashero',
		'instance'
	)
);





/* autoload init */
spl_autoload_register('Waashero_autoload');

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    // Register CLI cmd
	if ( method_exists( 'WP_CLI', 'add_command' ) ) {
		WP_CLI::add_command( 'waashero', 'Waashero_WP_CLI' ) ;
		
	}
}

if ( class_exists( 'WP_Ultimo' ) && class_exists( 'WU_Domain_Mapping_Hosting_Support' ) ) {
	$class = 'Waashero_Hosting_Support';
	require ( sprintf(
		'%s/inc/%s.class.php',
		WAASHERO_DIR,
		strtolower( $class )
	));
}
/* autoload function */
function Waashero_autoload( $class ) {
	if ( in_array( $class, array( 'Waashero', 'Waashero_Rewriter', 'Waashero_Settings', 'Waashero_Api', 'Waashero_Ajax', 'Waashero_WP_CLI', 'Waashero_Options' ) ) ) {
		require_once(
			sprintf(
				'%s/inc/%s.class.php',
				WAASHERO_DIR,
				strtolower( $class )
			)
		);
	}
   

}