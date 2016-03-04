jQuery(document).ready(function ($) {
	$('#yoast_wpseo_newssitemap-keywords').on('keyup', function () {
		if ($(this).val().split(",").length > 9) {
			$(this).addClass('wpseo-news-input-error');
			$(this).parent().find('div:first').css('font-weight', 'bold').css('color', '#ff0000');
		} else {
			$(this).removeClass('wpseo-news-input-error');
			$(this).parent().find('div:first').css('font-weight', 'normal').css('color', '#000');
		}
	});
});