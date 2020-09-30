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



    public static function waashero_notifications() {

        $result = Waashero_Api::getWaasheroNotifications();
         wp_send_json( $result );
    }
}

return new Waashero_Ajax();