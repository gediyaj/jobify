<?php
/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package Jobify
 * @since Jobify 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<a href="<?php the_permalink(); ?>" rel="bookmark" class="featured-image"><span class="overlay"><i class="icon-plus"></i></span> <?php the_post_thumbnail( 'content-grid' ); ?></a>
		
		<h1 class="entry-title">
			<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
		</h1>

		<div class="entry-meta">
			<?php echo get_the_date(); ?>
			<?php if ( comments_open() ) : ?>
				<span class="comments-link">
					 | 
					<?php comments_popup_link( __( '0 Comments', 'jobify' ), __( '1 Comment', 'jobify' ), __( '% Comments', 'jobify' ) ); ?>
				</span><!-- .comments-link -->
			<?php endif; ?>
		</div>
	</header><!-- .entry-header -->

	<div class="entry">
		<div class="entry-summary">
			<?php the_excerpt(); ?>
		</div>
	</div>
</article><!-- #post -->