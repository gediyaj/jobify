/**
 * jQuery to power image uploads, modifications and removals.
 *
 * The object passed to this script file via wp_localize_script is
 * soliloquy.
 *
 * @package   TGM-Soliloquy
 * @version   1.0.0
 * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @copyright Copyright (c) 2012, Thomas Griffin
 */
jQuery(document).ready(function($) {

	/** Prepare formfield variable */
	var formfield;

	/** Hide elements on page load */
	$('.soliloquy-image-meta').hide();

	/** Set default post meta fields */
	if ( $('#soliloquy-width').length > 0 && 0 == $('#soliloquy-width').val().length ) {
		$('#soliloquy-width').val(soliloquy.width);
	}

	if ( $('#soliloquy-height').length > 0 && 0 == $('#soliloquy-height').val().length ) {
		$('#soliloquy-height').val(soliloquy.height);
	}

	if ( $('#soliloquy-speed').length > 0 && 0 == $('#soliloquy-speed').val().length ) {
		$('#soliloquy-speed').val(soliloquy.speed);
	}

	if ( $('#soliloquy-duration').length > 0 && 0 == $('#soliloquy-duration').val().length ) {
		$('#soliloquy-duration').val(soliloquy.duration);
	}

	/** Process fadeToggle for slider size explanation */
	$('.soliloquy-size-more').on('click.soliloquySizeExplain', function(e) {
		e.preventDefault();
		$('#soliloquy-explain-size').fadeToggle();
	});

	/** Process image removals */
	$('#soliloquy-area').on('click.soliloquyRemove', '.remove-image', function(e) {
		e.preventDefault();
		formfield = $(this).parent().attr('id');

		/** Output loading icon and message */
		$('#soliloquy-upload').after('<span class="soliloquy-waiting"><img class="spinner" src="' + soliloquy.spinner + '" width="16px" height="16px" style="margin: 0 5px; vertical-align: bottom;" />' + soliloquy.removing + '</span>');

		/** Prepare our data to be sent via Ajax */
		var remove = {
			action: 		'soliloquy_remove_images',
			attachment_id: 	formfield,
			nonce: 			soliloquy.removenonce
		};

		/** Process the Ajax response and output all the necessary data */
		$.post(
			soliloquy.ajaxurl,
			remove,
			function(response) {
				$('#' + formfield).fadeOut('normal', function() {
					$(this).remove();

					/** Remove the spinner and loading message */
					$('.soliloquy-waiting').fadeOut('normal', function() {
						$(this).remove();
					});
				});
			},
			'json'
		);
	});

	/** Use thickbox to handle image meta fields */
	$('#soliloquy-area').on('click.soliloquyModify', '.modify-image', function(e) {
		e.preventDefault();
		$('html').addClass('soliloquy-editing');
		formfield = $(this).next().attr('id');
		tb_show( soliloquy.modifytb, 'TB_inline?width=640&height=500&inlineId=' + formfield );

		/** Close thickbox if they click the actual close button */
		$(document).contents().find('#TB_closeWindowButton').on('click.soliloquyIframe', function() {
			if( $('html').hasClass('soliloquy-editing') ) {
				$('html').removeClass('soliloquy-editing');
				tb_remove();
			}
		});

		/** Close thickbox if they click the overlay */
		$(document).contents().find('#TB_overlay').on('click.soliloquyIframe', function() {
			if( $('html').hasClass('soliloquy-editing') ) {
				$('html').removeClass('soliloquy-editing');
				tb_remove();
			}
		});

		return false;
	});

	/** Save image meta via Ajax */
	$(document).on('click.soliloquyMeta', '.soliloquy-meta-submit', function(e) {
		e.preventDefault();

		/** Set default meta values that any addon would need */
		var table 		= $(this).parent().find('.soliloquy-meta-table').attr('id');
		var attach 		= table.split('-');
		var attach_id 	= attach[3];

		/** Prepare our data to be sent via Ajax */
		var meta = {
			action: 	'soliloquy_update_meta',
			attach: 	attach_id,
			id: 		soliloquy.id,
			nonce: 		soliloquy.metanonce
		};

		/** Loop through each table item and send data for every item that has a usable class */
		$('#' + table + ' td').each(function() {
			/** Grab all the items within each td element */
			var children = $(this).find('*');

			/** Loop through each child element */
			$.each(children, function() {
				var field_class = $(this).attr('class');
				var field_val 	= $(this).val();

				if ( 'checkbox' == $(this).attr('type') )
					var field_val = $(this).is(':checked') ? 'true' : 'false';

				/** Store all data in the meta object */
				meta[field_class] = field_val;
			});
		});

		/** Output loading icon and message */
		$(this).after('<span class="soliloquy-waiting"><img class="spinner" src="' + soliloquy.spinner + '" width="16px" height="16px" style="margin: 0 5px; vertical-align: middle;" />' + soliloquy.saving + '</span>');

		/** Process the Ajax response and output all the necessary data */
		$.post(
			soliloquy.ajaxurl,
			meta,
			function(response) {
				/** Remove the spinner and loading message */
				$('.soliloquy-waiting').fadeOut('normal', function() {
					$(this).remove();
				});

				/** Remove thickbox with a slight delay */
				var metaTimeout = setTimeout(function() {
					$('html').removeClass('soliloquy-editing');
					tb_remove();
				}, 1000);
			},
			'json'
		);
	});

	/** Use thickbox to handle image uploads */
	$('#soliloquy-area').on('click.soliloquyUpload', '#soliloquy-upload', function(e) {
		e.preventDefault();
		$('html').addClass('soliloquy-uploading');
		formfield = $(this).parent().prev().attr('name');
 		tb_show( soliloquy.upload, 'media-upload.php?post_id=' + soliloquy.id + '&type=image&context=soliloquy-image-uploads&TB_iframe=true&width=640&height=500' );

 		/** Refresh image list and meta if a user selects to save changes instead of insert into the slider gallery */
		$(document).contents().find('#TB_closeWindowButton').on('click.soliloquyIframe', function() {
			/** Refresh if they click the actual close button */
			if( $('html').hasClass('soliloquy-uploading') ) {
				$('html').removeClass('soliloquy-uploading');
				tb_remove();
				soliloquyRefresh();
			}
		});

		/** Refresh if they click the overlay */
		$(document).contents().find('#TB_overlay').on('click.soliloquyIframe', function() {
			if( $('html').hasClass('soliloquy-uploading') ) {
				$('html').removeClass('soliloquy-uploading');
				tb_remove();
				soliloquyRefresh();
			}
		});

 		return false;
	});

	window.original_send_to_editor = window.send_to_editor;

	/** Send out an ajax call to refresh the image attachment list */
	window.send_to_editor = function(html) {
		if (formfield) {
			/** Remove thickbox and extra html class */
			tb_remove();
			$('html').removeClass('soliloquy-uploading');

			/** Delay the processing of the refresh until thickbox has closed */
			var timeout = setTimeout(function() {
				soliloquyRefresh();
			}, 1500); // End timeout function
		}
		else {
 			window.original_send_to_editor(html);
 		}
	};

	/** Reset variables */
	var formfield 	= '';
	var remove 		= '';
	var table 		= '';
	var attach 		= '';
	var attach_id 	= '';
	var meta 		= '';
	var metaTimeout = '';
	var timeout 	= '';
	var refresh 	= '';

	/** Make image uploads sortable */
	var items = $('#soliloquy-images');

	/** Use Ajax to update the item order */
	if ( 0 !== items.length ) {
		items.sortable({
			containment: '#soliloquy-area',
			update: function(event, ui) {
				/** Show the loading text and icon */
				$('.soliloquy-waiting').show();

				/** Prepare our data to be sent via Ajax */
				var opts = {
					url: 		soliloquy.ajaxurl,
                	type: 		'post',
                	async: 		true,
                	cache: 		false,
                	dataType: 	'json',
                	data:{
                    	action: 	'soliloquy_sort_images',
						order: 		items.sortable('toArray').toString(),
						post_id: 	soliloquy.id,
						nonce: 		soliloquy.sortnonce
                	},
                	success: function(response) {
                    	$('.soliloquy-waiting').hide();
                    	return;
                	},
                	error: function(xhr, textStatus ,e) {
                    	$('.soliloquy-waiting').hide();
                    	return;
                	}
            	};
            	$.ajax(opts);
			}
		});
	}

	/** jQuery function for loading the image uploads */
	function soliloquyRefresh() {
		/** Prepare our data to be sent via Ajax */
		var refresh = {
			action: 'soliloquy_refresh_images',
			id: 	soliloquy.id,
			nonce: 	soliloquy.nonce
		};
		var output = '';

		/** Output loading icon and message */
		$('#soliloquy-upload').after('<span class="soliloquy-waiting"><img class="spinner" src="' + soliloquy.spinner + '" width="16px" height="16px" style="margin: 0 5px; vertical-align: bottom;" />' + soliloquy.loading + '</span>');

		/** Process the Ajax response and output all the necessary data */
		$.post(
			soliloquy.ajaxurl,
			refresh,
			function(json) {
				/** Loop through the object */
				$.each(json.images, function(i, object) {
					/** Store each image and its data into the image variable */
					var image = json.images[i];

					/** Store the output into a variable */
					output +=
						'<li id="' + image.id + '" class="soliloquy-image attachment-' + image.id + '">' +
							'<img src="' + image.src + '" width="' + image.width + '" height="' + image.height + '" />' +
							'<a href="#" class="remove-image" title="' + soliloquy.remove + '"></a>' +
							'<a href="#" class="modify-image" title="' + soliloquy.modify + '"></a>' +
							'<div id="meta-' + image.id + '" class="soliloquy-image-meta" style="display: none;">' +
								'<div class="soliloquy-meta-wrap">' +
									'<h2>' + soliloquy.metatitle + '</h2>' +
									'<p>' + soliloquy.metadesc + '</p>';
									if ( image.before_image_meta_table ) {
										$.each(image.before_image_meta_table, function(i, array) {
											output += image.before_image_meta_table[i];
										});
									}
									output += '<table id="soliloquy-meta-table-' + image.id + '" class="form-table soliloquy-meta-table">' +
										'<tbody>';
											if ( image.before_image_title ) {
												$.each(image.before_image_title, function(i, array) {
													output += image.before_image_title[i];
												});
											}
											output += '<tr id="soliloquy-title-box-' + image.id + '" valign="middle">' +
												'<th scope="row">' + soliloquy.title + '</th>' +
												'<td>' +
													'<input id="soliloquy-title-' + image.id + '" class="soliloquy-title" type="text" size="75" name="_soliloquy_uploads[title]" value="' + image.title + '" />' +
												'</td>' +
											'</tr>';
											if ( image.before_image_alt ) {
												$.each(image.before_image_alt, function(i, array) {
													output += image.before_image_alt[i];
												});
											}
											output += '<tr id="soliloquy-alt-box-' + image.id + '" valign="middle">' +
												'<th scope="row">' + soliloquy.alt + '</th>' +
												'<td>' +
													'<input id="soliloquy-alt-' + image.id + '" class="soliloquy-alt" type="text" size="75" name="_soliloquy_uploads[alt]" value="' + image.alt + '" />' +
												'</td>' +
											'</tr>';
											if ( image.before_image_link ) {
												$.each(image.before_image_link, function(i, array) {
													output += image.before_image_link[i];
												});
											}
											output += '<tr id="soliloquy-link-box-' + image.id + '" valign="middle">' +
												'<th scope="row">' + soliloquy.link + '</th>' +
												'<td>' +
													'<label class="soliloquy-link-url">' + soliloquy.url + '</label>' +
													'<input id="soliloquy-link-' + image.id + '" class="soliloquy-link" type="text" size="70" name="_soliloquy_uploads[link]" value="' + image.link + '" />' +
													'<label class="soliloquy-link-title-label">' + soliloquy.linktitle + '</label>' +
													'<input id="soliloquy-link-title-' + image.id + '" class="soliloquy-link-title" type="text" size="40" name="_soliloquy_uploads[link_title]" value="' + image.linktitle + '" />' +
													'<input id="soliloquy-link-tab-' + image.id + '" class="soliloquy-link-check" type="checkbox" name="_soliloquy_uploads[link_tab]" value="' + image.linktab + '"' + image.linkcheck + ' />' +
													'<span class="description">' + soliloquy.tab + '</span>' +
												'</td>' +
											'</tr>';
											if ( image.before_image_caption ) {
												$.each(image.before_image_caption, function(i, array) {
													output += image.before_image_caption[i];
												});
											}
											output += '<tr id="soliloquy-caption-box-' + image.id + '" valign="middle">' +
												'<th scope="row">' + soliloquy.caption + '</th>' +
												'<td>' +
													'<textarea id="soliloquy-caption-' + image.id + '" class="soliloquy-caption" rows="3" cols="75" name="_soliloquy_uploads[caption]">' + image.caption + '</textarea>' +
												'</td>' +
											'</tr>';
											if ( image.after_meta_defaults ) {
												$.each(image.after_meta_defaults, function(i, array) {
													output += image.after_meta_defaults[i];
												});
											}
										output += '</tbody>' +
									'</table>';
									if ( image.after_image_meta_table ) {
										$.each(image.after_image_meta_table, function(i, array) {
											output += image.after_image_meta_table[i];
										});
									}
									output += '<a href="#" class="soliloquy-meta-submit button-secondary" title="' + soliloquy.savemeta + '">' + soliloquy.savemeta + '</a>' +
								'</div>' +
							'</div>' +
						'</li>';
				});

				/** Load the new HTML with the newly uploaded images */
				$('#soliloquy-images').html(output);

				/** Hide the image meta when refreshing the list */
				$('.soliloquy-image-meta').hide();
			},
			'json'
		);

		/** Remove the spinner and loading message */
		$('.soliloquy-waiting').fadeOut('normal', function() {
			$(this).remove();
		});
	}

	/** Handle dismissing of upgrade notice */
	$('#soliloquy-dismiss-notice').on('click.soliloquyDismiss', function(e){
		/** Prevent the default action from occurring */
		e.preventDefault();

		/** Prepare our data to be sent via Ajax */
		var opts = {
			url: 		soliloquy.ajaxurl,
            type: 		'post',
            async: 		true,
            cache: 		false,
            dataType: 	'json',
            data:{
                action: 	'soliloquy_dismiss_notice',
				nonce: 		soliloquy.dismissnonce
            },
            success: function(response) {
                $('#setting-error-tgmsp-upgrade-soliloquy').fadeOut();
                return;
            },
            error: function(xhr, textStatus ,e) {
                return;
            }
        };
        $.ajax(opts);
	});

});