<?php
/**
 * Template Name: Map + Jobs
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="homepage-content" role="main">

			<?php
				the_widget(
					'Jobify_Widget_Map',
					array(
						'search' => 'off',
						'zoom'   => 'auto',
						'center' => null
					),
					array(
						'before_widget' => sprintf( '<section id="%1$s" class="homepage-widget %2$s">', 'jobify_widget_map', 'jobify_widget_map' ),
						'after_widget'  => '</section>',
						'before_title'  => '<h3 class="homepage-widget-title">',
						'after_title'   => '</h3>',
						'widget_id' => 'jobify_widget_map-999'
					)
				);
			?>

			<div class="entry-content">
				<?php echo do_shortcode( '[jobs]' ); ?>
			</div>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>