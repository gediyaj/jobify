<?php
/**
 * Shortcode class for Soliloquy Lite.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy Lite
 * @author	Thomas Griffin
 */
class Tgmsp_Lite_Shortcode {

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

		add_shortcode( 'soliloquy', array( $this, 'shortcode' ) );
		add_filter( 'tgmsp_caption_output', 'do_shortcode' );

	}

	/**
	 * Outputs slider data in a shortcode called 'soliloquy'.
	 *
	 * @since 1.0.0
	 *
	 * @global array $soliloquy_data An array of data for the current Soliloquy ID
	 * @global int $soliloquy_count Incremental variable for each Soliloquy on current page
	 * @param array $atts Array of shortcode attributes
	 * @return string $slider Concatenated string of slider data
	 */
	public function shortcode( $atts ) {

		/** Create global variables to store all soliloquy ID's and meta on the current page */
		$soliloquy_data 	= array();
		$soliloquy_count 	= 0;
		global $soliloquy_data, $soliloquy_count;

		/** Extract shortcode atts */
		extract( shortcode_atts( array(
			'id' => 0
		), $atts ) );

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

		/** Ok, we have a valid slider ID - store all data in one variable and get started */
		$soliloquy_data[absint( $soliloquy_count )]['id'] 	= $id;
		$soliloquy_data[absint( $soliloquy_count )]['meta'] = get_post_meta( $id, '_soliloquy_settings', true );
		$slider 											= '';
		$images 											= $this->get_images( $id, $soliloquy_data[absint( $soliloquy_count )]['meta'] );
		$i 													= 1;
		$preloader											= false;

		/** Only proceed if we have images to output */
		if ( $images ) {
			/** Make sure jQuery is loaded and load script and slider */
			wp_enqueue_script( 'soliloquy-script' );
			wp_enqueue_style( 'soliloquy-style' );
			add_action( 'wp_footer', array( $this, 'slider_script' ), 99 );

			/** Allow devs to circumvent the entire slider if necessary - beware, this filter is powerful - use with caution */
			$pre = apply_filters( 'tgmsp_pre_load_slider', false, $id, $images, $soliloquy_data, $soliloquy_count, $slider );
			if ( $pre )
				return $pre;

			do_action( 'tgmsp_before_slider_output', $id, $images, $soliloquy_data, $soliloquy_count, $slider );

			/** If a custom size is chosen, all image sizes will be cropped the same, so grab width/height from first image */
			$width 	= $soliloquy_data[absint( $soliloquy_count )]['meta']['width'] ? $soliloquy_data[absint( $soliloquy_count )]['meta']['width'] : $images[0]['width'];
			$width	= $ratio_width = apply_filters( 'tgmsp_slider_width', $width, $id );
			$width	= preg_match( '|%$|', trim( $width ) ) ? trim( $width ) . ';' : absint( $width ) . 'px;';
			$height = $soliloquy_data[absint( $soliloquy_count )]['meta']['height'] ? $soliloquy_data[absint( $soliloquy_count )]['meta']['height'] : $images[0]['height'];
			$height	= $ratio_height = apply_filters( 'tgmsp_slider_height', $height, $id );
			$height	= preg_match( '|%$|', trim( $height ) ) ? trim( $height ) . ';' : absint( $height ) . 'px;';

			// If the user wants a preloader image, store the aspect ratio for dynamic height calculation.
			if ( isset( $soliloquy_data[absint( $soliloquy_count )]['meta']['preloader'] ) && $soliloquy_data[absint( $soliloquy_count )]['meta']['preloader'] ) {
				$preloader = true;
				$ratio_width  = preg_match( '|%$|', trim( $ratio_width ) ) ? str_replace( '%', '', $ratio_width ) : absint( $ratio_width );
				$ratio_height = preg_match( '|%$|', trim( $ratio_height ) ) ? str_replace( '%', '', $ratio_height ) : absint( $ratio_height );
				$soliloquy_data[absint( $soliloquy_count )]['ratio'] = ( $ratio_width / $ratio_height );
				add_action( 'tgmsp_callback_start_' . $id, array( $this, 'preloader' ) );
				add_filter( 'tgmsp_slider_classes', array( $this, 'preloader_class' ) );
			}

			/** Output the slider info */
			$slider = apply_filters( 'tgmsp_before_slider', $slider, $id, $images, $soliloquy_data, absint( $soliloquy_count ) );
			$slider .= '<div id="soliloquy-container-' . esc_attr( $id ) . '" ' . $this->get_custom_slider_classes() . ' style="' . apply_filters( 'tgmsp_slider_width_output', 'max-width: ' . $width, $width, $id ) . ' ' . apply_filters( 'tgmsp_slider_height_output', 'max-height: ' . $height, $height, $id ) . ' ' . apply_filters( 'tgmsp_slider_container_style', '', $id ) . '">';
				$slider .= '<div id="soliloquy-' . esc_attr( $id ) . '" class="soliloquy">';
					$slider .= '<ul id="soliloquy-list-' . esc_attr( $id ) . '" class="soliloquy-slides">';
						foreach ( $images as $image ) {
							$alt 			= empty( $image['alt'] ) ? apply_filters( 'tgmsp_no_alt', '', $id, $image ) : $image['alt'];
							$title 			= empty( $image['title'] ) ? apply_filters( 'tgmsp_no_title', '', $id, $image ) : $image['title'];
							$link_title 	= empty( $image['linktitle'] ) ? apply_filters( 'tgmsp_no_link_title', '', $id, $image ) : $image['linktitle'];
							$link_target 	= empty( $image['linktab'] ) ? apply_filters( 'tgmsp_no_link_target', '', $id, $image ) : 'target="_blank"';

							$slide = '<li id="soliloquy-' . esc_attr( $id ) . '-item-' . $i . '" class="soliloquy-item" style="' . apply_filters( 'tgmsp_slider_item_style', 'display: none;', $id, $image, $i ) . '" ' . apply_filters( 'tgmsp_slider_item_attr', '', $id, $image, $i ) . '>';
								if ( ! empty( $image['link'] ) )
									$slide .= apply_filters( 'tgmsp_link_output', '<a href="' . esc_url( $image['link'] ) . '" title="' . esc_attr( $link_title ) . '" ' . $link_target . '>', $id, $image, $link_title, $link_target );
								$slide .= apply_filters( 'tgmsp_image_output', '<img class="soliloquy-item-image" src="' . esc_url( $image['src'] ) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $title ) . '" />', $id, $image, $alt, $title );
								if ( ! empty( $image['link'] ) )
									$slide .= '</a>';
								if ( ! empty( $image['caption'] ) )
									$slide .= apply_filters( 'tgmsp_caption_output', '<div class="soliloquy-caption"><div class="soliloquy-caption-inside">' . $image['caption'] . '</div></div>', $id, $image );
							$slide .= '</li>';
							$slider .= apply_filters( 'tgmsp_individual_slide', $slide, $id, $image, $i );
							$i++;
						}
					$slider .= '</ul>';
					$slider = apply_filters( 'tgmsp_inside_slider', $slider, $id, $images, $soliloquy_data, absint( $soliloquy_count ) );
				$slider .= '</div>';
				$slider = apply_filters( 'tgmsp_inside_slider_container', $slider, $id, $images, $soliloquy_data, absint( $soliloquy_count ) );
			$slider .= '</div>';

			$slider = apply_filters( 'tgmsp_after_slider', $slider, $id, $images, $soliloquy_data, absint( $soliloquy_count ) );

			// If we are adding a preloading icon, do it now.
			if ( $preloader ) {
				$slider .= '<style type="text/css">.soliloquy-container.soliloquy-preloader{background: url("' . plugins_url( "css/images/preloader.gif", dirname( dirname( __FILE__ ) ) ) . '") no-repeat scroll 50% 50%;}@media only screen and (-webkit-min-device-pixel-ratio: 1.5),only screen and (-o-min-device-pixel-ratio: 3/2),only screen and (min--moz-device-pixel-ratio: 1.5),only screen and (min-device-pixel-ratio: 1.5){.soliloquy-container.soliloquy-preloader{background-image: url("' . plugins_url( "css/images/preloader@2x.gif", dirname( dirname( __FILE__ ) ) ) . '");background-size: 16px 16px;}}</style>';
			}
		}

		/** Increment the counter in case there are multiple slider instances on the same page */
		$soliloquy_count++;

		return apply_filters( 'tgmsp_slider_shortcode', $slider, $id, $images );

	}

	/**
	 * Instantiate the slider.
	 *
	 * @since 1.0.0
	 *
	 * @global array $soliloquy_data An array of data for the current Soliloquy ID
	 */
	public function slider_script() {

		global $soliloquy_data;

		/** Add support for multiple instances on the same page */
		$do_not_duplicate = array();

		/** Loop through each instance and output the data */
		foreach ( $soliloquy_data as $i => $slider ) {
			/** Only run if the slider ID hasn't been outputted yet */
			if ( ! in_array( $slider['id'], $do_not_duplicate ) ) {
				/** Store ID in variable */
				$do_not_duplicate[] = $slider['id'];
				$animation 			= isset( $slider['meta']['transition'] ) && 'fade' == $slider['meta']['transition'] ? 'fade' : 'slide';

				?>
				<script type="text/javascript">jQuery(window).load(function(){jQuery('#soliloquy-<?php echo absint( $slider['id'] ); ?>').soliloquy({animation:'<?php echo $animation; ?>',slideshowSpeed:<?php echo isset( $slider['meta']['speed'] ) ? absint( $slider['meta']['speed'] ) : '7000'; ?>,animationDuration:<?php echo isset( $slider['meta']['duration'] ) ? absint( $slider['meta']['duration'] ) : '600'; ?>,controlsContainer:'<?php echo apply_filters( 'tgmsp_slider_controls', '#soliloquy-container-' . absint( $slider['id'] ), $slider['id'] ); ?>',namespace:'soliloquy-',selector:'.soliloquy-slides > li',useCSS:false});});</script>
				<?php
			}
		}

	}

	/**
	 * Helper function to get image attachments for a particular slider.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id The ID of the post for retrieving attachments
	 * @return null|array Return early if no ID set, array of images on success
	 */
	public function get_images( $id, $meta = '' ) {

		/** Return early if no ID is set */
		if ( ! $id )
			return;

		/** Store images in an array and grab all attachments from the slider */
		$images = array();

		/** Get the slider size */
		if ( isset( $meta['custom'] ) && $meta['custom'] )
			$size = $meta['custom'];
		else
			$size = 'full';

		/** Prepare args for getting image attachments */
		$args = apply_filters( 'tgmsp_get_slider_images_args', array(
			'orderby' 			=> 'menu_order',
			'order' 			=> 'ASC',
			'post_type' 		=> 'attachment',
			'post_parent' 		=> $id,
			'post_mime_type' 	=> 'image',
			'post_status' 		=> null,
			'posts_per_page' 	=> -1
		), $id );

		/** Get all of the image attachments to the Soliloquy */
		$attachments = apply_filters( 'tgmsp_get_slider_images', get_posts( $args ), $args, $id );

		/** Loop through the attachments and store the data */
		if ( $attachments ) {
			foreach ( (array) $attachments as $attachment ) {
				/** Get attachment metadata for each attachment */
				$image = apply_filters( 'tgmsp_get_image_data', wp_get_attachment_image_src( $attachment->ID, $size ), $id, $attachment, $size );

				/** Store data in an array to send back to the shortcode */
				if ( $image ) {
					$images[] = apply_filters( 'tgmsp_image_data', array(
						'id' 		=> $attachment->ID,
						'src' 		=> $image[0],
						'width' 	=> $image[1],
						'height' 	=> $image[2],
						'title'		=> isset( $attachment->post_title ) ? $attachment->post_title : '',
						'alt' 		=> get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
						'link' 		=> get_post_meta( $attachment->ID, '_soliloquy_image_link', true ),
						'linktitle' => get_post_meta( $attachment->ID, '_soliloquy_image_link_title', true ),
						'linktab' 	=> get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ),
						'caption' 	=> isset( $attachment->post_excerpt ) ? $attachment->post_excerpt : ''
					), $attachment, $id );
				}
			}
		}

		/** Return array of images */
		return apply_filters( 'tgmsp_slider_images', $images, $meta, $attachments );

	}

	/**
	 * Getter method for retrieving custom slider classes.
	 *
	 * @since 1.0.0
	 *
	 * @global array $soliloquy_data Array of data for the current slider
	 * @global int $soliloquy_count The current Soliloquy instance on the page
	 */
	public function get_custom_slider_classes() {

		global $soliloquy_data, $soliloquy_count;
		$classes = array();

		/** Set the default soliloquy-container */
		$classes[] = 'soliloquy-container';

		/** Set a class for the type of transition being used */
		$classes[] = sanitize_html_class( 'soliloquy-' . strtolower( $soliloquy_data[absint( $soliloquy_count )]['meta']['transition'] ), '' );

		/** Now add a filter to addons can access and add custom classes */
		return 'class="' . implode( ' ', apply_filters( 'tgmsp_slider_classes', $classes, $soliloquy_data[absint( $soliloquy_count )]['id'] ) ) . '"';

	}

	/**
	 * Removes the fixed height and preloader image once the slider has initialized.
	 *
	 * @since 1.4.0
	 */
	public function preloader( $id ) {

		echo 'jQuery("#soliloquy-container-' . absint( $id ) . '").css({ "background" : "transparent", "height" : "auto" });';

	}

	/**
	 * Adds the preloader class to the slider to signify use of a preloading image.
	 *
	 * @since 1.4.0
	 *
	 * @param array $classes Array of slider classes
	 * @return array $classes Amended array of slider classes
	 */
	public function preloader_class( $classes ) {

		$classes[] = 'soliloquy-preloader';
		return array_unique( $classes );

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