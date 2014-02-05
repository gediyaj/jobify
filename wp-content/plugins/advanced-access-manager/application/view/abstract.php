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
abstract class aam_View_Abstract {

    /**
     *
     * @var type
     */
    static private $_subject = null;

    /**
     *
     */
    public function __construct() {
        if (is_null(self::$_subject)) {
            $subject_class = 'aam_Control_Subject_' . ucfirst(
                trim(aam_Core_Request::request('subject'), '')
            );
            if (class_exists($subject_class)){
                $this->setSubject(new $subject_class(
                    aam_Core_Request::request('subject_id')
                ));
            }
        }
    }

    /**
     *
     * @return type
     */
    public function getSubject() {
        return self::$_subject;
    }

    /**
     *
     * @param aam_Control_Subject $subject
     */
    public function setSubject(aam_Control_Subject $subject) {
        self::$_subject = $subject;
    }

    /**
     *
     * @param type $tmpl_path
     * @return type
     */
    public function loadTemplate($tmpl_path) {
        ob_start();
        require_once($tmpl_path);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

}