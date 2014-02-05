<?php global $rcp_options, $post; ?>

<?php if( ! is_user_logged_in() ) { ?>
	<h3 class="rcp_header">
		<?php echo apply_filters( 'rcp_registration_header_logged_in', __( 'Register New Account', 'jobify' ) ); ?>
	</h3>
<?php } else { ?>
	<h3 class="rcp_header">
		<?php echo apply_filters( 'rcp_registration_header_logged_out', __( 'Upgrade Your Subscription', 'jobify' ) ); ?>
	</h3>
<?php }

// show any error messages after form submission
rcp_show_error_messages( 'register' ); ?>

<form id="rcp_registration_form" method="POST" action="<?php echo esc_url( rcp_get_current_url() ); ?>">

	<?php $levels = rcp_get_subscription_levels( 'active' );
	if( $levels && count( $levels ) > 1 ) : ?>
	<fieldset class="rcp_subscription_fieldset">
		<p class="rcp_subscription_message"><?php echo apply_filters ( 'rcp_registration_choose_subscription', __( 'Choose your subscription level', 'jobify' ) ); ?></p>
		<div class="pricing-table-widget-<?php echo count( $levels ); ?>">
			<?php foreach( $levels as $key => $level ) : ?>
				<?php if( rcp_show_subscription_level( $level->id ) ) : ?>
				<div id="rcp_subscription_level_<?php echo $level->id; ?>" class="pricing-table-widget rcp_subscription_level">
					<div class="pricing-table-widget-title" style="background-color: #01da90">
						<input type="radio" class="required rcp_level" <?php if( $key == 0 || ( isset( $_GET['level']) && $_GET['level'] == $key ) ){ echo 'checked="checked"'; }?> name="rcp_level" rel="<?php echo esc_attr( $level->price ); ?>" value="<?php echo esc_attr( absint( $level->id ) ); ?>" <?php if( $level->duration == 0 ) { echo 'data-duration="forever"'; } ?>/>
						<span class="rcp_subscription_level_name"><?php echo stripslashes( $level->name ); ?></span>
					</div>

					<div class="pricing-table-widget-description">
						<h2><span class="rcp_price" rel="<?php echo esc_attr( $level->price ); ?>"><?php echo $level->price > 0 ? rcp_currency_filter( $level->price ) : __( 'free', 'jobify' ); ?></h2>

						<p><span class="rcp_level_duration"><?php echo $level->duration > 0 ? $level->duration . '&nbsp;' . rcp_filter_duration_unit( $level->duration_unit, $level->duration ) : __( 'unlimited', 'jobify' ); ?></span></p>

						<?php echo wpautop( wp_kses( $level->description, rcp_allowed_html_tags() ) ); ?>
						
					</div>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php elseif($levels) : ?>
		<input type="hidden" class="rcp_level" name="rcp_level" rel="<?php echo esc_attr( $levels[0]->price ); ?>" value="<?php echo esc_attr( $levels[0]->id ); ?>"/>
	<?php else : ?>
		<p><strong><?php _e( 'You have not created any subscription levels yet', 'jobify' ); ?></strong></p>
	<?php endif; ?>
	</fieldset>
		<?php
		if( rcp_has_discounts() ) : ?>
			<p id="rcp_discount_code_wrap">
				<label for="rcp_discount_code">
					<?php _e( 'Discount Code', 'jobify' ); ?>
					<span class="rcp_discount_valid" style="display: none;"> - <?php _e( 'Valid', 'jobify' ); ?></span>
					<span class="rcp_discount_invalid" style="display: none;"> - <?php _e( 'Invalid', 'jobify' ); ?></span>
				</label>
				<input type="text" id="rcp_discount_code" name="rcp_discount" class="rcp_discount_code" value=""/>
			</p>
		<?php endif;

		do_action( 'rcp_after_register_form_fields', $levels );

		$gateways = rcp_get_enabled_payment_gateways();
		if( count( $gateways ) > 1 ) :
			$display = rcp_has_paid_levels() ? '' : ' style="display: none;"';
			echo '<p id="rcp_payment_gateways"' . $display . '>';
				echo '<select name="rcp_gateway" id="rcp_gateway">';
					foreach( $gateways as $key => $gateway ) :
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $gateway ) . '</option>';
					endforeach;
				echo '</select>';
				echo '<label for="rcp_gateway">' . __( 'Choose Your Payment Method', 'jobify' ) . '</label>';
			echo '</p>';
		else:
			foreach( $gateways as $key => $gateway ) :
				echo '<input type="hidden" name="rcp_gateway" value="' . esc_attr( $key ) . '"/>';
			endforeach;
		endif;

		do_action( 'rcp_before_registration_submit_field', $levels );

		?>

	</fieldset>

	<?php if( ! is_user_logged_in() ) { ?>

	<?php do_action( 'rcp_before_register_form_fields' ); ?>

	<fieldset class="rcp_user_fieldset">
		<p id="rcp_user_login_wrap">
			<label for="rcp_user_Login"><?php echo apply_filters ( 'rcp_registration_username_label', __( 'Username', 'jobify' ) ); ?></label>
			<input name="rcp_user_login" id="rcp_user_login" class="required" type="text" <?php if( isset( $_POST['rcp_user_login'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_login'] ) . '"'; } ?>/>
		</p>
		<p id="rcp_user_email_wrap">
			<label for="rcp_user_email"><?php echo apply_filters ( 'rcp_registration_email_label', __( 'Email', 'jobify' ) ); ?></label>
			<input name="rcp_user_email" id="rcp_user_email" class="required" type="text" <?php if( isset( $_POST['rcp_user_email'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_email'] ) . '"'; } ?>/>
		</p>
		<p id="rcp_user_first_wrap">
			<label for="rcp_user_first"><?php echo apply_filters ( 'rcp_registration_firstname_label', __( 'First Name', 'jobify' ) ); ?></label>
			<input name="rcp_user_first" id="rcp_user_first" type="text" <?php if( isset( $_POST['rcp_user_first'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_first'] ) . '"'; } ?>/>
		</p>
		<p id="rcp_user_last_wrap">
			<label for="rcp_user_last"><?php echo apply_filters ( 'rcp_registration_lastname_label', __( 'Last Name', 'jobify' ) ); ?></label>
			<input name="rcp_user_last" id="rcp_user_last" type="text" <?php if( isset( $_POST['rcp_user_last'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_last'] ) . '"'; } ?>/>
		</p>
		<p id="rcp_password_wrap">
			<label for="password"><?php echo apply_filters ( 'rcp_registration_password_label', __( 'Password', 'jobify' ) ); ?></label>
			<input name="rcp_user_pass" id="rcp_password" class="required" type="password"/>
		</p>
		<p id="rcp_password_again_wrap">
			<label for="password_again"><?php echo apply_filters ( 'rcp_registration_password_again_label', __( 'Password Again', 'jobify' ) ); ?></label>
			<input name="rcp_user_pass_confirm" id="rcp_password_again" class="required" type="password"/>
		</p id="rcp_user_login_wrap">

		<?php do_action( 'rcp_after_password_registration_field' ); ?>

	</fieldset>
	<?php } ?>
	
	<p id="rcp_submit_wrap">
		<input type="hidden" name="rcp_register_nonce" value="<?php echo wp_create_nonce('rcp-register-nonce' ); ?>"/>
		<input type="submit" name="rcp_submit_registration" id="rcp_submit" value="<?php echo apply_filters ( 'rcp_registration_register_button', __( 'Register', 'jobify' ) ); ?>"/>
	</p>
</form>