<?php
/**
 * Admin class for Soliloquy Lite.
 *
 * @since 1.0.0
 *
 * @package	Soliloquy Lite
 * @author	Thomas Griffin
 */
class Tgmsp_Lite_Admin {

	/**
	 * Holds a copy of the object for easy reference.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		self::$instance = $this;

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_seo_support' ), 99 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

	}

	/**
	 * Deactivate Soliloquy Lite if the full version is installed and active.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {

		/** If the main Soliloquy plugin exists, update default post meta fields and deactivate ourself in favor of the full version */
		if ( class_exists( 'Tgmsp', false ) ) {
			/** Get current sliders and update default post meta fields */
			$sliders = get_posts( array( 'post_type' => 'soliloquy', 'posts_per_page' => -1 ) );
			if ( $sliders ) {
				foreach ( (array) $sliders as $slider ) {
					/** Grab Soliloquy meta from the slider */
					$meta = get_post_meta( $slider->ID, '_soliloquy_settings', true );

					/** Set default post meta fields */
					if ( empty( $meta['default'] ) ) 	$meta['default'] 	= 'default';
					if ( empty( $meta['custom'] ) ) 	$meta['custom'] 	= false;
					if ( empty( $meta['animate'] ) ) 	$meta['animate'] 	= 1;
					if ( empty( $meta['video'] ) ) 		$meta['video'] 		= 1;
					if ( empty( $meta['navigation'] ) ) $meta['navigation'] = 1;
					if ( empty( $meta['control'] ) ) 	$meta['control'] 	= 1;
					if ( empty( $meta['keyboard'] ) ) 	$meta['keyboard'] 	= 1;
					if ( empty( $meta['number'] ) ) 	$meta['number'] 	= 0;
					if ( empty( $meta['loop'] ) ) 		$meta['loop'] 		= 1;
					if ( empty( $meta['action'] ) ) 	$meta['action'] 	= 1;
					if ( empty( $meta['css'] ) ) 		$meta['css'] 		= 1;
					if ( empty( $meta['animate'] ) ) 	$meta['animate'] 	= 1;
					if ( empty( $meta['smooth'] ) ) 	$meta['smooth'] 	= 1;
					if ( empty( $meta['touch'] ) ) 		$meta['touch'] 		= 1;
					if ( empty( $meta['delay'] ) ) 		$meta['delay'] 		= 0;
					if ( empty( $meta['type'] ) ) 		$meta['type'] 		= 'default';
					if ( empty( $meta['preloader'] ) )  $meta['preloader']  = 0;

					/** Update post meta for the slider */
					update_post_meta( $slider->ID, '_soliloquy_settings', $meta );
				}
			}

			/** Deactive the plugin */
			deactivate_plugins( Tgmsp_Lite::get_file() );
		}

	}

	/**
	 * There is no need to apply SEO to the Soliloquy post type, so we check to
	 * see if some popular SEO plugins are installed, and if so, remove the inpost
	 * meta boxes from view.
	 *
	 * This method also has a filter that can be used to remove any unwanted metaboxes
	 * from the Soliloquy screen - tgmsp_remove_metaboxes.
	 *
	 * @since 1.0.0
	 */
	public function remove_seo_support() {

		$plugins = array(
			array( 'WPSEO_Metabox', 'wpseo_meta', 'normal' ),
			array( 'All_in_One_SEO_Pack', 'aiosp', 'advanced' ),
			array( 'Platinum_SEO_Pack', 'postpsp', 'normal' ),
			array( 'SEO_Ultimate', 'su_postmeta', 'normal' )
		);
		$plugins = apply_filters( 'tgmsp_remove_metaboxes', $plugins );

		/** Loop through the arrays and remove the metaboxes */
		foreach ( $plugins as $plugin )
			if ( class_exists( $plugin[0] ) )
				remove_meta_box( $plugin[1], convert_to_screen( 'soliloquy' ), $plugin[2] );

	}

	/**
	 * Add the metaboxes to the Soliloquy edit screen.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {

		add_meta_box( 'soliloquy_uploads', Tgmsp_Lite_Strings::get_instance()->strings['meta_uploads'], array( $this, 'soliloquy_uploads' ), 'soliloquy', 'normal', 'high' );
		add_meta_box( 'soliloquy_settings', Tgmsp_Lite_Strings::get_instance()->strings['meta_settings'], array( $this, 'soliloquy_settings' ), 'soliloquy', 'normal', 'high' );
		add_meta_box( 'soliloquy_upgrade', Tgmsp_Lite_Strings::get_instance()->strings['meta_upgrade'], array( $this, 'soliloquy_upgrade' ), 'soliloquy', 'side', 'core' );
		add_meta_box( 'soliloquy_instructions', Tgmsp_Lite_Strings::get_instance()->strings['meta_instructions'], array( $this, 'soliloquy_instructions' ), 'soliloquy', 'side', 'core' );

	}

	/**
	 * Callback function for Soliloquy image uploads.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post Current post object data
	 */
	public function soliloquy_uploads( $post ) {

		/** Always keep security first */
		wp_nonce_field( 'soliloquy_uploads', 'soliloquy_uploads' );

		?>
		<input id="soliloquy-uploads" type="hidden" name="soliloquy-uploads" value="1" />
		<div id="soliloquy-area">
			<p><?php echo Tgmsp_Lite_Strings::get_instance()->strings['upload_info']; ?></p>
			<a href="#" id="soliloquy-upload" class="button-secondary" title="<?php echo esc_attr( Tgmsp_Lite_Strings::get_instance()->strings['upload_images'] ); ?>"><?php echo esc_html( Tgmsp_Lite_Strings::get_instance()->strings['upload_images'] ); ?></a>

			<ul id="soliloquy-images">
				<?php
					/** List out all image attachments for the slider */
					$args = apply_filters( 'tgmsp_list_images_args', array(
						'orderby' 			=> 'menu_order',
						'order' 			=> 'ASC',
						'post_type' 		=> 'attachment',
						'post_parent' 		=> $post->ID,
						'post_mime_type' 	=> 'image',
						'post_status' 		=> null,
						'posts_per_page' 	=> -1
					) );
					$attachments = get_posts( $args );

					if ( $attachments ) {
						foreach ( $attachments as $attachment ) {
							echo '<li id="' . $attachment->ID . '" class="soliloquy-image attachment-' . $attachment->ID . '">';
								echo wp_get_attachment_image( $attachment->ID, 'soliloquy-thumb' );
								echo '<a href="#" class="remove-image" title="' . Tgmsp_Lite_Strings::get_instance()->strings['remove_image'] . '"></a>';
								echo '<a href="#" class="modify-image" title="' . Tgmsp_Lite_Strings::get_instance()->strings['modify_image'] . '"></a>';

								/** Begin outputting the meta information for each image */
								echo '<div id="meta-' . $attachment->ID . '" class="soliloquy-image-meta" style="display: none;">';
									echo '<div class="soliloquy-meta-wrap">';
										echo '<h2>' . Tgmsp_Lite_Strings::get_instance()->strings['update_meta'] . '</h2>';
										echo '<p>' . Tgmsp_Lite_Strings::get_instance()->strings['image_meta'] . '</p>';
										do_action( 'tgmsp_before_image_meta_table', $attachment );
										echo '<table id="soliloquy-meta-table-' . $attachment->ID . '" class="form-table soliloquy-meta-table">';
											echo '<tbody>';
												do_action( 'tgmsp_before_image_title', $attachment );
												echo '<tr id="soliloquy-title-box-' . $attachment->ID . '" valign="middle">';
													echo '<th scope="row">' . Tgmsp_Lite_Strings::get_instance()->strings['image_title'] . '</th>';
													echo '<td>';
														echo '<input id="soliloquy-title-' . $attachment->ID . '" class="soliloquy-title" type="text" size="75" name="_soliloquy_uploads[title]" value="' . esc_attr( strip_tags( $attachment->post_title ) ) . '" />';
													echo '</td>';
												echo '</tr>';
												do_action( 'tgmsp_before_image_alt', $attachment );
												echo '<tr id="soliloquy-alt-box-' . $attachment->ID . '" valign="middle">';
													echo '<th scope="row">' . Tgmsp_Lite_Strings::get_instance()->strings['image_alt'] . '</th>';
													echo '<td>';
														echo '<input id="soliloquy-alt-' . $attachment->ID . '" class="soliloquy-alt" type="text" size="75" name="_soliloquy_uploads[alt]" value="' . esc_attr( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) ) . '" />';
													echo '</td>';
												echo '</tr>';
												do_action( 'tgmsp_before_image_link', $attachment );
												echo '<tr id="soliloquy-link-box-' . $attachment->ID . '" valign="middle">';
													echo '<th scope="row">' . Tgmsp_Lite_Strings::get_instance()->strings['image_link'] . '</th>';
													echo '<td>';
														echo '<label class="soliloquy-link-url">' . Tgmsp_Lite_Strings::get_instance()->strings['image_url'] . '</label>';
														echo '<input id="soliloquy-link-' . $attachment->ID . '" class="soliloquy-link" type="text" size="70" name="_soliloquy_uploads[link]" value="' . esc_url( get_post_meta( $attachment->ID, '_soliloquy_image_link', true ) ) . '" />';
														echo '<label class="soliloquy-link-title-label">' . Tgmsp_Lite_Strings::get_instance()->strings['image_url_title'] . '</label>';
														echo '<input id="soliloquy-link-title-' . $attachment->ID . '" class="soliloquy-link-title" type="text" size="40" name="_soliloquy_uploads[link_title]" value="' . esc_attr( strip_tags( get_post_meta( $attachment->ID, '_soliloquy_image_link_title', true ) ) ) . '" />';
														echo '<input id="soliloquy-link-tab-' . $attachment->ID . '" class="soliloquy-link-check" type="checkbox" name="_soliloquy_uploads[link_tab]" value="' . esc_attr( get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ) ) . '"' . checked( get_post_meta( $attachment->ID, '_soliloquy_image_link_tab', true ), 1, false ) . ' />';
														echo '<span class="description">' . Tgmsp_Lite_Strings::get_instance()->strings['new_tab'] . '</span>';
													echo '</td>';
												echo '</tr>';
												do_action( 'tgmsp_before_image_caption', $attachment );
												echo '<tr id="soliloquy-caption-box-' . $attachment->ID . '" valign="middle">';
													echo '<th scope="row">' . Tgmsp_Lite_Strings::get_instance()->strings['image_caption'] . '</th>';
													echo '<td>';
														echo '<textarea id="soliloquy-caption-' . $attachment->ID . '" class="soliloquy-caption" rows="3" cols="75" name="_soliloquy_uploads[caption]">' . esc_html( $attachment->post_excerpt ) . '</textarea>';
													echo '</td>';
												echo '</tr>';
												do_action( 'tgmsp_after_meta_defaults', $attachment );
											echo '</tbody>';
										echo '</table>';
										do_action( 'tgmsp_after_image_meta_table', $attachment );

										echo '<a href="#" class="soliloquy-meta-submit button-secondary" title="' . Tgmsp_Lite_Strings::get_instance()->strings['save_meta'] . '">' . Tgmsp_Lite_Strings::get_instance()->strings['save_meta'] . '</a>';
									echo '</div>';
								echo '</div>';
							echo '</li>';
						}
					}
				?>
			</ul>
		</div><!-- end #soliloquy-area -->
		<?php

	}

	/**
	 * Callback function for Soliloquy settings.
	 *
	 * @since 1.0.0
	 *
	 * @global array $_wp_additional_image_sizes Additional registered image sizes
	 * @param object $post Current post object data
	 */
	public function soliloquy_settings( $post ) {

		global $_wp_additional_image_sizes;

		/** Always keep security first */
		wp_nonce_field( 'soliloquy_settings_script', 'soliloquy_settings_script' );

		do_action( 'tgmsp_before_settings_table', $post );

		?>
		<table class="form-table">
			<tbody>
				<?php do_action( 'tgmsp_before_setting_size', $post ); ?>
				<tr id="soliloquy-size-box" valign="middle">
					<th scope="row"><?php echo Tgmsp_Lite_Strings::get_instance()->strings['slider_size']; ?></th>
					<td>
						<div id="soliloquy-default-sizes">
							<input id="soliloquy-width" type="text" name="_soliloquy_settings[width]" value="<?php echo esc_attr( $this->get_custom_field( '_soliloquy_settings', 'width' ) ); ?>" /> &#215; <input id="soliloquy-height" type="text" name="_soliloquy_settings[height]" value="<?php echo esc_attr( $this->get_custom_field( '_soliloquy_settings', 'height' ) ); ?>" />
							<p class="description"><?php printf( '%s <a class="soliloquy-size-more" href="#">%s</a>', Tgmsp_Lite_Strings::get_instance()->strings['slider_size_desc'], Tgmsp_Lite_Strings::get_instance()->strings['slider_size_more'] ); ?></p>
							<p id="soliloquy-explain-size" class="description" style="display: none;"><?php printf( '%s <a href="%s" target="_blank">%s</a>.', Tgmsp_Lite_Strings::get_instance()->strings['slider_size_explain'], apply_filters( 'tgmsp_affiliate_url', 'http://soliloquywp.com/pricing/?utm_source=orgrepo&utm_medium=link&utm_campaign=Soliloquy%2BLite' ), Tgmsp_Lite_Strings::get_instance()->strings['slider_size_upgrade'] ); ?></p>
						</div>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_transition', $post ); ?>
				<tr id="soliloquy-transition-box" valign="middle">
					<th scope="row"><?php echo Tgmsp_Lite_Strings::get_instance()->strings['slider_transition']; ?></th>
					<td>
					<?php
						$transitions = apply_filters( 'tgmsp_slider_transitions', array( 'fade' ) );
						echo '<select id="soliloquy-transition" name="_soliloquy_settings[transition]">';
							foreach ( $transitions as $transition ) {
								echo '<option value="' . esc_attr( $transition ) . '"' . selected( $transition, $this->get_custom_field( '_soliloquy_settings', 'transition' ), false ) . '>' . esc_html( $transition ) . '</option>';
							}
						echo '</select>';
					?>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_speed', $post ); ?>
				<tr id="soliloquy-speed-box" valign="middle">
					<th scope="row"><?php echo Tgmsp_Lite_Strings::get_instance()->strings['slider_speed']; ?></th>
					<td>
						<input id="soliloquy-speed" type="text" name="_soliloquy_settings[speed]" value="<?php echo esc_attr( $this->get_custom_field( '_soliloquy_settings', 'speed' ) ); ?>" />
						<span class="description"><?php echo Tgmsp_Lite_Strings::get_instance()->strings['slider_milliseconds']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_duration', $post ); ?>
				<tr id="soliloquy-duration-box" valign="middle">
					<th scope="row"><?php echo Tgmsp_Lite_Strings::get_instance()->strings['slider_animation_dur']; ?></th>
					<td>
						<input id="soliloquy-duration" type="text" name="_soliloquy_settings[duration]" value="<?php echo esc_attr( $this->get_custom_field( '_soliloquy_settings', 'duration' ) ); ?>" />
						<span class="description"><?php echo Tgmsp_Lite_Strings::get_instance()->strings['slider_milliseconds']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_before_setting_preloader', $post ); ?>
				<tr id="soliloquy-preloader-box" valign="middle">
					<th scope="row"><label for="soliloquy-preloader"><?php echo Tgmsp_Lite_Strings::get_instance()->strings['slider_preloader']; ?></label></th>
					<td>
						<input id="soliloquy-preloader" type="checkbox" name="_soliloquy_settings[preloader]" value="<?php echo esc_attr( $this->get_custom_field( '_soliloquy_settings', 'preloader' ) ); ?>" <?php checked( $this->get_custom_field( '_soliloquy_settings', 'preloader' ), 1 ); ?> />
						<span class="description"><?php echo Tgmsp_Lite_Strings::get_instance()->strings['slider_preloader_desc']; ?></span>
					</td>
				</tr>
				<?php do_action( 'tgmsp_end_of_settings', $post ); ?>
			</tbody>
		</table>

		<?php do_action( 'tgmsp_after_settings_table', $post ); ?>

		<div class="soliloquy-advanced">
			<p><strong><?php echo sprintf( Tgmsp_Lite_Strings::get_instance()->strings['slider_cb'], sprintf( '<a href="' . apply_filters( 'tgmsp_affiliate_url', 'http://soliloquywp.com/pricing/?utm_source=orgrepo&utm_medium=link&utm_campaign=Soliloquy%2BLite' ) . '" title="%1$s" target="_blank">%1$s</a>', Tgmsp_Lite_Strings::get_instance()->strings['slider_cb_up'] ) ); ?></strong></p>
		</div>
		<?php

		do_action( 'tgmsp_after_settings', $post );

	}

	/**
	 * Callback function for Soliloquy upgrading methods.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post Current post object data
	 */
	public function soliloquy_upgrade( $post ) {

		$upgrade = '<p><strong>' . Tgmsp_Lite_Strings::get_instance()->strings['upgrade'] . '</strong></p>';
		$upgrade .= sprintf( '<p><a href="' . apply_filters( 'tgmsp_affiliate_url', 'http://soliloquywp.com/pricing/?utm_source=orgrepo&utm_medium=link&utm_campaign=Soliloquy%2BLite' ) . '" title="%1$s" target="_blank"><strong>%1$s</strong></a></p>', Tgmsp_Lite_Strings::get_instance()->strings['upgrade_now'] );

		echo $upgrade;

	}

	/**
	 * Callback function for Soliloquy instructions.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post Current post object data
	 */
	public function soliloquy_instructions( $post ) {

		$instructions = '<p>' . Tgmsp_Lite_Strings::get_instance()->strings['instructions'] . '</p>';
		$instructions .= '<p><code>[soliloquy id="' . $post->ID . '"]</code></p>';
		$instructions .= '<p>' . Tgmsp_Lite_Strings::get_instance()->strings['instructions_more'] . '</p>';
		$instructions .= '<p><code>if ( function_exists( \'soliloquy_slider\' ) ) soliloquy_slider( \'' . $post->ID . '\' );</code></p>';

		echo apply_filters( 'tgmsp_slider_instructions', $instructions, $post );

	}

	/**
	 * Outputs any error messages when verifying license keys.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {

		if ( Tgmsp_Lite::is_soliloquy_screen() && current_user_can( 'manage_options' ) ) {
			/** If a user hasn't dismissed the notice yet, output it for them to upgrade */
			if ( ! get_user_meta( get_current_user_id(), 'soliloquy_dismissed_notice', true ) )
				add_settings_error( 'tgmsp', 'tgmsp-upgrade-soliloquy', sprintf( Tgmsp_Lite_Strings::get_instance()->strings['upgrade_nag'], sprintf( '<a href="' . apply_filters( 'tgmsp_affiliate_url', 'http://soliloquywp.com/pricing/?utm_source=orgrepo&utm_medium=link&utm_campaign=Soliloquy%2BLite' ) . '" title="%1$s" target="_blank">%1$s</a>', Tgmsp_Lite_Strings::get_instance()->strings['upgrade_nag_link'] ), sprintf( '<a id="soliloquy-dismiss-notice" href="#" title="%1$s">%1$s</a>', Tgmsp_Lite_Strings::get_instance()->strings['upgrade_nag_dismiss'] ) ), 'updated' );

			/** Allow settings notices to be filtered */
			apply_filters( 'tgmsp_output_notices', settings_errors( 'tgmsp' ) );
		}

	}

	/**
	 * Helper function to get custom field values for the Soliloquy post type.
	 *
	 * @since 1.0.0
	 *
	 * @global int $id The current Soliloquy ID
	 * @global object $post The current Soliloquy post type object
	 * @param string $field The custom field name to retrieve
	 * @param string|int $setting The setting or array index to retrieve within the custom field
	 * @param int $index The array index number to retrieve
	 * @return string|boolean The custom field value on success, false on failure
	 */
	public function get_custom_field( $field, $setting = null, $index = null ) {

		global $id, $post;

		/** Do nothing if the field is not set */
		if ( ! $field )
			return false;

		/** Get the current Soliloquy ID */
		$post_id = ( null === $id ) ? $post->ID : $id;

		$custom_field = get_post_meta( $post_id, $field, true );

		/** Return the sanitized field and setting if an array, otherwise return the sanitized field */
		if ( $custom_field && isset( $custom_field[$setting] ) ) {
			if ( is_int( $index ) && is_array( $custom_field[$setting] ) )
				return stripslashes_deep( $custom_field[$setting][$index] );
			else
				return stripslashes_deep( $custom_field[$setting] );
		} elseif ( is_array( $custom_field ) ) {
			return stripslashes_deep( $custom_field );
		} else {
			return stripslashes( $custom_field );
		}

		return false;

	}

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		return self::$instance;

	}

}