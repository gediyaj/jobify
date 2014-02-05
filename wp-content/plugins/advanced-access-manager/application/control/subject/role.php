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
class aam_Control_Subject_Role extends aam_Control_Subject {

    /**
     * Subject UID: ROLE
     */
    const UID = 'role';

    /**
     * Retrieve Role based on ID
     *
     * @return WP_Role|null
     *
     * @access protected
     */
    protected function retrieveSubject() {
        $roles = new WP_Roles;
        $role = $roles->get_role($this->getId());

        if (is_null($role)) {
            aam_Core_Console::write('Role ' . $this->getId() . ' does not exist');
        } elseif (isset($role->capabilities)){
            //add role capability as role id, weird WordPress behavior
            //example is administrator capability
            $role->capabilities[$this->getId()] = true;
        }

        return $role;
    }

    /**
     *
     * @param type $delete_users
     * @return boolean
     */
    public function delete($delete_users = false) {
        $role = new WP_Roles;

        if ($this->getId() !== 'administrator') {
            if ($delete_users) {
                if (current_user_can('delete_users')) {
                    //delete users first
                    $users = new WP_User_Query(array(
                        'number' => '',
                        'blog_id' => get_current_blog_id(),
                        'role' => aam_Core_Request::post('role')
                    ));
                    foreach ($users->get_results() as $user) {
                        //user can not delete himself
                        if ($user->data->ID !== get_current_user_id()) {
                            wp_delete_user($user->data->ID);
                        }
                    }
                    $role->remove_role($this->getId());
                    $status = true;
                } else {
                    $status = false;
                }
            } else {
                $role->remove_role($this->getId());
                $status = true;
            }
        } else {
            $status = false;
        }

        return $status;
    }

    /**
     *
     * @param type $name
     * @return boolean
     */
    public function update($name) {
        $role = new WP_Roles;
        if ($name) {
            $role->roles[$this->getId()]['name'] = $name;
            $status = aam_Core_API::updateBlogOption($role->role_key, $role->roles);
        } else {
            $status = false;
        }

        return $status;
    }

    /**
     * Remove Capability
     *
     * @param string  $capability
     *
     * @return boolean
     *
     * @access public
     */
    public function removeCapability($capability) {
        return $this->getSubject()->remove_cap($capability);
    }

    /**
     * Check if Subject has capability
     *
     * Keep compatible with WordPress core
     *
     * @param string $capability
     *
     * @return boolean
     *
     * @access public
     */
    public function addCapability($capability) {
        return $this->getSubject()->add_cap($capability, 1);
    }

    /**
     *
     * @return type
     */
    public function getCapabilities() {
        return $this->getSubject()->capabilities;
    }

    /**
     *
     * @param type $value
     * @param type $object
     * @param type $object_id
     * @return type
     */
    public function updateOption($value, $object, $object_id = 0) {
        return aam_Core_API::updateBlogOption(
                        $this->getOptionName($object, $object_id), $value
        );
    }

    /**
     *
     * @param type $object
     * @param type $object_id
     * @param type $default
     * @return type
     */
    public function readOption($object, $object_id = 0, $default = null) {
        return aam_Core_API::getBlogOption(
                        $this->getOptionName($object, $object_id), $default
        );
    }

    /**
     *
     * @param type $object
     * @param type $object_id
     * @return type
     */
    public function deleteOption($object, $object_id = 0) {
        return aam_Core_API::deleteBlogOption(
                        $this->getOptionName($object, $object_id)
        );
    }

    /**
     *
     * @param type $object
     * @param type $object_id
     * @return string
     */
    protected function getOptionName($object, $object_id) {
        $name = "aam_{$object}" . ($object_id ? "_{$object_id}_" : '_');
        $name .= self::UID . '_' . $this->getId();

        return $name;
    }

    /**
     *
     * @return type
     */
    public function getUID() {
        return self::UID;
    }

    /**
     * Get Role Cache
     *
     * AAM does not store individual Role cache that is why this function returns
     * always empty array
     *
     * @return array
     *
     * @access public
     */
    public function readCache(){
        return array();
    }

    /**
     * Update Role Cache
     *
     * This function does nothing because AAM does not store Role's cache
     *
     * @return boolean
     *
     * @access public
     */
    public function updateCache(){
        return true;
    }

    /**
     * Clear Role Cache
     *
     * This function does nothing because AAM does not store Role's cache
     *
     * @return boolean
     *
     * @access public
     */
    public function clearCache(){
        return true;
    }

}