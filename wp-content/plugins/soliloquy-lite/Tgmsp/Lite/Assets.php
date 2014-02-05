<?php
/**
 * Aseets class for Soliloquy Lite.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy
 * @author	Thomas Griffin
 */
class Tgmsp_Lite_Assets {

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

		add_image_size( 'soliloquy-thumb', 115, 115, true );

		/** Register scripts and styles */
		wp_register_script( 'soliloquy-admin', plugins_url( 'js/admin.js', dirname( dirname( __FILE__ ) ) ), array( 'jquery' ), Tgmsp_Lite::get_instance()->version, true );
		wp_register_script( 'soliloquy-script', plugins_url( 'js/soliloquy.js', dirname( dirname( __FILE__ ) ) ), array( 'jquery' ), Tgmsp_Lite::get_instance()->version, true );
		wp_register_style( 'soliloquy-admin', plugins_url( 'css/admin.css', dirname( dirname( __FILE__ ) ) ), array(), Tgmsp_Lite::get_instance()->version );
		wp_register_style( 'soliloquy-style', plugins_url( 'css/soliloquy.css', dirname( dirname( __FILE__ ) ) ), array(), Tgmsp_Lite::get_instance()->version );

		/** Load assets */
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );

	}

	/**
	 * Enqueue custom scripts and styles for the Soliloquy post type.
	 *
	 * @since 1.0.0
	 *
	 * @global int $id The current post ID
	 * @global object $post The current post object
	 */
	public function load_assets() {

		global $id, $post;

		/** Load for any Soliloquy screen */
		if ( Tgmsp_Lite::is_soliloquy_screen() ) {
			wp_enqueue_style( 'soliloquy-admin' );

			/** Send the post ID along with our script */
			if ( Tgmsp_Lite::is_soliloquy_add_edit_screen() )
				$post_id = ( null === $id ) ? $post->ID : $id;
			else
				$post_id = 0;

			/** Store script arguments in an array */
			$args = apply_filters( 'tgmsp_slider_object_args', array(
				'alt'			=> Tgmsp_Lite_Strings::get_instance()->strings['image_alt'],
				'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
				'caption'		=> Tgmsp_Lite_Strings::get_instance()->strings['image_caption'],
				'dismissnonce'	=> wp_create_nonce( 'soliloquy_dismissing' ),
				'dismissing'	=> Tgmsp_Lite_Strings::get_instance()->strings['dismissing'],
				'duration'		=> 600,
				'id'			=> $post_id,
				'height'		=> 300,
				'link'			=> Tgmsp_Lite_Strings::get_instance()->strings['image_link'],
				'linknonce'		=> wp_create_nonce( 'soliloquy_linking' ),
				'linktitle'		=> Tgmsp_Lite_Strings::get_instance()->strings['image_url_title'],
				'loading'		=> Tgmsp_Lite_Strings::get_instance()->strings['loading'],
				'metadesc'		=> Tgmsp_Lite_Strings::get_instance()->strings['image_meta'],
				'metanonce'		=> wp_create_nonce( 'soliloquy_meta' ),
				'metatitle'		=> Tgmsp_Lite_Strings::get_instance()->strings['update_meta'],
				'modify'		=> Tgmsp_Lite_Strings::get_instance()->strings['modify_image'],
				'modifytb'		=> Tgmsp_Lite_Strings::get_instance()->strings['modify_image_tb'],
				'nonce'			=> wp_create_nonce( 'soliloquy_uploader' ),
				'remove'		=> Tgmsp_Lite_Strings::get_instance()->strings['remove_image'],
				'removenonce'	=> wp_create_nonce( 'soliloquy_remove' ),
				'removing'		=> Tgmsp_Lite_Strings::get_instance()->strings['removing'],
				'saving'		=> Tgmsp_Lite_Strings::get_instance()->strings['saving'],
				'sortnonce'		=> wp_create_nonce( 'soliloquy_sortable' ),
				'speed'			=> 7000,
				'spinner'		=> plugins_url( 'css/images/loading.gif', dirname( dirname( __FILE__ ) ) ),
				'savemeta'		=> Tgmsp_Lite_Strings::get_instance()->strings['save_meta'],
				'upload'		=> Tgmsp_Lite_Strings::get_instance()->strings['upload_images_tb'],
				'tab'			=> Tgmsp_Lite_Strings::get_instance()->strings['new_tab'],
				'title'			=> Tgmsp_Lite_Strings::get_instance()->strings['image_title'],
				'url'			=> Tgmsp_Lite_Strings::get_instance()->strings['image_url'],
				'width'			=> 600
			) );

			wp_enqueue_script( 'soliloquy-admin' );
			wp_localize_script( 'soliloquy-admin', 'soliloquy', $args );
		}

		/** Only load for the Soliloquy post type add and edit screens */
		if ( Tgmsp_Lite::is_soliloquy_add_edit_screen() ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			add_thickbox();
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