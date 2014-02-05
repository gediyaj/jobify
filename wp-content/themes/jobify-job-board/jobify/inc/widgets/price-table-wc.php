<?php
/**
 * Price Table for Restrict Content Pro
 *
 * Automatically populated with subscriptions.
 *
 * @since Jobify 1.0
 */
class Jobify_Widget_Price_Table_WC extends Jobify_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'jobify_widget_price_table_wc';
		$this->widget_description = __( 'Outputs Job Packages from WooCommerce', 'jobify' );
		$this->widget_id          = 'jobify_widget_price_table_wc';
		$this->widget_name        = __( 'WooCommerce Job Packages', 'jobify' );
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
		$packages     = get_posts( array(
			'post_type'  => 'product',
			'limit'      => -1,
			'tax_query'  => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'job_package'
				)
			)
		) );
		
		if ( ! $packages )
			return;
		
		$content = ob_get_clean();

		echo $before_widget;
		?>

		<div class="container">

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<?php if ( $description ) : ?>
				<p class="homepage-widget-description"><?php echo $description; ?></p>
			<?php endif; ?>

			<div class="pricing-table-widget-<?php echo count( $packages ); ?>">

				<?php foreach ( $packages as $key => $package ) : $product = get_product( $package ); ?>
					<div class="pricing-table-widget woocommerce">
						<div class="pricing-table-widget-title">
							<?php echo get_post_field( 'post_title', $package ); ?>
						</div>

						<div class="pricing-table-widget-description">
							<h2><?php echo $product->get_price_html(); ?></h2>

							<p><span class="rcp_level_duration">
								<?php
									printf( _n( '%d job', '%s jobs', $product->get_limit(), 'jobify' ) . ' ', $product->get_limit() );

									printf( _n( 'for %s day', 'for %s days', $product->get_duration(), 'jobify' ), $product->get_duration() );
								?>
							</span></p>

							<?php echo apply_filters( 'the_content', get_post_field( 'post_content', $product->id ) ); ?>

							<p>
								<?php
									$link 	= $product->add_to_cart_url();
									$label 	= apply_filters( 'add_to_cart_text', __( 'Add to cart', 'jobify' ) );
								?>
								<a href="<?php echo esc_url( $link ); ?>" class="button-secondary"><?php echo $label; ?></a>
							</p>
						</div>
					</div>
				<?php endforeach ?>

			</div>

		</div>

		<?php
		echo $after_widget;

		echo $content;

		$this->cache_widget( $args, $content );
	}
}