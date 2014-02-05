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
final class aam_Core_Update {

    /**
     * List of stages
     *
     * @var array
     *
     * @access private
     */
    private $_stages = array();

    /**
     * Constructoor
     *
     * @return void
     *
     * @access public
     */
    public function __construct() {
        //register update stages
        $this->_stages = apply_filters('aam_update_stages', array(
            array($this, 'downloadRepository'),
            array($this, 'flashCache'),
            //array($this, 'muPlugin'), - TODO. Deactivated for futher use
            array($this, 'updateFlag')
        ));
    }

    /**
     * Run the update if necessary
     *
     * @return void
     *
     * @access public
     */
    public function run() {
        foreach ($this->_stages as $stage) {
            //break the change if any stage failed
            if (call_user_func($stage) === false) {
                break;
            }
        }
    }

    /**
     * Download the Extension Repository
     *
     * This forces the system to retrieve the new set of extensions based on
     * license key
     *
     * @return boolean
     *
     * @access public
     */
    public function downloadRepository() {
        $response = true;
        if ($extensions = aam_Core_API::getBlogOption('aam_extensions')) {
            if (is_array($extensions)){
                $extension = new aam_Core_Extension();
                $extension->download();
            }
        }

        return $response;
    }

    /**
     * Flash all cache
     *
     * @return boolean
     *
     * @access public
     */
    public function flashCache(){
        global $wpdb;

        //clear visitor's cache first
        if (is_multisite()) {
            //get all sites first and iterate through each
            $query = 'SELECT blog_id FROM ' . $wpdb->blogs;
            $blog_list = $wpdb->get_results($query);
            if (is_array($blog_list)) {
                foreach ($blog_list as $blog) {
                    $query = 'DELETE FROM ' . $wpdb->get_blog_prefix($blog->blog_id) . 'options ';
                    $query .= 'WHERE `option_name` = "aam_visitor_cache"';
                    $wpdb->query($query);
                }
            }
        } else {
            $query = 'DELETE FROM ' . $wpdb->options . ' ';
            $query .= 'WHERE `option_name` = "aam_visitor_cache"';
            $wpdb->query($query);
        }

        //clear users cache
        $query = 'DELETE FROM ' . $wpdb->usermeta . ' ';
        $query .= 'WHERE `meta_key` = "aam_cache"';
        $wpdb->query($query);

        return true;
    }
    
    /**
     * Create Must-Use Plugin
     * 
     * Is used to hook into the earliest system load
     * 
     * @return boolean
     * 
     * @access public
     */
    public function muPlugin() {
        $base_dir = WP_CONTENT_DIR . '/mu-plugins';

        $continue = (file_exists($base_dir) ? true : @mkdir($base_dir, 0755));

        if ($continue) {
            $hook_file = $base_dir . '/aam.php';
            //remove current hook and replace with newer version
            if (file_exists($hook_file)) {
                @unlink($hook_file);
            }
            @copy(dirname(__FILE__) . '/mu.php', $base_dir . '/aam.php');
        }

        return true;
    }

    /**
     * Change the Update flag
     *
     * This will stop to run the update again
     *
     * @return boolean
     *
     * @access public
     */
    public function updateFlag() {
        return aam_Core_API::updateBlogOption('aam_updated', AAM_VERSION, 1);
    }

}