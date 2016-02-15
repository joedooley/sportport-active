jQuery(function ($) {
	$('#_woogle_category').on('focus change', function () {
		$(this).autocomplete('search', this.value);
	}).autocomplete({
		source: Woogle_Product.category_top_level_options,
		minLength: 0,
		delay: 500,
		search: function (event, ui) {
			if (this.value.length < 1) {
				$(this).autocomplete('option', 'source', Woogle_Product.category_top_level_options);
			} else {
				var topLevelCategory = this.value.split(' > ')[0];
				if (Woogle_Product.category_option_groups[topLevelCategory] != null) {
					$(this).autocomplete('option', 'source', Woogle_Product.category_option_groups[topLevelCategory]);
				} else {
					$(this).autocomplete('option', 'source', Woogle_Product.category_options);
				}
			}
		},
		select: function (event, ui) {
			if (ui.item.value.length > 0 && ui.item.value.indexOf('>') < 0) {
				var $input = $(this);
				setTimeout(function () {
					$input.autocomplete('search', ui.item.value);
				}, 300);
			}
		}
	});
	
	$('#_woogle_color').autocomplete({
		source: Woogle_Product.color_options,
		minLength: 0,
		delay: 500
	}).on('focus', function (event) {
		$(this).autocomplete( 'search', this.value );
	});
	
	$('#_woogle_size').autocomplete({
		source: Woogle_Product.size_options,
		minLength: 0,
		delay: 500
	}).on('focus', function (event) {
		$(this).autocomplete( 'search', this.value );
	});
});