<?php
defined('ABSPATH') OR exit;

class Waashero_Settings
{

    

    public static function update(){

        if ( isset($_POST['submit']) && isset($_POST['waashero']) ) {


            if(!is_admin() && !is_network_admin()){
                return;
            }
           

            // verify authentication (nonce)
            if ( !isset( $_POST['waashero_nonce'] ) )
                return;

            // verify authentication (nonce)
            if ( !wp_verify_nonce($_POST['waashero_nonce'], 'waashero_nonce') )
                return;

            $currentOptions = Waashero_Options::get_options();
            
            if(!is_network_admin()){
                if(ctype_digit($_POST['waashero']['load_balancer_micro_cache_seconds']) && (int) $_POST['waashero']['load_balancer_micro_cache_seconds'] >= 30 && (int) $_POST['waashero']['load_balancer_micro_cache_seconds'] <= 172800){
                    
                    $currentOptions['load_balancer_micro_cache_seconds'] = (int) $_POST['waashero']['load_balancer_micro_cache_seconds'];
                }else{
                    $currentOptions['load_balancer_micro_cache_seconds'] = 300;
                }


                

                $old_enable_development_mode = 0;

                if(isset($currentOptions['development_mode'])){
                    $old_enable_development_mode = (int) $currentOptions['development_mode'];
                }


                if(isset($_POST['waashero']['development_mode'])){
                    $currentOptions['development_mode'] = (int)$_POST['waashero']['development_mode'];
                }else{
                    $currentOptions['development_mode'] = 0;
                }


                if(isset($_POST['waashero']['enable_cdn'])){
                    $currentOptions['enable_cdn'] = (int)$_POST['waashero']['enable_cdn'];
                }else{
                    $currentOptions['enable_cdn'] = 0;
                }

               

                if(isset($_POST['waashero']['excludes'])){
                    $currentOptions['excludes'] = $_POST['waashero']['excludes'];
                }else{
                    $currentOptions['excludes'] ='';
                }

                

               

                if(isset($_POST['waashero']['enable_object_cache'])){
                    $currentOptions['enable_object_cache'] = (int)$_POST['waashero']['enable_object_cache'];
                }else{
                    $currentOptions['enable_object_cache'] =0;
                }

                if(isset($_POST['waashero']['enable_debug'])){
                    $currentOptions['enable_debug'] = (int)$_POST['waashero']['enable_debug'];
                }else{
                    $currentOptions['enable_debug'] =0;
                }

                if(isset($_POST['waashero']['enable_opcache'])){
                    $currentOptions['enable_opcache'] = (int)$_POST['waashero']['enable_opcache'];
                }else{
                    $currentOptions['enable_opcache'] = 0;
                }

                if(isset($_POST['waashero']['load_balancer_micro_cache'])){
                    $currentOptions['load_balancer_micro_cache'] = (int)$_POST['waashero']['load_balancer_micro_cache'];
                }else{
                    $currentOptions['load_balancer_micro_cache'] =0;
                }

                //email

                if($currentOptions['smtp_configured']==1){

                    if(isset($_POST['waashero']['smtp_enabled'])){
                        $currentOptions['smtp_enabled'] = (int)$_POST['waashero']['smtp_enabled'];
                    }else{
                        $currentOptions['smtp_enabled'] = 0;
                    }

                    if(isset($_POST['waashero']['smtp_from_email'])){

                        if (filter_var($_POST['waashero']['smtp_from_email'] . '@' . $currentOptions['smtp_configured_domain'] , FILTER_VALIDATE_EMAIL)) {
                            $currentOptions['smtp_from_email'] = $_POST['waashero']['smtp_from_email'];
                        }
                        
                    }

                    
                    if(isset($_POST['waashero']['smtp_from_name']) && strlen($_POST['waashero']['smtp_from_name']) < 60){


                        $currentOptions['smtp_from_name'] = $_POST['waashero']['smtp_from_name'];

                    }else{
                        $currentOptions['smtp_from_name'] = '';
                    }



                    if(isset($_POST['waashero']['smtp_force_from_name'])){
                        $currentOptions['smtp_force_from_name'] = (int)$_POST['waashero']['smtp_force_from_name'];
                    }else{
                        $currentOptions['smtp_force_from_name'] = 0;
                    }


                    if(isset($_POST['waashero']['smtp_reply_to_email'])){
                        
                        if (filter_var($_POST['waashero']['smtp_reply_to_email'], FILTER_VALIDATE_EMAIL)) {
                            $currentOptions['smtp_reply_to_email'] = $_POST['waashero']['smtp_reply_to_email'];
                        }else{
                            $currentOptions['smtp_reply_to_email'] = '';
                        }
                        
                    }

                    if(isset($_POST['waashero']['smtp_dev_rec_add'])){
                        
                        if (filter_var($_POST['waashero']['smtp_dev_rec_add'], FILTER_VALIDATE_EMAIL)) {
                            $currentOptions['smtp_dev_rec_add'] = $_POST['waashero']['smtp_dev_rec_add'];
                        }else{
                            $currentOptions['smtp_dev_rec_add'] = '';
                        }
                        
                    }
                    

                    
                }

                Waashero_Options::save_options($currentOptions);
                
                Waashero::SetObjectCache(true);
                Waashero::insert_htaccess_rules();

                
                //from production to development
                if($old_enable_development_mode == 0  && ($currentOptions['development_mode'] == 1 || $currentOptions['development_mode'] == 2)){

                    if (is_network_admin() ) {
                        add_action('network_admin_notices',array(__CLASS__,'admin_notice_development_mode_turned_off_text'));                        
                    }else{
                        add_action('admin_notices',array(__CLASS__,'admin_notice_development_mode_turned_off_text') );
                    }
                    
                }else if(($old_enable_development_mode == 1 || $old_enable_development_mode == 2) && $currentOptions['development_mode'] == 0){
                   
                }

               
            }

            if(is_network_admin()){
                
                if(isset($_POST['waashero']['allow_per_site_config'])){
                    $currentOptions['allow_per_site_config'] = (int)$_POST['waashero']['allow_per_site_config'];
                }else{
                    $currentOptions['allow_per_site_config'] = 0;
                }    
                

                Waashero_Options::save_options($currentOptions);
            }           

            return;
        }

        return;
    }

    static function admin_notice_development_mode_turned_off_text(){
        $class = 'notice notice-warning';
        $message = 'Development mode is disabled. All caches are flushed except Google CDN. If you made changes in files (e.g CSS or JS file) that were previously cached on Google CDN network, you need to flush the CDN. <a href="'.menu_page_url('waashero_main_menu.cdn-invalidation',false) .'">Clear CDN Cache</a>';
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ),  $message);
    }


    static function admin_notice_cdn_invalidation_time(){
        $class = 'notice notice-warning';
        $message = 'CDN invalidation process might take several minutes.';
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ),  $message);
    }
    
}

return new Waashero_Settings();