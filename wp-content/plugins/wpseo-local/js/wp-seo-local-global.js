jQuery(document).ready(function($) {
	$('#use_multiple_locations').click( function() {
		if( $(this).is(':checked') ) {
			$('#show-single-location').slideUp( function() {
				$('#show-multiple-locations').slideDown();	
				$('#show-opening-hours').slideUp();
			});
		}
		else {
			$('#show-multiple-locations').slideUp( function() {
				$('#show-single-location').slideDown();
				$('#show-opening-hours').slideDown();
			});
		}
	});

	$('#hide_opening_hours').click( function() {
		if( $(this).is(':checked') ) {
			$('#hide-opening-hours').slideUp();
		}
		else {
			$('#hide-opening-hours').slideDown();
		}
	});
	$('#multiple_opening_hours, #wpseo_multiple_opening_hours').click( function() {
		if( $(this).is(':checked') ) {
			$('.opening-hours .opening-hour-second').slideDown();
		}
		else {
			$('.opening-hours .opening-hour-second').slideUp();
		}
	});

	if( $('#wpseo-checkbox-multiple-locations-wrapper').length > 0 ) {
		$('#wpseo-checkbox-multiple-locations-wrapper input[type=checkbox]').click( function() {
			var parent = $(this).parents('.widget-inside');

			if( $(this).is(':checked') ) {
				$('#wpseo-locations-wrapper', parent).slideUp();
			}
			else {
				$('#wpseo-locations-wrapper', parent).slideDown();
			}
		});
	}

	// Show locations metabox before WP SEO metabox
	if ( $('#wpseo_locations').length > 0 && $('#wpseo_meta').length > 0 ) {
		$('#wpseo_locations').insertBefore( $('#wpseo_meta') );
	}

    $('.openinghours_from').change( function() {
    	var to_id = $(this).attr('id').replace('_from','_to_wrapper');
    	var second_id = $(this).attr('id').replace('_from','_second');

        if ( $(this).val() == 'closed' ) {
            $( '#' + to_id ).css('display','none');
            $( '#' + second_id ).css('display','none');
        }
        else {
            $( '#' + to_id ).css('display','inline');
            $( '#' + second_id ).css('display','block');
        }
    }).change();
    $('.openinghours_from_second').change( function() {
    	var to_id = $(this).attr('id').replace('_from','_to_wrapper');

        if ( $(this).val() == 'closed' ) {
            $( '#' + to_id ).css('display','none');
        }
        else {
            $( '#' + to_id ).css('display','inline');
        }
    }).change();

    if ($('.set_custom_images').length > 0) {
        if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $('.wrap').on('click', '.set_custom_images', function(e) {
                e.preventDefault();
                var button = $(this);
                var id = button.prev();
                wp.media.editor.send.attachment = function(props, attachment) {
                	$('#custom_marker').attr( 'src', attachment.url );
                	$('#hidden_custom_marker').attr( 'value', attachment.id );
                };
                wp.media.editor.open(button);
                return false;
            });
        }
    };

    $('#remove_marker').on('click', function(){
    	$('#custom_marker').attr( 'src', '' );
    	$('#hidden_custom_marker').attr( 'value', '' );
    });

    // Copy location data
    $('#wpseo_copy_from_location').change( function() {
    	var location_id = $(this).val();
    	
    	if( location_id == '' ) 
    		return;

    	$.post( wpseo_local_data.ajaxurl, {
    		location_id: location_id,
    		security: wpseo_local_data.sec_nonce,
    		action: 'wpseo_copy_location'
    	}, function( result ) {
    		if(result.charAt(result.length - 1) == 0) {
				result = result.slice(0, -1);
			}
			else if(result.substring(result.length - 2) == "-1") {
				result = result.slice(0, -2);
			}

			var data = $.parseJSON( result );
			if( data.success == 'true' || data.success == true ) {

				for( var i in data.location ) {
					var value = data.location[ i ];

					if( value != null && value != '' && typeof value != 'undefined' ) {
						if( i == 'is_postal_address' || i == 'multiple_opening_hours' ) {
							if( value == '1' ) {
								$('#wpseo_' + i).attr('checked', 'checked');
								$('.opening-hours .opening-hour-second').slideDown();
							}
						}
						else if ( i.indexOf('opening_hours') > -1 ) {
							$('#' + i).val( value );
						}
						else {
							$('#wpseo_' + i).val( value );
						}
					}
				}
			}
    	});
    });
});
