<?php
/*
Template Name: List Job
 */

get_header();?>

<style>
.page-header{display:none;}
.footer-cta{display:none;}
.site-footer{display:none;}
.site-header{display:none;}
.search_jobs{display:none;}
.job_types{display:none;}
.showing_jobs{display:none !important;}
.job_listings{margin-bottom:20px;}
</style>
<header class="page-header">
		<h1 class="page-title"><?php echo get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : _x( 'Blog', 'blog page title', 'jobify' ); ?></h1>
	</header>

	<div id="primary" class="content-area">
		<div id="content" class="site-content full" role="main">

			<div class="blog-archive">
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php the_content();?>
					<?php endwhile; ?>
				
				<?php endif; ?>
			</div>
			
		</div><!-- #content -->

		<?php //do_action( 'jobify_loop_after' ); ?>
	</div><!-- #primary -->
<?php get_footer(); ?>