<?php
/**
 * Job Manager supplemental functionality.
 *
 * @since Jobify 1.0
 */

function jobify_is_job_board() {
	return class_exists( 'WP_Job_Manager' );
}

/** Post Type ------------------------------------------------------------------------ */

function jobify_register_post_type_job_listing( $args ) {
	$args[ 'supports' ] = array( 'title', 'editor', 'custom-fields', 'thumbnail' );

	return $args;
}
add_filter( 'register_post_type_job_listing', 'jobify_register_post_type_job_listing' );

/**
 * When viewing a taxonomy archive, use the same template for all.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_job_archives() {
	global $wp_query;

	$taxonomies = array(
		'job_listing_category',
		'job_listing_region',
		'job_listing_type',
		'job_listing_tag'
	);

	if ( ! is_tax( $taxonomies ) )
		return;

	locate_template( array( 'taxonomy-job_listing_category.php' ), true );

	exit();
}
add_action( 'template_redirect', 'jobify_job_archives' );

/**
 * When viewing a taxonomy archive, make sure the job manager settings are respected.
 *
 * @since Jobify 1.0
 *
 * @param $query
 * @return $query
 */
function jobify_job_archives_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() )
			return;

	$taxonomies = array(
		'job_listing_category',
		'job_listing_region',
		'job_listing_type',
		'job_listing_tag'
	);

	if ( is_tax( $taxonomies ) ) {
		$query->set( 'posts_per_page', get_option( 'job_manager_per_page' ) );
		$query->set( 'post_type', array( 'job_listing' ) );
		$query->set( 'post_status', array( 'publish' ) );

		if ( get_option( 'job_manager_hide_filled_positions' ) == 1 ) {
			$query->set( 'meta_query', array(
				array(
					'key'     => '_filled',
					'value'   => '1',
					'compare' => '!='
				)
			) );
		}
	}

	return $query;
}
add_filter( 'pre_get_posts', 'jobify_job_archives_query' );

/**
 * When updating a post remove the cached meta information.
 *
 * @since Jobify 1.4.2
 */
function jobify_clear_location_cache() {
	global $post;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( ! is_object( $post ) )
		return;

	if ( 'job_listing' != $post->post_type )
		return;

	if ( ! current_user_can( 'edit_post', $post->ID ) )
		return;

	if ( $post->job_cords ) {
		delete_post_meta( $post->ID, 'job_cords' );
	}
}
add_action( 'save_post', 'jobify_clear_location_cache' );

/** Submission ------------------------------------------------------------------------ */

/**
 * Add extra fields to the submission form.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_submit_job_form_fields( $fields ) {
	$fields[ 'company' ][ 'company_website' ][ 'priority' ] = 4.2;

	$fields[ 'company' ][ 'company_description' ] = array(
		'label'       => __( 'Description', 'jobify' ),
		'type'        => 'wp-editor',
		'required'    => false,
		'placeholder' => '',
		'priority'    => 3.5
	);

	$fields[ 'company' ][ 'company_facebook' ] = array(
		'label'       => __( 'Facebook username', 'jobify' ),
		'type'        => 'text',
		'required'    => false,
		'placeholder' => __( 'yourcompany', 'jobify' ),
		'priority'    => 4.5
	);

	$fields[ 'company' ][ 'company_google' ] = array(
		'label'       => __( 'Google+ username', 'jobify' ),
		'type'        => 'text',
		'required'    => false,
		'placeholder' => __( 'yourcompany', 'jobify' ),
		'priority'    => 4.5
	);

	return $fields;
}
add_filter( 'submit_job_form_fields', 'jobify_submit_job_form_fields' );

/**
 * Save the extra frontend fields
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_job_manager_update_job_data( $job_id, $values ) {
	update_post_meta( $job_id, '_company_description', $values[ 'company' ][ 'company_description' ] );
	update_post_meta( $job_id, '_company_facebook', $values[ 'company' ][ 'company_facebook' ] );
	update_post_meta( $job_id, '_company_google', $values[ 'company' ][ 'company_google' ] );
}
add_action( 'job_manager_update_job_data', 'jobify_job_manager_update_job_data', 10, 2 );

/**
 * Add extra fields to the WordPress admin.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_wp_job_manager_job_listing_data_fields( $fields ) {
	$fields[ '_company_description' ] = array(
		'label' => __( 'Company Description', 'jobify' ),
		'placeholder' => '',
		'type'        => 'textarea'
	);

	$fields[ '_company_facebook' ] = array(
		'label' => __( 'Company Facebook', 'jobify' ),
		'placeholder' => ''
	);

	$fields[ '_company_google' ] = array(
		'label' => __( 'Company Google+', 'jobify' ),
		'placeholder' => ''
	);

	return $fields;
}
add_filter( 'job_manager_job_listing_data_fields', 'jobify_wp_job_manager_job_listing_data_fields' );

/**
 * Save the extra admin fields.
 *
 * WP Job Manager strips our tags out. Resave it after with the tags.
 *
 * @since Jobify 1.4.4
 *
 * @return void
 */
function jobify_job_manager_save_job_listing( $job_id, $post ) {
	update_post_meta( $job_id, '_company_description', wp_kses_post( $_POST[ '_company_description' ] ) );
}
add_action( 'job_manager_save_job_listing', 'jobify_job_manager_save_job_listing', 10, 2 );

/** Output ------------------------------------------------------------------------ */

/**
 * The Company Description template tag.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_the_company_description( $before = '', $after = '', $echo = true ) {
	$company_description = jobify_get_the_company_description();

	if ( strlen( $company_description ) == 0 )
		return;

	$company_description = wp_kses_post( $company_description );
	$company_description = $before . wpautop( $company_description ) . $after;

	if ( $echo )
		echo $company_description;
	else
		return $company_description;
}

/**
 * Get the company description.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_get_the_company_description( $post = 0 ) {
	$post = get_post( $post );

	if ( $post->post_type !== 'job_listing' )
		return;

	return apply_filters( 'the_company_description', $post->_company_description, $post );
}

/**
 * Trim the job location output on all pages except the actual listing.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_the_job_location( $location ) {
	if ( is_singular( 'job_listing' ) )
		return $location;

	$location = wp_trim_words( $location, 3, '' );

	return $location;
}
add_filter( 'the_job_location', 'jobify_the_job_location' );

/**
 * Get the Company Facebook
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_get_the_company_facebook( $post = 0 ) {
	$post = get_post( $post );

	if ( $post->post_type !== 'job_listing' )
		return;

	$company_facebook = $post->_company_facebook;

	if ( strlen( $company_facebook ) == 0 )
		return;

	return apply_filters( 'the_company_facebook', $company_facebook, $post );
}

/**
 * Get the Company Google Plus
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_get_the_company_gplus( $post = 0 ) {
	$post = get_post( $post );

	if ( $post->post_type !== 'job_listing' )
		return;

	$company_google = $post->_company_google;

	if ( strlen( $company_google ) == 0 )
		return;

	return apply_filters( 'the_company_google', $company_google, $post );
}

/** Shortcodes ------------------------------------------------------------------------ */

/**
 * Login Form Shortcode
 *
 * @since Jobify 1.0
 *
 * @return $form HTML form.
 */
function jobify_shortcode_login_form() {
	ob_start();

	wp_login_form( apply_filters( 'jobify_shortcode_login_form', array(
		'label_log_in' => _x( 'Sign In', 'login for submit label', 'jobify' ),
		'value_remember' => true,
		'redirect' => home_url()
	) ) );

	$form = ob_get_clean();

	return $form;
}
add_shortcode( 'jobify_login_form', 'jobify_shortcode_login_form' );

/**
 * Add a "Forgot Password" link to the login form
 *
 * @since Jobify 1.0
 *
 * @return $output HTML output
 */
function jobify_login_form_middle() {
	$output = sprintf( '<p class="has-account"><i class="icon-help-circled"></i> <a href="%s">%s</a></p>', wp_lostpassword_url(), __( 'Forgot Password?', 'jobify' ) );

	return $output;
}
add_filter( 'login_form_middle', 'jobify_login_form_middle' );

/**
 * Register Form Shortcode
 *
 * @since Jobify 1.0
 *
 * @return $form HTML form.
 */
function jobify_shortcode_register_form() {
	if ( ! class_exists( 'WP_Job_Manager_Form' ) )
		include_once( JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php' );

	include_once( get_template_directory() . '/inc/job-manager-form-register.php' );

	ob_start();

	WP_Job_Manager_Form_Register::output();

	$form = ob_get_clean();

	return $form;
}
add_shortcode( 'jobify_register_form', 'jobify_shortcode_register_form' );

/**
 * Posted Register Form
 *
 * @since Jobify 1.0
 *
 * @return $form HTML form.
 */
function jobify_load_posted_form() {
	if ( ! empty( $_POST['job_manager_form'] ) ) {
		$form        = esc_attr( $_POST['job_manager_form'] );

		$form_class  = 'WP_Job_Manager_Form_' . str_replace( '-', '_', $form );
		$form_file   = get_template_directory() . '/inc/job-manager-form-' . $form . '.php';

		if ( class_exists( $form_class ) )
			return $form_class;

		if ( ! file_exists( $form_file ) )
			return false;

		if ( ! class_exists( $form_class ) )
			include $form_file;

		return $form_class;
	}
}
add_action( 'init', 'jobify_load_posted_form' );

/**
 * Find pages that contain shortcodes.
 *
 * To avoid options, try to find pages for them.
 *
 * @since Jobify 1.0
 *
 * @return $_page
 */
function jobify_find_page_with_shortcode( $shortcodes ) {
	if ( ! is_array( $shortcodes ) )
		$shortcode = array( $shortcodes );

	$_page = 0;

	foreach ( $shortcodes as $shortcode ) {
		if ( ! get_option( 'job_manager_page_' . $shortcode ) ) {
			$pages = new WP_Query( array(
				'post_type'              => 'page',
				'post_status'            => 'publish',
				'ignore_sticky_posts'    => 1,
				'no_found_rows'          => true,
				'nopaging'               => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false
			) );

			while ( $pages->have_posts() ) {
				$pages->the_post();

				if ( has_shortcode( get_post()->post_content, $shortcode ) ) {
					$_page = get_post()->ID;

					break;
				}
			}

			add_option( 'job_manager_page_' . $shortcode, $_page );
		} else {
			$_page = get_option( 'job_manager_page_' . $shortcode );
		}

		if ( $_page > 0 )
			break;
	}

	return $_page;
}

/**
 * Clear shortcode options when a post is saved.
 *
 * @since Jobify 1.0
 *
 * @return void
 */
function jobify_clear_page_shortcode() {
	$shortcodes = array(
		'login_form',
		'register_form',
		'jobify_login_form',
		'jobify_register_form'
	);

	foreach ( $shortcodes as $shortcode ) {
		delete_option( 'job_manager_page_' . $shortcode );
	}
}
add_action( 'save_post', 'jobify_clear_page_shortcode' );

function jobify_job_manager_job_filters_after() {
?>
	<input type="submit" name="submit" value="<?php echo esc_attr_e( 'Search', 'jobify' ); ?>" />
<?php
}
add_action( 'job_manager_job_filters_search_jobs_end', 'jobify_job_manager_job_filters_after', 9 );
add_action( 'resume_manager_resume_filters_search_resumes_end', 'jobify_job_manager_job_filters_after', 9 );

function jobify_submit_job_form_logout_url( $url ) {
	$page = jobify_find_page_with_shortcode( array( 'jobify_login_form', 'login_form' ) );

	return get_permalink( $page );
}
add_filter( 'submit_job_form_login_url', 'jobify_submit_job_form_logout_url' );