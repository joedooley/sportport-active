if (jQuery('#sq_settings').length > 0) {
    sq_blocksettings();
} else {
    jQuery(document).ready(function () {
        sq_blocksettings();
    });
}

function sq_blocksettings() {
    jQuery('#sq_selectall').click(function (event) {  //on click
        if (this.checked) { // check select status
            jQuery('#sq_post_type_option input').each(function () { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"
            });
        } else {
            jQuery('#sq_post_type_option input').each(function () { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"
            });
        }
    });

}
//Submit the settings
function sq_submitSettings() {

    var sq_post_types = [];
    var serialize = jQuery('#sq_settings').find('input[class=sq_post_types]').serializeArray()
    jQuery(serialize).each(function () {
        sq_post_types.push(jQuery(this).attr('value'));
    });

    jQuery.post(
            sqQuery.ajaxurl,
            {
                action: 'sq_settings_update',
                sq_post_types: sq_post_types,
                sq_keyword_help: jQuery('#sq_settings').find('input[name=sq_keyword_help]:checked').val(),
                sq_keyword_information: jQuery('#sq_settings').find('input[name=sq_keyword_information]:checked').val(),
// --
                sq_google_country: jQuery('#sq_settings').find('select[name=sq_google_country] option:selected').val(),
                sq_google_country_strict: jQuery('#sq_settings').find('input[name=sq_google_country_strict]:checked').val(),
                sq_google_ranksperhour: jQuery('#sq_settings').find('select[name=sq_google_ranksperhour] option:selected').val(),
// --
                sq_sla: jQuery('#sq_settings').find('input[name=sq_sla]:checked').val(),
                sq_keywordtag: jQuery('#sq_settings').find('input[name=sq_keywordtag]:checked').val(),
                sq_local_images: jQuery('#sq_settings').find('input[name=sq_local_images]:checked').val(),
// --
                sq_google_wt: jQuery('#sq_settings').find('input[name=sq_google_wt]').val(),
                sq_bing_wt: jQuery('#sq_settings').find('input[name=sq_bing_wt]').val(),
                sq_alexa: jQuery('#sq_settings').find('input[name=sq_alexa]').val(),
// --
                nonce: sqQuery.nonce
            }
    ).done(function () {
        showSaved(2000);
    }, 'json');

}