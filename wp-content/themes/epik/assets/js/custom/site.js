(function ($, window, document, undefined) {

	var modal = new vanillaModal.VanillaModal({
		onBeforeOpen: function (e) {
			console.log('onBeforeOpen hook', e, this);
		},
		onOpen: function (e) {
			console.log('onOpen hook', e, this);
		},
		onBeforeClose: function (e) {
			console.log('onBeforeClose hook', e, this);
		},
		onClose: function (e) {
			console.log('onClose hook', e, this);
		}
	});

})(jQuery, window, document);
