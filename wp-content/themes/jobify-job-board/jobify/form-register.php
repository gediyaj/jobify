<?php
/**
 * Job Submission Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $job_manager;
?>
<form action="<?php echo $action; ?>" method="post" id="register-form" class="job-manager-form" enctype="multipart/form-data">
	<?php if ( isset ( $cred_fields ) ) : foreach ( $cred_fields as $key => $field ) : ?>
		<fieldset class="fieldset-<?php esc_attr( $key ); ?>">
			<label for="<?php esc_attr( $key ); ?>"><?php echo $field['label'] . ( $field['required'] ? '' : ' <small>' . __( '(optional)', 'jobify' ) . '</small>' ); ?></label>
			<div class="field">
				<?php get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
			</div>
		</fieldset>
	<?php endforeach; endif; ?>

	<?php if ( isset ( $info_fields ) ) : foreach ( $info_fields as $key => $field ) : ?>
		<fieldset class="fieldset-<?php esc_attr( $key ); ?>">
			<label for="<?php esc_attr( $key ); ?>"><?php echo $field['label'] . ( $field['required'] ? '' : ' <small>' . __( '(optional)', 'jobify' ) . '</small>' ); ?></label>
			<div class="field">
				<?php get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
			</div>
		</fieldset>
	<?php endforeach; endif; ?>

	<p class="has-account" id="login-modal"><i class="icon-help-circled"></i> <?php printf( __( 'Already have an account? <a href="%s">Login</a>', 'jobify' ), get_permalink( jobify_find_page_with_shortcode( array( 'login_form', 'jobify_login_form' ) ) ) ); ?></p>

	<p class="register-submit">
		<?php wp_nonce_field( 'register_form_posted' ); ?>
		<input type="hidden" name="job_manager_form" value="<?php echo $form; ?>" />
		<input type="submit" name="submit_register" class="button button-medium" value="<?php echo esc_attr( $submit_button_text ); ?>" />
	</p>
</form>