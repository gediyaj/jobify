<?php
/**
 * Callout
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Callout extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_callout';
		$this->widget_description = __( 'Display call-out area with a bit of text and a button.', 'jobify' );
		$this->widget_id          = 'jobify_widget_callout';
		$this->widget_name        = __( 'Callout', 'jobify' );
		$this->settings           = array(
			'description' => array(
				'type'  => 'textarea',
				'rows'  => 4,
				'std'   => null,
				'label' => __( 'Description:', 'jobify' ),
			),
			'title' => array(
				'type'  => 'text',
				'std'   => 'Learn More',
				'label' => __( 'Button Label:', 'jobify' )
			),
			'button-url' => array(
				'type'  => 'text',
				'std'   => null,
				'label' => __( 'Button URL:', 'jobify' )
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

		$button_label = isset ( $instance[ 'title' ] ) ? $instance[ 'title' ] : null;
		$button_url   = $instance[ 'button-url' ];
		$description  = $instance[ 'description' ];
		
		echo $before_widget;
		?>
		
			<div class="callout container">
				<div class="callout-description">
					<?php echo wpautop( $description ); ?>
				</div>

				<?php if ( '' != $button_label ) : ?>
				<div class="callout-action">
					<a href="<?php echo esc_url( $button_url ); ?>" class="button"><?php echo esc_attr( $button_label ); ?></a>
				</div>
				<?php endif; ?>
			</div>

		<?php
		echo $after_widget;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}