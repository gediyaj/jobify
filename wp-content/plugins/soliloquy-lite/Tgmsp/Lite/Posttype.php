<?php
/**
 * Posttype class for Soliloquy Lite.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy Lite
 * @author	Thomas Griffin
 */
class Tgmsp_Lite_Posttype {

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

		$labels = apply_filters( 'tgmsp_post_type_labels', array(
			'name' 					=> __( 'Soliloquy', 'soliloquy' ),
			'singular_name' 		=> __( 'Soliloquy', 'soliloquy' ),
			'add_new' 				=> __( 'Add New', 'soliloquy' ),
			'add_new_item' 			=> __( 'Add New Soliloquy Slider', 'soliloquy' ),
			'edit_item' 			=> __( 'Edit Soliloquy Slider', 'soliloquy' ),
			'new_item' 				=> __( 'New Soliloquy Slider', 'soliloquy' ),
			'view_item' 			=> __( 'View Soliloquy Slider', 'soliloquy' ),
			'search_items' 			=> __( 'Search Soliloquy Sliders', 'soliloquy' ),
			'not_found' 			=> __( 'No Soliloquy Sliders found', 'soliloquy' ),
			'not_found_in_trash' 	=> __( 'No Soliloquy Sliders found in trash', 'soliloquy' ),
			'parent_item_colon' 	=> '',
			'menu_name' 			=> __( 'Soliloquy', 'soliloquy' )
		) );

		$args = apply_filters( 'tgmsp_post_type_args', array(
			'labels' 				=> $labels,
			'public' 				=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_admin_bar'		=> false,
			'rewrite'				=> false,
			'query_var'				=> false,
			'menu_position' 		=> 176,
			'menu_icon' 			=> plugins_url( 'css/images/menu-icon.png', dirname( dirname( __FILE__ ) ) ),
			'supports' 				=> array( 'title' )
		) );

		/** Register post type with args */
		register_post_type( 'soliloquy', $args );

		/** Filter the post type columns */
		add_filter( 'manage_edit-soliloquy_columns', array( $this, 'soliloquy_columns' ) );
		add_filter( 'manage_soliloquy_posts_custom_column', array( $this, 'soliloquy_custom_columns' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'soliloquy_row_actions' ) );

	}

	/**
	 * Customize the post columns for the Soliloquy post type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns The default columns provided by WP_List_Table
	 */
	public function soliloquy_columns( $columns ) {

		$columns = array(
			'cb' 		=> '<input type="checkbox" />',
			'title' 	=> Tgmsp_Lite_Strings::get_instance()->strings['column_title'],
			'shortcode' => Tgmsp_Lite_Strings::get_instance()->strings['column_shortcode'],
			'template' 	=> Tgmsp_Lite_Strings::get_instance()->strings['column_function'],
			'images' 	=> Tgmsp_Lite_Strings::get_instance()->strings['column_number'],
			'modified' 	=> Tgmsp_Lite_Strings::get_instance()->strings['column_modified'],
			'date' 		=> Tgmsp_Lite_Strings::get_instance()->strings['column_date']
		);

		return $columns;

	}

	/**
	 * Add data to the custom columns added to the Soliloquy post type.
	 *
	 * @since 1.0.0
	 *
	 * @global object $post The current post object
	 * @param string $column The name of the custom column
	 * @param int $post_id The current post ID
	 */
	public function soliloquy_custom_columns( $column, $post_id ) {

		global $post;
		$post_id = absint( $post_id );

		switch ( $column ) {
			case 'shortcode' :
				echo '<code>[soliloquy id="' . $post_id . '"]</code>';
				break;

			case 'template' :
				echo '<code>if ( function_exists( \'soliloquy_slider\' ) ) soliloquy_slider( \'' . $post_id . '\' );</code>';
				break;

			case 'images' :
				$attachments = get_children( array( 'post_parent' => $post_id ) );
				echo count( $attachments );
				break;

			case 'modified' :
				the_modified_date();
				break;
		}

	}

	/**
	 * Filter out unnecessary row actions from the Soliloquy post table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions Default slider row actions
	 * @return array $actions Amended slider row actions
	 */
	public function soliloquy_row_actions( $actions ) {

		if ( Tgmsp_Lite::is_soliloquy_screen() ) {
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['view'] );
		}

		return $actions;

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