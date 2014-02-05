<?php
/**
 * Media class for Soliloquy Lite.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy Lite
 * @author	Thomas Griffin
 */
class Tgmsp_Lite_Media {

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

		add_action( 'load-media-upload.php', array( $this, 'load_media_upload' ) );
		add_action( 'load-async-upload.php', array( $this, 'load_async_upload' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'add_image_link' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'save_image_link' ), 10, 2 );

	}

	/**
	 * Runs on the load-media-upload.php action hook and conditionally
	 * adds actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function load_media_upload() {

		/** This is the screen and context we're looking for */
		if ( $this->is_our_context() ) {
			add_filter( 'media_upload_tabs', array( $this, 'remove_tabs' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_filter( 'media_upload_form_url', array( $this, 'media_upload_form_url' ) );
			add_filter( 'gettext', array( $this, 'thickbox_context' ), 1, 3 );
		}

	}

	/**
	 * Removes 'Gallery' and 'Media Library' tabs from the Media Uploader.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Default media upload tabs
	 * @return array $tabs Amended media upload tabs
	 */
	public function remove_tabs( $tabs ) {

		unset( $tabs['gallery'] );
		unset( $tabs['library'] );

		return $tabs;

	}

	/**
	 * Runs on the load-async-upload.php action hook and conditionally
	 * adds actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function load_async_upload() {

		/** This is the screen and context we're looking for */
		if ( $this->is_our_context() )
			add_filter( 'gettext', array( $this, 'thickbox_context' ), 1, 3 );

	}

	/**
	 * Is this the Soliloquy upload iframe context?
	 *
	 * @since 1.0.0
	 *
	 * @global string $pagenow Current WordPress admin screen
	 * @return bool
	 */
	public function is_our_context() {

		global $pagenow;

		if ( isset( $_REQUEST['context'] ) && 'soliloquy-image-uploads' == $_REQUEST['context'] )
			return true;

		if ( 'async-upload.php' == $pagenow && isset( $_REQUEST['fetch'] ) && isset( $_REQUEST['attachment_id'] ) ) {
			$parent = get_post( wp_get_post_parent_id( $_REQUEST['attachment_id'] ) );

			if ( $parent )
				if ( 'soliloquy' == $parent->post_type )
					return true;
		}

		/** The current action is not in our context, so return false */
		return false;

	}

	/**
	 * Adds context=soliloquy-image-uploads to the browser media upload form action URL
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The current media upload form URL
	 * @return string $url Amended media upload form URL with our context
	 */
	public function media_upload_form_url( $url ) {

		return add_query_arg( 'context', 'soliloquy-image-uploads', $url );

	}

	/**
	 * Removes any unnecessary elements during the slider insert and
	 * update process via CSS.
	 *
	 * @since 1.0.0
	 */
	public function admin_head() {

		?>
		<style type="text/css">#media-items .post_content, #media-items .url, #media-items .align, #media-items .image-size, #media-items .media-blank tr:nth-child(7n), p.media-types label:last-child, #gallery-settings { display: none !important; }#media-items td.field { vertical-align: middle; }</style>
		<?php

	}

	/**
	 * Filter the thickbox insert button text for our image upload context.
	 *
	 * @since 1.0.0
	 *
	 * @param string $translated_text The translated text string
	 * @param string $source_text The source text string (not yet translated)
	 * @param string $domain The textdomain for the text string
	 * @return string $translated_text Amended translated text
	 */
	public function thickbox_context( $translated_text, $source_text, $domain ) {

		if ( 'Insert into Post' == $source_text )
			return Tgmsp_Lite_Strings::get_instance()->strings['slider_insert_tb'];

		return $translated_text;

	}

	/**
	 * Add an extra image meta field to store image links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Default array of meta fields for uploads
	 * @param object $attachment The current attachment object
	 */
	public function add_image_link( $fields, $attachment ) {

		if ( $this->is_our_context() || Tgmsp_Lite::is_soliloquy_screen() ) {
			$fields['soliloquy_link'] = apply_filters( 'tgmsp_extra_media_fields_link', array(
				'label' => Tgmsp_Lite_Strings::get_instance()->strings['image_link'],
				'input' => 'text',
				'value' => get_post_meta( $attachment->ID, '_soliloquy_image_link', true )
			) );

			$fields['soliloquy_link_title'] = apply_filters( 'tgmsp_extra_media_fields_link_title', array(
				'label' => Tgmsp_Lite_Strings::get_instance()->strings['image_link_title'],
				'input' => 'text',
				'value' => get_post_meta( $attachment->ID, '_soliloquy_image_link_title', true )
			) );

			$fields['soliloquy_link_tab'] = apply_filters( 'tgmsp_extra_media_fields_link_tab', array(
				'label' => Tgmsp_Lite_Strings::get_instance()->strings['new_tab'],
				'input' => 'html',
				'html' 	=> '<input id="attachments[' . $attachment->ID . '][soliloquy_link_tab]" name="attachments[' . $attachment->ID . '][soliloquy_link_tab]" type="checkbox" value="' . get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ) . '"' . checked( get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ), 1, false ) . ' />'
			) );

			$fields = apply_filters( 'tgmsp_media_fields', $fields, $attachment );
		}

		return $fields;

	}

	/**
	 * Save extra image meta field to store image links.
	 *
	 * @since 1.0.0
	 *
	 * @param object $attachment The current attachment object
	 * @param array $post_var The submitted $_POST array
	 */
	public function save_image_link( $attachment, $post_var ) {

		if ( $this->is_our_context() || Tgmsp_Lite::is_soliloquy_screen() ) {
			/** Update image meta link field */
			update_post_meta( $attachment['ID'], '_soliloquy_image_link', isset( $post_var['soliloquy_link'] ) ? esc_url( $post_var['soliloquy_link'] ) : '' );
			update_post_meta( $attachment['ID'], '_soliloquy_image_link_title', isset( $post_var['soliloquy_link_title'] ) ? esc_attr( strip_tags( $post_var['soliloquy_link_title'] ) ) : '' );
			update_post_meta( $attachment['ID'], '_soliloquy_image_link_tab', isset( $post_var['soliloquy_link_tab'] ) ? (int) 1 : (int) 0 );

			do_action( 'tgmsp_update_media_fields', $attachment, $post_var );
		}

		return $attachment;

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