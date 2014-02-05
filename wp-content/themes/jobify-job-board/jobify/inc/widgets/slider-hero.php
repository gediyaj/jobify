<?php
/**
 * Solioquy Hero Slider
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Slider_Hero extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_slider_hero';
		$this->widget_description = __( 'Display a "Hero" Soliloquy slider.', 'jobify' );
		$this->widget_id          = 'jobify_widget_slider_hero';
		$this->widget_name        = __( 'Solioquy Hero Slider', 'jobify' );
		$this->settings           = array(
			'slider' => array(
				'type'    => 'select',
				'label'   => __( 'Slider:', 'jobify' ),
				'options' => jobify_slider_options(),
				'std'     => 0
			)
		);
		parent::__construct();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		if ( $this->get_cached_widget( $args ) )
			return;

		ob_start();

		extract( $args );
		
		$slider     = absint( $instance[ 'slider' ] );
		
		echo $before_widget;
		
		if ( function_exists( 'soliloquy_slider' ) ) {
			add_filter( 'tgmsp_caption_output', array( $this, 'caption_output' ), 10, 3 );
			add_filter( 'tgmsp_slider_width_output', array( $this, 'width_height' ) );
			add_filter( 'tgmsp_slider_height_output', array( $this, 'width_height' ) );
			add_filter( 'tgmsp_image_output', array( $this, 'image_output' ), 10, 5 );

			soliloquy_slider( $slider );

			remove_filter( 'tgmsp_caption_output', array( $this, 'caption_output' ), 10, 3 );
			remove_filter( 'tgmsp_slider_width_output', array( $this, 'width_height' ) );
			remove_filter( 'tgmsp_slider_height_output', array( $this, 'width_height' ) );
			remove_filter( 'tgmsp_image_output', array( $this, 'image_output' ), 10, 5 );
		}

		echo $after_widget;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}

	function width_height() {
		return;
	}

	function image_output( $output, $id, $image, $alt, $title ) {
		$retina      = get_post_meta( $id, '_soliloquy_settings', true );
		$retina      = isset ( $retina[ 'retina' ] ) ? 1 : 0;

		if ( $retina ) {
			$output = '<img class="soliloquy-item-image" src="' . esc_url( $image['src'] ) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $title ) . '" width="' . $image[ 'width' ] / 2 . '" height="' . $image[ 'height' ] / 2 . '" />';
		}

		return $output;
	}

	function caption_output( $output, $id, $image ) {
		$caption = '<div class="soliloquy-caption-wrap">';
		$caption .= '<h2 class="soliloquy-caption-title">' . $image[ 'title' ] . '</h2>';
		$caption .= wpautop( $output );
		$caption .= '</div>';

		return $caption;
	}
}