<div class="page-header">
	<h1 class="page-title"><?php the_title(); ?></h1>
	<h2 class="page-subtitle">
		<ul>
			<?php do_action( 'single_resume_meta_start' ); ?>

			<li class="job-title"><?php the_candidate_title(); ?></li>
			<li class="location"><i class="icon-location"></i> <?php the_candidate_location( false); ?></li>
			<li class="date-posted" itemprop="datePosted"><i class="icon-calendar"></i> <date><?php printf( __( 'Updated %s ago', 'resume_manager' ), human_time_diff( get_the_modified_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date></li>

			<?php do_action( 'single_resume_meta_end' ); ?>
		</ul>
	</h2>
</div>

<div id="content" class="site-content full" role="main">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</article><!-- #post -->
</div>