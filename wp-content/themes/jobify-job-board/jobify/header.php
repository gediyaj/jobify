<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package Jobify
 * @since Jobify 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width" />

	<title><?php wp_title( '|', true, 'right' ); ?></title>

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">

	<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
	<![endif]-->

	<?php wp_head(); ?>
</head>
<?php /*$cn=1;if ( current_user_can('administrator') ) {
		$url = get_home_url();
		if($cn==1)
		{
			echo '<script>window.location.replace("'.$url.'/wp-admin");</script>';
		}
		$cn=2;
		}*/?>
<body <?php body_class(); ?>>

	<div id="page" class="hfeed site">

		<header id="masthead" class="site-header" role="banner">
		
			<div class="container" style="position:relative;">
				<nav id="site-navigation" class="site-primary-navigation slide-left">
					<a href="#" class="primary-menu-toggle"><i class="icon-cancel-circled"></i> <span><?php _e( 'Close', 'jobify' ); ?></span></a>
					<?php locate_template( array( 'searchform-navigation.php' ), true ); ?>
					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu-primary' ) ); ?>
				</nav>
<?php	if ( is_user_logged_in() ){ //if (current_user_can('employee')) 
						$current_user = wp_get_current_user(); 
						$username = $current_user->user_login;?>
                		<div class="user-dtl" style="float: none; position: absolute; top: -25px; right: 15px; color: #062070; font-size: 14px; font-weight: lighter; background: #fff; padding: 2px 13px; border-radius: 4px;">
							<?php echo '<span style="color:#E27E2F; padding-right:10px;">welcome</span>'.$username;?>
                    	</div>
                    <?php }?>
				<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<a href="#" class="primary-menu-toggle in-header"><i class="icon-menu"></i></a>
				<?php endif; ?>

				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="site-branding">
					<?php $header_image = get_header_image(); ?>
					<h1 class="site-title">
						<?php if ( ! empty( $header_image ) ) : ?>
							<img src="<?php echo $header_image ?>" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" />
						<?php endif; ?>

						<span><?php bloginfo( 'name' ); ?></span>
					</h1>
					<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
				</a>
			</div>
		</header><!-- #masthead -->

		<div id="main" class="site-main">
