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
    public static function GetDomains()
    {

        $endpoint = '/domains/'; //must include endslash
        $authorization = "Authorization: Bearer " . WAASHERO_CLIENT_API_KEY;

        try {
            $ch = curl_init();

            // set header with token
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', $authorization));
            // set url
            curl_setopt($ch, CURLOPT_URL, WAASHERO_CLIENT_API_URL . $endpoint . WAASHERO_CLIENT_SERVER_ID);

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            // $output contains the output string
            $output = curl_exec($ch);

            // close curl resource to free up system resources
            curl_close($ch);


            $result = json_decode($output, true);

            if ($result['success'] == true) {

                return $result['domains'];
            }

            return null;
        } catch (Exception $e) {
            return null;
        }

    }

    /**
     * Confirm SSL Certificate
     * @param [type] $domain
     * @return void
     */
    public static function confirmSSLCert($domain)
    {
        $notification_key = get_current_user_id() . '_domain_notifications';
        $sslcert_notification_response = [];
        $endpoint = WAASHERO_CLIENT_API_URL . '/sslcert?domain=' . $domain;

        $ssl_response = self::curlHandler($endpoint, 'GET');
        if (!empty($ssl_response['code']) && $ssl_response['code'] == '200') {
            $sslcert_notification_response['success'] =  'SSL certificate successfully created for domain ' . $domain;
        } else {
            $sslcert_notification_response['error'] = (str_replace(".", '', $ssl_response['message'])) . ' for domain ' . $domain;
        }
        // Remove SSL Confirmation flag from DB on success
        if (!empty($sslcert_notification_response['success'])) {
            $ssl_certificate_confirmation_key = get_current_user_id() . '_ssl_flag';
            delete_option($ssl_certificate_confirmation_key);
        }
        self::setWaasheroNotifications($notification_key, $sslcert_notification_response);
        return null;
    }

    /**
     * Curl Handler
     *
     * @param [string] $url
     * @param string $method
     * @param [string] $data
     * @return void
     */
    private static function curlHandler($url, $method = 'POST', $fields_string = '')
    {

        $authorization = "Authorization: Bearer " . WAASHERO_CLIENT_API_KEY;
        try {

            $ch = curl_init();

            // set header with token
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', $authorization));
            // set url
            curl_setopt($ch, CURLOPT_URL, $url);

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            if ($method == "POST") {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            }

            // $output contains the output string
            $output = curl_exec($ch);
            // close curl resource to free up system resources
            curl_close($ch);
            return  (!empty($output) ? json_decode($output, true) : []);

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Sets notifications
     *
     * @param [string] $key
     * @param [arrray] $value
     * @return void
     */
    private static function setWaasheroNotifications($key, $value)
    {

        $messages = [];
        $notificatinos = get_option($key);
        if (!empty($notificatinos)) {
            $notificatinos = (!empty($value['success']) ? $value : array_merge($notificatinos, $value));
            return update_option($key, $value);
        } else {
            return update_option($key, $value);
        }
    }

    /**
     * Adds domain alias
     *
     * @param [type] $domain
     * @return void
     */
    public static function AddDomainAlias($domain)
    {

        $wildcard = false;
        if (strpos($domain, '*.') === 0) {
            $wildcard = true;
        }
        $data = array(
            'domain' => $domain,
            'wildcard' => $wildcard
        );

        $data = http_build_query($data);
        $endpoint = WAASHERO_CLIENT_API_URL . '/ultimo/domain/' . WAASHERO_CLIENT_SERVER_ID;

        try {

            $notification_key = get_current_user_id() . '_domain_notifications';
            $ssl_certificate_confirmation_key = get_current_user_id() . '_ssl_flag';
            $domain_notification_response = [];
            $response = self::curlHandler($endpoint, 'POST', $data);
            if (!empty($response['message']))
                $domain_notification_response['success'] = $response['message'];
            else
                $domain_notification_response['error'] = "Sorry could not add domain " . $domain;

            // Log Api Response for Notifications
            $confirmation_data['domain'] = $domain;
            $confirmation_data['status'] = 'pending';
            delete_option($ssl_certificate_confirmation_key);
            self::setWaasheroNotifications($ssl_certificate_confirmation_key, $confirmation_data);
            // Override the Api response message with  wp ultimo add domain message
            //self::setWaasheroNotifications($notification_key, $domain_notification_response);
            return null;

         } catch (Exception $e) {
             return null;
         }

    }

    /**
     * Get json encodedlist of notifications
     *
     * @return void
     */
    public static function getWaasheroNotifications()
    {
        $notification_key = get_current_user_id() . '_domain_notifications';
        $notificatinos = get_option($notification_key);
        if (!empty($notificatinos)) {
            // Flush Notification on Read
            delete_option($notification_key);
            $js_response['success'] = true;
            $js_response['messages'] = $notificatinos;
            echo json_encode($js_response);
            die;
        } else {
            $js_response['success'] = false;
            $js_response['messages'] = '';
            echo json_encode($js_response);
            die;
        }
    }
}

return new Waashero_Api();