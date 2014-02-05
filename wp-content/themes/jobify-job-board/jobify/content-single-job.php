<?php
/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package Jobify
 * @since Jobify 1.0
 */
?>

<div class="page-header">
	<h1 class="page-title"><?php the_title(); ?></h1>
	<h2 class="page-subtitle">
		<ul>
			<?php do_action( 'single_job_listing_meta_start' ); ?>

			<li class="job-type <?php echo get_the_job_type() ? sanitize_title( get_the_job_type()->slug ) : ''; ?>"><?php the_job_type(); ?></li>
			<li class="job-company"><?php the_company_name(); ?></li>
			<li class="job-location"><i class="icon-location"></i> <?php the_job_location(false); ?></li>
			<li class="job-date-posted"><i class="icon-calendar"></i> <?php printf( __( 'Posted <date>%s</date> ago', 'jobify' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></li>

			<?php do_action( 'single_job_listing_meta_end' ); ?>
		</ul>
	</h2>
</div>

<div id="content" class="site-content full" role="main">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry-content">
			<?php the_content(); ?>

			<?php get_template_part( 'content-single-job', 'related' ); ?>
		</div>
	</article><!-- #post -->
</div>