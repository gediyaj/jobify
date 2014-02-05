<?php
/**
 * Customize
 *
 * Theme options are lame! Manage any customizations through the Theme
 * Customizer. Expose the customizer in the Appearance panel for easy access.
 *
 * @package Jobify
 * @since Jobify 1.0
 */

/**
 * Get Theme Mod
 *
 * Instead of options, customizations are stored/accessed via Theme Mods
 * (which are still technically settings). This wrapper provides a way to
 * check for an existing mod, or load a default in its place.
 *
 * @since Jobify 1.0
 *
 * @param string $key The key of the theme mod to check. Prefixed with 'jobify_'
 * @return mixed The theme modification setting
 */
function jobify_theme_mod( $section, $key, $_default = false ) {
	$mods = jobify_get_theme_mods();

	$default = $mods[ $section ][ $key ][ 'default' ];

	if ( $_default )
		$mod = $default;
	else
		$mod = get_theme_mod( $key, $default );

	return apply_filters( 'jobify_theme_mod_' . $key, $mod );
}

/**
 * Register two new sections: General, and Social.
 *
 * @since Jobify 1.0
 *
 * @param object $wp_customize
 * @return void
 */
function jobify_customize_register_sections( $wp_customize ) {
	$wp_customize->add_section( 'jobify_general', array(
		'title'      => _x( 'General', 'Theme customizer section title', 'jobify' ),
		'priority'   => 10,
	) );

	$wp_customize->add_section( 'jobify_cta', array(
		'title'      => _x( 'Call to Action', 'Theme customizer section title', 'jobify' ),
		'priority'   => 900,
	) );
}
add_action( 'customize_register', 'jobify_customize_register_sections' );

/**
 * Default theme customizations.
 *
 * @since Jobify 1.0
 *
 * @return $options an array of default theme options
 */
function jobify_get_theme_mods( $args = array() ) {
	$defaults = array(
		'keys_only' => false
	);

	$args = wp_parse_args( $args, $defaults );

	$mods = array(
		'jobify_general' => array(
			'responsive' => array(
				'title'   => __( 'Enable Responsive Design', 'jobify' ),
				'type'    => 'checkbox',
				'default' => 1
			)
		),
		'colors' => array(
			'primary' => array(
				'title'   => __( 'Primary Color', 'jobify' ),
				'type'    => 'WP_Customize_Color_Control',
				'default' => '#01da90'
			)
		),
		'jobify_cta' => array(
			'jobify_cta_display' => array(
				'title'   => __( 'Display Call to Action Box', 'jobify' ),
				'type'    => 'checkbox',
				'default' => true
			),
			'jobify_cta_text' => array(
				'title'   => __( 'Text', 'jobify' ),
				'type'    => 'Jobify_Customize_Textarea_Control',
				'default' => "<h2>Got a question?</h2>\n\nWe&#39;re here to help. Check out our FAQs, send us an email or call us at 1 800 555 5555"
			),
			'jobify_cta_text_color' => array(
				'title'   => __( 'Text Color', 'jobify' ),
				'type'    => 'WP_Customize_Color_Control',
				'default' => '#ffffff'
			),
			'jobify_cta_background_color' => array(
				'title'   => __( 'Background Color', 'jobify' ),
				'type'    => 'WP_Customize_Color_Control',
				'default' => '#3399cc'
			)
		)
	);

	$mods = apply_filters( 'jobify_theme_mods', $mods );

	/** Return all keys within all sections (for transport, etc) */
	if ( $args[ 'keys_only' ] ) {
		$keys = array();
		$final = array();

		foreach ( $mods as $section ) {
			$keys = array_merge( $keys, array_keys( $section ) );
		}

		foreach ( $keys as $key ) {
			$final[ $key ] = '';
		}

		return $final;
	}

	return $mods;
}

/**
 * Register settings.
 *
 * Take the final list of theme mods, and register all the settings,
 * and add all of the proper controls.
 *
 * If the type is one of the default supported ones, add it normally. Otherwise
 * Use the type to create a new instance of that control type.
 *
 * @since Jobify 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function jobify_customize_register_settings( $wp_customize ) {
	$mods = jobify_get_theme_mods();

	foreach ( $mods as $section => $settings ) {
		foreach ( $settings as $key => $setting ) {
			$wp_customize->add_setting( $key, array(
				'default'    => jobify_theme_mod( $section, $key, true ),
			) );

			$type = $setting[ 'type' ];

			if ( in_array( $type, array( 'text', 'checkbox', 'radio', 'select', 'dropdown-pages' ) ) ) {
				$wp_customize->add_control( $key, array(
					'label'      => $setting[ 'title' ],
					'section'    => $section,
					'settings'   => $key,
					'type'       => $type,
					'choices'    => isset ( $setting[ 'choices' ] ) ? $setting[ 'choices' ] : null,
					'priority'   => isset ( $setting[ 'priority' ] ) ? $setting[ 'priority' ] : null
				) );
			} else {
				$wp_customize->add_control( new $type( $wp_customize, $key, array(
					'label'      => $setting[ 'title' ],
					'section'    => $section,
					'settings'   => $key,
					'priority'   => isset ( $setting[ 'priority' ] ) ? $setting[ 'priority' ] : null
				) ) );
			}
		}
	}

	do_action( 'jobify_customize_regiser_settings', $wp_customize );

	return $wp_customize;
}
add_action( 'customize_register', 'jobify_customize_register_settings' );

/**
 * Add postMessage support for all default fields, as well
 * as the site title and desceription for the Theme Customizer.
 *
 * @since Jobify 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function jobify_customize_register_transport( $wp_customize ) {
	$built_in = array( 'blogname' => '', 'blogdescription' => '', 'header_textcolor' => '' );
	$jobify   = jobify_get_theme_mods( array( 'keys_only' => true ) );

	$transport = array_merge( $built_in, $jobify );

	foreach ( $transport as $key => $default ) {
		$wp_customize->get_setting( $key )->transport = 'postMessage';
	}
}
add_action( 'customize_register', 'jobify_customize_register_transport' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since Jobify 1.0
 */
function jobify_customize_preview_js() {
	wp_enqueue_script( 'jobify-customizer', get_template_directory_uri() . '/js/jobify-theme-customizer.js', array( 'customize-preview' ), 20130704, true );
}
add_action( 'customize_preview_init', 'jobify_customize_preview_js' );

/**
 * Textarea Control
 *
 * Attach the custom textarea control to the `customize_register` action
 * so the WP_Customize_Control class is initiated.
 *
 * @since Jobify 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function Jobify_customize_textarea_control( $wp_customize ) {
	/**
	 * Textarea Control
	 *
	 * @since Jobify 1.0
	 */
	class Jobify_Customize_Textarea_Control extends WP_Customize_Control {
		public $type = 'textarea';

		public function render_content() {
	?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<textarea rows="8" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		</label>
	<?php
		}
	}
}
add_action( 'customize_register', 'jobify_customize_textarea_control', 1, 1 );

/**
 * Output the basic extra CSS for primary and accent colors.
 * Split away from widget colors for brevity.
 *
 * @since Jobify 1.0
 */
function jobify_header_css() {
?>
	<style id="jobify-custom-css">
		a,
		.button:hover,
		a.button-secondary,
		.load_more_jobs,
		.load_more_resumes,
		#wp-submit:hover,
		.btt:hover i,
		#submitcomment:hover,
		#searchsubmit:hover,
		.jobify_widget_slider .button-secondary:hover,
		input[type="submit"]:hover,
		.site-primary-navigation #searchform button,
		.entry-header a:hover,
		.entry-title a:hover {
			color: <?php echo jobify_theme_mod( 'colors', 'primary' ); ?>;
		}

		.site-header,
		.button,
		.button-secondary:hover,
		.search_jobs,
		.search_resumes,
		.load_more_jobs:hover,
		.load_more_resumes:hover,
		.paginate-links .page-numbers:hover,
		#wp-submit,
		button.mfp-close,
		#submitcomment,
		#searchsubmit,
		input[type="submit"],
		.content-grid .featured-image .overlay,
		.nav-menu-primary .sub-menu,
		.nav-menu-primary .children,
		.site-primary-navigation.open,
		.site-primary-navigation.close,
		#pmc_mailchimp div input[type="submit"],
		.pricing-table-widget.woocommerce .pricing-table-widget-title {
			background: <?php echo jobify_theme_mod( 'colors', 'primary' ); ?>;
		}

		.button:hover,
		a.button-secondary,
		.load_more_jobs,
		.load_more_resumes,
		.paginate-links .page-numbers:hover,
		input[type="text"]:focus,
		input[type="email"]:focus,
		input[type="password"]:focus,
		input[type="search"]:focus,
		input[type="number"]:focus,
		select:focus,
		textarea:focus,
		#wp-submit:hover,
		#submitcomment:hover,
		#searchsubmit:hover,
		input[type="submit"]:hover {
			border-color: <?php echo jobify_theme_mod( 'colors', 'primary' ); ?>;
		}

		.footer-cta {
			color: <?php echo jobify_theme_mod( 'jobify_cta', 'jobify_cta_text_color' ); ?>;
			background: <?php echo jobify_theme_mod( 'jobify_cta', 'jobify_cta_background_color' ); ?>;
		}

		ul.job_listings .job_listing:hover,
		.job_position_featured {
			box-shadow: inset 5px 0 0 <?php echo jobify_theme_mod( 'colors', 'primary' ); ?>;
		}
	</style>
<?php
}
add_action( 'wp_head', 'jobify_header_css' );