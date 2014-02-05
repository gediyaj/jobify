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
class aam_Control_Object_Capability extends aam_Control_Object {

    /**
     *
     */
    const UID = 'capability';

    /**
     *
     * @var type
     */
    private $_option = array();

    /**
     *
     * @param type $capabilities
     */
    public function save($capabilities = null) {
        if (is_array($capabilities)) {
            foreach ($capabilities as $capability => $grant) {
                if (intval($grant)) {
                    $this->getSubject()->addCapability($capability);
                } else {
                    $this->getSubject()->removeCapability($capability);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function cacheObject(){
        return false;
    }

    /**
     *
     * @param type $object_id
     */
    public function init($object_id) {
        if (empty($this->_option)) {
            $this->setOption($this->getSubject()->getCapabilities());
        }
    }

    /**
     *
     * @return type
     */
    public function backup() {
        return $this->getSubject()->getCapabilities();
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
     * @param type $capability
     * @return type
     */
    public function has($capability) {
        return $this->getSubject()->hasCapability($capability);
    }

}