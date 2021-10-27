<?php

defined( 'ABSPATH' ) or exit;

class Waashero {



	public static function instance() {
		new self();
	}

	public function __construct() {

		// Add hook to the new blog/subsite creation to create a DNS record
		if ( $this->uses_waashero() && defined( 'ULTIMO_FALSE' ) ) {
			add_action(
				'wp_insert_site',
				array(
					$this,
					'createRecordForNewSubsite',
				)
			);

			add_action(
				'wp_delete_site',
				array(
					$this,
					'DeleteRecordForOldSubsite',
				)
			);
		}
				  // mute core update email
		if ( ( defined( 'DOING_AUTOSAVE' )
            && DOING_AUTOSAVE )
            or ( defined( 'DOING_CRON' ) && DOING_CRON )
            or ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            or ( defined( 'XMLRPC_REQUEST' )
            && XMLRPC_REQUEST ) ) {

			if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				if ( $this::user_can_manage_admin_settings() && is_admin() ) {

					add_action(
						'wp_ajax_waashero_add_domain_alias',
						array(
							'Waashero_Ajax',
							'waashero_add_domain_alias',
						)
					);

					add_action(
						'wp_ajax_waashero_notifications',
						array(
							'Waashero_Ajax',
							'waashero_notifications',
						)
					);

				}
			}

			return;
		}

		if ( $this::user_can_manage_admin_settings()
			&& ( is_network_admin()
			|| is_admin() ) ) {

			add_action(
				'admin_enqueue_scripts',
				function () {
					wp_enqueue_style(
                        'waashero_css',
                        '/wp-content/mu-plugins/waashero/css/waashero.css'
                    );
					wp_enqueue_script(
                        'waashero_js',
                        '/wp-content/mu-plugins/waashero/js/settings.domains.js'
                    );
				}
			);


			// future use to add development mode
			// self::admin_notice_development_mode();

			// Confirm Domain Pending SSL certificate
			add_action(
				'admin_init',
				array(
					&$this,
					'checkDomainSSLCert',
				)
			);
		}

	}

    /**
     * Check if it uses our plugin
     *
     * @return void
     */
	private function uses_waashero() {
		return defined( 'DEFAULT_HOOKS' ) && DEFAULT_HOOKS;
	}
    
	/**
	 * Confirm SSL Certficate for domain
	 *
	 * @return void
	 */
	function checkDomainSSLCert() {
		$ssl_certificate_confirmation_key = get_current_user_id() . '_ssl_flag';
		$is_confirmation_pending          = get_option( $ssl_certificate_confirmation_key );
		if ( ! empty( $is_confirmation_pending['domain'] ) && ( ! empty( $is_confirmation_pending['status'] ) && $is_confirmation_pending['status'] == 'pending' ) ) {
			Waashero_Api::confirmSSLCert( $is_confirmation_pending['domain'] );
		}
	}

	/**
	 * Add functionality to create an DNS A record on subsite creation
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

		$add = Waashero_Api::AddDomainAlias( $data->domain, $data->blog_id );

	}

	/**
	 * Deletes site
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
	function DeleteRecordForOldSubsite( $data ) {
		$add = Waashero_Api::DeleteDomainAlias( $data->domain, $data->blog_id );
	}

    /**
     * Check for admin options
     *
     * @return void
     */
	public static function user_can_manage_admin_settings() {

		$capability = is_network_admin() ? 'manage_network_options' : 'manage_options';

		if ( current_user_can( $capability ) ) {

			return true;
		}

		return false;
	}

}

return new Waashero();
