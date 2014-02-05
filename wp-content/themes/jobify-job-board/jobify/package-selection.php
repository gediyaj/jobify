<?php if ( $packages || $user_packages ) : ?>

	<?php if ( $user_packages ) : ?>

	<h3 class="existing-packages"><?php _e( 'Your Existing Packages', 'jobify' ); ?></h3>

	<div class="pricing-table-widget-<?php echo count( $user_packages ); ?>">

		<?php foreach ( $user_packages as $key => $package ) : $product = get_product( $package->product_id ); ?>
			<div class="pricing-table-widget woocommerce">
				<div class="pricing-table-widget-title">
					<input type="radio" name="job_package" value="user-<?php echo $key; ?>" id="user-package-<?php echo $package->id; ?>" />
					<label for="user-package-<?php echo $package->id; ?>"><?php if ( $product ) echo $product->get_title(); else echo '-'; ?></label>
				</div>

				<div class="pricing-table-widget-description">
					<p><span class="rcp_level_duration">
						<?php
							printf( _n( '%s job posted out of %d', '%s jobs posted out of %s', $package->job_count, 'job_manager_wcpl' ) . ', ', $package->job_count, $package->job_limit );

							printf( _n( 'listed for %s day', 'listed for %s days', $package->job_duration, 'job_manager_wcpl' ), $package->job_duration );
						?>
					</span></p>

					<?php echo apply_filters( 'the_content', get_post_field( 'post_content', $product->id ) ); ?>
				</div>
			</div>
		<?php endforeach; ?>

	</div>

	<?php endif; ?>

	<div class="pricing-table-widget-<?php echo count( $packages ); ?>">

		<?php foreach ( $packages as $key => $package ) : $product = get_product( $package ); ?>
			<div class="pricing-table-widget woocommerce">
				<div class="pricing-table-widget-title">
					<input type="radio" <?php checked( $key, 0 ); ?> name="job_package" value="<?php echo $product->id; ?>" id="package-<?php echo $product->id; ?>" />
					<label for="package-<?php echo $product->id; ?>"><?php echo $product->get_title(); ?></label>
				</div>

				<div class="pricing-table-widget-description">
					<h2><?php echo $product->get_price_html(); ?></h2>

					<p><span class="rcp_level_duration">
						<?php
							printf( _n( '%d job', '%s jobs', $product->get_limit(), 'jobify' ) . ' ', $product->get_limit() );

							printf( _n( 'listed for %s day', 'listed for %s days', $product->get_duration(), 'jobify' ), $product->get_duration() );
						?>
					</span></p>

					<?php echo apply_filters( 'the_content', get_post_field( 'post_content', $product->id ) ); ?>
				</div>
			</div>
		<?php endforeach; ?>

	</div>

<?php else : ?>

	<p><?php _e( 'No packages found', 'job_manager_wcpl' ); ?></p>

<?php endif; ?>