<?php
/**
 * Resume Category
 *
 * @package Jobify
 * @since Jobify 1.0
 */

$taxonomy = get_taxonomy( get_queried_object()->taxonomy );

get_header(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php single_term_title(); ?></h1>

		<?php if( $taxonomy ) : ?>
			<h2 class="page-subtitle"><?php echo esc_attr( $taxonomy->labels->singular_name ); ?></h2>
		<?php endif; ?>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">
			<div class="entry-content">

				<?php if ( have_posts() ) : ?>
				<div class="resume_listings">
					<ul class="resumes">
						<?php while ( have_posts() ) : the_post(); ?>
							<?php get_job_manager_template_part( 'content', 'resume', 'resume_manager', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>
						<?php endwhile; ?>
					</ul>
				</div>
				<?php else : ?>
					<?php get_template_part( 'content', 'none' ); ?>
				<?php endif; ?>

			</div>
		</div><!-- #content -->

		<?php do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->

<?php get_footer(); ?>