/* ==========================================================
 * metabox.js
 * http://enviragallery.com/
 * ==========================================================
 * Copyright 2014 Thomas Griffin.
 *
 * Licensed under the GPL License, Version 2.0 or later (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
var selectedObjs;
 
jQuery(document).ready(function($) {

	if ($('ul#envira-album-drag-drop-area').length > 0) {
	
		// DOM containers
		var selectedGalleries = $('ul#envira-album-drag-drop-area'),
			availableGalleries = $('ul#envira-album-output'),
			mainContainer = $('#envira-gallery-main'),
			modalContainer = $('.envira-gallery-meta-container');
		
		// Draggable config
		var draggableOpts = {
			helper: 'clone', // Required for connectToSortable to work: http://api.jqueryui.com/draggable/#option-connectToSortable
		    connectToSortable: '#envira-album-drag-drop-area',
		    revert: 'invalid',
	    };
	    
	    /**
	    * Conditional Fields
	    */
        $('input[data-envira-conditional], select[data-envira-conditional]').each(function() {
	    	enviraConditionalElement(this);
        });
        $('input[data-envira-conditional], select[data-envira-conditional]').change(function() {
	        enviraConditionalElement(this);
        });
	    function enviraConditionalElement(element) {
			// data-envira-conditional may have multiple element IDs specified
			var conditionalElements = $(element).data('envira-conditional').split(',');
			var displayElements = false;
			
		    // Determine whether to display relational elements or not
			switch ($(element).attr('type')) {
		    	case 'checkbox':
		    		displayElements = $(element).is(':checked');
		    		break;
		    	default:
		    		displayElements = (($(element).val() == '' || $(element).val() == 0) ? false : true);
		    		break;
	    	} 
	    	
	    	// Show/hide elements
	    	for (var i = 0; i < conditionalElements.length; i++) {
		    	if (displayElements) {
			    	$('#' + conditionalElements[i]).fadeIn(300);
			    } else {
				    $('#' + conditionalElements[i]).fadeOut(300);
			    }
	    	}
	    }
	    
		// Setup sortable
	    $(selectedGalleries).sortable({
	    	stop: function(event,ui) {
		    	// Hide "Drop galleries here" description
		    	$('p.drag-drop-info').hide();
		    	
		        // Delete original element as we cloned it
	        	var element = ui.item;
	        	galleryID = $(element).data('envira-gallery');
	        	
	        	// Remove selected class on element
	        	ui.item.removeClass('selected');
	        	
	        	// Timeout by .1s to prevent JS error and let sortable finish the stop operation
	        	setTimeout(function(){
	        		$("li[data-envira-gallery='"+galleryID+"']", $(availableGalleries)).remove();	
	        	}, 100);
	    	},
	    });
	    
	    // Manually bind update event to sortable, so it can be triggered manually
	    // on gallery deletion / manual insertion
	    $(selectedGalleries).on('sortupdate', function() {
	    	// Get ordered list of gallery IDs and store in a hidden field
			var galleries = [];
	        $.each($(selectedGalleries).children(), function(i, item) {
	        	galleries.push($(item).data('envira-gallery'));
	        });
	        $('input[name=galleryIDs]').val(galleries.join(","));
	        
	        // Save gallery order using AJAX
            $.ajax({
                url:      envira_albums_metabox.ajax,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action:  		'envira_albums_sort_galleries',
                    galleryIDs:   	$('input[name=galleryIDs]').val(),
                    post_id: 		envira_albums_metabox.id,
                    nonce:   		envira_albums_metabox.sort
                },
                success: function(response) {
                	return;
                },
                error: function(xhr, textStatus ,e) {
                	return;
                }
            });
	    });
	    
	    // Setup draggables + multiselect
	    $('li', $(availableGalleries)).draggable(draggableOpts);
	    
	    // Search for Galleries
	    var searchTimer = null;
	    $('input#envira-albums-gallery-search').keyup(function() {
		    // Set delayed search to begin
		    if (searchTimer) {
			    window.clearTimeout(searchTimer);
		    }
		    searchTimer = window.setTimeout(function() {
			    searchTimer = null;

			    // Check search terms are at least 3 characters long
			    var search_terms = $('input#envira-albums-gallery-search').val();
			    if ( search_terms.length >= 3 || search_terms.length == 0 ) {
				    // Filter galleries by search terms
		            $.ajax({
		                url:      envira_albums_metabox.ajax,
		                type:     'post',
		                async:    true,
		                cache:    false,
		                data: {
		                    action:  		'envira_albums_search_galleries',
		                    search_terms:   search_terms,
		                    post_id: 		envira_albums_metabox.id,
		                    nonce:   		envira_albums_metabox.search
		                },
		                success: function(response) {
		                	if (response.length > 0) {
		                		// Insert found Galleries
		                		$(availableGalleries).html(response);

		                		// Setup draggables + multiselect
		                		$('li', $(availableGalleries)).draggable(draggableOpts);
		                	}
		                	return;
		                },
		                error: function(xhr, textStatus ,e) {
		                	return;
		                }
		            });
	       		}
		    }, 500);
	    });
	    $('input#envira-albums-gallery-search').on('search', function() {
		    // Cross button clicked in search - show all
		    $.ajax({
                url:      envira_albums_metabox.ajax,
                type:     'post',
                async:    true,
                cache:    false,
                data: {
                    action:  		'envira_albums_search_galleries',
                    search_terms:   '',
                    post_id: 		envira_albums_metabox.id,
                    nonce:   		envira_albums_metabox.search
                },
                success: function(response) {
                	if (response.length > 0) {
                		// Insert found Galleries
                		$(availableGalleries).html(response);

                		// Setup draggables + multiselect
                		$('li', $(availableGalleries)).draggable(draggableOpts);
                	}
                	return;
                },
                error: function(xhr, textStatus ,e) {
                	return;
                }
            });

		    searchTimer = null;
	    });
	    
	    // Select galleries
	    $('ul#envira-album-output').on('click', 'li.gallery', function() {
		    if ($(this).hasClass('selected')) {
			    $(this).removeClass('selected');
		    } else {
			    $(this).addClass('selected');
		    }
		    
		    // Show/hide 'Add Selected Galleries to Album' button depending on whether
		    // any galleries have been selected
		    if ($('ul#envira-album-output > li.selected').length > 0) {
				$('a.envira-galleries-add').fadeIn();  
		    } else {
			    $('a.envira-galleries-add').fadeOut();  
		    }
	    });
	    
	    // Add multiple galleries to album
	    $('a.envira-galleries-add').click(function(e) {
		    e.preventDefault();
		    $('ul#envira-album-output > li.selected').each(function() {
				// Hide "Drop galleries here" description
		    	$('p.drag-drop-info').hide();
		    	
		    	// Remove selected class
		    	// Move element
		    	$(this).removeClass('selected').appendTo($(selectedGalleries));
		    	
		    	// Trigger sortable update to save new gallery IDs in this album
		    	$(selectedGalleries).trigger('sortupdate');

		    	// Hide button
		    	$('a.envira-galleries-add').fadeOut();
		    });
	    });
	    
	    // Process gallery removal from an album.
	    $(mainContainer).on('click', '.envira-album-remove-gallery', function(e){
	        e.preventDefault();
			
			// Bail out if the user does not actually want to remove the gallery.
	        var confirm_delete = confirm(envira_albums_metabox.remove);
	        if ( ! confirm_delete ) {
	            return;
	        }
	        
	        // Restore original element back to the available galleries section, and
	        // make it draggable
			$(this).parent().appendTo($(availableGalleries)).draggable(draggableOpts);
			
			// Trigger sortable update to save new gallery IDs in this album
			$(selectedGalleries).trigger('sortupdate');
	    });
	    
	    // Vars to store previous and next gallery IDs when the modal editor is open
		var galleryIDs = [];
	    
	    // Open up the media modal area for modifying album metadata.
	    $(mainContainer).on('click', '.envira-album-modify-gallery', function(e){
	    	e.preventDefault();
	    	var galleryID = $(this).parent().data('envira-gallery');
	    	
	    	// Get attachment IDs in order, so we can cycle through them using previous/next functionality
	        galleryIDs = [];
	        $('ul#envira-album-drag-drop-area li.gallery').each(function() {
	        	galleryIDs.push($(this).data('envira-gallery')); 
	        });
	           
	        // Open modal
	    	openModal(galleryID);
	    });
	    
	    // Open modal when left or right button clicked
	    $('body').on('click', 'button.left, button.right', function(e){
	    	e.preventDefault();
	    	
	    	// Close current modal
	    	closeModal();
	    	
	    	// Get gallery ID
	    	var galleryID = $(this).attr('data-gallery-id');
	    	
	        // Open new modal
	        openModal(galleryID);
	    });
	    
	    // Open Modal
	    var modal;
	    var openModal = function(galleryID) {
		    // Get modal and display it
		    modal = $('#envira-gallery-meta-'+galleryID).appendTo('body');
		    $(modal).show();
		    
		    // Enable left and right navigation by default in the modal
	        $('button.left', $(modal)).removeClass('disabled');
	        $('button.right', $(modal)).removeClass('disabled');
	        
	        // Get index of this attachment in array
	        // IE compatible
	        var galleryIDIndex = -1;
	        for (var i = 0; i < galleryIDs.length; i++) {
		        if (galleryIDs[i] == galleryID) {
			        galleryIDIndex = i;
			        break;
		        }
	        }
	        
	        if (galleryIDIndex == 0) {
		        // At the start of the attachment list
		        // Disable left button
		        $('button.left', $(modal)).addClass('disabled');
		        $('button.left', $(modal)).attr('data-gallery-id', '');
		        
		        // Enable right button
		        if (galleryIDs.length > 1) {
		        	$('button.right', $(modal)).removeClass('disabled');
					$('button.right', $(modal)).attr('data-gallery-id', galleryIDs[(galleryIDIndex+1)]);
		        } else {
			        $('button.right', $(modal)).addClass('disabled');
					$('button.right', $(modal)).attr('data-gallery-id', '');
		        }
	        } else if (galleryIDIndex == (galleryIDs.length - 1)) {
		        // At the start of the attachment list
		        // Enable left button
		        $('button.left', $(modal)).removeClass('disabled');
		        $('button.left', $(modal)).attr('data-gallery-id', galleryIDs[(galleryIDIndex-1)]);
		        
				// Disable right button
		        $('button.right', $(modal)).addClass('disabled');
		        $('button.right', $(modal)).attr('data-gallery-id', '');
	        } else {
		        // Enable left and right buttons
		        $('button.left', $(modal)).removeClass('disabled');
		        $('button.left', $(modal)).attr('data-gallery-id', galleryIDs[(galleryIDIndex-1)]);
		        $('button.right', $(modal)).removeClass('disabled');
		        $('button.right', $(modal)).attr('data-gallery-id', galleryIDs[(galleryIDIndex+1)]);
	        }
	        
	        // Close modal on close button or background click
	        $(document).on('click', '.media-modal-close, .media-modal-backdrop', function(e) {
	            e.preventDefault();
	            closeModal();
	        });
	        
	        // Close modal on esc keypress
	        $(document).on('keydown', function(e) {
	            if ( 27 == e.keyCode ) {
		        	closeModal();    
	            }
	        });
	    }
	    
	    // Close Modal
	    var closeModal = function() {
		    // Get modal
			var formfield = $(modal).attr('id');
			var formfieldArr = formfield.split('-');
			var galleryID = formfieldArr[(formfieldArr.length-1)];
				
	        // Close modal
	        $('#' + formfield).appendTo('li[data-envira-gallery="' + galleryID + '"]').hide();
	    }
	    
	    // Define the chosen cover image when a gallery image is clicked in the modal
	    $( 'body' ).on( 'click', '.attachment', function( e ) {
	    	// Get the cover image ID and URL.  One of these will have data, the other will be undefined,
	    	// depending on the gallery type.
	    	var cover_image_id = $( this ).data( 'cover-image-id' ),
	    		cover_image_url = $( this ).data( 'cover-image-url' );

	    		console.log(cover_image_id);
	    		console.log(cover_image_url);
	    	
	    	// Remove active classes on all attachments in this view
	    	$( '.attachment', $( this ).closest( 'div.attachment-details' ) ).removeClass( 'details' ).removeClass( 'selected' );
	    
			// Add active class to this attachment
			$( this ).addClass( 'details' ).addClass( 'selected' );

			// Update the hidden field, depending on which value is set
			if ( typeof cover_image_id !== 'undefined' ) {
				// Update ID
				$( 'input.envira-gallery-cover-image-id', $( this ).closest( 'div.attachment-details' ) ).val( cover_image_id );
			}
			if ( typeof cover_image_url !== 'undefined' ) {
				// Update URL
				$( 'input.envira-gallery-cover-image-url', $( this ).closest( 'div.attachment-details' ) ).val( cover_image_url );
			}
	    } );
	    
	    // Save changes when 'Save Metadata' button is pressed
	    $('body').on('click', '.envira-gallery-meta-submit', function(e) {
			e.preventDefault();
			
			// Get this modal container
			var galleryID = $(this).data('envira-gallery-item');
			var thisModalContainer = $(this).closest('#envira-gallery-meta-'+galleryID);
			var $this = $(this),
				default_t = $this.text(),
				spinner   = $('span.settings-save-status span.spinner', $(thisModalContainer)),
	            saved	  = $('span.settings-save-status span.saved', $(thisModalContainer));
			
			// Get field values
			var title = $('input.envira-gallery-title', $(thisModalContainer)).val();
			var caption = $('textarea[name="_eg_album_data[galleries][' + galleryID + '][caption]"]', $(thisModalContainer)).val();
			var alt = $('input.envira-gallery-alt', $(thisModalContainer)).val();
			var cover_image_id = $('input.envira-gallery-cover-image-id', $(thisModalContainer)).val();
			var cover_image_url = $('input.envira-gallery-cover-image-url', $(thisModalContainer)).val();
			
			// Change submit button = Saving
	        // Display saving spinner
	        $this.text(envira_albums_metabox.saving);
	        $this.attr('disabled','disabled');
	        $(spinner).show();
	            
			// Save gallery using AJAX
	        $.ajax({
	            url:      envira_albums_metabox.ajax,
	            type:     'post',
	            async:    true,
	            cache:    false,
	            dataType: 'json',
	            data: {
	                action:  		'envira_albums_update_gallery',
	                title:   		title,
	                caption: 		caption,
	                alt:			alt,
	                cover_image_id:	cover_image_id,
	                cover_image_url:cover_image_url,
	                post_id: 		envira_albums_metabox.id,
	                gallery_id:		galleryID,
	                nonce:   		envira_albums_metabox.sort
	            },
	            success: function(response) {
		            // Change cover image ID on sortable view
	            	var coverImageSrc = $('li.selected img', $(thisModalContainer)).attr('src');
	            	$("ul#envira-album-drag-drop-area li[data-envira-gallery='"+galleryID+"'] > img").attr('src', coverImageSrc);
	            	
	            	// Hide spinner, show saved text, revert button back to default state
	                $this.text(default_t);
		            $this.attr('disabled',false);
	                $(spinner).fadeOut('slow', function() {
		            	$(saved).fadeIn('fast', function() {
				            setTimeout(function(){
				                $(saved).fadeOut('slow');
		                    }, 500);	
		            	});    
	                });
					
	            },
	            error: function(xhr, textStatus, e) {
		            alert(xhr.responseText);
	            	return;
	            }
	        }); 
	    });
    
    } // Close screen check
});