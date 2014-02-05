<?php
/**
 * Resumes
 *
 * @package Jobify
 * @since Jobify 1.0
 */

get_header(); ?>

	<header class="page-header">
		<h1 class="page-title"><?php echo apply_filters( 'jobify_resume_archives_title', __( 'All Rèsumès', 'jobify' ) ); ?></h1>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">
			<div class="entry-content">
				<?php echo do_shortcode( apply_filters( 'jobify_resume_archive_shortcode', '[resumes]' ) ); ?>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>