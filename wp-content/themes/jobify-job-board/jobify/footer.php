<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package Jobify
 * @since Jobify 1.0
 */
?>

		</div><!-- #main -->

		<?php if ( jobify_theme_mod( 'jobify_cta', 'jobify_cta_display' ) ) : ?>
		<div class="footer-cta">
			<div class="container">
				<?php echo wpautop( jobify_theme_mod( 'jobify_cta', 'jobify_cta_text' ) ); ?>
			</div>
		</div>
		<?php endif; ?>

		<footer id="colophon" class="site-footer" role="contentinfo">
			<?php if ( is_active_sidebar( 'widget-area-footer' ) ) : ?>
			<div class="footer-widgets">
				<div class="container">
					<?php dynamic_sidebar( 'widget-area-footer' ); ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="copyright">
				<div class="container">
					<div class="site-info">
						<?php echo apply_filters( 'jobify_footer_copyright', sprintf( __( '&copy; %1$s %2$s &mdash; All Rights Reserved', 'jobify' ), date( 'Y' ), get_bloginfo( 'name' ) ) ); ?>
					</div><!-- .site-info -->

					<a href="#top" class="btt"><i class="icon-up-circled"></i></a>

					<?php
						if ( has_nav_menu( 'footer-social' ) ) :
							$social = wp_nav_menu( array(
								'theme_location'  => 'footer-social',
								'container_class' => 'footer-social',
								'items_wrap'      => '%3$s',
								'depth'           => 1,
								'echo'            => false,
								'link_before'     => '<span class="screen-reader-text">',
								'link_after'      => '</span>',
							) );

							echo strip_tags( $social, '<a><div><span>' );
						endif;
					?>
				</div>
			</div>
		</footer><!-- #colophon -->
	</div><!-- #page -->

	<?php wp_footer(); ?>
</body>
</html>