<?php
/**
 * Recent Jobs and Job Spotlight
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Jobs extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_jobs';
		$this->widget_description = __( 'Output a list of sortable jobs.', 'jobify' );
		$this->widget_id          = 'jobify_widget_jobs';
		$this->widget_name        = __( 'Jobs', 'jobify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Recent Jobs', 'jobify' ),
				'label' => __( 'Title:', 'jobify' )
			),
			'number' => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 5,
				'label' => __( 'Number of jobs to show:', 'jobify' )
			),
			'spotlight' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display the "Job Spotlight" section', 'jobify' )
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

		$title     = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$number    = $instance[ 'number' ];
		$spotlight = $instance[ 'spotlight' ];
		
		echo $before_widget;
		?>
			
			<div class="container">

				<div class="recent-jobs<?php echo $spotlight ? ' has-spotlight' : null; ?>">
					<?php 
						if ( $title ) echo $before_title . $title . $after_title;
						echo do_shortcode( '[jobs show_filters=0 per_page=' . $number . ']' );
					?>
				</div>

				<?php if ( $spotlight ) : ?>
				<div class="job-spotlight">
					<h3 class="homepage-widget-title"><?php echo apply_filters( 'jobify_job_spotlight_title', __( 'Job Spotlight', 'jobify' ) ); ?></h3>

					<?php
						$spotlight = new WP_Query( array(
							'post_type' => 'job_listing',
							'post_status' => 'publish',
							'meta_query' => array(
								array(
									'key'   => '_featured',
									'value' => 1
								)
							),
							'posts_per_page' => 1,
						    'orderby' => 'rand',
						    'no_found_rows' => true,
						    'update_post_term_cache' => false,
						    'update_post_meta_cache' => false,
						) );

						if ( $spotlight->have_posts() ) : while ( $spotlight->have_posts() ) : $spotlight->the_post();
							add_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
					?>
						<?php get_template_part( 'content', 'single-job-featured' ); ?>		
					<?php 
							remove_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
						endwhile; endif; 
					?>			
				</div>
				<?php endif; ?>

			</div>

		<?php
		echo $after_widget;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}

	public function excerpt_length() {
		return 20;
	}
}