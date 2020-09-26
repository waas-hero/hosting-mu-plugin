<?php
defined('ABSPATH') OR exit;
/**
 * waashero_api short summary.
 *
 * waashero_api description.
 *
 * @version 1.0
 * @author Waashero
 */
class Waashero_Api
{
    
    
    /**
     * waashero_api Gets all domains for a server.
     *
     *
     * @version 1.0
     * @author Waashero
     * @return $domains JSON
     */
     public static  function GetDomains() {
        
        $endpoint      = '/domains/'; //must include endslash
        $authorization = "Authorization: Bearer ".WAASHERO_CLIENT_API_KEY;

         try{
                $ch = curl_init(); 

                // set header with token 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , 'Accept: application/json', $authorization ));
                // set url 
                curl_setopt( $ch, CURLOPT_URL, WAASHERO_CLIENT_API_URL. $endpoint. WAASHERO_CLIENT_SERVER_ID ); 

                //return the transfer as a string 
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
                curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 ); 
                curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );

                // $output contains the output string 
                $output = curl_exec( $ch ); 

                // close curl resource to free up system resources 
                curl_close( $ch );      


                $result = json_decode( $output, true );

                if( $result['success'] == true ){          
                    
                    return $result['domains'];
                }

                return null; 
         }
         catch( Exception $e ) {
             return null;
         }
         
      }
    
    /**
     * Adds domain alias
     *
     * @param [type] $domain
     * @return void
     */
    public static function AddDomainAlias( $domain ) {

        $endpoint = '/ultimo/domain/'; //must include all slashes
        try{

         $notification_key = get_current_user_id().'_domain_notifications';
         $sslcert_notification_response = [];
         $domain_notification_response = [];

         $response =  self::AddDomainAliasHandler($domain , 'POST' ,  $endpoint);
         if( !empty( $response ) ) {
            $domain_notification_response['success'] = $response;
            // Confirm SSL Certificate
            $attempt = 1;
            $endpoint = '/sslcert';
            while( $attempt < 4 ) {
                sleep( $attempt*7 );
                $ssl_response =  self::AddDomainAliasHandler( $domain , 'GET' ,  $endpoint);
                $ssl_response = json_decode( $ssl_response , true );
                if( !empty( $ssl_response['code'] ) && $ssl_response['code'] == '200'){
                    $sslcert_notification_response['success'] = $ssl_response['message'].' for domain'.$domain;
                    break;
                } else {
                    $sslcert_notification_response['error'] = $ssl_response['message'].' for domain'.$domain;
                }
                $attempt ++;
            }
        } else {
            $domain_notification_response['error'] = "Sorry could not add domain ".$domain;
        }

        // Log Api Response for Notifications
        $messages = array_merge($sslcert_notification_response,$domain_notification_response);
        self::setWaasheroNotifications($notification_key , $messages);
        if( wp_doing_ajax() ) {
            $js_response['success'] = true;
            $js_response['message'] = $response;
            echo json_encode($js_response);
            die;
        } else {

            return null;
        }

        } catch( Exception $e ) {

            return null;
        }

    }

    /**
     * Domain handler
     *
     * @param [string] $domain
     * @param string $method
     * @param [string] $endpoint
     * @return void
     */
    private static function AddDomainAliasHandler( $domain , $method = 'POST' ,  $endpoint ) {

        $authorization = "Authorization: Bearer ".WAASHERO_CLIENT_API_KEY;
        $url = WAASHERO_CLIENT_API_URL. $endpoint.( $method !== "POST" ? "?domain=".$domain: WAASHERO_CLIENT_SERVER_ID );

        try{

            $wildcard = false;
            if ( strpos($domain, '*.') === 0 ) {
                $wildcard = true;
            }

            $data = array(
                'domain' => $domain,
                'wildcard' => $wildcard
            );
            $fields_string = http_build_query($data);

            $ch = curl_init();

            // set header with token
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Accept: application/json', $authorization ));
            // set url
            curl_setopt( $ch, CURLOPT_URL, $url );

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            if($method == "POST"){
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string );
            }else{
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            }

            // $output contains the output string
            $output = curl_exec($ch);
            // close curl resource to free up system resources
            curl_close( $ch );
            return  (is_object($output) ? json_decode( $output, true ) :$output);

        }
        catch( Exception $e ) {
            return null;
        }
    }

    /**
     * Sets notifications
     *
     * @param [string] $key
     * @param [arrray] $value
     * @return void
     */
    private static function setWaasheroNotifications( $key , $value ) {

        $messages = [];
        $notificatinos = get_option($key);
        if( !empty( $notificatinos ) ) {
            $notificatinos  = array_merge($notificatinos , $value);

            return update_option($key,$value);
        } else {

            return  update_option($key,$value);
        }
    }

    /**
     * Get json encodedlist of notifications
     *
     * @return void
     */
    public static function getWaasheroNotifications() {
        $notification_key = get_current_user_id().'_domain_notifications';
        $notificatinos = get_option($notification_key);
        if( !empty( $notificatinos ) ) { 
            // Flush Notification on Read
            delete_option($notification_key);
            $js_response['success'] = true;
            $js_response['messages'] = $notificatinos;
            echo json_encode($js_response);
            die;
        }else{
            $js_response['success'] = false;
            $js_response['messages'] = '';
            echo json_encode($js_response);
            die;
        }
    }
}

return new Waashero_Api();