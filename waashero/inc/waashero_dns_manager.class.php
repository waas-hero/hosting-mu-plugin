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
    public $tab = 'A';
    
    public function __construct() {

        if ( $this->uses_waas_builder() ){
            add_action( 'admin_menu', [$this, 'loadDnsManagerMenu'] );
            add_action( 'admin_enqueue_scripts', [$this, 'ajax_form_scripts'] );
            add_action( 'wp_ajax_record_form', [$this, 'record_form'] ); 
   
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
                'edit_posts', 
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

        if ( file_exists( WAASHERO_BLADE_VIEWS_DIR.'/dns_manager.blade.php' ) ) {
            
            $blade = new BladeOne(WAASHERO_BLADE_VIEWS_DIR, WAASHERO_BLADE_CACHE_DIR, BladeOne::MODE_AUTO);
            echo $blade->run( "dns_manager", [ "records" => $this->records, "tab" => $this->tab ] );

        }
    }

    public function getRecords(){
        $this->records = Waashero_Api::getDnsRecords( get_current_blog_id() );
    }

    public function ajax_form_scripts() {
        wp_register_script( 'wpbuilderpro', '' );
        wp_localize_script( 'wpbuilderpro', 'wpbuilderpro',
            array( 
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'wpbuilderpro-nonce' ),
            )
        );
        wp_enqueue_script( 'wpbuilderpro' );
    }

    public function record_form(){
   
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wpbuilderpro-nonce' ) ) {
            die( __( 'Security check' ) ); 
        }

        $hostname = sanitize_text_field($_POST['hostname']);
        $value = sanitize_text_field($_POST['value']);
        $ttl = sanitize_text_field($_POST['ttl']);
        $site_id = get_current_blog_id();

        if($hostname && $value && $ttl && $site_id){
            echo json_encode(['success' => true, 'message' => 'Record added.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Record not added.']);
        }
        die();
    }

}

return new Waashero_Dns_Manager();