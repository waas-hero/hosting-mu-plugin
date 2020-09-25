<?php

defined('ABSPATH') OR exit;

class Waashero_Options {
    

    private static $default = array(
            'enable_cdn' => '1',
            'url'        => '',           
            'excludes'   => '',
            'relative'   => '1',
        
            'enable_object_cache' => '1',
            'enable_debug'        => '0',
            'load_balancer_micro_cache'         => '0',
            'load_balancer_micro_cache_seconds' => '30',
            'enable_opcache'  => '1',
            'development_mode'=> '0',
            'smtp_configured' => '0',
            'allow_per_site_config'=> '0'
        );

    public static function get_options( $global = false ) {
       
        $options = false;
        if( get_site_option( 'waashero' ) != NULL ) {
            $options = get_site_option( 'waashero' );
        }

        if( !$options ) {
            add_site_option( 'waashero', self::$default );
            $options = self::$default;
        }

        $current_options = wp_parse_args( $options );

        if($global){
            return $current_options;
        }

        $option = self::$default;
        
        if( is_multisite() ) {

            if( is_network_admin() || is_main_site() ) {
                $option = $current_options;              
            } else {

                if( $current_options['allow_per_site_config'] == 1 ) {
                    $option = wp_parse_args( get_option( 'waashero' ), self::$default );                   
                } else {
                    $option =$current_options;               
                }               
            }            
        } else {
            $option = $current_options;         
        }

        
        $option['url'] = 'https://'.WAASHERO_CDN_HOSTNAME;  

        //fix
        if( $option['development_mode'] == 2 ) {
            $option['development_mode'] = 0;
        }

        return $option;
        
    }


   

    public static function save_options( $options ) {
        
        $current_options = wp_parse_args( get_site_option('waashero'), self::$default);  


        if( is_multisite() ) {

            if( is_network_admin() || is_main_site() ) {
                update_site_option( 'waashero', $options );             
            } else {

                if( $current_options['allow_per_site_config'] == 1 ) {
                    update_option( 'waashero', $options );                
                } else {
                    update_site_option( 'waashero', $options );                
                }               
            }            
        } else {
            update_site_option( 'waashero', $options );          
        }

    }

}
return new Waashero_Options();