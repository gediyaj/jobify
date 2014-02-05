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
class aam_Control_Object_Menu extends aam_Control_Object {

    /**
     * Object Unique ID
     */
    const UID = 'menu';

    /**
     * List of options
     *
     * @var array
     *
     * @access private
     */
    private $_option = array();

    /**
     * Filter Menu List
     *
     * @global array $menu
     * @global array $submenu
     *
     * @return void
     *
     * @access public
     */
    public function filter() {
        global $menu;

        //filter menu & submenu first
        $capability = uniqid('aam_'); //random capability means NO access
        //let's go and iterate menu & submenu
        foreach ($menu as $id => $item) {
            if ($this->has($item[2])) {
                $menu[$id][1] = $capability;
                $denied = true;
            } else {
                $denied = false;
            }
            //filter submenu
            $submenu = $this->filterSubmenu($item[2], $denied);
            //a trick to whether remove the Root Menu or replace link with the first
            //available submenu
            if ($denied && $submenu){
                $menu[$id][2] = $submenu[1];
                $menu[$id][1] = $submenu[0];
            } elseif ($denied){ //ok, no available submenus, remove it completely
                unset($menu[$id]);
            }
        }
    }

    /**
     * Filter submenu
     *
     * @global array $submenu
     *
     * @param array  $menu
     * @param boolean $denied
     *
     * @return string|null
     *
     * @access public
     */
    public function filterSubmenu($menu, $denied) {
        global $submenu;

        //go to submenu
        $available = null;
        if (isset($submenu[$menu])) {
            foreach ($submenu[$menu] as $sid => $sub_item) {
                if ($this->has($sub_item[2])) {
                    //weird WordPress behavior, it gets the first submenu link
                    //$submenu[$menu][$sid][1] = $capability;
                    unset($submenu[$menu][$sid]);
                } elseif (is_null($available)){ //find first available submenu
                    $available = array($sub_item[1], $sub_item[2]);
                }
            }
        }

        //replace submenu with available new if found
        if ($denied && !is_null($available) && ($available[1] != $menu) ){
            $submenu[$available[1]] = $submenu[$menu];
            unset($submenu[$menu]);
        }

        return $available;
    }

    /**
     *
     * @param type $menu
     */
    public function save($menu = null) {
        if (is_array($menu)) {
            $this->getSubject()->updateOption($menu, self::UID);
        }
    }

    /**
     * @inheritdoc
     */
    public function cacheObject(){
        return true;
    }

    /**
     *
     * @return type
     */
    public function backup() {
        return $this->getSubject()->readOption(self::UID, '', array());
    }

    /**
     *
     * @return type
     */
    public function getUID() {
        return self::UID;
    }

    /**
     *
     * @param type $option
     */
    public function setOption($option) {
        $this->_option = (is_array($option) ? $option : array());
    }

    /**
     *
     * @return type
     */
    public function getOption() {
        return $this->_option;
    }

    /**
     *
     * @param type $menu
     * @return type
     */
    public function has($menu) {
        $response = false;
        //decode URL in case of any special characters like &amp;
        $menu_decoded = htmlspecialchars_decode($menu);
        //check if menu is restricted
        if (isset($this->_option[$menu_decoded])) {
            $response = (intval($this->_option[$menu_decoded]) ? true : false);
        }

        return $response;
    }

}