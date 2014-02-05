<?php
/**
 * List Blog Posts
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Blog_Posts extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_blog_posts';
		$this->widget_description = __( 'Display recent blog posts.', 'jobify' );
		$this->widget_id          = 'jobify_widget_blog_posts';
		$this->widget_name        = __( 'Blog Posts', 'jobify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Recent News Article', 'jobify' ),
				'label' => __( 'Title:', 'jobify' )
			),
			'description' => array(
				'type'  => 'textarea',
				'rows'  => 4,
				'std'   => '',
				'label' => __( 'Description:', 'jobify' ),
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

		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$description = $instance[ 'description' ];
		$posts       = new WP_Query( apply_filters( 'widget_jobify_blog_posts', array( 'posts_per_page' => 3, 'ignore_sticky_posts' => true ) ) );
		
		echo $before_widget;
		?>

		<div class="container">

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<?php if ( $description ) : ?>
				<p class="homepage-widget-description"><?php echo $description; ?></p>
			<?php endif; ?>
			
			<div class="content-grid">
			<?php if ( $posts->have_posts() ) : ?>
				<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
					<?php get_template_part( 'content', 'grid' ); ?>
				<?php endwhile; ?>
			<?php endif; ?>
			</div>

		</div>

		<?php
		echo $after_widget;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}