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

    public static function waashero_create_backup(){

        $result = array('success'  => false);
        $task = Waashero_Api::ManualBackup();
       

        
        if(!is_null($task)){
            $result['success'] = true;
            $result['taskid'] = $task;
        }

        wp_send_json($result); 
    }

    public static function waashero_get_task_status(){


        $id = $_POST['waashero_task_id'];

        $result = array('success'  => false);
        $task = Waashero_Api::GetTaskStatus($id);
        
        if(!is_null($task)){
            $result['success'] = true;
            $result['task'] = $task;
        }

        wp_send_json($result);       
    }


    public static function waashero_cdn_invalidation(){


        

        $result = array('success'  => false);
        $task = Waashero_Api::FlushGoogleCdn();
        
        if(!is_null($task)){
            $result['success'] = true;
            $result['taskid'] = $task;
        }

        wp_send_json($result);       
    }
    
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