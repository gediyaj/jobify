<?php
/**
 * Individual Testimonials
 *
 * Use "individual" category with Testimonials by WooThemes
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Testimonials extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_testimonials';
		$this->widget_description = __( 'Display a slider of all the people you have helped.', 'jobify' );
		$this->widget_id          = 'jobify_widget_testimonials';
		$this->widget_name        = __( 'Testimonials', 'jobify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Kind Words From Happy Campers', 'jobify' ),
				'label' => __( 'Title:', 'jobify' )
			),
			'description' => array(
				'type'  => 'textarea',
				'rows'  => 4,
				'std'   => 'What other people thought about the service provided by Jobify',
				'label' => __( 'Description:', 'jobify' ),
			),
			'number' => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 8,
				'label' => __( 'Number of testimonials:', 'jobify' )
			),
			'background' => array(
				'type'  => 'text',
				'std'   => null,
				'label' => __( 'Background Image:', 'jobify' )
			),
			'animations' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Enable jQuery animations', 'jobify' )
			),
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

		wp_enqueue_script( 'flexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array( 'jquery' ) );

		extract( $args );

		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$number      = $instance[ 'number' ];
		$description = $instance[ 'description' ];
		$background  = esc_url( $instance[ 'background' ] );

		echo $before_widget;
		?>

		<div class="container">

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<?php if ( $description ) : ?>
				<p class="homepage-widget-description"><?php echo $description; ?></p>
			<?php endif; ?>
			
			<div class="testimonial-slider-wrap <?php echo $instance[ 'animations' ] ? 'animated' : 'static'; ?>">
				<div class="testimonial-slider">
					<?php	
						do_action( 'woothemes_testimonials', array( 
							'category' => 'individual', 
							'limit'    => $number,
							'size'     => 70,
							'before'   => '',
							'after'    => ''
						) );
					?>
				</div>
			</div>

		</div>

		<style>
		#<?php echo $this->id; ?> { background-image: url(<?php echo $background; ?>); }
		</style>

		<?php
		echo $after_widget;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}