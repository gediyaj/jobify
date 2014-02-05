<?php
/*
Plugin Name: job Apply List
Description: job Apply List
Plugin URI: http://webcontrive.com/jobify/
Author URI: http://webcontrive.com/jobify/
Author: Marchenko Alexandr
License: praks
Version: 1.2
*/
$includes = ABSPATH . PLUGINDIR . '/manage/';
$pluginDir=get_option('siteurl').'/wp-content/plugins/manage/';



global $custom_table_example_db_version;
$custom_table_example_db_version = '1.1'; 

function custom_install()
{
    global $wpdb;
    global $custom_table_example_db_version;

    $table_name = $wpdb->prefix . 'kpp'; // do not forget about tables prefix

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      name tinytext NOT NULL,
      email VARCHAR(100) NOT NULL,
      age int(11) NULL,
      PRIMARY KEY  (id)
    );";

    // we do not execute sql directly
    // we are calling dbDelta which cant migrate database
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // save current database version for later use (on upgrade)
    add_option('custom_table_example_db_version', $custom_table_example_db_version);
}
register_activation_hook(__FILE__, 'custom_install');


function custom_table_example_admin_menu()
{
    add_menu_page(__('manage', 'table_example'), __('Apply Job List', 'table_example'), 'activate_plugins', 'manage', 'table_example_persons_page_handler');
}

add_action('admin_menu', 'custom_table_example_admin_menu');

function table_example_persons_page_handler()
{
	require_once(ABSPATH . 'wp-content/plugins/List Job/view.php');
}


?>