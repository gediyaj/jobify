/**
 * ======================================================================
 * LICENSE: This file is subject to the terms and conditions defined in *
 * file 'license.txt', which is part of this source code package.       *
 * ======================================================================
 */

AAM.prototype.myFeature = function() {
    //Send Email to Us
    jQuery('.my-feature-message-action').bind('click', function(event) {
        event.preventDefault();
        jQuery('#aam_message').trigger('click');
    });
};

jQuery(document).ready(function() {
    aamInterface.addAction('aam_init_features', function() {
        aamInterface.myFeature();
    });
});