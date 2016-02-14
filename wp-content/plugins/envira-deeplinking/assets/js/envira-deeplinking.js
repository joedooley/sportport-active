/**
* Fired when a hash is detected in the URL bar
* If the Lightbox is open, jumps to the chosen image
*/
function envira_deeplinking() {

    // Get hash
    var hash = window.location.hash.substr( 1 );
    
    // Check if a hash exists, and it's an Envira Gallery Hash
    if ( ! hash ) {
        return;
    }
    if ( hash.length == 0 ) {
        return;
    }
    if ( hash.indexOf( 'enviragallery' ) == -1 ) {
        return;
    }

    

    // If here, hash is valid.
    // Split it into a gallery ID and image ID
    var result      = hash.split( '-' ),
        gallery     = result[0].split( '!' )[1],
        gallery_id  = gallery.split( 'enviragallery' )[1],
        image_id    = result[1];

    // Check if we have a Fancybox instance of the Gallery
    if ( typeof envira_galleries[ gallery_id ] === 'undefined' ) {
        return;
    }

    // Get the index of the image_id
    // We query Fancybox because some images in the DOM may not be linked to images, and this results in an inaccurate index
    var image_found = false;
    for ( var index = 0; index < envira_galleries[ gallery_id ].length; index++ ) {
        // Get the image
        var linked_image    = jQuery( envira_galleries[ gallery_id ][ index ] ),
            linked_image_id = jQuery( 'img', linked_image ).data( 'envira-item-id' );

        // If the linked image id matches the image id specified in the URL, we have a match
        if ( linked_image_id == image_id ) {
            image_found = true;
            break;
        }
    }

    // If we didn't find the image, our image_id is actually an index
    // Let's make that zero based
    if ( ! image_found ) {
        index = image_id - 1;
    }

    // We now have a zero based index to tell the Lightbox which image to display in the index
    if ( jQuery.envirabox && jQuery.envirabox.isActive && ( jQuery.envirabox.current.element.attr( 'data-envirabox-group' ) === gallery || jQuery.envirabox.current.element.attr( 'rel' ) === gallery ) ) {
        // Lightbox is open on our current gallery, so just jump to the image we want
        jQuery.envirabox.jumpto( index );
    } else {
        // Trigger a click on the image link in the gallery view, to load the Lightbox
        // We do this on rel and data-envirabox-group as only one will be present, 
        // depending on whether HTML5 is enabled or not in the Gallery settings
        jQuery( 'a[rel=' + gallery + ']' ).eq( index ).trigger( 'click' );
        jQuery( 'a[data-envirabox-group=' + gallery + ']' ).eq( index ).trigger( 'click' );
    }

}

/**
* Bind the envira_deeplinking function when the window hash changes
*/
jQuery( window ).bind( 'hashchange', function() { 

    envira_deeplinking();
} );