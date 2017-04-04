if (jQuery('#sq_settings').length > 0) {
    sq_blocksettingsseo();
} else {
    jQuery(document).ready(function () {
        sq_blocksettingsseo();
    });
}

function sq_blocksettingsseo() {

///////////////////////////////
    var snippet_timeout;
    //switch click
    jQuery('#sq_settings_form').find('input[name=sq_auto_title],input[name=sq_auto_description]').on('click', function () {
        sq_getSnippet();
    });

    //Custom title/description
    jQuery('#sq_customize').on('click', function () {
        jQuery('#sq_customize_settings').show();
        jQuery('#sq_snippet_disclaimer').show();
        jQuery('#sq_title_description_keywords').addClass('sq_custom_title');
    });

    jQuery('.sq_checkissues').on('click', function () {
        location.href = '?page=sq_seo&action=sq_checkissues&nonce=' + jQuery('#sq_settings_form').find('input[name=nonce]').val();
    });

    //Listen the title field imput for snippet preview
    jQuery('#sq_settings').find('input[name=sq_fp_title]').on('keyup', function () {
        if (snippet_timeout) {
            clearTimeout(snippet_timeout);
        }

        snippet_timeout = setTimeout(function () {
            sq_submitSettings();
            sq_getSnippet();
        }, 1000);

        sq_trackLength(jQuery(this), 'title');
    });

    //Listen the description field imput for snippet preview
    jQuery('#sq_settings').find('textarea[name=sq_fp_description]').on('keyup', function () {
        if (snippet_timeout) {
            clearTimeout(snippet_timeout);
        }

        snippet_timeout = setTimeout(function () {
            sq_submitSettings();
            sq_getSnippet();
        }, 1000);

        sq_trackLength(jQuery(this), 'description');
    });

    jQuery('#sq_settings').find('input[name=sq_fp_keywords]').on('keyup', function () {
        if (snippet_timeout) {
            clearTimeout(snippet_timeout);
        }

        snippet_timeout = setTimeout(function () {
            sq_submitSettings();
        }, 1000);

    });

    //Squirrly On/Off
    if (jQuery('#sq_settings').find('input[name=sq_auto_seo]').length > 0) {
        sq_getSnippet();
    }

    //Listen the favicon switch
    jQuery('#sq_auto_favicon1').on('click', function () {
        jQuery('#sq_favicon').removeClass('deactivated');
    });
    jQuery('#sq_auto_favicon0').on('click', function () {
        jQuery('#sq_favicon').addClass('deactivated');
    });

    //Listen the favicon switch
    jQuery('#sq_auto_sitemap1').on('click', function () {
        jQuery('#sq_sitemap').removeClass('deactivated');
    });
    jQuery('#sq_auto_sitemap0').on('click', function () {
        jQuery('#sq_sitemap').addClass('deactivated');
    });
    jQuery('#sq_auto_jsonld1').on('click', function () {
        jQuery('#sq_jsonld').removeClass('deactivated');

    });
    jQuery('#sq_auto_jsonld0').on('click', function () {
        jQuery('#sq_jsonld').addClass('deactivated');
    });

    jQuery('.sq_social_link').on('click', function () {
        var previewtop = jQuery('#sq_social_media_accounts').offset().top - 100;
        jQuery('html,body').animate({scrollTop: previewtop}, 1000);
    });

    //If select all options in sitemap
    jQuery('#sq_selectall').click(function () {  //on click
        if (this.checked) { // check select status
            jQuery('#sq_sitemap_buid input').each(function () { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"
            });
        } else {
            jQuery('#sq_sitemap_buid input').each(function () { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"
            });
        }
    });

    //Listen the Squirrly Auto seo switch ON
    jQuery('#sq_use_on').on('click', function () {
        jQuery('#sq_settings .sq_seo_switch_condition').show();
        jQuery('#sq_title_description_keywords').removeClass('deactivated');
        jQuery('#sq_social_media').removeClass('deactivated');

        if (jQuery('#sq_settings').find('input[name=sq_auto_sitemap]:checked').val() == 1) {
            jQuery('#sq_sitemap').removeClass('deactivated');
        }

        if (jQuery('#sq_settings').find('input[name=sq_auto_favicon]:checked').val() == 1) {
            jQuery('#sq_favicon').removeClass('deactivated');
        }

        if (jQuery('#sq_settings').find('input[name=sq_auto_jsonld]:checked').val() == 1) {
            jQuery('#sq_jsonld').removeClass('deactivated');
        }

        if (parseInt(jQuery('.sq_count').html()) > 0) {
            var notif = (parseInt(jQuery('.sq_count').html()) - 1);
            if (notif > 0) {
                jQuery('.sq_count').html(notif);
            } else {
                jQuery('.sq_count').html(notif);
                jQuery('.sq_count').hide();
            }
        }
        jQuery('#sq_fix_auto').slideUp('fast');


    });
    //Listen the Squirrly Auto seo switch OFF
    jQuery('#sq_use_off').on('click', function () {
        jQuery('#sq_settings .sq_seo_switch_condition').hide();

        jQuery('#sq_title_description_keywords').addClass('deactivated');
        jQuery('#sq_social_media').addClass('deactivated');
        jQuery('#sq_favicon').addClass('deactivated');
        jQuery('#sq_sitemap').addClass('deactivated');
        jQuery('#sq_jsonld').addClass('deactivated');


        if (parseInt(jQuery('.sq_count').html()) >= 0) {
            var notif = (parseInt(jQuery('.sq_count').html()) + 1);
            if (notif > 0) {
                jQuery('.sq_count').html(notif).show();
            }
        }
        jQuery('#sq_fix_auto').slideDown('show');
    });

    jQuery('#sq_title_description_keywords').on('click', function () {
        if (jQuery('#sq_title_description_keywords').hasClass('deactivated')) {
            jQuery('#sq_use_on').trigger('click');
            jQuery(this).removeClass('deactivated');
        }
    });
    jQuery('#sq_social_media.deactivated').on('click', function () {
        if (jQuery('#sq_social_media').hasClass('deactivated')) {
            jQuery('#sq_use_on').trigger('click');
            jQuery(this).removeClass('deactivated');
        }
    });
    jQuery('#sq_favicon.deactivated').on('click', function () {
        if (jQuery('#sq_favicon').hasClass('deactivated')) {
            jQuery('#sq_use_on').trigger('click');
            jQuery('#sq_auto_favicon1').trigger('click');
            jQuery(this).removeClass('deactivated');
        }
    });
    jQuery('#sq_sitemap.deactivated').on('click', function () {
        if (jQuery('#sq_sitemap').hasClass('deactivated')) {
            jQuery('#sq_use_on').trigger('click');
            jQuery('#sq_auto_sitemap1').trigger('click');
            jQuery(this).removeClass('deactivated');
        }
    });
    jQuery('#sq_jsonld.deactivated').on('click', function () {
        if (jQuery('#sq_jsonld').hasClass('deactivated')) {
            jQuery('#sq_use_on').trigger('click');
            jQuery('#sq_auto_jsonld1').trigger('click');
            jQuery(this).removeClass('deactivated');
        }
    });

///////////////////////////////
////////////////////FIX ACTIONS
    //FIX Google settings
    jQuery('#sq_google_index1').on('click', function () {
        if (parseInt(jQuery('.sq_count').html()) > 0) {
            var notif = (parseInt(jQuery('.sq_count').html()) - 1);
            if (notif > 0) {
                jQuery('.sq_count').html(notif);
            } else {
                jQuery('.sq_count').html(notif);
                jQuery('.sq_count').hide();
            }
        }
        jQuery('#sq_fix_private').slideUp('show');

    });
    jQuery('#sq_google_index0').on('click', function () {
        if (parseInt(jQuery('.sq_count').html()) >= 0) {
            var notif = (parseInt(jQuery('.sq_count').html()) + 1);
            if (notif > 0) {
                jQuery('.sq_count').html(notif).show();
            }
        }
        jQuery('#sq_fix_private').slideDown('show');
    });

    //JsonLD switch types
    jQuery('.sq_jsonld_type').on('change', function () {
        jQuery('.sq_jsonld_types').hide();
        jQuery('.sq_jsonld_' + jQuery('#sq_settings').find('select[name=sq_jsonld_type] option:selected').val()).show();

    });
    //////////////////////////////////////////

    //Upload image from library
    jQuery('#sq_json_imageselect').on('click', function (event) {
        var frame;

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });


        // When an image is selected in the media frame...
        frame.on('select', function () {

            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            // Send the attachment URL to our custom image input field.
            jQuery('input[name=sq_jsonld_logo]').val(attachment.url);

        });

        // Finally, open the modal on click
        frame.open();
    });

    jQuery('#sq_auto_facebook1').on('click', function () {
        jQuery('p.sq_select_ogimage').slideDown();
        jQuery('div.sq_select_ogimage_preview').slideDown();

    });
    jQuery('#sq_auto_facebook0').on('click', function () {
        jQuery('p.sq_select_ogimage').slideUp();
        jQuery('div.sq_select_ogimage_preview').slideUp();
    });

    jQuery('div.sq_fp_ogimage_close').on('click', function (event) {
        jQuery('input[name=sq_fp_ogimage]').val('');
        jQuery('div.sq_fp_ogimage').html('');
        jQuery('div.sq_fp_ogimage_close').hide();
    });
    //Upload image from library
    jQuery('#sq_fp_imageselect').on('click', function (event) {
        var frame;

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });


        // When an image is selected in the media frame...
        frame.on('select', function () {

            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();

            // Send the attachment URL to our custom image input field.
            jQuery('input[name=sq_fp_ogimage]').val(attachment.url);
            if (attachment.url != '') {
                jQuery('div.sq_fp_ogimage').html('<img src="' + attachment.url + '">');
                jQuery('div.sq_fp_ogimage_close').show();
            }else{
                jQuery('div.sq_fp_ogimage').html('');
                jQuery('div.sq_fp_ogimage_close').hide();
            }
        });

        // Finally, open the modal on click
        frame.open();
    });
}

//Submit the settings
function sq_submitSettings() {
    var sq_sitemap = [];
    var serialize = jQuery('#sq_settings').find('input[class=sq_sitemap]').serializeArray()
    jQuery(serialize).each(function () {
        sq_sitemap.push(jQuery(this).attr('value'));
    });

    var sq_sitemap_show = [];
    var serialize = jQuery('#sq_settings').find('input[class=sq_sitemap_show]').serializeArray()
    jQuery(serialize).each(function () {
        sq_sitemap_show.push(jQuery(this).attr('value'));
    });

    jQuery.post(
        sqQuery.ajaxurl,
        {
            action: 'sq_settingsseo_update',
// --
            sq_use: jQuery('#sq_settings').find('input[name=sq_use]:checked').val(),
            sq_auto_title: jQuery('#sq_settings').find('input[name=sq_auto_title]:checked').val(),
            sq_auto_description: jQuery('#sq_settings').find('input[name=sq_auto_description]:checked').val(),
            sq_auto_canonical: jQuery('#sq_settings').find('input[name=sq_auto_canonical]:checked').val(),
            sq_auto_meta: jQuery('#sq_settings').find('input[name=sq_auto_meta]:checked').val(),
            sq_auto_favicon: jQuery('#sq_settings').find('input[name=sq_auto_favicon]:checked').val(),
            sq_auto_facebook: jQuery('#sq_settings').find('input[name=sq_auto_facebook]:checked').val(),
            sq_auto_twitter: jQuery('#sq_settings').find('input[name=sq_auto_twitter]:checked').val(),
            sq_auto_twittersize: jQuery('#sq_settings').find('input[name=sq_auto_twittersize]:checked').val(),
            sq_og_locale: jQuery('#sq_settings').find('select[name=sq_og_locale] option:selected').val(),
//--
            sq_twitter_account: jQuery('#sq_settings').find('input[name=sq_twitter_account]').val(),
            sq_facebook_account: jQuery('#sq_settings').find('input[name=sq_facebook_account]').val(),
            sq_google_plus: jQuery('#sq_settings').find('input[name=sq_google_plus]').val(),
            sq_linkedin_account: jQuery('#sq_settings').find('input[name=sq_linkedin_account]').val(),
            sq_pinterest_account: jQuery('#sq_settings').find('input[name=sq_pinterest_account]').val(),
            sq_instagram_account: jQuery('#sq_settings').find('input[name=sq_instagram_account]').val(),
//--
            sq_auto_sitemap: jQuery('#sq_settings').find('input[name=sq_auto_sitemap]:checked').val(),
            sq_auto_feed: jQuery('#sq_settings').find('input[name=sq_auto_feed]:checked').val(),
            sq_sitemap: sq_sitemap,
            sq_sitemap_show: sq_sitemap_show,
            sq_sitemap_frequency: jQuery('#sq_settings').find('select[name=sq_sitemap_frequency] option:selected').val(),
            sq_sitemap_ping: jQuery('#sq_settings').find('input[name=sq_sitemap_ping]:checked').val(),
// --
            sq_auto_jsonld: jQuery('#sq_settings').find('input[name=sq_auto_jsonld]:checked').val(),
            sq_jsonld_type: jQuery('#sq_settings').find('select[name=sq_jsonld_type] option:selected').val(),
            sq_jsonld_name: jQuery('#sq_settings').find('input[name=sq_jsonld_name]').val(),
            sq_jsonld_jobTitle: jQuery('#sq_settings').find('input[name=sq_jsonld_jobTitle]').val(),
            sq_jsonld_logo: jQuery('#sq_settings').find('input[name=sq_jsonld_logo]').val(),
            sq_jsonld_telephone: jQuery('#sq_settings').find('input[name=sq_jsonld_telephone]').val(),
            sq_jsonld_contactType: jQuery('#sq_settings').find('select[name=sq_jsonld_contactType] option:selected').val(),
            sq_jsonld_description: jQuery('#sq_settings').find('textarea[name=sq_jsonld_description]').val(),
//--
            sq_auto_seo: jQuery('#sq_settings').find('input[name=sq_auto_seo]:checked').val(),
            sq_fp_title: jQuery('#sq_settings').find('input[name=sq_fp_title]').val(),
            sq_fp_description: jQuery('#sq_settings').find('textarea[name=sq_fp_description]').val(),
            sq_fp_keywords: jQuery('#sq_settings').find('input[name=sq_fp_keywords]').val(),
            sq_fp_ogimage: jQuery('#sq_settings').find('input[name=sq_fp_ogimage]').val(),
// --
            ignore_warn: jQuery('#sq_settings').find('input[name=ignore_warn]:checked').val(),
// --
            sq_google_analytics: jQuery('#sq_settings').find('input[name=sq_google_analytics]').val(),
            sq_facebook_insights: jQuery('#sq_settings').find('input[name=sq_facebook_insights]').val(),
            sq_facebook_analytics: jQuery('#sq_settings').find('input[name=sq_facebook_analytics]').val(),

            sq_pinterest: jQuery('#sq_settings').find('input[name=sq_pinterest]').val(),
            sq_auto_amp: jQuery('#sq_settings').find('input[name=sq_auto_amp]:checked').val(),
            // --

            nonce: sqQuery.nonce
        }
    ).done(function () {
        sq_showSaved(2000);
    }, 'json');

}