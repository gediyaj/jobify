<?php
/**
 * Share Post/Page/Job
 *
 * @package Jobify
 * @since Jobify 1.0
 */

global $post;

$message = apply_filters( 'jobify_share_message', sprintf( _x( 'Check out %1$s on %2$s! %3$s', '1: Article title 2: Site Name 3: Site URL', 'jobify' ), urlencode( get_the_title() ), urlencode( get_bloginfo( 'name' ) ), esc_url_raw( get_permalink() ) ) );
?>
<div class="entry-share">
	<span class="entry-share-link">
		<div class="share-popup">
			<?php do_action( 'jobify_share_before', $message ); ?>
			<a target="_blank" href="<?php printf( 'http://www.twitter.com?status=%s', $message ); ?>"><i class="icon-twitter"></i></a>
			<a target="_blank" href="<?php printf( 'http://www.facebook.com/sharer.php?u=%s', $message ); ?>"><i class="icon-facebook"></i></a>
			<a target="_blank" href="https://plus.google.com/share?url=<?php the_permalink(); ?>"><i class="icon-gplus"></i></a>
			<a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&url=<?php the_permalink(); ?>&title=<?php the_title_attribute(); ?>&summary=<?php echo esc_attr( $post->post_excerpt ); ?>"><i class="icon-linkedin"></i></a>
			<?php do_action( 'jobify_share_after', $message ); ?>
		</div>

		<a href="#" class="open-share-popup"><i class="icon-share"></i> <?php _e( 'Share', 'jobify' ); ?></a>
	</span>

	<?php if ( function_exists( 'zilla_likes' ) ) : ?>
	<span class="entry-like">
		<?php zilla_likes(); ?>
	</span>
	<?php endif; ?>
</div>