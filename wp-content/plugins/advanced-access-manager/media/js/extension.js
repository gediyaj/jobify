/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

jQuery(document).ready(function() {
    jQuery('#extension_list').dataTable({
        sDom: "<'top'f<'clear'>>t<'footer'p<'clear'>>",
        //bProcessing : false,
        bStateSave: true,
        sPaginationType: "full_numbers",
        bAutoWidth: false,
        bSort: false,
        oLanguage: {
            "sSearch": "",
            "oPaginate": {
                "sFirst": "&Lt;",
                "sLast": "&Gt;",
                "sNext": "&gt;",
                "sPrevious": "&lt;"
            }
        },
        fnDrawCallback: function() {
            jQuery('.extension-action-purchase').each(function() {
                var link = jQuery(this).attr('link');
                var extension = jQuery(this).attr('extension');

                jQuery(this).bind('click', function() {
                    //show the dialog
                    jQuery('#install_extension').dialog({
                        resizable: false,
                        height: 'auto',
                        width: '30%',
                        modal: true,
                        buttons: [
                            {
                                text: 'Purchase',
                                icons: {primary: "ui-icon-cart"},
                                click: function() {
                                    window.open(link, '_blank');
                                }
                            },
                            {
                                text: 'Install',
                                icons: {primary: "ui-icon-check"},
                                click: function() {
                                    var license = jQuery.trim(
                                            jQuery('#license_key').val()
                                    );

                                    if (license) {
                                        //add loader
                                        jQuery('#install_extension').append(jQuery('<div/>', {
                                            'class' : 'loading-extension'
                                        }));

                                        jQuery.ajax(aamLocal.ajaxurl, {
                                            type: 'POST',
                                            dataType: 'json',
                                            data: {
                                                action: 'aam',
                                                sub_action: 'install_extension',
                                                extension: extension,
                                                license: license,
                                                _ajax_nonce: aamLocal.nonce
                                            },
                                            success: function(response) {
                                                if (response.status === 'success') {
                                                    location.reload();
                                                } else {
                                                    jQuery('#license_key').effect('highlight', 2000);
                                                }
                                            },
                                            error: function() {
                                                jQuery('#license_key').effect('highlight', 2000);
                                            },
                                            complete: function(){
                                                jQuery('.loading-extension', '#install_extension').remove();
                                            }
                                        });
                                    } else {
                                        jQuery('#license_key').effect('highlight', 2000);
                                    }
                                }
                            },
                            {
                                text: 'Close',
                                icons: {primary: "ui-icon-close"},
                                click: function() {
                                    jQuery(this).dialog('close');
                                }
                            }
                        ]
                    });
                });
            });
            jQuery('.extension-action-ok').each(function() {
                jQuery(this).bind('click', function() {
                    var license = jQuery(this).attr('license');
                    var extension = jQuery(this).attr('extension');
                    var dialog = this;

                    jQuery('#installed_license_key').html(license);
                    //show the dialog
                    jQuery('#update_extension').dialog({
                        resizable: false,
                        height: 'auto',
                        width: '25%',
                        modal: true,
                        buttons: [
                            {
                                text: 'Remove',
                                icons: {primary: "ui-icon-trash"},
                                click: function() {
                                    jQuery.ajax(aamLocal.ajaxurl, {
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            action: 'aam',
                                            sub_action: 'remove_extension',
                                            extension: extension,
                                            license: license,
                                            _ajax_nonce: aamLocal.nonce
                                        },
                                        success: function(response) {
                                            if (response.status === 'success') {
                                                location.reload();
                                            } else {
                                                jQuery(dialog).dialog('close');
                                            }
                                        },
                                        complete: function() {
                                            jQuery(dialog).dialog('close');
                                        }
                                    });
                                }
                            },
                            {
                                text: 'Close',
                                icons: {primary: "ui-icon-close"},
                                click: function() {
                                    jQuery(this).dialog('close');
                                }
                            }
                        ]
                    });
                });
            });
        }
    });

    initTooltip('#aam');

});

/**
 * Initialize tooltip for selected area
 *
 * @param {String} selector
 *
 * @returns {void}
 *
 * @access public
 */
function initTooltip(selector) {
    jQuery('[tooltip]', selector).hover(function() {
        // Hover over code
        var title = jQuery(this).attr('tooltip');
        jQuery(this).data('tipText', title).removeAttr('tooltip');
        jQuery('<div/>', {
            'class': 'aam-tooltip'
        }).text(title).appendTo('body').fadeIn('slow');
    }, function() {
        //Hover out code
        jQuery(this).attr('tooltip', jQuery(this).data('tipText'));
        jQuery('.aam-tooltip').remove();
    }).mousemove(function(e) {
        jQuery('.aam-tooltip').css({
            top: e.pageY + 15, //Get Y coordinates
            left: e.pageX + 15 //Get X coordinates
        });
    });
}