<?php if ( $apply = get_the_job_application_method() ) :
	?>
    
	<div class="application">
		<input class="application_button" type="button" value="<?php esc_attr_e( 'Apply', 'jobify' ); ?>" />

		<div class="application_details animated">
			<h2 class="modal-title"><?php _e( 'Apply', 'jobify' ); ?></h2>

			<div class="application-content">
            
			<?php
			//==============================  Developer Praks  ===================================================
			//====================================================================================================== 
			$sql = "CREATE TABLE IF NOT EXISTS `wp_applylist` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `post_id` varchar(30) NOT NULL,
					  `user_id` varchar(60) NOT NULL,
					  PRIMARY KEY (`id`)
					)";
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
				
			global $wpdb,$posts,$email, $emailapply, $fname ,$postid;
						
			if ( is_user_logged_in() ) 
			{  
				
				echo '<a style="text-decoration: none;background-color: #f08d3c; padding: 15px; color: #fff;" href="?job_listing='.$_GET['job_listing'].'&apply_job=apply_job">Apply Job To Continue</a>';
			}
			else
			{
				$mgs = '<p style="color:red;">' . sprintf( __('You have not login ') ) . '</p>';
				//echo $mgs;
				wp_login_form();
			}
				/*switch ( $apply->type ) {
					case 'email' :

						if ( class_exists( 'Astoundify_Job_Manager_Apply_GF' ) ) :
							echo do_shortcode( '[gravityform id="' . get_option( 'job_manager_gravity_form' ) . '" title="false" ajax="true"]' );
						else :
							echo '<p>' . sprintf( __( 'To apply for this job <strong>email your details to</strong> <a class="job_application_email" href="mailto:%1$s%2$s">%1$s</a>', 'jobify' ), $apply->email, '?subject=' . rawurlencode( $apply->subject ) ) . '</p>';
						endif;

					break;
					case 'url' :
						echo '<p>' . sprintf( __( 'To apply for this job please visit the following URL: <a href="%1$s">%1$s &rarr;</a>', 'jobify' ), $apply->url ) . '</p>';
					break;
				}*/
			?>
			</div>
		</div>
	</div>
<?php endif; ?>