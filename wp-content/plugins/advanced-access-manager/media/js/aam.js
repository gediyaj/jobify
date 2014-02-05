/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

/**
 * Main AAM UI Class
 *
 * @returns {AAM}
 */
function AAM() {

    /**
     * Current Subject
     *
     * @type {Object}
     *
     * @access public
     */
    this.subject = {
        type: 'role',
        id: aamLocal.defaultSegment.role
    };

    /**
     * Current Post Term
     *
     * @type {Int}
     *
     * @access public
     */
    this.postTerm = '';

    /**
     * User Role to filter
     *
     * @type String
     *
     * @access public
     */
    this.userRoleFilter = aamLocal.defaultSegment.role;

    /**
     * ConfigPress editor
     *
     * @type {Object}
     *
     * @access public
     */
    this.editor = null;

    /**
     * JavaScript Custom Actions
     *
     * @type Array
     */
    this.actions = new Array();

    //Let's init the UI
    this.initUI();
}

/**
 * List of Blog Tables
 *
 * @type {Object}
 *
 * @access public
 */
AAM.prototype.blogTables = {
    capabilities: null,
    inheritRole: null,
    postList: null,
    eventList: null,
    filterRoleList: null
};

/**
 * List of Segment Tables
 *
 * @type {Object}
 *
 * @access public
 */
AAM.prototype.segmentTables = {
    roleList: null,
    userList: null
};

/**
 * Add Custom Action to queue
 *
 * @param {String} action
 * @param {Fuction} callback
 *
 * @returns {void}
 */
AAM.prototype.addAction = function(action, callback) {
    if (typeof this.actions[action] === 'undefined') {
        this.actions[action] = new Array();
    }

    this.actions[action].push(callback);
};

/**
 * Do Custom Action queue
 *
 * @param {String} action
 * @param {Object} params
 *
 * @returns {void}
 */
AAM.prototype.doAction = function(action, params) {
    if (typeof this.actions[action] !== 'undefined') {
        for (var i in this.actions[action]) {
            this.actions[action][i].call(this, params);
        }
    }
};

/**
 * Set Current Subject
 *
 * @param {String} type
 * @param {String} id
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.setSubject = function(type, id) {
    //reset subject first
    this.subject.type = type;
    this.subject.id = id;
};

/**
 * Get Current Subject
 *
 * @param {String} subject
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.getSubject = function() {
    return this.subject;
};

/**
 * Initialize the UI
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initUI = function() {
    //initialize side blocks - Control Panel & Control Manager
    this.initControlPanel();
    this.initControlManager();

    //Retrieve settings of default segment
    this.retrieveSettings();
};

/**
 * Initialize tooltip for selected area
 *
 * @param {String} selector
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initTooltip = function(selector) {
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
};

/**
 * Show Metabox Loader
 *
 * @param {String} selector
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.showMetaboxLoader = function(selector) {
    jQuery('.aam-metabox-loader', selector).show();
};

/**
 * Hide Metabox Loader
 *
 * @param {String} selector
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.hideMetaboxLoader = function(selector) {
    jQuery('.aam-metabox-loader', selector).hide();
};

/**
 * Compile default Ajax POST package
 *
 * @param {String}  action
 * @param {Boolean} include_subject
 *
 * @returns {Object}
 *
 * @access public
 */
AAM.prototype.compileAjaxPackage = function(action, include_subject) {
    var data = {
        action: 'aam',
        sub_action: action,
        _ajax_nonce: aamLocal.nonce
    };
    if (include_subject) {
        data.subject = this.getSubject().type;
        data.subject_id = this.getSubject().id;
    }

    return data;
};

/**
 * Initialize Control Panel Metabox
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initControlPanel = function() {
    var _this = this;
    //Role Back feature
    jQuery('#aam_roleback').bind('click', function(event) {
        event.preventDefault();
        var buttons = {};
        buttons[aamLocal.labels['Rollback Settings']] = function() {
            _this.showMetaboxLoader('#control_panel');
            jQuery.ajax(aamLocal.ajaxurl, {
                type: 'POST',
                dataType: 'json',
                data: _this.compileAjaxPackage('roleback', true),
                success: function(response) {
                    if (response.status === 'success') {
                        _this.retrieveSettings();
                        if (response.more === 1) {
                            jQuery('#aam_roleback').show();
                        } else {
                            jQuery('#aam_roleback').hide();
                        }
                    }
                    _this.highlight('#control_panel .inside', response.status);
                },
                error: function() {
                    _this.highlight('#control_panel .inside', 'failure');
                },
                complete: function() {
                    _this.hideMetaboxLoader('#control_panel');
                    jQuery("#restore_dialog").dialog("close");
                }
            });
        };
        buttons[aamLocal.labels['Cancel']] = function() {
            jQuery("#restore_dialog").dialog("close");
        };

        jQuery("#restore_dialog").dialog({
            resizable: false,
            height: 180,
            modal: true,
            buttons: buttons
        });
    });

    //Save the AAM settings
    jQuery('#aam_save').bind('click', function(event) {
        event.preventDefault();
        _this.showMetaboxLoader('#control_panel');

        //get information from the form
        var data = _this.compileAjaxPackage('save', true);
        //collect data from the form
        //1. Collect Main Menu
        jQuery('input', '#admin_menu_content').each(function() {
            data[jQuery(this).attr('name')] = (jQuery(this).prop('checked') ? 1 : 0);
        });
        //2. Collect Metaboxes & Widgets
        jQuery('input', '#metabox_list').each(function() {
            data[jQuery(this).attr('name')] = (jQuery(this).prop('checked') ? 1 : 0);
        });
        if (_this.getSubject().type !== 'visitor') {
            //3. Collect Capabilities
            var caps = _this.blogTables.capabilities.fnGetData();
            for (var i in caps) {
                data['aam[capability][' + caps[i][0] + ']'] = caps[i][1];
            }
            //4. Collect Events
            var events = _this.blogTables.eventList.fnGetData();
            for (var j in events) {
                data['aam[event][' + j + '][event]'] = events[j][0];
                data['aam[event][' + j + '][event_specifier]'] = events[j][1];
                data['aam[event][' + j + '][post_type]'] = events[j][2];
                data['aam[event][' + j + '][action]'] = events[j][3];
                data['aam[event][' + j + '][action_specifier]'] = events[j][4];
            }
        }

        //5. Collect ConfigPress
        data['aam[configpress]'] = _this.editor.getValue();

        //send the Ajax request to save the data
        jQuery.ajax(aamLocal.ajaxurl, {
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(response) {
                if (response.status === 'success') {
                    jQuery('#aam_roleback').show();
                    _this.retrieveSettings();
                }
                _this.highlight('#control_panel .inside', response.status);
            },
            error: function() {
                _this.highlight('#control_panel .inside', 'failure');
            },
            complete: function() {
                _this.hideMetaboxLoader('#control_panel');
            }
        });
    });
    //Send Email to Us
    jQuery('#aam_message').bind('click', function(event) {
        event.preventDefault();
        var buttons = {};
        buttons[aamLocal.labels['Send E-mail']] = function() {
            location.href = 'mailto:support@wpaam.com';
            jQuery("#message_dialog").dialog("close");
        };
        jQuery("#message_dialog").dialog({
            resizable: false,
            height: 'auto',
            width: '20%',
            modal: true,
            buttons: buttons
        });
    });
    //Init Tooltip
    this.initTooltip('#control_panel');
};

/**
 * Initialize Control Manager Metabox
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initControlManager = function() {
    var _this = this;
    jQuery('.control-manager a').each(function() {
        jQuery(this).bind('click', function(event) {
            event.preventDefault();
            var segment = jQuery(this).attr('segment');
            _this.loadSegment(segment);
        });
    });

    //by default load the Role Segment
    this.loadSegment('role');
};

/**
 * Initialize & Load the Control Segment
 *
 * Segment is a access control area like Blog, Role or User. It is virtual
 * understanding of what we are managing right now
 *
 * @param {String} segment
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.loadSegment = function(segment) {
    var _this = this;
    //clear all active segments
    jQuery('.control-manager a').each(function() {
        _this.terminate(this, 'manager-item-' + jQuery(this).attr('segment'));
    });

    //hide all segment contents from control manager
    jQuery('.control-manager-content > div').hide();

    switch (segment) {
        case 'role':
            this.loadRoleSegment();
            break;

        case 'user':
            this.loadUserSegment();
            break;

        case 'visitor':
            this.loadVisitorSegment();
            break;

        default:
            this.doAction('aam_load_segment');
            break;
    }

    //activate segment icon
    this.launch(jQuery('.manager-item-' + segment), 'manager-item-' + segment);
};

/**
 * Initialize & Load Role Segment
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.loadRoleSegment = function() {
    var _this = this;

    jQuery('#role_manager_wrap').show();
    if (this.segmentTables.roleList === null) {
        this.segmentTables.roleList = jQuery('#role_list').dataTable({
            sDom: "<'top'f<'role-top-actions'><'clear'>>t<'footer'ip<'clear'>>",
            bServerSide: true,
            sPaginationType: "full_numbers",
            bAutoWidth: false,
            bSort: false,
            fnServerData: function(sSource, aoData, fnCallback, oSettings) {
                oSettings.jqXHR = jQuery.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": aamLocal.ajaxurl,
                    "data": aoData,
                    "success": fnCallback
                });
            },
            fnServerParams: function(aoData) {
                aoData.push({
                    name: 'action',
                    value: 'aam'
                });
                aoData.push({
                    name: 'sub_action',
                    value: 'role_list'
                });
                aoData.push({
                    name: '_ajax_nonce',
                    value: aamLocal.nonce
                });
            },
            fnInitComplete: function() {
                var add = jQuery('<a/>', {
                    'href': '#',
                    'class': 'role-top-action role-top-action-add',
                    'tooltip': aamLocal.labels['Add New Role']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.launch(jQuery(this), 'role-top-action-add');
                    _this.launchAddRoleDialog(this);
                });
                jQuery('#role_list_wrapper .role-top-actions').append(add);
                _this.initTooltip(jQuery('#role_list_wrapper .role-top-actions'));
            },
            fnDrawCallback: function() {
                jQuery('#role_list_wrapper .clear-table-filter').bind('click', function(event) {
                    event.preventDefault();
                    jQuery('#role_list_filter input').val('');
                    _this.segmentTables.roleList.fnFilter('');
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
                    aTargets: [0, 1]
                }
            ],
            fnRowCallback: function(nRow, aData) { //format data
                jQuery('td:eq(1)', nRow).html(jQuery('<div/>', {
                    'class': 'role-actions'
                }));  //
                //add role attribute
                jQuery(nRow).attr('role', aData[0]);

                jQuery('.role-actions', nRow).empty();
                jQuery('.role-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'role-action role-action-manage',
                    'tooltip': aamLocal.labels['Manage']
                }).bind('click', {
                    role: aData[0]
                }, function(event) {
                    event.preventDefault();
                    _this.setSubject('role', event.data.role);
                    _this.userRoleFilter = event.data.role;
                    _this.retrieveSettings();
                    _this.setCurrent('role', nRow, aData[2]);
                    if (_this.segmentTables.userList !== null) {
                        _this.segmentTables.userList.fnDraw();
                    }
                }));

                jQuery('.role-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'role-action role-action-edit',
                    'tooltip': aamLocal.labels['Edit']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.launch(jQuery(this), 'role-action-edit');
                    _this.launchEditRoleDialog(this, aData);
                }));

                jQuery('.role-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'role-action role-action-delete',
                    'tooltip': aamLocal.labels['Delete']
                }).bind('click', function(event) {
                    event.preventDefault();
                    if ((aData[0] === 'administrator')) {
                        //open the dialog
                        var buttons = {};
                        buttons[aamLocal.labels['Close']] = function() {
                            jQuery('#delete_admin_role_dialog').dialog("close");
                        };
                        jQuery('#delete_admin_role_dialog').dialog({
                            resizable: false,
                            height: 'auto',
                            width: '25%',
                            modal: true,
                            buttons: buttons
                        });
                    } else {
                        _this.launch(jQuery(this), 'role-action-delete');
                        _this.launchDeleteRoleDialog(this, aData);
                    }
                }));

                //set active
                if (_this.getSubject().type === 'role'
                        && _this.getSubject().id === aData[0]) {
                    _this.setCurrent('role', nRow, aData[2]);
                }

                _this.initTooltip(nRow);
            },
            fnInfoCallback: function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
                return (iMax !== iTotal ? _this.clearFilterIndicator() : '');
            }
        });
    }
};

/**
 * Highlight current subject
 *
 * @param {String} subject
 * @param {Object} nRow
 * @param {String} name
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.setCurrent = function(subject, nRow, name) {
    var _this = this;

    //terminate any active subject
    jQuery('.user-action-manage-active').each(function() {
        _this.terminate(jQuery(this), 'user-action-manage');
    });
    jQuery('.role-action-manage-active').each(function() {
        _this.terminate(jQuery(this), 'role-action-manage');
    });
    jQuery('.aam-bold').each(function() {
        jQuery(this).removeClass('aam-bold');
    });

    //highlight the row
    jQuery('td:eq(0)', nRow).addClass('aam-bold');
    _this.launch(
            jQuery('.' + subject + '-action-manage', nRow),
            subject + '-action-manage'
            );
    jQuery('.current-subject').html(subject + ' ' + name);
};

/**
 * Generate clear filter indicator for all tables
 *
 * @returns {String}
 *
 * @access public
 */
AAM.prototype.clearFilterIndicator = function() {
    var info = '<div class="table-filtered">';
    info += aamLocal.labels['Filtered'] + '. ';
    info += '<a href="#" class="clear-table-filter">';
    info += aamLocal.labels['Clear'] + '.</a></div>';

    return info;
};

/**
 * Launch Add Role Dialog
 *
 * @param {Object} button
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchAddRoleDialog = function(button) {
    var _this = this;
    //clean-up the form first
    jQuery('#role_name').val('');
    //open the dialog
    var buttons = {};
    buttons[aamLocal.labels['Add New Role']] = function() {
        //prepare ajax package
        var data = _this.compileAjaxPackage('add_role');
        data.name = jQuery('#role_name').val();

        //send the request
        jQuery.ajax(aamLocal.ajaxurl, {
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(response) {
                if (response.status === 'success') {
                    _this.segmentTables.roleList.fnDraw();
                }
                _this.highlight('#control_manager .inside', response.status);
            }
        });
        jQuery('#manage_role_dialog').dialog("close");
    };
    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#manage_role_dialog').dialog("close");
    };

    jQuery('#manage_role_dialog').dialog({
        resizable: false,
        height: 'auto',
        width: '30%',
        modal: true,
        title: aamLocal.labels['Add New Role'],
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'role-top-action-add');
        }
    });
};

/**
 * Launch Edit Role Dialog
 *
 * @param {Object} button
 * @param {Object} aData
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchEditRoleDialog = function(button, aData) {
    var _this = this;
    //populate the form with data
    jQuery('#role_name').val(aData[2]);
    //launch the dialog
    var buttons = {};
    buttons[aamLocal.labels['Save Changes']] = function() {
        var data = _this.compileAjaxPackage('edit_role');
        data.subject = 'role';
        data.subject_id = aData[0];
        data.name = jQuery('#role_name').val();
        //save the changes
        jQuery.ajax(aamLocal.ajaxurl, {
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(response) {
                if (response.status === 'success') {
                    _this.segmentTables.roleList.fnDraw();
                }
                _this.highlight('#control_manager .inside', response.status);
            },
            error: function() {
                _this.highlight('#control_manager .inside', 'failure');
            },
            complete: function() {
                jQuery('#manage_role_dialog').dialog("close");
            }
        });
    };
    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#manage_role_dialog').dialog("close");
    };

    jQuery('#manage_role_dialog').dialog({
        resizable: false,
        height: 'auto',
        modal: true,
        width: '30%',
        title: aamLocal.labels['Edit Role'],
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'role-action-edit');
        }
    });
};

/**
 * Launch Delete Role Dialog
 *
 * @param {Object} button
 * @param {Object} aData
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchDeleteRoleDialog = function(button, aData) {
    var _this = this;
    //render the message first
    if (aData[1]) {
        var message = aamLocal.labels['Delete Role with Users Message'].replace(
                '%d', aData[1]
        );
        message = message.replace('%s', aData[2]);
        jQuery('#delete_role_dialog .dialog-content').html(message);
    } else {
        message = aamLocal.labels['Delete Role Message'].replace('%s', aData[2]);
        jQuery('#delete_role_dialog .dialog-content').html(message);
    }

    var buttons = {};
    buttons[aamLocal.labels['Delete Role']] = function() {
        //prepare ajax package
        var data = _this.compileAjaxPackage('delete_role');
        data.subject = 'role';
        data.subject_id = aData[0];
        data.delete_users = aData[1];
        //send the request
        jQuery.ajax(aamLocal.ajaxurl, {
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(response) {
                if (response.status === 'success') {
                    //reset the current role
                    var subject = _this.getSubject();
                    if (subject.type === 'role' && subject.id === aData[0]) {
                        _this.setSubject('role', null);
                    }
                    _this.segmentTables.roleList.fnDraw();
                }
                _this.highlight('#control_manager .inside', response.status);
            },
            error: function() {
                _this.highlight('#control_manager .inside', 'failure');
            },
            complete: function() {
                jQuery('#delete_role_dialog').dialog("close");
            }
        });
    };
    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#delete_role_dialog').dialog("close");
    };

    //launch the dialog
    jQuery('#delete_role_dialog').dialog({
        resizable: false,
        height: 'auto',
        modal: true,
        title: aamLocal.labels['Delete Role'],
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'role-action-delete');
        }
    });
};

/**
 * Initialize & Load User Segment
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.loadUserSegment = function() {
    var _this = this;
    jQuery('#user_manager_wrap').show();
    if (this.segmentTables.userList === null) {
        this.segmentTables.userList = jQuery('#user_list').dataTable({
            sDom: "<'top'f<'user-top-actions'><'clear'>>t<'footer'ip<'clear'>>",
            bServerSide: true,
            sPaginationType: "full_numbers",
            bAutoWidth: false,
            bSort: false,
            sAjaxSource: true,
            fnServerData: function(sSource, aoData, fnCallback, oSettings) {
                oSettings.jqXHR = jQuery.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": aamLocal.ajaxurl,
                    "data": aoData,
                    "success": fnCallback
                });
            },
            fnServerParams: function(aoData) {
                aoData.push({
                    name: 'action',
                    value: 'aam'
                });
                aoData.push({
                    name: 'sub_action',
                    value: 'user_list'
                });
                aoData.push({
                    name: '_ajax_nonce',
                    value: aamLocal.nonce
                });
                aoData.push({
                    name: 'role',
                    value: _this.userRoleFilter
                });
            },
            aoColumnDefs: [
                {
                    bVisible: false,
                    aTargets: [0, 1, 4]
                }
            ],
            fnInitComplete: function() {
                var add = jQuery('<a/>', {
                    'href': aamLocal.addUserURI,
                    'target': '_blank',
                    'class': 'user-top-action user-top-action-add',
                    'tooltip': aamLocal.labels['Add User']
                });

                var filter = jQuery('<a/>', {
                    'href': '#',
                    'class': 'user-top-action user-top-action-filter',
                    'tooltip': aamLocal.labels['Filter Users']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.launch(jQuery(this), 'user-top-action-filter');
                    _this.launchFilterUserDialog(this);
                });

                var refresh = jQuery('<a/>', {
                    'href': '#',
                    'class': 'user-top-action user-top-action-refresh',
                    'tooltip': aamLocal.labels['Refresh List']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.segmentTables.userList.fnDraw();
                });

                jQuery('#user_list_wrapper .user-top-actions').append(filter);
                jQuery('#user_list_wrapper .user-top-actions').append(add);
                jQuery('#user_list_wrapper .user-top-actions').append(refresh);
                _this.initTooltip(jQuery('#user_list_wrapper .user-top-actions'));
            },
            fnDrawCallback: function() {
                jQuery('#user_list_wrapper .clear-table-filter').bind('click', function(event) {
                    event.preventDefault();
                    jQuery('#user_list_filter input').val('');
                    _this.userRoleFilter = '';
                    _this.segmentTables.userList.fnFilter('');
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
            fnRowCallback: function(nRow, aData, iDisplayIndex) { //format data
                //add User attribute
                jQuery(nRow).attr('user', aData[0]);
                jQuery('td:eq(1)', nRow).html(jQuery('<div/>', {
                    'class': 'user-actions'
                }));

                jQuery('.user-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'user-action user-action-manage',
                    'tooltip': aamLocal.labels['Manager']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.setSubject('user', aData[0]);
                    _this.retrieveSettings();
                    _this.setCurrent('user', nRow, aData[2]);
                }));

                jQuery('.user-actions', nRow).append(jQuery('<a/>', {
                    'href': aamLocal.editUserURI + '?user_id=' + aData[0],
                    'target': '_blank',
                    'class': 'user-action user-action-edit',
                    'tooltip': aamLocal.labels['Edit']
                }));

                var status = (aData[4] === '1' ? 'user-action-block-active' : 'user-action-block');
                jQuery('.user-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'user-action ' + status,
                    'tooltip': aamLocal.labels['Block']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.blockUser(this, aData);
                }));

                jQuery('.user-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'user-action user-action-delete',
                    'tooltip': aamLocal.labels['Delete']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.launch(jQuery(this), 'user-action-delete');
                    _this.deleteUser(this, aData);
                }));

                //set active
                if (_this.getSubject().type === 'user'
                        && _this.getSubject().id === aData[0]) {
                    _this.setCurrent('user', nRow, aData[2]);
                }

                _this.initTooltip(nRow);
            },
            fnInfoCallback: function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
                return (iMax !== iTotal ? _this.clearFilterIndicator() : '');
            }
        });
    }
};

/**
 * Block the selected user
 *
 * @param {Object} button
 * @param {Object} aData
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.blockUser = function(button, aData) {
    var _this = this;
    var data = this.compileAjaxPackage('block_user');
    data.subject = 'user';
    data.subject_id = aData[0];
    //send the request
    jQuery.ajax(aamLocal.ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(response) {
            _this.highlight('#control_manager .inside', response.status);
            if (response.user_status === 1) {
                _this.launch(jQuery(button), 'user-action-block');
            } else {
                _this.terminate(jQuery(button), 'user-action-block');
            }
        },
        error: function() {
            _this.highlight('#control_manager .inside', 'failure');
        }
    });
};

/**
 * Delete selected User
 *
 * @param {Object} button
 * @param {Object} aData
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.deleteUser = function(button, aData) {
    var _this = this;
    //insert content
    jQuery('#delete_user_dialog .dialog-content').html(
            aamLocal.labels['Delete User Message'].replace('%s', aData[2])
            );
    var buttons = {};
    buttons[aamLocal.labels['Delete']] = function() {
        var data = _this.compileAjaxPackage('delete_user');
        data.subject = 'user';
        data.subject_id = aData[0];
        //send request
        jQuery.ajax(aamLocal.ajaxurl, {
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(response) {
                if (response.status === 'success') {
                    _this.segmentTables.userList.fnDraw();
                }
                _this.highlight('#control_manager .inside', response.status);

            },
            error: function() {
                _this.highlight('#control_manager .inside', 'failure');
            },
            complete: function() {
                jQuery('#delete_user_dialog').dialog('close');
            }
        });
    };

    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#delete_user_dialog').dialog("close");
    };
    //show the dialog
    jQuery('#delete_user_dialog').dialog({
        resizable: false,
        height: 'auto',
        width: '30%',
        modal: true,
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'user-action-delete');
        }
    });
};

/**
 * Launch the Filter User List by User Role dialog
 *
 * @param {Object} button
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchFilterUserDialog = function(button) {
    var _this = this;
    if (this.blogTables.filterRoleList === null) {
        this.blogTables.filterRoleList = jQuery('#filter_role_list').dataTable({
            sDom: "<'top'f<'clear'>>t<'footer'ip<'clear'>>",
            bServerSide: true,
            sPaginationType: "full_numbers",
            bAutoWidth: false,
            bSort: false,
            sAjaxSource: true,
            fnServerData: function(sSource, aoData, fnCallback, oSettings) {
                oSettings.jqXHR = jQuery.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": aamLocal.ajaxurl,
                    "data": aoData,
                    "success": fnCallback
                });
            },
            fnServerParams: function(aoData) {
                aoData.push({
                    name: 'action',
                    value: 'aam'
                });
                aoData.push({
                    name: 'sub_action',
                    value: 'role_list'
                });
                aoData.push({
                    name: '_ajax_nonce',
                    value: aamLocal.nonce
                });
            },
            fnDrawCallback: function() {
                jQuery('#filter_role_list_wrapper .clear-table-filter').bind('click', function(event) {
                    event.preventDefault();
                    jQuery('#filter_role_list_filter input').val('');
                    _this.blogTables.filterRoleList.fnFilter('');
                });
                _this.initTooltip('#filter_role_list_wrapper');
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
                    aTargets: [0, 1]
                }
            ],
            fnRowCallback: function(nRow, aData) { //format data
                jQuery('td:eq(1)', nRow).html(jQuery('<div/>', {
                    'class': 'user-actions'
                }));

                jQuery('.user-actions', nRow).empty();
                jQuery('.user-actions', nRow).append(jQuery('<a/>', {
                    'href': '#',
                    'class': 'user-action user-action-select',
                    'tooltip': 'Select Role'
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.userRoleFilter = aData[0];
                    _this.segmentTables.userList.fnDraw();
                    jQuery('#filter_user_dialog').dialog('close');
                }));
            },
            fnInfoCallback: function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
                return (iMax !== iTotal ? _this.clearFilterIndicator() : '');
            }
        });
    } else {
        this.blogTables.filterRoleList.fnDraw();
    }
    //show the dialog
    var buttons = {};
    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#filter_user_dialog').dialog("close");
    };
    jQuery('#filter_user_dialog').dialog({
        resizable: false,
        height: 'auto',
        width: '40%',
        modal: true,
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'user-top-action-filter');
        }
    });
};

/**
 * Initialize & Load visitor segment
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.loadVisitorSegment = function() {
    jQuery('#visitor_manager_wrap').show();
    this.setSubject('visitor', 1);
    this.setCurrent('Visitor', '', '');
    this.retrieveSettings();
};

/**
 * Retrieve main metabox settings
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.retrieveSettings = function() {
    var _this = this;

    jQuery('.aam-main-loader').show();
    jQuery('.aam-main-content').empty();

    jQuery.ajax(aamLocal.siteURI, {
        type: 'POST',
        dataType: 'html',
        data: {
            action: 'features',
            _ajax_nonce: aamLocal.nonce,
            subject: _this.getSubject().type,
            subject_id: _this.getSubject().id
        },
        success: function(response) {
            jQuery('.aam-main-content').html(response);
            _this.initSettings();
            _this.checkRoleback();
        },
        complete: function() {
            jQuery('.aam-main-loader').hide();
        }
    });
};

/**
 * Check if current subject has roleback available
 *
 * @returns {undefined}
 */
AAM.prototype.checkRoleback = function() {
    var _this = this;

    jQuery.ajax(aamLocal.ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: _this.compileAjaxPackage('check_roleback', true),
        success: function(response) {
            if (response.status === 1) {
                jQuery('#aam_roleback').show();
            } else {
                jQuery('#aam_roleback').hide();
            }
        },
        complete: function() {
            jQuery('.aam-main-loader').hide();
        }
    });
};

/**
 * Initialize Main metabox settings
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initSettings = function() {
    //remove all dialogs to make sure that there are no confusions
    jQuery('.ui-dialog').remove();

    //init Settings Menu
    jQuery('.feature-list .feature-item').each(function() {
        jQuery(this).bind('click', function() {
            jQuery('.feature-list .feature-item').removeClass(
                    'feature-item-active'
            );
            jQuery(this).addClass('feature-item-active');
            jQuery('.feature-content .feature-content-container').hide();
            jQuery('#' + jQuery(this).attr('feature') + '_content').show();
            //hide help content
            jQuery('.aam-help > span').hide();
            jQuery('#feature_help_' + jQuery(this).attr('feature')).css(
                    'display', 'table-cell'
            );
        });
    });

    this.initMenuTab();
    this.initMetaboxTab();
    this.initCapabilityTab();
    this.initPostTab();
    this.initEventTab();
    this.initConfigPressTab();

    this.doAction('aam_init_features');

    jQuery('.feature-list .feature-item:eq(0)').trigger('click');
};

/**
 * Initialize ConfigPress Feature
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initConfigPressTab = function() {
    this.editor = CodeMirror.fromTextArea(document.getElementById("configpress"), {});
    this.initTooltip('#configpress_content');
};

/**
 * Initialize Capability Feature
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initCapabilityTab = function() {
    var _this = this;

    //indicator that current user has default capability set. In case he does
    //not, it will show additional top action - Restore Default Capabilities
    var userDefault = true;

    this.blogTables.capabilities = jQuery('#capability_list').dataTable({
        sDom: "<'top'lf<'capability-top-actions'><'clear'>>t<'footer'ip<'clear'>>",
        sPaginationType: "full_numbers",
        bAutoWidth: false,
        bSort: false,
        bDestroy: true,
        sAjaxSource: true,
        fnServerData: function(sSource, aoData, fnCallback) {
            jQuery.ajax({
                dataType: 'json',
                type: "POST",
                url: aamLocal.ajaxurl,
                data: aoData,
                success: function(data) {
                    //set Default Capability set indicator
                    userDefault = parseInt(data.aaDefault);
                    //populate oTable
                    fnCallback(data);
                },
                error: function() {
                    _this.parent.failure();
                }
            });
        },
        fnServerParams: function(aoData) {
            aoData.push({
                name: 'action',
                value: 'aam'
            });
            aoData.push({
                name: 'sub_action',
                value: 'load_capabilities'
            });
            aoData.push({
                name: '_ajax_nonce',
                value: aamLocal.nonce
            });
            aoData.push({
                name: 'subject',
                value: _this.getSubject().type
            });
            aoData.push({
                name: 'subject_id',
                value: _this.getSubject().id
            });
        },
        fnInitComplete: function() {
            var a = jQuery('#capability_list_wrapper .capability-top-actions');

            var filter = jQuery('<a/>', {
                'href': '#',
                'class': 'capability-top-action capability-top-action-filter',
                'tooltip': aamLocal.labels['Filter Capabilities by Category']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.launch(jQuery(this), 'capability-top-action-filter');
                _this.launchCapabilityFilterDialog(this);
            });
            jQuery(a).append(filter);

            //do not allow for user to add any new capabilities or copy from
            //existing role
            if (_this.getSubject().type !== 'user') {
                var copy = jQuery('<a/>', {
                    'href': '#',
                    'class': 'capability-top-action capability-top-action-copy',
                    'tooltip': aamLocal.labels['Inherit Capabilities']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.launch(jQuery(this), 'capability-top-action-copy');
                    _this.launchRoleCopyDialog(this);
                });

                var add = jQuery('<a/>', {
                    'href': '#',
                    'class': 'capability-top-action capability-top-action-add',
                    'tooltip': aamLocal.labels['Add New Capability']
                }).bind('click', function(event) {
                    event.preventDefault();
                    _this.launch(jQuery(this), 'capability-top-action-add');
                    _this.launchAddCapabilityDialog(this);
                });

                jQuery(a).append(copy);
                jQuery(a).append(add);
            } else if (userDefault === 0) {
                //add Restore Default Capability button
                var restore = jQuery('<a/>', {
                    'href': '#',
                    'class': 'capability-top-action capability-top-action-restore',
                    'tooltip': aamLocal.labels['Restore Default Capabilities']
                }).bind('click', function(event) {
                    event.preventDefault();
                    var data = _this.compileAjaxPackage('restore_capability', true);
                    //show indicator that is running
                    jQuery(this).addClass('capability-top-action-restore-running');
                    jQuery.ajax(aamLocal.ajaxurl, {
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function(response) {
                            if (response.status === 'success') {
                                _this.retrieveSettings();
                            } else {
                                _this.highlight('#capability_content', 'failure');
                            }
                        },
                        error: function() {
                            _this.highlight('#capability_content', 'failure');
                        }
                    });
                });

                jQuery(a).append(restore);
            }

            _this.initTooltip(a);
        },
        aoColumnDefs: [
            {
                bVisible: false,
                aTargets: [0, 1]
            }
        ],
        fnRowCallback: function(nRow, aData) {
            jQuery('td:eq(2)', nRow).empty().append(jQuery('<div/>', {
                'class': 'capability-actions'
            }));
            var actions = jQuery('.capability-actions', nRow);
            //add capability checkbox
            jQuery(actions).append(jQuery('<div/>', {
                'class': 'capability-action'
            }));
            jQuery('.capability-action', actions).append(jQuery('<input/>', {
                type: 'checkbox',
                id: aData[0],
                checked: (parseInt(aData[1]) === 1 ? true : false),
                name: 'aam[capability][' + aData[0] + ']'
            }).bind('change', function() {
                var status = (jQuery(this).prop('checked') === true ? 1 : 0);
                _this.blogTables.capabilities.fnUpdate(status, nRow, 1, false);
            }));
            jQuery('.capability-action', actions).append(
                    '<label for="' + aData[0] + '"><span></span></label>'
                    );
            //add capability delete
            jQuery(actions).append(jQuery('<a/>', {
                'href': '#',
                'class': 'capability-action capability-action-delete',
                'tooltip': aamLocal.labels['Delete']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.launch(jQuery(this), 'capability-action-delete');
                _this.launchDeleteCapabilityDialog(this, aData, nRow);
            }));
            _this.initTooltip(nRow);
        },
        fnDrawCallback: function() {
            jQuery('#capability_list_wrapper .clear-table-filter').bind('click', function(event) {
                event.preventDefault();
                jQuery('#capability_list_wrapper input').val('');
                _this.blogTables.capabilities.fnFilter('');
                _this.blogTables.capabilities.fnFilter('', 2);
            });
        },
        fnInfoCallback: function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
            return (iMax !== iTotal ? _this.clearFilterIndicator() : '');
        },
        oLanguage: {
            sSearch: "",
            oPaginate: {
                sFirst: "&Lt;",
                sLast: "&Gt;",
                sNext: "&gt;",
                sPrevious: "&lt;"
            },
            sLengthMenu: "_MENU_"
        }
    });
};

/**
 * Launch the Delete Capability Dialog
 *
 * @param {Object} button
 * @param {Object} aData
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchDeleteCapabilityDialog = function(button, aData, nRow) {
    var _this = this;
    jQuery('#delete_capability .dialog-content').html(
            aamLocal.labels['Delete Capability Message'].replace('%s', aData[3])
            );
    var buttons = {};
    buttons[aamLocal.labels['Delete Capability']] = function() {
        var data = _this.compileAjaxPackage('delete_capability');
        data.capability = aData[0];
        jQuery.ajax(aamLocal.ajaxurl, {
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(response) {
                if (response.status === 'success') {
                    _this.blogTables.capabilities.fnDeleteRow(nRow);
                }
                _this.highlight('#capability_content', response.status);
            },
            error: function() {
                _this.highlight('#capability_content', 'failure');
            }
        });
        jQuery('#delete_capability').dialog("close");
    };
    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#delete_capability').dialog("close");
    };

    jQuery('#delete_capability').dialog({
        resizable: false,
        height: 'auto',
        modal: true,
        title: aamLocal.labels['Delete Capability'],
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'capability-action-delete');
        }
    });
};

/**
 * Launch Capability Filter Dialog
 *
 * @param {Object} button
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchCapabilityFilterDialog = function(button) {
    var _this = this;

    jQuery('#capability_group_list').dataTable({
        sDom: "t",
        sPaginationType: "full_numbers",
        bAutoWidth: false,
        bSort: false,
        bDestroy: true,
        fnRowCallback: function(nRow, aData) {
            jQuery('.capability-action-select', nRow).bind('click', function(event) {
                event.preventDefault();
                _this.blogTables.capabilities.fnFilter(
                        aData[0].replace('&amp;', '&'), 2
                        );
                jQuery('#filter_capability_dialog').dialog('close');
            });
        }
    });
    var buttons = {};
    buttons[aamLocal.labels['Close']] = function() {
        jQuery('#filter_capability_dialog').dialog("close");
    };
    jQuery('#filter_capability_dialog').dialog({
        resizable: false,
        height: 'auto',
        width: '30%',
        modal: true,
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'capability-top-action-filter');
        }
    });
};

/**
 * Launch Capability Role Copy dialog
 *
 * @param {Object} button
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchRoleCopyDialog = function(button) {
    var _this = this;

    this.blogTables.inheritRole = jQuery('#copy_role_list').dataTable({
        sDom: "<'top'f<'clear'>>t<'footer'ip<'clear'>>",
        bServerSide: true,
        sPaginationType: "full_numbers",
        bAutoWidth: false,
        bSort: false,
        bDestroy: true,
        sAjaxSource: true,
        fnServerData: function(sSource, aoData, fnCallback, oSettings) {
            oSettings.jqXHR = jQuery.ajax({
                "dataType": 'json',
                "type": "POST",
                "url": aamLocal.ajaxurl,
                "data": aoData,
                "success": fnCallback
            });
        },
        fnServerParams: function(aoData) {
            aoData.push({
                name: 'action',
                value: 'aam'
            });
            aoData.push({
                name: 'sub_action',
                value: 'role_list'
            });
            aoData.push({
                name: '_ajax_nonce',
                value: aamLocal.nonce
            });
        },
        fnDrawCallback: function() {
            jQuery('#copy_role_list_wrapper .clear-table-filter').bind('click', function(event) {
                event.preventDefault();
                jQuery('#copy_role_list_filter input').val('');
                _this.blogTables.inheritRole.fnFilter('');
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
                aTargets: [0, 1]
            }
        ],
        fnRowCallback: function(nRow, aData, iDisplayIndex) { //format data
            jQuery('td:eq(1)', nRow).html(jQuery('<div/>', {
                'class': 'user-actions'
            }));  //

            jQuery('.user-actions', nRow).empty();
            jQuery('.user-actions', nRow).append(jQuery('<a/>', {
                'href': '#',
                'class': 'user-action user-action-select',
                'title': aamLocal.labels['Select Role']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.showMetaboxLoader('#copy_role_dialog');
                var data = _this.compileAjaxPackage('role_capabilities');
                data.subject = 'role';
                data.subject_id = aData[0];
                jQuery.ajax(aamLocal.ajaxurl, {
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(response) {
                        if (response.status === 'success') {
                            //reset the capability list
                            var oSettings = _this.blogTables.capabilities.fnSettings();
                            for (var i in oSettings.aoData) {
                                var cap = oSettings.aoData[i]._aData[0];
                                var ntr = oSettings.aoData[i].nTr;
                                if (typeof response.capabilities[cap] !== 'undefined') {
                                    _this.blogTables.capabilities.fnUpdate(1, ntr, 1, false);
                                    jQuery('#' + cap).attr('checked', 'checked');
                                } else {
                                    _this.blogTables.capabilities.fnUpdate(0, ntr, 1, false);
                                    jQuery('#' + cap).removeAttr('checked');
                                }
                            }
                        }
                        _this.highlight('#capability_content', response.status);
                    },
                    error: function() {
                        _this.highlight('#capability_content', 'failure');
                    },
                    complete: function() {
                        //grab the capability list for selected role
                        _this.hideMetaboxLoader('#copy_role_dialog');
                        jQuery('#copy_role_dialog').dialog('close');
                    }
                });
            }));
        },
        fnInfoCallback: function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
            return (iMax !== iTotal ? _this.clearFilterIndicator() : '');
        }
    });

    var buttons = {};
    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#copy_role_dialog').dialog("close");
    };

    //show the dialog
    jQuery('#copy_role_dialog').dialog({
        resizable: false,
        height: 'auto',
        width: '40%',
        modal: true,
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'capability-top-action-copy');
        }
    });
};

/**
 * Launch Add Capability Dialog
 *
 * @param {Object} button
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchAddCapabilityDialog = function(button) {
    var _this = this;
    //reset form
    jQuery('#capability_name').val('');

    var buttons = {};
    buttons[aamLocal.labels['Add Capability']] = function() {
        var capability = jQuery.trim(jQuery('#capability_name').val());
        if (capability) {
            _this.showMetaboxLoader('#capability_form_dialog');
            var data = _this.compileAjaxPackage('add_capability');
            data.capability = capability;
            jQuery.ajax(aamLocal.ajaxurl, {
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response.status === 'success') {
                        _this.blogTables.capabilities.fnAddData([
                            response.capability,
                            1,
                            'Miscelaneous',
                            data.capability,
                            '']);
                        _this.highlight('#capability_content', 'success');
                        jQuery('#capability_form_dialog').dialog("close");
                    } else {
                        _this.highlight('#capability_form_dialog', 'failure');
                    }
                },
                error: function() {
                    _this.highlight('#capability_form_dialog', 'failure');
                },
                complete: function() {
                    _this.hideMetaboxLoader('#capability_form_dialog');
                }
            });
        } else {
            jQuery('#capability_name').effect('highlight', 2000);
        }
    };
    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#capability_form_dialog').dialog("close");
    };

    //show dialog
    jQuery('#capability_form_dialog').dialog({
        resizable: false,
        height: 'auto',
        width: '30%',
        modal: true,
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'capability-top-action-add');
        }
    });
};

/**
 * Initialize and Load the Menu Feature
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initMenuTab = function() {
    var _this = this;
    this.initMenuAccordion(false);

    jQuery('.menu-item-action-restrict').each(function() {
        jQuery(this).bind('click', function(event) {
            event.preventDefault();
            _this.launch(jQuery(this), 'menu-item-action-restrict');
        });
    });

    jQuery('.whole_menu').each(function() {
        jQuery(this).bind('change', function() {
            if (jQuery(this).attr('checked')) {
                jQuery('input[type="checkbox"]', '#submenu_' + jQuery(this).attr('id')).attr(
                        'checked', 'checked'
                        );
            } else {
                jQuery('input[type="checkbox"]', '#submenu_' + jQuery(this).attr('id')).removeAttr(
                        'checked'
                        );
            }
        });
    });

    this.initTooltip('#main_menu_list');
};

/**
 * Init Main Menu Accordion
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initMenuAccordion = function() {
    //destroy if already initialized
    if (jQuery('#main_menu_list').hasClass('ui-accordion')) {
        jQuery('#main_menu_list').accordion('destroy');
    }

    //initialize
    jQuery('#main_menu_list').accordion({
        collapsible: true,
        header: 'h4',
        heightStyle: 'content',
        icons: {
            header: "ui-icon-circle-arrow-e",
            headerSelected: "ui-icon-circle-arrow-s"
        },
        active: false
    });
};

/**
 * Initialize and load Metabox Feature
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initMetaboxTab = function() {
    var _this = this;

    jQuery('.metabox-top-action-add').bind('click', function(event) {
        event.preventDefault();
        var link = jQuery.trim(jQuery('#metabox_link').val());

        if (link) {
            //init metaboxes
            var data = _this.compileAjaxPackage('init_link');
            data.link = link;

            //send the request
            jQuery.ajax(aamLocal.ajaxurl, {
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(response) {
                    if (response.status === 'success') {
                        jQuery('#metabox_link').val('');
                        _this.loadMetaboxes(0);
                    }
                    _this.highlight('#metabox_content', response.status);
                },
                error: function() {
                    _this.highlight('#metabox_content', 'failure');
                }
            });
        } else {
            jQuery('#metabox_link').effect('highlight', 2000);
        }

    });

    jQuery('.metabox-top-action-refresh').bind('click', function(event) {
        event.preventDefault();
        _this.loadMetaboxes(1);
    });

    this.initTooltip('.metabox-top-actions');

    this.loadMetaboxes(0);
};

/**
 * Load Metabox list
 *
 * @param {Boolean} refresh
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.loadMetaboxes = function(refresh) {
    var _this = this;
    //init metaboxes
    var data = this.compileAjaxPackage('load_metaboxes', true);
    data.refresh = refresh;
    //show loader and reset the metabox list holder
    jQuery('#metabox_list_container').empty();
    this.showMetaboxLoader('#metabox_content');
    //send the request
    jQuery.ajax(aamLocal.ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(response) {
            jQuery('#metabox_list_container').html(response.content);
            jQuery('#metabox_list').accordion({
                collapsible: true,
                header: 'h4',
                heightStyle: 'content',
                icons: {
                    header: "ui-icon-circle-arrow-e",
                    headerSelected: "ui-icon-circle-arrow-s"
                },
                active: false
            });
            //init Tooltips
            _this.initTooltip('#metabox_list_container');
        },
        error: function() {
            _this.highlight('#metabox_content', 'failure');
        },
        complete: function() {
            _this.hideMetaboxLoader('#metabox_content');
        }
    });
};

/**
 * Initialize and Load Event Feature
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initEventTab = function() {
    var _this = this;

    jQuery('#event_event').bind('change', function() {
        if (jQuery(this).val() === 'status_change') {
            jQuery('#status_changed').show();
        } else {
            jQuery('#status_changed').hide();
        }
    });

    jQuery('#event_action').bind('change', function() {
        jQuery('.event-specifier').hide();
        jQuery('#event_specifier_' + jQuery(this).val() + '_holder').show();
    });

    this.blogTables.eventList = jQuery('#event_list').dataTable({
        sDom: "<'event-top-actions'><'clear'>t<'footer'p<'clear'>>",
        //bProcessing : false,
        sPaginationType: "full_numbers",
        bAutoWidth: false,
        bSort: false,
        sAjaxSource: true,
        fnServerData: function(sSource, aoData, fnCallback, oSettings) {
            oSettings.jqXHR = jQuery.ajax({
                "dataType": 'json',
                "type": "POST",
                "url": aamLocal.ajaxurl,
                "data": aoData,
                "success": fnCallback
            });
        },
        fnServerParams: function(aoData) {
            aoData.push({
                name: 'action',
                value: 'aam'
            });
            aoData.push({
                name: 'sub_action',
                value: 'event_list'
            });
            aoData.push({
                name: '_ajax_nonce',
                value: aamLocal.nonce
            });
            aoData.push({
                name: 'subject',
                value: _this.getSubject().type
            });
            aoData.push({
                name: 'subject_id',
                value: _this.getSubject().id
            });
        },
        aoColumnDefs: [
            {
                bVisible: false,
                aTargets: [1, 4]
            }
        ],
        fnInitComplete: function() {
            var filter = jQuery('<a/>', {
                'href': '#',
                'class': 'event-top-action event-top-action-add',
                'tooltip': aamLocal.labels['Add Event']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.launch(jQuery(this), 'event-top-action-add');
                _this.launchManageEventDialog(this, null);
            });
            jQuery('#event_list_wrapper .event-top-actions').append(filter);
            _this.initTooltip(jQuery('#event_list_wrapper .event-top-actions'));
        },
        fnDrawCallback: function() {
            jQuery('#event_list_wrapper .clear-table-filter').bind('click', function(event) {
                event.preventDefault();
                jQuery('#event_list_filter input').val('');
                _this.blogTables.eventList.fnFilter('');
            });
        },
        fnRowCallback: function(nRow, aData) {
            if (jQuery('.event-actions', nRow).length) {
                jQuery('.event-actions', nRow).empty();
            } else {
                jQuery('td:eq(3)', nRow).html(jQuery('<div/>', {
                    'class': 'event-actions'
                }));
            }
            jQuery('.event-actions', nRow).append(jQuery('<a/>', {
                'href': '#',
                'class': 'event-action event-action-edit',
                'tooltip': aamLocal.labels['Edit Event']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.launch(jQuery(this), 'event-action-edit');
                _this.launchManageEventDialog(this, aData, nRow);
            }));
            jQuery('.event-actions', nRow).append(jQuery('<a/>', {
                'href': '#',
                'class': 'event-action event-action-delete',
                'tooltip': aamLocal.labels['Delete Event']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.launch(jQuery(this), 'event-action-delete');
                _this.launchDeleteEventDialog(this, aData, nRow);
            }));

            _this.initTooltip(nRow);

            //decorate the data in row
            jQuery('td:eq(0)', nRow).html(
                    jQuery('#event_event option[value="' + aData[0] + '"]').text()
                    );
            jQuery('td:eq(1)', nRow).html(
                    jQuery('#event_bind option[value="' + aData[2] + '"]').text()
                    );
            jQuery('td:eq(2)', nRow).html(
                    jQuery('#event_action option[value="' + aData[3] + '"]').text()
                    );
        },
        oLanguage: {
            sSearch: "",
            oPaginate: {
                sFirst: "&Lt;",
                sLast: "&Gt;",
                sNext: "&gt;",
                sPrevious: "&lt;"
            }
        }
    });
};

/**
 * Launch Add/Edit Event Dialog
 *
 * @param {Object} button
 * @param {Object} aData
 * @param {Object} nRow
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchManageEventDialog = function(button, aData, nRow) {
    var _this = this;

    //reset form and pre-populate if edit mode
    jQuery('input, select', '#manage_event_dialog').val('');
    jQuery('.event-specifier', '#manage_event_dialog').hide();
    jQuery('#status_changed', '#manage_event_dialog').hide();

    if (aData !== null) {
        jQuery('#event_event', '#manage_event_dialog').val(aData[0]);
        jQuery('#event_specifier', '#manage_event_dialog').val(aData[1]);
        jQuery('#event_bind', '#manage_event_dialog').val(aData[2]);
        jQuery('#event_action', '#manage_event_dialog').val(aData[3]);
        jQuery('#event_specifier_' + aData[3], '#manage_event_dialog').val(aData[4]);
        //TODO - Make this more dynamical
        jQuery('#event_event', '#manage_event_dialog').trigger('change');
        jQuery('#event_action', '#manage_event_dialog').trigger('change');
    }

    var buttons = {};
    buttons[aamLocal.labels['Save Event']] = function() {
        //validate first
        var data = _this.validEvent();
        if (data !== null) {
            if (aData !== null) {
                _this.blogTables.eventList.fnUpdate(data, nRow);
            } else {
                _this.blogTables.eventList.fnAddData(data);
            }
            jQuery('#manage_event_dialog').dialog("close");
        } else {
            jQuery('#manage_event_dialog').effect('highlight', 3000);
        }
    };
    buttons[aamLocal.labels['Close']] = function() {
        jQuery('#manage_event_dialog').dialog("close");
    };
    jQuery('#manage_event_dialog').dialog({
        resizable: false,
        height: 'auto',
        width: '40%',
        modal: true,
        buttons: buttons,
        close: function() {
            _this.terminate(
                    jQuery(button),
                    (aData ? 'event-action-edit' : 'event-top-action-add')
                    );
        }
    });
};

/**
 * Validate Event Form
 *
 * @returns {Boolean}
 *
 * @access public
 */
AAM.prototype.validEvent = function() {
    var data = new Array();

    data.push(jQuery('#event_event').val());
    data.push(jQuery('#event_specifier').val());
    data.push(jQuery('#event_bind').val());
    var action = jQuery('#event_action').val();
    data.push(action);
    data.push(jQuery('#event_specifier_' + action).val());
    data.push('--'); //Event Actions Cell

    return data;
};

/**
 * Launch Delete Event Dialog
 *
 * @param {Object} button
 * @param {Object} aData
 * @param {Object} nRow
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchDeleteEventDialog = function(button, aData, nRow) {
    var _this = this;
    var buttons = {};
    buttons[aamLocal.labels['Delete Event']] = function() {
        _this.blogTables.eventList.fnDeleteRow(nRow);
        jQuery('#delete_event').dialog("close");
    };
    buttons[aamLocal.labels['Cancel']] = function() {
        jQuery('#delete_event').dialog("close");
    };
    jQuery('#delete_event').dialog({
        resizable: false,
        height: 'auto',
        modal: true,
        title: aamLocal.labels['Delete Event'],
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'event-action-delete');
        }
    });
};

/**
 * Initialize and Load Post Feature
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initPostTab = function() {
    var _this = this;

    this.initPostTree();

    jQuery('#sidetreecontrol span').bind('click', function() {
        jQuery("#tree").replaceWith('<ul id="tree" class="filetree"></ul>');
        _this.initPostTree();
    });

    jQuery('.post-access-area').buttonset();
    jQuery('.post-access-area input', '#access_dialog').each(function() {
        jQuery(this).bind('click', function() {
            jQuery('#access_dialog .dataTable').hide();
            jQuery('#term_access_' + jQuery(this).val()).show();
            jQuery('#post_access_' + jQuery(this).val()).show();
        });
    });

    this.blogTables.postList = jQuery('#post_list').dataTable({
        sDom: "<'top'lf<'post-top-actions'><'clear'>><'post-breadcrumb'>t<'footer'ip<'clear'>>",
        sPaginationType: "full_numbers",
        bAutoWidth: false,
        bSort: false,
        bServerSide: true,
        sAjaxSource: true,
        fnServerData: function(sSource, aoData, fnCallback, oSettings) {
            oSettings.jqXHR = jQuery.ajax({
                "dataType": 'json',
                "type": "POST",
                "url": aamLocal.ajaxurl,
                "data": aoData,
                "success": fnCallback
            });
        },
        fnServerParams: function(aoData) {
            aoData.push({
                name: 'action',
                value: 'aam'
            });
            aoData.push({
                name: 'sub_action',
                value: 'post_list'
            });
            aoData.push({
                name: '_ajax_nonce',
                value: aamLocal.nonce
            });
            aoData.push({
                name: 'term',
                value: _this.postTerm
            });
        },
        fnInitComplete: function() {
            var a = jQuery('#post_list_wrapper .post-top-actions');

            var filter = jQuery('<a/>', {
                'href': '#',
                'class': 'post-top-action post-top-action-filter',
                'tooltip': aamLocal.labels['Filter Posts by Post Type']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.launch(jQuery(this), 'post-top-action-filter');
                _this.launchFilterPostDialog(this);
            });

            var refresh = jQuery('<a/>', {
                'href': '#',
                'class': 'post-top-action post-top-action-refresh',
                'tooltip': aamLocal.labels['Refresh List']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.blogTables.postList.fnDraw();
            });
            jQuery(a).append(filter);
            jQuery(a).append(refresh);
            _this.initTooltip(a);
        },
        oLanguage: {
            sSearch: "",
            oPaginate: {
                sFirst: "&Lt;",
                sLast: "&Gt;",
                sNext: "&gt;",
                sPrevious: "&lt;"
            },
            sLengthMenu: "_MENU_"
        },
        aoColumnDefs: [
            {
                bVisible: false,
                aTargets: [0, 1, 2]
            }
        ],
        fnRowCallback: function(nRow, aData, iDisplayIndex) { //format data
            jQuery('td:eq(0)', nRow).html(jQuery('<a/>', {
                'href': "#",
                'class': "post-type-post"
            }).bind('click', function(event) {
                event.preventDefault();
                var button = jQuery('.post-action-manage', nRow);
                _this.launch(button, 'post-action-manage');
                _this.launchManageAccessDialog(button, nRow, aData, 'post');
            }).text(aData[3]));
            jQuery('td:eq(2)', nRow).append(jQuery('<div/>', {
                'class': 'post-actions'
            }));

            jQuery('.post-actions', nRow).append(jQuery('<a/>', {
                'href': aData[2].replace('&amp;', '&'),
                'class': 'post-action post-action-edit',
                'target': '_blank',
                'tooltip': aamLocal.labels['Edit']
            }));

            jQuery('.post-actions', nRow).append(jQuery('<a/>', {
                'href': '#',
                'class': 'post-action post-action-manage',
                'tooltip': aamLocal.labels['Manage Access']
            }).bind('click', function(event) {
                event.preventDefault();
                _this.launch(jQuery(this), 'post-action-manage');
                _this.launchManageAccessDialog(this, nRow, aData, 'post');
            }));

            _this.initTooltip(nRow);
        },
        fnDrawCallback: function() {
            jQuery('.post-breadcrumb').addClass('post-breadcrumb-loading');
            _this.loadBreadcrumb();
            jQuery('#event_list_wrapper .clear-table-filter').bind('click', function(event) {
                event.preventDefault();
                jQuery('#event_list_filter input').val('');
                _this.blogTables.postList.fnFilter('');
            });
        },
        fnInfoCallback: function(oSettings, iStart, iEnd, iMax, iTotal, sPre) {
            return (iMax !== iTotal ? _this.clearFilterIndicator() : '');
        }
    });
};

/**
 * Launch Filter Post Dialog (Category Tree)
 *
 * @param {Object} button
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchFilterPostDialog = function(button) {
    var _this = this;
    var buttons = {};
    buttons[aamLocal.labels['Close']] = function() {
        jQuery('#filter_post_dialog').dialog("close");
    };
    jQuery('#filter_post_dialog').dialog({
        resizable: false,
        height: 'auto',
        width: 'auto',
        modal: true,
        buttons: buttons,
        close: function() {
            _this.terminate(jQuery(button), 'post-top-action-filter');
        }
    });
};

/**
 * Launch Manage Access Control for selected post
 *
 * @param {Object} button
 * @param {Object} aData
 * @param {String} type
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launchManageAccessDialog = function(button, nRow, aData, type) {
    var _this = this;
    //prepare form for view
    jQuery('.dataTable', '#access_dialog').hide();

    if (type === 'term') {
        jQuery('#term_access').show();
        jQuery('#term_access_frontend').show();
    } else {
        jQuery('#term_access').hide();
    }

    //by default show frontend access
    jQuery('#post_access_frontend').show();

    //reset the Frontend/Backend radio
    if (jQuery('.post-access-area', '#access_dialog').length) {
        //in case it is Visitor, this section is not rendered
        jQuery('.post-access-area input').attr('checked', false);
        jQuery('.post-access-area #post_area_frontend').attr('checked', true);
        jQuery('.post-access-area').buttonset('refresh');
    }

    //reset all checkboxes
    jQuery('input', '#access_dialog').prop('checked', false);

    //retrieve settings and display the dialog
    var data = this.compileAjaxPackage('get_access', true);
    data.id = aData[0];
    data.type = type;

    jQuery.ajax(aamLocal.ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(response) {
            if (type === 'term') {
                jQuery('.post-access-title').html('All Posts in Term');
                if (response.counter !== -1) {
                    jQuery('.post-access-block', '#access_dialog').show();
                }
            } else {
                jQuery('.post-access-block', '#access_dialog').hide();
                jQuery('.post-access-title').html('Post Access');
            }
            //populate the form
            for (var object in response.settings) {
                for (var area in response.settings[object]) {
                    for (var action in response.settings[object][area]) {
                        var element = '#' + object + '_' + area;
                        element += '_' + action;
                        jQuery(element, '#access_dialog').attr(
                                'checked',
                                (parseInt(response.settings[object][area][action]) === 1 ? true : false)
                                );
                    }
                }
            }

            var buttons = {};
            buttons[aamLocal.labels['Restore Default']] = function() {
                //retrieve settings and display the dialog
                var data = _this.compileAjaxPackage('clear_access', true);
                data.id = aData[0];
                data.type = type;

                jQuery.ajax(aamLocal.ajaxurl, {
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(response) {
                        _this.highlight(nRow, response.status);
                    },
                    error: function() {
                        _this.highlight(nRow, 'failure');
                    }
                });
                jQuery('#access_dialog').dialog("close");
            };

            if (response.counter <= 5) {
                buttons[aamLocal.labels['Apply']] = function() {
                    _this.showMetaboxLoader('#access_dialog');
                    var data = _this.compileAjaxPackage('save_access', true);
                    data.id = aData[0];
                    data.type = type;

                    jQuery('input', '#access_control_area').each(function() {
                        if (jQuery(this).attr('object')) {
                            var name = 'access[' + jQuery(this).attr('object') + '][';
                            name += jQuery(this).attr('area') + '][';
                            name += jQuery(this).attr('action') + ']';
                            data[name] = (jQuery(this).prop('checked') ? 1 : 0);
                        }
                    });

                    jQuery.ajax(aamLocal.ajaxurl, {
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function(response) {
                            _this.highlight(nRow, response.status);
                        },
                        error: function() {
                            _this.highlight(nRow, 'failure');
                        },
                        complete: function() {
                            _this.hideMetaboxLoader('#access_dialog');
                        }
                    });
                    jQuery('#access_dialog').dialog("close");
                };
                jQuery('.aam-lock-message', '#access_dialog').hide();
            } else {
                jQuery('.aam-lock-message', '#access_dialog').show();
            }

            buttons[aamLocal.labels['Close']] = function() {
                jQuery('#access_dialog').dialog("close");
            };

            jQuery('#access_dialog').dialog({
                resizable: false,
                height: 'auto',
                width: '25%',
                modal: true,
                title: 'Manage Access',
                buttons: buttons,
                close: function() {
                    _this.terminate(
                            jQuery(button), 'post-breadcrumb-line-action-manage'
                            );
                }
            });
        },
        error: function() {
            _this.highlight(nRow, 'failure');
        }
    });
};

/**
 * Build Post Breadcrumb
 *
 * @param {Object} response
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.buildPostBreadcrumb = function(response) {
    var _this = this;

    jQuery('.post-breadcrumb').empty();
    //create a breadcrumb
    jQuery('.post-breadcrumb').append(jQuery('<div/>', {
        'class': 'post-breadcrumb-line'
    }));

    for (var i in response.breadcrumb) {
        jQuery('.post-breadcrumb-line').append(jQuery('<a/>', {
            href: '#'
        }).bind('click', {
            term: response.breadcrumb[i][0]
        }, function(event) {
            event.preventDefault();
            _this.postTerm = event.data.term;
            _this.blogTables.postList.fnDraw();
        }).html(response.breadcrumb[i][1])).append(jQuery('<span/>', {
            'class': 'aam-gt'
        }).html('&Gt;'));
    }
    //deactive last one
    jQuery('.post-breadcrumb-line a:last').replaceWith(response.breadcrumb[i][1]);
    jQuery('.post-breadcrumb-line .aam-gt:last').remove();

    jQuery('.post-breadcrumb').append(jQuery('<div/>', {
        'class': 'post-breadcrumb-line-actions'
    }));

    if (/^[\d]+$/.test(this.postTerm)) {
        jQuery('.post-breadcrumb-line-actions').append(jQuery('<a/>', {
            'href': response.link,
            'target': '_blank',
            'class': 'post-breadcrumb-line-action post-breadcrumb-line-action-edit',
            'tooltip': aamLocal.labels['Edit Term']
        }));
        jQuery('.post-breadcrumb-line-actions').append(jQuery('<a/>', {
            'href': '#',
            'class': 'post-breadcrumb-line-action post-breadcrumb-line-action-manage',
            'tooltip': aamLocal.labels['Manager Access']
        }).bind('click', {id: response.breadcrumb[i][0]}, function(event) {
            event.preventDefault();
            _this.launch(this, 'post-breadcrumb-line-action-manage');
            var aData = new Array();
            aData[0] = event.data.id;
            _this.launchManageAccessDialog(
                    this, jQuery('.post-breadcrumb'), aData, 'term'
                    );
        }));
    } else {
        jQuery('.post-breadcrumb-line-actions').append(jQuery('<a/>', {
            'href': 'http://wpaam.com',
            'target': '_blank',
            'class': 'post-breadcrumb-line-action post-breadcrumb-line-action-lock',
            'tooltip': aamLocal.labels['Unlock Default Accesss Control']
        }));
        this.doAction('aam_breadcrumb_action', response);
    }
    _this.initTooltip(jQuery('.post-breadcrumb-line-actions'));
};

/**
 * Load Post Breadcrumb
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.loadBreadcrumb = function() {
    var _this = this;
    var data = this.compileAjaxPackage('post_breadcrumb');
    data.id = _this.postTerm;
    //send the request
    jQuery.ajax(aamLocal.ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(response) {
            _this.buildPostBreadcrumb(response);
        },
        complete: function() {
            jQuery('.post-breadcrumb').removeClass('post-breadcrumb-loading');
        }
    });
};

/**
 * Initialize and Load Category Tree
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.initPostTree = function() {
    var _this = this;

    var data = this.compileAjaxPackage('post_tree');

    jQuery("#tree").treeview({
        url: aamLocal.ajaxurl,
        // add some additional, dynamic data and request with POST
        ajax: {
            data: data,
            type: 'post',
            complete: function() {
                jQuery('#tree li').each(function() {
                    var id = jQuery(this).attr('id');
                    if (id && !jQuery(this).attr('active')) {
                        jQuery('.important', this).html(jQuery('<a/>', {
                            href: '#'
                        }).html(jQuery('.important', this).text()).bind('click', {
                            id: id
                        }, function(event) {
                            event.preventDefault();
                            _this.postTerm = event.data.id;
                            _this.blogTables.postList.fnDraw();
                            jQuery('#filter_post_dialog').dialog('close');
                        }));
                        jQuery(this).attr('active', true);
                    }
                });
            }
        },
        animated: "medium",
        control: "#sidetreecontrol",
        persist: "location"
    });
};

/**
 * Launch the button
 *
 * @param {Object} element
 * @param {String} inactive
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.launch = function(element, inactive) {
    jQuery(element).removeClass(inactive).addClass(inactive + '-active');
};

/**
 * Terminate the button
 *
 * @param {Object} element
 * @param {String} inactive
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.terminate = function(element, inactive) {
    jQuery(element).removeClass(inactive + '-active').addClass(inactive);
};

/**
 * Highlight the specified DOM area
 *
 * @param {String} selector
 * @param {String} status
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.highlight = function(selector, status) {
    if (status === 'success') {
        jQuery(selector).effect("highlight", {
            color: '#98CE90'
        }, 3000);
    } else {
        jQuery(selector).effect("highlight", {
            color: '#FFAAAA'
        }, 3000);
    }
};

/**
 * Grab and build Role Select dropdown
 *
 * @param {String} selector
 * @param {String} selected
 *
 * @returns {void}
 *
 * @access public
 */
AAM.prototype.renderRoleSelectList = function(selector, selected) {
    var _this = this;
    //load Role list
    jQuery(selector).removeClass('dialog-input').addClass('dialog-input-dynamic').empty();
    var data = this.compileAjaxPackage('role_list');
    jQuery.ajax(aamLocal.ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(response) {
            jQuery(selector).append(jQuery('<option/>', {
                'value': ''
            }).html('Choose...'));
            for (var i in response.aaData) {
                jQuery(selector).append(jQuery('<option/>', {
                    'value': response.aaData[i][0]
                }).html(response.aaData[i][2]));
            }

            if (typeof selected !== 'undefined') {
                jQuery(selector).val(selected);
            }

            jQuery(selector).removeClass('dialog-input-dynamic').addClass(
                    'dialog-input'
                    );
        },
        error: function() {
            _this.highlight(selector, 'failure');
        }
    });
};

jQuery(document).ready(function() {
    aamInterface = new AAM();
});