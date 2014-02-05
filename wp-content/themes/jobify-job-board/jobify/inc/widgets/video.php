<?php
/**
 * Video Widget
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Video extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_video';
		$this->widget_description = __( 'Display a video via oEmbed with a title and description.', 'jobify' );
		$this->widget_id          = 'jobify_widget_video';
		$this->widget_name        = __( 'Video', 'jobify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Basic Listing', 'jobify' ),
				'label' => __( 'Title:', 'jobify' )
			),
			'description' => array(
				'type'  => 'textarea',
				'rows'  => 8,
				'std'   => '',
				'label' => __( 'Description:', 'jobify' ),
			),
			'video' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Video URL:', 'jobify' )
			),
			'animations' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Enable jQuery animations', 'jobify' )
			),
		);
		$this->control_ops = array(
			'width'  => 400
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

		global $wp_embed;

		ob_start();

		extract( $args );

		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$description = $instance[ 'description' ];
		$video       = esc_url( $instance[ 'video' ] );
		
		$content = ob_get_clean();

		echo $before_widget;
		?>

		<div class="container">
			<div class="video-description">
				<?php if ( $title ) echo $before_title . $title . $after_title; ?>

				<?php if ( $description ) : ?>
					<p class="homepage-widget-description"><?php echo wpautop( $description ); ?></p>
				<?php endif; ?>
			</div>

			<div class="video-preview <?php echo $instance[ 'animations' ] ? 'animated' : 'static'; ?>">
				<?php echo $wp_embed->run_shortcode( '[embed]' . $video . '[/embed]' ); ?>
			</div>
		</div>

		<?php
		echo $after_widget;

		echo $content;

		$this->cache_widget( $args, $content );
	}
}