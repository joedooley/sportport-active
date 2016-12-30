if (jQuery('#sq_settings').length > 0) {
    sq_blockmenu();
} else {
    jQuery(document).ready(function () {
        sq_blockmenu();
    });
}


function ctl_setThemeColors(background, button, text) {
    jQuery('#sq_settings fieldset').css('background-color', background);
    jQuery('#sq_settings input[type="submit"]').css('background-color', button);
    jQuery('#sq_settings input[type="submit"]').css('color', text);
    jQuery('#sq_settings legend > span').css('color', text);
    jQuery('#sq_settings legend label').css('color', text);
    jQuery('#sq_settings_body fieldset legend > span a').css('color', text);

}

function sq_blockmenu() {
///////////////////////////////

    //Go to dashboard listener
    jQuery("#sq_goto_dashboard").bind('click', function () {
        location.href = "?page=sq_dashboard";
    });

    jQuery('input[name=sq_update]').bind('click', function () {
        jQuery('#sq_settings_form').submit();
    });

    jQuery('input[name=sq_restore]').bind('click', function () {
        jQuery('.sq_settings_restore').show();
    });

    jQuery('.sq_settings_restore').find('.sq_close').bind('click', function () {
        jQuery('.sq_settings_restore').find('textarea').val('');
        jQuery('.sq_settings_restore').hide();
    });

    //Go to settings listener
    jQuery("#sq_goto_seo").bind('click', function () {
        location.href = "?page=sq_seo";
    });

    //Go to settings listener
    jQuery("#sq_goto_settings").bind('click', function () {
        location.href = "?page=sq_settings";
    });

    //Set the squirrly seo style according to wp colors
    var colorwait = setInterval(function () {
        if (jQuery('#adminmenuback').is(':visible')) {
            clearInterval(colorwait);

            ctl_setThemeColors(jQuery('#adminmenuback').css('background-color')
                    , jQuery('#adminmenu li.current a.menu-top').css('background-color')
                    , jQuery('#adminmenu a').css('color'));
        }

    }, 100);

    //switch click
    jQuery('#sq_settings_form').find('input[type=radio]').bind('change', function () {
        sq_submitSettings();
    });

    //Custom title/description
    jQuery('#sq_customize').bind('click', function () {
        jQuery('#sq_customize_settings').show();
        jQuery('#sq_snippet_disclaimer').show();
        jQuery('#sq_title_description_keywords').addClass('sq_custom_title');
    });

    //Login
    jQuery('.sq_login_link').bind('click', function () {
        var previewtop = jQuery('#sq_settings_login').offset().top - 100;
        jQuery('html,body').animate({scrollTop: previewtop}, 1000);
    });

    if (jQuery('#sq_canonical').length > 0) {
        jQuery('#titlewrap').after(jQuery('#sq_canonical'));
        jQuery('#sq_canonical').show();
    }
}

//Show the title length in post editor
function sq_trackLength(field, type) {
    var min = 0;
    var max = 0;
    if (typeof field === 'undefined')
        return;

    if (type === 'title' || type === 'wp_title') {
        min = 10;
        max = 75;
    } else
    if (type === 'description') {
        min = 70;
        max = 165;
    }
    if (min > 0 && min > field.val().length)
        jQuery('#sq_' + type + '_info').html(__snippetshort);
    else
    if (max > 0 && max < field.val().length)
        jQuery('#sq_' + type + '_info').html(__snippetlong);
    else
    if (max > 0) {
        jQuery('#sq_' + type + '_info').html(field.val().length + '/' + max);
    }
}

//get the snippet in settings and post editor
function sq_getSnippet(url, show_url) {
    if (jQuery('#sq_snippet').length == 0) {
        return;
    }

    if (typeof url === 'undefined') {
        url = '';
    }
    if (typeof show_url === 'undefined') {
        show_url = '';
    }
    jQuery('#sq_snippet_ul').addClass('sq_minloading');

    jQuery('#sq_snippet_title').html('');
    jQuery('#sq_snippet_url').html('');
    jQuery('#sq_snippet_description').html('');
    jQuery('#sq_snippet_keywords').hide();
    jQuery('#sq_snippet').show();
    jQuery('#sq_snippet_update').hide();
    jQuery('#sq_snippet_customize').hide();
    jQuery('#ogimage_preview').hide();

    setTimeout(function () {
        jQuery.post(
                sqQuery.ajaxurl,
                {
                    action: 'sq_get_snippet',
                    url: url,
                    nonce: sqQuery.nonce
                }
        ).done(function (response) {
            jQuery('#sq_snippet_ul').removeClass('sq_minloading');
            jQuery('#sq_snippet_update').show();
            jQuery('#sq_snippet_customize').show();
            jQuery('#sq_snippet_keywords').show();
            jQuery('#ogimage_preview').show();
            if (response) {
                jQuery('#sq_snippet_title').html(response.title);
                if (show_url !== '')
                    jQuery('#sq_snippet_url').html('<a href="' + url + '" target="_blank">' + show_url + '</a>');
                else
                    jQuery('#sq_snippet_url').html(response.url);

                jQuery('#sq_snippet_description').html(response.description);
            }
        }).fail(function () {
            jQuery('#sq_snippet_ul').removeClass('sq_minloading');
            jQuery('#sq_snippet_update').show();
        }, 'json');
    }, 500);
}

//Show user status in Squirrly > Account info
function sq_getUserStatus() {
    jQuery('#sq_userinfo').addClass('sq_loading');
    jQuery('#sq_userstatus').addClass('sq_loading');

    jQuery.getJSON(
            __api_url + 'sq/user/status/?callback=?',
            {
                token: __token,
                lang: (document.getElementsByTagName("html")[0].getAttribute("lang") || window.navigator.language)
            }
    ).success(function (response) {
        checkResponse(response);

        jQuery('#sq_userinfo').removeClass('sq_loading').removeClass('sq_error');
        jQuery('#sq_userstatus').removeClass('sq_loading').removeClass('sq_error');
        if (typeof response.info !== 'undefined' && response.info !== '') {
            jQuery('#sq_userinfo').html(response.info);
        }
        if (typeof response.stats !== 'undefined' && response.stats !== '') {
            jQuery('#sq_userstatus').html(response.stats);
        }
        if (typeof response.data !== 'undefined' && typeof response.data.user_registered_date !== 'undefined') {
            var currentDate = new Date();
            var day = currentDate.getDate();
            if (day.toString().length === 1)
                day = '0' + day.toString();
            var month = currentDate.getMonth() + 1;
            if (month.toString().length === 1)
                month = '0' + month.toString();
            var year = currentDate.getFullYear();
            var currDate = year + '-' + month + '-' + day;
            var passed = ((new Date(currDate).getTime() - new Date(response.data.user_registered_date).getTime()) / (24 * 60 * 60 * 1000));
            ;
            if (passed <= 3 && jQuery('#sq_survey').length > 0)
                jQuery('#sq_survey').show();
        }
    }).error(function () {
        // jQuery('#sq_userinfo').removeClass('sq_loading');
        jQuery('#sq_userinfo').html('');
        jQuery('#sq_userstatus').html('');
    });
    jQuery('#sq_survey').show();
}

//Recheck the user rank in Squirrly > Performance analytics
function sq_recheckRank(post_id) {
    jQuery('.sq_rank_column_button_recheck').hide();
    jQuery('#sq_rank_value' + post_id).html('').addClass('sq_loading');
    jQuery.post(
            sqQuery.ajaxurl,
            {
                action: 'sq_recheck',
                post_id: post_id,
                nonce: sqQuery.nonce
            }
    ).done(function (response) {
        if (typeof response.rank !== 'undefined') {
            jQuery('#sq_rank_value' + post_id).html(response.rank).removeClass('sq_loading');
        } else {
            jQuery('#sq_rank_value' + post_id).html('Error').removeClass('sq_loading');
        }
        setTimeout(function () {
            jQuery('.sq_rank_column_button_recheck').show();
        }, 10000)


    }).fail(function () {
        jQuery('#sq_rank_value' + post_id).html('Error').removeClass('sq_loading');
        jQuery('.sq_rank_column_button_recheck').show();
    }, 'json');
}

//Show user status in Squirrly > Account info
function sq_getSlides(category) {
    if (jQuery('#sq_help' + category + 'slides').length == 0) {
        return;
    }

    jQuery.getJSON(
            __api_url + 'sq/help/slides?callback=?',
            {
                category: category,
                lang: (document.getElementsByTagName("html")[0].getAttribute("lang") || window.navigator.language)
            }
    ).success(function (response) {
        jQuery('#sq_help' + category + 'slides').removeClass('sq_loading').removeClass('sq_error');
        if (typeof response.html !== 'undefined' && response.html !== '') {
            jQuery('#sq_help' + category + 'slides').html(response.html).show();
        }
    });
}

function sq_getHelp(category, zone) {
    var loadingAjax = true;

    if (zone == 'content' && jQuery('#sq_help' + category + zone).length == 0) {
        sq_getHelp(category, 'side');
        return;
    } else {
        if (jQuery('#sq_help' + category + zone).length == 0) {
            return;
        }
    }

    jQuery('#sq_help' + category + zone).addClass('sq_loading');
    jQuery.getJSON(
            __api_url + 'sq/help/?callback=?',
            {
                token: __token,
                user_url: __blog_url,
                category: category,
                zone: zone,
                lang: (document.getElementsByTagName("html")[0].getAttribute("lang") || window.navigator.language)
            }
    ).success(function (response) {
        checkResponse(response);
        loadingAjax = false;

        jQuery('#sq_help' + category + zone).removeClass('sq_loading').removeClass('sq_error');
        if (typeof response.html !== 'undefined' && response.html !== '') {
            jQuery('#sq_help' + category + zone).html(response.html).show();
            if (typeof response.remained_here !== 'undefined') {
                var active_help = category;
                if (response.remained_here > 0) {
                    if (category === 'settingsseo') {
                        active_help = 'settings';
                    }
                } else {
                    active_help = '';
                }

                jQuery.post(
                        sqQuery.ajaxurl,
                        {
                            action: 'sq_active_help',
                            active_help: active_help,
                            nonce: sqQuery.nonce
                        });
            }
        }
        if (typeof response.side !== 'undefined' && response.side !== '') {
            jQuery('#sq_help' + category + 'side').html(response.side).show();
        }
    }).error(function () {
        loadingAjax = false;
        jQuery('#sq_help' + category + zone).removeClass('sq_loading');
        jQuery('#sq_help' + category + 'content').html('Lost connection with the server. Please make sure you whitelisted the IP from https://api.squirrly.co').show();
    });

    setTimeout(function () {
        if (loadingAjax) {
            jQuery('#sq_help' + category + zone).removeClass('sq_loading').addClass('sq_error').show();
            jQuery('#sq_help' + category + 'content').html('Lost connection with the server. Please make sure you whitelisted the IP from https://api.squirrly.co');
        }
    }, 20000);

}
function checkResponse(response) {
    if (typeof response.error !== 'undefined') {
        if (response.error === 'invalid_token') {
            jQuery.post(
                    sqQuery.ajaxurl,
                    {
                        action: 'sq_reset', nonce: sqQuery.nonce
                    }
            ).done(function (response) {
                if (typeof response.reset !== 'undefined')
                    if (response.reset === 'success')
                        location.href = "?page=sq_dashboard";
            }, 'json');
        }
    }
}

function showSaved(time) {
    jQuery("#sq_settings").prepend('<div class="sq_savenotice sq_absolute" ><span class="sq_success">Saved!</span></div>');

    if (typeof sq_help_reload == 'function') {
        sq_help_reload();
    }

    if (typeof time !== 'undefined') {
        setTimeout(function () {
            jQuery('.sq_savenotice').hide();


        }, time);
    }
}
