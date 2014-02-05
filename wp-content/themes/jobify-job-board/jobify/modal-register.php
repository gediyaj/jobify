<?php
/**
 * Register
 *
 * @package Jobify
 * @since Jobify 1.0
 */

$register = jobify_find_page_with_shortcode( array( 'jobify_register_form', 'register_form' ) );
$register = get_post( $register );
?>

<div id="register-modal-wrap" class="modal-register modal">
	<h2 class="modal-title"><?php echo esc_attr( $register->post_title ); ?></h2>

	<?php echo do_shortcode( get_post_field( 'post_content', $register->ID ) ); ?>
</div>
