<?php
/**
 * Template Name: Jobify Homepage
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="homepage-content" role="main">

			<?php
				if ( ! dynamic_sidebar( 'widget-area-front-page' ) ) :
					global $wp_widget_factory;

					$widgets = apply_filters( 'jobify_campaign_default_widgets', array(
						
					) );

					foreach ( $widgets as $widget ) :
						$widget_obj = $wp_widget_factory->widgets[$widget];

						the_widget( $widget, array(), array(
							'before_widget' => sprintf( '<section id="%1$s" class="homepage-widget %2$s">', $widget_obj->id, $widget_obj->widget_options[ 'classname' ] ),
							'after_widget'  => '</section>',
							'before_title'  => '<h3 class="homepage-widget-title">',
							'after_title'   => '</h3>'
						) );
					endforeach;
				endif;
			?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>