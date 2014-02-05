/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

AAM.prototype.site_id = 1;
AAM.prototype.segmentTables.siteList = null;

AAM.prototype.reloadSite = function(site_id, siteurl, ajaxurl) {
    var _this = this;
    this.site_id = site_id;
    aamLocal.siteURI = siteurl;
    aamLocal.ajaxurl = ajaxurl;

    //reset default values for current subjects
    this.setSubject('role', aamLocal.defaultSegment.role);

    this.retrieveSettings();

    //highlight the active site
    jQuery('#site_list .aam-multisite-bold').removeClass('aam-multisite-bold');
    jQuery('#site_list .multisite-action-manage-active').each(function() {
        _this.terminate(jQuery(this), 'multisite-action-manage');
    });
    var nRow = jQuery('#site_list tr[site="' + site_id + '"]');
    jQuery('td:eq(0)', nRow).addClass('aam-multisite-bold');
    this.launch(jQuery('.multisite-action-manage', nRow), 'multisite-action-manage');

    //reload all segmentTables
    for (var i in this.segmentTables) {
        if (this.segmentTables[i] !== null) {
            this.segmentTables[i].fnDraw();
        }
    }
};

AAM.prototype.loadMultisiteSegment = function() {
    var _this = this;

    if (typeof this.siteList === 'undefined') {
        this.siteList = jQuery('#site_list').dataTable({
            sDom: "<'top'f<'multisite-top-actions'><'clear'>>t<'footer'ip<'clear'>>",
            bServerSide: true,
            sPaginationType: "full_numbers",
            bAutoWidth: false,
            bSort: false,
            sAjaxSource: ajaxurl,
            fnServerParams: function(aoData) {
                aoData.push({
                    name: 'action',
                    value: 'aam'
                });
                aoData.push({
                    name: 'sub_action',
                    value: 'site_list'
                });
                aoData.push({
                    name: '_ajax_nonce',
                    value: aamLocal.nonce
                });
                aoData.push({
                    name: 's',
                    value: jQuery('#site_list_filter input').val()
                });
            },
            fnInitComplete: function() {
                var add = jQuery('<a/>', {
                    'href': aamMultisiteLocal.addSiteURI,
                    'target': '_blank',
                    'class': 'multisite-top-action multisite-top-action-add',
                    'tooltip': aamLocal.labels['Add New Site']
                });
                jQuery('#site_list_wrapper .multisite-top-actions').append(add);

                var refresh = jQuery('<a/>', {
                    'href': '#',
                    'class': 'multisite-top-action multisite-top-action-refresh',
                    'tooltip': aamLocal.labels['Refresh']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.siteList.fnDraw();
                });
                jQuery('#site_list_wrapper .multisite-top-actions').append(refresh);

                _this.initTooltip(jQuery('#site_list_wrapper .site-top-actions'));
            },
            fnDrawCallback: function() {
                jQuery('#multisite_list_wrapper .clear-table-filter').bind('click', function(event) {
                    event.preventDefault();
                    jQuery('#multisite_list_filter input').val('');
                    _this.siteList.fnFilter('');
                });
            },
            oLanguage: {
                sSearch: "",
                oPaginate: {
                    sFirst: "&Lt;",
                    sLast: "&Gt;",
                    sNext: "&gt;",
                    sPrevious: "&lt;"
                }
            },
            aoColumnDefs: [
                {
                    bVisible: false,
                    aTargets: [0, 1, 2, 4]
                }
            ],
            fnRowCallback: function(nRow, aData) { //format data
                jQuery('td:eq(1)', nRow).html(jQuery('<div/>', {
                    'class': 'multisite-actions'
                }));  //
                //add role attribute
                jQuery(nRow).attr('site', aData[0]);

                if (parseInt(aData[0]) === _this.site_id) {
                    jQuery('.current-site').html(aData[3] + ':');
                    jQuery('td:eq(0)', nRow).addClass('aam-multisite-bold');
                    var current = true;
                } else {
                    current = false;
                }

                jQuery('.multisite-actions', nRow).empty();
                jQuery('.multisite-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'multisite-action multisite-action-manage' + (current ? '-active' : ''),
                    'tooltip': aamLocal.labels['Manage']
                }).bind('click', function(event) {
                    event.preventDefault();
                    //change title
                    jQuery('.current-site').html(aData[3] + ':');
                    _this.reloadSite(aData[0], aData[1], aData[2]);
                }));

                var def_site = (parseInt(aData[5]) === 1 ? true : false);

                jQuery('.multisite-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'multisite-action multisite-action-pin' + (def_site ? '-active' : ''),
                    'tooltip': (def_site ? aamLocal.labels['Unset Default'] : aamLocal.labels['Set as Default'])
                }).bind('click', function(event) {
                    event.preventDefault();
                    var button = this;
                    
                    if (def_site) {
                        var unpin_data = _this.compileAjaxPackage('unpin_site', false);
                        unpin_data.blog = aData[0];
                        jQuery.ajax(ajaxurl, {
                            type: 'POST',
                            dataType: 'json',
                            data: unpin_data,
                            success: function(response) {
                                if (response.status === 'success') {
                                    _this.siteList.fnDraw();
                                }
                                _this.highlight('.control-manager-content', response.status);
                            },
                            error: function() {
                                _this.highlight('.control-manager-content', 'failure');
                            }
                        });
                    } else {
                        jQuery('#default_site').html(aData[3]);
                        var pin_data = _this.compileAjaxPackage('pin_site', false);
                        pin_data.blog = aData[0];

                        var buttons = {};
                        buttons[aamLocal.labels['Set Default']] = function() {
                            jQuery.ajax(ajaxurl, {
                                type: 'POST',
                                dataType: 'json',
                                data: pin_data,
                                success: function(response) {
                                    if (response.status === 'success') {
                                        _this.siteList.fnDraw();
                                    }
                                    _this.highlight('.control-manager-content', response.status);
                                },
                                error: function() {
                                    _this.highlight('.control-manager-content', 'failure');
                                },
                                complete: function() {
                                    jQuery("#tap_default_site").dialog("close");
                                }
                            });
                            jQuery("#tap_default_site").dialog("close");
                        };
                        buttons[aamLocal.labels['Cancel']] = function() {
                            jQuery("#tap_default_site").dialog("close");
                        };

                        jQuery("#tap_default_site").dialog({
                            resizable: false,
                            height: 'auto',
                            modal: true,
                            buttons: buttons
                        });
                    }
                }));

                jQuery('.multisite-actions', nRow).append(jQuery('<a/>', {
                    'href': aamMultisiteLocal.editSiteURI + '?id=' + aData[0],
                    'class': 'multisite-action multisite-action-edit',
                    'target': '_blank',
                    'tooltip': aamLocal.labels['Edit']
                }));

                _this.initTooltip(nRow);
            },
            fnInfoCallback: function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
                return (iMax !== iTotal ? _this.clearFilterIndicator() : '');
            }
        });
    }

    jQuery('#multisite_manager_wrap').show();
};

jQuery(document).ready(function() {
    aamInterface.addAction('aam_load_segment', function() {
        aamInterface.loadMultisiteSegment();
    });
    //by default load the Multisite panel first
    aamInterface.loadSegment('multisite');
});