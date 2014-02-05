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
class aam_View_Event extends aam_View_Abstract {

    /**
     *
     * @return type
     */
    public function retrieveEventList() {
        $response = array('aaData' => array());
        $events = $this->getSubject()->getObject(aam_Control_Object_Event::UID);
        foreach ($events->getOption() as $event) {
            $response['aaData'][] = array(
                $event['event'],
                $event['event_specifier'],
                $event['post_type'],
                $event['action'],
                $event['action_specifier'],
                ''
            );
        }

        return json_encode($response);
    }

    /**
     *
     * @global type $wp_post_statuses
     * @return type
     */
    public function getPostStatuses() {
        global $wp_post_statuses;

        return $wp_post_statuses;
    }

    /**
     *
     * @global type $wp_post_types
     * @return type
     */
    public function getPostTypes() {
        global $wp_post_types;

        return $wp_post_types;
    }

    /**
     *
     * @return type
     */
    public function content() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/event.phtml');
    }

}