<?php
/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

/**
 * Abstract Object Class
 *
 * Object is any part of the WordPress that is controlled by AAM. The example of
 * object is Post, Page, Metabox, Widget etc.
 *
 * @package AAM
 * @author Vasyl Martyniuk <support@wpaam.com>
 * @copyright Copyright C 2013 Vasyl Martyniuk
 * @license GNU General Public License {@link http://www.gnu.org/licenses/}
 */
abstract class aam_Control_Object {

    /**
     * Subject
     *
     * @var aam_Control_Subject
     *
     * @access private
     */
    private $_subject = null;

    /**
     * Constructor
     *
     * @param aam_Control_Subject $subject
     * @param int $object_id
     *
     * @return void
     *
     * @access public
     */
    public function __construct(aam_Control_Subject $subject, $object_id) {
        $this->setSubject($subject);
        $this->init($object_id);
    }

    /**
     * Sleep method
     *
     * Used for caching mechanism
     *
     * @return array
     *
     * @access public
     */
    public function __sleep(){
        return array('_option');
    }

     /**
     * Indicate either object can be cached on not
     *
     * @return boolean
     *
     * @access public
     */
    abstract public function cacheObject();

    /**
     * Initialize object
     *
     * @param string|int $object_id
     *
     * @return void
     *
     * @access public
     */
    public function init($object_id) {
        //if (empty($this->_option)) {
            $this->setOption(
                    $this->getSubject()->readOption($this->getUID(), $object_id)
            );
        //}
    }

    /**
     * Set current subject
     *
     * Either it is User or Role
     *
     * @param aam_Control_Subject $subject
     *
     * @return void
     *
     * @access public
     */
    public function setSubject(aam_Control_Subject $subject) {
        $this->_subject = $subject;
    }

    /**
     * Get Subject
     *
     * @return aam_Control_Subject
     *
     * @access public
     */
    public function getSubject() {
        return $this->_subject;
    }

    /**
     * Get current object UID
     *
     * @return string
     *
     * @access public
     */
    abstract public function getUID();

    /**
     *
     */
    abstract public function setOption($option);

    /**
     *
     */
    abstract public function getOption();

    /**
     *
     */
    abstract public function save($params = array());

}