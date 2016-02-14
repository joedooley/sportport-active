jQuery(document).ready(function($) {
	/**
	* Images Tab
	* Show / Hide "Add Tag(s) to Selected Images" button when image(s) selected
	*/
	// Select / deselect images
    $('a.envira-tags-multiple').fadeOut();
    $('ul#envira-gallery-output').on('click', 'li.envira-gallery-image > img', function() {
        // Show/hide 'Deleted Selected Images from Album' button depending on whether
        // any galleries have been selected
        if ($('ul#envira-gallery-output > li.selected').length > 0) {
            $('a.envira-tags-multiple').fadeIn();  
        } else {
            $('a.envira-tags-multiple').fadeOut();  
        }
    });
    $('a.envira-tags-multiple').on('click', function(e) {
    	e.preventDefault();

    	// Bail out if the user doesn't define any tags in the prompt
        var tags = prompt( envira_tags.multiple );
    	if ( tags == '' ) {
    		return false;
    	}

        // Build array of image attachment IDs
        var attach_ids = [];
        $('ul#envira-gallery-output > li.selected').each(function() {
            attach_ids.push($(this).attr('id'));
        });

        // Process the Ajax response and output all the necessary data.
        $.post(
            envira_gallery_metabox.ajax,
            {
            	action:        'envira_tags_tag_multiple_images',
            	attachment_ids:attach_ids,
            	tags: 		   tags,
            	post_id:       envira_gallery_metabox.id,
            	nonce:         envira_tags.nonce
            },
            function(response) {
                console.log(response);
            }//,
            //'json'
        );
    })

	/**
	* Settings
	*/
	if ($('tr#envira-config-tags-filtering-box').length > 0) {
        tagBox.init();
	}
	$('body').on('click', 'input#publish, input#save-post', function() {
		// Get tags div
		var container = $('tr#envira-config-tags-filtering-box');
		var tagsDiv = $('div.tagsdiv', $(container));
		var finalTagsTextArea = $('textarea.the-tags', $(container)); // tagBox.flushTags stores tags in this hidden textarea
		
		// Flush tags into hidden textarea
		tagBox.flushTags(tagsDiv, false, 1);

		// Map textarea to hidden input field
		$('input.envira-gallery-tags', $(container)).val($(finalTagsTextArea).val());
	});
	
	// Most Popular Tags
	$( 'tr#envira-config-tags-filtering-box .the-tagcloud a' ).on( 'click', function( e ) {

		var container 		= $( this ).closest( 'td' ),
			tag_input 		= $( 'input.newtag', $( container ) ),
			tag_input_val 	= $( tag_input ).val(),
			tag       		= $( this ).text(),
			tag_input_val_new = ( tag_input_val == '' ) ? tag : tag_input_val + ',' + tag; 

		// Add tag to tag input
		$( tag_input ).val( tag_input_val_new );

	} );

	/**
	* Modal
	*/
	// Initialise Post Tagging JS on modal load when the info icon on an image is clicked
	$('#envira-gallery').on('click.enviraModify', '.envira-gallery-modify-image', function(e){
        // Init the tagging JS
        tagBox.init();
	});

	// Most Popular Tags
	$( '.envira-gallery-media-frame .the-tagcloud a' ).on( 'click', function( e ) {

		var container 		= $( this ).closest( 'label.envira-tags' ),
			tag_input 		= $( 'input.newtag', $( container ) ),
			tag_input_val 	= $( tag_input ).val(),
			tag       		= $( this ).text(),
			tag_input_val_new = ( tag_input_val == '' ) ? tag : tag_input_val + ',' + tag; 

		// Add tag to tag input
		$( tag_input ).val( tag_input_val_new );

	} );
	
	// Save tags to hidden WP field on save
	$('body').on('click', '.envira-gallery-meta-submit', function() {
		// Get tags div for the modal window we have open
		var container = $(this).closest('.media-modal-content');
		var tagsDiv = $('div.tagsdiv', $(container));
		var finalTagsTextArea = $('textarea.the-tags', $(container)); // tagBox.flushTags stores tags in this hidden textarea
		
		// Flush tags into hidden textarea
		tagBox.flushTags(tagsDiv, false, 1);

		// Envira only sends fields with data-envira-meta set, so map the populated textarea
		// to our hidden field
		$('input.envira-gallery-tags').val($(finalTagsTextArea).val());
	});
});