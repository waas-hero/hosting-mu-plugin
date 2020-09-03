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


     public static function domains_page()
	{

      
       wp_enqueue_script( 'waashero_settings_backups', '/wp-content/mu-plugins/waashero/js/settings.domains.js' );
      
       $alternate_color = '';
       $domains =  Waashero_Api::GetDomains();
         
     

   


      

?>
<div class="wrap">
         <h1 class="wp-heading-inline">Domains (beta)</h1>
  
    <hr class="wp-header-end">


       <?php if(is_null($domains)) : ?>
    <p>Error communicating with the server</p>

<?php else : ?>

        <?php if(empty($domains)) : ?>
              <p>No domains found</p>
    <?php else : ?>
    
 
    <p class="search-box">
	<input type="text" id="domain-alias-hostname" name="s" placeholder="www.example.com">
	<input type="button" id="domain-alias-submit" class="button" value="Add Domain Alias">
    
    </p>
  

<p>More information about DNS & SSL status or requirements can be found in <a target="_blank" href="https://app.waashero.com/sites/domains/<?php echo WAASHERO_APP_ID; ?>">site domains page.</a></p>
              <iframe    id="iframe_waashero_domains"    src="https://app.waashero.com/api/client/domainsiframe?apikey=<?php echo urlencode (WAASHERO_CLIENT_API_KEY); ?>"    frameborder="0"    width="100%"    marginheight="0"    marginwidth="0" height="700"    scrolling="no" ></iframe>


    <?php endif; ?>

   
<?php endif; ?>
            
    
    

    </div>
<?php
         
         
    }


    public static function backups_page()
	{

      
       wp_enqueue_script( 'waashero_settings_backups', '/wp-content/mu-plugins/waashero/js/settings.backups.js' );
       $alternate_color = '';
       $backups =  Waashero_Api::GetBackups();
     


      

?>
<div class="wrap">
         <h1 class="wp-heading-inline">Backups</h1>
    <a id="waashero_do_backup" href="javascript:void(0)" class="page-title-action">Create Backup</a>
    <hr class="wp-header-end">


       <?php if(is_null($backups)) : ?>
    <p>Error communicating with the server</p>

<?php else : ?>

        <?php if(empty($backups)) : ?>
              <p>No backups found</p>
    <?php else : ?>

                    <table class="widefat fixed" cellspacing="0" style="margin-top:20px">
    <thead>
    <tr>

            <th id="columnname" class="manage-column column-columnname" scope="col">Date</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Type</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Status</th> 

    </tr>
    </thead>   

    <tbody>

        <?php foreach($backups as $b): ?>  

          <tr class="<?php echo $alternate_color ?>">
            <td class="column-columnname"><?php echo Waashero::ticks_to_time_with_zone($b['date']); ?></td>
            <td class="column-columnname"><?php echo $b['type']; ?></td>
            <td class="column-columnname">
                
                 <?php if($b['id'] == '') : ?>
                  <span class="waashero_pending_task" data-task-id="<?php echo $b['taskid']; ?>"><img src="/wp-admin/images/loading.gif" /></span>
                 <?php else : ?>
                 <span>Success</span>
                 <?php endif; ?>
              </td>
        </tr>

        <?php 
          if($alternate_color == ''){
              $alternate_color = 'alternate';
          }else{
              $alternate_color = '';
          }

        ?>


    <?php endforeach; ?>


      
      
     
    </tbody>
</table>


    <?php endif; ?>

   
<?php endif; ?>
             

    </div>
<?php
    }


    
    public static function cdn_invalidation_page()
	{

        wp_enqueue_script( 'waashero_settings_backups', '/wp-content/mu-plugins/waashero/js/settings.cdn-invalidation.js' );
        $alternate_color = '';
        $invalidations = Waashero_Api::GetCdnCacheInvalidation();

      

?>
<div class="wrap">
         <h1  class="wp-heading-inline">CDN Invalidation</h1>
       <a id="waashero_clear_cdn_cache" href="javascript:void(0)" class="page-title-action">Clear CDN Cache</a>
      <hr class="wp-header-end">
    <div class="notice notice-info"><p>CDN invalidation might take several minutes.</p></div>
    </div>


   <?php if(is_null($invalidations)) : ?>
    <p>Error communicating with the server</p>

<?php else : ?>

        <?php if(empty($invalidations)) : ?>
              <p>No CDN invalidations found</p>
    <?php else : ?>

                    <table class="widefat fixed" cellspacing="0" style="margin-top:20px">
    <thead>
    <tr>

            <th id="columnname" class="manage-column column-columnname" scope="col">Date</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Path</th>
            <th id="columnname" class="manage-column column-columnname" scope="col">Status</th> 

    </tr>
    </thead>   

    <tbody>

        <?php foreach($invalidations as $b): ?>  

          <tr class="<?php echo $alternate_color ?>">
            <td class="column-columnname"><?php echo Waashero::ticks_to_time_with_zone($b['date']); ?></td>
            <td class="column-columnname"><?php echo $b['path']; ?></td>
            <td class="column-columnname">
            
                  <?php if($b['status'] != 'Finished') : ?>
                  <span class="waashero_pending_task" data-task-id="<?php echo $b['id']; ?>"><img src="/wp-admin/images/loading.gif" /></span>
                 <?php else : ?>
                 <span><?php echo $b['status']; ?></span>
                 <?php endif; ?>    
              </td>
            </tr>

        <?php 
                  if($alternate_color == ''){
                      $alternate_color = 'alternate';
                  }else{
                      $alternate_color = '';
                  }

        ?>


    <?php endforeach; ?>


      
      
     
    </tbody>
</table>


    <?php endif; ?>

   
<?php endif; ?>

<?php
    }



	public static function settings_page()
	{

        $cache = null;

        if(function_exists("apcu_cache_info")){
            $cache = apcu_cache_info();
        }

        $opcachestatus = "";
        $options = Waashero_Options::get_options();  
        
?>


<div class="wrap">
    
    <h1 class="wp-heading-inline">Waashero Settings</h1>
    <hr class="wp-header-end">
    <form method="post">

       <input type="hidden" name="waashero[dummy]"  value="1" />
     

        <script type="text/javascript">
	jQuery(function() {
	  
	  
	    jQuery('.nav-tab-wrapper a').click(function () {
	        var t = jQuery(this).attr('href');
	        jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
	        jQuery(this).addClass('nav-tab-active');
	        jQuery('.waashero_tab_content').hide();
	        jQuery(t).fadeIn(300);
	        return false;
	    })

	 
	});
        </script>

        <div class="plugin_config" style="margin-top:20px;">
                  <h2 class="nav-tab-wrapper">
                   <a href="#waashero_plugin_config-general" class="nav-tab nav-tab-active">General</a>
                   <a href="#waashero_plugin_config-cdn" class="nav-tab">CDN</a>
                 
                   <a href="#waashero_plugin_config-oc" class="nav-tab">Object-Cache</a>
                   <a href="#waashero_plugin_config-op" class="nav-tab">OPcache</a>
                   <a href="#waashero_plugin_email" class="nav-tab">Email</a>
                 </h2>
            <div id="waashero_settings_tabs" style="padding:10px;">
                <div id="waashero_plugin_config-general" class="waashero_tab_content">
                    <table class="form-table">


                         
                        <tr valign="top">
                            <th scope="row">
                                Development Mode
                            </th>
                            <td>
                              <fieldset>
                                    <label for="waashero_development_mode">
                                        <select name="waashero[development_mode]" id="waashero_development_mode">
                                            <option <?php selected(0, $options['development_mode']) ?> value="0">Off</option>                                          
                                            <option <?php selected(1, $options['development_mode']) ?> value="1">Development</option>
                                           <!-- <option <?php selected(2, $options['development_mode']) ?> value="2">Development - LSCache</option>-->
                                        </select>                                      
                                      
                                        <p class="description">                                         
                                         <b>Development</b>: disable OPcache, Object-Cache, CDN, Web Application Firewall &amp; a few more configurations regardless of their statuses. <b>Recommended for development or debugging a possible issue</b>.<br /> 
                                          Take note, this option will <b>not disable</b> any caching plugin.
                                        </p>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                        
                       


                    </table>
                </div>
                <div id="waashero_plugin_config-cdn" class="waashero_tab_content" style="display: none;">
                    <table class="form-table">


                        <tr valign="top">
                            <th scope="row">
                                <?php _e("CDN", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_enable_cdn">
                                        <input type="checkbox" name="waashero[enable_cdn]" id="waashero_enable_cdn" value="1" <?php checked(1, $options['enable_cdn']) ?> />
                                        <?php _e("Enable Google Cloud CDN", "waashero"); ?>
                                         <p class="description">                                        
                                         Works only when Litespeed cache plugin is active.<br />
                                         If using another caching plugin, you will need to configure their CDN feature.  
                                        </p>
                                    </label>
                                   
                                </fieldset>
                            </td>
                        </tr>


                        <tr valign="top">
                            <th scope="row">
                                CDN Hostname
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_url">
                                        <input disabled="disabled" type="text" value="<?php echo WAASHERO_CDN_HOSTNAME; ?>" size="64" class="regular-text code" />
                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                     

                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Exclusions", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_excludes">
                                        <input type="text" name="waashero[excludes]" id="waashero_excludes" value="<?php echo $options['excludes']; ?>" size="64" class="regular-text code" placeholder=".php" />
                                       
                                    </label>

                                    <p class="description">
                                        <?php _e("Enter the exclusions (directories or extensions) separated by", "waashero"); ?>
                                        <code>,</code> <br />                                        
                                    </p>
                                </fieldset>
                            </td>
                        </tr>

                     

                    




                        <tr valign="top" class="hidden">
                            <th scope="row">
                                <?php _e("Proxy Global Cache", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_load_balancer_micro_cache">
                                        <input type="checkbox" name="waashero[load_balancer_micro_cache]" id="waashero_load_balancer_micro_cache" value="1" <?php checked(1, $options['load_balancer_micro_cache']) ?> />
                                       Enable
                                    </label>
                                         <p class="description">Enable global caching via private load balancer. <b>Experts only!</b></p>
                                </fieldset>
                            </td>
                        </tr>


                          <tr valign="top" class="hidden">
                            <th scope="row">
                                <?php _e("Proxy Cache Expire", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_load_balancer_micro_cache_seconds">
                                        <input type="number" name="waashero[load_balancer_micro_cache_seconds]" id="waashero_load_balancer_micro_cache_seconds" value="<?php echo $options['load_balancer_micro_cache_seconds']; ?>" />
                                      
                                    </label>
                                     <p class="description">Global proxy cache expires in seconds.</p>
                                </fieldset>
                            </td>
                        </tr>



                    </table>
                </div>                  
                <div id="waashero_plugin_config-oc" class="waashero_tab_content" style="display: none;">
                    <table class="form-table">


                        <tr valign="top">
                            <th scope="row">
                                Object Cache
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_enable_object_cache">
                                        <input type="checkbox" name="waashero[enable_object_cache]" id="waashero_enable_object_cache" value="1" <?php checked(1, $options['enable_object_cache']) ?> />
                                        Enable
                                    </label>
                                     <p class="description">Reduces the MySQL queries and improves the general website performance <br />
                                        <b>Important!</b> Some plugins might not be compatible with <a href="https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Caching" target="_blank">persistent Object-Cache</a>.
                                     </p>
                                </fieldset>
                            </td>
                        </tr>

                        <?php
        function bsize($s) {
            foreach (array('','K','M','G') as $i => $k) {
                if ($s < 1024) break;
                $s/=1024;
            }
            return sprintf("%5.1f %sBytes",$s,$k);
        }

                        ?>

                        <?php    if(function_exists("apcu_cache_info")){

                                     $time = time();
                                     $mem=apcu_sma_info();
                                     $mem_size = $mem['num_seg']*$mem['seg_size'];
                                     $mem_avail= $mem['avail_mem'];
                                     $mem_used = $mem_size-$mem_avail;
                                     //  $seg_size = bsize($mem['seg_size']);

                                     $time_minus_start_time = $time-$cache['start_time'];
                                     if($time_minus_start_time <=0){
                                         $time_minus_start_time = 1;
                                     }

                                     $num_hits_plus_num_misses = $cache['num_hits']+$cache['num_misses'];

                                     if($num_hits_plus_num_misses <=0){
                                         $num_hits_plus_num_misses = 1;
                                     }

                                     $req_rate_user = sprintf("%.2f", $cache['num_hits'] ? (($cache['num_hits']+$cache['num_misses'])/$time_minus_start_time) : 0);
                                     $hit_rate_user = sprintf("%.2f", $cache['num_hits'] ? (($cache['num_hits'])/$time_minus_start_time) : 0);
                                     $miss_rate_user = sprintf("%.2f", $cache['num_misses'] ? (($cache['num_misses'])/$time_minus_start_time) : 0);
                                     $insert_rate_user = sprintf("%.2f", $cache['num_inserts'] ? (($cache['num_inserts'])/$time_minus_start_time) : 0);

                                     $number_vars = $cache['num_entries'];
                                     $size_vars = bsize($cache['mem_size']);

                                     $used = bsize($mem_used).sprintf(" (%.1f%%)",$mem_used *100/$mem_size);
                                     $free =bsize($mem_avail).sprintf(" (%.1f%%)",$mem_avail*100/$mem_size);

                                     $hitPercent = sprintf(" (%.1f%%)",$cache['num_hits']*100/$num_hits_plus_num_misses);
                                     $missPercent = sprintf(" (%.1f%%)",$cache['num_misses']*100/$num_hits_plus_num_misses);

                                     $hints = ini_get_all("apcu")["apc.entries_hint"]["local_value"];

                        ?>
                        <tr valign="top">
                            <th scope="row">
                                Stats
                            </th>
                            <td>

                                <?php
                                     echo <<<EOB

		<table cellspacing=0 style='background:#fff'>
		<tbody>

<tr class=tr-0><td class=td-0>Used</td><td>{$used}</td></tr>
<tr class=tr-0><td class=td-0>Free</td><td>{$free}</td></tr>



    		<tr class=tr-0><td class=td-0>Cached Variables</td><td>$number_vars ($size_vars)</td></tr>
			<tr class=tr-1><td class=td-0>Hits</td><td>{$cache['num_hits']}  {$hitPercent}</td></tr>
			<tr class=tr-0><td class=td-0>Misses</td><td>{$cache['num_misses']} {$missPercent}</td></tr>
			<tr class=tr-1><td class=td-0>Request Rate (hits, misses)</td><td>$req_rate_user cache requests/second</td></tr>
			<tr class=tr-0><td class=td-0>Hit Rate</td><td>$hit_rate_user cache requests/second</td></tr>
			<tr class=tr-1><td class=td-0>Miss Rate</td><td>$miss_rate_user cache requests/second</td></tr>
			<tr class=tr-0><td class=td-0>Insert Rate</td><td>$insert_rate_user cache requests/second</td></tr>
            <tr class=tr-1><td class=td-0>Entries Hint</td><td>{$hints}</td></tr>
			<tr class=tr-1><td class=td-0>Cache full count</td><td>{$cache['expunges']}</td></tr>
		</tbody>
		</table>



EOB;
                                ?>

                            </td>
                        </tr>
                        <?php   } ?>



                    </table>
                </div>
                <div id="waashero_plugin_config-op" class="waashero_tab_content" style="display: none;">
                    <table class="form-table">


                        <tr valign="top">
                            <th scope="row">
                                OPcache Settings
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_enable_opcache">
                                        <input type="checkbox" name="waashero[enable_opcache]" id="waashero_enable_opcache" value="1" <?php checked(1, $options['enable_opcache']) ?> />
                                        Enable
                                    </label>
                                      <p class="description">Improves the general PHP execution time.</p>
                                </fieldset>
                            </td>
                        </tr>



                    </table>
                </div>
                <div id="waashero_plugin_email" class="waashero_tab_content" style="display: none;">
                    <table class="form-table">


                         <?php if($options['smtp_configured'] == 1) : ?>
                          <tr valign="top">
                            <th scope="row">
                                <?php _e("Email", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_smtp_enabled">
                                        <input type="checkbox" name="waashero[smtp_enabled]" id="waashero_smtp_enabled" value="1" <?php checked(1, $options['smtp_enabled']) ?> />
                                        <?php _e("Enable", "waashero"); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e("Enable Waashero built-in external SMTP functionality.", "waashero"); ?>                                       
                                    </p>
                                </fieldset>
                            </td>
                        </tr>

                              <tr valign="top">
                            <th scope="row">
                                <?php _e("From Email Address Prefix", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_smtp_from_email">
                                        <input type="text" placeholder="<?php echo $options['smtp_from_email']; ?>" autocomplete="off" name="waashero[smtp_from_email]" id="waashero_smtp_from_email" value="<?php echo $options['smtp_from_email']; ?>" size="64" class="regular-text code" />
                                        @<?php echo $options['smtp_configured_domain']; ?>
                                    </label>

                                    <p class="description">
                                        <?php _e("This email address will be used in the 'From' field.", "waashero"); ?>                                       
                                    </p>
                                </fieldset>
                            </td>
                        </tr>

                           <tr valign="top">
                            <th scope="row">
                                <?php _e("From Name", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_smtp_from_name">
                                        <input type="text" placeholder="Company Name" autocomplete="off" name="waashero[smtp_from_name]" id="waashero_smtp_from_name" value="<?php echo $options['smtp_from_name']; ?>" size="64" class="regular-text code" />                                       
                                    </label>

                                    <p class="description">
                                        <?php _e("This text will be used in the 'FROM' field.", "waashero"); ?>                                      
                                    </p>
                                </fieldset>
                            </td>
                        </tr>

                           <tr valign="top">
                            <th scope="row">
                                <?php _e("Force From Name Replacement", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_smtp_force_from_name">
                                        <input type="checkbox" name="waashero[smtp_force_from_name]" id="waashero_smtp_force_from_name" value="1" <?php checked(1, $options['smtp_force_from_name']) ?> />
                                        <?php _e("When enabled, the plugin will set the above From Name for each email. Disable it if you're using contact form plugins, it will prevent the plugin from replacing form submitter's name when contact email is sent.", "waashero"); ?>
                                    </label>
                                   
                                </fieldset>
                            </td>
                        </tr>

                            <tr valign="top">
                            <th scope="row">
                                <?php _e("Reply-To Email Address", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_smtp_reply_to_email">
                                        <input type="text" name="waashero[smtp_reply_to_email]" autocomplete="off" id="waashero_smtp_reply_to_email" value="<?php echo $options['smtp_reply_to_email']; ?>" size="64" class="regular-text code" />
                                       
                                    </label>

                                    <p class="description">
                                        <?php _e("Optional. This email address will be used in the 'Reply-To' field of the email. Leave it blank to use 'From' email as the reply-to value.", "waashero"); ?>                                       
                                    </p>
                                </fieldset>
                            </td>
                        </tr>

                               <tr valign="top">
                            <th scope="row">
                                <?php _e("Development Mode Email Address", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_smtp_dev_rec_add">
                                        <input type="text" name="waashero[smtp_dev_rec_add]" autocomplete="off" id="waashero_smtp_dev_rec_add" value="<?php echo $options['smtp_dev_rec_add']; ?>" size="64" class="regular-text code" />
                                       
                                    </label>

                                    <p class="description">
                                      When Waashero 'Development Mode' is enabled, all emails will be sent to this email address.<br />
                                      Useful for staging environments when testing functionalities that sends emails.                                 
                                    </p>
                                </fieldset>
                            </td>
                        </tr>

                        <?php else : ?>

                           <tr valign="top">
                            <th scope="row">
                                Configured SMTP credentials
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_smtp_configured">
                                        <input disabled="disabled" type="checkbox" name="waashero[smtp_configured]" id="waashero_smtp_configured" value="1" <?php checked(1, $options['smtp_configured']) ?> />
                                         Please install sending only email service. Check our <a href="https://waashero.com/support/email/set-sending-email-service" target="_blank">documentation</a>.
                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                        <?php endif; ?>


                        

                        

                      
                      

                    </table>
                </div>
            </div>
        </div>

        <?php wp_nonce_field('waashero_nonce', 'waashero_nonce'); ?>
        <?php submit_button() ?>
    </form>




</div><?php


	}

  public static function subsite_settings_page()
	{

        $cache = null;

        if(function_exists("apcu_cache_info")){
            $cache = apcu_cache_info();
        }

        $opcachestatus = "";
        $options = Waashero_Options::get_options();  
        
      ?>


<div class="wrap">
    
    <h1 class="wp-heading-inline">Hosting Settings</h1>
    <hr class="wp-header-end">
    <form method="post">
         <input type="hidden" name="waashero[dummy]"  value="1" />
      
     

        <script type="text/javascript">
	jQuery(function() {
	  
	  
	    jQuery('.nav-tab-wrapper a').click(function () {
	        var t = jQuery(this).attr('href');
	        jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
	        jQuery(this).addClass('nav-tab-active');
	        jQuery('.waashero_tab_content').hide();
	        jQuery(t).fadeIn(300);
	        return false;
	    })

	 
	});
        </script>

        <div class="plugin_config" style="margin-top:20px;">
                  <h2 class="nav-tab-wrapper">
                   <a href="#waashero_plugin_config-general" class="nav-tab nav-tab-active">General</a>
                   <a href="#waashero_plugin_config-cdn" class="nav-tab">CDN</a>                  
                   <a href="#waashero_plugin_config-op" class="nav-tab">OPcache</a>
                 
                 </h2>
            <div id="waashero_settings_tabs" style="padding:10px;">
                <div id="waashero_plugin_config-general" class="waashero_tab_content">
                    <table class="form-table">


                         
                        <tr valign="top">
                            <th scope="row">
                                Development Mode
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_development_mode">
                                        <select name="waashero[development_mode]" id="waashero_development_mode">
                                            <option <?php selected(0, $options['development_mode']) ?> value="0">Off</option>                                          
                                            <option <?php selected(1, $options['development_mode']) ?> value="1">Development</option>
                                           <!-- <option <?php selected(2, $options['development_mode']) ?> value="2">Development - LSCache</option>-->
                                        </select>                                      
                                      
                                         <p class="description">                                         
                                         <b>Development</b>: disable OPcache, Object-Cache, CDN, Web Application Firewall &amp; a few more configurations regardless of their statuses. <b>Recommended for development or debugging a possible issue</b>.<br /> 
                                          Take note, this option will <b>not disable</b> any caching plugin.
                                        </p>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                        
                       


                    </table>
                </div>
                <div id="waashero_plugin_config-cdn" class="waashero_tab_content" style="display: none;">
                    <table class="form-table">


                        <tr valign="top">
                            <th scope="row">
                                <?php _e("CDN", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_enable_cdn">
                                        <input type="checkbox" name="waashero[enable_cdn]" id="waashero_enable_cdn" value="1" <?php checked(1, $options['enable_cdn']) ?> />
                                        <?php _e("Enable Google Cloud CDN", "waashero"); ?>                                        
                                    </label>
                                   
                                </fieldset>
                            </td>
                        </tr>


                      

                      

                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Exclusions", "waashero"); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_excludes">
                                        <input type="text" name="waashero[excludes]" id="waashero_excludes" value="<?php echo $options['excludes']; ?>" size="64" class="regular-text code" placeholder=".php" />
                                       
                                    </label>

                                    <p class="description">
                                        <?php _e("Enter the exclusions (directories or extensions) separated by", "waashero"); ?>
                                        <code>,</code> <br />                                       
                                    </p>
                                </fieldset>
                            </td>
                        </tr>

                     





                      



                    </table>
                </div>                  
              
                <div id="waashero_plugin_config-op" class="waashero_tab_content" style="display: none;">
                    <table class="form-table">


                        <tr valign="top">
                            <th scope="row">
                                OPcache Settings
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_enable_opcache">
                                        <input type="checkbox" name="waashero[enable_opcache]" id="waashero_enable_opcache" value="1" <?php checked(1, $options['enable_opcache']) ?> />
                                        Enable
                                    </label>
                                      <p class="description">Improves the general PHP execution time.</p>
                                </fieldset>
                            </td>
                        </tr>



                    </table>
                </div>
             
            </div>
        </div>

        <?php wp_nonce_field('waashero_nonce', 'waashero_nonce'); ?>
        <?php submit_button() ?>
    </form>




</div><?php


	}
    
   public static function network_settings_page()
	{

       $options = Waashero_Options::get_options();  
        
      ?>


<div class="wrap">
    
    <h1 class="wp-heading-inline">Waashero Settings</h1>
    <hr class="wp-header-end">
    <form method="post">

     <input type="hidden" name="waashero[dummy]"  value="1" />
     

        <script type="text/javascript">
	jQuery(function() {
	  
	  
	    jQuery('.nav-tab-wrapper a').click(function () {
	        var t = jQuery(this).attr('href');
	        jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
	        jQuery(this).addClass('nav-tab-active');
	        jQuery('.waashero_tab_content').hide();
	        jQuery(t).fadeIn(300);
	        return false;
	    })

	 
	});
        </script>

        <div class="plugin_config" style="margin-top:20px;">
                  <h2 class="nav-tab-wrapper">
                   <a href="#waashero_plugin_config-general" class="nav-tab nav-tab-active">General</a>                  
                 </h2>
            <div id="waashero_settings_tabs" style="padding:10px;">
                <div id="waashero_plugin_config-general" class="waashero_tab_content">
                    <table class="form-table">


                         <tr valign="top">
                            <th scope="row">
                                Per Site Configuration
                            </th>
                            <td>
                                <fieldset>
                                    <label for="waashero_allow_per_site_config">
                                        <input type="checkbox" name="waashero[allow_per_site_config]" id="waashero_allow_per_site_config" value="1" <?php checked(1, $options['allow_per_site_config']) ?> />
                                        Enable
                                        <p class="description">
                                         Checking this option will enable the settings page on all subsites instead of using the primary site configuration.
                                        </p>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                        
                       


                    </table>
                </div>
                         
            
              
            </div>
        </div>




        <?php wp_nonce_field('waashero_nonce', 'waashero_nonce'); ?>
        <?php submit_button() ?>
    </form>




</div><?php


	}
    
}