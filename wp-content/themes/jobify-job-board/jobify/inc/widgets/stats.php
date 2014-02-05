<?php
/**
 * Site Stats
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Stats extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_stats';
		$this->widget_description = __( 'Display useful statistics about your website.', 'jobify' );
		$this->widget_id          = 'jobify_widget_stats';
		$this->widget_name        = __( 'Stats', 'jobify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Site Stats', 'jobify' ),
				'label' => __( 'Title:', 'jobify' )
			),
			'description' => array(
				'type'  => 'textarea',
				'rows'  => 4,
				'std'   => 'Here we list our site stats and how many people we&#39;ve helped find a job and companies have 
found recruits',
				'label' => __( 'Description:', 'jobify' ),
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

		global $wpdb;

		ob_start();

		extract( $args );

		$title       = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$description = $instance[ 'description' ];

		$jobs_posted = wp_count_posts( 'job_listing' )->publish;

		$jobs_filled = $wpdb->get_var( 
			"SELECT COUNT(*) 
			 FROM $wpdb->postmeta 
			 WHERE meta_key = '_filled' 
			 AND meta_value = '1'" 
		);

		$companies   = $wpdb->get_col( 
			"SELECT pm.meta_value FROM {$wpdb->postmeta} pm
			 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = '_company_name' 
			 AND p.post_status = 'publish' 
			 AND p.post_type = 'job_listing'" 
		);
		$companies   = count( array_unique( $companies ) );
		
		$users       = count_users();
		$registered  = $users[ 'total_users' ];

		echo $before_widget;
		?>

		<div class="container">

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<?php if ( $description ) : ?>
				<p class="homepage-widget-description"><?php echo $description; ?></p>
			<?php endif; ?>

			<ul class="job-stats">
				<li class="job-stat">
					<strong><?php echo number_format_i18n( $jobs_posted ); ?></strong>
					<?php echo _n( 'Job Posted', 'Jobs Posted', $jobs_posted, 'jobify' ); ?>
				</li>
				<li class="job-stat">
					<strong><?php echo number_format_i18n( $jobs_filled ); ?></strong>
					<?php echo _n( 'Position Filled', 'Positions Filled', $jobs_filled, 'jobify' ); ?>
				</li>
				<li class="job-stat">
					<strong><?php echo number_format_i18n( $companies ); ?></strong>
					<?php echo _n( 'Company', 'Companies', $companies, 'jobify' ); ?>
				</li>
				<li class="job-stat">
					<strong><?php echo number_format_i18n( $registered ); ?></strong>
					<?php echo _n( 'Member', 'Members', $registered, 'jobify' ); ?>
				</li>
			</ul>
		
		</div>

		<?php
		echo $after_widget;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}