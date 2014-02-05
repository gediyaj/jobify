/**
 * Functionality specific to Jobify
 *
 * Provides helper functions to enhance the theme experience.
 */

var Jobify = {}

Jobify.App = ( function($) {
	var currentPopup;

	function avoidSubmission() {
		$( '.job_filters, .resume_filters' ).submit(function(e) {
			return false;
		});
	}

	function mobileMenu() {
		$( '.primary-menu-toggle' ).click(function(e){
			e.preventDefault();

			$( '.site-primary-navigation, .primary-menu-toggle.in-header, .site-branding' ).toggleClass( 'open' );
		});
	}

	function wooButtons() {
		$( '.woocommerce .button' ).removeClass( 'button' ).addClass( 'button-secondary button-small' );
	}

	return {
		init : function() {
			avoidSubmission();
			mobileMenu();
			wooButtons();

			$('body').bind( 'init_checkout update_checkout updated_checkout', function() {
				console.log( 'wat' );
				wooButtons();
			});

			$( '.field, .search_category, .search_categories' )
				.find( 'select:not([multiple])' )
				.wrap( '<div class="select"></div>' )
				.parents( '.field' )
				.addClass( 'has-select' );

			$( '.site-primary-navigation .login a, .nav-menu-primary .register a' ).click(function(e) {
				e.preventDefault();

				if ( currentPopup )
					currentPopup.close();

				currenetPopup = Jobify.App.popup({
					items : {
						src : '#' + $(this).parent().attr( 'id' ) + '-wrap'
					}
				});
			});

			$( '.rcp_subscription_level' ).click(function(e) {
				e.preventDefault();

				$( '.rcp_subscription_level' ).removeClass( 'selected' );

				$(this)
					.addClass( 'selected' )
					.find( 'input[type="radio"]' )
					.attr( 'checked', true );
			});

			$( '.rcp_subscription_level_fake' ).click(function(e) {
				e.preventDefault();

				window.location = $(this).data( 'href' );
			});

			$( '.open-share-popup' ).click(function(e) {
				e.preventDefault();

				$(this).prev().fadeToggle( 'fast' );
			});
		},

		popup : function( args ) {
			return $.magnificPopup.open( $.extend( args, {
				type            : 'inline',
				fixedContentPos : false,
				removalDelay    : 500
			} ) );
		},

		/**
		 * Check if we are on a mobile device (or any size smaller than 980).
		 * Called once initially, and each time the page is resized.
		 */
		isMobile : function( width ) {
			var isMobile = false;

			var width = 1180;

			if ( $(window).width() <= width )
				isMobile = true;

			return isMobile;
		}
	}
} )(jQuery);

Jobify.Widgets = ( function($) {
	return {
		init : function() {
			if ( jobifySettings.pages.is_widget_home ) {
				$.each( jobifySettings.widgets, function(m, value) {
					var fn = Jobify.Widgets[m];

					if ( typeof fn === 'function' )
						fn();
				} );
			}

			if ( jobifySettings.pages.is_testimonials ) {
				Jobify.Widgets.jobify_widget_testimonials();
			}
		},

		jobify_widget_companies : function() {
			var companySlider = $( '.company-slider' ).flexslider({
				selector   : '.testimonials-list .company-slider-item',
				controlNav : false,
				animation  :  'slide',
				prevText   : '<i class="icon-left-open"></i>',
				nextText   : '<i class="icon-right-open"></i>',
				maxItems   : 5,
				minItems   : 1,
				itemWidth  : 200,
				slideshow  : false,
				move       : 1
			});

			return true;
		},

		jobify_widget_testimonials : function() {
			if ( jobifySettings.widgets.jobify_widget_testimonials && jobifySettings.widgets.jobify_widget_testimonials.animate && ! $( '.testimonial-slider-wrap' ).hasClass( 'static' ) ) {
				$( '.jobify_widget_testimonials' ).waypoint(function(direction) {
					if ( 'down' != direction )
						return;

					$( '.testimonials-list blockquote' ).each(function(i) {
						var _el = $(this);

						setTimeout(function(){
							_el
								.addClass( 'animated fadeInUp' )
						}, i * 400);
					});
				}, { 'offset' : '50%' } );
			}

			var testimonialSlider = $( '.testimonial-slider' ).flexslider({
				selector   : '.testimonials-list .individual-testimonial',
				controlNav : false,
				animation  :  'slide',
				prevText   : '<i class="icon-left-open"></i>',
				nextText   : '<i class="icon-right-open"></i>',
				maxItems   : 4,
				minItems   : 1,
				itemWidth  : 220,
				slideshow  : false,
				move       : 1
			});
		},

		jobify_widget_stats : function() {
			if ( jobifySettings.widgets.jobify_widget_stats.animate === 0 )
				return;

			$( '.jobify_widget_stats' ).waypoint(function(direction) {
				if ( 'down' != direction )
					return;

				$( '.job-stats li strong' ).each(function(i) {
					$(this).delay(500 * i).queue(function(next){
						$(this).addClass( 'animated bounceIn' );
					});
				});
			}, {
				triggerOnce : true,
				offset      : '50%'
			});
		},

		jobify_widget_video : function() {
			if ( jobifySettings.widgets.jobify_widget_video.animate === 0 )
				return;

			$( '.jobify_widget_video' ).waypoint(function(direction) {
				if ( 'down' != direction )
					return;

				$( '.video-preview' ).fadeIn().addClass( 'animated fadeInRightBig' );
			}, { 'offset' : '50%' } );
		},

		count : function($this){
			var current = parseInt( $this.html(), 10 ),
			    goal    = $this.data( 'count' );

			if ( 0 == goal )
				return;

			$this.html(++current);

			if ( current !== goal ) {
				setTimeout( function(){
					Jobify.Widgets.count( $this )
				}, 75 );
			}

			return this;
		}
	}
} )(jQuery);

Jobify.Jobs = ( function($) {
	var $applicationDetails;

	function applyForJob() {
		$( '.application_button' ).click( function(e) {
			e.preventDefault();

			$applicationDetails.show();

			Jobify.App.popup({
				items : {
					src : $applicationDetails
				}
			});

			return false;
		} );
	}

	return {
		init : function() {
			$applicationDetails = $( '.application_details' );

			$applicationDetails.hide();

			applyForJob();
		}
	}
} )(jQuery);

Jobify.Resumes = ( function($) {
	var $resumeDetails;

	function applyForResume() {
		$( '.resume_contact_button' ).click( function(e) {
			e.preventDefault();

			$resumeDetails.show();

			Jobify.App.popup({
				items : {
					src : $resumeDetails
				}
			});

			return false;
		} );
	}

	return {
		init : function() {
			$resumeDetails = $( '.resume_contact_details' );

			$resumeDetails.hide();

			applyForResume();
		}
	}
} )(jQuery);

jQuery( document ).ready(function($) {
	Jobify.App.init();

	Jobify.Widgets.init();

	if ( jobifySettings.pages.is_job )
		Jobify.Jobs.init();

	if ( jobifySettings.pages.is_resume )
		Jobify.Resumes.init();
});