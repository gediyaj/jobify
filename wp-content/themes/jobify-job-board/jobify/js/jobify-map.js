Jobify.Map = ( function($) {
	var $map;

	function setupMap() {
		$map       = $( '#jobify-map-canvas' );

		$map.gmap( {
			mapTypeId          : google.maps.MapTypeId.ROADMAP,
			streetViewControl  : false,
			scrollwheel        : false,
			center             : new google.maps.LatLng( jobifyMapSettings.center.lat, jobifyMapSettings.center.long ),
			zoom               : jobifyMapSettings.zoom == 'auto' ? 8 : parseFloat( jobifyMapSettings.zoom ),
			zoomControlOptions : {
				position : google.maps.ControlPosition.LEFT_CENTER
			}
		} ).bind( 'init', function(evt, map) {
			if ( ! $( 'body' ).hasClass( 'page-template-page-templatesmap-jobs-php' ) ) {
				$( '.map-filter' ).show();
			}
		} );
	}

	function addLocations( points ) {
		$.each( points, function( index, value ) {
			var _item = value;

			if ( ! _item.location )
				return;

			$map.gmap( 'addMarker', {
				'position'  : new google.maps.LatLng( _item.location[0], _item.location[1] ),
				'bounds'    : jobifyMapSettings.center.long && ( jobifyMapSettings.zoom != 'auto' ) ? false : true,
				'animation' : google.maps.Animation.DROP,
				'title'     : _item.title,
				'tooltip'   : false
			}, function(map, marker) {
				new Tooltip({
					marker   : marker,
					content  : _item.title,
					cssClass : 'map-tooltip'
				});
			}).click(function(event, map) {
				window.location = _item.permalink;
			});
		});
	}

	function bindFilter() {
		var data,
		    xhr;

		$( '.live-map' ).submit(function() {

			var keywords, location, category = '';

			keywords = $( '#search_keywords' ).val();
			keywords = keywords === '' ? $( '.job_listings #search_keywords' ).val() : keywords;

			location = $( '#search_location' ).val();
			location = location === '' ? $( '.job_listings #search_location' ).val() : location;

			category = $( '#search_category' ).val();
			category = category === '' ? $( '.job_listings #search_category' ).val() : category;

			data = {
				'action'          : 'jobify_update_map',
				'search_keywords' : keywords,
				'search_location' : location,
				'search_category' : category
			}

			xhr = $.ajax({
				type    : 'POST',
				url     : jobifySettings.ajaxurl,
				data    : data,
				success : function( response ) {
					$( '#jobify-map-canvas' ).gmap( 'clear', 'markers' );

					addLocations( $.parseJSON( response ), null );
				}
			});

			return false;
		});
	}

	return {
		init : function() {
			setupMap();
			bindFilter();

			$( '.live-map' ).trigger( 'submit' );

			$( '#search_keywords, #search_location, #search_categories' ).change( function() {
				if ( '' === $(this).val() ) {
					return;
				}

				$( '.live-map' ).trigger( 'submit' );
			});
		}
	}
} )(jQuery);

jQuery( document ).ready(function($) {
	Jobify.Map.init();
});