<?php
/**
 * Contextual help class for Soliloquy Lite.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy Lite
 * @author	Thomas Griffin
 */
class Tgmsp_Lite_Help {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		add_action( 'admin_head', array( $this, 'contextual_help' ) );

	}

	/**
	 * Adds contextual help to Soliloquy pages.
	 *
	 * @since 1.0.0
	 *
	 * @global object $post The current post object
	 */
	public function contextual_help() {

		global $post;
		$current_screen = get_current_screen();

		/** Set a 'global' help sidebar for all Soliloquy related pages */
		if ( Tgmsp_Lite::is_soliloquy_screen() )
			$current_screen->set_help_sidebar( sprintf( '<p><strong>%1$s</strong></p><p><strong><a href="' . apply_filters( 'tgmsp_affiliate_url', 'http://soliloquywp.com/pricing/?utm_source=orgrepo&utm_medium=link&utm_campaign=Soliloquy%2BLite' ) . '" title="%2$s" target="_blank">%2$s</a></strong></p>', Tgmsp_Lite_Strings::get_instance()->strings['sidebar_help_title'], Tgmsp_Lite_Strings::get_instance()->strings['sidebar_help_upgrade'] ) );

		/** Set help for the main edit screen */
		if ( 'edit-soliloquy' == $current_screen->id && Tgmsp_Lite::is_soliloquy_screen() ) {
			$current_screen->add_help_tab( array(
				'id'		=> 'soliloquy-main-help',
				'title'		=> Tgmsp_Lite_Strings::get_instance()->strings['overview'],
				'content'	=> sprintf( '<p>%s</p><p>%s</p><p><strong>%s</strong></p>', Tgmsp_Lite_Strings::get_instance()->strings['main_help'], Tgmsp_Lite_Strings::get_instance()->strings['main_help_two'], sprintf( Tgmsp_Lite_Strings::get_instance()->strings['upgrade_nag'], sprintf( '<a href="' . apply_filters( 'tgmsp_affiliate_url', 'http://soliloquywp.com/pricing/?utm_source=orgrepo&utm_medium=link&utm_campaign=Soliloquy%2BLite' ) . '" title="%1$s" target="_blank">%1$s</a>', Tgmsp_Lite_Strings::get_instance()->strings['upgrade_nag_link'] ), '' ) )
			) );
		}

		/** Set help for the Add New and Edit screens */
		if ( Tgmsp_Lite::is_soliloquy_add_edit_screen() ) {
			$current_screen->add_help_tab( array(
				'id'		=> 'soliloquy-add-help',
				'title'		=> Tgmsp_Lite_Strings::get_instance()->strings['overview'],
				'content'	=> sprintf( '<p>%s</p>', Tgmsp_Lite_Strings::get_instance()->strings['add_edit_help'] )
			) );
			$current_screen->add_help_tab( array(
				'id'		=> 'soliloquy-advanced-help',
				'title'		=> Tgmsp_Lite_Strings::get_instance()->strings['advanced_help'],
				'content'	=> sprintf( '<p><strong>%1$s</strong></p><p><a href="' . apply_filters( 'tgmsp_affiliate_url', 'http://soliloquywp.com/pricing/?utm_source=orgrepo&utm_medium=link&utm_campaign=Soliloquy%2BLite' ) . '" title="%2$s" target="_blank"><strong>%2$s</strong></a></p><p>', Tgmsp_Lite_Strings::get_instance()->strings['advanced_help_desc'], Tgmsp_Lite_Strings::get_instance()->strings['advanced_help_up'] )
			) );
		}

	}

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	}

}