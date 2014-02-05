<?php
/**
 * Price table populated by Price Options
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Price_Table extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_price_table';
		$this->widget_description = __( 'Output the price table (based on the "Price Table" widget area)', 'jobify' );
		$this->widget_id          = 'jobify_widget_price_table';
		$this->widget_name        = __( 'Price Table', 'jobify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Plans and Pricing', 'jobify' ),
				'label' => __( 'Title:', 'jobify' )
			),
			'description' => array(
				'type'  => 'textarea',
				'rows'  => 4,
				'std'   => '',
				'label' => __( 'Description:', 'jobify' ),
			),
			'nothing' => array(
				'type' => 'description',
				'std'  => __( 'Drag "Price Option" widgets to the "Price Table" widget area to populate this widget.', 'jobify' )
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

		$title        = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$description  = $instance[ 'description' ];
		$the_sidebars = wp_get_sidebars_widgets();
		$widget_count = count( $the_sidebars[ 'widget-area-price-options' ] );
		
		$content = ob_get_clean();

		echo $before_widget;
		?>

		<div class="container">

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<?php if ( $description ) : ?>
				<p class="homepage-widget-description"><?php echo $description; ?></p>
			<?php endif; ?>

			<div class="pricing-table-widget-<?php echo $widget_count; ?>">
				<?php dynamic_sidebar( 'widget-area-price-options' ); ?>
			</div>

		</div>

		<?php
		echo $after_widget;

		echo $content;

		$this->cache_widget( $args, $content );
	}
}