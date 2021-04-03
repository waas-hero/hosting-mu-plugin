<?php
/**
 * Plugin Name: WaaS Hero Hosting App Configurations
 * Description: This plugin stores all media to cloud. High performance one click implementation of object cache and Google Cloud CDN for Waashero cloud platform users.
 * Author:      J Hanlon
 * License:     GNU General Public License v3 or later
 * Plugin URI: https://waashero.com
 * Version: 1.0.0

 * Author URI: https://waashero.com
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * text-domain: waas-mu-config
 */

// Basic security, prevents file from being loaded directly.
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );


function waas_hero_mu_init() {
    if( !file_exists( WPMU_PLUGIN_DIR. "/waashero-config.php" ) ) {
        $file = fopen( WPMU_PLUGIN_DIR. "/waashero-config.php", 'a' );
        fclose( $file );
        file_put_contents( WPMU_PLUGIN_DIR. "/waashero-config.php", "<?php \ndefine( 'sm_buckets', 'wb-storage' );\n" );
    }
    global $wpdb;
    $table_name = $wpdb->options;
    $query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

    if ( !class_exists( 'WU_Domain_Mapping_Hosting_Support' ) && class_exists('WP_Ultimo') ) {
        $instance = WP_Ultimo::get_instance();
        $version  = $instance->version;
      
        if ( !empty( $version ) && $version < 2 ) {
            
            require WP_PLUGIN_DIR.'/wp-ultimo/inc/class-wu-domain-mapping-hosting-support.php';
        }
        
       
        
    }
    
    if ( !wp_installing() && $table_name && $wpdb->get_var( $query ) == $table_name ) {
        require_once WPMU_PLUGIN_DIR.'/waashero/wp-stateless/wp-stateless-media.php';
        require WPMU_PLUGIN_DIR.'/waashero/waashero.php';
    }

    if( !class_exists( 'WPConfigTransformer' ) && file_exists( WPMU_PLUGIN_DIR.'/waashero/wp-stateless/wp-config-transformer/src/WPConfigTransformer.php' ) ) {
        require_once WPMU_PLUGIN_DIR.'/waashero/wp-stateless/wp-config-transformer/src/WPConfigTransformer.php';
    }
}
add_action( 'plugins_loaded', 'waas_hero_mu_init' );


/* Force new sites to https */
add_filter( 'wp_initialize_site_args', function( $args, $site ) {
	$url = 'https://' . $site->domain . $site->path;

	$args['options']['home']    = $url;
	$args['options']['siteurl'] = $url;

	return $args;
}, 10, 2 );
