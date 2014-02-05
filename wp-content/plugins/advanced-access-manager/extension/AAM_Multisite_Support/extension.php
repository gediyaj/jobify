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
class AAM_Extension_Multisite {

    /**
     *
     * @var type 
     */
    private $_parent = null;

    /**
     *
     * @var type 
     */
    private $_subject = null;

    /**
     *
     * @param aam|aam_View_Connector $parent
     */
    public function __construct(aam $parent) {
        $this->setParent($parent);
        if (aam_Core_API::isNetworkPanel()) {
            add_action('admin_print_scripts', array($this, 'printScripts'));
            add_action('admin_print_styles', array($this, 'printStyles'));
            add_action('aam_localization_labels', array($this, 'localizationLabels'));
            add_filter('aam_ui_subjects', array($this, 'addUISubject'), 10, 1);
            add_action('wpmu_new_blog', array($this, 'newBlog'), 10, 6);
        } elseif (is_admin()) {
            add_filter('aam_ajax_call', array($this, 'ajax'), 10, 2);
        }
    }

    /**
     * 
     * @param type $blog_id
     * @param type $user_id
     * @param type $domain
     * @param type $path
     * @param type $site_id
     * @param type $meta
     */
    public function newBlog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
        global $wpdb;
        
        if ($default_id = aam_Core_API::getBlogOption('aam_default_site', 0, 1)){
            $default_option = $wpdb->get_blog_prefix($default_id) . 'user_roles';
            $roles = aam_Core_API::getBlogOption($default_option, null, $default_id);
            if ($roles){
                aam_Core_API::updateBlogOption(
                        $wpdb->get_blog_prefix($blog_id) . 'user_roles', 
                        $roles, $blog_id
                );
            }
        }
    }

    /**
     * 
     * @return type
     */
    protected function getSiteList() {
        //retrieve site list first
        $blog_list = $this->retrieveSiteList();
        
        $response = array(
            'iTotalRecords' => count($blog_list),
            'iTotalDisplayRecords' => count($blog_list),
            'sEcho' => aam_Core_Request::request('sEcho'),
            'aaData' => array(),
        );
        $default = aam_Core_API::getBlogOption('aam_default_site', 0, 1);

        foreach ($blog_list as $site) {
            $blog = get_blog_details($site->blog_id);
            $response['aaData'][] = array(
                $site->blog_id,
                get_admin_url($site->blog_id, 'admin.php'),
                get_admin_url($site->blog_id, 'admin-ajax.php'),
                $blog->blogname,
                '',
                ($site->blog_id == $default ? 1 : 0)
            );
        }

        return json_encode($response);
    }
    
    /**
     * Retieve the list of sites
     * 
     * @return array
     * 
     * @access public
     */
    public function retrieveSiteList(){
        global $wpdb;
        
        return $wpdb->get_results('SELECT blog_id FROM ' . $wpdb->blogs);
    }

    /**
     * 
     * @param type $subjects
     * @return type
     */
    public function addUISubject($subjects) {
        $subjects['multisite'] = array(
            'position' => 1,
            'segment' => 'multisite',
            'label' => __('Sites', 'aam'),
            'title' => __('Site Manager', 'aam'),
            'class' => 'manager-item manager-item-multisite',
            'id' => 'aam_multisite',
            'content' => array($this, 'content')
        );

        return $subjects;
    }

    /**
     * 
     * @return type
     */
    public function content() {
        ob_start();
        require dirname(__FILE__) . '/ui.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Print necessary scripts
     *
     * @return void
     *
     * @access public
     */
    public function printScripts() {
        if ($this->getParent()->isAAMScreen()) {
            wp_enqueue_script(
                    'aam-multisite-admin', AAM_MULTISITE_BASE_URL . '/multisite.js', array('aam-admin')
            );
            $localization = array(
                'nonce' => wp_create_nonce('aam_ajax'),
                'addSiteURI' => admin_url('network/site-new.php'),
                'editSiteURI' => admin_url('network/site-info.php')
            );

            wp_localize_script(
                    'aam-multisite-admin', 'aamMultisiteLocal', $localization
            );
        }
    }

    /**
     * 
     */
    public function printStyles() {
        if ($this->getParent()->isAAMScreen()) {
            wp_enqueue_style(
                    'aam-multisite-admin', AAM_MULTISITE_BASE_URL . '/multisite.css'
            );
        }
    }

    /**
     * 
     * @param type $labels
     * @return type
     */
    public function localizationLabels($labels) {
        $labels['Set Default'] = __('Set Default', 'aam');
        $labels['Unset Default'] = __('Unset Default', 'aam');
        $labels['Set as Default'] = __('Set as Default', 'aam');

        return $labels;
    }

    /**
     * 
     * @param type $default
     * @param aam_Control_Subject $subject
     * @return type
     */
    public function ajax($default, aam_Control_Subject $subject = null) {
        $this->setSubject($subject);

        switch (aam_Core_Request::request('sub_action')) {
            case 'site_list':
                $response = $this->getSiteList();
                break;

            case 'pin_site':
                $response = $this->pinSite();
                break;

            case 'unpin_site':
                $response = $this->unpinSite();
                break;

            default:
                $response = $default;
                break;
        }

        return $response;
    }

    protected function pinSite() {
        return json_encode(array(
            'status' => aam_Core_API::updateBlogOption(
                    'aam_default_site', aam_Core_Request::post('blog'), 1
            ) ? 'success' : 'failure'
        ));
    }

    protected function unpinSite() {
        return json_encode(array(
            'status' => aam_Core_API::deleteBlogOption('aam_default_site', 1) ? 'success' : 'failure'
        ));
    }

    /**
     * 
     * @param aam $parent
     */
    public function setParent(aam $parent) {
        $this->_parent = $parent;
    }

    /**
     *
     * @return aam
     */
    public function getParent() {
        return $this->_parent;
    }

    /**
     * 
     * @param type $subject
     */
    public function setSubject($subject) {
        $this->_subject = $subject;
    }

    /**
     * 
     * @return type
     */
    public function getSubject() {
        return $this->_subject;
    }

}
