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
class aam_View_ConfigPress extends aam_View_Abstract
{

    /**
     *
     * @return type
     */
    public function content()
    {
        return $this->loadTemplate(dirname(__FILE__) . '/tmpl/configpress.phtml');
    }

}