<?php
/*
Plugin Name: Soliloquy Lite
Plugin URI: http://soliloquywp.com/
Description: Soliloquy is the best responsive WordPress slider plugin. Period. This is the lite version.
Author: Thomas Griffin
Author URI: http://thomasgriffinmedia.com/
Version: 1.5.2
License: GNU General Public License v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/*
	Copyright 2013	 Thomas Griffin	 (email : thomas@thomasgriffinmedia.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/** Load all of the necessary class files for the plugin */
spl_autoload_register( 'Tgmsp_Lite::autoload' );

/**
 * Init class for Soliloquy Lite.
 *
 * Loads all of the necessary components for the Soliloquy Lite plugin.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy Lite
 * @author	Thomas Griffin
 */
class Tgmsp_Lite {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Holds a copy of the main plugin filepath.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	private static $file = __FILE__;

	/**
	 * Holds version of the plugin.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public $version = '1.5.2';

	/**
	 * Constructor. Hooks all interactions into correct areas to start
	 * the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		/** Run a hook before the slider is loaded and pass the object */
		do_action_ref_array( 'tgmsp_init', array( $this ) );

		/** Run activation hook and make sure the WordPress version supports the plugin */
		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		/** Add theme support for post thumbnails if it doesn't exist */
		if ( ! current_theme_supports( 'post-thumbnails' ) )
			add_theme_support( 'post-thumbnails' );

		/** Load the plugin */
		add_action( 'init', array( $this, 'init' ) );

	}

	/**
 	 * Registers a plugin activation hook to make sure the current WordPress
 	 * version is suitable (>= 3.3.1) for use and that the full version of
 	 * Soliloquy is not already active.
 	 *
 	 * @since 1.0.0
 	 *
 	 * @global int $wp_version The current version of this particular WP instance
 	 */
	public function activation() {

		global $wp_version;

		if ( class_exists( 'Tgmsp', false ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'The main Soliloquy plugin is active on this site.' );
		}

		if ( version_compare( $wp_version, '3.3.1', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( printf( __( 'Sorry, but your version of WordPress, <strong>%s</strong>, does not meet Soliloquy\'s required version of <strong>3.3.1</strong> to run properly. The plugin has been deactivated. <a href="%s">Click here to return to the Dashboard</a>', 'soliloquy-lite' ), $wp_version, admin_url() ) );
		}

	}

	/**
	 * Registers the post type and loads all the actions and
	 * filters for the class.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		/** Load the plugin textdomain for internationalizing strings */
		load_plugin_textdomain( 'soliloquy', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		/** Instantiate all the necessary admin components of the plugin */
		if ( is_admin() ) :
			$tgmsp_lite_admin		= new Tgmsp_Lite_Admin();
			$tgmsp_lite_ajax		= new Tgmsp_Lite_Ajax();
			$tgmsp_lite_editor		= new Tgmsp_Lite_Editor();
			$tgmsp_lite_help		= new Tgmsp_Lite_Help();
			$tgmsp_lite_media		= new Tgmsp_Lite_Media();
			$tgmsp_lite_strings		= new Tgmsp_Lite_Strings();
		endif;

		/** Instantiate all the necessary components of the plugin */
		$tgmsp_lite_assets		= new Tgmsp_Lite_Assets();
		$tgmsp_lite_posttype	= new Tgmsp_Lite_Posttype();
		$tgmsp_lite_shortcode	= new Tgmsp_Lite_Shortcode();

	}

	/**
	 * PSR-0 compliant autoloader to load classes as needed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classname The name of the class
	 * @return null Return early if the class name does not start with the correct prefix
	 */
	public static function autoload( $classname ) {

		if ( 'Tgmsp_Lite' !== mb_substr( $classname, 0, 10 ) )
			return;

		$filename = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . str_replace( '_', DIRECTORY_SEPARATOR, $classname ) . '.php';
		if ( file_exists( $filename ) )
			require $filename;

	}

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	}

	/**
	 * Getter method for retrieving the main plugin filepath.
	 *
	 * @since 1.2.0
	 */
	public static function get_file() {

		return self::$file;

	}

	/**
	 * Helper flag method for any Soliloquy screen.
	 *
	 * @since 1.2.0
	 *
	 * @return bool True if on a Soliloquy screen, false if not
	 */
	public static function is_soliloquy_screen() {

		$current_screen = get_current_screen();

		if ( ! $current_screen )
			return false;

		if ( 'soliloquy' == $current_screen->post_type )
			return true;

		return false;

	}

	/**
	 * Helper flag method for the Add/Edit Soliloquy screens.
	 *
	 * @since 1.2.0
	 *
	 * @return bool True if on a Soliloquy Add/Edit screen, false if not
	 */
	public static function is_soliloquy_add_edit_screen() {

		$current_screen = get_current_screen();

		if ( ! $current_screen )
			return false;

		if ( 'soliloquy' == $current_screen->post_type && 'post' == $current_screen->base )
			return true;

		return false;

	}

}

/** Instantiate the init class */
$tgmsp_lite = new Tgmsp_Lite();

if ( ! function_exists( 'soliloquy_slider' ) ) {
	/**
	 * Template tag function for outputting the slider within templates.
	 *
	 * @since 1.0.0
	 *
	 * @package Soliloquy Lite
	 * @param int $id The Soliloquy slider ID
	 * @param bool $return Flag for returning or echoing the slider content
	 */
	function soliloquy_slider( $id, $return = false ) {

		$id = absint( $id );

		/** Return if no slider ID has been entered or if it is not valid */
		if ( ! $id ) {
			printf( '<p>%s</p>', Tgmsp_Lite_Strings::get_instance()->strings['no_id'] );
			return;
		}

		$validate = get_post( $id, OBJECT );
		if ( ! $validate || isset( $validate->post_type ) && 'soliloquy' !== $validate->post_type ) {
			printf( '<p>%s</p>', Tgmsp_Lite_Strings::get_instance()->strings['invalid_id'] );
			return;
		}

		if ( $return )
			return do_shortcode( '[soliloquy id="' . $id . '"]' );
		else
			echo do_shortcode( '[soliloquy id="' . $id . '"]' );

	}
}