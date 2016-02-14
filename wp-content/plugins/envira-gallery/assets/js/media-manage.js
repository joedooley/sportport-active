/**
* Handles selection, deselection and sorting of media in an Envira Gallery
*/
jQuery( document ).ready( function( $ ) {
	
	// Make gallery items sortable.
    var gallery = $('#envira-gallery-output');
    gallery.sortable({
        containment: '#envira-gallery-output',
        items: 'li',
        cursor: 'move',
        forcePlaceholderSize: true,
        placeholder: 'dropzone',
        update: function(event, ui) {
            // Make ajax request to sort out items.
            var opts = {
                url:      envira_gallery_metabox.ajax,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action:  'envira_gallery_sort_images',
                    order:   gallery.sortable('toArray').toString(),
                    post_id: envira_gallery_metabox.id,
                    nonce:   envira_gallery_metabox.sort
                },
                success: function(response) {
                    // Repopulate the Envira Gallery Image Collection
                    EnviraGalleryImagesUpdate();
                    
                    return;
                },
                error: function(xhr, textStatus ,e) {
                    return;
                }
            };
            $.ajax(opts);
        }
    });

    // Select / deselect images
    $('a.envira-gallery-images-delete').fadeOut();
    $('ul#envira-gallery-output').on('click', 'li.envira-gallery-image > img', function() {
        var gallery_item = $(this).parent();

        if ($(gallery_item).hasClass('selected')) {
            $(gallery_item).removeClass('selected');
        } else {
            $(gallery_item).addClass('selected');
        }
        
        // Show/hide 'Deleted Selected Images from Album' button depending on whether
        // any galleries have been selected
        if ($('ul#envira-gallery-output > li.selected').length > 0) {
            $('a.envira-gallery-images-delete').fadeIn();  
        } else {
            $('a.envira-gallery-images-delete').fadeOut();  
        }
    });

} );