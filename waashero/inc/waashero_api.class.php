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
        $authorization = "Authorization: Bearer ".WAASHERO_CLIENT_API_KEY;
        $url = WAASHERO_CLIENT_API_URL. $endpoint. WAASHERO_CLIENT_SERVER_ID;

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
            // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
            // curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string ); 

            // $output contains the output string 
            $output = curl_exec($ch); 
            
            // close curl resource to free up system resources 
            curl_close( $ch );      

            $result = json_decode( $output, true );
          
            return null; 
        }
        catch( Exception $e ) {
            return null;
        }
         
     }

    
}

return new Waashero_Api();