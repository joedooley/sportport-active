jQuery( document ).ready( function( $ ) {
	'use strict';

	var wcavi_original_gallery_images = $( wc_additional_variation_images_local.gallery_images_class ).html();
	var wcavi_original_main_images = $( wc_additional_variation_images_local.main_images_class ).html();

	// create namespace to avoid any possible conflicts
	$.wc_additional_variation_images_frontend = {
		isCloudZoom: function() {
			var cloudZoomClass = $( 'a.woocommerce-main-image' ).hasClass( 'cloud-zoom' );

			return cloudZoomClass;
		},

		runLightBox: function( callback ) {
			// user trigger
			$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_lightbox', wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images );

			// if cloud zoom is active
			if ( $.wc_additional_variation_images_frontend.isCloudZoom() ) {

				$( '.cloud-zoom' ).each( function() {
					$( this ).data( 'zoom' ).destroy();
				});

				$( '.cloud-zoom, .cloud-zoom-gallery' ).CloudZoom();
			} else {

				if ( $.isFunction( $.fn.prettyPhoto ) ) {
					// lightbox
					$( wc_additional_variation_images_local.lightbox_images ).prettyPhoto({
						hook: 'data-rel',
						social_tools: false,
						theme: 'pp_woocommerce',
						horizontal_padding: 20,
						opacity: 0.8,
						deeplinking: false
					});
				}
			}

			$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_lightbox_done', [ wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

			if ( callback ) {
				callback();
			}
		},

		reset: function( callback ) {

			if ( wc_additional_variation_images_local.custom_reset_swap == true ) {
				var response = '';

				$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_on_reset', [ response, wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

			} else {
				// replace the original gallery images
				$( wc_additional_variation_images_local.gallery_images_class ).fadeOut( 50, function() {
					$( this ).html( wcavi_original_gallery_images ).hide().fadeIn( 100, function() {
						$.wc_additional_variation_images_frontend.runLightBox();
					});
				});
			}

			$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_on_reset_done', [ wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

			if ( callback ) {
				callback();
			}
		},

		imageSwap: function( response, callback ) {

			if ( wc_additional_variation_images_local.custom_swap == true ) {
				$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_image_swap_callback', [ response, wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

			} else {

				$( wc_additional_variation_images_local.gallery_images_class ).fadeOut( 50, function() {
					$( this ).html( response.gallery_images ).hide().fadeIn( 100, function() {
						$.wc_additional_variation_images_frontend.runLightBox();
					});
				});
			}

			$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_image_swap_done_callback', [ wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

			if ( callback ) {
				callback();
			}
		},

		imageSwapOriginal: function( callback ) {

			if ( wc_additional_variation_images_local.custom_original_swap == true ) {
				var response = '';

				$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_ajax_default_image_swap_callback', [ response, wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

			} else {
				$( wc_additional_variation_images_local.gallery_images_class ).fadeOut( 50, function() {
					$( this ).html( wcavi_original_gallery_images ).hide().fadeIn( 100, function() {
						$.wc_additional_variation_images_frontend.runLightBox();
					});
				});
			}

			$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_ajax_default_image_swap_done_callback', [ wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

			if ( callback ) {
				callback();
			}
		},

		hideGallery: function() {
			$( wc_additional_variation_images_local.gallery_images_class ).hide().css( 'visibility', 'hidden' );
		},

		showGallery: function() {
			$( wc_additional_variation_images_local.gallery_images_class ).css( 'visibility', 'visible' ).fadeIn( 'fast' );
		},

		init: function() {

			// when variation changes trigger
			$( 'form.variations_form' ).on( 'show_variation', function( event, variation ) {
				$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_before_show_variation' );

				var $data = {
						action: 'wc_additional_variation_images_load_frontend_images_ajax',
						ajaxImageSwapNonce: wc_additional_variation_images_local.ajaxImageSwapNonce,
						variation_id: variation.variation_id,
						post_id: $( 'form.variations_form' ).data( 'product_id' )
					};

				$( wc_additional_variation_images_local.gallery_images_class ).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				$.post( wc_additional_variation_images_local.ajaxurl, $data, function( response ) {
					if ( response.length ) {
						response = $.parseJSON( response );

						$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_ajax_response_callback', [ response, wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

						// replace with new image set
						$.wc_additional_variation_images_frontend.imageSwap( response );

					} else {

						$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_ajax_response_callback', [ response, wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );

						// replace with original image set
						$.wc_additional_variation_images_frontend.imageSwapOriginal();
					}

					$( wc_additional_variation_images_local.gallery_images_class ).unblock();
				});	
			});

			// on reset click
			$( 'form.variations_form' ).on( 'click', '.reset_variations', function() {
				$.wc_additional_variation_images_frontend.reset();
			});

			// on reset select trigger
			$( 'form.variations_form' ).on( 'reset_image', function() {
				$.wc_additional_variation_images_frontend.reset();
			});

			// add support for swatches and photos plugin
			$( '#variations_clear' ).on( 'click', function() {
				$.wc_additional_variation_images_frontend.reset();
			});

			$( '.swatch-anchor' ).on( 'click', function() {
				var option = $( this ).parent( '.select-option' );

				if ( option.hasClass( 'selected' ) ) {
					$.wc_additional_variation_images_frontend.reset();
				}
			});

			$( 'form.variations_form' ).trigger( 'wc_additional_variation_images_frontend_init', [ wc_additional_variation_images_local.gallery_images_class, wc_additional_variation_images_local.main_images_class, wcavi_original_gallery_images, wcavi_original_main_images ] );
		}
	}; // close namespace

	$.wc_additional_variation_images_frontend.init();

// end document ready
});	