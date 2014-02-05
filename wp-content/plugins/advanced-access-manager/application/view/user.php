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
class aam_View_User extends aam_View_Abstract {

    /**
     *
     * @return type
     */
    public function content() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/user.phtml');
    }

    /**
     *
     * @return type
     */
    public function retrieveList() {
        //get total number of users
        $total = count_users();
        $result = $this->query();
        $response = array(
            'iTotalRecords' => $total['total_users'],
            'iTotalDisplayRecords' => $result->get_total(),
            'sEcho' => aam_Core_Request::request('sEcho'),
            'aaData' => array(),
        );
        foreach ($result->get_results() as $user) {
            $response['aaData'][] = array(
                $user->ID,
                $user->user_login,
                ($user->display_name ? $user->display_name : $user->user_nicename),
                '',
                $user->user_status
            );
        }

        return json_encode($response);
    }

    /**
     *
     * @return \WP_User_Query
     */
    public function query() {
        if ($search = trim(aam_Core_Request::request('sSearch'))) {
            $search = "{$search}*";
        }
        $args = array(
            'number' => '',
            'blog_id' => get_current_blog_id(),
            'role' => aam_Core_Request::request('role'),
            'fields' => 'all',
            'number' => aam_Core_Request::request('iDisplayLength'),
            'offset' => aam_Core_Request::request('iDisplayStart'),
            'search' => $search,
            'search_columns' => array('user_login', 'user_email', 'display_name'),
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        );

        return new WP_User_Query($args);
    }

    /**
     *
     * @return type
     */
    public function block() {
        if ($this->getSubject()->block()){
            $response = array(
                'status' => 'success',
                'user_status' => $this->getSubject()->user_status
            );
        } else{
            $response = array('status' => 'failure');
        }

        return json_encode($response);
    }

    /**
     *
     * @return type
     */
    public function delete() {
        return json_encode(
                array(
                    'status' => $this->getSubject()->delete() ? 'success' : 'failure'
                )
        );
    }

}