/**
* Handles the meta icon helper.
*/
jQuery( document ).ready( function( $ ) {
	
	if ( 0 !== $('.envira-helper-needed').length ) {
        $('<div class="envira-meta-helper-overlay" />').prependTo('#envira-gallery');
    }
    $(document).on('click', '.envira-meta-icon', function(e){
        e.preventDefault();
        var $this     = $(this),
            container = $this.parent(),
            helper    = $this.next();
        if ( helper.is(':visible') ) {
            $('.envira-meta-helper-overlay').remove();
            container.removeClass('envira-helper-active');
        } else {
            if ( 0 === $('.envira-meta-helper-overlay').length ) {
                $('<div class="envira-meta-helper-overlay" />').prependTo('#envira-gallery');
            }
            container.addClass('envira-helper-active');
        }
    });

} );