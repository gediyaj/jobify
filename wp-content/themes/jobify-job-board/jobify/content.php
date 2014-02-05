<?php
/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package Jobify
 * @since Jobify 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry">
		<?php if ( is_singular() && has_post_thumbnail() ) : ?>
			<div class="entry-feature">
				<?php the_post_thumbnail( 'fullsize' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( is_single() ) : ?>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php else : ?>
		<h1 class="entry-title">
			<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
		</h1>
		<?php endif; ?>

		<div class="entry-summary">
			<?php if ( is_singular() ) : ?>
				<?php the_content(); ?>

				<?php if ( is_singular() ) : ?>
				<?php the_tags( '<p class="entry-tags"><i class="icon-tag"></i> ' . __( 'Tags:', 'jobify' ) . ' ', ', ', '</p>' ); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'jobify' ) . '</span>', 'after' => '</div>', 'link_before' => '<span>', 'link_after' => '</span>' ) ); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php the_excerpt(); ?>

				<p><a href="<?php the_permalink(); ?>" rel="bookmark" class="button button-medium"><?php _e( 'Continue Reading', 'jobify' ); ?></a></p>
			<?php endif; ?>
		</div>
	</div>

	<header class="entry-header">
		<div class="entry-author">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 100 ); ?>
			<?php printf( __( 'Written by <a class="author-link" href="%s" rel="author">%s</a>', 'jobify' ), esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ), get_the_author() ); ?>
		</div>

		<div class="entry-meta">
			<?php echo get_the_date(); ?>
			<?php if ( comments_open() ) : ?>
				<span class="comments-link">
					 | 
					<?php comments_popup_link( __( '0 Comments', 'jobify' ), __( '1 Comment', 'jobify' ), __( '% Comments', 'jobify' ) ); ?>
				</span><!-- .comments-link -->
			<?php endif; ?>
			
			<?php get_template_part( 'content-share' ); ?>

		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->
</article><!-- #post -->