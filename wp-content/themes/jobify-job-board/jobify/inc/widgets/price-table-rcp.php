<?php
/**
 * Price Table for Restrict Content Pro
 *
 * Automatically populated with subscriptions.
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Price_Table_RCP extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_price_table_rcp';
		$this->widget_description = __( 'Outputs subscription options for Restrict Content Pro', 'jobify' );
		$this->widget_id          = 'jobify_widget_price_table_rcp';
		$this->widget_name        = __( 'RCP Price Table', 'jobify' );
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
		$levels       = rcp_get_subscription_levels( 'active' );
		
		if ( ! $levels )
			return;
		
		$content = ob_get_clean();

		echo $before_widget;
		?>

		<div class="container">

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<?php if ( $description ) : ?>
				<p class="homepage-widget-description"><?php echo $description; ?></p>
			<?php endif; ?>

			<div class="pricing-table-widget-<?php echo count( $levels ); ?>">
				<?php foreach( $levels as $key => $level ) : ?>
					<?php if( rcp_show_subscription_level( $level->id ) ) : ?>
					<div id="rcp_subscription_level_<?php echo $level->id; ?>" class="pricing-table-widget rcp_subscription_level_fake" data-href="<?php echo esc_url( get_permalink( jobify_find_page_with_shortcode( array( 'register_form' ) ) ) ); ?>">
						<div class="pricing-table-widget-title" style="background-color: #01da90">
							<span class="rcp_subscription_level_name"><?php echo stripslashes( $level->name ); ?></span>
						</div>

						<div class="pricing-table-widget-description">
							<h2><span class="rcp_price" rel="<?php echo esc_attr( $level->price ); ?>"><?php echo $level->price > 0 ? rcp_currency_filter( $level->price ) : __( 'free', 'jobify' ); ?></h2>

							<p><span class="rcp_level_duration"><?php echo $level->duration > 0 ? $level->duration . '&nbsp;' . rcp_filter_duration_unit( $level->duration_unit, $level->duration ) : __( 'unlimited', 'jobify' ); ?></span></p>

							<?php echo wpautop( wp_kses( $level->description, rcp_allowed_html_tags() ) ); ?>
						</div>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

		</div>

		<?php
		echo $after_widget;

		echo $content;

		$this->cache_widget( $args, $content );
	}
}

function jobify_rcp_filter_currency() {
	global $rcp_options;
	
	$currency = isset( $rcp_options[ 'currency' ] ) ? $rcp_options[ 'currency' ] : 'USD';
	$currency = strtolower( $currency );

	add_filter( 'rcp_' . $currency . '_currency_filter_after', 'jobify_rcp_sup_sign_after', 10, 4 );
	add_filter( 'rcp_' . $currency . '_currency_filter_before', 'jobify_rcp_sup_sign_before', 10, 4 );
}
//add_action( 'init', 'jobify_rcp_filter_currency' );

function jobify_rcp_sup_sign_before( $formatted, $currency, $symbol, $price ) {
	return '<sup>' . $symbol . '</sup>' . $price;
}

function jobify_rcp_sup_sign_after( $formatted, $currency, $symbol, $price ) {
	return $price . ' ' . '<sup>' . $symbol . '</sup>';
}