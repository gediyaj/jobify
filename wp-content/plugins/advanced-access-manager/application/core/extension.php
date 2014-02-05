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
class aam_Core_Extension {

    /**
     * Extensions download Failed
     */
    const STATUS_FAILED = 'failed';

    /**
     * Extensions status pending
     */
    const STATUS_PENDING = 'pending';

    /**
     * Extensions installed successfully
     */
    const STATUS_INSTALLED = 'installed';

    /**
     * Basedir to Extentions repository
     *
     * @var string
     *
     * @access private
     */
    private $_basedir = '';

    /**
     *
     * @var type
     */
    private $_cache = array();

    /**
     * Main AAM class
     *
     * @var aam
     *
     * @access private
     */
    private $_parent;

    /**
     * Consturctor
     *
     * @return void
     *
     * @access public
     */
    public function __construct(aam $parent = null) {
        $this->setParent($parent);
        $this->_basedir = AAM_BASE_DIR . 'extension';
    }

    /**
     * Load active extensions
     *
     * @return void
     *
     * @access public
     */
    public function load() {

        //iterate through each active extension and load it
        foreach (scandir($this->_basedir) as $module) {
            if (!in_array($module, array('.', '..'))) {
                $this->bootstrapExtension($module);
            }
        }
    }

    /**
     *
     */
    public function download() {
        $this->initFilesystem();
        $repository = aam_Core_API::getBlogOption('aam_extensions', array(), 1);

        if (is_array($repository)) {
            //get the list of extensions
            foreach ($repository as $extension => $data) {
                if ($this->retrieve($data->license)) {
                    $repository[$extension]->status = self::STATUS_INSTALLED;
                } else {
                    $repository[$extension]->status = self::STATUS_FAILED;
                }
            }
            aam_Core_API::updateBlogOption('aam_extensions', $repository, 1);
        }
    }

    /**
     * Add new extension to repository
     *
     * @param string $extension
     * @param string $license
     *
     * @return boolean
     *
     * @access public
     */
    public function add($extension, $license){
        $this->initFilesystem();
        $repository = aam_Core_API::getBlogOption('aam_extensions', array(), 1);

        if ($this->retrieve($license)){
            $repository[$extension] = (object) array(
                'status' => self::STATUS_INSTALLED,
                'license' => $license,
                //ugly way but quick
                'basedir' => $this->_basedir . '/' . str_replace(' ', '_', $extension)
            );
            aam_Core_API::updateBlogOption('aam_extensions', $repository, 1);
            $response = true;
        } else {
            $response = false;
        }

        return $response;
    }

    /**
     * Remove Extension from the repository
     *
     * @param string $extension
     * @param string $license
     *
     * @return boolean
     *
     * @access public
     */
    public function remove($extension, $license){
        global $wp_filesystem;

        $this->initFilesystem();
        $repository = aam_Core_API::getBlogOption('aam_extensions', array(), 1);
        $response = false;

        if (isset($repository[$extension])){
            $basedir = $repository[$extension]->basedir;
            if ($wp_filesystem->rmdir($basedir, true)){
                $response = true;
                unset($repository[$extension]);
                aam_Core_API::updateBlogOption('aam_extensions', $repository, 1);
            }
        }

        return $response;
    }

    /**
     * Initialize WordPress filesystem
     *
     * @return void
     *
     * @access protected
     */
    protected function initFilesystem(){
         require_once ABSPATH . 'wp-admin/includes/file.php';

        //initialize Filesystem
        WP_Filesystem();
    }

    /**
     * Retrieve extension based on license key
     *
     * @global WP_Filesystem $wp_filesystem
     * @param string $license
     *
     * @return boolean
     *
     * @access protected
     */
    protected function retrieve($license) {
        global $wp_filesystem;

        $url = WPAAM_REST_API . '?method=extension&license=' . $license;
        $res = wp_remote_request($url, array('timeout' => 10));
        $response = false;
        if (!is_wp_error($res)) {
            //write zip archive to the filesystem first
            $zip = AAM_TEMP_DIR . '/' . uniqid();
            $content = base64_decode($res['body']);
            if ($content && $wp_filesystem->put_contents($zip, $content)) {
                $response = $this->insert($zip);
                $wp_filesystem->delete($zip);
            }
        }

        return $response;
    }

    /**
     *
     * @param type $zip
     * @return boolean
     */
    protected function insert($zip) {
        $response = true;
        if (is_wp_error(unzip_file($zip, $this->_basedir))) {
            aam_Core_Console::write('Failed to insert extension');
            $response = false;
        }

        return $response;
    }

    /**
     * Bootstrap the Extension
     *
     * In case of any errors, the output can be found in console
     *
     * @param string $extension
     *
     * @return void
     *
     * @access protected
     */
    protected function bootstrapExtension($extension) {
        $bootstrap = $this->_basedir . "/{$extension}/index.php";
        if (file_exists($bootstrap) && !isset($this->_cache[$extension])) {
            $this->_cache[$extension] = require_once($bootstrap);
        }
    }

    /**
     * Set Parent class
     *
     * @param aam $parent
     *
     * @return void
     *
     * @access public
     */
    public function setParent($parent){
        $this->_parent = $parent;
    }

    /**
     * Get Parent class
     *
     * @return aam
     *
     * @access public
     */
    public function getParent(){
        return $this->_parent;
    }

}