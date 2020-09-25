<?php

defined('ABSPATH') OR exit;

class Waashero {



    public static function instance() {
        new self();
    }

    public function __construct() {



        // Add hook to the new blog/subsite creation to create a DNS record
        add_action('wp_insert_site', array($this, 'createRecordForNewSubsite') );

        add_action( 'admin_enqueue_scripts', array(&$this, 'waashero_requirements_enqueue_scripts'));
                  // mute core update email
       
        // //cache plugins        
        // if(method_exists('LiteSpeed_Cache_API','hook_control')){
        //     LiteSpeed_Cache_API::hook_control( array( &$this, 'enable_micro_cache_and_304' ) ) ; 
        // }

        // //litspeed 3+
        // add_action( 'litespeed_control_finalize', array( &$this, 'enable_micro_cache_and_304' ) );

      

        

        // add_action( 'wpfc_cache_detection_info', array(&$this, 'waashero_third_party_is_cacheable_wpfc'), PHP_INT_MAX );
        // add_filter( 'rocket_buffer',array(&$this, 'waashero_third_party_is_cacheable_wp_rocket'), 10, 1 );
        // add_filter( 'w3tc_pagecache_set', array(&$this, 'waashero_third_party_is_cacheable_w3tc'), 10, 2 );         

        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) OR ( defined('DOING_CRON') && DOING_CRON) OR ( defined('DOING_AJAX') && DOING_AJAX) OR ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
          
            
            if(( defined('DOING_AJAX') && DOING_AJAX)){
                if($this::user_can_manage_admin_settings() && is_admin()){
                    
                    add_action(
                        'wp_ajax_waashero_add_domain_alias', 
                        array( 
                            'Waashero_Ajax', 
                            'waashero_add_domain_alias' 
                        )
                    );

                    add_action( 
                        'wp_ajax_waashero_welcome_tour', 
                        array( 
                            'Waashero_Ajax', 
                            'waashero_welcome_tour'
                        )
                    );
                  
                }
            }
            
            return;
        }      

        if( $this::user_can_manage_admin_settings() && ( is_admin() || is_network_admin() ) ) {           
                   
             Waashero_Settings::update();            
        }
   

        if( $this::user_can_manage_admin_settings() && ( is_network_admin() || is_admin())) {



          

            $menu_type = is_network_admin() ? "network_admin_menu" :"admin_menu";

            add_action( 'admin_enqueue_scripts',   function () {
                wp_enqueue_style( 'waashero_css','/wp-content/mu-plugins/waashero/css/waashero.css');
                wp_enqueue_script( 'waashero_js','/wp-content/mu-plugins/waashero/js/settings.domains.js');
            });

          
            add_action( 
                'admin_init',  
                array(
                    __CLASS__,
                    'flush_cache_listener'
                )
            );

            add_action( 
                'current_screen',  
                array(
                    &$this,
                    'render_tour'
                ) 
            );     
            // future use to add development mode
            //self::admin_notice_development_mode();  
            
        }
    
    }




    /**
    * Add functionality to create an DNS A record on subsite creation
    *
    *
    * @param $blog_id
    * @param $user_id
    * @param $domain
    * @param $path
    * @param $site_id
    * @param $meta
    *
    * @return void
    */
    function createRecordForNewSubsite( $data ) {

     
        $add = Waashero_Api::AddDomainAlias( $data->domain );
     
        // Switch the newly created blog
        //switch_to_blog( $data->blog_id );
        
        
        // Restore to the current blog
        //restore_current_blog();
    }


    function checkIsHtml( $buffer ) {

        if(!$buffer){
            return false;
        }

        if(preg_match('/<html[^\>]*>/si', $buffer) && preg_match('/<body[^\>]*>/si', $buffer)){
            return true;
        }       

        return false;
    }

    function waashero_is_cacheable_header_set( $headers ) {
        $x_cacheable_header_content ='X-Cacheable';
        $header_content = '';

         foreach( $headers as $header ) {
            $splitted = explode( ":",$header );

            if( $splitted[0] == $x_cacheable_header_content ) {
                $header_content = $splitted[1];
            }
            
         }

        if ( strpos( $header_content, ' yes' ) === 0 ) { 

            return true;
        }


        return false;
     }

     function waashero_third_party_is_cacheable_wpfc() {
        header('X-Cacheable: yes',true);

     }

     function waashero_third_party_is_cacheable_wp_rocket( $buffer ) {
        header( 'X-Cacheable: yes', true );

        return $buffer;

     }

    function waashero_third_party_is_cacheable_w3tc( $data, $this_page_key ) {
        header( 'X-Cacheable: yes', true );

        return $data; 
    }

    public static function user_can_manage_admin_settings() {

        $capability = is_network_admin() ? 'manage_network_options' : 'manage_options' ;

        if ( current_user_can( $capability ) ) {   

            return true;
        }

        return false;
    }


    // Where we handle OPcache flush action
    static function flush_cache_listener() {
        if ( ! isset( $_REQUEST['waashero_flush_cache_action'] ) ) {
            return;
        }
       
        $action = sanitize_key( $_REQUEST['waashero_flush_cache_action'] );
        if ($action == 'done_flush_waashero_caches' ) {

            $flush_message_type = 'admin_notices';       

            if(is_multisite() && is_network_admin()){
                $flush_message_type = 'network_admin_notices';              
            }
            add_action($flush_message_type,  array(__CLASS__,'admin_notice_flushed_caches') ); 
            return;
        }
       

       

        if ( $action == 'flush_waashero_caches' ) {
            check_admin_referer( 'waashero_top_bar_action' );
            //opcache_reset(); we have to define this
            wp_cache_flush();

            $url = esc_url_raw( remove_query_arg('waashero_flush_cache_action') );
            $url = esc_url_raw( remove_query_arg('_wpnonce') );
            $url = esc_url_raw( add_query_arg( array( 'waashero_flush_cache_action' => 'done_flush_waashero_caches' ) ) );      
         
            wp_redirect($url);
            
        }



    }

    static function admin_notice_flushed_caches() {
        $class = 'notice notice-success is-dismissible';
        $message = 'OPcache & Object-Cache were successfully flushed.';
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }

    static function admin_notice_development_mode() {

      
        //options use the old values before the update, check if is save page
        if(defined("WAASHERO_DEVELOPMENT_MODE") && (!isset($_POST['submit']) && !isset($_POST['waashero']))) {
          
          
            add_action('admin_notices',array(__CLASS__,'admin_notice_development_mode_text') );
            if ( is_multisite() ) {
                add_action('network_admin_notices',array(__CLASS__,'admin_notice_development_mode_text') );
              
            }
        }
  
    }


    static function admin_notice_development_mode_text(){
        $class = 'notice notice-warning';
        $message = 'Development mode is enabled and many performance & security features are disabled. <a href="'.menu_page_url('waashero_main_menu',false).'">Disable Development Mode</a>';
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ),  $message );
    }

    function waashero_requirements_enqueue_scripts($hook) {
	    if ( 'update-core.php' == $hook ) {

            wp_enqueue_script( 'waashero_requirements_update_core', '/wp-content/mu-plugins/waashero/js/update-core.js' );
	    }
    }

    function waashero_dynamic_ip_whitelist(){
        
        $option_key = 'waashero_dynamic_ip_whitelist';
        $user_ip = $_SERVER["REMOTE_ADDR"];

        if( empty($user_ip) || $user_ip == '127.0.0.1' ) {
            return;
        }

        $should_update_htaccess = false;
        $minutes = 60;
        $whitelisted = false;
        if( get_site_option( $option_key ) != NULL ) {
            $whitelisted = get_site_option( $option_key );
        }
        if(!$whitelisted || !is_array( $whitelisted ) ){
            $whitelisted = array();
        }

        $now =  new DateTime();

        if(array_key_exists($user_ip,$whitelisted)){

        
            $valid_until = $whitelisted[$user_ip];

            if($valid_until < $now){
                $new_valid_until = new DateTime();
                $valid_until = $new_valid_until->modify("+{$minutes} minutes");
                $whitelisted[$user_ip] = $valid_until;
                update_site_option($option_key,$whitelisted);

                //maybe the IP is removed from htaccess
                $should_update_htaccess = true;
            }



            
        }else{
            $new_valid_until = new DateTime();
            $valid_until = $new_valid_until->modify("+{$minutes} minutes");
            $whitelisted[$user_ip] = $valid_until;
            update_site_option($option_key,$whitelisted);
            $should_update_htaccess = true;
        }

         $save = false;
        foreach ($whitelisted as $key => $value){
            
           
            if($value < $now){
                unset($whitelisted[$key]);
                $should_update_htaccess = true;
                $save = true;
            }
          
        }

        if($save == true){
            update_site_option($option_key,$whitelisted);
        }

        if($should_update_htaccess == true){
            self::insert_htaccess_rules();
        }
    }
   
    function enable_micro_cache_and_304() {

       

        $options =  Waashero_Options::get_options(); 

        if ($options['load_balancer_micro_cache']) {
            $seconds = $options['load_balancer_micro_cache_seconds'];
            header('Cache-Control: public,s-maxage='.$seconds,true);
            header('X-Cacheable: yes',true);
            header_remove("pragma");
        }else{
            header("Cache-Control:no-cache, must-revalidate, max-age=0");
            header_remove("pragma");
            header('X-Cacheable: yes',true);
        }

    }


    private static function format_message($mesg,$tag)
	{

		$formatted = sprintf("%s [%s:%s] [%s] %s\n", date('r'), $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT'], $tag, $mesg);
		return $formatted;
	}

  

    public static function SetObjectCache(){
     

        
        $wordpress_object_cache_file_location = ABSPATH . 'wp-content/object-cache.php';
        $disable = true;
        $options = Waashero_Options::get_options(true);

     


        if($options["development_mode"] == 0 && $options["enable_object_cache"] == 1){
            $disable = false;
        }      
       

        if($disable == true){
        
            if (file_exists($wordpress_object_cache_file_location)) {
                unlink($wordpress_object_cache_file_location);
                wp_cache_flush();
                if(function_exists('apcu_clear_cache')){                   
                    apcu_clear_cache();
                }
            } 

        }else {
         
            if(!file_exists($wordpress_object_cache_file_location)){
                $object_cache_file_location = WAASHERO_DIR . '/inc/class-waashero-object-cache.php';
                $object_cache_php = file_get_contents($object_cache_file_location);
                file_put_contents($wordpress_object_cache_file_location,$object_cache_php);
                wp_cache_flush();
                if(function_exists('apcu_clear_cache')){                   
                    apcu_clear_cache();
                }

            }
        }

    }


    //still need to work on it
    /**
     * needs to bre removed
     *
     * @return void
     */
    public static function waashero_autologin() {




        if ($_SERVER['REQUEST_METHOD'] === 'POST' && self::Is_Backend_LOGIN()) {


            if(isset($_POST["waashero_auto_login_value"])){


             
                $local_file = "";

                try {
                    $waashero_auto_login_value = $_POST["waashero_auto_login_value"];
                    $user_ip = $_SERVER["REMOTE_ADDR"];
                    if(empty($user_ip)){
                        return;
                    }

                    $local_file = dirname(constant('ABSPATH')) . '/conf/cal_' . $user_ip;



                    if (file_exists($local_file) && fileowner($local_file) === 0) {

                        $file_content = file_get_contents($local_file);
                        $validUntil = date("F j Y g:i:s A T", self::ticks_to_time(explode(",", $file_content)[0]));
                        $file_value = explode(",", $file_content)[1];
                        $utc_now = date("F j Y g:i:s A T", time() - date("Z"));
                        $wp_user =explode(",", $file_content)[2];
                        $file_ip = explode(",", $file_content)[3];


                       


                        if(($file_value === $waashero_auto_login_value) && ($validUntil > $utc_now) && ($file_ip === $user_ip)){

                           
                            if ( username_exists($wp_user) || email_exists($wp_user) ) {

                                if(is_user_logged_in()){
                                    wp_logout();
                                }

                                //get user's ID
                                $user = get_user_by('login', $wp_user);
                                
                                if($user == false){
                                     $user = get_user_by('email', $wp_user);
                                }                               
                             
                                
                                $user_id = $user->ID;
                                //login
                                wp_set_current_user($user_id, $wp_user);
                                wp_set_auth_cookie($user_id);
                                do_action('wp_login', $user->user_login, $user);
                                //redirect to home page after logging in (i.e. don't show content of www.site.com/?p=1234 )
                                wp_redirect( get_admin_url() );
                                exit;


                            }else{
                                header("HTTP/1.1 200 OK");
                                header( 'Content-Type: text/html; charset=utf-8' );
                                echo '<strong>ERROR:</strong> User with '.$wp_user.' username or email address does not exist. Go to <a href="https://app.waashero.com/sites/settings/'.WAASHERO_CLIENT_API_KEY.'">Site->Settings page</a> and change the auto login username.';
                                exit( 1 );
                            }



                        }
                       
                    }
                }
                catch (Exception $e) {

                  

                }
                finally{

                  
                }


            }
        }


    }

    public static function Is_Backend_LOGIN(){
        $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);

        $included_files = get_included_files();

        return ((in_array($ABSPATH_MY.'wp-login.php', $included_files) || in_array($ABSPATH_MY.'wp-register.php', $included_files) ) || $GLOBALS['pagenow'] === 'wp-login.php' || $_SERVER['PHP_SELF']== '/wp-login.php');
    }



    public static function ticks_to_time($ticks) {
        return floor(($ticks - 621355968000000000) / 10000000);
    }



    
    public static function ticks_to_time_with_zone($epoch){
  
        $d = date('Y-m-d H:i:s', $epoch);
        $local_timestamp = get_date_from_gmt($d, 'd M y h:i A' );      
        return $local_timestamp;
    }
  
    public static function insert_htaccess_rules()
	{

        $insertion = array('','## Do not edit the contents of this block! ##');
        array_push($insertion,'RewriteEngine On');

        $global_options = Waashero_Options::get_options(true);

        $whitelisted = get_site_option('waashero_dynamic_ip_whitelist');
        if(!$whitelisted || !is_array($whitelisted)){
            $whitelisted = array();
        }

        foreach ($whitelisted as $key => $value){            
            array_push($insertion,'SetEnvIfNoCase Remote_Addr ^'.$key.'$ MODSEC-OFF');
            array_push($insertion,
                'RewriteCond %{REMOTE_ADDR} ^'. str_replace('.','\.',$key) .'$',
                'RewriteRule .* - [E=noconntimeout:1]');
        }


        array_push($insertion,'');

        $marker = "WAASHERO";
        $filename = '/home/'.WAASHERO_CLIENT_API_KEY .'/public_html/.htaccess';

		if ( ! file_exists($filename) ) {
			if ( ! is_writable( dirname($filename) ) ) {
				return false ;
			}
		
			try {
				touch($filename) ;
			}
			catch ( ErrorException $ex ){
				return false ;
			}
			
		}
		elseif ( ! is_writeable($filename) ) {
			return false ;
		}

		if ( ! is_array($insertion) ) {
			$insertion = explode( "\n", $insertion ) ;
		}

		$start_marker = "# BEGIN {$marker}" ;
		$end_marker   = "# END {$marker}" ;

		$fp = fopen($filename, 'r+' ) ;
		if ( ! $fp ) {
			return false ;
		}

		// Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
		flock( $fp, LOCK_EX ) ;

		$lines = array() ;
		while ( ! feof($fp) ) {
			$lines[] = rtrim(fgets($fp), "\r\n" ) ;
		}

		// Split out the existing file into the preceding lines, and those that appear after the marker
		$pre_lines = $post_lines = $existing_lines = array() ;
		$found_marker = $found_end_marker = false ;
		foreach ( $lines as $line ) {
			if ( ! $found_marker && false !== strpos($line, $start_marker) ) {
				$found_marker = true ;
				continue ;
			}
			elseif ( ! $found_end_marker && false !== strpos($line, $end_marker) ) {
				$found_end_marker = true ;
				continue ;
			}

			if ( ! $found_marker ) {
				$pre_lines[] = $line ;
			}
			elseif ( $found_marker && $found_end_marker ) {
				$post_lines[] = $line ;
			}
			else {
				$existing_lines[] = $line ;
			}
		}

		// Check to see if there was a change
		if ( $existing_lines === $insertion ) {
			flock($fp, LOCK_UN) ;
			fclose($fp) ;

			return true ;
		}

		// Generate the new file data
        $new_file_data = implode( "\n", array_merge(
            $pre_lines,
            array( $start_marker ),
            $insertion,
            array( $end_marker ),
            $post_lines
        ) ) ;


		// Write to the start of the file, and truncate it to that length
		fseek($fp, 0) ;
		$bytes = fwrite($fp, $new_file_data) ;
		if ( $bytes ) {
			ftruncate($fp, ftell($fp)) ;
		}
		fflush($fp) ;
		flock($fp, LOCK_UN) ;
		fclose($fp) ;

		return (bool) $bytes ;
	}



    function render_tour(){
        

        if(self::is_wp_dashboard()){
            
            $options = Waashero_Options::get_options(); 
            $should_render_tour = true;

            if(isset($options['welcome_tour'])){
                
                if($options['welcome_tour'] == 1){
                    $should_render_tour = false;
                }
            }

            if($should_render_tour){
                

                add_action( 'admin_enqueue_scripts',   function () {
                    $script_file = '/wp-content/mu-plugins/waashero/js/welcome_tour_pointer.js';

                    if(is_multisite()){

                        if(!is_main_site()){
                            $script_file = '/wp-content/mu-plugins/waashero/js/subsite_welcome_tour_pointer.js';
                        }
                        
                    }
                    // Add pointers style to queue.
                    wp_enqueue_style( 'wp-pointer' );
                    wp_enqueue_script( 'wp-pointer' );
                    wp_enqueue_script( 'waashero_hopscotch', $script_file );
                });
                
                
            }


        }

    }

    public static function is_wp_dashboard(){
        if (is_admin()) {	
            $screen = get_current_screen();
            if ($screen -> id == "dashboard") {	
                return true;	
            }
        }

        return false;
    }

}

return new Waashero();