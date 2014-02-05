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
class aam_Core_Console {

    /**
     *
     * @param type $message
     */
    public static function write($message) {
        file_put_contents(
                AAM_TEMP_DIR . '/console.log', $message . "\n", FILE_APPEND
        );
    }

}