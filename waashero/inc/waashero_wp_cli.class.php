<?php

defined('ABSPATH') OR exit;


if (!defined( 'WP_CLI' ) || !WP_CLI ) return;

/**
 * waashero_wp_cli short summary.
 *
 * waashero_wp_cli description.
 *
 * @version 1.0
 * @author Waashero
 */





class Waashero_WP_CLI {
    
    /**
     * Get HalfElf Stats
     *
     * ## EXAMPLES
     *
     * wp halfelf stats
     *
     */
    public function devmode( $args ) {

        $state = $args[0];

        if( $state =='enable' ) {           

            $options = Waashero_Options::get_options();

            $options['development_mode'] = 1;
            Waashero_Options::save_options( $options );
           

            Waashero::SetObjectCache(true);
         

            Waashero::insert_htaccess_rules();

            WP_CLI::success( __( 'Development mode is enabled.', 'waashero' ) );
            
        } elseif( $state =='disable' ) {
            

            $options = Waashero_Options::get_options();

            $options['development_mode'] = 0;
            Waashero_Options::save_options($options);
           

            Waashero::SetObjectCache(true);
         

            Waashero::insert_htaccess_rules();
            WP_CLI::success( __( 'Development mode is disabled.', 'waashero' ) );
        } else {
            WP_CLI::error( __( 'Wrong argument', 'waashero' ) ); 
        }

       
    }


    public function install_smtp( $args, $assoc_args ) {


        $username = $assoc_args['username'];
        $password = $assoc_args['password'];
        $from_email = $assoc_args['from_email'];       

        $options = Waashero_Options::get_options();  
        $options['smtp_configured'] = '1';
        $options['smtp_enabled'] = '1';
        $options['smtp_configured_domain'] =explode('@',$username)[1];
        $options['smtp_username'] = $username;
        $options['smtp_password'] = $password;
        $options['smtp_from_email'] = $from_email;       

   
        Waashero_Options::save_options( $options );
        WP_CLI::success( __( 'SMTP is configured.', 'waashero' ) );
        
    }

    public function objectcache( $args, $assoc_args ) {

   
        $options = Waashero_Options::get_options();
        $options['enable_object_cache'] = '0';
        Waashero_Options::save_options( $options );
        WP_CLI::success( __( 'Object-Cache is disabled.', 'waashero' ) );
        
    }

    public function exposed_function() {

        // give output
        WP_CLI::success( 'hello from exposed_function() !' );

    }

    /**
     * Overrides Htaccess file
     *
     * @param [string] $token
     * @param [string] $domains
     * @return void
     */
    public function override_htaccess_file( $token, $domains ) {
        $insertion = array( '', '## Do not edit the contents of this block! ##' );
        array_push( $insertion, 'RewriteEngine On' );
        $whitelisted = get_site_option( 'waashero_dynamic_ip_whitelist' );
        if( !$whitelisted || !is_array( $whitelisted ) ) {
            $whitelisted = array();
        }

        foreach ( $whitelisted as $key => $value ) {            
            array_push( $insertion,'SetEnvIfNoCase Remote_Addr ^'.$key.'$ MODSEC-OFF');
            array_push( $insertion,
                'RewriteCond %{REMOTE_ADDR} ^'. str_replace( '.','\.',$key ) .'$',
                'RewriteRule .* - [E=noconntimeout:1]');
        }


        array_push( $insertion, '' );

        $marker   = "WAASHERO";
        $uploads  = wp_upload_dir( null, false );
        $dir = trim( ABSPATH, "/" );
        $filename = '';
        if( file_exists( $dir.'\.htaccess' ) ) {
            $filename = $dir.'\.htaccess';
        } else {
            WP_CLI::error( __( 'Wrong path', 'waashero' ) );
        }
        

		if ( ! file_exists( $filename ) ) {
			if ( ! is_writable( dirname( $filename ) ) ) {
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
			flock( $fp, LOCK_UN ) ;
			fclose( $fp ) ;

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
		fseek( $fp, 0 ) ;
		$bytes = fwrite( $fp, $new_file_data ) ;
		if ( $bytes ) {
			ftruncate( $fp, ftell( $fp ) ) ;
		}
		fflush( $fp ) ;
		flock( $fp, LOCK_UN ) ;
		fclose( $fp ) ;
    }

    /**
     * Overrides WP Config Files
     *
     * @param [string] $token
     * @param [string] $domains
     * @return void
     */
    public function override_wp_config_file( $token, $domains ) {
        if ( file_exists (ABSPATH . "wp-config.php") && is_writable (ABSPATH . "wp-config.php") ){
            $this->wp_config_put('', $token, $domains );
        } elseif ( file_exists (dirname (ABSPATH) . "/wp-config.php") && is_writable (dirname (ABSPATH) . "/wp-config.php")){
            $this->wp_config_put('/', $token, $domains );
        } else { 
            WP_CLI::error( __( 'Wrong path', 'waashero' ) );;
        }
    }

    public function wp_config_put( $slash = '', $token, $domains ) {
        $config = file_get_contents (ABSPATH . "wp-config.php");
        $config = preg_replace ("/^([\r\n\t ]*)(\<\?)(php)?/i", "<?php define('WP_Token', $token );", $config);
        file_put_contents( ABSPATH . $slash . "wp-config.php", $config );
    }
   
   
}