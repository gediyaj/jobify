<?php
/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

/**
 * Ajax Call router
 *
 * Based on sub_action prepare and runs proper model
 *
 * @package AAM
 * @author Vasyl Martyniuk <support@wpaam.com>
 * @copyright Copyright C 2013 Vasyl Martyniuk
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
class aam_View_Ajax extends aam_View_Abstract{

    /**
     * Process the ajax call
     *
     * @return string
     *
     * @access public
     */
    public function run() {
        switch (aam_Core_Request::request('sub_action')) {
            case 'role_list':
                $response = $this->retrieveRoleList();
                break;

            case 'user_list':
                $response = $this->retrieveUserList();
                break;

            case 'add_role':
                $response = $this->addRole();
                break;

            case 'edit_role':
                $response = $this->editRole();
                break;

            case 'delete_role':
                $response = $this->deleteRole();
                break;

            case 'block_user':
                $response = $this->blockUser();
                break;

            case 'delete_user':
                $response = $this->deleteUser();
                break;

            case 'load_metaboxes':
                $response = $this->loadMetaboxes();
                break;

            case 'init_link':
                $response = $this->initLink();
                break;

            case 'load_capabilities':
                $response = $this->loadCapabilities();
                break;

            case 'role_capabilities':
                $response = $this->getRoleCapabilities();
                break;

            case 'add_capability':
                $response = $this->addCapability();
                break;

            case 'delete_capability':
                $response = $this->deleteCapability();
                break;
            
             case 'restore_capability':
                $response = $this->restoreCapability();
                break;

            case 'post_type_list':
                $response = $this->getPostTypeList();
                break;

            case 'post_list':
                $response = $this->getPostList();
                break;

            case 'post_tree':
                $response = $this->getPostTree();
                break;

            case 'post_breadcrumb':
                $response = $this->generatePostBreadcrumb();
                break;

            case 'save_access':
                $response = $this->saveAccess();
                break;

            case 'get_access':
                $response = $this->getAccess();
                break;

            case 'clear_access':
                $response = $this->clearAccess();
                break;

            case 'event_list':
                $response = $this->getEventList();
                break;

            case 'save':
                $response = $this->save();
                break;

            case 'check_roleback':
                $response = $this->checkRoleback();
                break;

            case 'roleback':
                $response = $this->roleback();
                break;

            case 'install_extension':
                $response = $this->installExtension();
                break;

            case 'remove_extension':
                $response = $this->removeExtension();
                break;

            //TODO - Remove in 07/15/2014
            case 'migrate':
                $response = $this->migrate();
                break;

            default:
                $response = apply_filters(
                        'aam_ajax_call', -1, $this->getSubject()
                );
                break;
        }

        return $response;
    }

    /**
     * Retrieve Available for Editing Role List
     *
     * @return string
     *
     * @access protected
     */
    protected function retrieveRoleList() {
        $model = new aam_View_Role;

        return $model->retrieveList();
    }

    /**
     * Retrieve Available User List
     *
     * @return string
     *
     * @access protected
     */
    protected function retrieveUserList() {
        $model = new aam_View_User;

        return $model->retrieveList();
    }

    /**
     * Add New Role
     *
     * @return string
     *
     * @access protected
     */
    protected function addRole() {
        $model = new aam_View_Role;

        return $model->add();
    }

    /**
     * Edit Existing Role
     *
     * @return string
     *
     * @access protected
     */
    protected function editRole() {
        $model = new aam_View_Role;

        return $model->edit();
    }

    /**
     * Delete Existing Role
     *
     * @return string
     *
     * @access protected
     */
    protected function deleteRole() {
        $model = new aam_View_Role;

        return $model->delete();
    }

    /**
     * Block Selected User
     *
     * @return string
     *
     * @access protected
     */
    protected function blockUser() {
        $model = new aam_View_User;

        return $model->block();
    }

    /**
     * Delete Selected User
     *
     * @return string
     *
     * @access protected
     */
    protected function deleteUser() {
        $model = new aam_View_User;

        return $model->delete();
    }

    /**
     * Load List of Metaboxes
     *
     * @return string
     *
     * @access protected
     */
    protected function loadMetaboxes() {
        $model = new aam_View_Metabox;

        return $model->retrieveList();
    }

    /**
     * Initialize list of metaboxes from individual link
     *
     * @return string
     *
     * @access protected
     */
    protected function initLink() {
        $model = new aam_View_Metabox;

        return $model->initLink();
    }

    /**
     * Load list of capabilities
     *
     * @return string
     *
     * @access protected
     */
    protected function loadCapabilities() {
        $model = new aam_View_Capability;

        return $model->retrieveList();
    }

    /**
     * Get list of Capabilities by selected Role
     *
     * @return string
     *
     * @access protected
     */
    protected function getRoleCapabilities() {
        $model = new aam_View_Capability;

        return $model->retrieveRoleCapabilities();
    }

    /**
     * Add New Capability
     *
     * @return string
     *
     * @access protected
     */
    protected function addCapability() {
        $model = new aam_View_Capability;

        return $model->addCapability();
    }

    /**
     * Delete Capability
     *
     * @return string
     *
     * @access protected
     */
    protected function deleteCapability() {
        $model = new aam_View_Capability;

        return $model->deleteCapability();
    }
    
    /**
     * Restore Capabilities
     *
     * @return string
     *
     * @access protected
     */
    protected function restoreCapability() {
        $model = new aam_View_Capability;

        return $model->restoreCapability();
    }

    /**
     * Get the list of Post Types
     *
     * This is used for Post & Term Access Feature
     *
     * @return string
     *
     * @access protected
     */
    protected function getPostTypeList() {
        $model = new aam_View_Post;

        return $model->retrievePostTypeList();
    }

    /**
     * Get the List of Posts
     *
     * @return string
     *
     * @access protected
     */
    protected function getPostList() {
        $model = new aam_View_Post;

        return $model->retrievePostList();
    }

    /**
     * Get Post Tree
     *
     * @return string
     *
     * @access protected
     */
    protected function getPostTree() {
        $model = new aam_View_Post;

        return $model->getPostTree();
    }

    /**
     * Save Access settings for Post or Term
     *
     * @return string
     *
     * @access protected
     */
    protected function saveAccess() {
        $model = new aam_View_Post();

        return $model->saveAccess();
    }

    /**
     * Get Access settings for Post or Term
     *
     * @return string
     *
     * @access protected
     */
    protected function getAccess() {
        $model = new aam_View_Post();

        return $model->getAccess();
    }

    /**
     * Restore default access level for Post or Term
     *
     * @return string
     *
     * @access protected
     */
    protected function clearAccess() {
        $model = new aam_View_Post();

        return $model->clearAccess();
    }

    /**
     * Prepare and generate the post breadcrumb
     *
     * @return string
     *
     * @access protected
     */
    protected function generatePostBreadcrumb() {
        $model = new aam_View_Post;

        return $model->getPostBreadcrumb();
    }

    /**
     * Get Event List
     *
     * @return string
     *
     * @access protected
     */
    protected function getEventList() {
        $model = new aam_View_Event;

        return $model->retrieveEventList();
    }

    /**
     * Save AAM settings
     *
     * @return string
     *
     * @access protected
     */
    protected function save() {
        $model = new aam_View_Manager;
        
        return $model->save();
    }

    /**
     * Roleback the changes
     *
     * @return string
     *
     * @access protected
     */
    protected function roleback() {
        $model = new aam_View_Manager;

        return $model->roleback();
    }

    /**
     * Check whether roleback action can be performed
     *
     * @return string
     *
     * @access protected
     */
    protected function checkRoleback() {
        $model = new aam_View_Manager;

        return $model->checkRoleback();
    }

    /**
     * Install extension
     *
     * @return string
     *
     * @access protected
     */
    protected function installExtension() {
        $model = new aam_View_Extension();

        return $model->install();
    }

    /**
     * Remove extension
     *
     * @return string
     *
     * @access protected
     */
    protected function removeExtension() {
        $model = new aam_View_Extension();

        return $model->remove();
    }

    /**
     * Migrate
     *
     * @return string
     *
     * @access protected
     * @todo Remove in 07/15/2014
     */
    protected function migrate() {
        $model = new aam_Core_Migrate();

        return $model->run();
    }

}