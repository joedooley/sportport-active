/* ==========================================================
 * edit.js
 * ==========================================================
 * Copyright 2015 Thomas Griffin.
 * https://thomasgriffin.io
 * ========================================================== */
jQuery(document).ready(function($){
	// Initialize Select2.
	omapiSelect();

	// Hide/show any state specific settings.
	omapiToggleSettings();

	// Set the tab height.
	omapiTabHeight();

    // Support Toggles on content
    omapiSettingsToggle();

	// Confirm resetting settings.
	omapiResetSettings();

	/**
     * Sets the tab panel and content height to equal heights, whichever is greater.
     *
     * @since 1.0.0
     */
    function omapiTabHeight(){
	    var tabs 		   = $('.omapi-tabs'),
	    	tabs_height    = parseInt(tabs.css('height'));
	    	content 	   = $('.omapi-tabs-content'),
	    	content_height = parseInt(content.css('height'));

		// If height is the same, do nothing.
		if ( tabs_height == content_height ) {
			return;
		}

		if ( content_height > tabs_height ) {
			tabs.height(content_height - 1);
		} else if ( content_height > 197 ) {
			tabs.height(content_height - 1);
		} else {
			content.height(tabs_height - 33);
			tabs.find('.omapi-panels li:last-child a').css('borderBottom', '0');
		}
    }

    function omapiSettingsToggle(){

        $('.omapi-ui-toggle-controller').click(function () {
            $(this).siblings(".omapi-ui-toggle-content").toggleClass("visible");
        });

    }

    /**
     * Confirms the settings reset for the active tab.
     *
     * @since 1.0.0
     */
    function omapiResetSettings(){
	    $(document).on('click', 'input[name=reset]', function(e){
		    return confirm(omapi.confirm);
		});
    }

    /**
     * Toggles the shortcode list setting.
     *
     * @since 1.1.4
     */
    function omapiToggleSettings(){
	    var shortcode_val = $('#omapi-field-shortcode').is(':checked');
	    if ( ! shortcode_val ) {
		    $('.omapi-field-box-shortcode_output').hide();
	    }
	    $(document).on('change', '#omapi-field-shortcode', function(e){
		    if ( $(this).is(':checked') ) {
			    $('.omapi-field-box-shortcode_output').show(0, omapiTabHeight);
		    } else {
			    $('.omapi-field-box-shortcode_output').hide(0, omapiTabHeight);
		    }
		});

		var mailpoet_val = $('#omapi-field-mailpoet').is(':checked');
	    if ( ! mailpoet_val ) {
		    $('.omapi-field-box-mailpoet_list').hide();
	    }
	    $(document).on('change', '#omapi-field-mailpoet', function(e){
		    if ( $(this).is(':checked') ) {
			    $('.omapi-field-box-mailpoet_list').show(0, omapiTabHeight);
		    } else {
			    $('.omapi-field-box-mailpoet_list').hide(0, omapiTabHeight);
		    }
		});
    }

    /**
     * Initializes the Select2 replacement for select fields.
     *
     * @since 1.0.0
     */
    function omapiSelect(){
	    $('.omapi-select-ajax').each(function(i, el){
		    var ajax_action = $(this).attr('id').indexOf('taxonomies') > -1 ? 'omapi_query_taxonomies' : 'omapi_query_posts',
		    	init_action = 'omapi_query_taxonomies' == ajax_action ? 'omapi_query_selected_taxonomies' : 'omapi_query_selected_posts';
		    $(this).select2({
			    minimumInputLength: 1,
			    multiple: true,
			    ajax: {
				    url: omapi.ajax,
			        dataType: 'json',
			        type: 'POST',
			        quietMillis: 250,
			        data: function (term, page) {
			            return {
				            action: ajax_action,
			                q: 	    term,
			                nonce:  omapi.nonce
			            };
			        },
			        results: function (data, page) {
			            return { results: data.items };
			        }
			    },
			    initSelection: function(el, cb){
				    var ids = $(el).val(),
				    	data = {
					    	action: init_action,
					    	ids:    ids,
					    	nonce:  omapi.nonce
						};
				    $.post(omapi.ajax, data, function(data){
					    cb(data.items);
					    omapiTabHeight();
					}, 'json');
			    },
			    formatResult: omapiFormatResult,
				formatSelection: omapiFormatSelection,
				escapeMarkup: function(m){return m;}
			}).on('change select2-removed', function(){
				omapiTabHeight();
			});
	    });
    }

    /**
     * Formats the queried post selection for Select2.
     *
     * @since 1.0.0
     */
    function omapiFormatResult(item){
	    var markup = '';
        if ( item.title !== undefined ) {
            markup += '<div value="' + item.id + '">' + item.title + '</div>';
        }
        return markup;
    }

    /**
     * Formats the queried post selection for Select2.
     *
     * @since 1.0.0
     */
    function omapiFormatSelection(item){
	    return item.title;
    }
});