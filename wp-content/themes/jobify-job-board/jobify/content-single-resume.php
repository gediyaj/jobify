<?php if ( resume_manager_user_can_view_resume( $post->ID ) ) : ?>
	<div class="single-resume-content">

		<div class="resume_description">
			<h2 class="job-overview-title"><?php _e( 'Description', 'jobify' ); ?></h2>

			<?php echo apply_filters( 'the_resume_description', get_the_content() ); ?>
		</div>

		<div class="resume-info">

			<?php if ( ( $skills = wp_get_object_terms( $post->ID, 'resume_skill', array( 'fields' => 'names' ) ) ) && is_array( $skills ) ) : ?>
				<h2><?php _e( 'Skills', 'resume_manager' ); ?></h2>
				<ul class="resume-manager-skills">
					<?php echo '<li>' . implode( '</li><li>', $skills ) . '</li>'; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $items = get_post_meta( $post->ID, '_candidate_education', true ) ) : ?>
				<h2><?php _e( 'Education', 'resume_manager' ); ?></h2>
				<dl class="resume-manager-education">
				<?php
					foreach( $items as $item ) : ?>

						<dt>
							<h3><?php echo esc_html( $item['location'] ); ?></h3>
						</dt>
						<dd>
							<small class="date"><?php echo esc_html( $item['date'] ); ?></small>
							<strong class="qualification"><?php echo esc_html( $item['qualification'] ); ?></strong>
							<?php echo wpautop( wptexturize( $item['notes'] ) ); ?>
						</dd>

					<?php endforeach;
				?>
				</dl>
			<?php endif; ?>

			<?php if ( $items = get_post_meta( $post->ID, '_candidate_experience', true ) ) : ?>
				<h2><?php _e( 'Experience', 'resume_manager' ); ?></h2>
				<dl class="resume-manager-experience">
				<?php
					foreach( $items as $item ) : ?>

						<dt>
							<h3><?php echo esc_html( $item['employer'] ); ?></h3>
						</dt>
						<dd>
							<small class="date"><?php echo esc_html( $item['date'] ); ?></small>
							<strong class="job_title"><?php echo esc_html( $item['job_title'] ); ?></strong>
							<?php echo wpautop( wptexturize( $item['notes'] ) ); ?>
						</dd>

					<?php endforeach;
				?>
				</dl>
			<?php endif; ?>
		</div>

		<div class="resume-aside">
			<?php the_candidate_photo(); ?>

			<?php get_job_manager_template( 'contact-details.php', array( 'post' => $post ), 'resume_manager', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>

			<?php if ( $items = get_post_meta( $post->ID, '_links', true ) ) : ?>
				<ul class="resume-links">
					<?php foreach( $items as $item ) :
						$parsed_url = parse_url( $item['url'] );
						$host       = current( explode( '.', $parsed_url['host'] ) );
					?>
						<li class="resume-link resume-link-<?php echo esc_attr( sanitize_title( $host ) ); ?>"><a rel="nofollow" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['name'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<ul class="meta">
				<?php do_action( 'single_resume_meta_start' ); ?>

				<?php if ( get_the_resume_category() ) : ?>
					<?php
						$categories = get_the_terms( $post->ID, 'resume_category' );

						if ( $categories ) : foreach ( $categories as $category ) :
					?>
					<li class="resume-category">
						<a href="<?php echo get_term_link( $category, 'resume_category' ); ?>"><i class="icon-tag"></i> <?php echo $category->name; ?></a>
					</li>
					<?php endforeach; endif; ?>
				<?php endif; ?>

				<?php do_action( 'single_resume_meta_end' ); ?>
			</ul>

			<?php get_template_part( 'content-share' ); ?>
		</div>
	</div>
<?php else : ?>

	<?php get_job_manager_template_part( 'access-denied', 'single-resume', 'resume_manager', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>

<?php endif; ?>