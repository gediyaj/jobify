<?php

/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

/**
 *
 * @package AAM
 * @author Vasyl Martyniuk <support@wpaam.com>
 * @copyright Copyright C 2013 Vasyl Martyniuk
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
final class aam_Core_API {

    /**
     * Get current blog's option
     *
     * @param string $option
     * @param mixed  $default
     * @param int    $blog_id;
     *
     * @return mixed
     *
     * @access public
     * @static
     */
    public static function getBlogOption($option, $default = FALSE, $blog_id = null) {
        if (is_multisite()) {
            $blog = (is_null($blog_id) ? get_current_blog_id() : $blog_id);
            $response = get_blog_option($blog, $option, $default);
        } else {
            $response = get_option($option, $default);
        }

        return $response;
    }

    /**
     * Update Blog Option
     *
     * @param string $option
     * @param mixed  $data
     * @param int    $blog_id
     *
     * @return bool
     *
     * @access public
     * @static
     */
    public static function updateBlogOption($option, $data, $blog_id = null) {
        if (is_multisite()) {
            $blog = (is_null($blog_id) ? get_current_blog_id() : $blog_id);
            $response = update_blog_option($blog, $option, $data);
        } else {
            $response = update_option($option, $data);
        }

        return $response;
    }

    /**
     * Delete Blog Option
     *
     * @param string $option
     * @param int    $blog_id
     * 
     * @return bool
     *
     * @access public
     * @static
     */
    public static function deleteBlogOption($option, $blog_id = null) {
         if (is_multisite()) {
            $blog = (is_null($blog_id) ? get_current_blog_id() : $blog_id);
            $response = delete_blog_option($blog, $option);
        } else {
            $response = delete_option($option);
        }
        
        return $response;
    }

    /**
     * Initiate HTTP request
     *
     * @param string $url Requested URL
     * @param bool $send_cookies Wheather send cookies or not
     * @param bool $return_content Return content or not
     * @return bool Always return TRUE
     */
    public static function cURL($url, $send_cookies = TRUE, $return_content = FALSE) {
        $header = array(
            'User-Agent' => aam_Core_Request::server('HTTP_USER_AGENT')
        );

        $cookies = array();
        if (is_array($_COOKIE) && $send_cookies) {
            foreach ($_COOKIE as $key => $value) {
                //SKIP PHPSESSID - some servers does not like it for security reason
                if ($key !== 'PHPSESSID') {
                    $cookies[] = new WP_Http_Cookie(array(
                        'name' => $key,
                        'value' => $value
                    ));
                }
            }
        }

        $res = wp_remote_request($url, array(
            'headers' => $header,
            'cookies' => $cookies,
            'timeout' => 5)
        );

        if (is_wp_error($res)) {
            $result = array(
                'status' => 'error',
                'url' => $url
            );
        } else {
            $result = array('status' => 'success');
            if ($return_content) {
                $result['content'] = $res['body'];
            }
        }

        return $result;
    }

    /**
     * Check whether it is Multisite Network panel
     * 
     * @return boolean
     * 
     * @access public
     */
    public static function isNetworkPanel() {
        return (is_multisite() && is_network_admin() ? TRUE : FALSE);
    }
    
    /**
     * Check if SSL is used
     * 
     * @return boolean
     * 
     * @access public
     * @static
     */
    public static function isSSL(){
       if (force_ssl_admin()){
           $response = true;
       } elseif (aam_Core_Request::server('HTTPS')){
           $response = true;
       } elseif (aam_Core_Request::server('REQUEST_SCHEME') == 'https'){
           $response = true;
       } else {
           $response = false;
       }

       return $response;
   }

}