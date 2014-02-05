<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme and one of the
 * two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php echo get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : _x( 'Blog', 'blog page title', 'jobify' ); ?></h1>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">

			<div class="blog-archive">
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'content', get_post_format() ); ?>
					<?php endwhile; ?>
				<?php else : ?>
					<?php get_template_part( 'content', 'none' ); ?>
				<?php endif; ?>
			</div>
			
		</div><!-- #content -->

		<?php do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->

<?php get_footer(); ?>