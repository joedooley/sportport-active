(function($){
"use strict";

	$(document).on('change', 'select[id^="wc_settings_isb"]', function() {

		var curr_settings = [];

		curr_settings.push($('#wc_settings_isb_style').val());
		curr_settings.push($('#wc_settings_isb_color').val());
		curr_settings.push($('#wc_settings_isb_position').val());
		curr_settings.push($('#wc_settings_isb_special').val());
		curr_settings.push($('#wc_settings_isb_special_text').val());

		var curr_data = {
			action: 'isb_respond',
			data: curr_settings
		};

		$.post(isb.ajax, curr_data, function(response) {
			if (response) {
				$('#isb_preview').empty().append(response);
			} else {
				alert('Error!');
			}
		});

		return false;

	});

})(jQuery);