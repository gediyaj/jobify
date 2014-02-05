<?php
/**
 * Singular Price Option (to be used with Price Table)
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Price_Option extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_price_option';
		$this->widget_description = __( 'Create a price option for the pricing table.', 'jobify' );
		$this->widget_id          = 'jobify_widget_price_option';
		$this->widget_name        = __( 'Price Option', 'jobify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Basic Listing', 'jobify' ),
				'label' => __( 'Title:', 'jobify' )
			),
			'color' => array(
				'type'  => 'colorpicker',
				'std'   => '#01da90',
				'label' => __( 'Color:', 'jobify' )
			),
			'description' => array(
				'type'  => 'textarea',
				'rows'  => 8,
				'std'   => '',
				'label' => __( 'Description:', 'jobify' ),
			)
		);
		$this->control_ops = array(
			'width'  => 300
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

		$title       = $instance[ 'title' ];
		$color       = $instance[ 'color' ];
		$description = $instance[ 'description' ];
		
		$content = ob_get_clean();

		echo $before_widget;
		?>

		<div class="pricing-table-widget-title" style="background-color: <?php echo esc_attr( $color ); ?>">
			<?php echo esc_attr( $title ); ?>
		</div>

		<div class="pricing-table-widget-description">
			<?php echo wpautop( apply_filters( 'jobify_price_option_description', $description ) ); ?>
		</div>

		<?php
		echo $after_widget;

		echo $content;

		$this->cache_widget( $args, $content );
	}
}