<?php
defined('ABSPATH') OR exit;
/**
 * waashero_ajax short summary.
 *
 * waashero_ajax description.
 *
 * @version 1.0
 * @author BatoPC
 */
class Waashero_Ajax
{

    /**
     * Calls function to add domain alias
     *
     * @return void
     */
    public static function waashero_add_domain_alias() {

        $domain = $_POST['domain'];
        $result = Waashero_Api::AddDomainAlias( $domain ); 
        wp_send_json( $result );       
    }


    public static function waashero_welcome_tour(){
        
        $options = Waashero_Options::get_options();
        $options['welcome_tour'] = 1;
        Waashero_Options::save_options($options);       
        $result = array('success'  => true);
        wp_send_json($result);   
    }
}