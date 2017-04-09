var sq_script = 'kr';

function sq_getKR() {
    var loadingAjax = true;
    jQuery('#sq_krinfo').addClass('sq_loading');
    jQuery.getJSON(
        __api_url + 'sq/kr/?callback=?',
        {
            token: __token,
            user_url: __blog_url,
            country: 'com',
            lang: (document.getElementsByTagName("html")[0].getAttribute("lang") || window.navigator.language)
        }
    ).success(function (response) {
        loadingAjax = false;

        jQuery('#sq_krinfo').removeClass('sq_loading').removeClass('sq_error');
        if (typeof response.html !== 'undefined' && response.html !== '') {
            jQuery('#sq_krinfo').html(response.html);
        }

    }).error(function () {
        loadingAjax = false;
        jQuery('#sq_krinfo').find('fieldset').removeClass('sq_loading');
        jQuery('#sq_krinfo').html('Lost connection with the server. Please make sure you whitelisted the IP from https://api.squirrly.co').show();
    });

    setTimeout(function () {
        if (loadingAjax) {
            jQuery('#sq_krinfo').removeClass('sq_loading').addClass('sq_error').show();
            jQuery('#sq_krinfo').html('Lost connection with the server. Please make sure you whitelisted the IP from https://api.squirrly.co');
        }
    }, 20000);

}