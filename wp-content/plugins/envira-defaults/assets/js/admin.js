jQuery( document ).ready( function( $ ) {

	// Global vars we'll use
	var envira_defaults_url;

	/**
	* New Gallery: When the 'Add New' option for an Envira Gallery is clicked, display a modal
	* to give the user an option to copy the config from another Gallery or use the Envira
	* Defaults config.
	*/
	$( "a[href$='post-new.php?post_type=envira']" ).on( 'click', function( e ) {

		// Prevent default action
		e.preventDefault();

		// Get the link target, as we will use this to load the Add New screen later
		envira_defaults_url = $( this ).attr( 'href' );

		// Show modal dialog
		tb_show( envira_defaults.gallery_modal_title, envira_defaults.gallery_modal_url );

	} ); 

	/**
	* New Album: When the 'Add New' option for an Envira Album is clicked, display a modal
	* to give the user an option to copy the config from another Album or use the Envira
	* Defaults config.
	*/
	$( "a[href$='post-new.php?post_type=envira_album']" ).on( 'click', function( e ) {

		// Prevent default action
		e.preventDefault();

		// Get the link target, as we will use this to load the Add New screen later
		envira_defaults_url = $( this ).attr( 'href' );

		// Show modal dialog
		tb_show( envira_defaults.album_modal_title, envira_defaults.album_modal_url );

	} ); 

	/**
	* New Gallery/Album: When the modal form is submitted, grab the config ID and redirect to the gallery
	* screen with that configuration parameter
	*/
	$( 'body' ).on( 'submit', 'form#envira-defaults-config', function( e ) {

		// Prevent submit action
		e.preventDefault();

		// Amend the URL if a Gallery/Album was chosen
		if ( $( 'select', $( this ) ).val() != '' ) {
			envira_defaults_url += '&envira_defaults_config_id=' + $( 'select', $( this ) ).val();
		}

		// Redirect
		window.location = envira_defaults_url;

	} );

	/**
	* Bulk Actions: When the user chooses the "Apply Defaults" option from the Bulk Actions dropdown on the WP_List_Table
	* and they click Apply, display a modal to give the user an option to copy the config from another Gallery or use the
	* Envira Defaults config.
	*/
	$( 'body' ).on( 'click', 'input#doaction, input#doaction2', function( e ) {

		// Get action based on the input button clicked
		var input_button_id = $( this ).attr( 'id' );
		switch ( input_button_id ) {
			case 'doaction':
				// Check the action matches envira-defaults
				var action = $( 'select[name=action]' ).val();
				if ( action != 'envira-defaults' ) {
					return;
				}
				break;
			case 'doaction2':
				// Check the action matches envira-defaults
				var action = $( 'select[name=action2]' ).val();
				if ( action != 'envira-defaults' ) {
					return;
				}
				break;

		}

		// Prevent default action
		e.preventDefault();

		// Show modal dialog for Galleries or Albums
		if ( $( 'body.post-type-envira_album' ).length > 0 ) {
			// Album Screen
			tb_show( envira_defaults.album_modal_apply_title, envira_defaults.album_modal_apply_url );
		} else {
			// Gallery Screen
			tb_show( envira_defaults.gallery_modal_apply_title, envira_defaults.gallery_modal_apply_url );
		}

		return false;

	} );

	/**
	* Bulk Actions Gallery/Album: When the modal form is submitted, update the selected galleries/albums
	*/
	$( 'body' ).on( 'submit', 'form#envira-defaults-apply-config', function( e ) {

		// Prevent submit action
		e.preventDefault();

		// Get the Gallery/Album ID
		var id  		= $( 'select', $( this ) ).val(),
			post_ids 	= [],
			post_type 	= $( this ).attr( 'data-post-type' );

		// Get list of selected Galleries / Albums
		$( 'tbody#the-list input[type=checkbox]:checked' ).each( function( i ) {
			post_ids.push( $( this ).val() );
		} );

		// If no Galleries / Albums selected, bail
		if ( post_ids.length == 0 ) {
			return false;
		}

		// Clear any existing messages
		$( '#message' ).remove();

		// Perform an AJAX request to change the config of the selected Galleries / Albums
		$.ajax( {
			url: 		ajaxurl,
			type: 		'post',
			async: 		true,
			cache: 		false,
			data: {
				action: 	'envira_defaults_apply',
				nonce: 		envira_defaults.nonce,
				id: 		id,
				post_ids: 	post_ids,
				post_type:  post_type
			},
			success: function( response ) {
				// Close modal
				tb_remove();

				// Unselect the previously selected items
				$( 'tbody#the-list input[type=checkbox]' ).prop( 'checked', false );

				// Display a message to tell the user the action succeeded
				$( 'div.wrap > h1' ).after( '<div id="message" class="updated notice is-dismissible"><p>Settings applied successfully!</p></div>' );
				
				// Return
				return false;
			},
			error: function( xhr, textStatus, e ) {
				// Close modal
				tb_remove();

				// Display error
				$( 'div.wrap > h1' ).after( '<div id="message" class="error notice is-dismissible"><p>Error: ' + textStatus + '</p></div>' );

				// Return
				return false;
			}
		} );

		
		return false;
		

	} );

} );