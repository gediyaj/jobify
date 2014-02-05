<?php
/**
 * login
 *
 * @package Jobify
 * @since Jobify 1.0
 */

$login = jobify_find_page_with_shortcode( array( 'jobify_login_form', 'login_form' ) );
$login = get_post( $login );
?>

<div id="login-modal-wrap" class="modal-login modal">
	<h2 class="modal-title"><?php echo esc_attr( $login->post_title ); ?></h2>

	<?php echo do_shortcode( get_post_field( 'post_content', $login->ID ) ); ?>
</div>
