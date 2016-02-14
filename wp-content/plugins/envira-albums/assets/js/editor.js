;(function($){
    $(function(){
    	/*
        // Close the modal window on user action.
        var envira_trigger_target  = envira_editor_frame = false;
        */
        var envira_append_and_hide = function(e){
            e.preventDefault();
            $('.envira-albums-default-ui .selected').removeClass('details selected');
            $('.envira-albums-default-ui').appendTo('.envira-albums-default-ui-wrapper').hide();
            envira_trigger_target = envira_editor_frame = false;
        };

		// Add Album Button
        $(document).on('click', '.envira-albums-choose-album, .envira-gallery-modal-trigger', function(e){
          	e.preventDefault();

            // Store the trigger target.
            envira_trigger_target = e.target;

            // Show the modal.
            envira_editor_frame = true;
            $('.envira-albums-default-ui').appendTo('body').show();

            $(document).on('click', '.media-modal-close, .media-modal-backdrop, .envira-gallery-cancel-insertion', envira_append_and_hide);
            $(document).on('keydown', function(e){
                if ( 27 == e.keyCode && envira_editor_frame ) {
                    envira_append_and_hide(e);
                }
            });
        });
        
        // Click Album in Modal
        $(document).on('click', '.envira-albums-default-ui .thumbnail, .envira-albums-default-ui .check, .envira-albums-default-ui .media-modal-icon', function(e){
            e.preventDefault();
            if ( $(this).parent().parent().hasClass('selected') ) {
                $(this).parent().parent().removeClass('details selected');
                $('.envira-albums-insert-gallery').attr('disabled', 'disabled');
            } else {
                $(this).parent().parent().parent().find('.selected').removeClass('details selected');
                $(this).parent().parent().addClass('details selected');
                $('.envira-albums-insert-gallery').removeAttr('disabled');
            }
        });

        $(document).on('click', '.envira-albums-default-ui .check', function(e){
            e.preventDefault();
            $(this).parent().parent().removeClass('details selected');
            $('.envira-albums-insert-gallery').attr('disabled', 'disabled');
        });

		// Insert Album Button
        $(document).on('click', '.envira-albums-default-ui .envira-albums-insert-gallery', function(e){
            e.preventDefault();
            
            // Get album ID
            var albumID = $('.envira-albums-default-ui .selected').data('envira-album-id');
            
            // Insert into editor
            wp.media.editor.insert('[envira-album id="' + albumID + '"]');

            // Hide the modal.
            envira_append_and_hide(e);
        });
    });
}(jQuery));