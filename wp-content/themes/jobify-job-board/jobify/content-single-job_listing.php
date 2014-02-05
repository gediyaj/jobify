<?php
/**
 * Job Content
 *
 * @package Jobify
 * @since Jobify 1.0
 */

global $job_manager;
?>

<div class="single_job_listing">
<?php //=================================== praks ============================================================================================
if(isset($_GET['apply_job']))
{
	// Get Current User Information
					$current_user = wp_get_current_user();
					$userid = $current_user->ID;
				 	$useremail = $current_user->user_email;
					$postid = get_the_ID();
					$posttitle = get_the_title($postid);
				 	$admin_email = get_option( 'admin_email' );
					$siteurl = get_site_url();
					$userexist =  $wpdb->get_var("select * from `wp_applylist` where user_id ='".$userid."' AND post_id = '".$postid."' ");
					
					  if(empty($userexist))
					  {
						  $my_post = array(
						  'post_title'    => $posttitle,
						  'post_type'	  =>'applyjob',
						  'post_status'   => 'publish',
						  'post_author'   => $userid,
						);
						
						// Insert the post into the database
						wp_insert_post( $my_post );

					  	$wpdb->query("INSERT INTO `wp_applylist`
								  (`id`, `post_id`, `user_id`)
									  VALUES(
									  null,
								  	'".$postid."',
								  	'".$userid."'							  
								  	)")or die(mysql_error());
								  
						  echo "<p style='color: black; background: #4FC06A; padding: 10px; width: 94%;'>Apply Job Successfully</p>";
						  
						   $to = $useremail;
						   $subject = "Apply For A job";
						   $message = "<a href=".$siteurl.">".$siteurl."</a>";
						   $message .= "<h1>Apply Job Successfully.</h1>";
						   $header = "From: Jobify <".$admin_email."> \r\n";
						   $header .= "Cc:".$admin_email." \r\n";
						   $header .= "MIME-Version: 1.0\r\n";
						   $header .= "Content-type: text/html\r\n";
						   $retval = mail ($to,$subject,$message,$header);
						   
						   $to = $admin_email;
						   $subject = "Apply For A job";
						   
						   $message = "<a href=".$siteurl.">".$siteurl."</a>";
						   $message .= "<h3>Apply Job For ".$current_user->user_login.".</h2><br><br>";
						   $message .= "<h4>User Name:- ".$current_user->user_login.".</h4><br>";
						   $message .= "<h4>User Email:- ".$useremail.".</h4><br>";
						   $message .= "<h4>Apply for post:- ".$posttitle.".</h4><br>";
						   
						   $header = "From: Jobify<".$admin_email."> \r\n";
						   $header .= "Cc:".$useremail." \r\n";
						   $header .= "MIME-Version: 1.0\r\n";
						   $header .= "Content-type: text/html\r\n";
						   $retval = mail ($to,$subject,$message,$header);
						   
					  }
					  else
					  {
						  echo "<p style='color: black; background: rgb(255, 45, 45); padding: 10px; width: 94%;'>Already Apply This Job</p>";
					  }
}
?>
	<?php if ( $post->post_status == 'expired' ) : ?>

		<div class="job-manager-info"><?php _e( 'This job listing has expired', 'jobify' ); ?></div>

	<?php else : ?>

		<?php if ( is_position_filled() ) : ?>
			<div class="job-manager-error"><?php _e( 'This position has been filled', 'jobify' ); ?></div>
		<?php endif; ?>

		<div class="job-overview-content">
			<div class="job-overview<?php echo '' == jobify_get_the_company_description() ? ' no-company-desc' : null; ?>">
				<h2 class="job-overview-title"><?php _e( 'Overview', 'jobify' ); ?></h2>

				<?php echo apply_filters( 'the_job_description', get_the_content() ); ?>
			</div>

			<?php if ( '' != jobify_get_the_company_description() ) : ?>
			<div class="job-company-about">
				<h2 class="job-overview-title" itemscope itemtype="http://data-vocabulary.org/Organization"><?php printf( __( 'About %s', 'jobify' ), get_the_company_name() ); ?></h2>

				<?php jobify_the_company_description(); ?>
			</div>
			<?php endif; ?>

			<div class="job-meta">
				<ul class="meta">
					<li>
						<?php
							if ( class_exists( 'Astoundify_Job_Manager_Companies' ) && '' != get_the_company_name() ) :
								$companies   = Astoundify_Job_Manager_Companies::instance();
								$company_url = esc_url( $companies->company_url( get_the_company_name() ) );
						?>
						<a href="<?php echo $company_url; ?>" target="_blank"><?php the_company_logo(); ?></a>
						<?php else : ?>
							<?php the_company_logo(); ?>
						<?php endif; ?>
					</li>

					<li class="job-type <?php echo get_the_job_type() ? sanitize_title( get_the_job_type()->slug ) : ''; ?>"><?php the_job_type(); ?></li>

					<?php if ( ! is_position_filled() && $post->post_status !== 'preview' ) : ?><li><?php get_job_manager_template( 'job-application.php' ); ?></li><?php endif; ?>

					<li>
						<h4 class="company-social-title"><?php _e( 'Company Details', 'jobify' ); ?></h4>

						<?php do_action( 'job_listing_company_details_before' ); ?>

						<ul class="company-social">
							<?php do_action( 'job_listing_company_social_before' ); ?>

							<?php if ( get_the_company_website() ) : ?>
							<li><a href="<?php echo get_the_company_website(); ?>" target="_blank" itemprop="url">
								<i class="icon-link"></i>
								<?php _e( 'Website', 'jobify' ); ?>
							</a></li>
							<?php endif; ?>

							<?php if ( get_the_company_twitter() ) : ?>
							<li><a href="http://twitter.com/<?php echo get_the_company_twitter(); ?>">
								<i class="icon-twitter"></i>
								<?php _e( 'Twitter', 'jobify' ); ?>
							</a></li>
							<?php endif; ?>

							<?php if ( jobify_get_the_company_facebook() ) : ?>
							<li><a href="http://facebook.com/<?php echo jobify_get_the_company_facebook(); ?>">
								<i class="icon-facebook"></i>
								<?php _e( 'Facebook', 'jobify' ); ?>
							</a></li>
							<?php endif; ?>

							<?php if ( jobify_get_the_company_gplus() ) : ?>
							<li><a href="http://plus.google.com/<?php echo jobify_get_the_company_gplus(); ?>">
								<i class="icon-gplus"></i>
								<?php _e( 'Google+', 'jobify' ); ?>
							</a></li>
							<?php endif; ?>

							<?php do_action( 'job_listing_company_social_after' ); ?>
						</ul>

						<?php if ( class_exists( 'Astoundify_Job_Manager_Companies' ) || get_option( 'job_manager_enable_categories' ) ) : ?>
						<ul class="company-social">
							<?php if ( class_exists( 'Astoundify_Job_Manager_Companies' ) && '' != get_the_company_name() ) : ?>
							<li>
								<a href="<?php echo $company_url; ?>" title="<?php printf( __( 'More jobs by %s', 'jobify' ), get_the_company_name() ); ?>"><i class="icon-newspaper"></i> <?php _e( 'More Jobs', 'jobify' ); ?></a>
							</li>
							<?php endif; ?>

							<?php
								if ( get_option( 'job_manager_enable_categories' ) ) :
									$categories = get_the_terms( $post->ID, 'job_listing_category' );

									if ( $categories ) :
										$category = current( $categories );
							?>
							<li>
								<a href="<?php echo get_term_link( $category, 'job_listing_category' ); ?>"><i class="icon-tag"></i> <?php echo $category->name; ?></a>
							</li>
								<?php endif; ?>
							<?php endif; ?>
						</ul>
						<?php endif; ?>

						<?php get_template_part( 'content-share' ); ?>

						<?php do_action( 'job_listing_company_details_after' ); ?>
					</li>
				</ul>
			</div>
		</div>

	<?php endif; ?>
</div>