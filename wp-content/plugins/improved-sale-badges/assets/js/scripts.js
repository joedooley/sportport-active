(function($){
"use strict";

	if ( $('.isb_variable').length > 0 ) {

		var curr = $('input[name=variation_id]').val();

		if ( curr !== '' ) {
			$('.isb_variable[data-id='+curr+']').show();
		}

		$(document).on( 'change', 'input[name=variation_id]', function() {

			if ( $('input[name=variation_id]').val() == '' ) {
				$('.isb_variable').hide();
				return;
			}
			var curr = $('input[name=variation_id]').val();

			$('.isb_variable').hide();
			$('.isb_variable[data-id='+curr+']').show();
		});

	}

	$('.isb_scheduled_sale').each( function() {

		var curr = $(this).find('span.isb_scheduled_time');

		if ( curr.text() == '' ) {
			return;
		}

		var timestamp = curr.text()*1000 - Date.now();

		timestamp /= 1000; // from ms to seconds

		function component(x, v) {
			return Math.floor(x / v);
		}

		var $div = curr;

		function do_it() {
				timestamp--;

			var days    = component(timestamp, 24 * 60 * 60),
				hours   = component(timestamp,      60 * 60) % 24,
				minutes = component(timestamp,           60) % 60,
				seconds = component(timestamp,            1) % 60;

			if ( curr.hasClass('isb_scheduled_compact') ) {
				$div.html( ( days !== 0 ? days + '<span>d</span>' : '' ) + hours + ':' + minutes + ':' + seconds);
			}
			else {
				$div.html( ( days !== 0 ? days + ' days, ' : '' ) + hours + ':' + minutes + ':' + seconds);
			}

		}
		do_it();
		setInterval(function() {
			do_it();
		}, 1000);

	});

})(jQuery);