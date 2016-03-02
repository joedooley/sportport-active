<?php

/**
 * WPSEO_Local_Core class.
 *
 * @package Yoast SEO Local
 * @since   1.0
 */
if ( !class_exists( 'WPSEO_Local_Core' ) ) {
	class WPSEO_Local_Core {

		var $options = array();
		var $days = array();

		/**
		* @var Yoast_Plugin_License_Manager Holds an instance of the license manager class
		*/
		protected $license_manager = null;

		/**
		 * Constructor for the WPSEO_Local_Core class.
		 *
		 * @since 1.0
		 */
		function __construct() {

			$this->options = get_option( "wpseo_local" );
			$this->days    = array(
				'monday'    => __( 'Monday', 'yoast-local-seo' ),
				'tuesday'   => __( 'Tuesday', 'yoast-local-seo' ),
				'wednesday' => __( 'Wednesday', 'yoast-local-seo' ),
				'thursday'  => __( 'Thursday', 'yoast-local-seo' ),
				'friday'    => __( 'Friday', 'yoast-local-seo' ),
				'saturday'  => __( 'Saturday', 'yoast-local-seo' ),
				'sunday'    => __( 'Sunday', 'yoast-local-seo' ),
			);

			if ( wpseo_has_multiple_locations() ) {
				add_action( 'init', array( $this, 'create_custom_post_type' ), 10, 1 );
				add_action( 'init', array( $this, 'create_taxonomies' ), 10, 1 );
				add_action( 'init', array( $this, 'exclude_taxonomy' ), 10, 1 );
			}

			if ( is_admin() ) {

				$this->license_manager = $this->get_license_manager();

				$this->license_manager->setup_hooks();

				add_action( 'wpseo_licenses_forms', array( $this->license_manager, 'show_license_form' ) );
				add_action( 'update_option_wpseo_local', array( $this, 'save_permalinks_on_option_save' ), 10, 2 );

				// Setting action for removing the transient on update options
				if ( method_exists( 'WPSEO_Utils', 'register_cache_clear_option' ) ) {
					WPSEO_Utils::register_cache_clear_option( 'wpseo_local', 'kml' );
				}

			} else {
				// XML Sitemap Index addition
				add_action( 'template_redirect', array( $this, 'redirect_old_sitemap' ) );
				add_action( 'init', array( $this, 'init' ), 11 );
				add_filter( 'wpseo_sitemap_index', array( $this, 'add_to_index' ) );
			}

			// Add support for Jetpack's Omnisearch
			add_action( 'init', array( $this, 'support_jetpack_omnisearch' ) );
			add_action( 'save_post', array( $this, 'invalidate_sitemap' ) );

			// Run update if needed
			add_action( 'plugins_loaded', array( &$this, 'do_upgrade' ), 14 );

			// Extend the search with metafields
			add_action( 'pre_get_posts', array( &$this, 'enhance_search' ) );
			add_filter( 'the_excerpt', array( &$this, 'enhance_location_search_results' ) );
		}

		function enhance_search( $query ) {
			if( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
				$meta_query = array(
					'relation' => 'OR'
				);

				$meta_query[] = array(
					'key' => '_wpseo_business_address',
					'value' => get_search_query(),
					'compare' => 'LIKE',
				);
				$meta_query[] = array(
					'key' => '_wpseo_business_city',
					'value' => get_search_query(),
					'compare' => 'LIKE',
				);
				$meta_query[] = array(
					'key' => '_wpseo_business_zipcode',
					'value' => get_search_query(),
					'compare' => 'LIKE',
				);

				$query->set( 'meta_query', $meta_query );
				add_filter( 'posts_where', array( $this, 'search_where' ) );
			}

			return $query;
		}

		function search_where( $where ) {
			global $wpdb;

			// First change all white space characters to single spaces
			$where = preg_replace( '/\s+/', ' ', $where );

			// Then replace the right AND by OR
			$where = str_replace( "AND ( ( {$wpdb->postmeta}.meta_key = '_wpseo_business_address'", "OR ( ( {$wpdb->postmeta}.meta_key = '_wpseo_business_address'", $where );

			return $where;
		}

		function enhance_location_search_results( $excerpt ) {
			if( is_search() ) {
				global $post;

				if( 'wpseo_locations' === get_post_type( $post->ID ) ) {
					$excerpt .= '<div class="wpseo-local-search-details">';
					$excerpt .= wpseo_local_show_address( array( 'id' => $post->ID, 'hide_name' => true ) );
					$excerpt .= '</div>';
				}
			}

			return $excerpt;
		}

		function do_upgrade() {
			$options = get_option( 'wpseo_local' );

			if ( ! isset( $options['version'] ) ) {
				$options['version'] = '0';
			}

			if ( version_compare( $options['version'], WPSEO_LOCAL_VERSION, '<' ) ) {

				// upgrade to new licensing class
				$license_manager = $this->get_license_manager();

				if( $license_manager->license_is_valid() === false ) {

					if( isset( $options['license'] ) ) {
						$license_manager->set_license_key( $options['license'] );
					}

					if( isset( $options['license-status'] ) ) {
						$license_manager->set_license_status( $options['license-status'] );
					}

				}

				// other upgrades
				wpseo_local_do_upgrade( $options['version'] );
			}
		}

		/**
		* Returns an instance of the Yoast_Plugin_License_Manager class
		* Takes care of remotely (de)activating licenses and plugin updates.
		*/
		protected function get_license_manager() {

			// We need WP SEO 1.5+ or higher but WP SEO Local doesn't have a version check.
			if( ! $this->license_manager ) {

				require_once dirname( __FILE__ ) . '/class-product.php';

				$this->license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Local() );
				$this->license_manager->set_license_constant_name( 'WPSEO_LOCAL_LICENSE' );
			}

			return $this->license_manager;
		}

		/**
		 * Adds the rewrite for the Geo sitemap and KML file
		 *
		 * @since 1.0
		 */
		public function init() {

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				add_action( 'wpseo_do_sitemap_geo', array( $this, 'build_local_sitemap' ) );
				add_action( 'wpseo_do_sitemap_locations', array( $this, 'build_kml' ) );

				add_rewrite_rule( 'geo-sitemap\.xml$', 'index.php?sitemap=geo_', 'top' );
				add_rewrite_rule( 'locations\.kml$', 'index.php?sitemap=locations', 'top' );


				if ( preg_match( '/(geo-sitemap.xml|locations.kml)(.*?)$/', $_SERVER['REQUEST_URI'], $match ) ) {
					if ( in_array( $match[1], array( 'geo-sitemap.xml', 'locations.kml' ) ) ) {
						$sitemap = 'geo';
						if( $match[1] == 'locations.kml' ) {
							$sitemap = 'locations';
						}

						$GLOBALS['wpseo_sitemaps']->build_sitemap( $sitemap );
					} else {
						return;
					}

					// 404 for invalid or emtpy sitemaps
					if ( $GLOBALS['wpseo_sitemaps']->bad_sitemap ) {
						$GLOBALS['wp_query']->is_404 = true;
						return;
					}

					$GLOBALS['wpseo_sitemaps']->output();
					$GLOBALS['wpseo_sitemaps']->sitemap_close();
				}
			}
		}

		/**
		 * Method to invalidate the sitemap
		 *
		 * @param integer $post_id
		 */
		public function invalidate_sitemap( $post_id ) {
			// If this is just a revision, don't invalidate the sitemap cache yet.
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( get_post_type( $post_id ) === 'wpseo_locations' ) {
				wpseo_invalidate_sitemap_cache( 'kml' );
			}
		}

		/**
		 * Adds support for Jetpack's Omnisearch
		 */
		function support_jetpack_omnisearch() {
			if ( class_exists( 'Jetpack_Omnisearch_Posts' ) ) {
				new Jetpack_Omnisearch_Posts( 'wpseo_locations' );
			}
		}


		/**
		 * Redirects old geo_sitemap.xml to geo-sitemap.xml to be more in line with other XML sitemaps of Yoast SEO plugin.
		 *
		 * @since 1.2.2.1
		 *
		 */
		public function redirect_old_sitemap() {
			if ( preg_match( '/(geo_sitemap.xml)(.*?)$/', $_SERVER['REQUEST_URI'], $match ) ) {

				if( $match[1] == 'geo_sitemap.xml' ) {
					wp_redirect( trailingslashit( wpseo_xml_sitemaps_base_url( '' ) ) . 'geo-sitemap.xml', 301 );
					exit;
				}
			}
		}

		public function exclude_taxonomy_for_sitemap( $exclude, $taxonomy ) {
			if( $taxonomy == 'wpseo_locations_category' ) {
				$exclude = true;
			}

			return $exclude;
		}

		/**
		 * Adds the Geo Sitemap to the Index Sitemap.
		 *
		 * @since 1.0
		 *
		 * @param $str string String with the filtered additions to the index sitemap in it.
		 * @return string $str string String with the local XML sitemap additions to the index sitemap in it.
		 */
		public function add_to_index( $str ) {

			$date = get_option( 'wpseo_local_xml_update' );
			if ( !$date || $date == '' ) {
				$date = date( 'c' );
			}

			$str .= '<sitemap>' . "\n";
			$str .= '<loc>' . wpseo_xml_sitemaps_base_url( 'geo-sitemap.xml' ) . '</loc>' . "\n";
			$str .= '<lastmod>' . $date . '</lastmod>' . "\n";
			$str .= '</sitemap>' . "\n";
			return $str;
		}

		/**
		 * Pings Google with the (presumeably updated) Geo Sitemap.
		 *
		 * @since 1.0
		 */
		private function ping() {

			// Ping Google. Just do it.
			wp_remote_get( 'http://www.google.com/webmasters/tools/ping?sitemap=' . wpseo_xml_sitemaps_base_url( 'geo-sitemap.xml' ) );
		}

		/**
		 * Updates the last update time transient for the local sitemap and pings Google with the sitemap.
		 *
		 * @since 1.0
		 */
		public function update_sitemap() {
			// Empty sitemap cache
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );
			if( $caching ) {
				delete_transient( 'wpseo_sitemap_cache_kml' );
			}

			update_option( 'wpseo_local_xml_update', date( 'c' ) );

			// Ping sitemap
			$this->ping();
		}


		/**
		 * This function generates the Geo sitemap's contents.
		 *
		 * @since 1.0
		 */
		public function build_local_sitemap() {

			// Build entry for Geo Sitemap
			// Remark: no transient caching needed here, since the one home_url() request is faster than getting the transient cache.
			$output = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:geo="http://www.google.com/geo/schemas/sitemap/1.0">
				<url>
					<loc>' . wpseo_xml_sitemaps_base_url( 'locations.kml' ) . '</loc>
					<lastmod>' . date( 'c' ) . '</lastmod>
					<priority>1</priority>
				</url>
			</urlset>';

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
				$GLOBALS['wpseo_sitemaps']->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . dirname( plugin_dir_url( __FILE__ ) ) . '/styles/geo-sitemap.xsl"?>' );
			}
		}

		/**
		 * This function generates the KML file contents.
		 *
		 * @since 1.0
		 */
		public function build_kml() {

			$output = '';
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );

			if ( $caching ) {
				$output = get_transient( 'wpseo_sitemap_cache_kml' );
			}

			if ( ! $output || '' == $output ) {
				$location_data = $this->get_location_data();

				if ( isset( $location_data["businesses"] ) && is_array( $location_data["businesses"] ) && count( $location_data["businesses"] ) > 0 ) {
					$output = "<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
					$output .= "\t<Document>\n";
					$output .= "\t\t<name>" . ( !empty( $location_data['kml_name'] ) ? $location_data['kml_name'] : " Locations for " . $location_data['business_name'] ) . "</name>\n";

					if ( !empty( $location_data->author ) ) {
						$output .= "\t\t<atom:author>\n";
						$output .= "\t\t\t<atom:name>" . $location_data['author'] . "</atom:name>\n";
						$output .= "\t\t</atom:author>\n";
					}
					if ( !empty( $location_data_fields["business_website"] ) ) {
						$output .= "\t\t<atom:link href=\"" . $location_data['website'] . "\" />\n";
					}

					$output .= "\t\t<open>1</open>\n";
					$output .= "\t\t<Folder>\n";

					foreach ( $location_data['businesses'] as $key => $business ) {
						if ( !empty( $business ) ) {
							$business_name        = esc_attr( $business['business_name'] );
							$business_description = !empty( $business['business_description'] ) ? esc_attr( strip_shortcodes( $business['business_description'] ) ) : "";
							$business_description = htmlentities( $business_description );
							$business_url         = esc_url( $business['business_url'] );
							if ( wpseo_has_multiple_locations() && !empty( $business['post_id'] ) )
								$business_url = get_permalink( $business['post_id'] );
							if ( ! isset ( $business['full_address'] ) || empty ( $business['full_address'] ) ) {
								$business['full_address'] = wpseo_local_get_address_format( $business['business_address'], false, $business['business_zipcode'], $business['business_city'], $business['business_state'], true, false, false );
								if( ! empty( $business['business_country'] ) )
									$business['full_address'] .= ', ' . WPSEO_Local_Frontend::get_country( $business['business_country'] );
							}
							$business_fulladdress = $business['full_address'];

							$output .= "\t\t\t<Placemark>\n";
							$output .= "\t\t\t\t<name><![CDATA[" . $business_name . "]]></name>\n";
							$output .= "\t\t\t\t<address><![CDATA[" . $business_fulladdress . "]]></address>\n";
							$output .= "\t\t\t\t<description><![CDATA[" . $business_description . "]]></description>\n";
							$output .= "\t\t\t\t<atom:link href=\"" . $business_url . "\"/>\n";
							$output .= "\t\t\t\t<LookAt>\n";
							$output .= "\t\t\t\t\t<latitude>" . $business["coords"]["lat"] . "</latitude>\n";
							$output .= "\t\t\t\t\t<longitude>" . $business["coords"]["long"] . "</longitude>\n";
							$output .= "\t\t\t\t\t<altitude>1500</altitude>\n";
							$output .= "\t\t\t\t\t<range></range>\n";
							$output .= "\t\t\t\t\t<tilt>0</tilt>\n";
							$output .= "\t\t\t\t\t<heading></heading>\n";
							$output .= "\t\t\t\t\t<altitudeMode>relativeToGround</altitudeMode>\n";
							$output .= "\t\t\t\t</LookAt>\n";
							$output .= "\t\t\t\t<Point>\n";
							$output .= "\t\t\t\t\t<coordinates>" . $business["coords"]["long"] . "," . $business["coords"]["lat"] . ",0</coordinates>\n";
							$output .= "\t\t\t\t</Point>\n";
							$output .= "\t\t\t</Placemark>\n";
						}
					}

					$output .= "\t\t</Folder>\n";
					$output .= "\t</Document>\n";
					$output .= "</kml>\n";

					if ( $caching ) {
						set_transient( 'wpseo_sitemap_cache_kml', $output, DAY_IN_SECONDS );
					}
				}
			}

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
				$GLOBALS['wpseo_sitemaps']->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . dirname( plugin_dir_url( __FILE__ ) ) . '/styles/kml-file.xsl"?>' );
			}
		}

		/**
		 * Empties the
		 * @param $new_value
		 * @param $old_value
		 */
		function save_permalinks_on_option_save( $old_value, $new_value ) {

			// Don't do anything when location slug isn't changed
			if( $old_value['locations_slug'] == $new_value['locations_slug'] ) {
				return;
			}

			// Empty sitemap cache
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );
			if( $caching ) {
				delete_transient( 'wpseo_sitemap_cache_kml' );
			}
		}

		/**
		 * Builds an array based upon the data from the wpseo_locations post type. This data is needed as input for the Geo sitemap & KML API.
		 *
		 * @since 1.0
		 */
		function get_location_data( $post_id = null ) {
			$locations               = array();
			$locations["businesses"] = array();

			if ( wpseo_has_multiple_locations() ) {
				$args = array(
					'post_type'      => 'wpseo_locations',
					'posts_per_page' => -1,
					'fields'		 => 'ids'
				);
				if( null != $post_id ) {
					$args['posts_per_page'] = 1;
					$args['post__in'] = array( $post_id );
				}
				$posts = get_posts( $args );

				foreach ( $posts as $post_id ) {
					$business = array(
						"business_name"        => get_the_title( $post_id ),
						"business_type"        => get_post_meta( $post_id, '_wpseo_business_type', true ),
						"business_address"     => get_post_meta( $post_id, '_wpseo_business_address', true ),
						"business_city"        => get_post_meta( $post_id, '_wpseo_business_city', true ),
						"business_state"       => get_post_meta( $post_id, '_wpseo_business_state', true ),
						"business_zipcode"     => get_post_meta( $post_id, '_wpseo_business_zipcode', true ),
						"business_country"     => get_post_meta( $post_id, '_wpseo_business_country', true ),
						"business_phone"       => get_post_meta( $post_id, '_wpseo_business_phone', true ),
						"business_phone_2nd"   => get_post_meta( $post_id, '_wpseo_business_phone_2nd', true ),
						"business_fax"         => get_post_meta( $post_id, '_wpseo_business_fax', true ),
						"business_email"       => get_post_meta( $post_id, '_wpseo_business_email', true ),
						"business_url"	       => get_post_meta( $post_id, '_wpseo_business_url', true ),
						"business_description" => wpseo_local_get_excerpt( $post_id ),
						"coords"               => array(
							'lat'  => get_post_meta( $post_id, '_wpseo_coordinates_lat', true ),
							'long' => get_post_meta( $post_id, '_wpseo_coordinates_long', true )
						),
						"post_id"              => $post_id
					);

					$is_postal_address = get_post_meta( $post_id, '_wpseo_is_postal_address', true );
					$business['is_postal_address'] = $is_postal_address == '1';

					if( empty( $business['business_url'] ) ) {
						$business['business_url'] = get_permalink( $post_id );
					}

					array_push( $locations["businesses"], $business );
				}
			} else {
				$options = get_option( 'wpseo_local' );

				$business = array(
					"business_name"        => $options['location_name'],
					"business_type"        => $options['location_type'],
					"business_address"     => $options['location_address'],
					"business_city"        => $options['location_city'],
					"business_state"       => $options['location_state'],
					"business_zipcode"     => $options['location_zipcode'],
					"business_country"     => $options['location_country'],
					"business_phone"       => $options['location_phone'],
					"business_phone_2nd"   => $options['location_phone_2nd'],
					"business_fax"         => $options['location_fax'],
					"business_email"       => $options['location_email'],
					"business_description" => get_option( "blogname" ) . ' - ' . get_option( "blogdescription" ),
					"business_url"         => wpseo_xml_sitemaps_base_url( '' ),
					"coords"               => array(
						'lat'  => $options['location_coords_lat'],
						'long' => $options['location_coords_long'],
					)
				);

				array_push( $locations["businesses"], $business );
			}

			$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';

			$locations["business_name"] = get_option( "blogname" );
			$locations["kml_name"]      = "Locations for " . $locations["business_name"] . ".";
			$locations["kml_url"]       = home_url( $base . '/locations.kml' );
			$locations["kml_website"]   = wpseo_xml_sitemaps_base_url( '' );
			$locations["author"]        = get_option( "blogname" );

			return $locations;
		}

		/**
		 * Retrieves the lat/long coordinates from the Google Maps API
		 *
		 * @param Array $location_info Array with location info. Array structure: array( _wpseo_business_address, _wpseo_business_city, _wpseo_business_state, _wpseo_business_zipcode, _wpseo_business_country )
		 * @param bool  $force_update  Whether to force the update or not
		 * @param int $post_id
		 *
		 * @return bool|array Returns coordinates in array ( Format: array( 'lat', 'long' ) ). False when call the Maps API did not succeed
		 */
		public function get_geo_data( $location_info, $force_update = false, $post_id = 0 ) {
			$full_address =  wpseo_local_get_address_format( $location_info['_wpseo_business_address'], false, $location_info['_wpseo_business_zipcode'], $location_info['_wpseo_business_city'], $location_info['_wpseo_business_state'], true, false, false ) . ', ' . WPSEO_Local_Frontend::get_country( $location_info['_wpseo_business_country'] );

			$coordinates = array();

			if ( ( $post_id === 0 || empty( $post_id ) ) && isset( $location_info['_wpseo_post_id'] ) )
				$post_id = $location_info['_wpseo_post_id'];

			if ( $force_update || empty( $location_info['_wpseo_coords']['lat'] ) || empty( $location_info['_wpseo_coords']['long'] ) ) {

				$results = wpseo_geocode_address( $full_address );

				if ( is_wp_error( $results ) )
					return false;

				if ( isset( $results->results[0] ) && !empty( $results->results[0] ) ) {
					$coordinates['lat']  = $results->results[0]->geometry->location->lat;
					$coordinates['long'] = $results->results[0]->geometry->location->lng;

					if ( wpseo_has_multiple_locations() && $post_id !== 0 ) {

						update_post_meta( $post_id, '_wpseo_coordinates_lat', $coordinates['lat'] );
						update_post_meta( $post_id, '_wpseo_coordinates_long', $coordinates['long'] );
					} else {
						$options                         = get_option( 'wpseo_local' );
						$options['location_coords_lat']  = $coordinates['lat'];
						$options['location_coords_long'] = $coordinates['long'];

						update_option( 'wpseo_local', $options );
					}
				}
			} else {
				$coordinates['lat']  = $location_info['_wpseo_coords']['lat'];
				$coordinates['long'] = $location_info['_wpseo_coords']['long'];
			}

			$return_array['coords']       = $coordinates;
			$return_array["full_address"] = $full_address;

			return $return_array;
		}

		/**
		 * Check if the uploaded custom marker does not exceed 100x100px
		 *
		 * @param int    $imageid  The ID of the uploaded custom marker
		 */
		public function check_custom_marker_size( $imageid ) {
			if( empty( $imageid ) ) {
				return;
			}

			$image = wp_get_attachment_image_src( $imageid );

			if( ! is_array( $image ) ) {
				return;
			}

			if( $image[1] > 100 || $image[2] > 100 ) {
				echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'The uploaded custom marker exceeds the recommended size of 100x100 px. Please be aware this might influence the info popup.', 'yoast-local-seo' ) . '</p>';
			}
		}

		/**
		 * Creates the wpseo_locations Custom Post Type
		 */
		function create_custom_post_type() {
			/* Locations as Custom Post Type */
			$label_singular = !empty( $this->options['locations_label_singular'] ) ? $this->options['locations_label_singular'] : __( 'Location', 'yoast-local-seo' );
			$label_plural = !empty( $this->options['locations_label_plural'] ) ? $this->options['locations_label_plural'] : __( 'Locations', 'yoast-local-seo' );
			$labels = array(
				'name'               => $label_plural,
				'singular_name'      => $label_singular,
				'add_new'            => sprintf( __( 'New %s', 'yoast-local-seo' ), $label_singular ),
				'new_item'           => sprintf( __( 'New %s', 'yoast-local-seo' ), $label_singular ),
				'add_new_item'       => sprintf( __( 'Add New %s', 'yoast-local-seo' ), $label_singular ),
				'edit_item'          => sprintf( __( 'Edit %s', 'yoast-local-seo' ), $label_singular ),
				'view_item'          => sprintf( __( 'View %s', 'yoast-local-seo' ), $label_singular ),
				'search_items'       => sprintf( __( 'Search %s', 'yoast-local-seo' ), $label_plural ),
				'not_found'          => sprintf( __( 'No %s found', 'yoast-local-seo' ), $label_plural ),
				'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'yoast-local-seo' ), $label_plural ),
			);

			$slug = !empty( $this->options['locations_slug'] ) ? $this->options['locations_slug'] : 'locations';

			$args_cpt = array(
				'labels'               => $labels,
				'public'               => true,
				'show_ui'              => true,
				'capability_type'      => 'post',
				'hierarchical'         => false,
				'rewrite'              => array( 'slug' => $slug ),
				'has_archive'          => $slug,
				'query_var'            => true,
				'supports'             => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes', 'publicize', 'wpcom-markdown' )
			);
			$args_cpt = apply_filters( 'wpseo_local_cpt_args', $args_cpt );

			register_post_type( 'wpseo_locations', $args_cpt );
		}

		/**
		 * Create custom taxonomy for wpseo_locations Custom Post Type
		 */
		function create_taxonomies() {

			$labels = array(
				'name'              => __( 'Location categories', 'yoast-local-seo' ),
				'singular_name'     => __( 'Location category', 'yoast-local-seo' ),
				'search_items'      => __( 'Search Location categories', 'yoast-local-seo' ),
				'all_items'         => __( 'All Location categories', 'yoast-local-seo' ),
				'parent_item'       => __( 'Parent Location category', 'yoast-local-seo' ),
				'parent_item_colon' => __( 'Parent Location category:', 'yoast-local-seo' ),
				'edit_item'         => __( 'Edit Location category', 'yoast-local-seo' ),
				'update_item'       => __( 'Update Location category', 'yoast-local-seo' ),
				'add_new_item'      => __( 'Add New Location category', 'yoast-local-seo' ),
				'new_item_name'     => __( 'New Location category Name', 'yoast-local-seo' ),
				'menu_name'         => __( 'Location categories', 'yoast-local-seo' ),
			);

			$slug = !empty( $this->options['locations_taxo_slug'] ) ? $this->options['locations_taxo_slug'] : 'locations-category';

			$args = array(
				'hierarchical'          => true,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite' 				=> array( 'slug' => $slug )
			);
			$args = apply_filters( 'wpseo_local_custom_taxonomy_args', $args );

			register_taxonomy(
				'wpseo_locations_category',
				'wpseo_locations',
				$args
			);
		}

		/**
		 * Call filter to exclude taxonomies from sitemap
		 */
		function exclude_taxonomy() {
			add_filter( 'wpseo_sitemap_exclude_taxonomy', array( $this, 'exclude_taxonomy_for_sitemap' ), 10, 2 );
		}

		/**
		 * Inserts attachment in WordPress. Used by import panel
		 *
		 * @param int    $post_id  The post ID where the attachment belongs to
		 * @param string $image_url file url of the file which has to be uploaded
		 * @param bool   $setthumb If there's an image in the import file, then set is as a Featured Image
		 * @return int|WP_Error attachment ID. Returns WP_Error when upload goes wrong
		 */
		function insert_attachment( $post_id, $image_url, $setthumb = false ) {

			$file_array = array();
			$description = get_the_title( $post_id );
			$tmp = download_url( $image_url );

			// Set variables for storage
			// Fix file filename for query strings
			preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $image_url, $matches);
			$file_array['name'] = basename( $matches[0] );
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
			}

			// do the validation and storage stuff
			$attachment_id = media_handle_sideload( $file_array, $post_id, $description );

			// If error storing permanently, unlink
			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $file_array['tmp_name'] );
				return $attachment_id;
			}

			if ( $setthumb ) {
				update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
			}

			return $attachment_id;
		}

		/**
		 * Returns the valid local business types currently shown on Schema.org
		 *
		 * @link http://schema.org/docs/full.html In the bottom of this page is a list of Local Business types.
		 * @return array
		 */
		function get_local_business_types() {
			return array(
				"Organization" => "Organization",
				"Corporation" => "Corporation",
				"GovernmentOrganization" => "Government Organization",
				"NGO" => "NGO",
				"EducationalOrganization" => "Educational Organization",
				"CollegeOrUniversity" => "&mdash; College or University",
				"ElementarySchool" => "&mdash; Elementary School",
				"HighSchool" => "&mdash; High School",
				"MiddleSchool" => "&mdash; Middle School",
				"Preschool" => "&mdash; Preschool",
				"School" => "&mdash; School",
				"PerformingGroup" => "Performing Group",
				"DanceGroup" => "&mdash; Dance Group",
				"MusicGroup" => "&mdash; Music Group",
				"TheaterGroup" => "&mdash; Theater Group",
				"SportsTeam" => "Sports Team",
				"LocalBusiness" => "Local Business",
				"AnimalShelter" => "Animal Shelter",
				"AutomotiveBusiness" => "Automotive Business",
				"AutoBodyShop" => "&mdash; Auto Body Shop",
				"AutoDealer" => "&mdash; Auto Dealer",
				"AutoPartsStore" => "&mdash; Auto Parts Store",
				"AutoRental" => "&mdash; Auto Rental",
				"AutoRepair" => "&mdash; Auto Repair",
				"AutoWash" => "&mdash; Auto Wash",
				"GasStation" => "&mdash; Gas Station",
				"MotorcycleDealer" => "&mdash; Motorcycle Dealer",
				"MotorcycleRepair" => "&mdash; Motorcycle Repair",
				"ChildCare" => "Child Care",
				"DryCleaningOrLaundry" => "Dry Cleaning or Laundry",
				"EmergencyService" => "Emergency Service",
				"FireStation" => "&mdash; Fire Station",
				"Hospital" => "&mdash; Hospital",
				"PoliceStation" => "&mdash; Police Station",
				"EmploymentAgency" => "Employment Agency",
				"EntertainmentBusiness" => "Entertainment Business",
				"AdultEntertainment" => "&mdash; Adult Entertainment",
				"AmusementPark" => "&mdash; Amusement Park",
				"ArtGallery" => "&mdash; Art Gallery",
				"Casino" => "&mdash; Casino",
				"ComedyClub" => "&mdash; Comedy Club",
				"MovieTheater" => "&mdash; Movie Theater",
				"NightClub" => "&mdash; Night Club",
				"FinancialService" => "Financial Service",
				"AccountingService" => "&mdash; Accounting Service",
				"AutomatedTeller" => "&mdash; Automated Teller",
				"BankOrCreditUnion" => "&mdash; Bank or Credit Union",
				"InsuranceAgency" => "&mdash; Insurance Agency",
				"FoodEstablishment" => "Food Establishment",
				"Bakery" => "&mdash; Bakery",
				"BarOrPub" => "&mdash; Bar or Pub",
				"Brewery" => "&mdash; Brewery",
				"CafeOrCoffeeShop" => "&mdash; Cafe or Coffee Shop",
				"FastFoodRestaurant" => "&mdash; Fast Food Restaurant",
				"IceCreamShop" => "&mdash; Ice Cream Shop",
				"Restaurant" => "&mdash; Restaurant",
				"Winery" => "&mdash; Winery",
				"GovernmentOffice" => "Government Office",
				"PostOffice" => "&mdash; Post Office",
				"HealthAndBeautyBusiness" => "Health And Beauty Business",
				"BeautySalon" => "&mdash; Beauty Salon",
				"DaySpa" => "&mdash; Day Spa",
				"HairSalon" => "&mdash; Hair Salon",
				"HealthClub" => "&mdash; Health Club",
				"NailSalon" => "&mdash; Nail Salon",
				"TattooParlor" => "&mdash; Tattoo Parlor",
				"HomeAndConstructionBusiness" => "Home And Construction Business",
				"Electrician" => "&mdash; Electrician",
				"GeneralContractor" => "&mdash; General Contractor",
				"HVACBusiness" => "&mdash; HVAC Business",
				"HousePainter" => "&mdash; House Painter",
				"Locksmith" => "&mdash; Locksmith",
				"MovingCompany" => "&mdash; Moving Company",
				"Plumber" => "&mdash; Plumber",
				"RoofingContractor" => "&mdash; Roofing Contractor",
				"InternetCafe" => "Internet Cafe",
				"Library" => " Library",
				"LodgingBusiness" => "Lodging Business",
				"BedAndBreakfast" => "&mdash; Bed And Breakfast",
				"Hostel" => "&mdash; Hostel",
				"Hotel" => "&mdash; Hotel",
				"Motel" => "&mdash; Motel",
				"MedicalOrganization" => "Medical Organization",
				"Dentist" => "&mdash; Dentist",
				"DiagnosticLab" => "&mdash; Diagnostic Lab",
				"Hospital" => "&mdash; Hospital",
				"MedicalClinic" => "&mdash; Medical Clinic",
				"Optician" => "&mdash; Optician",
				"Pharmacy" => "&mdash; Pharmacy",
				"Physician" => "&mdash; Physician",
				"VeterinaryCare" => "&mdash; Veterinary Care",
				"ProfessionalService" => "Professional Service",
				"AccountingService" => "&mdash; Accounting Service",
				"Attorney" => "&mdash; Attorney",
				"Dentist" => "&mdash; Dentist",
				"Electrician" => "&mdash; Electrician",
				"GeneralContractor" => "&mdash; General Contractor",
				"HousePainter" => "&mdash; House Painter",
				"Locksmith" => "&mdash; Locksmith",
				"Notary" => "&mdash; Notary",
				"Plumber" => "&mdash; Plumber",
				"RoofingContractor" => "&mdash; Roofing Contractor",
				"RadioStation" => "Radio Station",
				"RealEstateAgent" => "Real Estate Agent",
				"RecyclingCenter" => "Recycling Center",
				"SelfStorage" => "Self Storage",
				"ShoppingCenter" => "Shopping Center",
				"SportsActivityLocation" => "Sports Activity Location",
				"BowlingAlley" => "&mdash; Bowling Alley",
				"ExerciseGym" => "&mdash; Exercise Gym",
				"GolfCourse" => "&mdash; Golf Course",
				"HealthClub" => "&mdash; Health Club",
				"PublicSwimmingPool" => "&mdash; Public Swimming Pool",
				"SkiResort" => "&mdash; Ski Resort",
				"SportsClub" => "&mdash; Sports Club",
				"StadiumOrArena" => "&mdash; Stadium or Arena",
				"TennisComplex" => "&mdash; Tennis Complex",
				"Store" => " Store",
				"AutoPartsStore" => "&mdash; Auto Parts Store",
				"BikeStore" => "&mdash; Bike Store",
				"BookStore" => "&mdash; Book Store",
				"ClothingStore" => "&mdash; Clothing Store",
				"ComputerStore" => "&mdash; Computer Store",
				"ConvenienceStore" => "&mdash; Convenience Store",
				"DepartmentStore" => "&mdash; Department Store",
				"ElectronicsStore" => "&mdash; Electronics Store",
				"Florist" => "&mdash; Florist",
				"FurnitureStore" => "&mdash; Furniture Store",
				"GardenStore" => "&mdash; Garden Store",
				"GroceryStore" => "&mdash; Grocery Store",
				"HardwareStore" => "&mdash; Hardware Store",
				"HobbyShop" => "&mdash; Hobby Shop",
				"HomeGoodsStore" => "&mdash; HomeGoods Store",
				"JewelryStore" => "&mdash; Jewelry Store",
				"LiquorStore" => "&mdash; Liquor Store",
				"MensClothingStore" => "&mdash; Mens Clothing Store",
				"MobilePhoneStore" => "&mdash; Mobile Phone Store",
				"MovieRentalStore" => "&mdash; Movie Rental Store",
				"MusicStore" => "&mdash; Music Store",
				"OfficeEquipmentStore" => "&mdash; Office Equipment Store",
				"OutletStore" => "&mdash; Outlet Store",
				"PawnShop" => "&mdash; Pawn Shop",
				"PetStore" => "&mdash; Pet Store",
				"ShoeStore" => "&mdash; Shoe Store",
				"SportingGoodsStore" => "&mdash; Sporting Goods Store",
				"TireShop" => "&mdash; Tire Shop",
				"ToyStore" => "&mdash; Toy Store",
				"WholesaleStore" => "&mdash; Wholesale Store",
				"TelevisionStation" => "Television Station",
				"TouristInformationCenter" => "Tourist Information Center",
				"TravelAgency" => "Travel Agency",
				"Airport" => "Airport",
				"Aquarium" => "Aquarium",
				"Beach" => "Beach",
				"BusStation" => "BusStation",
				"BusStop" => "BusStop",
				"Campground" => "Campground",
				"Cemetery" => "Cemetery",
				"Crematorium" => "Crematorium",
				"EventVenue" => "Event Venue",
				"FireStation" => "Fire Station",
				"GovernmentBuilding" => "Government Building",
				"CityHall" => "&mdash; City Hall",
				"Courthouse" => "&mdash; Courthouse",
				"DefenceEstablishment" => "&mdash; Defence Establishment",
				"Embassy" => "&mdash; Embassy",
				"LegislativeBuilding" => "&mdash; Legislative Building",
				"Hospital" => "Hospital",
				"MovieTheater" => "Movie Theater",
				"Museum" => "Museum",
				"MusicVenue" => "Music Venue",
				"Park" => "Park",
				"ParkingFacility" => "Parking Facility",
				"PerformingArtsTheater" => "Performing Arts Theater",
				"PlaceOfWorship" => "Place Of Worship",
				"BuddhistTemple" => "&mdash; Buddhist Temple",
				"CatholicChurch" => "&mdash; Catholic Church",
				"Church" => "&mdash; Church",
				"HinduTemple" => "&mdash; Hindu Temple",
				"Mosque" => "&mdash; Mosque",
				"Synagogue" => "&mdash; Synagogue",
				"Playground" => "Playground",
				"PoliceStation" => "PoliceStation",
				"RVPark" => "RVPark",
				"StadiumOrArena" => "StadiumOrArena",
				"SubwayStation" => "SubwayStation",
				"TaxiStand" => "TaxiStand",
				"TrainStation" => "TrainStation",
				"Zoo" => "Zoo",
				"Residence" => "Residence",
				"ApartmentComplex" => "&mdash; Apartment Complex",
				"GatedResidenceCommunity" => "&mdash; Gated Residence Community",
				"SingleFamilyResidence" => "&mdash; Single Family Residence"
			);
		}

	}
}
