<?php
/**
 * Ajax class for Soliloquy Lite.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy Lite
 * @author	Thomas Griffin
 */
class Tgmsp_Lite_Ajax {

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

		add_action( 'wp_ajax_soliloquy_dismiss_notice', array( $this, 'dismiss' ) );
		add_action( 'wp_ajax_soliloquy_refresh_images', array( $this, 'refresh_images' ) );
		add_action( 'wp_ajax_soliloquy_iframe_refresh_images', array ( $this, 'refresh_images' ) );
		add_action( 'wp_ajax_soliloquy_sort_images', array( $this, 'sort_images' ) );
		add_action( 'wp_ajax_nopriv_soliloquy_sort_images', array( $this, 'sort_images' ) );
		add_action( 'wp_ajax_soliloquy_remove_images', array( $this, 'remove_images' ) );
		add_action( 'wp_ajax_soliloquy_update_meta', array( $this, 'update_meta' ) );

	}

	/**
	 * Dismisses the upgrade nag notice from the Dashboard.
	 *
	 * @since 1.0.0
	 */
	public function dismiss() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_dismissing', 'nonce' );

		/** Update the user meta with a value */
		if ( update_user_meta( get_current_user_id(), 'soliloquy_dismissed_notice', 1 ) );
			echo json_encode( true );

		/** Kill the script */
		die;

	}

	/**
	 * Ajax callback to refresh attachment images for the current Soliloquy.
	 *
	 * @since 1.0.0
	 */
	public function refresh_images() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_uploader', 'nonce' );

		/** Prepare our variables */
		$response['images'] = array(); // This will hold our images as an object titled 'images'
		$images 			= array();
		$args 				= array(
			'orderby' 			=> 'menu_order',
			'order' 			=> 'ASC',
			'post_type' 		=> 'attachment',
			'post_parent' 		=> $_POST['id'],
			'post_mime_type' 	=> 'image',
			'post_status' 		=> null,
			'posts_per_page' 	=> -1
		);

		/** Get all of the image attachments to the Soliloquy */
		$attachments = get_posts( $args );

		/** Loop through the attachments and store the data */
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				/** Get attachment metadata for each attachment */
				$image = wp_get_attachment_image_src( $attachment->ID, 'soliloquy-thumb' );

				/** Store data in an array to send back to the script as on object */
				$images[] = apply_filters( 'tgmsp_ajax_refresh_callback', array(
					'id' 		=> $attachment->ID,
					'src' 		=> $image[0],
					'width' 	=> $image[1],
					'height' 	=> $image[2],
					'title' 	=> $attachment->post_title,
					'alt' 		=> get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
					'link' 		=> get_post_meta( $attachment->ID, '_soliloquy_image_link', true ),
					'linktitle' => get_post_meta( $attachment->ID, '_soliloquy_image_link_title', true ),
					'linktab' 	=> get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ),
					'linkcheck' => checked( get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ), 1, false ),
					'caption' 	=> $attachment->post_excerpt
				), $attachment );
			}
		}

		$response['images'] = $images;

		do_action( 'tgmsp_ajax_refresh_images', $_POST, $images );

		/** Json encode the images, send them back to the script for processing and die */
		echo json_encode( $response );
		die;

	}

	/**
	 * Ajax callback to save the sortable image order for the current slider.
	 *
	 * @since 1.0.0
	 */
	public function sort_images() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_sortable', 'nonce' );

		/** Prepare our variables */
		$order 	= explode( ',', $_POST['order'] );
		$i 		= 1;

		/** Update the menu order for the images in the database */
		foreach ( $order as $id ) {
			$sort 				= array();
			$sort['ID'] 		= $id;
			$sort['menu_order'] = $i;
			wp_update_post( $sort );
			$i++;
		}

		do_action( 'tgmsp_ajax_sort_images', $_POST );

		/** Send the order back to the script */
		echo json_encode( $order );
		die;

	}

	/**
	 * Ajax callback to remove an image from the current Soliloquy.
	 *
	 * @since 1.0.0
	 */
	public function remove_images() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_remove', 'nonce' );

		/** Prepare our variable */
		$attachment_id = (int) $_POST['attachment_id'];

		/** Delete the corresponding attachment */
		wp_delete_attachment( $attachment_id );

		do_action( 'tgmsp_ajax_remove_images', $attachment_id );

		die;

	}

	/**
	 * Ajax callback to update image meta for the current Soliloquy.
	 *
	 * @since 1.0.0
	 */
	public function update_meta() {

		/** Do a security check first */
		check_ajax_referer( 'soliloquy_meta', 'nonce' );

		/** Make sure attachment ID is an integer */
		$attachment_id = (int) $_POST['attach'];

		/** Update attachment title */
		$title 					= array();
		$title['ID'] 			= $attachment_id;
		$title['post_title'] 	= strip_tags( $_POST['soliloquy-title'] );
		wp_update_post( $title );

		/** Update attachment alt text */
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', strip_tags( $_POST['soliloquy-alt'] ) );

		/** Update attachment link items */
		update_post_meta( $attachment_id, '_soliloquy_image_link', esc_url( $_POST['soliloquy-link'] ) );
		update_post_meta( $attachment_id, '_soliloquy_image_link_title', esc_attr( strip_tags( $_POST['soliloquy-link-title'] ) ) );
		update_post_meta( $attachment_id, '_soliloquy_image_link_tab', ( 'true' == $_POST['soliloquy-link-check'] ) ? (int) 1 : (int) 0 );

		/** Update attachment caption */
		$caption 					= array();
		$caption['ID'] 				= $attachment_id;
		$caption['post_excerpt'] 	= wp_kses_post( $_POST['soliloquy-caption'] );
		wp_update_post( $caption );

		do_action( 'tgmsp_ajax_update_meta', $_POST );

		die;

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