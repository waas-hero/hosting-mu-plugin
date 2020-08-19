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

    
     public static  function GetDomains() {
          

         try{
                $ch = curl_init(); 

                // set url 
                curl_setopt( $ch, CURLOPT_URL, 'https://app.waashero.com/api/client/getdomains?apikey='. urlencode ( WAASHERO_CLIENT_API_KEY ) ); 

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
    public static  function AddDomainAlias( $domain ) {
         

        try{
             
            $wildcard = false;             
            if ( strpos($domain, '*.') === 0 ) {
            $wildcard = true;
            }
             
            $ch = curl_init();            

            // set url 
            curl_setopt($ch, CURLOPT_URL, 'https://app.waashero.com/api/client/adddomainalias'); 

            //return the transfer as a string 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'apikey='.urlencode (WAASHERO_CLIENT_API_KEY). '&domain='.urlencode ( $domain ). '&wildcard='.urlencode ( $wildcard ) ); 

            // $output contains the output string 
            $output = curl_exec($ch); 

            // close curl resource to free up system resources 
            curl_close( $ch );      


            $result = json_decode( $output, true );

            return $result; 
        }
        catch( Exception $e ) {
            return   array('success'  => false, 'error'=> 'unknown');;
        }
         
     }

     public static  function GetBackups(){
          

         try{
             $ch = curl_init(); 

             // set url 
             curl_setopt($ch, CURLOPT_URL, 'https://app.waashero.com/api/client/getbackups?apikey='. urlencode (WAASHERO_CLIENT_API_KEY)); 

             //return the transfer as a string 
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
             curl_setopt($ch, CURLOPT_TIMEOUT, 10);

             // $output contains the output string 
             $output = curl_exec($ch); 

             // close curl resource to free up system resources 
             curl_close($ch);      


             $result = json_decode($output,true);

             if($result['success'] == true){          
                 
                 return $result['backups'];
             }

             return null; 
         }
         catch(Exception $e){
             return null;
         }
         
      }

     public static  function GetCdnCacheInvalidation(){
         

         try{
             $ch = curl_init(); 

             // set url 
             curl_setopt($ch, CURLOPT_URL, 'https://app.waashero.com/api/client/getcdncacheinvalidation?apikey='. urlencode (WAASHERO_CLIENT_API_KEY)); 

             //return the transfer as a string 
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
             curl_setopt($ch, CURLOPT_TIMEOUT, 10);

             // $output contains the output string 
             $output = curl_exec($ch); 

             // close curl resource to free up system resources 
             curl_close($ch);      


             $result = json_decode($output,true);

             if($result['success'] == true){          
                 
                 return $result['tasks'];
             }

             return null; 
         }
         catch(Exception $e){
             return null;
         }
         
     }

     public static  function ManualBackup(){
         

         try{
             $ch = curl_init();            

             // set url 
             curl_setopt($ch, CURLOPT_URL, 'https://app.waashero.com/api/client/manualbackup'); 

             //return the transfer as a string 
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
             curl_setopt($ch, CURLOPT_TIMEOUT, 10);
             curl_setopt($ch, CURLOPT_POST, true); 
             curl_setopt($ch, CURLOPT_POSTFIELDS, 'apikey='.urlencode (WAASHERO_CLIENT_API_KEY)); 

             // $output contains the output string 
             $output = curl_exec($ch); 

             // close curl resource to free up system resources 
             curl_close($ch);      


             $result = json_decode($output,true);

             if($result['success'] == true){          
                 
                 return $result['taskid'];
             }

             return null; 
         }
         catch(Exception $e){
             return null;
         }
         
     }

     public static  function FlushGoogleCdn(){
         

         try{
             $ch = curl_init();            

             // set url 
             curl_setopt($ch, CURLOPT_URL, 'https://app.waashero.com/api/client/flushgooglecdn'); 

             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
             curl_setopt($ch, CURLOPT_TIMEOUT, 10);
             curl_setopt($ch, CURLOPT_POST, true); 
             curl_setopt($ch, CURLOPT_POSTFIELDS, 'apikey='.urlencode (WAASHERO_CLIENT_API_KEY)); 

             // $output contains the output string 
             $output = curl_exec($ch); 

             // close curl resource to free up system resources 
             curl_close($ch);      


             $result = json_decode($output,true);

             if($result['success'] == true){          
                 
                 return $result['taskid'];
             }

             return null; 
         }
         catch(Exception $e){
             return null;
         }
         
     }

     public static  function GetTaskStatus($id){
         

         try{
             $ch = curl_init();             

             // set url 
             curl_setopt($ch, CURLOPT_URL, 'https://app.waashero.com/api/client/gettaskstatus'); 

             //return the transfer as a string 
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
             curl_setopt($ch, CURLOPT_TIMEOUT, 10);
             curl_setopt($ch, CURLOPT_POST, true); 
             curl_setopt($ch, CURLOPT_POSTFIELDS, 'apikey='.urlencode (WAASHERO_CLIENT_API_KEY) . '&id='.urlencode ($id)); 

             // $output contains the output string 
             $output = curl_exec($ch); 

             // close curl resource to free up system resources 
             curl_close($ch);      


             $result = json_decode($output,true);

             if($result['success'] == true){          
                 
                 return $result['task'];
             }

             return null; 
         }
         catch(Exception $e){
             return null;
         }
         
     }
    
}