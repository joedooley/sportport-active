<?php
/**
 * Alternitive facet loader override
 * This is only loaded if the alt loader option is enabled.
 *
 * @package   facet_stack/front
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */


/**
 * Enqueue the Alternative Loader
 *
 * @since 1.0.0
 * @uses "add_meta_boxes" hook
 *
 * @param bool $has_assets true if FacetWP assets are to be loaded
 *
 * @return bool $has_assets Return original check
 */
function facet_stack_enqueue_alt_loader( $has_assets ) {
	if( true === $has_assets ){
		add_action( 'wp_footer', 'facet_stack_alt_loader', 100);		
	}
	return $has_assets;
}
// add filter to check if FacetWP assets are being loaded. If true, add action to load overrides in footer.
add_filter( 'facetwp_load_assets', 'facet_stack_enqueue_alt_loader', 50);


/**
 * Output the FWP alternate loader scripts
 *
 * @since 1.0.0
 * @uses "wp_footer" hook
 *
 */
function facet_stack_alt_loader(){
	?>
	<script>
	if( FWP ){ 
		var facets = {},
			wrap_resizer,
			is_loading = false;

		wrap_resizer = function( facet ){
			if( ! facets[ facet ] || ! facets[ facet ].height ){ return; }
			facets[ facet ].element.css({
				height: ''
			});
			var new_height = facets[ facet ].element.height();
			
			facets[ facet ].element.height( facets[ facet ].height );
			facets[ facet ].element.animate( {
				opacity: 1,
				height: new_height
			}, 300 );			
		}

		jQuery( '.facetwp-facet' ).css({position: "relative", "min-height" : 40 });
		FWP.loading_handler = function( args ){
			is_loading = true;
			jQuery('.facetwp-template').stop().animate({ opacity: 0.3}, 200);
			var height = args.element.height(),
				loader = jQuery( '<div style="width: 100%; margin: 0px; position: absolute; top: 0px; bottom: 0px; z-index: 999; text-align: center;"><div class="facetwp-loading" style="margin-left:auto;margin-right:auto;"></div></div>' ),
				offset = args.element.scrollTop();
			loader.css({
				"padding-top": ( height / 2 ) - 14,
				"padding-bottom": ( height / 2 ) - 14,
				"top" : offset
			});
			
			args.element.children().css({
				"opacity": '0.4'
			});
			args.element.append( loader );
			facets[ args.facet_name ] = {
				element : args.element,
				height : height
			};
			var template = loader.clone();
		}
		jQuery(document).on('click', '.facetwp-toggle,.facetwp-expand', function() {
			var wrapper 	= jQuery( this ).closest( '.facetwp-facet' ),
				facet_name 	= wrapper.data('name');
			if( facet_name && facets[ facet_name ] ){
				facets[ facet_name ].height = wrapper.height();
				wrap_resizer( facet_name );
			}
		});
		jQuery( document ).on('facetwp-loaded', function( ev ){
			if( true === is_loading ){
				for( var e in facets ){
					wrap_resizer( e );
				}
			}
			jQuery('.facetwp-template').stop().animate({ opacity: 1}, 200);
		})
	}
	</script>
	<?php
}