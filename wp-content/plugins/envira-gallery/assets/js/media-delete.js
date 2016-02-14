jQuery( document ).ready( function( $ ) {
	// Delete multiple images from gallery
    $('a.envira-gallery-images-delete').click(function(e) {
        e.preventDefault();

        // Bail out if the user does not actually want to remove the image.
        var confirm_delete = confirm(envira_gallery_metabox.remove_multiple);
        if ( ! confirm_delete ) {
            return false;
        }

        // Build array of image attachment IDs
        var attach_ids = [];
        $('ul#envira-gallery-output > li.selected').each(function() {
            attach_ids.push($(this).attr('id'));
        });

        // Prepare our data to be sent via Ajax.
        var remove = {
            action:        'envira_gallery_remove_images',
            attachment_ids:attach_ids,
            post_id:       envira_gallery_metabox.id,
            nonce:         envira_gallery_metabox.remove_nonce
        };

        // Process the Ajax response and output all the necessary data.
        $.post(
            envira_gallery_metabox.ajax,
            remove,
            function(response) {
                // Remove each image
                $('ul#envira-gallery-output > li.selected').remove();

                // Hide Delete Button
                $('a.envira-gallery-images-delete').fadeOut();

                // Refresh the modal view to ensure no items are still checked if they have been removed.
                $('.envira-gallery-load-library').attr('data-envira-gallery-offset', 0).addClass('has-search').trigger('click');

                // Repopulate the Envira Gallery Image Collection
                EnviraGalleryImagesUpdate();
            },
            'json'
        );
    });

    // Process image removal from a gallery.
    $('#envira-gallery').on('click', '.envira-gallery-remove-image', function(e){
        e.preventDefault();

        // Bail out if the user does not actually want to remove the image.
        var confirm_delete = confirm(envira_gallery_metabox.remove);
        if ( ! confirm_delete )
            return;

        // Prepare our data to be sent via Ajax.
        var attach_id = $(this).parent().attr('id'),
            remove = {
                action:        'envira_gallery_remove_image',
                attachment_id: attach_id,
                post_id:       envira_gallery_metabox.id,
                nonce:         envira_gallery_metabox.remove_nonce
            };

        // Process the Ajax response and output all the necessary data.
        $.post(
            envira_gallery_metabox.ajax,
            remove,
            function(response) {
                $('#' + attach_id).fadeOut('normal', function() {
                    $(this).remove();

                    // Refresh the modal view to ensure no items are still checked if they have been removed.
                    $('.envira-gallery-load-library').attr('data-envira-gallery-offset', 0).addClass('has-search').trigger('click');

                    // Repopulate the Envira Gallery Image Collection
                    EnviraGalleryImagesUpdate();
                });
            },
            'json'
        );
    });
} );