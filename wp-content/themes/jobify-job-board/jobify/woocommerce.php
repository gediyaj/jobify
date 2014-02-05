<?php
/**
 * WooCommerce Job Packages
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Jobify
 * @since Jobify 1.0
 */

global $wp_query;

$packages = new WP_Query( array(
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

get_header(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php _e( 'Job Packages', 'jobify' ); ?></h1>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">

			<div class="job-packages pricing-table-widget-<?php echo $packages->found_posts; ?>">

				<?php while ( $packages->have_posts() ) : $packages->the_post(); $package = get_product( get_post()->ID ); ?>
					<div class="pricing-table-widget woocommerce">
						<div class="pricing-table-widget-title">
							<?php the_title(); ?>
						</div>

						<div class="pricing-table-widget-description">
							<h2><?php echo $package->get_price_html(); ?></h2>

							<p><span class="rcp_level_duration">
								<?php
									printf( _n( '%d job', '%s jobs', $package->get_limit(), 'jobify' ) . ' ', $package->get_limit() );

									printf( _n( 'for %s day', 'for %s days', $package->get_duration(), 'jobify' ), $package->get_duration() );
								?>
							</span></p>

							<?php the_content(); ?>

							<p>
								<?php
									$link 	= $package->add_to_cart_url();
									$label 	= apply_filters( 'add_to_cart_text', __( 'Add to cart', 'jobify' ) );
								?>
								<a href="<?php echo esc_url( $link ); ?>" class="button-secondary"><?php echo $label; ?></a>
							</p>
						</div>
					</div>
				<?php endwhile; ?>

			</div>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>