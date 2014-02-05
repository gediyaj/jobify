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
class AAM_Extension_MyFeature {

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

        if (is_admin()) {
            add_action('admin_print_scripts', array($this, 'printScripts'));
            add_action('admin_print_styles', array($this, 'printStyles'));
            add_filter('aam_ui_features', array($this, 'feature'), 10);
        }
    }

    public function feature($features) {
        //add feature
        $features['my_feature'] = array(
            'id' => 'my_feature',
            'position' => 35,
            'title' => __('My Feature', 'aam'),
            'anonimus' => true,
            'content' => array($this, 'content'),
            'help' => __('My customly developed feature', 'aam')
        );

        return $features;
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
                    'aam-my-feature-admin', 
                    AAM_MY_FEATURE_BASE_URL . '/my_feature.js', 
                    array('aam-admin')
            );
        }
    }

    /**
     * 
     */
    public function printStyles() {
        if ($this->getParent()->isAAMScreen()) {
            wp_enqueue_style(
                    'aam-my-feature-admin', 
                    AAM_MY_FEATURE_BASE_URL . '/my_feature.css'
            );
        }
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
