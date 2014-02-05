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
class aam_View_Menu extends aam_View_Abstract {

    /**
     *
     * @return type
     */
    public function content() {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/menu.phtml');
    }

    /**
     *
     * @global type $menu
     * @global type $submenu
     * @return type
     */
    public function getMenu() {
        global $menu;
        
        $response = array();
        
        //let's create menu list with submenus
        foreach ($menu as $menu_item) {
            if (!preg_match('/^separator/', $menu_item[2])) {
                $submenu = $this->getSubmenu($menu_item[2]);
                $remove = !$this->getSubject()->hasCapability($menu_item[1]);
                if (($remove === false) || (count($submenu) !== 0)) {
                    $item = array(
                        'name' => $this->removeHTML($menu_item[0]),
                        'id' => $menu_item[2],
                        'submenu' => $submenu
                    );
                    $response[] = $item;
                }
            }
        }
        
        return $response;
    }
    
    /**
     * Prepare filtered submenu
     * 
     * @global array $submenu
     * @param string $menu
     * 
     * @return array
     * 
     * @access public
     */
    public function getSubmenu($menu) {
        global $submenu;

        $filtered_submenu = array();
        if (isset($submenu[$menu])) {
            foreach ($submenu[$menu] as $submenu_item) {
                if ($this->getSubject()->hasCapability($submenu_item[1]) !== false) {
                    //prepare title
                    $submenu_title = $this->removeHTML($submenu_item[0]);
                    if (strlen($submenu_title) > 18) {
                        $submenu_short = substr($submenu_title, 0, 15) . '..';
                    } else {
                        $submenu_short = $submenu_title;
                    }

                    $filtered_submenu[] = array(
                        'name' => $submenu_title,
                        'short' => $submenu_short,
                        'id' => $submenu_item[2]
                    );
                }
            }
        }
        
        return $filtered_submenu;
    }
    
    /**
     * Check if the entire branch is restricted
     * 
     * @param array $menu
     * 
     * @return boolean
     * 
     * @access public
     */
    public function hasRestrictedAll($menu){
        $menuControl = $this->getSubject()->getObject(aam_Control_Object_Menu::UID);
        $response = $menuControl->has($menu['id']);
        
        foreach($menu['submenu'] as $submenu){
            if ($menuControl->has($submenu['id']) === false){
                $response = false;
                break;
            }
        }
        
        return $response;
    }

    /**
     *
     * @param type $text
     * @return type
     */
    public function removeHTML($text) {
        // Return clean content
        return strip_tags($text);
    }

}