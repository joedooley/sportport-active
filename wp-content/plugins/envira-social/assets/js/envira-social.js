jQuery(document).ready(function($) {

	$(document).on('click', '.envira-social-buttons a', function(e) {
		e.preventDefault();

		var url = $(this).attr('href'),
			width = $(this).parent().data('width'),
			height = $(this).parent().data('height'),
			network = $(this).parent().data('network');

		// if url = #, determine URL based on network and get nearest image
		if ( url == '#' ) {
			var image = $('img.envirabox-image').attr('src'),
				alt = $('img.envirabox-image').attr('alt'),
				title = $('img.envirabox-image').data('envira-title'),
				caption = $('img.envirabox-image').data('envira-caption'),
				gallery_id = $('img.envirabox-image').data('envira-gallery-id'),
				gallery_item_id = $('img.envirabox-image').data('envira-item-id');

			switch ( network ) {
				case 'facebook':
					url = 'https://www.facebook.com/dialog/feed?app_id=' + envira_social.facebook_app_id + '&display=popup&link=' + window.location.href.split('#')[0] + '&picture=' + image + '&name=' + title + '&caption=' + caption + '&description=' + alt + '&redirect_uri=' + window.location.href.split('#')[0] + '#envira_social_sharing_close';
                    break;

				case 'twitter':
					url = 'https://twitter.com/intent/tweet?text=' + caption + '&url=' + window.location.href.split('#')[0] + '?envira_social_gallery_id=' + gallery_id + '&envira_social_gallery_item_id=' + gallery_item_id;
					break;

				case 'google':
					url = 'https://plus.google.com/share?url=' + window.location.href.split('#')[0] + '?envira_social_gallery_id=' + gallery_id + '&envira_social_gallery_item_id=' + gallery_item_id;
					break;

				case 'pinterest':
					url = 'http://pinterest.com/pin/create/button/?url=' + window.location.href.split('#')[0] + '&media=' + image + '&description=' + caption;
					break;

				case 'email':
					url = 'mailto:?subject=' + caption + '&body=' + image;
					break;
			}
		}
		
		// Open Window
		var enviraSocialWin = window.open( url, 'Share', 'width=' + width + ',height=' + height );

		return false;
	});

	// Gallery: Show Sharing Buttons on Image Hover
	$( 'div.envira-gallery-item-inner' ).hover(function() {
		$( 'div.envira-social-buttons', $( this ) ).fadeIn();		
	}, function() {
		$( 'div.envira-social-buttons', $( this ) ).fadeOut();	
	});

	// If the envira_social_sharing_close=1 key/value parameter exists, close the window
	if ( location.href.search( 'envira_social_sharing_close' ) > -1 ) {
		window.close();
	} 

} );