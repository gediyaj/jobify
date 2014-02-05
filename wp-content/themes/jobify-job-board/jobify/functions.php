<?php
/**
 * Jobify functions and definitions.
 *
 * Sets up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * see http://codex.wordpress.org/Plugin_API
 *
 * @package WordPress
 * @subpackage Jobify
 * @since Jobify 1.0
 */

/**
 * Sets up the content width value based on the theme's design.
 * @see jobify_content_width() for template-specific adjustments.
 */
if ( ! isset( $content_width ) )
	$content_width = 680;

/**
 * Plugin Notice
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_features_notice() {
	$plugins = array(
		sprintf( '<a href="%s">WP Job Manager</a>', wp_nonce_url( network_admin_url( 'update.php?action=install-plugin&plugin=wp-job-manager' ), 'install-plugin_wp-job-manager' ) ),
		sprintf( '<a href="%s">Testimonials by WooThemes</a>', wp_nonce_url( network_admin_url( 'update.php?action=install-plugin&plugin=testimonials-by-woothemes' ), 'install-plugin_testimonials-by-woothemes' ) ),
		sprintf( '<a href="%s">Soliloquy Lite</a>', wp_nonce_url( network_admin_url( 'update.php?action=install-plugin&plugin=soliloquy-lite' ), 'install-plugin_soliloquy-lite' ) )
	);
?>
	<div class="updated">
		<p><?php printf(
					__( '<strong>Notice:</strong> To take advantage of all of the great features Jobify offers, please install the %s. <a href="%s" class="alignright">Hide this message.</a>', 'jobify' ),
					implode( ', ', $plugins ),
					wp_nonce_url( add_query_arg( array( 'action' => 'jobify-hide-plugin-notice' ), admin_url( 'index.php' ) ), 'jobify-hide-plugin-notice' )
			); ?></p>
	</div>
<?php
}
if ( ! get_user_meta( get_current_user_id(), 'jobify-hide-plugin-notice', true ) )
	add_action( 'admin_notices', 'jobify_features_notice' );

/**
 * Hide plugin notice.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_hide_plugin_notice() {
	check_admin_referer( 'jobify-hide-plugin-notice' );

	$user_id = get_current_user_id();

	add_user_meta( $user_id, 'jobify-hide-plugin-notice', 1 );
}
if ( is_admin() )
	add_action( 'admin_action_jobify-hide-plugin-notice', 'jobify_hide_plugin_notice' );

/**
 * Sets up theme defaults and registers the various WordPress features that
 * Jobify supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for automatic feed links, post
 * formats, admin bar, and post thumbnails.
 * @uses register_nav_menu() To add support for a navigation menu.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_setup() {
	/*
	 * Makes Jobify available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Jobify, use a find and
	 * replace to change 'jobify' to the name of your theme in all
	 * template files.
	 */
	load_theme_textdomain( 'jobify', get_template_directory() . '/languages' );

	// Editor style
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// Add support for custom background
	add_theme_support( 'custom-background', array(
		'default-color'    => '#ffffff'
	) );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary'       => __( 'Navigation Menu', 'jobify' ),
		'footer-social' => __( 'Footer Social', 'jobify' )
	) );

	add_theme_support( 'job-manager-templates' );
	add_theme_support( 'resume-manager-templates' );

	/** Shortcodes */
	add_filter( 'widget_text', 'do_shortcode' );

	/*
	 * This theme uses a custom image size for featured images, displayed on
	 * "standard" posts and pages.
	 */
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'content-grid', 400, 200, true );
	add_image_size( 'content-job-featured', 450, 175, true );

	/**
	 * WooCommerce
	 */
	add_theme_support( 'woocommerce' );

	/**
	 * Misc
	 */
	add_filter( 'excerpt_more', '__return_false' );
}
add_action( 'after_setup_theme', 'jobify_setup' );

/**
 * Returns the Google font stylesheet URL, if available.
 *
 * The use of Source Sans Pro and Bitter by default is localized. For languages
 * that use characters not supported by the font, the font can be disabled.
 *
 * @since Jobify 1.0
 *
 * @return string Font stylesheet or empty string if disabled.
 */
function jobify_fonts_url() {
	$fonts_url = '';

	/* Translators: If there are characters in your language that are not
	 * supported by Montserrat, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$montserrat = _x( 'on', 'Montserrat font: on or off', 'jobify' );

	/* Translators: If there are characters in your language that are not
	 * supported by Varela Round, translate this to 'off'. Do not translate into your
	 * own language.
	 */
	$varela = _x( 'on', 'Varela Round font: on or off', 'jobify' );

	if ( 'off' !== $montserrat || 'off' !== $varela ) {
		$font_families = array();

		if ( 'off' !== $montserrat )
			$font_families[] = 'Montserrat:400,700';

		if ( 'off' !== $varela )
			$font_families[] = 'Varela+Round';

		$protocol = is_ssl() ? 'https' : 'http';
		$query_args = array(
			'family' => implode( '|', $font_families ),
			'subset' => 'latin',
		);
		$fonts_url = add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" );
	}

	return $fonts_url;
}

/**
 * Adds additional stylesheets to the TinyMCE editor if needed.
 *
 * @uses jobify_fonts_url() to get the Google Font stylesheet URL.
 *
 * @since Jobify 1.0
 *
 * @param string $mce_css CSS path to load in TinyMCE.
 * @return string
 */
function jobify_mce_css( $mce_css ) {
	$fonts_url = jobify_fonts_url();

	if ( empty( $fonts_url ) )
		return $mce_css;

	if ( ! empty( $mce_css ) )
		$mce_css .= ',';

	$mce_css .= esc_url_raw( str_replace( ',', '%2C', $fonts_url ) );

	return $mce_css;
}
add_filter( 'mce_css', 'jobify_mce_css' );

/**
 * Loads our special font CSS file.
 *
 * To disable in a child theme, use wp_dequeue_style()
 * function mytheme_dequeue_fonts() {
 *     wp_dequeue_style( 'jobify-fonts' );
 * }
 * add_action( 'wp_enqueue_scripts', 'mytheme_dequeue_fonts', 11 );
 *
 * Also used in the Appearance > Header admin panel:
 * @see twentythirteen_custom_header_setup()
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_fonts() {
	$fonts_url = jobify_fonts_url();

	if ( ! empty( $fonts_url ) )
		wp_enqueue_style( 'jobify-fonts', esc_url_raw( $fonts_url ), array(), null );
}
add_action( 'wp_enqueue_scripts', 'jobify_fonts' );

/**
 * Enqueues scripts and styles for front end.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_scripts_styles() {
	global $wp_styles, $edd_options;

	/*
	 * Adds JavaScript to pages with the comment form to support sites with
	 * threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	wp_deregister_script( 'wp-job-manager-job-application' );

	$deps = array( 'jquery' );

	if ( class_exists( 'WooCommerce' ) )
		$deps[] = 'woocommerce';

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'magnific-popup', get_template_directory_uri() . '/js/jquery.magnific-popup.min.js' );
	wp_enqueue_script( 'waypoints', get_template_directory_uri() . '/js/waypoints.min.js' );
	wp_enqueue_script( 'jobify', get_template_directory_uri() . '/js/jobify.js', $deps, 20130718 );

	/**
	 * Localize/Send data to our script.
	 */
	$jobify_settings = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'i18n'    => array(

		),
		'pages'   => array(
			'is_widget_home'  => is_page_template( 'page-templates/jobify.php' ),
			'is_job'          => is_singular( 'job_listing' ),
			'is_resume'       => is_singular( 'resume' ),
			'is_testimonials' => is_page_template( 'page-templates/testimonials.php' ) || is_post_type_archive( 'testimonial' )
		),
		'widgets' => array()
	);

	foreach ( jobify_homepage_widgets() as $widget ) {
		$options = get_option( 'widget_' . $widget[ 'classname' ] );

		if ( ! isset( $widget[ 'callback' ][0] ) )
			continue;

		$options = $options[ $widget[ 'callback' ][0]->number ];

		$jobify_settings[ 'widgets' ][ $widget[ 'classname' ] ] = array(
			'animate' => isset ( $options[ 'animations' ] ) && 1 == $options[ 'animations' ] ? 1 : 0
		);
	}

	wp_localize_script( 'jobify', 'jobifySettings', $jobify_settings );

	/** Styles */
	wp_enqueue_style( 'animate', get_template_directory_uri() . '/css/animate.css' );
	wp_enqueue_style( 'entypo', get_template_directory_uri() . '/css/entypo.css' );
	wp_enqueue_style( 'magnific-popup', get_template_directory_uri() . '/css/magnific-popup.css' );
	wp_enqueue_style( 'jobify', get_stylesheet_uri(), array( 'entypo' ), 20130814 );

	wp_dequeue_style( 'wp-job-manager-frontend' );
	wp_dequeue_style( 'wp-job-manager-resume-frontend' );

	if ( jobify_theme_mod( 'jobify_general', 'responsive' ) )
		wp_enqueue_style( 'jobify-responsive', get_template_directory_uri() . '/css/responsive.css', array( 'jobify' ) );
}
add_action( 'wp_enqueue_scripts', 'jobify_scripts_styles' );

/**
 * Get all widgets used on the home page.
 *
 * @since Jobify 1.0
 *
 * @return array $_widgets An array of active widgets
 */
function jobify_homepage_widgets() {
	global $wp_registered_sidebars, $wp_registered_widgets;

	$index            = 'widget-area-front-page';
	$sidebars_widgets = wp_get_sidebars_widgets();
	$_widgets         = array();

	if ( empty( $sidebars_widgets ) || empty($wp_registered_sidebars[$index]) || !array_key_exists($index, $sidebars_widgets) || !is_array($sidebars_widgets[$index]) || empty($sidebars_widgets[$index]) )
		return $_widgets;

	foreach ( (array) $sidebars_widgets[$index] as $id ) {
		$_widgets[] = isset( $wp_registered_widgets[$id] ) ? $wp_registered_widgets[$id] : null;
	}

	return $_widgets;
}

/**
 * Adjust page when responsive is off to normal scale.
 *
 * @since Jobify 1.1
 */
function jobify_nonresponsive_viewport() {
	if ( ! jobify_theme_mod( 'jobify_general', 'responsive' ) )
		return;

	echo '<meta name="viewport" content="initial-scale=1">';
}
add_action( 'wp_head', 'jobify_nonresponsive_viewport' );

/**
 * Creates a nicely formatted and more specific title element text for output
 * in head of document, based on current view.
 *
 * @since Jobify 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function jobify_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'jobify' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'jobify_wp_title', 10, 2 );

/**
 * Registers widgets, and widget areas.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_widgets_init() {
	register_widget( 'Jobify_Widget_Callout' );
	register_widget( 'Jobify_Widget_Video' );
	register_widget( 'Jobify_Widget_Blog_Posts' );

	if ( class_exists( 'WP_Job_Manager' ) ) {
		unregister_widget( 'WP_Job_Manager_Widget_Recent_Jobs' );

		register_widget( 'Jobify_Widget_Jobs' );
		register_widget( 'Jobify_Widget_Stats' );
		register_widget( 'Jobify_Widget_Map' );
	}

	if ( class_exists( 'Woothemes_Testimonials' ) ) {
		register_widget( 'Jobify_Widget_Companies' );
		register_widget( 'Jobify_Widget_Testimonials' );
	}

	if ( function_exists( 'soliloquy_slider' ) ) {
		register_widget( 'Jobify_Widget_Slider' );
		register_widget( 'Jobify_Widget_Slider_Hero' );
	}

	if ( defined( 'RCP_PLUGIN_VERSION' ) ) {
		register_widget( 'Jobify_Widget_Price_Table_RCP' );
	} else if ( class_exists( 'WooCommerce' ) ) {
		register_widget( 'Jobify_Widget_Price_Table_WC' );
	} else {
		register_widget( 'Jobify_Widget_Price_Table' );
		register_widget( 'Jobify_Widget_Price_Option' );
	}

	register_sidebar( array(
		'name'          => __( 'Homepage Widget Area', 'jobify' ),
		'id'            => 'widget-area-front-page',
		'description'   => __( 'Choose what should display on the custom static homepage.', 'jobify' ),
		'before_widget' => '<section id="%1$s" class="homepage-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="homepage-widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Widget Area', 'jobify' ),
		'id'            => 'widget-area-footer',
		'description'   => __( 'Display columns of widgets in the footer.', 'jobify' ),
		'before_widget' => '<aside id="%1$s" class="footer-widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="footer-widget-title">',
		'after_title'   => '</h3>',
	) );

	if ( ! ( defined( 'RCP_PLUGIN_VERSION' ) || class_exists( 'WooCommerce' ) ) ) {
		register_sidebar( array(
			'name'          => __( 'Price Table', 'jobify' ),
			'id'            => 'widget-area-price-options',
			'description'   => __( 'Drag multiple "Price Option" widgets here. Then drag the "Pricing Table" widget to the "Homepage Widget Area".', 'jobify' ),
			'before_widget' => '<div id="%1$s" class="pricing-table-widget %2$s">',
			'after_widget'  => '</div>'
		) );
	}
}
add_action( 'widgets_init', 'jobify_widgets_init' );

/**
 * Extends the default WordPress body class to denote:
 * 1. Custom fonts enabled.
 *
 * @since Jobify 1.0
 *
 * @param array $classes Existing class values.
 * @return array Filtered class values.
 */
function jobify_body_class( $classes ) {
	if ( wp_style_is( 'jobify-fonts', 'queue' ) )
		$classes[] = 'custom-font';

	if ( get_option( 'job_manager_enable_categories' ) )
		$classes[] = 'wp-job-manager-categories';

	if ( class_exists( 'WP_Job_Manager_Job_Tags' ) )
		$classes[] = 'wp-job-manager-tags';

	return $classes;
}
add_filter( 'body_class', 'jobify_body_class' );

/**
 * Extends the default WordPress comment class to add 'no-avatars' class
 * if avatars are disabled in discussion settings.
 *
 * @since Jobify 1.0
 *
 * @param array $classes Existing class values.
 * @return array Filtered class values.
 */
function jobify_comment_class( $classes ) {
	if ( ! get_option( 'show_avatars' ) )
		$classes[] = 'no-avatars';

	return $classes;
}
add_filter( 'comment_class', 'jobify_comment_class' );

/**
 * Adds a class to menu items that have children elements
 * so that they can be styled
 *
 * @since Jobify 1.0
 */
function jobify_add_menu_parent_class( $items ) {
	$parents = array();

	foreach ( $items as $item ) {
		if ( $item->menu_item_parent && $item->menu_item_parent > 0 ) {
			$parents[] = $item->menu_item_parent;
		}
	}

	foreach ( $items as $item ) {
		if ( in_array( $item->ID, $parents ) ) {
			$item->classes[] = 'has-children';
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'jobify_add_menu_parent_class' );

/**
 * Append modal boxes to the bottom of the the page that
 * will pop up when certain links are clicked.
 *
 * Login/Register pages must be set in EDD settings for this to work.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_inline_modals() {
	if ( ! jobify_is_job_board() )
		return;

	$login = jobify_find_page_with_shortcode( array( 'jobify_login_form', 'login_form' ) );

	if ( 0 != $login )
		get_template_part( 'modal', 'login' );

	$register = jobify_find_page_with_shortcode( array( 'jobify_register_form', 'register_form' ) );

	if ( 0 != $register )
		get_template_part( 'modal', 'register' );
}
add_action( 'wp_footer', 'jobify_inline_modals' );

/**
 * If the menu item has a custom class, that means it is probably
 * going to be triggering a modal. The ID will be used to determine
 * the inline content to be displayed, so we need it to provide context.
 * This uses the specificed class name instead of `menu-item-x`
 *
 * @since Jobify 1.0
 *
 * @param string $id The ID of the current menu item
 * @param object $item The current menu item
 * @param array $args Arguments
 * @return string $id The modified menu item ID
 */
function jobify_nav_menu_item_id( $id, $item, $args ) {
	if ( ! empty( $item->classes[0] ) ) {
		return current($item->classes) . '-modal';
	}

	return $id;
}
add_filter( 'nav_menu_item_id', 'jobify_nav_menu_item_id', 10, 3 );

/**
 * Object meta helper.
 *
 * @since Jobify 1.0
 *
 * @param string $key The meta key to get.
 * @param int $post_id The post ID to pull the meta from.
 * @return mixed The found post meta
 */
function jobify_item_meta( $key, $post_id = null ) {
	global $post;

	if ( is_null( $post_id ) && is_object( $post ) )
		$post_id = $post->ID;

	$meta = get_post_meta( $post_id, $key, true );

	if ( $meta )
		return apply_filters( 'jobify_meta_' . $key, $meta );

	return false;
}

/**
 * Pagination
 *
 * After the loop, attach pagination to the current query.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_pagination() {
	global $wp_query;

	$big = 999999999; // need an unlikely integer

	$links = paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var('paged') ),
		'total' => $wp_query->max_num_pages,
		'prev_text' => '<i class="icon-left-open-big"></i>',
		'next_text' => '<i class="icon-right-open-big"></i>'
	) );
?>
	<div class="paginate-links container">
		<?php echo $links; ?>
	</div>
<?php
}
add_action( 'jobify_loop_after', 'jobify_pagination' );

if ( ! function_exists( 'jobify_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments
 * template simply create your own twentythirteen_comment(), and that function
 * will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Thirteen 1.0
 *
 * @param object $comment Comment to display.
 * @param array $args Optional args.
 * @param int $depth Depth of comment.
 * @return void
 */
function jobify_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
		<p><?php _e( 'Pingback:', 'jobify' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'jobify' ), '<span class="ping-meta"><span class="edit-link">', '</span></span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
	?>
	<li id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
			<div class="comment-avatar">
				<?php echo get_avatar( $comment, 75 ); ?>
			</div><!-- .comment-author -->

			<header class="comment-meta">
				<span class="comment-author vcard"><cite class="fn"><?php comment_author_link(); ?></cite></span>
				<?php echo _x( 'on', 'comment author "on" date', 'jobify' ); ?>
				 <?php
					printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						sprintf( _x( '%1$s at %2$s', 'on 1: date, 2: time', 'jobify' ), get_comment_date(), get_comment_time() )
					);
					edit_comment_link( __( 'Edit', 'jobify' ), '<span class="edit-link"><i class="icon-pencil"></i> ', '<span>' );

					comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'jobify' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) );
				?>
			</header><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'jobify' ); ?></p>
			<?php endif; ?>

			<div class="comment-content">
				<?php comment_text(); ?>
			</div><!-- .comment-content -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // End comment_type check.
}
endif;

/**
 * Testimonials by WooThemes
 *
 * @since Jobify 1.0
 *
 * @param string $tpl
 * @return string $tpl
 */
function jobify_woothemes_testimonials_item_template( $tpl, $args ) {
	if ( 'individual' == $args[ 'category' ] ) {
		$tpl  = '<blockquote id="quote-%%ID%%" class="individual-testimonial %%CLASS%%">';
		$tpl .= '<p>%%TEXT%%</p>';
		$tpl .= '<cite class="individual-testimonial-author">%%AVATAR%% %%AUTHOR%%</cite>';
		$tpl .= '</blockquote>';
	} else {
		$tpl  = '<div class="company-slider-item">';
		$tpl .= '%%AVATAR%%';
		$tpl .= '</div>';
	}

	return $tpl;
}
add_filter( 'woothemes_testimonials_item_template', 'jobify_woothemes_testimonials_item_template', 10, 2 );

if ( ! function_exists( 'shortcode_exists' ) ) :
/**
 * Whether a registered shortcode exists named $tag
 *
 * @since 3.6.0
 *
 * @global array $shortcode_tags
 * @param string $tag
 * @return boolean
 */
function shortcode_exists( $tag ) {
	global $shortcode_tags;
	return array_key_exists( $tag, $shortcode_tags );
}
endif;

if ( ! function_exists( 'has_shortcode' ) ) :
/**
 * Whether the passed content contains the specified shortcode
 *
 * @since 3.6.0
 *
 * @global array $shortcode_tags
 * @param string $tag
 * @return boolean
 */
function has_shortcode( $content, $tag ) {
	if ( shortcode_exists( $tag ) ) {
		preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) )
			return false;

		foreach ( $matches as $shortcode ) {
			if ( $tag === $shortcode[2] )
				return true;
		}
	}
	return false;
}
endif;

/**
 * Job Manager
 */
require_once( get_template_directory() . '/inc/job-manager.php' );

/**
 * Widgets
 */
require_once( get_template_directory() . '/inc/widgets.php' );
require_once( get_template_directory() . '/inc/widgets/callout.php' );
require_once( get_template_directory() . '/inc/widgets/video.php' );
require_once( get_template_directory() . '/inc/widgets/blog-posts.php' );

if ( defined( 'RCP_PLUGIN_VERSION' ) ) {
	require_once( get_template_directory() . '/inc/widgets/price-table-rcp.php' );
} else if ( class_exists( 'WooCommerce' ) ) {
	require_once( get_template_directory() . '/inc/widgets/price-table-wc.php' );
} else {
	require_once( get_template_directory() . '/inc/widgets/price-option.php' );
	require_once( get_template_directory() . '/inc/widgets/price-table.php' );
}

if ( class_exists( 'WP_Job_Manager' ) ) {
	require_once( get_template_directory() . '/inc/widgets/jobs.php' );
	require_once( get_template_directory() . '/inc/widgets/stats.php' );
	require_once( get_template_directory() . '/inc/widgets/map.php' );
}

if ( class_exists( 'Woothemes_Testimonials' ) ) {
	require_once( get_template_directory() . '/inc/widgets/companies.php' );
	require_once( get_template_directory() . '/inc/widgets/testimonials.php' );
}

if ( function_exists( 'soliloquy_slider' ) ) {
	require_once( get_template_directory() . '/inc/widgets/slider.php' );
	require_once( get_template_directory() . '/inc/widgets/slider-hero.php' );
}

/**
 * Custom Header
 */
require_once( get_template_directory() . '/inc/custom-header.php' );

/**
 * Customizer
 */
require_once( get_template_directory() . '/inc/theme-customizer.php' );