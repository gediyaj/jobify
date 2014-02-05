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
class aam_View_Role extends aam_View_Abstract {

    /**
     *
     * @return type
     */
    public function content() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/role.phtml');
    }

    /**
     *
     * @return type
     */
    public function retrieveList() {
        //retrieve list of users
        $count = count_users();
        $user_count = $count['avail_roles'];

        //filter by name
        $search = strtolower(trim(aam_Core_Request::request('sSearch')));
        $filtered = array();
        $roles = get_editable_roles();
        foreach ($roles as $id => $role) {
            if (!$search || preg_match('/^' . $search . '/i', $role['name'])) {
                $filtered[$id] = $role;
            }
        }

        $response = array(
            'iTotalRecords' => count($roles),
            'iTotalDisplayRecords' => count($filtered),
            'sEcho' => aam_Core_Request::request('sEcho'),
            'aaData' => array(),
        );
        foreach ($filtered as $role => $data) {
            $users = (isset($user_count[$role]) ? $user_count[$role] : 0);
            $response['aaData'][] = array(
                $role,
                $users,
                $data['name'],
                ''
            );
        }

        return json_encode($response);
    }

    /**
     *
     * @return type
     */
    public function add() {
        $name = trim(aam_Core_Request::post('name'));
        $roles = new WP_Roles;
        $role_id = 'aamrole_' . uniqid();

        if ($roles->add_role($role_id, $name)) {
            $response = array(
                'status' => 'success',
                'role' => $role_id
            );
        } else {
            $response = array('status' => 'failure');
        }

        return json_encode($response);
    }

    /**
     *
     * @return type
     */
    public function edit() {
        $result = $this->getSubject()->update(trim(aam_Core_Request::post('name')));
        return json_encode(
                array('status' => ($result ? 'success' : 'failure'))
        );
    }

    /**
     *
     * @return type
     */
    public function delete() {
        if ($this->getSubject()->delete(aam_Core_Request::post('delete_users'))) {
            $status = 'success';
        } else {
            $status = 'failure';
        }

        return json_encode(array('status' => $status));
    }

}