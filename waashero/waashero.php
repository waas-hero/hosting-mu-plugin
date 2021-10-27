<?php
/*
* Plugin Name: Waashero
* Plugin URI: https://waashero.com
* Description: High performance one click implementation of object cache and Google Cloud CDN for Waashero cloud platform users.
* Version: 1.0.0
* Author: Waashero
* Author URI: https://waashero.com
*/

defined( 'ABSPATH' ) or exit;


/* constants */

define( 'WAASHERO_DIR', dirname( __FILE__ ) );
define( 'WAASHERO_BASE', plugin_basename( __FILE__ ) );

$classes = array( 'Waashero', 'Waashero_Api', 'Waashero_Ajax', 'Waashero_WP_CLI' );
foreach ( $classes as $class ) {
	if ( file_exists( WAASHERO_DIR . '/inc/' . strtolower( $class ) . '.class.php' ) ) {
		require sprintf(
			'%s/inc/%s.class.php',
			WAASHERO_DIR,
			strtolower( $class )
		);
	}
}


if ( defined( 'WP_CLI' ) && WP_CLI ) {

	// Register CLI cmd
	if ( method_exists( 'WP_CLI', 'add_command' ) ) {
		WP_CLI::add_command( 'waashero', 'Waashero_WP_CLI' );

	}
}

if ( class_exists( 'WP_Ultimo' ) && class_exists( 'WU_Domain_Mapping_Hosting_Support' ) ) {
	$class = 'Waashero_Hosting_Support';
	require sprintf(
		'%s/inc/%s.class.php',
		WAASHERO_DIR,
		strtolower( $class )
	);

}
