<?php
/**
 * Template Name: Pricing
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php the_title(); ?></h1>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">
			<div class="page-content">
				<?php the_content(); ?>
			</div>

			<?php 
				do_action( 'jobify_pricing_page_before' );

				if ( defined( 'RCP_PLUGIN_VERSION' ) )
					$widget = 'Jobify_Widget_Price_Table_RCP';
				else if ( class_exists( 'WooCommerce' ) )
					$widget = 'Jobify_Widget_Price_Table_WC';
				else
					$widget = 'Jobify_Widget_Price_Table';

				the_widget( 
					$widget, 
					array(
						'title'       => null,
						'description' => null
					),
					array(
						'widget_id'     => 'widget-area-front-page',
						'before_widget' => '<section id="%1$s" class="homepage-widget %2$s">',
						'after_widget'  => '</section>',
						'before_title'  => '<h3 class="homepage-widget-title">',
						'after_title'   => '</h3>',
					) 
				); 

				do_action( 'jobify_pricing_page_after' );
			?>
		</div><!-- #content -->

		<?php do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->

	<?php endwhile; ?>

<?php get_footer(); ?>