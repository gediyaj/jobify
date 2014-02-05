<?php
/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

$dirname = basename(dirname(__FILE__));
define('AAM_MULTISITE_BASE_URL', AAM_BASE_URL . 'extension/' . $dirname);

require_once dirname(__FILE__) . '/extension.php';

return new AAM_Extension_Multisite($this->getParent());