/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 * Things like site title and description changes.
 */

( function( $ ) {
	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title' ).text( to );
		} );
	} );

	// Header text color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			$( '.site-title, .nav-menu-primary ul li a, .nav-menu-primary li a' ).css( 'color', to );
			$( '.nav-menu-primary li.login a' ).css( 'border-color', to );
		} );
	} );

	// Primary color.
	wp.customize( 'primary', function( value ) {
		value.bind( function( to ) {
			$( '.button:hover, a.button-secondary, .load_more_jobs, #wp-submit:hover' ).css( 'color', to );

			$( '.site-header, .button, .button-secondary:hover, .search_jobs, .load_more_jobs:hover, .paginate-links .page-numbers:hover, #wp-submit, button.mfp-close, .nav-menu-primary .sub-menu, .nav-menu-primary .children' ).css( 'background-color', to );

			$( '.button:hover, a.button-secondary, .load_more_jobs, .paginate-links .page-numbers:hover, input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, input[type="search"]:focus, input[type="number"]:focus, select:focus, textarea:focus, #wp-submit:hover' ).css( 'border-color', to );
		} );
	} );

	wp.customize( 'jobify_cta_text_color', function( value ) {
		value.bind( function( to ) {
			$( '.footer-cta' ).css( 'color', to );
		} );
	} );

	wp.customize( 'jobify_cta_background_color', function( value ) {
		value.bind( function( to ) {
			$( '.footer-cta' ).css( 'background-color', to );
		} );
	} );
} )( jQuery );
