<?php
defined('ABSPATH') OR exit;

require_once WAASHERO_DIR . '/vendor/autoload.php';
use eftec\bladeone\BladeOne;

/**
 * waashero_api short summary.
 *
 * waashero_api description.
 *
 * @version 1.0
 * @author Waashero
 */
class Waashero_Dns_Manager
{

    public $records = [];
    
    public function __construct() {
        
        if ( $this->uses_waas_builder() ){
            add_action( 'admin_menu', [$this, 'loadDnsManagerMenu'] );
            $this->getRecords();
        }
        
    }

    /**
     * Checks if this site is hosted on Wp builder pro.com or not
     *
     * @since 1.7.3
     * @return bool
     */
    public function uses_waas_builder() {

        return defined( 'WAASHERO_CLIENT_API_KEY' ) && WAASHERO_CLIENT_API_KEY;

    }

    /**
     * Checks if this site is hosted on Wp builder pro.com or not
     *
     * @since 1.7.3
     * @return bool
     */
    public function loadDnsManagerMenu() {
    
            add_menu_page( 
                __('Dns Manager'), 
                'Dns Manager', 
                'manage_option', 
                'wh-dns-manager', 
                [$this, 'loadDnsManagerPage'], 
                '', 
                null 
            );
       
    }

     /**
     * Checks if this site is hosted on Wp builder pro.com or not
     *
     * @since 1.7.3
     * @return bool
     */
    public function loadDnsManagerPage() {

        $views = WAASHERO_DIR . '/views';
        $cache = WAASHERO_DIR . '/cache';

        if ( file_exists( $views.'/dns_manager.blade.php' ) ) {
            
            $blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);
            echo $blade->run( "dns_manager", [ "records" => $this->records ] );

        }
    }

    public function getRecords(){
        $this->records = Waashero_Api::getDnsRecords( get_current_blog_id() );
    }

}

return new Waashero_Dns_Manager();