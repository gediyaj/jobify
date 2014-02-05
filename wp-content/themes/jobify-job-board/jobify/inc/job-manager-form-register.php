<?php
/**
 * WP_Job_Manager_Form_Register class.
 */
class WP_Job_Manager_Form_Register extends WP_Job_Manager_Form {

	public    static $form_name = 'register';
	protected static $job_id;
	protected static $preview_job;
	protected static $steps;
	protected static $step;

	/**
	 * Init form
	 */
	public static function init() {
		add_action( 'wp', array( __CLASS__, 'process' ) );

		// Get step/job
		self::$step   = ! empty( $_REQUEST['step'] ) ? max( absint( $_REQUEST['step'] ), 0 ) : 0;

		$register = jobify_find_page_with_shortcode( array( 'jobify_register_form', 'register_form' ) );
		$register = get_post( $register );

		self::$action = get_permalink( $register->ID );

		self::$steps  = (array) apply_filters( 'register_form_steps', array(
			'submit' => array(
				'name'     => __( 'Register', 'jobify' ),
				'view'     => array( __CLASS__, 'submit' ),
				'handler'  => array( __CLASS__, 'submit_handler' ),
				'priority' => 10
				),
			)
		);

		usort( self::$steps, array( __CLASS__, 'sort_by_priority' ) );
	}

	/**
	 * Increase step from outside of the class
	 */
	public function next_step() {
		self::$step ++;
	}

	/**
	 * Decrease step from outside of the class
	 */
	public function previous_step() {
		self::$step --;
	}

	/**
	 * Sort array by priority value
	 */
	private static function sort_by_priority( $a, $b ) {
		return $a['priority'] - $b['priority'];
	}

	/**
	 * init_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public static function init_fields() {
		self::$fields = apply_filters( 'register_form_fields', array(
			'creds' => array(
				'nicename' => array(
					'label'       => __( 'Your Name', 'jobify' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1
				),
				'email' => array(
					'label'       => __( 'Email Address', 'jobify' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => __( 'recruiter@company.com', 'jobify' ),
					'priority'    => 2
				),
				'password' => array(
					'label'       => __( 'Password', 'jobify' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 3
				)
			)
		) );

		if ( class_exists( 'WP_Resume_Manager' ) ) {
			self::$fields[ 'info' ][ 'role' ] = array(
				'label'       => __( 'About You', 'jobify' ),
				'type'        => 'select',
				'required'    => true,
				'priority'    => 4,
				'options'     => array(
					'subscriber' => __( 'I&#39;m an employeer looking to hire', 'jobify' ),
					'candidate'  => __( 'I&#39;m a candidate looking for a job', 'jobify' )
				)
			);
		}
	}

	/**
	 * Get post data for fields
	 *
	 * @return array of data
	 */
	protected static function get_posted_fields() {
		self::init_fields();

		$values = array();

		foreach ( self::$fields as $group_key => $fields ) {
			foreach ( $fields as $key => $field ) {
				$values[ $group_key ][ $key ] = isset( $_POST[ $key ] ) ? stripslashes( $_POST[ $key ] ) : '';
				$values[ $group_key ][ $key ] = sanitize_text_field( $values[ $group_key ][ $key ] );

				// Set fields value
				self::$fields[ $group_key ][ $key ]['value'] = $values[ $group_key ][ $key ];
			}
		}

		return $values;
	}

	/**
	 * Validate hte posted fields
	 *
	 * @return bool on success, WP_ERROR on failure
	 */
	protected static function validate_fields( $values ) {
		foreach ( self::$fields as $group_key => $fields ) {
			foreach ( $fields as $key => $field ) {
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) )
					return new WP_Error( 'validation-error', sprintf( __( '%s is a required field', 'jobify' ), $field['label'] ) );
			}
		}

		return true;
	}

	/**
	 * Process function. all processing code if needed - can also change view if step is complete
	 */
	public static function process() {
		$keys = array_keys( self::$steps );

		if ( isset( $keys[ self::$step ] ) && is_callable( self::$steps[ $keys[ self::$step ] ]['handler'] ) ) {
			call_user_func( self::$steps[ $keys[ self::$step ] ]['handler'] );
		}
	}

	/**
	 * output function. Call the view handler.
	 */
	public static function output() {
		$keys = array_keys( self::$steps );

		self::show_errors();

		if ( isset( $keys[ self::$step ] ) && is_callable( self::$steps[ $keys[ self::$step ] ]['view'] ) ) {
			call_user_func( self::$steps[ $keys[ self::$step ] ]['view'] );
		}
	}

	/**
	 * Submit Step
	 */
	public static function submit() {
		global $job_manager, $post;

		self::init_fields();

		get_job_manager_template( 'form-register.php', array(
			'form'               => self::$form_name,
			'action'             => self::get_action(),
			'cred_fields'        => self::get_fields( 'creds' ),
			'info_fields'        => self::get_fields( 'info' ),
			'submit_button_text' => __( 'Register', 'jobify' )
		) );

		wp_reset_query();
	}

	/**
	 * Submit Step is posted
	 */
	public static function submit_handler() {
		try {
			// Get posted values
			$values = self::get_posted_fields();

			if ( empty( $_POST[ 'submit_register' ] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'register_form_posted' ) )
				return;

			// Validate required
			if ( is_wp_error( ( $return = self::validate_fields( $values ) ) ) )
				throw new Exception( $return->get_error_message() );

			$role   = $values[ 'info' ][ 'role' ];
			$values = $values[ 'creds' ];

			$user_email = apply_filters( 'user_registration_email', sanitize_email( $values[ 'email' ] ) );

			if ( empty( $user_email ) )
				return false;

			if ( ! is_email( $user_email ) )
				throw new Exception( __( 'Your email address isn&#8217;t correct.', 'jobify' ) );

			if ( email_exists( $user_email ) )
				throw new Exception( __( 'This email is already registered, please choose another one.', 'jobify' ) );

			// Email is good to go - use it to create a user name
			$username = sanitize_user( $values[ 'nicename' ] );
			$password = esc_attr( $values[ 'password' ] );

			// Ensure username is unique
			$append     = 1;
			$o_username = $username;

			while( username_exists( $username ) ) {
				$username = $o_username . $append;
				$append ++;
			}

			// Final error check
			$reg_errors = new WP_Error();
			do_action( 'register_post', $username, $user_email, $reg_errors );
			$reg_errors = apply_filters( 'registration_errors', $reg_errors, $username, $user_email );

			if ( $reg_errors->get_error_code() )
				return $reg_errors;

			// Get the role
			$role = esc_attr( $role );

			// Create account
			$new_user = array(
				'user_login' => $username,
				'user_pass'  => $password,
				'user_email' => $user_email,
				'role'       => $role
			);

			$user_id = wp_insert_user( apply_filters( 'job_manager_create_account_data', $new_user ) );

			if ( is_wp_error( $user_id ) )
				return $user_id;

			// Notify
			wp_new_user_notification( $user_id, $password );

			// Login
			if ( apply_filters( 'jobify_force_login_on_register', true ) ) {
				wp_set_auth_cookie( $user_id, true, is_ssl() );
				$current_user = get_user_by( 'id', $user_id );

				wp_safe_redirect( apply_filters( 'jobify_registeration_redirect', home_url() ) );
				exit();
			} else {
				do_action( 'jobify_user_registered', $current_user );
			}

			return true;
		} catch ( Exception $e ) {
			self::add_error( $e->getMessage() );
			return;
		}
	}
}

WP_Job_Manager_Form_Register::init();
