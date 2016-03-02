<?php
/**
 * @package Admin
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin' ) ) {
	/**
	 * Class that holds most of the admin functionality for WP SEO Local.
	 */
	class WPSEO_Local_Admin {

		var $group_name = 'yoast_wpseo_local_options';
		var $option_name = 'wpseo_local';

		/**
		 * Class constructor
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'options_init' ) );

			// Adds page to WP SEO menu
			add_action( 'wpseo_submenu_pages', array( $this, 'register_settings_page' ), 20 );

			// Register local into admin_pages
			add_action( 'init', array( $this, 'register_wpseo' ) );

			// Add import options for Local SEO to general import panel of WP SEO
			if( wpseo_has_multiple_locations() ) {
				if ( version_compare( WPSEO_VERSION, '2', '>=' ) ) {
					add_action( 'wpseo_import_tab_header', array( $this, 'create_import_tab_header' ) );
					add_action( 'wpseo_import_tab_content', array( $this, 'create_import_tab_content' ) );
				} else {
					add_action( 'wpseo_import', array( $this, 'import_panel' ), 10, 1 );
				}
			}

			// Add styles and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'config_page_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'config_page_styles' ) );
			add_action( 'admin_footer', array( $this, 'config_page_footer' ) );

			// Flush the rewrite rules after options change
			add_action( 'update_option_wpseo_local', array( $this, 'update_multiple_locations' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'flush_rewrite_rules' ) );
		}

		/**
		 * Registers the wpseo_local setting for Settings API
		 *
		 * @since 1.0
		 */
		function options_init() {
			register_setting( 'yoast_wpseo_local_options', 'wpseo_local' );
		}

		/**
		 * Adds local page to admin_page variable of wpseo
		 */
		function register_wpseo() {
			add_filter('wpseo_admin_pages', array( $this, 'register_local_page') );
		}

		/**
		 * Registers local page 
		 */
		function register_local_page( $pages ) {
			$pages[] = 'wpseo_local';

			return $pages;
		}

		/**
		 * Registers the settings page in the WP SEO menu
		 *
		 * @since 1.0
		 */
		function register_settings_page( $submenu_pages ) {
			$submenu_pages[] = array(
				'wpseo_dashboard',
				__( 'Yoast SEO:', 'yoast-local-seo' ) . ' ' . __( 'Local SEO', 'yoast-local-seo' ),
				__( 'Local SEO', 'yoast-local-seo' ),
				'manage_options',
				'wpseo_local',
				array( $this, 'load_page' ),
			);

			return $submenu_pages;
		}

		/**
		 * Load the form for a WPSEO admin page
		 */
		function load_page() {
			if ( isset( $_GET['page'] ) ) {
				require_once( WPSEO_LOCAL_PATH . 'admin/pages/local.php' );
			}
		}

		/**
		 * Loads some CSS
		 *
		 * @since 1.0
		 */
		function config_page_styles() {
			global $pagenow, $post;

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$css_ext = '.css';
			} else {
				$css_ext = '.min.css';
			}

			if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpseo_local' ) {
				wp_enqueue_style( 'yoast-local-admin-css', plugins_url( 'styles/yst_plugin_tools' . $css_ext, dirname( __FILE__ ) ), WPSEO_LOCAL_VERSION );
			} else if ( ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpseo_local' ) || ( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) && $post->post_type == 'wpseo_locations' ) ) {
				wp_enqueue_style( 'jquery-chosen-css', plugins_url( 'styles/chosen' . $css_ext, dirname( __FILE__ ) ), WPSEO_LOCAL_VERSION );
				wp_enqueue_style( 'wpseo-local-admin-css', plugins_url( 'styles/admin' . $css_ext, dirname( __FILE__ ) ), WPSEO_LOCAL_VERSION );
			} else {
				if ( $pagenow == 'post-new.php' || $pagenow == 'post.php' ) {
					wp_enqueue_style( 'wpseo-local-admin-css', plugins_url( 'styles/admin' . $css_ext, dirname( __FILE__ ) ), WPSEO_LOCAL_VERSION );
				}
			}
		}

		/**
		 * Enqueues the (tiny) global JS needed for the plugin.
		 */
		function config_page_scripts() {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$css_ext = '.css';
				$js_ext = '.js';
			} else {
				$css_ext = '.min.css';
				$js_ext = '.min.js';
			}

			wp_enqueue_script( 'wpseo-local-global-script', plugins_url( 'js/wp-seo-local-global'.$js_ext, dirname( __FILE__ ) ), array( 'jquery' ), WPSEO_LOCAL_VERSION, true );
			global $pagenow, $post;
			if ( ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpseo_local' ) || ( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) && $post->post_type == 'wpseo_locations' )  || ( 'edit-tags.php' == $pagenow )  ) {
				wp_enqueue_script( 'jquery-chosen', plugins_url( 'js/chosen.jquery.min.js', dirname( __FILE__ ) ), array( 'jquery' ), WPSEO_LOCAL_VERSION, true );
				wp_enqueue_style( 'jquery-chosen-css', plugins_url( 'styles/chosen'.$css_ext, dirname( __FILE__ ) ), WPSEO_LOCAL_VERSION );
				wp_enqueue_media();
			}
		}

		/**
		 * Print the required JavaScript in the footer
		 */
		function config_page_footer() {
			global $pagenow, $post;
			if ( ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wpseo_local' ) || ( in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) && $post->post_type == 'wpseo_locations' ) ) {
				?>
				<script>
					jQuery(document).ready(function ($) {
						$(".chzn-select, #business_type, #location_country, #default_country").chosen({
							allow_single_deselect: true
						});
					});
				</script>
			<?php
			}
		}

		/**
		 * Creates new import tab
		 * @since 1.3.5
		 */
		function create_import_tab_header() {
			echo '<a class="nav-tab" id="local-seo-tab" href="#top#local-seo">Local SEO</a>';
		}

		/**
		 * Creates content for Local SEO import tab
		 * @since 1.3.5
		 */
		function create_import_tab_content() {
			echo '<div id="local-seo" class="wpseotab">';
			$this->output_import_html();

			if( ! empty( $_POST ) ) {
				$this->handle_csv_import();
			}

			echo '</div>';
		}

		/**
		 * Builds the HTML for the import form
		 *
		 * @since 1.3.5
		 */
		function output_import_html() {
			$upload_dir       = wp_upload_dir();
			$wpseo_upload_dir = $upload_dir["basedir"] . '/wpseo/import/';

			echo '<p>' . sprintf( __('View the %sdocumentation%s to check what format of the CSV file should be.', 'yoast-local-seo'), '<a href="https://yoast.com/question/csv-import-file-local-seo-look-like/" target="_blank">', '</a>' ) . '</p>';

			echo '<form action="" method="post" enctype="multipart/form-data">';
			WPSEO_Local_Admin_Wrappers::file_upload( 'csvuploadlocations', __( 'Upload CSV', 'yoast-local-seo' ) );
			echo '<label for="csv_separator" class="checkbox">' . __( 'Column separator', 'yoast-local-seo' ) . ':</label>';
			echo '<select class="textinput" id="csv_separator" name="csv_separator">';
			echo '<option value="comma">' . __( 'Comma', 'yoast-local-seo' ) . '</option>';
			echo '<option value="semicolon">' . __( 'Semicolon', 'yoast-local-seo' ) . '</option>';
			echo '</select>';
			echo '<br class="clear">';
			echo '<p>';
			echo '<input class="checkbox double" id="is-simplemap-import" type="checkbox" name="is-simplemap-import" value="1"> ';
			echo '<label for="is-simplemap-import">' . __( 'This CSV is exported by the SimpleMap plugin', 'yoast-local-seo' ) . '</label>';
			echo '</p>';
			echo '<br class="clear">';
			echo '<br/>';

			echo '<p><em>' . __('Note', 'yoast-local-seo') . ': ' . __('The Geocoding API is limited to 2,500 queries a day, so when you have large CSV files, with no coordinates, cut them in pieces of 2,500 rows and import them one a day. Indeed, it\'s not funny. It\'s reality.', 'yoast-local-seo') . '</em></p>';

			if( ! is_writable( $wpseo_upload_dir ) ) {
				echo '<p>' . sprintf( __( 'Make sure the %s directory is writeable.', 'yoast-local-seo' ), '<code>"' . $wpseo_upload_dir . '"</code>' ) . '</p>';
			}

			# Add a NONCE field
			echo wp_nonce_field( 'wpseo_local_import_nonce', 'wpseo_local_import_nonce_field' );

			echo '<input type="submit" class="button-primary" name="csv-import" value="Import" ' . ( ! is_writable( $wpseo_upload_dir ) ? ' disabled="disabled"' : '' ) . ' />';
			echo '</form>';

		}

		/**
		 * Generates the import panel for importing locations via CSV
		 */
		function import_panel() {

			echo '<div id="local-seo-import" class="yoastbox">';
			echo '<h2>' . __( 'CSV import of locations for Local Search', 'yoast-local-seo' ) . '</h2>';

			$this->output_import_html();

			if( ! empty( $_POST ) ) {
				$this->handle_csv_import();
			}

			echo '</div>';
		}

		/**
		 * Handles the CSV import and saves the locations
		 */
		public function handle_csv_import() {
			/**
			 * Set the max execution time of this script to 3600 seconds (1 hour)
			 * @TODO: Devide the upload in batches of 10 locations per batch.
			 */
			ini_set( 'max_execution_time', 3600 );
			$upload_dir       = wp_upload_dir();
			$wpseo_upload_dir = $upload_dir["basedir"] . '/wpseo/import/';
			$options  = get_option( $this->option_name );
			$count = 0;
			$last_imported = 0;

			if( isset( $_POST['csv-import'] ) && check_admin_referer( 'wpseo_local_import_nonce', 'wpseo_local_import_nonce_field' ) ) {
				$csv_path = $wpseo_upload_dir . basename( $_FILES['wpseo']['name']['csvuploadlocations'] );
				if ( !empty( $_FILES['wpseo'] ) && !move_uploaded_file( $_FILES['wpseo']['tmp_name']['csvuploadlocations'], $csv_path ) ) {
					echo '<p class="error">' . __( 'Sorry, there was an error while uploading the CSV file.<br>Please make sure the ' . $wpseo_upload_dir . ' directory is writable (chmod 777).', 'yoast-local-seo' ) . '</p>';
				}
				else {
					$is_simplemap_import = !empty( $_POST['is-simplemap-import'] ) && $_POST['is-simplemap-import'] == '1';

					$separator = ",";
					if ( ( !empty( $_POST['csv_separator'] ) && $_POST['csv_separator'] == "semicolon" ) && false == $is_simplemap_import ) {
						$separator = ";";
					}

					// Get location data from CSV
					$column_names = array( "name", "address", "city", "zipcode", "state", "country", "phone", "phone2nd", "description", "image", "category", "url", "business_type", "opening_hours_monday_from", "opening_hours_monday_to", "opening_hours_monday_second_from", "opening_hours_monday_second_to", "opening_hours_tuesday_from", "opening_hours_tuesday_to", "opening_hours_tuesday_second_from", "opening_hours_tuesday_second_to", "opening_hours_wednesday_from", "opening_hours_wednesday_to", "opening_hours_wednesday_second_from", "opening_hours_wednesday_second_to", "opening_hours_thursday_from", "opening_hours_thursday_to", "opening_hours_thursday_second_from", "opening_hours_thursday_second_to", "opening_hours_friday_from", "opening_hours_friday_to", "opening_hours_friday_second_from", "opening_hours_friday_second_to", "opening_hours_saturday_from", "opening_hours_saturday_to", "opening_hours_saturday_second_from", "opening_hours_saturday_second_to", "opening_hours_sunday_from", "opening_hours_sunday_to", "opening_hours_sunday_second_from", "opening_hours_sunday_second_to", "notes_1", "notes_2", "notes_3" );
					if( $is_simplemap_import ) {
						$column_names = array( "name", "address", "address2", "city", "state", "zipcode", "country", "phone", "email", "fax", "url", "description", "special", "lat", "long", "pubdate", "category", "tag" );
					}

					$handle       = fopen( $csv_path, "r" );
					$locations    = array();
					$row          = 0;
					while ( ( $csvdata = fgetcsv( $handle, 1000, $separator ) ) !== FALSE ) {
						if ( $row > 0 ) {
							$tmp_location = array();
							for ( $i = 0; $i < count( $column_names ); $i++ ) {

								// Skip columns for simplemap import
								if( $is_simplemap_import && in_array( $column_names[$i], array( 'address2', 'email', 'url', 'special', 'pubdate', 'tag' ) ) ) {
									continue;
								}

								if ( isset( $csvdata[$i] ) ) {
									$tmp_location[$column_names[$i]] = addslashes( $csvdata[$i] );
								}
							}
							array_push( $locations, $tmp_location );
						}
						$row++;
					}
					fclose( $handle );

					$debug = false;

					global $wpseo_local_core;
					$business_types = $wpseo_local_core->get_local_business_types();
					array_walk( $business_types, 'wpseo_local_sanitize_business_types' );

					// Create WordPress posts in custom post type
					$errors = array();

					foreach ( $locations as $location ) {
						// Create standard post data
						$current_post['ID']           = '';
						$current_post['post_title']   = isset( $location["name"] ) ? $location["name"] : '';
						$current_post['post_content'] = isset( $location["description"] ) ? $location["description"] : '';
						$current_post['post_status']  = "publish";
						$current_post['post_date']    = date( "Y-m-d H:i:s", time() );
						$current_post['post_type']    = 'wpseo_locations';

						$post_id = wp_insert_post( $current_post );

						if ( !$debug ) {
							if( empty( $location['lat'] ) && empty( $location['long'] ) ) {
								$full_address =  wpseo_local_get_address_format( $location['address'], true, $location['zipcode'], $location['city'], $location['state'], true, false, false );
								if( ! empty( $location['country'] ) )
									$full_address .= ', ' . WPSEO_Local_Frontend::get_country( $location['country'] );

								$geo_data = wpseo_geocode_address( $full_address );

								if ( ! is_wp_error( $geo_data ) && !empty( $geo_data->results[0] ) ) {
									$location['lat']  = $geo_data->results[0]->geometry->location->lat;
									$location['long'] = $geo_data->results[0]->geometry->location->lng;
								}
								else {
									$location['lat'] = '';
									$location['long'] = '';

									if( $geo_data->get_error_code() == 'wpseo-query-limit' ) {
										$errors[] = sprintf( __('The usage of the Google Maps API has exceeds their limits. Please consider entering an API key in the %soptions%s', 'yoast-local-seo' ), '<a href="' . admin_url( 'admin.php?page=wpseo_local' ) . '">', '</a>' );
										if( ! empty( $last_imported ) ) {
											$errors[] = sprintf( __( 'The last successfully imported location is <a href="%s" title="%s">%s</a>', 'yoast-local-seo' ), get_edit_post_link( $last_imported ), get_the_title( $last_imported ), get_the_title( $last_imported ) );
										}
										break;
									}
									else {
										$errors[] = sprintf( __('Location <em>' . esc_attr( $location["name"] ) . '</em> could not be geo-coded. %sEdit this location%s.', 'yoast-local-seo' ), '<a href="' . admin_url( 'post.php?post=' . esc_attr( $post_id ) . '&action=edit' ) . '">', '</a>' );
									}
								}
							}

							// Insert custom fields for location details
							if ( !empty( $post_id ) ) {
								add_post_meta( $post_id, "_wpseo_business_name", isset( $location["name"] ) ? $location["name"] : '', true );
								add_post_meta( $post_id, '_wpseo_business_address', isset( $location["address"] ) ? $location["address"] : '', true );
								add_post_meta( $post_id, '_wpseo_business_city', isset( $location["city"] ) ? $location["city"] : '', true );
								add_post_meta( $post_id, '_wpseo_business_state', isset( $location["state"] ) ? $location["state"] : '', true );
								add_post_meta( $post_id, '_wpseo_business_zipcode', isset( $location["zipcode"] ) ? $location["zipcode"] : '', true );
								add_post_meta( $post_id, '_wpseo_business_country', isset( $location["country"] ) ? $location["country"] : '', true );
								add_post_meta( $post_id, '_wpseo_business_phone', isset( $location["phone"] ) ? $location["phone"] : '', true );
								add_post_meta( $post_id, '_wpseo_business_fax', isset( $location["fax"] ) ? $location["fax"] : '', true );

								if( isset( $location["phone_2nd"] ) )
									add_post_meta( $post_id, '_wpseo_business_phone_2nd', $location["phone_2nd"], true );
								if( isset( $location["email"] ) )
									add_post_meta( $post_id, '_wpseo_business_email', $location["email"], true );

								if( isset( $location['category'] ) )
									wp_set_object_terms( $post_id, $location['category'], 'wpseo_locations_category' );

								if( isset( $location['business_type'] ) ) {
									$business_type = $location['business_type'];
									if( false == in_array( $business_type, array_keys( $business_types ) ) ) {
										$business_type = array_search( $business_type, $business_types );
									}

									add_post_meta( $post_id, '_wpseo_business_type', $business_type, true );

								}

								if( isset( $location['url'] ) )
									add_post_meta( $post_id, '_wpseo_business_url', $location['url'], true );

								// Add notes
								for( $i = 0; $i < 3; $i++ ) {
									$n = $i + 1;
									if( ! empty( $location['notes_' . $n ] ) ) {
										add_post_meta( $post_id, '_wpseo_business_notes_' . $n, $location['notes_' . $n] );
									}
								}

								add_post_meta( $post_id, '_wpseo_coordinates_lat', $location["lat"], true );
								add_post_meta( $post_id, '_wpseo_coordinates_long', $location["long"], true );

								$count++;
								$last_imported = $post_id;
							}

							// Add image as post thumbnail
							if ( !empty( $location['image'] ) ) {
								$wpseo_local_core->insert_attachment( $post_id, $location['image'], true );
							}

							// Opening hours
							foreach ( $wpseo_local_core->days as $key => $day ) {
								if( isset( $location['opening_hours_' . $key . '_from'] ) && ! empty( $location['opening_hours_' . $key . '_from'] ) && isset( $location['opening_hours_' . $key . '_to'] ) && ! empty( $location['opening_hours_' . $key . '_to'] ) ) {
									if( 'closed' == strtolower( $location['opening_hours_' . $key . '_from'] ) || 'closed' == strtolower( $location['opening_hours_' . $key . '_to'] ) ) {
										add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_from', 'closed', true );
									}
									else {
										$time_from = strtotime( $location['opening_hours_' . $key . '_from'] );
										$time_to = strtotime( $location['opening_hours_' . $key . '_to'] );

										if( false !== $time_from && false !== $time_to ) {
											add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_from', date( 'H:i', $time_from ), true );
											add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_to', date( 'H:i', $time_to ), true );
										} else {
											add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_from', 'closed', true );
											if( false === $time_from ) {
												$errors[] = sprintf( __( '%s is not a valid time notation', 'yoast-local-seo' ), $location['opening_hours_' . $key . '_from'] );
											} else if( false === $time_to ) {
												$errors[] = sprintf( __( '%s is not a valid time notation', 'yoast-local-seo' ), $location['opening_hours_' . $key . '_to'] );
											}
										}

										if( isset( $location['opening_hours_' . $key . '_second_from'] ) && ! empty( $location['opening_hours_' . $key . '_second_from'] ) && isset( $location['opening_hours_' . $key . '_second_to'] ) && ! empty( $location['opening_hours_' . $key . '_second_to'] ) ) {
											$time_second_from = strtotime( $location['opening_hours_' . $key . '_second_from'] );
											$time_second_to = strtotime( $location['opening_hours_' . $key . '_second_to'] );

											if( false !== $time_second_from && false !== $time_second_to ) {
												add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_second_from', date( 'H:i', $time_second_from ), true );
												add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_second_to', date( 'H:i', $time_second_to ), true );

												// Multiple openingtimes are set. Enable them in the backend.
												update_post_meta( $post_id, '_wpseo_multiple_opening_hours', 'on', true);
											} else {
												add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_second_from', 'closed', true );
												if( false === $time_second_from ) {
													$errors[] = sprintf( __( '%s is not a valid time notation', 'yoast-local-seo' ), $location['opening_hours_' . $key . '_second_from'] );
												} else if( false === $time_second_to ) {
													$errors[] = sprintf( __( '%s is not a valid time notation', 'yoast-local-seo' ), $location['opening_hours_' . $key . '_second_to'] );
												}
											}
										} else {
											add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_second_from', 'closed', true );
										}
									}
								} else {
									add_post_meta( $post_id, '_wpseo_opening_hours_' . $key . '_from', 'closed', true );
								}
							}
						}
					}

					$msg = '';
					if ( $count > 0 ) {
						$msg .= $count . ' locations found and successfully imported.<br/>';
					}

					if( ! empty( $errors ) ) {

						$msg .= '<p>';
						$msg .= '<strong>' . __('Some errors has occured', 'yoast-local-seo') . '</strong><br>';
						foreach( $errors as $error ) {
							$msg .= $error . '<br>';
						}
						$msg .= '</p>';
					}
					if ( $msg != '' ) {
						echo '<div id="message" class="message updated" style="width:94%;"><p>' . $msg . '</p></div>';
					}
				}
			}
		}

		/**
		 * Flushes the rewrite rules if multiple locations is turned on or off or the slug is changed.
		 *
		 * @since 1.3.1
		 */
		public function update_multiple_locations( $old_option_value, $new_option_value ) {
			$old_value_exists = array_key_exists( 'use_multiple_locations', $old_option_value );
			$new_value_exists = array_key_exists( 'use_multiple_locations', $new_option_value );

			$old_option_value['locations_slug'] = isset( $old_option_value['locations_slug'] ) ? esc_attr( $old_option_value['locations_slug'] ) : '';
			$new_option_value['locations_slug'] = isset( $new_option_value['locations_slug'] ) ? esc_attr( $new_option_value['locations_slug'] ) : '';

			if( ( false === $old_value_exists && true === $new_value_exists ) || ( $old_option_value['locations_slug'] != $new_option_value['locations_slug'] ) ) {
				set_transient( 'wpseo_local_permalinks_settings_changed', true, 60 );
			}
		}

		/**
		 * Flushes the rewrite rules if multiple locations is turned on or off or the slug is changed.
		 *
		 * @since 1.3.1
		 */
		public function flush_rewrite_rules() {
			if ( get_transient( 'wpseo_local_permalinks_settings_changed' ) == true ) {
				flush_rewrite_rules();

				delete_transient( 'plugin_settings_have_changed' );
			}
		}


	} /* End of class */

} /* End of class-exists wrapper */
