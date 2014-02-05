/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

function aamMigration() {
    this.steps = {
        1: {
            id: 'aam_collect_data',
            action: 'collect'
        },
        2: {
            id: 'aam_migrating',
            action: 'migrate'
        },
        3: {
            id: 'aam_cleanup',
            action: 'cleanup'
        },
        4: {
            id: 'aam_migration_complete',
            action: 'complete'
        }
    };
}

aamMigration.prototype.run = function() {
    this.activateStep(1);
};

aamMigration.prototype.activateStep = function(index) {
    if (typeof this.steps[index] !== 'undefined') {
        jQuery('#' + this.steps[index].id).css('opacity', '1');
        jQuery('#' + this.steps[index].id).removeClass('migration-step-pending').addClass('migration-step-running');
        this.sendRequest(index);
    }
};

aamMigration.prototype.completeStep = function(index) {
    jQuery('#' + this.steps[index].id).removeClass('migration-step-running').addClass('migration-step-completed');
};

aamMigration.prototype.failedStep = function(index) {
    jQuery('#' + this.steps[index].id).removeClass('migration-step-running').addClass('migration-step-failed');
};

aamMigration.prototype.sendRequest = function(index) {
    var _this = this;
    jQuery.ajax(aamMigrateLocal.ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'aam',
            sub_action: 'migrate',
            step: this.steps[index].action,
            _ajax_nonce: aamMigrateLocal.nonce
        },
        success: function(response) {
            if (response.status === 'success') {
                if (parseInt(response.stop) === 1) {
                    _this.completeStep(index);
                    _this.activateStep(index + 1);
                } else {
                    _this.sendRequest(index);
                }
            } else {
                _this.failedStep(index);
            }
        },
        error: function() {
            _this.failedStep(index);
        }
    });
};


jQuery(document).ready(function() {
    jQuery('#aam_migrate').bind('click', function() {
        var holder = jQuery('<div/>', {'class': 'migration-dialog'});
        //add header
        jQuery(holder).append(jQuery('<div/>', {
            'class': 'migration-header'
        }).html('Migration Process Status'));
        //add notice
        jQuery(holder).append(jQuery('<div/>', {
            'class': 'migration-notice'
        }).html('Warning! Please take a minute to read about migration process. <a href="http://wpaam.com/forum/viewtopic.php?f=2&t=20" target="_blank">Read more</a>'));
        jQuery(holder).append(jQuery('<div/>', {
            'class': 'migration-notice'
        }).html('Please do not reload the page until process is complete! <a href="#" id="start_migration">Start the Migration</a> or <a href="#" id="dont_bother">No Thank you</a>'));

        //add migration steps
        var step_holder = jQuery('<div/>', {'class': 'migration-steps'});
        jQuery(step_holder).append(jQuery('<div/>', {
            'class': 'migration-step migration-step-pending',
            'id': 'aam_collect_data'
        }).html('Collecting AAM Data'));
        jQuery(step_holder).append(jQuery('<div/>', {
            'class': 'migration-step migration-step-pending',
            'id': 'aam_migrating'
        }).html('Migrating Settings'));
        jQuery(step_holder).append(jQuery('<div/>', {
            'class': 'migration-step migration-step-pending',
            'id': 'aam_cleanup'
        }).html('Migration Clean-up'));
        jQuery(step_holder).append(jQuery('<div/>', {
            'class': 'migration-step migration-step-pending',
            'id': 'aam_migration_complete'
        }).html('Migration Completed (You can Reload the Page)'));

        jQuery(holder).append(step_holder);

        jQuery('body').append(holder);

        jQuery('#start_migration').bind('click', function(event) {
            event.preventDefault();
            migration.run();
        });

        jQuery('#dont_bother').bind('click', function(event) {
            event.preventDefault();
            jQuery.ajax(aamMigrateLocal.ajaxurl, {
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'aam',
                    sub_action: 'migrate',
                    step: 'complete',
                    _ajax_nonce: aamMigrateLocal.nonce
                },
                success: function(response) {
                    if (response.status === 'success') {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Failed to Update Database');
                }
            });
        });

        //run the migration process
        var migration = new aamMigration();
    });
});