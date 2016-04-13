(function (document, $, undefined) {

	$('.single-product div.product .product_title').click(function () {
		$('.sku').toggle();
	})




})(document, jQuery);


jQuery(function ($) {

	// Quantity buttons
	// $('div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)').addClass('buttons_added').append('<input type="button" value="+" class="plus"
	// />').prepend('<input type="button" value="-" class="minus" />');


	$('div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)').append('<div class="inc button buttons_added">+</div><div class="dec button buttons_added">-</div>');

	$(".buttons_added").on("click", function () {

		var $button  = $(this);
		var oldValue = $button.parent().find("input").val();

		if ($button.text() == "+") {
			var newVal = parseFloat(oldValue) + 1;
		} else {
			// Don't allow decrementing below zero
			if (oldValue > 0) {
				var newVal = parseFloat(oldValue) - 1;
			} else {
				newVal = 0;
			}
		}

		$button.parent().find("input").val(newVal);
	});


	//var id = $button.attr("id");
	//$.ajax({
	//	type: "POST",
	//	url: "dosomething.php?id=" + id + "&newvalue=" + newVal,
	//	success: function () {
	//		$button.parent().find("input").val(newVal);
	//	}
	//});

});






	//$(document).on('click', '.plus, .minus', function () {
	//
	//	// Get values
	//	var $qty       = $(this).closest('.quantity').find('.qty'),
	//	    currentVal = parseFloat($qty.val()),
	//	    max        = parseFloat($qty.attr('max')),
	//	    min        = parseFloat($qty.attr('min')),
	//	    step       = $qty.attr('step');
	//
	//	// Format values
	//	if (!currentVal || currentVal === '' || currentVal === 'NaN') currentVal = 0;
	//	if (max === '' || max === 'NaN') max = '';
	//	if (min === '' || min === 'NaN') min = 0;
	//	if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN') step = 1;
	//
	//	// Change the value
	//	if ($(this).is('.plus')) {
	//
	//		if (max && ( max == currentVal || currentVal > max )) {
	//			$qty.val(max);
	//		} else {
	//			$qty.val(currentVal + parseFloat(step));
	//		}
	//
	//	} else {
	//
	//		if (min && ( min == currentVal || currentVal < min )) {
	//			$qty.val(min);
	//		} else if (currentVal > 0) {
	//			$qty.val(currentVal - parseFloat(step));
	//		}
	//
	//	}
	//
	//	// Trigger change event
	//	$qty.trigger('change');
	//
	//});
