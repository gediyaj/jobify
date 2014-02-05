
<div class="resume_contact">
	<input class="resume_contact_button" type="button" value="<?php _e( 'Contact', 'resume_manager' ); ?>" />

	<div class="resume_contact_details">
		<h2 class="modal-title"><?php _e( 'Contact', 'jobify' ); ?></h2>

		<div class="resume-contact-content">
			<?php
				$email   = get_post_meta( $post->ID, '_candidate_email', true );
				$subject = sprintf( __( 'Contact via the resume for "%s" on %s', 'resume_manager' ), $post->post_title, home_url() );

				if ( class_exists( 'Astoundify_Job_Manager_Apply_GF', false ) ) :
					echo do_shortcode( '[gravityform id="' . get_option( 'job_manager_gravity_form_resumes' ) . '" title="false" ajax="true"]' );
				else :
					echo '<p>' . sprintf( __( 'To apply for this job <strong>email your details to</strong> <a class="job_application_email" href="mailto:%1$s%2$s">%1$s</a>', 'jobify' ), $email, '?subject=' . rawurlencode( $subject ) ) . '</p>';
				endif;
			?>

			<?php if ( ! resume_manager_user_can_view_contact_details( $post->ID ) ) : ?>
				<?php get_job_manager_template_part( 'access-denied', 'contact-details', 'resume_manager', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>
			<?php endif; ?>
		</div>
	</div>
</div>