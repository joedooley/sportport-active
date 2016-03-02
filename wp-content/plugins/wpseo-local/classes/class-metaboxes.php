<?php

/**
 * WPSEO_Local_Metaboxes class.
 *
 * @package Yoast SEO Local
 * @since   1.0
 */
if ( !class_exists( 'WPSEO_Local_Metaboxes' ) ) {
	class WPSEO_Local_Metaboxes {

		var $days = array();
		var $options;

		/**
		 * Constructor for the WPSEO_Local_Metaboxes class.
		 *
		 * @since 1.0
		 */
		function __construct() {

			$this->options = get_option( 'wpseo_local' );

			// Create custom post type functionality + meta boxes for Custom Post Type
			add_action( 'add_meta_boxes', array( $this, 'add_location_metaboxes' ) );
			add_action( 'save_post', array( &$this, 'wpseo_locations_save_meta' ), 1, 2 );

			// Only add the filter on Yoast SEO before 3.0, because 3.0 removed this filter. 2.3.5 was the last 2.x
			// version
			if ( version_compare( WPSEO_VERSION, '2.3.5', '<=' ) ) {
				add_filter( 'wpseo_linkdex_results', array( &$this, 'filter_linkdex_results' ), 10, 3 );
			}
			add_filter( 'wpseo_social_meta_boxes', array( $this, 'filter_wpseo_social_meta_boxes') );

			// Add button for adding shortcodes in RTE
			add_action( 'media_buttons', array( &$this, 'add_media_buttons' ), 20 );
			add_action( 'admin_footer',  array( &$this, 'add_mce_popup' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts') );
			
		}


		/**
		 * Adds metabox for editing screen of the wpseo_locations Custom Post Type
		 */
		function add_location_metaboxes() {
			add_meta_box( 'wpseo_locations', __( 'Business address details', 'yoast-local-seo' ), array( &$this, 'metabox_locations' ), 'wpseo_locations', 'normal', 'high' );
		}

		/**
		 * Builds the metabox for editing screen of the wpseo_locations Custom Post Type
		 */
		function metabox_locations() {
			/** @var WPSEO_Local_Core */
			global $wpseo_local_core;

			$post_id = get_the_ID();

			echo '<div style="overflow: hidden;" id="wpseo-local-metabox">';

			// Noncename needed to verify where the data originated
			echo '<input type="hidden" name="locationsmeta_noncename" id="locationsmeta_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';


			// Copy from other locations field
			$locations = get_posts( array(
				'post_type' => 'wpseo_locations',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',
				'fields' => 'ids',
				'exclude' => $post_id,
			) );

			if( count( $locations ) > 0 ) :
				echo '<p>';
				echo '<label class="textinput">' . __('Copy data from another location', 'yoast-local-seo') . ':</label>';
				echo '<select class="chzn-select" name="_wpseo_copy_from_location" id="wpseo_copy_from_location" style="width: 400px;" data-placeholder="' . __( 'Choose your location', 'yoast-local-seo' ) . '">';
				echo '<option value=""></option>';
				foreach( $locations as $location_id ) :
					echo '<option value="' . $location_id . '">' . get_the_title( $location_id ) . '</option>';
				endforeach;
				echo '</select>';
				echo '</p>';
				echo '<p style="clear:both; margin-left: 150px;"><em><strong>' . __('Note', 'yoast-local-seo') . ':</strong> ' . __('selecting a location will overwrite all data below. If you accidently selected a location, just refresh the page and make sure you don\'t save it.', 'yoast-local-seo') . '</em></p><br>';
				

				wp_reset_postdata();
			endif;

			// Get the location data if its already been entered
			$business_type          = get_post_meta( $post_id, '_wpseo_business_type', true );
			$business_address       = get_post_meta( $post_id, '_wpseo_business_address', true );
			$business_city          = get_post_meta( $post_id, '_wpseo_business_city', true );
			$business_state         = get_post_meta( $post_id, '_wpseo_business_state', true );
			$business_zipcode       = get_post_meta( $post_id, '_wpseo_business_zipcode', true );
			$business_country       = get_post_meta( $post_id, '_wpseo_business_country', true );
			$business_phone         = get_post_meta( $post_id, '_wpseo_business_phone', true );
			$business_phone_2nd     = get_post_meta( $post_id, '_wpseo_business_phone_2nd', true );
			$business_fax           = get_post_meta( $post_id, '_wpseo_business_fax', true );
			$business_email         = get_post_meta( $post_id, '_wpseo_business_email', true );
			$notes_1 	            = get_post_meta( $post_id, '_wpseo_business_notes_1', true );
			$notes_2 	            = get_post_meta( $post_id, '_wpseo_business_notes_2', true );
			$notes_3     	        = get_post_meta( $post_id, '_wpseo_business_notes_3', true );
			$business_url 	        = get_post_meta( $post_id, '_wpseo_business_url', true );
			$business_vat_id 	    = get_post_meta( $post_id, '_wpseo_business_vat_id', true );
			$business_tax_id 	    = get_post_meta( $post_id, '_wpseo_business_tax_id', true );
			$business_coc_id 	    = get_post_meta( $post_id, '_wpseo_business_coc_id', true );
			$coordinates_lat        = get_post_meta( $post_id, '_wpseo_coordinates_lat', true );
			$coordinates_long       = get_post_meta( $post_id, '_wpseo_coordinates_long', true );
			$is_postal_address      = get_post_meta( $post_id, '_wpseo_is_postal_address', true );
			$multiple_opening_hours = get_post_meta( $post_id, '_wpseo_multiple_opening_hours', true );
			$multiple_opening_hours = $multiple_opening_hours == 'on';

			// Echo out the field
			echo '<p><label class="textinput" for="wpseo_business_type">Business type:</label>';
			echo '<select class="chzn-select" name="_wpseo_business_type" id="wpseo_business_type" style="width: 200px;" data-placeholder="' . __( 'Choose your business type', 'yoast-local-seo' ) . '">';
			echo '<option></option>';
			foreach ( $wpseo_local_core->get_local_business_types() as $bt_option => $bt_label ) {
				echo '<option ' . selected( $business_type, $bt_option, false ) . ' value="' . $bt_option . '">' . $bt_label . '</option>';
			}
			echo '</select></p>';
			echo '<p class="desc label">' . sprintf( __( 'If your business type is not listed, please read %sthe FAQ entry%s.', 'yoast-local-seo' ), '<a href="http://kb.yoast.com/article/49-my-business-is-not-listed-can-you-add-it" target="_blank">', '</a>' ) . '</p><br class="clear">';
			echo '<p><label class="textinput" for="wpseo_business_address">' . __( 'Business address:', 'yoast-local-seo' ) . '</label>';
			echo '<input type="text" name="_wpseo_business_address" id="wpseo_business_address" value="' . $business_address . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_city">' . __( 'Business city', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_city" id="wpseo_business_city" value="' . $business_city . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_state">' . __( 'Business state', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_state" id="wpseo_business_state" value="' . $business_state . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_zipcode">' . __( 'Business zipcode', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_zipcode" id="wpseo_business_zipcode" value="' . $business_zipcode . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_country">' . __( 'Business country', 'yoast-local-seo' ) . ':</label>';
			echo '<select class="chzn-select" name="_wpseo_business_country" id="wpseo_business_country" style="width: 200px; margin-top: 8px;" data-placeholder="' . __( 'Choose your country', 'yoast-local-seo' ) . '">';
			echo '<option></option>';
			$countries = WPSEO_Local_Frontend::get_country_array();
			foreach ( $countries as $key => $val ) {
				echo '<option value="' . $key . '"' . ( $business_country == $key ? ' selected="selected"' : '' ) . '>' . $countries[$key] . '</option>';
			}
			echo '</select></p>';
			echo '<p><label class="textinput" for="wpseo_business_phone">' . __( 'Main phone number', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_phone" id="wpseo_business_phone" value="' . $business_phone . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_phone_2nd">' . __( 'Second phone number', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_phone_2nd" id="wpseo_business_phone_2nd" value="' . $business_phone_2nd . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_fax">' . __( 'Fax number', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_fax" id="wpseo_business_fax" value="' . $business_fax . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_email">' . __( 'Email address', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_email" id="wpseo_business_email" value="' . $business_email . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_url">' . __( 'URL', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_url" id="wpseo_business_url" value="' . $business_url . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_vat_id">' . __( 'VAT ID', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_vat_id" id="wpseo_business_vat_id" value="' . $business_vat_id . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_tax_id">' . __( 'Tax ID', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_tax_id" id="wpseo_business_tax_id" value="' . $business_tax_id . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_business_coc_id">' . __( 'Chamber of Commerce ID', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_business_coc_id" id="wpseo_business_coc_id" value="' . $business_coc_id . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_custom_notes_1">' . __( 'Notes', 'yoast-local-seo' ) . ':</label>';
			echo '<textarea name="_wpseo_business_notes_1" id="wpseo_business_notes_1" >' . $notes_1 . '</textarea></p>';
			echo '<p><label class="textinput" for="wpseo_business_notes_2">' . __( 'Notes', 'yoast-local-seo' ) . ':</label>';
			echo '<textarea name="_wpseo_business_notes_2" id="wpseo_business_notes_2" >' . $notes_2 . '</textarea></p>';
			echo '<p><label class="textinput" for="wpseo_business_notes_3">' . __( 'Notes', 'yoast-local-seo' ) . ':</label>';
			echo '<textarea name="_wpseo_business_notes_3" id="wpseo_business_notes_3" >' . $notes_3 . '</textarea></p>';

			echo '<p>' . __( 'You can enter the lat/long coordinates yourself. If you leave them empty they will be calculated automatically. If you want to re-calculate these fields, please make them blank before saving this location.', 'yoast-local-seo' ) . '</p>';
			echo '<p><label class="textinput" for="wpseo_coordinates_lat">' . __( 'Latitude', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_coordinates_lat" id="wpseo_coordinates_lat" value="' . $coordinates_lat . '" /></p>';
			echo '<p><label class="textinput" for="wpseo_coordinates_long">' . __( 'Longitude', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="text" name="_wpseo_coordinates_long" id="wpseo_coordinates_long" value="' . $coordinates_long . '" /></p>';
			echo '<p>' . __( 'If the marker is not in the right location for your store, you can drag the pin to the location where you want it.', 'yoast-local-seo' ) . '</p>';
			wpseo_local_show_map( array( 'id' => $post_id, 'echo' => true, 'show_route' => false, 'map_style' => 'roadmap', 'draggable' => true ) );
			echo '<p>';
			echo '<label class="textinput" for="wpseo_is_postal_address">' . __( 'This address is a postal address (not a physical location)', 'yoast-local-seo' ) . ':</label>';
			echo '<input type="checkbox" class="checkbox" name="_wpseo_is_postal_address" id="wpseo_is_postal_address" value="1" ' . checked( $is_postal_address, 1, false ) . ' />';
			echo '</p>';

			$hide_opening_hours = isset( $this->options['hide_opening_hours'] ) && $this->options['hide_opening_hours'] == 'on';
			// Opening hours
			echo '<br class="clear">';
			echo '<div id="hide-opening-hours" style="display: ' . ( $hide_opening_hours ? 'none' : 'block' ) . ';">';
			echo '<h4>' . __( 'Opening hours', 'yoast-local-seo' ) . '</h4>';

			echo '<div id="opening-hours-multiple">';
			echo '<label for="wpseo_multiple_opening_hours" class="textinput">' . __( 'I have two sets of opening hours per day', 'yoast-local-seo' ) . ':</label>';
			echo '<input class="checkbox" id="wpseo_multiple_opening_hours" type="checkbox" name="_wpseo_multiple_opening_hours" value="on" ' . checked( true, $multiple_opening_hours, false ) . '> ';
			echo '</div>';
			echo '<br class="clear">';

			foreach ( $wpseo_local_core->days as $key => $day ) {
				$field_name = '_wpseo_opening_hours_' . $key;
				$value_from = get_post_meta( $post_id, $field_name . '_from', true );
				if ( !$value_from )
					$value_from = '09:00';
				$value_to = get_post_meta( $post_id, $field_name . '_to', true );
				if ( !$value_to )
					$value_to = '17:00';
				$value_second_from = get_post_meta( $post_id, $field_name . '_second_from', true );
				if ( !$value_second_from )
					$value_second_from = '09:00';
				$value_second_to = get_post_meta( $post_id, $field_name . '_second_to', true );
				if ( !$value_second_to )
					$value_second_to = '17:00';

				echo '<div class="clear opening-hours">';

				if ( !isset( $this->options['opening_hours_24h'] ) )
					$this->options['opening_hours_24h'] = false;

				echo '<label class="textinput">' . $day . ':</label>';
				echo '<select class="openinghours_from" style="width: 100px;" id="' . $field_name . '_from" name="' . $field_name . '_from">';
				echo wpseo_show_hour_options( $this->options['opening_hours_24h'], $value_from );
				echo '</select><span id="' . $field_name . '_to_wrapper"> - ';
				echo '<select class="openinghours_to" style="width: 100px;" id="' . $field_name . '_to" name="' . $field_name . '_to">';
				echo wpseo_show_hour_options( $this->options['opening_hours_24h'], $value_to );
				echo '</select></span>';

				echo '<div class="clear opening-hour-second ' . ( !$multiple_opening_hours ? 'hidden' : '' ) . '">';
				echo '<div id="' . $field_name . '_second">';
				echo '<label class="textinput">&nbsp;</label>';
				echo '<select class="openinghours_from_second" style="width: 100px;" id="' . $field_name . '_second_from" name="' . $field_name . '_second_from">';
				echo wpseo_show_hour_options( $this->options['opening_hours_24h'], $value_second_from );
				echo '</select><span id="' . $field_name . '_second_to_wrapper"> - ';
				echo '<select class="openinghours_to_second" style="width: 100px;" id="' . $field_name . '_second_to" name="' . $field_name . '_second_to">';
				echo wpseo_show_hour_options( $this->options['opening_hours_24h'], $value_second_to );
				echo '</select></span>';
				echo '</div>';
				echo '</div>';

				echo '</div>';
			}

			echo '</div><!-- #hide-opening-hours -->';

			echo '<br class="clear" />';
			echo '</div>';
		}

		/**
		 * Handles and saves the data entered in the wpseo_locations metabox
		 */
		function wpseo_locations_save_meta( $post_id, $post ) {
			// First check if post type is wpseo_locations
			if ( $post->post_type == "wpseo_locations" ) {

				global $wpseo_local_core;

				// verify this came from the our screen and with proper authorization,
				// because save_post can be triggered at other times
				if ( false == isset( $_POST['locationsmeta_noncename'] ) || ( isset( $_POST['locationsmeta_noncename'] ) && !wp_verify_nonce( $_POST['locationsmeta_noncename'], plugin_basename( __FILE__ ) ) ) ) {
					return $post_id;
				}

				// Is the user allowed to edit the post or page?
				if ( !current_user_can( 'edit_post', $post_id ) ) {
					return $post_id;
				}

				// OK, we're authenticated: we need to find and save the data
				// We'll put it into an array to make it easier to loop though.

				$locations_meta['_wpseo_business_type']          = isset( $_POST['_wpseo_business_type'] ) ? $_POST['_wpseo_business_type'] : 'LocalBusiness';
				$locations_meta['_wpseo_business_address']       = isset( $_POST['_wpseo_business_address'] ) ? $_POST['_wpseo_business_address'] : '';
				$locations_meta['_wpseo_business_city']          = isset( $_POST['_wpseo_business_city'] ) ? $_POST['_wpseo_business_city'] : '';
				$locations_meta['_wpseo_business_state']         = isset( $_POST['_wpseo_business_state'] ) ? $_POST['_wpseo_business_state'] : '';
				$locations_meta['_wpseo_business_zipcode']       = isset( $_POST['_wpseo_business_zipcode'] ) ? $_POST['_wpseo_business_zipcode'] : '';
				$locations_meta['_wpseo_business_country']       = isset( $_POST['_wpseo_business_country'] ) ? $_POST['_wpseo_business_country'] : '';
				$locations_meta['_wpseo_business_phone']         = isset( $_POST['_wpseo_business_phone'] ) ? $_POST['_wpseo_business_phone'] : '';
				$locations_meta['_wpseo_business_phone_2nd']     = isset( $_POST['_wpseo_business_phone_2nd'] ) ? $_POST['_wpseo_business_phone_2nd'] : '';
				$locations_meta['_wpseo_business_fax']           = isset( $_POST['_wpseo_business_fax'] ) ? $_POST['_wpseo_business_fax'] : '';
				$locations_meta['_wpseo_business_email']         = isset( $_POST['_wpseo_business_email'] ) ? $_POST['_wpseo_business_email'] : '';
				$locations_meta['_wpseo_business_vat_id']        = isset( $_POST['_wpseo_business_vat_id'] ) ? $_POST['_wpseo_business_vat_id'] : '';
				$locations_meta['_wpseo_business_tax_id']        = isset( $_POST['_wpseo_business_tax_id'] ) ? $_POST['_wpseo_business_tax_id'] : '';
				$locations_meta['_wpseo_business_coc_id']        = isset( $_POST['_wpseo_business_coc_id'] ) ? $_POST['_wpseo_business_coc_id'] : '';
				$locations_meta['_wpseo_business_notes_1']       = isset( $_POST['_wpseo_business_notes_1'] ) ? $_POST['_wpseo_business_notes_1'] : '';
				$locations_meta['_wpseo_business_notes_2']       = isset( $_POST['_wpseo_business_notes_2'] ) ? $_POST['_wpseo_business_notes_2'] : '';
				$locations_meta['_wpseo_business_notes_3']       = isset( $_POST['_wpseo_business_notes_3'] ) ? $_POST['_wpseo_business_notes_3'] : '';
				$locations_meta['_wpseo_is_postal_address']      = isset( $_POST['_wpseo_is_postal_address'] ) ? $_POST['_wpseo_is_postal_address'] : '';
				$locations_meta['_wpseo_multiple_opening_hours'] = isset( $_POST['_wpseo_multiple_opening_hours'] ) ? $_POST['_wpseo_multiple_opening_hours'] : '';
				foreach ( $wpseo_local_core->days as $key => $day ) {
					$field_name                                   = '_wpseo_opening_hours_' . $key;
					$locations_meta[$field_name . '_from']        = isset( $_POST[$field_name . '_from'] ) ? $_POST[$field_name . '_from'] : '';
					$locations_meta[$field_name . '_to']          = isset( $_POST[$field_name . '_to'] ) ? $_POST[$field_name . '_to'] : '';
					$locations_meta[$field_name . '_second_from'] = isset( $_POST[$field_name . '_second_from'] ) ? $_POST[$field_name . '_second_from'] : '';
					$locations_meta[$field_name . '_second_to']   = isset( $_POST[$field_name . '_second_to'] ) ? $_POST[$field_name . '_second_to'] : '';

					if( $locations_meta[$field_name . '_from'] == 'closed' ) {
						$locations_meta[$field_name . '_to'] = $locations_meta[$field_name . '_from'];
					}
					if( $locations_meta[$field_name . '_second_from'] == 'closed' ) {
						$locations_meta[$field_name . '_second_to'] = $locations_meta[$field_name . '_second_from'];
					}
				}

				$locations_meta['_wpseo_business_url'] 	         = isset( $_POST['_wpseo_business_url'] ) && '' != $_POST['_wpseo_business_url'] ? $_POST['_wpseo_business_url'] : get_permalink( $post_id );

				// Put http:// in front of the URL, if it's not there yet.
				if( !preg_match( "~^(?:f|ht)tps?://~i", $locations_meta['_wpseo_business_url'] ) ) {
			        $locations_meta['_wpseo_business_url'] = "http://" . $locations_meta['_wpseo_business_url'];
			    }

				// If lat/long fields are empty or address is changed calculate them
				$coords_lat_old = get_post_meta( $post_id, '_wpseo_coordinates_lat', true );
				$coords_long_old = get_post_meta( $post_id, '_wpseo_coordinates_long', true );
				$old_address = get_post_meta( $post_id, '_wpseo_business_address', true );
				$new_address = isset( $_POST['_wpseo_business_address'] ) ? $_POST['_wpseo_business_address'] : '';
				
				if ( empty( $_POST['_wpseo_coordinates_lat'] ) || empty( $_POST['_wpseo_coordinates_long'] ) || $new_address != $old_address || $_POST['_wpseo_coordinates_lat'] != $coords_lat_old || $_POST['_wpseo_coordinates_long'] != $coords_long_old ) {

					$coords_lat = $_POST['_wpseo_coordinates_lat'];
					$coords_long = $_POST['_wpseo_coordinates_long'];

					if( empty( $_POST['_wpseo_coordinates_lat'] ) || empty( $_POST['_wpseo_coordinates_long'] ) || $new_address != $old_address ) {
						$geodata = $wpseo_local_core->get_geo_data( $locations_meta, true, $post_id );
						if( $geodata ) {
							$coords_lat = $geodata['coords']['lat'];
							$coords_long = $geodata['coords']['long'];
						}
					}

					update_post_meta( $post_id, '_wpseo_coordinates_lat', $coords_lat );
					update_post_meta( $post_id, '_wpseo_coordinates_long', $coords_long );
				}

				// Add values of $locations_meta as custom fields
				foreach ( $locations_meta as $key => $value ) { // Cycle through the $locations_meta array
					if ( $post->post_type == 'revision' )
						return; // Don't store custom data twice

					if ( !empty( $value ) )
						update_post_meta( $post_id, $key, $value );
					else
						delete_post_meta( $post_id, $key ); // Delete if blank
				}

				// Re-ping the new sitemap
				$wpseo_local_core->update_sitemap();
			}

			return true;
		}

		function add_media_buttons() {
			$is_post_edit_page = in_array( basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php'));
			if( !$is_post_edit_page )
			    return;

			if ( !post_type_supports( get_post_type(), 'editor') )
				return;

			echo '<a href="#TB_inline?width=480&height=600&inlineId=wpseo_add_map" class="thickbox button" id="wpseo_add_map_button" title="' . __('Insert Google map', 'yoast-local-seo') . '"><span class="wpseo_media_icon wpseo_icon_map"></span> ' . __('Map', 'yoast-local-seo') . '</a>';

			echo '<a href="#TB_inline?width=480&inlineId=wpseo_add_address" class="thickbox button" id="wpseo_add_address_button" title="' . __('Insert address', 'yoast-local-seo') . '"><span class="wpseo_media_icon wpseo_icon_address"></span> ' . __('Address', 'yoast-local-seo') . '</a> ';

			echo '<a href="#TB_inline?width=480&inlineId=wpseo_add_opening_hours" class="thickbox button" id="wpseo_add_opening_hours_button" title="' . __('Insert Opening hours', 'yoast-local-seo') . '"><span class="wpseo_media_icon wpseo_icon_opening_hours"></span> ' . __('Opening hours', 'yoast-local-seo') . '</a>';

			if ( wpseo_has_multiple_locations() ) {
				echo '<a href="#TB_inline?width=480&height=510&inlineId=wpseo_add_storelocator" class="thickbox button" id="wpseo_add_storelocator_button" title="' . __('Insert Store locator', 'yoast-local-seo') . '"><span class="wpseo_media_icon wpseo_icon_storelocator"></span> ' . __('Store locator', 'yoast-local-seo') . '</a>';
			}
		}

		function add_mce_popup(){
			$is_post_edit_page = in_array( basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php'));
			if( !$is_post_edit_page )
			    return;

			if ( !post_type_supports( get_post_type(), 'editor') )
				return;
		    ?>

		    <script>
			    function WPSEO_InsertMap() {
			    	var wrapper = jQuery('#wpseo_add_map');
			    	var location_id = jQuery("#wpseo_map_location_id").val();
			        var term_id = jQuery("#wpseo_map_term_id").val();
				    var center_id = jQuery("#wpseo_map_center_location_id").val();

			    	<?php if( wpseo_has_multiple_locations() ) { ?>
				        if( location_id == '' ) {
				            alert("<?php _e('Please select a location', 'yoast-local-seo'); ?>");
				            return;
				        }

				        if( location_id != 'all' && term_id != '' ) {
				            alert("<?php _e('If you want to use a category, please set location to \'all\'', 'yoast-local-seo'); ?>");
				            return;
				        }

				        if( location_id != 'all' && center_id != '' ) {
					        alert("<?php _e('If you select a single location , please set location to \'all\'', 'yoast-local-seo'); ?>");
					        return;
				        }
			        <?php } ?>

			        var map_style = jQuery('input[name=wpseo_map_style]:checked', '.wpseo_map_style').val()
			        var width = jQuery("#wpseo_map_width").val();
			        var height = jQuery("#wpseo_map_height").val();
			        var zoom = jQuery("#wpseo_map_zoom").val();
			        var scrollable = jQuery("#wpseo_map_scrollable").is(":checked") ? ' scrollable="1"' : ' scrollable="0"';
				    var draggable = jQuery("#wpseo_map_draggable").is(":checked") ? ' draggable="1"' : ' draggable="0"';
			        var show_route = jQuery("#wpseo_map_show_route").is(":checked") ? ' show_route="1"' : ' show_route="0"';
			        var show_state = jQuery("#wpseo_map_show_state").is(":checked") ? ' show_state="1"' : ' show_state="0"';
			        var show_country = jQuery("#wpseo_map_show_country").is(":checked") ? ' show_country="1"' : '';
			        var show_url = jQuery("#wpseo_map_show_url").is(":checked") ? ' show_url="1"' : '';
			        var show_email = jQuery("#wpseo_map_show_email").is(":checked") ? ' show_email="1"' : '';

			        var id = '';
			        if( location_id != 'undefined' && typeof location_id != 'undefined' ) {
			        	id = "id=\"" + location_id + "\" ";
			        }

			        var term = '';
			        if( term_id != 'undefined' && typeof term_id != 'undefined' && term_id != '' ) {
			        	term = "term_id=\"" + term_id + "\" ";
			        }

				    var center = ''
				    if( center_id != 'undefined' && typeof center_id != 'undefined' && center_id != '' ) {
					    center = "center=\"" + center_id + "\" ";
				    }

			        window.send_to_editor("[wpseo_map " + id + term + center + " width=\"" + width + "\" height=\"" + height + "\" zoom=\"" + zoom + "\" map_style=\"" + map_style + "\"" + scrollable + draggable + show_route + show_state + show_country + show_url + "]");
			    }
		        function WPSEO_InsertAddress() {
		            var location_id = jQuery("#wpseo_address_location_id").val();
		            var term_id = jQuery("#wpseo_address_term_id").val();

		            <?php if( wpseo_has_multiple_locations() ) { ?>
			            if( location_id == '' ) {
			                alert("<?php _e('Please select a location', 'yoast-local-seo'); ?>");
			                return;
			            }

				        if( location_id != 'all' && term_id != '' && jQuery("#wpseo_address_term_id").length ) {
				            alert("<?php _e('If you want to use a category, please set location to \'all\'', 'yoast-local-seo'); ?>");
				            return;
				        }
			        <?php } ?>

		            var hide_name = jQuery("#wpseo_hide_name").is(":checked") ? ' hide_name="1"' : '';
		            var oneline = jQuery("#wpseo_oneline").is(":checked") ? ' oneline="1"' : '';
		            var show_state = jQuery("#wpseo_show_state").is(":checked") ? ' show_state="1"' : ' show_state="0"';
		            var show_country = jQuery("#wpseo_show_country").is(":checked") ? ' show_country="1"' : ' show_country="0"';
		            var show_phone = jQuery("#wpseo_show_phone").is(":checked") ? ' show_phone="1"' : ' show_phone="0"';
		            var show_phone_2 = jQuery("#wpseo_show_phone_2").is(":checked") ? ' show_phone_2="1"' : ' show_phone_2="0"';
		            var show_fax = jQuery("#wpseo_show_fax").is(":checked") ? ' show_fax="1"' : ' show_fax="0"';
		            var show_email = jQuery("#wpseo_show_email").is(":checked") ? ' show_email="1"' : ' show_email="0"';
		            var show_url = jQuery("#wpseo_show_url").is(":checked") ? ' show_url="1"' : '';
			        var show_vat = jQuery("#wpseo_show_vat_id").is(":checked") ? ' show_vat="1"' : '';
			        var show_tax = jQuery("#wpseo_show_tax_id").is(":checked") ? ' show_tax="1"' : '';
			        var show_coc = jQuery("#wpseo_show_coc_id").is(":checked") ? ' show_coc="1"' : '';
		            var show_opening_hours = jQuery("#wpseo_show_opening_hours").is(":checked") ? ' show_opening_hours="1"' : '';
		            var hide_closed = jQuery("#wpseo_hide_closed").is(":checked") ? ' hide_closed="1"' : '';
		            var orderby = '';
		            var order = '';

		            var id = '';
		            if( location_id != 'undefined' && typeof location_id != 'undefined' ) {
		            	id = "id=\"" + location_id + "\" ";
		            }

		            var term = '';
		            if( term_id != 'undefined' && typeof term_id != 'undefined' && term_id != '' ) {
			        	term = "term_id=\"" + term_id + "\" ";
			        }

		            var shortcode_name = 'wpseo_address';
		            if( location_id == 'all' ) {
		            	shortcode_name = 'wpseo_all_locations';

		            	orderby = ' orderby=' + jQuery("#wpseo_address_all_locations_orderby").val();
		            	order = ' order=' + jQuery("#wpseo_address_all_locations_order").val();
		            }

		            window.send_to_editor("[" + shortcode_name + " " + id + term + hide_name + oneline + show_state + show_country + show_phone +show_phone_2 + show_fax + show_email + show_url + show_vat + show_tax + show_coc + show_opening_hours + hide_closed + orderby + order + "]");
		        }
		        function WPSEO_InsertOpeningHours() {
		        	var wrapper = jQuery('#wpseo_add_opening_hours');

		            var location_id = jQuery("#wpseo_oh_location_id").val();
		            if( location_id == '' ) {
		                alert("<?php _e('Please select a location', 'yoast-local-seo'); ?>");
		                return;
		            }

		            var id = '';
		            if( location_id != 'undefined' && typeof location_id != 'undefined' ) {
		            	id = "id=\"" + location_id + "\" ";
		            }
		            var hide_closed = jQuery("#wpseo_oh_hide_closed").is(":checked") ? ' hide_closed="1"' : '';

		            window.send_to_editor("[wpseo_opening_hours " + id + hide_closed + "]");
		        }
		        <?php if ( wpseo_has_multiple_locations() ) { ?>
		        function WPSEO_InsertStorelocator() {
		        	var show_map = jQuery("#wpseo_sl_show_map").is(":checked") ? ' show_map="1"' : ' show_map="0"';
		        	var scrollable = jQuery("#wpseo_sl_scrollable").is(":checked") ? ' scrollable="1"' : ' scrollable="0"';
			        var draggable = jQuery("#wpseo_sl_draggable").is(":checked") ? ' draggable="1"' : ' draggable="0"';
		        	var show_radius = jQuery("#wpseo_sl_show_radius").is(":checked") ? ' show_radius="1"' : '';
		        	var show_nearest_suggestion = jQuery("#wpseo_sl_show_nearest_suggestion").is(":checked") ? ' show_nearest_suggestion="1"' : ' show_nearest_suggestion="0"';
		        	var show_filter = jQuery("#wpseo_sl_show_filter").is(":checked") ? ' show_filter="1"' : '';
		        	var radius = ' radius="' + jQuery("#wpseo_sl_radius").val() + '"';

		            var map_style = jQuery('input[name=wpseo_sl_map_style]:checked', '.wpseo_map_style').val()
		            var oneline = jQuery("#wpseo_sl_oneline").is(":checked") ? ' oneline="1"' : '';
		            var show_state = jQuery("#wpseo_sl_show_state").is(":checked") ? ' show_state="1"' : '';
		            var show_country = jQuery("#wpseo_sl_show_country").is(":checked") ? ' show_country="1"' : '';
		            var show_phone = jQuery("#wpseo_sl_show_phone").is(":checked") ? ' show_phone="1"' : '';
		            var show_phone_2 = jQuery("#wpseo_sl_show_phone_2").is(":checked") ? ' show_phone_2="1"' : '';
		            var show_fax = jQuery("#wpseo_sl_show_fax").is(":checked") ? ' show_fax="1"' : '';
		            var show_email = jQuery("#wpseo_sl_show_email").is(":checked") ? ' show_email="1"' : '';
		            var show_url = jQuery("#wpseo_sl_show_url").is(":checked") ? ' show_url="1"' : '';
		            var show_opening_hours = jQuery("#wpseo_sl_show_opening_hours").is(":checked") ? ' show_opening_hours="1"' : '';
		            var hide_closed = jQuery("#wpseo_sl_hide_closed").is(":checked") ? ' hide_closed="1"' : '';
		            window.send_to_editor("[wpseo_storelocator " + show_map + scrollable + draggable + show_radius + show_nearest_suggestion + radius + show_filter + " map_style=\"" + map_style + "\"" + oneline + show_state + show_country + show_phone +show_phone_2 + show_fax + show_email + show_url + show_opening_hours + hide_closed + "]");
		        }

		        function WPSEO_Address_Change_Order( obj ) {
		        	if( jQuery(obj).val() == 'all' ) {
		        		jQuery( '#wpseo_address_all_locations_order_wrapper' ).slideDown();
		        	}
		        	else {
		        		jQuery( '#wpseo_address_all_locations_order_wrapper' ).slideUp();
		        		jQuery( '#wpseo_address_term_id' ).val('');
		        	}
		        }

		        function WPSEO_Address_Change_Term_Order( obj ) {
		        	if( jQuery(obj).val() != 'all' && jQuery(obj).val() != '' ) {
		        		jQuery( '#wpseo_address_location_id' ).val('all');
		        	}
		        }

		        function WPSEO_Map_Change_Location( obj ) {
		        	if( jQuery(obj).val() != 'all' ) {
		        		jQuery( '#wpseo_map_term_id' ).val('');
		        	}
		        }

		        function WPSEO_Map_Change_Term( obj ) {
		        	if( jQuery(obj).val() != 'all' && jQuery(obj).val() != '' ) {
		        		jQuery( '#wpseo_map_location_id' ).val('all');
		        	}
		        }
		        <?php } ?>
		    </script>

		    <div id="wpseo_add_map" style="display:none;">
		        <div class="wrap">
		            <div>
		            	<style>
		            		.wpseo-textfield {
		            			border: 1px solid #dfdfdf;
		            			-webkit-border-radius: 3px;
		            			border-radius: 3px;
		            			width: 60px;
		            		}
		            		.wpseo-select {
		            			width: 100px;
		            		}
		            		.wpseo-for-textfield {
		            			display: inline-block;
		            			width: 70px;
		            		}
		            	</style>

		                <div style="padding:15px 15px 0 15px;">
		                    <h3><?php _e('Insert Google Map', 'yoast-local-seo'); ?></h3>
		                </div>

		                <?php if ( wpseo_has_multiple_locations() ) { ?>
		                <div style="padding:15px 15px 0 15px;">
		                    <select id="wpseo_map_location_id" onchange="WPSEO_Map_Change_Location( this )">
		                        <option value="">  -- <?php _e('Select a location', 'yoast-local-seo'); ?>  -- </option>
		                        <?php
		                            $locations = get_posts( array(
                    					'post_type'      => 'wpseo_locations',
                    					'posts_per_page' => -1,
                    					'orderby' => 'title',
                    					'order' => 'ASC',
                    					'fields' => 'ids'
                    				) );

		                            if( ! empty( $locations ) ) {
		                                echo '<option value="all">' . __('All locations', 'yoast-local-seo') . '</option>';

			                            foreach ( $locations as $location_id ) {
				                            ?>
				                            <option value="<?php echo $location_id; ?>" <?php selected( $location_id, get_the_ID(), true ); ?>><?php echo get_the_title( $location_id ); ?></option>
			                                <?php
			                            }
		                            }
		                        ?>
		                    </select>
		                    <select id="wpseo_map_term_id" onchange="WPSEO_Map_Change_Term( this )">
		                        <option value="">  -- <?php _e('Select a category', 'yoast-local-seo'); ?>  -- </option>
		                        <?php
		                            $categories = get_terms( 'wpseo_locations_category', array(
                    					'hide_empty' => false,
                    				) );
                    				
                    				foreach( $categories as $category ) {
		                                ?>
		                                <option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
		                                <?php
		                            }
		                        ?>
		                    </select> <br/>
			                <p><?php _e( 'Center map on this location', 'yoast-local-seo' ); ?></p>
			                <select id="wpseo_map_center_location_id"">
				                <?php
				                $locations = get_posts( array(
					                'post_type'      => 'wpseo_locations',
					                'posts_per_page' => -1,
					                'orderby' => 'title',
					                'order' => 'ASC',
					                'fields' => 'ids'
				                ) );

				                if( ! empty( $locations ) ) {
					                echo '<option value="">' . __('All locations', 'yoast-local-seo') . '</option>';

					                foreach ( $locations as $location_id ) {
						                ?>
						                <option value="<?php echo $location_id; ?>" <?php selected( $location_id, get_the_ID(), true ); ?>><?php echo get_the_title( $location_id ); ?></option>
						                <?php
					                }
				                }
				                ?>
			                </select>
		                </div>
		                <?php } ?>

		                <div style="padding:15px 15px 0 15px;">
		                	<label class="wpseo-for-textfield"><?php _e('Map style', 'yoast-local-seo'); ?>: </label>
		                	<ul>
		                	<?php
		                		$map_styles = array(
		                			'ROADMAP' => __('Roadmap', 'yoast-local-seo'),
		                			'HYBRID' => __('Hybrid', 'yoast-local-seo'),
		                			'SATELLITE' => __('Satellite', 'yoast-local-seo'),
		                			'TERRAIN' => __('Terrain', 'yoast-local-seo')
		                		);

		                		foreach( $map_styles as $key => $label ) {
		                			?>
		                			<li class="wpseo_map_style" style="display: inline-block; width: 120px; height: 150px; margin-right: 10px;text-align: center;">
		                				<label for="wpseo_map_style-<?php echo strtolower( $key ); ?>">
		                					<img src="<?php echo plugins_url( '/images/map-' . strtolower( $key ) . '.png', dirname( __FILE__ ) ); ?>" alt="<?php echo $label; ?>"><br>
		                					<?php echo $label; ?><br>
		                					<input type="radio" name="wpseo_map_style" id="wpseo_map_style-<?php echo strtolower( $key ); ?>" value="<?php echo strtolower( $key ); ?>" <?php checked( 'ROADMAP', $key ); ?>>
		                				</label>
		                			</li>
		                			<?php		
		                		}
		                	?>
		                	</ul>
		                </div>

		                <div style="padding:15px 15px 0 15px;">
		                	<label class="wpseo-for-textfield" for="wpseo_map_width"><?php _e('Width', 'yoast-local-seo'); ?>: </label><input id="wpseo_map_width" class="wpseo-textfield" value="400"><br>
		                	<label class="wpseo-for-textfield" for="wpseo_map_height"><?php _e('Height', 'yoast-local-seo'); ?>: </label><input id="wpseo_map_height" class="wpseo-textfield" value="300"><br>
		                	<label class="wpseo-for-textfield" for="wpseo_map_zoom"><?php _e('Zoom level', 'yoast-local-seo'); ?>: </label>
		                	<select id="wpseo_map_zoom" class="wpseo-select" value="300">
		                		<option value="-1"><?php _e('Auto', 'yoast-local-seo'); ?></option>
		                		<option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option>
		                	</select><br>
		                	<br>
		                	<input type="checkbox" id="wpseo_map_scrollable" checked="checked" /> <label for="wpseo_map_scrollable"><?php _e('Allow scrolling of the map', 'yoast-local-seo'); ?></label><br>
			                <input type="checkbox" id="wpseo_map_draggable" checked="checked" /> <label for="wpseo_map_draggable"><?php _e('Allow dragging of the map', 'yoast-local-seo'); ?></label><br>
		                	<input type="checkbox" id="wpseo_map_show_route" /> <label for="wpseo_map_show_route"><?php _e('Show route planner', 'yoast-local-seo'); ?></label><br>
		                    <input type="checkbox" id="wpseo_map_show_state" /> <label for="wpseo_map_show_state"><?php _e('Show state in info-popup', 'yoast-local-seo'); ?></label><br>
		                    <input type="checkbox" id="wpseo_map_show_country" /> <label for="wpseo_map_show_country"><?php _e('Show country in info-popup', 'yoast-local-seo'); ?></label><br>
		                    <input type="checkbox" id="wpseo_map_show_url" /> <label for="wpseo_map_show_url"><?php _e('Show URL in info-popup', 'yoast-local-seo'); ?></label><br>
		                    <input type="checkbox" id="wpseo_map_show_email" /> <label for="wpseo_map_show_email"><?php _e('Show email in info popup', 'yoast-local-seo'); ?></label><br>
		                </div>
		                <div style="padding:15px;">
		                    <input type="button" class="button-primary" value="<?php _e('Insert map', 'yoast-local-seo'); ?>" onclick="WPSEO_InsertMap();"/>&nbsp;&nbsp;&nbsp;
							<a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "yoast-local-seo"); ?></a>
		                </div>
		            </div>
		        </div>
		    </div>
		    <div id="wpseo_add_address" style="display:none;">
		        <div class="wrap">
		            <div>
		                <div style="padding:15px 15px 0 15px;">
		                    <h3><?php _e('Insert Address', 'yoast-local-seo'); ?></h3>
		                </div>

		                <?php if ( wpseo_has_multiple_locations() ) { ?>
		                <div style="padding:15px 15px 0 15px;">
		                    <select id="wpseo_address_location_id" onchange="WPSEO_Address_Change_Order( this );">
		                        <option value="">  -- <?php _e('Select a location', 'yoast-local-seo'); ?>  -- </option>
		                        <?php
		                            $locations = get_posts( array(
	                					'post_type'      => 'wpseo_locations',
	                					'posts_per_page' => -1,
	                					'orderby' => 'title',
	                					'order' => 'ASC',
	                					'fields' => 'ids'
	                				) );

			                        if( ! empty( $locations ) ) {
				                        echo '<option value="all">' . __('Show all locations', 'yoast-local-seo') . '</option>';

				                        foreach( $locations as $location_id ) {
					                        ?>
					                        <option value="<?php echo $location_id; ?>" <?php selected( $location_id, get_the_ID(), true ); ?>><?php echo get_the_title( $location_id ); ?></option>
				                            <?php
				                        }
			                        }
		                        ?>
		                    </select>
		                    <?php
		                    $categories = get_terms( 'wpseo_locations_category', array(
            					'hide_empty' => false,
            				) );
            				if( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
            				?>
			                    <select id="wpseo_address_term_id" onchange="WPSEO_Address_Change_Term_Order( this );">
			                        <option value="">  -- <?php _e('Select a category', 'yoast-local-seo'); ?>  -- </option>
			                        <?php
	                    				foreach( $categories as $category ) {
			                                ?>
			                                <option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
			                                <?php
			                            }
			                        ?>
			                    </select>
		                    <?php } ?>

		                    <br/>

		                    <div id="wpseo_address_all_locations_order_wrapper" style="display: none;">
		                    	<label for="wpseo_address_all_locations_orderby"><?php _e( 'Order by', 'yoast-local-seo' ); ?>: </label>
		                    	<select name="wpseo_address_all_locations_orderby" id="wpseo_address_all_locations_orderby">
		                    		<option value="title"><?php _e( 'Alphabetical', 'yoast-local-seo' ); ?></option>
		                    		<option value="date"><?php _e( 'By publish date', 'yoast-local-seo' ); ?></option>
		                    	</select><br>

		                    	<label for="wpseo_address_all_locations_order"><?php _e( 'Order', 'yoast-local-seo' ); ?>: </label>
		                    	<select name="wpseo_address_all_locations_order" id="wpseo_address_all_locations_order">
		                    		<option value="ASC"><?php _e( 'Ascending', 'yoast-local-seo' ); ?></option>
		                    		<option value="DESC"><?php _e( 'Descending', 'yoast-local-seo' ); ?></option>
		                    	</select>
		                    </div>
		                </div>
		                <?php } ?>

		                <div style="padding:15px 15px 0 15px;">
		                	<label for="wpseo_hide_name"><input type="checkbox" id="wpseo_hide_name" /> <?php _e('Hide business name', 'yoast-local-seo'); ?></label><br>
		                	<label for="wpseo_oneline"><input type="checkbox" id="wpseo_oneline" /> <?php _e('Show address on one line', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_show_state"><input type="checkbox" id="wpseo_show_state" checked /> <?php _e('Show state', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_show_country"><input type="checkbox" id="wpseo_show_country" checked /> <?php _e('Show country', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_show_phone"><input type="checkbox" id="wpseo_show_phone" checked /> <?php _e('Show phone number', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_show_phone_2nd"><input type="checkbox" id="wpseo_show_phone_2nd" checked /> <?php _e('Show 2nd phone number', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_show_fax"><input type="checkbox" id="wpseo_show_fax" checked /> <?php _e('Show fax number', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_show_email"><input type="checkbox" id="wpseo_show_email" checked /> <?php _e('Show email', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_show_url"><input type="checkbox" id="wpseo_show_url" /> <?php _e('Show URL', 'yoast-local-seo'); ?></label><br>
			                <label for="wpseo_show_vat_id"><input type="checkbox" id="wpseo_show_vat_id" /> <?php _e('Show VAT ID', 'yoast-local-seo'); ?></label><br>
			                <label for="wpseo_show_tax_id"><input type="checkbox" id="wpseo_show_tax_id" /> <?php _e('Show Tax ID', 'yoast-local-seo'); ?></label><br>
			                <label for="wpseo_show_coc_id"><input type="checkbox" id="wpseo_show_coc_id" /> <?php _e('Show Chamber of Commerce ID', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_show_opening_hours"><input type="checkbox" id="wpseo_show_opening_hours" /> <?php _e('Show opening hours', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_hide_closed"><input type="checkbox" id="wpseo_hide_closed" /> <?php _e('Hide closed days', 'yoast-local-seo'); ?></label><br>
		                </div>
		                <div style="padding:15px;">
		                    <input type="button" class="button-primary" value="<?php _e('Insert address', 'yoast-local-seo'); ?>" onclick="WPSEO_InsertAddress();"/>&nbsp;&nbsp;&nbsp;
		                	<a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "yoast-local-seo"); ?></a>
		                </div>
		            </div>
		        </div>
		    </div>
		    <div id="wpseo_add_opening_hours" style="display:none;">
		        <div class="wrap">
		            <div>
		                <div style="padding:15px 15px 0 15px;">
		                    <h3><?php _e('Insert Opening Hours', 'yoast-local-seo'); ?></h3>
		                </div>

		                <?php if ( wpseo_has_multiple_locations() ) { ?>
		                <div style="padding:15px 15px 0 15px;">
		                    <select id="wpseo_oh_location_id">
		                        <option value="">  -- <?php _e('Select a location', 'yoast-local-seo'); ?>  -- </option>
		                        <?php
		                            $locations = get_posts( array(
                    					'post_type'      => 'wpseo_locations',
                    					'posts_per_page' => -1,
                    					'orderby' => 'title',
                    					'order' => 'ASC',
                    					'fields' => 'ids'
                    				) );
		                            foreach( $locations as $location_id ) {
		                                ?>
		                                <option value="<?php echo $location_id; ?>" <?php selected( $location_id, get_the_ID(), true ); ?>><?php echo get_the_title( $location_id ); ?></option>
		                                <?php
		                            }
		                        ?>
		                    </select> <br/>

		                </div>
		                <?php } ?>

		                <div style="padding:15px 15px 0 15px;">
		                	<label for="wpseo_oh_hide_closed"><input type="checkbox" id="wpseo_oh_hide_closed" /> <?php _e('Hide closed days', 'yoast-local-seo'); ?></label>
		                </div>
		                <div style="padding:15px;">
		                    <input type="button" class="button-primary" value="<?php _e('Insert opening hours', 'yoast-local-seo'); ?>" onclick="WPSEO_InsertOpeningHours();"/>&nbsp;&nbsp;&nbsp;
							<a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "yoast-local-seo"); ?></a>
		                </div>
		            </div>
		        </div>
		    </div>

		    <?php if ( wpseo_has_multiple_locations() ) { ?>
		    <div id="wpseo_add_storelocator" style="display:none;">
		        <div class="wrap">
		            <div>
		                <div style="padding:15px 15px 0 15px;">
		                    <h3><?php _e('Insert Store locator', 'yoast-local-seo'); ?></h3>
		                </div>

		                <div style="padding:15px 15px 0 15px;">
		                	<label for="wpseo_sl_show_map"><input type="checkbox" id="wpseo_sl_show_map" checked="checked" /> <?php _e('Show Map with the search results', 'yoast-local-seo'); ?></label><br>
		                	<label for="wpseo_sl_scrollable"><input type="checkbox" id="wpseo_sl_scrollable" checked="checked" /> <?php _e('Allow scrolling of the map', 'yoast-local-seo'); ?></label><br>
			                <label for="wpseo_sl_draggable"><input type="checkbox" id="wpseo_sl_draggable" checked="checked" /> <?php _e('Allow dragging of the map', 'yoast-local-seo'); ?></label><br>
		                	<label for="wpseo_sl_show_filter"><input type="checkbox" id="wpseo_sl_show_filter" /> <?php _e('Show filter to narrow down search results', 'yoast-local-seo'); ?></label><br>
		                	<label for="wpseo_sl_show_radius"><input type="checkbox" id="wpseo_sl_show_radius" /> <?php _e('Show radius to limit your search', 'yoast-local-seo'); ?></label><br>
		                	<label for="wpseo_sl_show_nearest_suggestion"><input type="checkbox" id="wpseo_sl_show_nearest_suggestion" checked="checked" /> <?php _e('Show the nearest location, if none are found within radius', 'yoast-local-seo'); ?></label><br>
		                	
		                	<br>
		                	<label for="wpseo_sl_radius"><?php printf( __('Search radius (in %s)', 'yoast-local-seo'), empty( $this->options['unit_system'] ) || $this->options['unit_system'] == 'METRIC' ? 'km' : 'mi' ); ?> <input type="text" id="wpseo_sl_radius" value="10" /></label><br>
		                </div>
		                <div style="padding:0 15px 0 15px;">
		                	<p><?php _e('Please specify below how the search results should look like.', 'yoast-local-seo'); ?></p>
		                	<label class="wpseo-for-textfield"><?php _e('Map style', 'yoast-local-seo'); ?>: </label>
		                	<ul>
		                	<?php
		                		$map_styles = array(
		                			'ROADMAP' => __('Roadmap', 'yoast-local-seo'),
		                			'HYBRID' => __('Hybrid', 'yoast-local-seo'),
		                			'SATELLITE' => __('Satellite', 'yoast-local-seo'),
		                			'TERRAIN' => __('Terrain', 'yoast-local-seo')
		                		);

		                		foreach( $map_styles as $key => $label ) {
		                			?>
		                			<li class="wpseo_map_style" style="display: inline-block; width: 120px; height: 150px; margin-right: 10px;text-align: center;">
		                				<label for="wpseo_sl_map_style-<?php echo strtolower( $key ); ?>">
		                					<img src="<?php echo plugins_url( '/images/map-' . strtolower( $key ) . '.png', dirname( __FILE__ ) ); ?>" alt="<?php echo $label; ?>"><br>
		                					<?php echo $label; ?><br>
		                					<input type="radio" name="wpseo_sl_map_style" id="wpseo_sl_map_style-<?php echo strtolower( $key ); ?>" value="<?php echo strtolower( $key ); ?>" <?php checked( 'ROADMAP', $key ); ?>>
		                				</label>
		                			</li>
		                			<?php		
		                		}
		                	?>
		                	</ul>
		                	<label for="wpseo_sl_oneline"><input type="checkbox" id="wpseo_sl_oneline" /> <?php _e('Show address on one line', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_show_state"><input type="checkbox" id="wpseo_sl_show_state" /> <?php _e('Show state', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_show_country"><input type="checkbox" id="wpseo_sl_show_country" /> <?php _e('Show country', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_show_phone"><input type="checkbox" id="wpseo_sl_show_phone" /> <?php _e('Show phone number', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_show_phone_2nd"><input type="checkbox" id="wpseo_sl_show_phone_2nd" /> <?php _e('Show 2nd phone number', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_show_fax"><input type="checkbox" id="wpseo_sl_show_fax" /> <?php _e('Show fax number', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_show_email"><input type="checkbox" id="wpseo_sl_show_email" /> <?php _e('Show email', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_show_url"><input type="checkbox" id="wpseo_sl_show_url" /> <?php _e('Show URL', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_show_opening_hours"><input type="checkbox" id="wpseo_sl_show_opening_hours" /> <?php _e('Show opening hours', 'yoast-local-seo'); ?></label><br>
		                    <label for="wpseo_sl_hide_closed"><input type="checkbox" id="wpseo_sl_hide_closed" /> <?php _e('Hide closed days', 'yoast-local-seo'); ?></label><br>
		                </div>
		                <div style="padding:15px;">
		                    <input type="button" class="button-primary" value="<?php _e('Insert Store locator', 'yoast-local-seo'); ?>" onclick="WPSEO_InsertStorelocator();"/>&nbsp;&nbsp;&nbsp;
		                	<a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "yoast-local-seo"); ?></a>
		                </div>
		            </div>
		        </div>
		    </div>
		    <?php } ?>

		    <?php
		}

		/**
		 * Filter the Page Analysis results to make sure we're giving the correct hints.
		 *
		 * @since 0.2
		 *
		 * @param array  $results The results array to filter and update.
		 * @param array  $job     The current jobs variables.
		 * @param object $post    The post object for the current page.
		 *
		 * @return array $results
		 */
		function filter_linkdex_results( $results, $job, $post ) {

			// @todo dit moet nog gaan werken voor single implementaties, first pass enzo.

			if ( $post->post_type != 'wpseo_locations' )
				return $results;

			$custom = get_post_custom();

			if ( strpos( $job['title'], $custom['_wpseo_business_city'][0] ) === false ) {
				$results['local-title'] = array(
					'val' => 4,
					'msg' => __( 'Your title does not contain your location\'s city, you should really add that.', 'yoast-local-seo' )
				);
			} else {
				$results['local-title'] = array(
					'val' => 9,
					'msg' => __( 'Your title contains your location\'s city, well done!', 'yoast-local-seo' )
				);
			}
			
			if ( stripos( $job['pageUrl'], $custom['_wpseo_business_city'][0] ) === false ) {
				$results['local-url'] = array(
					'val' => 4,
					'msg' => __( 'Your URL does not contain your location\'s city, you should really add that.', 'yoast-local-seo' )
				);
			} else {
				$results['local-url'] = array(
					'val' => 9,
					'msg' => __( 'Your URL contains your location\'s city, well done!', 'yoast-local-seo' )
				);
			}
			return $results;
		}

		/**
		 * Filters the meta boxes on the social tab on Yoast SEO to add a local checkbox.
		 *
		 * @param array $mbs Array of metaboxes.
		 *
		 * @return array
		 */
		function filter_wpseo_social_meta_boxes( $mbs ) {
			$mbs[ 'opengraph-local' ]   = array(
				"name" => "opengraph-local",
				"type" => "checkbox",
				"std"  => "",
				"title" => __( 'Business Markup', 'yoast-local-seo' ),
				"expl" => __( 'If this is your contact page, check this box to add OpenGraph markup so your business details are shared when this page is shared on Facebook.', 'yoast-local-seo' )
			);
			return $mbs;
		}

		/**
		 * Enqueues the pluginscripts.
		 */
		function enqueue_scripts() {
			wp_enqueue_script( 'wp-seo-local-seo', plugins_url( '../js/wp-seo-local-plugin' . WPSEO_CSSJS_SUFFIX . '.js', __FILE__ ), array(), WPSEO_VERSION, true );

			wp_localize_script( 'wp-seo-local-seo', 'wpseoLocalL10n', $this->localize_script() );
		}

		/**
		 * Localizes scripts for the videoplugin.
		 * @return array
		 */
		function localize_script() {
			$custom = get_post_custom();

			return array(
				'location'              => ( ! empty( $custom[ '_wpseo_business_city' ][0] ) ) ? $custom[ '_wpseo_business_city' ][0] : '',
				'title_no_location'     => __( 'Your title does not contain your location\'s city, you should really add that.', 'yoast-local-seo' ),
				'title_location'        => __( 'Your title contains your location\'s city, well done!', 'yoast-local-seo' ),
				'url_no_location'       => __( 'Your URL does not contain your location\'s city, you should really add that.', 'yoast-local-seo' ),
				'url_location'          => __( 'Your URL contains your location\'s city, well done!', 'yoast-local-seo' ),
			);
		}
	}
}
