<?php
/**
 * Featured Job
 *
 * @package Jobify
 * @since Jobify 1.0
 */
?>

<div class="single-job-spotlight">
	<div class="single-job-spotlight-feature-image">
		<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_post_thumbnail( 'content-job-featured' ); ?></a>
	</div>

	<div class="single-job-spotlight-content">
		<p><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></p>

		<?php the_excerpt(); ?>
	</div>

	<div class="single-job-spotlight-actions">
		<div class="action"><span class="job-location"><i class="icon-location"></i> <?php the_job_location(false); ?></span></div>
		<div class="action"><span class="job-type <?php echo get_the_job_type() ? sanitize_title( get_the_job_type()->slug ) : ''; ?>"><?php the_job_type(); ?></span></div>
	</div>
</div>