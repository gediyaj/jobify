<?php
/**
 * Archives
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<header class="page-header">
		<h1 class="page-title">
			<?php if ( is_day() ) : ?>
				<?php printf( __( 'Daily Archives: %s', 'jobify' ), '<span>' . get_the_date() . '</span>' ); ?>
			<?php elseif ( is_month() ) : ?>
				<?php printf( __( 'Monthly Archives: %s', 'jobify' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'jobify' ) ) . '</span>' ); ?>
			<?php elseif ( is_year() ) : ?>
				<?php printf( __( 'Yearly Archives: %s', 'jobify' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'jobify' ) ) . '</span>' ); ?>
			<?php elseif ( is_author() ) : ?>
				<?php the_post(); ?>
				<?php printf( __( 'Author: %s', 'jobify' ), '<span class="vcard">' . get_the_author() ); ?>
				<?php rewind_posts(); ?>
			<?php elseif ( is_tax() || is_category() || is_tag() ) : ?>
				<?php single_term_title(); ?>
			<?php else : ?>
				<?php _e( 'Blog Archives', 'jobify' ); ?>
			<?php endif; ?>
		</h1>
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