<?php
/**
 * Single Post
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

	<div id="primary" class="content-area">
		<?php if ( is_singular( 'job_listing' ) ) : ?>

			<?php get_template_part( 'content-single', 'job' ); ?>

		<?php elseif ( is_singular( 'resume' ) ) : ?>

			<?php get_template_part( 'content-single', 'the-resume' ); ?>

		<?php else : ?>

			<div id="content" class="site-content full" role="main">
				<div class="blog-archive">
					<?php get_template_part( 'content', get_post_format() ); ?>
					<?php comments_template(); ?>
				</div>
			</div><!-- #content -->

		<?php endif; ?>

		<?php do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->

	<?php endwhile; ?>

<?php get_footer(); ?>