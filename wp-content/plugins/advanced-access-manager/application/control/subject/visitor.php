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
class aam_Control_Subject_Visitor extends aam_Control_Subject
{

    /**
     * Subject UID: VISITOR
     */
    const UID = 'visitor';

    /**
     * Constructor
     *
     * @param string|int $id
     *
     * @return void
     *
     * @access public
     */
    public function __construct($id) {
       //run parent constructor
       parent::__construct('');
    }

    /**
     * Retrieve Visitor Subject
     *
     * @return stdClass
     *
     * @access protected
     */
    protected function retrieveSubject(){
        return new stdClass();
    }

    /**
     *
     * @return type
     */
    public function getCapabilities(){
        return array();
    }

    /**
     *
     * @param type $value
     * @param type $object
     * @param type $object_id
     * @return type
     */
    public function updateOption($value, $object, $object_id = 0){
        return aam_Core_API::updateBlogOption(
                $this->getOptionName($object, $object_id), $value
        );
    }

    /**
     *
     * @param type $object
     * @param type $object_id
     * @return type
     */
    public function readOption($object, $object_id = 0){
        return aam_Core_API::getBlogOption(
                $this->getOptionName($object, $object_id)
        );
    }

    /**
     *
     * @param type $object
     * @param type $object_id
     * @return type
     */
    public function deleteOption($object, $object_id = 0){
        return aam_Core_API::deleteBlogOption(
                $this->getOptionName($object, $object_id)
        );
    }

    /**
     *
     * @param type $object
     * @param type $object_id
     * @return type
     */
    protected function getOptionName($object, $object_id){
        return 'aam_' . self::UID . "_{$object}" . ($object_id ? "_{$object_id}" : '');
    }

    /**
     *
     * @return type
     */
    public function getUID(){
        return self::UID;
    }

    /**
     * Get Visitor's Cache
     *
     * Read Visitor's option aam_visitor_cache and return it
     *
     * @return array
     *
     * @access public
     */
    public function readCache(){
        $cache = aam_Core_API::getBlogOption('aam_visitor_cache', array());
        return (is_array($cache) ? $cache : array());
    }

    /**
     * Insert or Update Visitor's Cache
     *
     * @return boolean
     *
     * @access public
     */
    public function updateCache(){
        return aam_Core_API::updateBlogOption(
            'aam_visitor_cache', $this->getObjects()
        );
    }

    /**
     * Delete Visitor's Cache
     *
     * @return boolean
     *
     * @access public
     */
    public function clearCache(){
        return aam_Core_API::deleteBlogOption('aam_visitor_cache');
    }

}