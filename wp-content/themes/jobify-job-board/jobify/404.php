<?php
/**
 * 404
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<header class="page-header">
		<h1 class="page-title">
			<?php _e( 'Page Not Found', 'jobify' ); ?>
		</h1>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">

			<div class="blog-archive">
				<?php get_template_part( 'content', 'none' ); ?>
			</div>
			
		</div><!-- #content -->

		<?php do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->

<?php get_footer(); ?>