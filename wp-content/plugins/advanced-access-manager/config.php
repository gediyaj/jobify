<?php
/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

//AAM Version for Update purpose
define('AAM_VERSION', '2.1.1');

define('AAM_BASE_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);

//jean.yves.dumaine@gmail.com feedback - thank you
$base_url = WP_PLUGIN_URL . '/' . basename(AAM_BASE_DIR) . '/';
if (force_ssl_admin()){
   $base_url = str_replace('http', 'https', $base_url);
} elseif (!empty($_SERVER['HTTPS'])){
    $base_url = str_replace('http', 'https', $base_url);
} elseif (isset($_SERVER['REQUEST_SCHEME']) 
            && ($_SERVER['REQUEST_SCHEME'] == 'https')){
    $base_url = str_replace('http', 'https', $base_url);
}

define('AAM_BASE_URL', $base_url);

define('AAM_TEMPLATE_DIR', AAM_BASE_DIR . 'view/html/');
define('AAM_LIBRARY_DIR', AAM_BASE_DIR . 'library/');
define('AAM_TEMP_DIR', WP_CONTENT_DIR . '/aam/');
define('AAM_MEDIA_URL', AAM_BASE_URL . 'media/');

define('AAM_APPL_ENV', (getenv('APPL_ENV') ? getenv('APPL_ENV') : 'production'));
//Rest API
if (AAM_APPL_ENV === 'production') {
    define('WPAAM_REST_API', 'http://wpaam.com/rest');
} else {
    define('WPAAM_REST_API', 'http://wpaam.localhost/rest');
}

/**
 * Autoloader for project Advanced Access Manager
 *
 * Try to load a class if prefix is mvb_
 *
 * @param string $class_name
 */
function aam_autoload($class_name) {
    $chunks = explode('_', strtolower($class_name));
    $prefix = array_shift($chunks);

    if ($prefix === 'aam') {
        $base_path = AAM_BASE_DIR . '/application';
        $path = $base_path . '/' . implode('/', $chunks) . '.php';
        if (file_exists($path)) {
            require($path);
        }
    }
}

spl_autoload_register('aam_autoload');

function aam_content_folder() {
    echo "<div class='update-nag'>";
    echo __('<b>wp-content</b> folder is not writable or does not exists. ', 'aam');
    echo '<a href="http://wpaam.com/support#viewtopic.php?f=4&t=23" target="_blank">';
    echo __('Read more.', 'aam') . '</a>';
    echo '</div>';
}

//make sure that we have always content dir
if (!file_exists(AAM_TEMP_DIR)) {
    if (@mkdir(AAM_TEMP_DIR)) {
        //silence the directory
        file_put_contents(AAM_TEMP_DIR . '/index.php', '');
    } else {
        define('AAM_CONTENT_DIR_FAILURE', 1);
        if (is_multisite()) {
            add_action('network_admin_notices', 'aam_content_folder');
        } else {
            add_action('admin_notices', 'aam_content_folder');
        }
    }
}

load_plugin_textdomain('aam', false, basename(AAM_BASE_DIR) . '/lang');

//set migration admin notice. TODO - remove in July 15 2014
function aam_migration_note() {
    if (class_exists('aam_Core_Migrate') && !aam_Core_API::getBlogOption('aam_migrated')) {
        echo "<div class='update-nag'>";
        echo __('Migrate your old AAM settings to the new AAM platform. ', 'aam');
        echo '<a href="#" id="aam_migrate">' . __('Click to Migrate', 'aam') . '</a>';
        echo '</div>';
    }
}

if (is_multisite()) {
    add_action('network_admin_notices', 'aam_migration_note');
} else {
    add_action('admin_notices', 'aam_migration_note');
}