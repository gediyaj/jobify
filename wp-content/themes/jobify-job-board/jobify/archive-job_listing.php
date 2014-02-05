<?php
/**
 * Job Archives
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php echo apply_filters( 'jobify_job_archives_title', __( 'All Jobs', 'jobify' ) ); ?></h1>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">
			<div class="entry-content">
				<?php echo do_shortcode( apply_filters( 'jobify_job_archive_shortcode', '[jobs]' ) ); ?>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>