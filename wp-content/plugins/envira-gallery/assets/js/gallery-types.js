/**
* Handles changing Gallery Types, for example from Default to Instagram
*/
jQuery( document ).ready( function( $ ) {

    $( document ).on('change', 'input[name="_envira_gallery[type]"]:radio', function(e){
        
        var $this = $(this);
        $('.envira-gallery-type-spinner .envira-gallery-spinner').css({'display' : 'inline-block', 'margin-top' : '-1px'});

        // Prepare our data to be sent via Ajax.
        var change = {
            action:  'envira_gallery_change_type',
            post_id: envira_gallery_metabox.id,
            type:    $this.val(),
            nonce:   envira_gallery_metabox.change_nonce
        };

        // Process the Ajax response and output all the necessary data.
        $.post(
            envira_gallery_metabox.ajax,
            change,
            function(response) {
                console.log(response);

                // Append the response data.
                if ( 'default' == response.type ) {
                    $('#envira-gallery-main').html(response.html);
                    // enviraPlupload();
                } else {
                    $('#envira-gallery-main').html(response.html);
                }

                // Fire an event to attach to.
                $(document).trigger('enviraGalleryType', response);

                // Remove the spinner.
                $('.envira-gallery-type-spinner .envira-gallery-spinner').hide();
            },
            'json'
        );
    });  

} );    