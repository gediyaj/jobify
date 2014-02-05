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
class aam_View_Extension extends aam_View_Abstract {

    /**
     * Extensions Repository
     *
     * @var array
     *
     * @access private
     */
    private $_repository = array();

    /**
     * Constructor
     *
     * The filter "aam_cpanel" can be used to control the Control Panel items.
     *
     * @return void
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();

        //get repository
        $repository = aam_Core_API::getBlogOption('aam_extensions', array(), 1);
        if (is_array($repository)){
            $this->_repository = $repository;
        }
    }

    /**
     * Install extension
     *
     * @return string
     *
     * @access public
     */
    public function install(){
        $extension = new aam_Core_Extension;
        $license = aam_Core_Request::post('license');
        $ext = aam_Core_Request::post('extension');

        if ($license && $extension->add($ext, $license)){
            $response = array('status' => 'success');
        } else {
            $response = array('status' => 'failure');
        }

        return json_encode($response);
    }

    /**
     * Remove extension
     *
     * @return string
     *
     * @access public
     */
    public function remove(){
        $extension = new aam_Core_Extension;
        $license = aam_Core_Request::post('license');
        $ext = aam_Core_Request::post('extension');

        if ($extension && $extension->remove($ext, $license)){
            $response = array('status' => 'success');
        } else {
            $response = array('status' => 'failure');
        }

        return json_encode($response);
    }

    /**
     * Run the Manager
     *
     * @return string
     *
     * @access public
     */
    public function run() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/extension.phtml');
    }

    /**
     * Check if extensions exists
     *
     * @param string $extension
     *
     * @return boolean
     *
     * @access public
     */
    public function hasExtension($extension){
        return (isset($this->_repository[$extension]) ? true : false);
    }

    /**
     * Get Extension
     *
     * @param string $extension
     *
     * @return stdClass
     *
     * @access public
     */
    public function getExtension($extension){
        return ($this->hasExtension($extension) ? $this->_repository[$extension] : new stdClass);
    }

}