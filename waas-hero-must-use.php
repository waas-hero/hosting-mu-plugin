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

/* Prefix your custom functions!
 *
 * Function names must be unique in PHP.
 * In order to make sure the name of your function does not
 * exist anywhere else in WordPress, or in other plugins,
 * give your function a unique custom prefix.
 * Example prefix: wpr20151231
 * Example function name: wpr20151231__do_something
 *
 * For the rest of your function name after the prefix,
 * make sure it is as brief and descriptive as possible.
 * When in doubt, do not fear a longer function name if it
 * is going to remind you at once of what the function does.
 * Imagine you’ll be reading your own code in some years, so
 * treat your future self with descriptive naming. ;)
 */

/**
 * Pass your custom function to the wp_rocket_loaded action hook.
 *
 * Note: wp_rocket_loaded itself is hooked into WordPress’ own
 * plugins_loaded hook.
 * Depending what kind of functionality your custom plugin
 * should implement, you can/should hook your function(s) into
 * different action hooks, such as for example
 * init, after_setup_theme, or template_redirect.
 * 
 * Learn more about WordPress actions and filters here:
 * https://developer.wordpress.org/plugins/hooks/
 *
 * @param string 'wp_rocket_loaded'         Hook name to hook function into
 * @param string 'yourprefix__do_something' Function name to be hooked
 */
function waas_hero_mu_init() {
    if( !file_exists( WPMU_PLUGIN_DIR. "/waashero-config.php" ) ) {
        $file = fopen( WPMU_PLUGIN_DIR. "/waashero-config.php", 'a' );
        fclose( $file );
        file_put_contents( WPMU_PLUGIN_DIR. "/waashero-config.php", "<?php \ndefine( 'sm_bucket', 'wb-storage' );\n" );
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