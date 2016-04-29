if (jQuery('.sq_helpcontent').length > 0) {
    sq_blockdashboard();
} else {
    jQuery(document).ready(function () {
        sq_blockdashboard();
    });
}

function sq_blockdashboard(){
    if (jQuery('#sq_settings_login').length > 0) {
        jQuery('#sq_settings_login').after(jQuery('.sq_helpcontent'));
    }
    jQuery('.sq_helpcontent').show();
    jQuery('.sq_slidelist a').bind('click', function () {
        var li = jQuery(this).parent('li');
        li.addClass('sq_loading');
        li.find('a').hide();
        li.find('div').hide();
        if (li.find('iframe').length == 0) {
            li.append('<iframe src="//www.slideshare.net/slideshow/embed_code/' + li.find('a').attr('rel') + '" width="327" height="250" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="display:none; max-width: 100%;  max-height: 100%;" allowfullscreen> </iframe>');
        }
        li.find('iframe').show();
        li.append('<span>x</span>');

        li.find('span').bind('click', function () {
            var li = jQuery(this).parent('li');
            li.find('a').show();
            li.find('div').show();
            li.find('iframe').hide();
            jQuery(this).remove();
        });
    });

};