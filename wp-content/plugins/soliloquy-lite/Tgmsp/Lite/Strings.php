<?php
/**
 * Strings class for Soliloquy Lite.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy Lite
 * @author	Thomas Griffin
 */
class Tgmsp_Lite_Strings {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Holds a copy of all the strings used by Soliloquy.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $strings = array();

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		$this->strings = apply_filters( 'tgmsp_strings', array(
			'add_edit_help'			=> __( 'Create and manage your slider from this screen. Click on the Upload Images button to begin uploading your images, and once uploaded, you can drag-and-drop sort them, add image meta and set slider options.', 'soliloquy-lite' ),
			'add_slider'			=> esc_attr__( 'Add Soliloquy Slider', 'soliloquy-lite' ),
			'add_slider_editor'		=> esc_attr__( 'Add Slider', 'soliloquy-lite' ),
			'advanced_help'			=> __( 'Soliloquy Advanced', 'soliloquy-lite' ),
			'advanced_help_desc'	=> __( 'Want even more advanced features for Soliloquy? How about ajax preloading, embedded video support for YouTube and Vimeo, easy internal linking, full support for available FlexSlider options and APIs, custom sizes, and even Addons?', 'soliloquy-lite' ),
			'advanced_help_demo'	=> __( 'Or go ahead and test a live demo of the full version yourself.', 'soliloquy-lite' ),
			'advanced_help_up'		=> __( 'Click here to upgrade to the full version of Soliloquy!', 'soliloquy-lite' ),
			'column_date'			=> __( 'Date', 'soliloquy-lite' ),
			'column_function'		=> __( 'Function', 'soliloquy-lite' ),
			'column_modified'		=> __( 'Last Modified', 'soliloquy-lite' ),
			'column_number'			=> __( 'Number of Images', 'soliloquy-lite' ),
			'column_shortcode'		=> __( 'Shortcode', 'soliloquy-lite' ),
			'column_title'			=> __( 'Title', 'soliloquy-lite' ),
			'dismissing'			=> __( 'Dismissing...', 'soliloquy-lite' ),
			'email_error'			=> __( 'Oops - there was an error. Please try again!', 'soliloquy-lite' ),
			'email_instructions'	=> __( 'Signup for the Soliloquy Newsletter!', 'soliloquy-lite' ),
			'email_desc'			=> __( 'Receive all the latest news on Soliloquy, including plugin updates, new Addons and discount promotions! <em>You will never be spammed.</em>', 'soliloquy-lite' ),
			'email_now'				=> esc_attr__( 'Signup!', 'soliloquy-lite' ),
			'email_placeholder'		=> __( 'Enter your email address here...', 'soliloquy-lite' ),
			'email_success'			=> __( 'Success! You have been signed up!', 'soliloquy-lite' ),
			'image_alt'				=> __( 'Image Alt Tag', 'soliloquy-lite' ),
			'image_caption'			=> __( 'Image Caption', 'soliloquy-lite' ),
			'image_link'			=> __( 'Image Link', 'soliloquy-lite' ),
			'image_link_title'		=> __( 'Image Link Title', 'soliloquy-lite' ),
			'image_meta'			=> __( 'All of the fields below are optional, so leave blank the fields you do not want or need.', 'soliloquy-lite' ),
			'image_title'			=> __( 'Image Title', 'soliloquy-lite' ),
			'image_url'				=> __( 'URL', 'soliloquy-lite' ),
			'image_url_title'		=> __( 'Title', 'soliloquy-lite' ),
			'instructions'			=> __( 'You can place this slider anywhere into your posts, pages, custom post types or widgets by using the shortcode below:', 'soliloquy-lite' ),
			'instructions_more'		=> __( 'You can also place this slider into your template files by using the function below:', 'soliloquy-lite' ),
			'invalid_id'			=> __( 'The slider ID you entered is not valid. Please check to make sure you entered it correctly.', 'soliloquy-lite' ),
			'loading'				=> __( 'Loading...', 'soliloquy-lite' ),
			'main_help'				=> __( 'Soliloquy utilizes custom post types in order to handle slider instances. Each slider instance has its own separate images, attributes and settings. You can get started by clicking the "Add New" button beside the page title.', 'soliloquy-lite' ),
			'main_help_two'			=> __( 'This page can also be used as a quick reference to grab a slider\'s shortcode or template tag for outputting the slider in your posts, pages or theme files.', 'soliloquy-lite' ),
			'menu_title'			=> __( 'Settings', 'soliloquy-lite' ),
			'meta_instructions'		=> __( 'Soliloquy Instructions', 'soliloquy-lite' ),
			'meta_settings'			=> __( 'Soliloquy Settings', 'soliloquy-lite' ),
			'meta_upgrade'			=> __( 'Upgrade Soliloquy', 'soliloquy-lite' ),
			'meta_uploads'			=> __( 'Upload and Customize Images', 'soliloquy-lite' ),
			'modify_image'			=> esc_attr__( 'Click Here to Modify Your Image', 'soliloquy-lite' ),
			'modify_image_tb'		=> __( 'Modify Your Image', 'soliloquy-lite' ),
			'new_tab'				=> __( 'Open link in new tab?', 'soliloquy-lite' ),
			'no_id'					=> __( 'No slider ID was entered. Please enter a slider ID.', 'soliloquy-lite' ),
			'overview'				=> __( 'Overview', 'soliloquy-lite' ),
			'page_title'			=> __( 'Soliloquy Settings', 'soliloquy-lite' ),
			'plugin_settings'		=> __( 'Settings', 'soliloquy-lite' ),
			'pm_cf_deleted'			=> __( 'Soliloquy slider custom field deleted.', 'soliloquy-lite' ),
			'pm_cf_updated'			=> __( 'Soliloquy slider custom field updated.', 'soliloquy-lite' ),
			'pm_draft'				=> __( 'Soliloquy slider draft updated.', 'soliloquy-lite' ),
			'pm_general'			=> __( 'Soliloquy slider updated.', 'soliloquy-lite' ),
			'pm_published'			=> __( 'Soliloquy slider published.', 'soliloquy-lite' ),
			'pm_revision'			=> __( 'Soliloquy slider restored to revision from %s', 'soliloquy-lite' ),
			'pm_saved'				=> __( 'Soliloquy slider saved.', 'soliloquy-lite' ),
			'pm_scheduled'			=> __( 'Soliloquy slider scheduled for: <strong>%1$s</strong>.', 'soliloquy-lite' ),
			'pm_submitted'			=> __( 'Soliloquy slider submitted.', 'soliloquy-lite' ),
			'remove_image'			=> esc_attr__( 'Click Here to Remove Your Image', 'soliloquy-lite' ),
			'removing'				=> __( 'Removing...', 'soliloquy-lite' ),
			'save_meta'				=> esc_attr__( 'Save Meta', 'soliloquy-lite' ),
			'saving'				=> __( 'Saving...', 'soliloquy-lite' ),
			'sidebar_help_title'	=> __( 'For more information:', 'soliloquy-lite' ),
			'sidebar_help_upgrade'	=> __( 'Upgrade Soliloquy', 'soliloquy-lite' ),
			'slider_animate'		=> __( 'Animate Slider Automatically?', 'soliloquy-lite' ),
			'slider_animate_desc'	=> __( 'If unchecked, users must manually scroll through slides.', 'soliloquy-lite' ),
			'slider_animation_dur'	=> __( 'Animation Duration', 'soliloquy-lite' ),
			'slider_cb'				=> __( 'For even more advanced functionality like ajax preloading, embedded video support, controlling mousewheel and keyboard navigation, pause/play elements, extra animations, custom slider sizes registered with WordPress and smooth height controls, %s', 'soliloquy-lite' ),
			'slider_cb_up'			=> __( 'click here to upgrade to the full version of Soliloquy!', 'soliloquy-lite' ),
			'slider_choose'			=> __( 'Choose Your Slider', 'soliloquy-lite' ),
			'slider_insert_tb'		=> __( 'Insert into Slider', 'soliloquy-lite' ),
			'slider_milliseconds'	=> __( 'Value is calculated using milliseconds.', 'soliloquy-lite' ),
			'slider_preloader'		=> __( 'Use Loading Icon?', 'soliloquy-lite' ),
			'slider_preloader_desc'	=> __( 'Outputs a loading icon while your slider loads to prevent content shifting.', 'soliloquy-lite' ),
			'slider_select'			=> __( 'Please select a slider.', 'soliloquy-lite' ),
			'slider_select_desc'	=> __( 'Select a slider below from the list of available sliders and then click \'Insert\' to place the slider into the editor.', 'soliloquy-lite' ),
			'slider_select_insert'	=> esc_attr__( 'Insert Slider', 'soliloquy-lite' ),
			'slider_select_cancel'	=> esc_attr__( 'Cancel Slider Insertion', 'soliloquy-lite' ),
			'slider_size'			=> __( 'Slider Size', 'soliloquy-lite' ),
			'slider_size_desc'		=> __( 'The <strong>relative</strong> size of the slider in pixels (<strong>width</strong> &#215; <strong>height</strong>).', 'soliloquy-lite' ),
			'slider_size_more'		=> __( 'Click here to learn more', 'soliloquy-lite' ),
			'slider_size_explain'	=> __( '<strong>When setting your own slider size, the width and height of your images must match your specified size.</strong> This setting provides a truly responsive option for your slider and will not crop or adjust images exactly to the specified dimensions. If you need custom pre-defined image sizes, ', 'soliloquy-lite' ),
			'slider_size_upgrade'	=> __( 'upgrade to the full version of Soliloquy for that functionality', 'soliloquy-lite' ),
			'slider_speed'			=> __( 'Slider Speed', 'soliloquy-lite' ),
			'slider_transition'		=> __( 'Slider Transition', 'soliloquy-lite' ),
			'update_meta'			=> __( 'Update Image Metadata', 'soliloquy-lite' ),
			'upload_images'			=> esc_attr__( 'Click Here to Upload Images', 'soliloquy-lite' ),
			'upload_images_tb'		=> __( 'Upload Your Images', 'soliloquy-lite' ),
			'upload_info'			=> __( 'Click on the button below to select and upload your images. Once your images have been uploaded, you can customize their properties and sort them according to your needs. The images will appear below the upload button as thumbnail versions of their actual size.', 'soliloquy-lite' ),
			'upgrade'				=> __( 'Do you want to experience Soliloquy at its full potential, have complete control over your sliders and receive rock-solid support?', 'soliloquy-lite' ),
			'upgrade_now'			=> __( 'Click here to purchase your upgrade for Soliloquy and unleash the power of the best responsive WordPress slider plugin on the market!', 'soliloquy-lite' ),
			'upgrade_nag'			=> __( 'Want full access to all available Soliloquy features? %s %s', 'soliloquy-lite' ),
			'upgrade_nag_link'		=> __( 'Click here to upgrade Soliloquy now!', 'soliloquy-lite' ),
			'upgrade_nag_dismiss'	=> __( 'Dismiss this notice', 'soliloquy-lite' ),
			'working'				=> __( 'Doing...', 'soliloquy-lite' )
		) );

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