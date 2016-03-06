/* ==========================================================
 * helper.js
 * ==========================================================
 * Copyright 2015 Thomas Griffin.
 * https://thomasgriffin.io
 * ========================================================== */
jQuery(document).ready(function($){
	$(document).on('OptinMonsterPreOptin', function(event, optin, object){
		// Send a request to force optin to work even if no provider is set.
		var data 		 = optin.optin_data;
		data.no_provider = true;
		object.setProp('optin_data', data);
		
		// Now make an ajax request to make the optin locally.
		data.action = 'mailpoet';
		data.nonce  = omapi_localized.nonce;
		data.optin  = optin.original_optin;
		$.post(omapi_localized.ajax, data, function(){}, 'json');
	});
});