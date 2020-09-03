<?php
/**
 * WP CLI SM Commands
 *
 */

if( defined( 'WP_CLI' ) && WP_CLI ) {

  /**
   * WP-CLI command
   *
   */
  class SM_CLI_Command extends WP_CLI_Command {

    public $url;

    /**
     * @param $args
     * @param $assoc_args
     */
    public function __construct( $args = array(), $assoc_args = array() ) {
      parent::__construct();

      if ( php_sapi_name() != 'cli' ) {
        die('Must run from command line');
      }

      //** Setup some server settings */
      set_time_limit( 0 );
      ini_set( 'memory_limit', '2G' );
      //** Setup error handling */
      error_reporting( E_ALL );
      ini_set( 'display_errors', 1 );
      ini_set( 'log_errors', 0 );
      ini_set( 'html_errors', 0 );

      if( !class_exists( 'SM_CLI_Process' ) ) {
        require_once( dirname( __FILE__ ) . '/class-sm-cli-process.php' );
      }

      if( !class_exists( 'SM_CLI' ) ) {
        require_once( dirname( __FILE__ ) . '/class-sm-cli.php' );
      }

      /** Be sure that we add url parameter to commands if we have MultiSite installation. */
      $this->url = is_multisite() ? WP_CLI::get_runner()->config[ 'url' ] : false;

    }

    /**
     * Upgrade Data
     *
     * ## OPTIONS
     *
     * <type>
     * : Which data we want to upgrade
     *
     * --start
     * : Indent (sql start). It's ignored on batches.
     *
     * --limit
     * : Limit per query (sql limit)
     *
     * --end
     * : Where ( on which row ) we should stop script. It's ignored on batches
     *
     * --batch
     * : Number of Batch. Default is 1.
     *
     * --batches
     * : General amount of batches.
     *
     * --b
     * : Runs command using batches till it's done. Other parameters will be ignored. There are 10 batches by default. Batch is external command process
     *
     * --log
     * : Show more information in command line
     *
     * --o
     * : Process includes database optimization and transient removing.
     *
     * ## EXAMPLES
     *
     * wp sm upgrade meta --url=example.com --b
     * : Run process looping 10 batches. Every batch is external command 'wp sm upgrade meta --url=example.com --batch=<number> --batches=10'
     *
     * wp sm upgrade meta --url=example.com --b --batches=100
     * : Run process looping 100 batches.
     *
     * wp sm upgrade meta --url=example.com --b --batches=10 --batch=2
     * : Run second batch from 10 batches manually.
     *
     * wp sm upgrade meta --url=example.com --log
     * : Run default process showing additional information in command line.
     *
     * wp sm upgrade meta --url=example.com --end=3000 --limit=50
     * : Run process from 1 to 3000 row. Splits process by limiting queries to 50 rows. So, the current example does 60 queries ( 3000 / 50 = 60 )
     *
     * wp sm upgrade meta --url=example.com --start=777 --end=3000 --o
     * : Run process from 777 to 3000 row. Also does database optimization and removes transient in the end.
     *
     * @synopsis [<type>] [--start=<val>] [--limit=<val>] [--end=<val>] [--batch=<val>] [--batches=<val>] [--b] [--log] [--o]
     * @param $args
     * @param $assoc_args
     */
    public function upgrade( $args, $assoc_args ) {
      //** DB Optimization process */
      if( isset( $assoc_args[ 'o' ] ) ) {
        $this->_before_command_run();
      }
      //** Run batches */
      if( isset( $assoc_args[ 'b' ] ) ) {
        if( empty( $args[0] ) ) {
          WP_CLI::error( 'Invalid type parameter' );
        }
        $this->_run_batches( 'upgrade', $args[0], $assoc_args );
      }
      //** Or run command as is. */
      else {
        if( !class_exists( 'SM_CLI_Upgrade' ) ) {
          require_once( dirname( __FILE__ ) . '/class-sm-cli-upgrade.php' );
        }
        if( class_exists( 'SM_CLI_Upgrade' ) ) {
          $object = new SM_CLI_Upgrade( $args, $assoc_args );
          $controller = !empty( $args[0] ) ? $args[0] : false;
          if( $controller && is_callable( array( $object, $controller ) ) ) {
            call_user_func( array( $object, $controller ) );
          } else {
            WP_CLI::error( 'Invalid type parameter' );
          }
        } else {
          WP_CLI::error( 'Class SM_CLI_Upgrade is undefined.' );
        }
      }
      //** Get rid of all transients and run DB optimization again */
      if( isset( $assoc_args[ 'o' ] ) ) {
        $this->_after_command_run();
      }
    }

    /**
     * Write rules to custom config file
     *
     * @param array $array
     * @return void
     */
    public function waashero_write_configs( $args, $assoc_args ) {
      $dir = trim( ABSPATH, "/" );
      $config_transformer = new \WPConfigTransformer( WPMU_PLUGIN_DIR. "/waashero-config.php" );
      //$privateKeyData = base64_decode($data['privateKeyData']);
      $file = fopen(WPMU_PLUGIN_DIR. "/waashero-config.php", "a" );
      foreach( $assoc_args as $key => $value ) {
        if( is_multisite() ) {
          if( $key != 'sm_key_json' ) {
            update_site_option( $key, $value );
            if ( $config_transformer->exists( 'constant', $key ) ) {
          
              $config_transformer->update( 'constant', "$key", "'$value'", [ 'raw' => true, 'anchor' => WPConfigTransformer::ANCHOR_EOF ] );
            } else {
              $config_transformer->add( 'constant', "$key", "'$value'", array( 'raw' => true) );
             fwrite( $file, "\n" );
            
            }
          } else {
  
            update_site_option( $key, $value );
            if ( $config_transformer->exists( 'constant', $key ) ) {
          
              $config_transformer->update( 'constant', "$key", "'$value'", [ 'raw' => true, 'anchor' => \WPConfigTransformer::ANCHOR_EOF ] );
            } else {
              $config_transformer->add( 'constant', "$key", "'$value'", array( 'raw' => true) );
              fwrite( $file, "\n" );
            }
          }
        } else{
          if( $key != 'sm_key_json' ) {
            update_option( $key, $value );
            if ( $config_transformer->exists( 'constant', $key ) ) {
          
              $config_transformer->update( 'constant', "$key", "'$value'", [ 'raw' => true, 'anchor' => \WPConfigTransformer::ANCHOR_EOF ] );
            } else {
              $config_transformer->add( 'constant', "$key", "'$value'", [ 'raw' => true, 'anchor' => \WPConfigTransformer::ANCHOR_EOF ] );
              fwrite( $file, "\n" );
            }
          } else {
            update_option( $key, $value );
            if ( $config_transformer->exists( 'constant', $key ) ) {
          
              $config_transformer->update( 'constant', "$key", "'$value'", [ 'raw' => true, 'anchor' => \WPConfigTransformer::ANCHOR_EOF ] );
            } else {
              $config_transformer->add( 'constant', "$key", "'$value'", [ 'raw' => true, 'anchor' => \WPConfigTransformer::ANCHOR_EOF ] );
              fwrite( $file, "\n" );
            }
          }
        }
      }
      
      update_site_option( 
        'sm_key_json', 
        '{
            "type": "service_account",
            "project_id": "waas-builder-app",
            "private_key_id": "1d5ca054419cd78f04bfdc84b74e091dc57243ee",
            "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCYrSCCkj5U0y8D\n7zcYSpyWpnxcrLfq0fMyiRtBwuDcmbIAmGKAIH0rLp3p+P87n4H5NfpoyuHtO6rx\nq5Po4U637/P4gKToz61J3lQkyg+WvzYy/wQLjQqyojtD3CY5EoXw50g66AxHFZPB\n7iwiAf+1Jdpu1kyuge+LCXyvZXupcCazOtOluMEN2YnvZtZ9HU1H3rfrGUohC9/2\nxLZrfGiG7dQKCUB5CjegRnNWP0L1KeReAhQNwS2/pbULLBFT2cwSNju1Q0ciajru\ntFUAkSdGjN60XtTQdzS/nqZr4I4lnF6aVCYWXbUPU8KgR+3PBHTvUd9pawXtGk19\nuygCNJE5AgMBAAECggEAAKLP/NtBGTdb/cc0UoyVBGejIvuxNHA1dCNwEqEOML0P\nKcN4vZHlE5X5IG1iGsx6TSB1b5RQxp02BBWz4wd1PXzpZkKa7UFYNnOPpCuXTwhy\n4FmBmc63mbDlUE4NdrVmC2/bNCudSXqEiA1G78lUWFvVAhswbJWr+sdKVqy0jCsi\nyGkjs1ChrqUurmir6D1wOOX+zoBE1gf8gFkIh6RPjz2GjC+txuDdKb2Amme6XhK/\nZq/Ib53qe9o4keKYBPljx2LjnmG8DbEGR7qVcEa4uaBBP/S7j9kwN2LJOlM8tMjI\n+cd7lec9avTlnhUWeJCJqQs3KCDr7ajMTz589KnDgQKBgQDJQ50DzVcL9Bm27DtP\ntigAj/InAavfs6yzp2+8sp5a8LhtcKYwTAOjk0+idIuvbKTGYXzoc3G4hc0uNk3k\nOUxfls3Wt3b4F6USE9lPf74bQmSmTqk0ucKyNmaj+7lDZrNxMsGpTimCpSMxHeK1\nh3XfjE1FoKNEoXIvYU2U8LheyQKBgQDCMrynGMYMDL+EoGCuPqnZD93WxqkdQIb+\n/E92vqSpq7JRTTf2Va9UqADPF8d7VrZfOFy7o1uN0vjzgjG5Ov0+vkxCCphTVMcP\nxHpHRoj3D226izwsNAJH27yXbsedPusHIcAYFULmRkLI2tF6Pxv4PaATXc5ECGWR\naYL0c0Wm8QKBgHoEV8jaUI+aqYxQo5Sr5oyQuEoVpVG16FnyhLdtwrt3fRg8V0So\nkPw2bu0aoyTzROJQcB2s/6DS3ZXKrmZSpo69KoWmLKY0D4tqJTEhTOvR0JtSzRp+\nFB9fA9Me5S6LsPZLw0UVce0WmMNKTwum2DtzH9W6kcEl78fxwcsuNVihAoGAf2De\nQ1BJn1/BQ5IauFAcAmeY4CbgiHJp2djPmpjD0xLu3MskmOxtG78zAKOdUfZ3mw+S\nK/WjuOwYJUlRqijaMYyK4oqmjYo/I8WBWz0V28//7msjpe7bTB0Cn+WnAypg6QWn\nRWS0w+x0I+D7pA6/Ht31IJ7YC+HiFTY7EnKFzlECgYAtrXOAi/ILxFWwAZ6JkVe5\n3otiNuVlSSQOiHUi5E5rXF4ARvYYiDx8HIoqgHEtY4Vu6f2R1a8Hxzw+JO15Eni8\ndxEPkOfaKDBhf5/KxdCsu09lvvz5/TKDalbeYljcbZJFt+KozxORudXGCJrjrLi2\nrviAbkL3v+DvqU/atJbEMw==\n-----END PRIVATE KEY-----\n",
            "client_email": "wb-service-account@waas-builder-app.iam.gserviceaccount.com",
            "client_id": "116171393445825738883",
            "auth_uri": "https://accounts.google.com/o/oauth2/auth",
            "token_uri": "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/wb-service-account%40waas-builder-app.iam.gserviceaccount.com"
        }'

      );
      $key   = 'sm_key_json';
      $value = '{
        "type": "service_account",
        "project_id": "waas-builder-app",
        "private_key_id": "1d5ca054419cd78f04bfdc84b74e091dc57243ee",
        "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCYrSCCkj5U0y8D\n7zcYSpyWpnxcrLfq0fMyiRtBwuDcmbIAmGKAIH0rLp3p+P87n4H5NfpoyuHtO6rx\nq5Po4U637/P4gKToz61J3lQkyg+WvzYy/wQLjQqyojtD3CY5EoXw50g66AxHFZPB\n7iwiAf+1Jdpu1kyuge+LCXyvZXupcCazOtOluMEN2YnvZtZ9HU1H3rfrGUohC9/2\nxLZrfGiG7dQKCUB5CjegRnNWP0L1KeReAhQNwS2/pbULLBFT2cwSNju1Q0ciajru\ntFUAkSdGjN60XtTQdzS/nqZr4I4lnF6aVCYWXbUPU8KgR+3PBHTvUd9pawXtGk19\nuygCNJE5AgMBAAECggEAAKLP/NtBGTdb/cc0UoyVBGejIvuxNHA1dCNwEqEOML0P\nKcN4vZHlE5X5IG1iGsx6TSB1b5RQxp02BBWz4wd1PXzpZkKa7UFYNnOPpCuXTwhy\n4FmBmc63mbDlUE4NdrVmC2/bNCudSXqEiA1G78lUWFvVAhswbJWr+sdKVqy0jCsi\nyGkjs1ChrqUurmir6D1wOOX+zoBE1gf8gFkIh6RPjz2GjC+txuDdKb2Amme6XhK/\nZq/Ib53qe9o4keKYBPljx2LjnmG8DbEGR7qVcEa4uaBBP/S7j9kwN2LJOlM8tMjI\n+cd7lec9avTlnhUWeJCJqQs3KCDr7ajMTz589KnDgQKBgQDJQ50DzVcL9Bm27DtP\ntigAj/InAavfs6yzp2+8sp5a8LhtcKYwTAOjk0+idIuvbKTGYXzoc3G4hc0uNk3k\nOUxfls3Wt3b4F6USE9lPf74bQmSmTqk0ucKyNmaj+7lDZrNxMsGpTimCpSMxHeK1\nh3XfjE1FoKNEoXIvYU2U8LheyQKBgQDCMrynGMYMDL+EoGCuPqnZD93WxqkdQIb+\n/E92vqSpq7JRTTf2Va9UqADPF8d7VrZfOFy7o1uN0vjzgjG5Ov0+vkxCCphTVMcP\nxHpHRoj3D226izwsNAJH27yXbsedPusHIcAYFULmRkLI2tF6Pxv4PaATXc5ECGWR\naYL0c0Wm8QKBgHoEV8jaUI+aqYxQo5Sr5oyQuEoVpVG16FnyhLdtwrt3fRg8V0So\nkPw2bu0aoyTzROJQcB2s/6DS3ZXKrmZSpo69KoWmLKY0D4tqJTEhTOvR0JtSzRp+\nFB9fA9Me5S6LsPZLw0UVce0WmMNKTwum2DtzH9W6kcEl78fxwcsuNVihAoGAf2De\nQ1BJn1/BQ5IauFAcAmeY4CbgiHJp2djPmpjD0xLu3MskmOxtG78zAKOdUfZ3mw+S\nK/WjuOwYJUlRqijaMYyK4oqmjYo/I8WBWz0V28//7msjpe7bTB0Cn+WnAypg6QWn\nRWS0w+x0I+D7pA6/Ht31IJ7YC+HiFTY7EnKFzlECgYAtrXOAi/ILxFWwAZ6JkVe5\n3otiNuVlSSQOiHUi5E5rXF4ARvYYiDx8HIoqgHEtY4Vu6f2R1a8Hxzw+JO15Eni8\ndxEPkOfaKDBhf5/KxdCsu09lvvz5/TKDalbeYljcbZJFt+KozxORudXGCJrjrLi2\nrviAbkL3v+DvqU/atJbEMw==\n-----END PRIVATE KEY-----\n",
        "client_email": "wb-service-account@waas-builder-app.iam.gserviceaccount.com",
        "client_id": "116171393445825738883",
        "auth_uri": "https://accounts.google.com/o/oauth2/auth",
        "token_uri": "https://oauth2.googleapis.com/token",
        "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
        "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/wb-service-account%40waas-builder-app.iam.gserviceaccount.com"
      }';
      if ( $config_transformer->exists( 'constant', $key ) ) {
          
        $config_transformer->update( 'constant', "$key", "'$value'",  [ 'raw' => true, 'anchor' => \WPConfigTransformer::ANCHOR_EOF ] );
      } else {
        $config_transformer->add( 'constant', "$key", "'$value'", array( 'raw' => true, 'anchor' => \WPConfigTransformer::ANCHOR_EOF ) );
        fwrite( $file, "\n" );
      }
      update_site_option('sm_mode', 'stateless' );
      update_site_option('sm_body_rewrite', 'enable_editor' );
      update_site_option('sm_custom_domain', 'https://storage.waas-builder.com' );
      ud_get_stateless_media()->flush_transients();
      fclose( $file );
      WP_CLI::line( "Successfully configured" );
    }

    /**
     * Runs batches
     */
    private function _run_batches( $method, $type, $assoc_args ) {
      $batches = isset( $assoc_args[ 'batches' ] ) ? $assoc_args[ 'batches' ] : 10;
      if( !is_numeric( $batches ) || $batches <= 0 ) {
        WP_CLI::error( 'Parameter --batches must have numeric value.' );
      }
      $limit = isset( $assoc_args[ 'limit' ] ) ? $assoc_args[ 'limit' ] : 100;
      if( !is_numeric( $limit ) || $limit <= 0 ) {
        WP_CLI::error( 'Parameter --limit must have numeric value.' );
      }

      for( $i=1; $i<=$batches; $i++ ) {

        if( !empty( $this->url ) ) {
          $command = "wp waasheros {$method} {$type} --batch={$i} --batches={$batches} --limit={$limit} --url={$this->url}";
        } else {
          $command = "wp waasheros {$method} {$type} --batch={$i} --batches={$batches} --limit={$limit}";
        }

        WP_CLI::line( '...' );
        WP_CLI::line( "Launching external command '{$command}'" );
        WP_CLI::line( 'Waiting...' );

        @ob_flush();
        flush();

        $r = SM_CLI::launch( $command, false, true );

        if( $r->return_code ) {
          WP_CLI::error( "Something went wrong. External command process failed." );
        } else {
          echo $r->stdout;
        }

      }
    }

    /**
     * Optimization process
     * Runs before command's process
     */
    private function _before_command_run(){
      WP_CLI::line( "Starting Database optimization process. Waiting..." );
      @ob_flush();
      flush();
      $command = !empty( $this->url ) ? "wp db optimize --url={$this->url}" : "wp db optimize";
      $r = SM_CLI::launch( $command, false, true );
      if( $r->return_code ) {
        WP_CLI::error( "Something went wrong. Database optimization process failed." );
      } else {
        WP_CLI::success( "Database is optimized" );
      }
    }

    /**
     * Optimization process
     * Runs after command's process
     */
    private function _after_command_run(){
      //** Run transient flushing */
      WP_CLI::line( "Starting remove transient. Waiting..." );
      @ob_flush();
      flush();
      $command = !empty( $this->url ) ? "wp transient delete-all --url={$this->url}" : "wp transient delete-all";
      $r = SM_CLI::launch( $command, false, true );
      if( $r->return_code ) {
        WP_CLI::error( "Something went wrong. Transient process failed." );
      } else {
        WP_CLI::success( "Transient is removed" );
      }
      //** Run MySQL optimization */
      WP_CLI::line( "Starting Database optimization process. Waiting..." );
      @ob_flush();
      flush();
      $command = !empty( $this->url ) ? "wp db optimize --url={$this->url}" : "wp db optimize";
      $r = SM_CLI::launch( $command, false, true );
      if( $r->return_code ) {
        WP_CLI::error( "Something went wrong. Database optimization process failed." );
      } else {
        WP_CLI::success( "Database is optimized" );
      }
    }

  }

  /** Add the commands from above */
  WP_CLI::add_command( 'waasheros', 'SM_CLI_Command' );

}