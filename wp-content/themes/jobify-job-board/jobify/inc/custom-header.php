<?php
/**
 * Implements a custom header for Twenty Thirteen.
 * See http://codex.wordpress.org/Custom_Headers
 *
 * @package Jobify
 * @since Jobify 1.0
 */

/**
 * Sets up the WordPress core custom header arguments and settings.
 *
 * @uses add_theme_support() to register support for 3.4 and up.
 * @uses jobify_header_style() to style front-end.
 * @uses jobify_admin_header_style() to style wp-admin form.
 * @uses jobify_admin_header_image() to add custom markup to wp-admin form.
 * @uses register_default_headers() to set up the bundled header images.
 *
 * @since Jobify 1.0
 */
function jobify_custom_header_setup() {
	$args = array(
		// Text color and image (empty to use none).
		'default-text-color'     => 'ffffff',

		// Set height and width, with a maximum value for the width.
		'height'                 => 44,
		'width'                  => 200,
		'flex-width'             => true,
		'flex-height'            => true,

		// Callbacks for styling the header and the admin preview.
		'wp-head-callback'       => 'jobify_header_style',
		'admin-head-callback'    => 'jobify_admin_header_style',
		'admin-preview-callback' => 'jobify_admin_header_image',
	);

	add_theme_support( 'custom-header', $args );

	add_action( 'admin_print_styles-appearance_page_custom-header', 'jobify_fonts' );
}
add_action( 'after_setup_theme', 'jobify_custom_header_setup' );

/**
 * Styles the header text displayed on the blog.
 *
 * get_header_textcolor() options: Hide text (returns 'blank'), or any hex value.
 *
 * @since Jobify 1.0
 */
function jobify_header_style() {
	$header_image = get_header_image();
	$text_color   = get_header_textcolor();

	if ( 'blank' == $text_color )
		$text_color = 'fff';
?>
	<style type="text/css">
	<?php if ( ! display_header_text() ) : ?>
	.site-title span {
		position: absolute;
		clip: rect(1px, 1px, 1px, 1px);
	}
	<?php endif; ?>
	.site-branding,
	.site-description,
	.nav-menu-primary ul li a,
	.nav-menu-primary li a,
	.primary-menu-toggle i,
	.site-primary-navigation .primary-menu-toggle,
	.site-primary-navigation #searchform input[type="text"] {
		color: #<?php echo esc_attr( $text_color ); ?>;
	}

	.nav-menu-primary li.login > a {
		border-color: #<?php echo esc_attr( $text_color ); ?>;	
	}

	.site-primary-navigation:not(.open) li.login > a:hover {
		color: <?php echo jobify_theme_mod( 'colors', 'primary' ); ?>;
		background: #<?php echo esc_attr( $text_color ); ?>;
	}
	</style>
	<?php
}

/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 *
 * @since Jobify 1.0
 */
function jobify_admin_header_style() {
	$header_image = get_header_image();
	$text_color   = get_header_textcolor();
?>
	<style>
	.site-header {
		background: <?php echo jobify_theme_mod( 'colors', 'primary' ); ?>;
		overflow: hidden;
	}

	.site-branding,
	.site-description {
		color: #<?php echo esc_attr( $text_color ); ?>;
	}

	.site-header {
		padding: 35px;
		box-shadow: inset rgba(0, 0, 0, .10) 0 -4px 0;
	}

	.site-branding {
		float: left;
	}

	.site-primary-navigation {
		float: right;
	}

	/**
	 * Branding
	 */
	.site-branding {
		text-decoration: none;
	}

	.site-branding:hover {
		text-decoration: none;
	}

	.site-title {
		font: bold 36px/normal 'Montserrat', sans-serif;
		text-transform: uppercase;
		margin: 0;
		padding: 0;
	}

	.site-title span,
	.site-title img {
		float: left;
	}

	<?php if ( ! display_header_text() ) : ?>
	.site-branding span {
		position: absolute !important;
		clip: rect(1px 1px 1px 1px); /* IE7 */
		clip: rect(1px, 1px, 1px, 1px);
	}
	<?php endif; ?>
	</style>
<?php
}

/**
 * Outputs markup to be displayed on the Appearance > Header admin panel.
 * This callback overrides the default markup displayed there.
 *
 * @since Jobify 1.0
 */
function jobify_admin_header_image() {
	$header_image = get_header_image();
?>
	<header id="masthead" class="site-header" role="banner">
		<div class="container">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="site-branding">
				<?php $header_image = get_header_image(); ?>
				<h1 class="site-title">
					<?php if ( ! empty( $header_image ) ) : ?>
						<img src="<?php echo $header_image ?>" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" />
					<?php endif; ?>

					<span><?php bloginfo( 'name' ); ?></span>
				</h1>
			</a>
		</div>
	</header><!-- #masthead -->
<?php }
