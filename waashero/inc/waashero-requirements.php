<?php
header('X-Cacheable: no',true);

defined('ABSPATH') OR exit;

if(defined('DISABLE_WAASHERO_PLUGIN') && DISABLE_WAASHERO_PLUGIN){
    ini_set('opcache.enable', '0');
    return;
}

if(!defined("WAASHERO_APP_ID")){
    define("WAASHERO_APP_ID", '' );
}

if(!defined( "LITESPEED_DISABLE_OBJECT" )){
    define("LITESPEED_DISABLE_OBJECT",true);
}



require WPMU_PLUGIN_DIR.'/waashero/waashero.php';
//Waashero::SetObjectCache();

$options =  Waashero_Options::get_options();



if($options) {


    if(!defined("WAASHERO_DEVELOPMENT_MODE_TYPE")){
        define("WAASHERO_DEVELOPMENT_MODE_TYPE",$options["development_mode"]);
    }
    

    
    
    //development mode
    if(WAASHERO_DEVELOPMENT_MODE_TYPE == 1){
        
        if(!defined("WAASHERO_DEVELOPMENT_MODE")){
            define("WAASHERO_DEVELOPMENT_MODE",true);
        }     

    }

    

    
    if($options['enable_opcache'] == 1 && !defined("WAASHERO_DEVELOPMENT_MODE")){

    }else{
        ini_set('opcache.enable', '0');
    }
    


}




function waashero_filter_output($final) {
    return apply_filters('waashero_final_output', $final);
}


if(!defined("WAASHERO_DEVELOPMENT_MODE") && $options && $options["enable_cdn"] == 1){       
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    
    if (is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
        ob_start("waashero_filter_output");
    }
    
}