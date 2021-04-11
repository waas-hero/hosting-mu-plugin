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

            add_action( 'wp_ajax_record_list', [$this, 'record_list'] ); 
            add_action( 'wp_ajax_record_form', [$this, 'record_form'] ); 
            add_action( 'wp_ajax_record_delete', [$this, 'record_delete'] );
          
            add_action( 'admin_enqueue_scripts', [$this, 'custom_admin_open_sans_font'] );

            $this->getRecords();
        }
        
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

        wp_enqueue_style( 'wpbuilderpro-font', 'https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&#8217', false);
    }

    // WordPress Custom Font @ Admin
    public function custom_admin_open_sans_font() {
        wp_enqueue_style( ' add_google_fonts ', "https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap", false );
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
            echo $blade->run( "dns_manager", [ "records" => $this->records ] );

        }
    }

    public function getRecords(){
        $this->records = Waashero_Api::getDnsRecords();
    }

    public function record_list(){
        echo Waashero_Api::getDnsRecords();
        die();
    }

    public function record_delete(){
        
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wpbuilderpro-nonce' ) ) {
            die( __( 'Security check' ) ); 
        }
        if( ! isset($_POST['hostname']) && $_POST['hostname']){
            echo json_encode(['success' => false, 'message' => 'Please enter a hostname.']);
            die();
        }
        if( ! isset($_POST['value']) && $_POST['value']){
            echo json_encode(['success' => false, 'message' => 'Please enter a value.']);
            die();
        }
        if( ! isset($_POST['ttl']) && $_POST['ttl']){
            echo json_encode(['success' => false, 'message' => 'Please enter a ttl value.']);
            die();
        }
        if( ! isset($_POST['type']) && $_POST['type']){
            echo json_encode(['success' => false, 'message' => 'Please enter a type.']);
            die();
        }
     
        $response = Waashero_Api::deleteDnsRecord([
            'hostname' =>  sanitize_text_field($_POST['hostname']),
            'value' =>  sanitize_text_field($_POST['value']),
            'ttl' =>  sanitize_text_field($_POST['ttl']),
            'type' =>  sanitize_text_field($_POST['type']),
            'domain' => home_url()
        ]);

        echo json_encode($response);
        die();
    }



    public function record_form(){
   
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wpbuilderpro-nonce' ) ) {
            die( __( 'Security check' ) ); 
        }
        if( ! isset($_POST['hostname']) && $_POST['hostname']){
            echo json_encode(['success' => false, 'message' => 'Please enter a hostname.']);
            die();
        }
        if( ! isset($_POST['value']) && $_POST['value']){
            echo json_encode(['success' => false, 'message' => 'Please enter a value.']);
            die();
        }
        if( ! isset($_POST['ttl']) && $_POST['ttl']){
            echo json_encode(['success' => false, 'message' => 'Please enter a ttl value.']);
            die();
        }
        if( ! isset($_POST['type']) && $_POST['type']){
            echo json_encode(['success' => false, 'message' => 'Please enter a type.']);
            die();
        }

        $response = Waashero_Api::addDnsRecord([
            'hostname' =>  sanitize_text_field($_POST['hostname']),
            'value' =>  sanitize_text_field($_POST['value']),
            'ttl' =>  sanitize_text_field($_POST['ttl']),
            'type' =>  sanitize_text_field($_POST['type']),
            'domain' => home_url()
        ]);

        echo json_encode($response);

        die();
    }

}

return new Waashero_Dns_Manager();