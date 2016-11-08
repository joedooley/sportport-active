<?php
/*
Plugin Name: Facet WP Customizations
Description: Adds infinite scroll, refresh fades and more to theme.
Version: 1.0
Author: Joe Dooley
Author URI: https://www.developingdesigns.com/
*/

add_action( 'wp_head', 'fwpis_load_more', 99 );
/**
 * Add infinite scrolling to Facet WP
 */
function fwpis_load_more() {
	if ( is_shop() || is_product_taxonomy() ) {

		?>

		<script>
			(function ($) {
				window.fwp_is_paging = false;

				$(document).on('facetwp-refresh', function () {
					if (!window.fwp_is_paging) {
						window.fwp_page     = 1;
						FWP.extras.per_page = 'default';
					}

					window.fwp_is_paging = false;
				});

				$(document).on('facetwp-loaded', function () {
					window.fwp_total_rows = FWP.settings.pager.total_rows;

					if (!FWP.loaded) {
						window.fwp_default_per_page = FWP.settings.pager.per_page;

						$(window).scroll(function () {
							if ($(window).scrollTop() == $(document).height() - $(window).height()) {
								var rows_loaded = (window.fwp_page * window.fwp_default_per_page);
								if (rows_loaded < window.fwp_total_rows) {
									window.fwp_page++;
									window.fwp_is_paging = true;
									FWP.extras.per_page  = (window.fwp_page * window.fwp_default_per_page);
									FWP.soft_refresh     = true;
									FWP.refresh();
								}
							}
						});
					}
				});

				// Add Opacity whenever more products are fetched
				$(document).on('facetwp-refresh', function () {
					$('.facetwp-template').animate({opacity: 0}, 1000);
				});
				$(document).on('facetwp-loaded', function () {
					$('.facetwp-template').animate({opacity: 1}, 1000);
				});

			})(jQuery);

		</script>

		<?php
	}
}


/**
 * Set Facetwp cache expiration to one day
 *
 * @param $seconds
 *
 * @return int
 */
//add_filter( 'facetwp_cache_lifetime', function ( $seconds ) {
//	return 86400;
//} );
