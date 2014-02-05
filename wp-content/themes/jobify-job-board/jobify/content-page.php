<?php
/**
 * Single content
 *
 * @package Jobify
 * @since Jobify 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content<?php echo has_shortcode( $post->post_content, 'jobs' ) ? ' has-jobs' : null; ?>">
		<?php the_content(); ?>
	</div>
</article><!-- #post -->