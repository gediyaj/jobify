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
class aam_View_Capability extends aam_View_Abstract {

    /**
     *
     * @var type
     */
    private $_groups = array(
        'system' => array(
            'level_0', 'level_1', 'level_2', 'level_3', 'level_4', 'level_5',
            'level_6', 'level_7', 'level_8', 'level_9', 'level_10'
        ),
        'post' => array(
            'delete_others_pages', 'delete_others_posts', 'delete_pages',
            'delete_posts', 'delete_private_pages', 'delete_private_posts',
            'delete_published_pages', 'delete_published_posts', 'edit_others_pages',
            'edit_others_posts', 'edit_pages', 'edit_private_posts',
            'edit_private_pages', 'edit_posts', 'edit_published_pages',
            'edit_published_posts', 'publish_pages', 'publish_posts', 'read',
            'read_private_pages', 'read_private_posts', 'edit_permalink'
        ),
        'backend' => array(
            'aam_manage', 'activate_plugins', 'add_users', 'create_users',
            'delete_users', 'delete_themes', 'edit_dashboard', 'edit_files',
            'edit_plugins', 'edit_theme_options', 'edit_themes', 'edit_users',
            'export', 'import', 'install_plugins', 'install_themes', 'list_users',
            'manage_options', 'manage_links', 'manage_categories', 'promote_users',
            'unfiltered_html', 'unfiltered_upload', 'update_themes', 'update_plugins',
            'update_core', 'upload_files', 'delete_plugins', 'remove_users',
            'switch_themes'
        )
    );

    /**
     *
     * @return type
     */
    public function retrieveList() {
        $response = array(
            'aaData' => array(),
            'aaDefault' => 1 //Default set of Capabilities indicator
        );

        $subject = $this->getSubject();
        $roles = new WP_Roles();
        if ($subject->getUID() === aam_Control_Subject_Role::UID) {
            //prepare list of all capabilities
            $caps = array();
            foreach ($roles->role_objects as $role) {
                $caps = array_merge($caps, $role->capabilities);
            }
            //init all caps
            foreach ($caps as $capability => $grant) {
                $response['aaData'][] = array(
                    $capability,
                    ($subject->hasCapability($capability) ? 1 : 0),
                    $this->getGroup($capability),
                    $this->getHumanText($capability),
                    ''
                );
            }
        } else {
            $role_list = $subject->roles;
            $role = $roles->get_role(array_shift($role_list));
            foreach ($role->capabilities as $capability => $grant) {
                $response['aaData'][] = array(
                    $capability,
                    ($subject->hasCapability($capability) ? 1 : 0),
                    $this->getGroup($capability),
                    $this->getHumanText($capability),
                    ''
                );
                $response['aaDefault'] = ($subject->isDefaultCapSet() ? 1 : 0);
            }
        }

        return json_encode($response);
    }

    /**
     *
     * @return type
     */
    public function getGroupList(){
        return apply_filters('aam_capability_groups', array(
            __('System', 'aam'),
            __('Post & Page', 'aam'),
            __('Backend Interface', 'aam'),
            __('Miscellaneous', 'aam')
        ));
    }

    /**
     *
     * @return type
     */
    public function retrieveRoleCapabilities() {
        return json_encode(array(
            'status' => 'success',
            'capabilities' => $this->getSubject()->getCapabilities()
        ));
    }

    /**
     *
     * @return type
     */
    public function addCapability() {
        $roles = new WP_Roles();
        $capability = trim(aam_Core_Request::post('capability'));

        if ($capability) {
            $normalized = str_replace(' ', '_', strtolower($capability));
            //add the capability to administrator's role as default behavior
            $roles->add_cap('administrator', $normalized);
            $response = array('status' => 'success', 'capability' => $normalized);
        } else {
            $response = array('status' => 'failure');
        }

        return json_encode($response);
    }

    /**
     *
     * @return type
     */
    public function deleteCapability() {
        $roles = new WP_Roles();
        $capability = trim(aam_Core_Request::post('capability'));

        if ($capability) {
            foreach ($roles->role_objects as $role) {
                $role->remove_cap($capability);
            }
            $response = array('status' => 'success');
        } else {
            $response = array('status' => 'failure');
        }

        return json_encode($response);
    }
    
    /**
     * Restore default user capabilities
     * 
     * @return string
     * 
     * @access public
     */
    public function restoreCapability(){
        $subject = $this->getSubject();
        $response = array('status' => 'failure');
        if (($subject->getUID() == aam_Control_Subject_User::UID) 
                                                    && $subject->resetCapability()){
            $response['status'] = 'success';
        }
        
        return json_encode($response);
    }

    /**
     *
     * @param type $text
     * @return type
     */
    protected function getHumanText($text) {
        $parts = preg_split('/_/', $text);
        if (is_array($parts)) {
            foreach ($parts as &$part) {
                $part = ucfirst($part);
            }
        }

        return implode(' ', $parts);
    }

    /**
     *
     * @param type $capability
     * @return type
     */
    protected function getGroup($capability) {
        if (in_array($capability, $this->_groups['system'])) {
            $response = __('System', 'aam');
        } elseif (in_array($capability, $this->_groups['post'])) {
            $response = __('Post & Page', 'aam');
        } elseif (in_array($capability, $this->_groups['backend'])) {
            $response = __('Backend Interface', 'aam');
        } else {
            $response = __('Miscellaneous', 'aam');
        }

        return apply_filters('aam_capability_group', $response, $capability);
    }

    /**
     *
     * @return type
     */
    public function content() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/capability.phtml');
    }

}