<?php

/**
 * Address shortcode handler
 *
 * @since 0.1
 *
 * @param array $atts Array of shortcode parameters
 *
 * @return string
 */
function wpseo_local_show_address( $atts ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'                 => '',
		'hide_name'          => false,
		'show_state'         => true,
		'show_country'       => true,
		'show_phone'         => true,
		'show_phone_2'       => true,
		'show_fax'           => true,
		'show_email'         => true,
		'show_url'           => false,
		'show_vat'           => false,
		'show_tax'           => false,
		'show_coc'           => false,
		'show_opening_hours' => false,
		'hide_closed'        => false,
		'oneline'            => false,
		'comment'            => '',
		'from_sl'	         => false,
		'from_widget'        => false,
		'widget_title'       => '',
		'before_title'       => '',
		'after_title'        => '',
		'echo'               => false
	), $atts ) );

	$options           = get_option( 'wpseo_local' );
	if( isset( $options['hide_opening_hours'] ) && $options['hide_opening_hours'] == 'on' ) {
		$atts['show_opening_hours'] = false;
	}

	$is_postal_address = false;

	if ( wpseo_has_multiple_locations() ) {
		if ( get_post_type() == 'wpseo_locations' ) {
			if ( ( $atts['id'] == '' || $atts['id'] == 'current' ) && ! is_post_type_archive() ) {
				$atts['id'] = get_queried_object_id();
			}

			if ( is_post_type_archive() && ( $atts['id'] == '' || $atts['id'] == 'current' ) ) {
				return '';
			}
		}
		else if ( $atts['id'] == '' ) {
			return is_singular() ? __( 'Please provide a post ID if you want to show an address outside a Locations singular page', 'yoast-local-seo' ) : '';
		}

		// Get the location data if its already been entered
		$business_name     = get_the_title( $atts['id'] );
		$business_type     = get_post_meta( $atts['id'], '_wpseo_business_type', true );
		$business_address  = get_post_meta( $atts['id'], '_wpseo_business_address', true );
		$business_city     = get_post_meta( $atts['id'], '_wpseo_business_city', true );
		$business_state    = get_post_meta( $atts['id'], '_wpseo_business_state', true );
		$business_zipcode  = get_post_meta( $atts['id'], '_wpseo_business_zipcode', true );
		$business_country  = get_post_meta( $atts['id'], '_wpseo_business_country', true );
		$business_phone    = get_post_meta( $atts['id'], '_wpseo_business_phone', true );
		$business_phone_2nd= get_post_meta( $atts['id'], '_wpseo_business_phone_2nd', true );
		$business_fax      = get_post_meta( $atts['id'], '_wpseo_business_fax', true );
		$business_email    = get_post_meta( $atts['id'], '_wpseo_business_email', true );
		$business_url      = get_post_meta( $atts['id'], '_wpseo_business_url', true );
		$business_vat      = get_post_meta( $atts['id'], '_wpseo_business_vat_id', true );
		$business_tax      = get_post_meta( $atts['id'], '_wpseo_business_tax_id', true );
		$business_coc      = get_post_meta( $atts['id'], '_wpseo_business_coc_id', true );
		$is_postal_address = get_post_meta( $atts['id'], '_wpseo_is_postal_address', true );
		$is_postal_address = $is_postal_address == '1';

		if( empty( $business_url ) ) {
			$business_url = get_permalink( $atts['id'] );
		}
	}
	else {
		$business_name    	= isset( $options['location_name'] ) ? $options['location_name'] : '';
		$business_type    	= isset( $options['business_type'] ) ? $options['business_type'] : '';
		$business_address 	= isset( $options['location_address'] ) ? $options['location_address'] : '';
		$business_city    	= isset( $options['location_city'] ) ? $options['location_city'] : '';
		$business_state   	= isset( $options['location_state'] ) ? $options['location_state'] : '';
		$business_zipcode 	= isset( $options['location_zipcode'] ) ? $options['location_zipcode'] : '';
		$business_country 	= isset( $options['location_country'] ) ? $options['location_country'] : '';
		$business_phone   	= isset( $options['location_phone'] ) ? $options['location_phone'] : '';
		$business_phone_2nd	= isset( $options['location_phone_2nd'] ) ? $options['location_phone_2nd'] : '';
		$business_fax     	= isset( $options['location_fax'] ) ? $options['location_fax'] : '';
		$business_email   	= isset( $options['location_email'] ) ? $options['location_email'] : '';
		$business_url     	= ! empty( $options['location_url'] ) ? $options['location_url'] : wpseo_xml_sitemaps_base_url( '' );
		$business_vat   	= isset( $options['location_vat_id'] ) ? $options['location_vat_id'] : '';
		$business_tax   	= isset( $options['location_tax_id'] ) ? $options['location_tax_id'] : '';
		$business_coc   	= isset( $options['location_coc_id'] ) ? $options['location_coc_id'] : '';
	}

	/*
	* This array can be used in a filter to change the order and the labels of contact details
	*/
	$business_contact_details = array(
		array(
			'key' => 'phone',
			'label' => __( 'Phone', 'yoast-local-seo' ),
		),
		array(
			'key' => 'phone_2',
			'label' => __( 'Secondary phone', 'yoast-local-seo' ),
		),
		array(
			'key' => 'fax',
			'label' => __( 'Fax', 'yoast-local-seo' ),
		),
		array(
			'key' => 'email',
			'label' => __( 'Email', 'yoast-local-seo' ),
		),
		array(
			'key' => 'url',
			'label' => __( 'URL', 'yoast-local-seo' ),
		),
		array(
			'key' => 'vat',
			'label' => __( 'VAT ID', 'yoast-local-seo' ),
		),
		array(
			'key' => 'tax',
			'label' => __( 'Tax ID', 'yoast-local-seo' ),
		),
		array(
			'key' => 'coc',
			'label' => __( 'Chamber of Commerce ID', 'yoast-local-seo' ),
		),
	);

	$business_contact_details = apply_filters( 'wpseo_local_contact_details', $business_contact_details );

	$tag_title_open  = '';
	$tag_title_close = '';
	if ( ! $atts['oneline'] ) {
		if ( ! $atts['from_widget'] ) {
			$tag_name        = apply_filters( 'wpseo_local_location_title_tag_name', 'h3' );
			$tag_title_open  = '<' . esc_html( $tag_name ) . '>';
			$tag_title_close = '</' . esc_html( $tag_name ) . '>';
		}
		else if ( $atts['from_widget'] && $atts['widget_title'] == '' ) {
			$tag_title_open  = $atts['before_title'];
			$tag_title_close = $atts['after_title'];
		}
	}

	$output = '<div id="wpseo_location-' . $atts['id'] . '" class="wpseo-location" itemscope itemtype="http://schema.org/' . ( $is_postal_address ? 'PostalAddress' : $business_type ) . '">';

	if( false == $atts['hide_name'] ) {
		$output .= $tag_title_open . ( $atts['from_sl'] ? '<a href="' . $business_url . '">' : '' ) . '<span class="wpseo-business-name" itemprop="name">' . $business_name . '</span>' . ( $atts['from_sl'] ? '</a>' : '' ) . $tag_title_close . ( $atts['oneline'] ? ', ' : '' );
	}
	$output .= '<' . ( $atts['oneline'] ? 'span' : 'div' ) . ' ' . ( $is_postal_address ? '' : 'itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"' ) . ' class="wpseo-address-wrapper">';

	// Output city/state/zipcode in right format
	$output .= wpseo_local_get_address_format( $business_address, $atts['oneline'], $business_zipcode, $business_city, $business_state, $atts['show_state'] );


	if ( $atts['show_country'] && ! empty( $business_country ) )
		$output .= $atts['oneline'] ? ', ' : ' ';


	if ( $atts['show_country'] && ! empty( $business_country ) ) {
		$output .= '<' . ( $atts['oneline'] ? 'span' : 'div' ) . '  class="country-name" itemprop="addressCountry">' . WPSEO_Local_Frontend::get_country( $business_country ) . '</' . ( $atts['oneline'] ? 'span' : 'div' ) . '>';
	}
	$output .= '</' . ( $atts['oneline'] ? 'span' : 'div' ) . '>';

	$details_output = '';
	foreach( $business_contact_details as $order => $details ) {

		if ( 'phone' == $details['key'] && $atts['show_phone'] && ! empty( $business_phone ) ) {
			$details_output .= sprintf( '<span class="wpseo-phone">%s: <a href="tel:' . preg_replace('/[^0-9+]/', '', $business_phone ) . '" class="tel"><span itemprop="telephone">' . $business_phone . '</span></a></span>' . ( $atts['oneline'] ? ' ' : '<br/>' ), $details['label'] );
		}
		if ( 'phone_2' == $details['key'] && $atts['show_phone_2'] && ! empty( $business_phone_2nd ) ) {
			$details_output .= sprintf( '<span class="wpseo-phone2nd">%s: <a href="tel:' . preg_replace('/[^0-9+]/', '', $business_phone_2nd ) . '" class="tel">' . $business_phone_2nd . '</a></span>' . ( $atts['oneline'] ? ' ' : '<br/>' ), $details['label'] );
		}
		if ( 'fax' == $details['key'] && $atts['show_fax'] && ! empty( $business_fax ) ) {
			$details_output .= sprintf( '<span class="wpseo-fax">%s: <span class="tel" itemprop="faxNumber">' . $business_fax . '</span></span>' . ( $atts['oneline'] ? ' ' : '<br/>' ), $details['label'] );
		}
		if ( 'email' == $details['key'] && $atts['show_email'] && ! empty( $business_email ) ) {
			$details_output .= sprintf( '<span class="wpseo-email">%s: <a href="mailto:' . $business_email . '" itemprop="email">' . $business_email . '</a></span>' . ( $atts['oneline'] ? ' ' : '<br/>' ), $details['label'] );
		}
		if ( 'url' == $details['key'] && $atts['show_url'] ) {
			$details_output .= sprintf( '<span class="wpseo-url">%s: <a href="' . $business_url . '" itemprop="url">' . $business_url . '</a></span>' . ( $atts['oneline'] ? ' ' : '<br/>' ), $details['label'] );
		}
		if( 'vat' == $details['key'] && $atts['show_vat'] && ! empty( $business_vat ) ) {
			$details_output .= sprintf( '<span class="wpseo-vat">%s: <span itemprop="vatID">' . $business_vat .'</span></span>' . ( $atts['oneline'] ? ' ' : '<br/>' ), $details['label'] );
		}
		if( 'tax' == $details['key'] && $atts['show_tax'] && ! empty( $business_tax ) ) {
			$details_output .= sprintf( '<span class="wpseo-tax">%s: <span itemprop="taxID">' . $business_tax .'</span></span>' . ( $atts['oneline'] ? ' ' : '<br/>' ), $details['label'] );
		}
		if( 'coc' == $details['key'] && $atts['show_coc'] && ! empty( $business_coc ) ) {
			$details_output .= sprintf( '<span class="wpseo-vat">%s: ' . $business_coc .'</span>' . ( $atts['oneline'] ? ' ' : '<br/>' ), $details['label'] );
		}
	}

	if( '' != $details_output && true == $atts['oneline'] ) {
		$output .= ' - ';
	}

	$output .= $details_output;

	if ( $atts['show_opening_hours'] ) {
		$args = array(
			'id'          => wpseo_has_multiple_locations() ? $atts['id'] : '',
			'hide_closed' => $atts['hide_closed']
		);
		$output .= '<br/>' . wpseo_local_show_opening_hours( $args, true ) . '<br/>';
	}
	$output .= '</div>';

	if ( $atts['comment'] != '' )
		$output .= '<div class="wpseo-extra-comment">' . wpautop( html_entity_decode( $atts['comment'] ) ) . '</div>';

	if ( $atts['echo'] )
		echo $output;

	return $output;
}

/**
 * Shortcode for shoing all locations at once. May come handy for "office overview" pages
 *
 * @since 1.1.7
 *
 * @param array $atts Array of shortcode parameters
 *
 * @return string
 */
function wpseo_local_show_all_locations( $atts ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'number'             => - 1,
		'term_id'			 => '',
		'orderby'            => 'menu_order title',
		'order'              => 'ASC',
		'show_state'         => true,
		'show_country'       => true,
		'show_phone'         => true,
		'show_phone_2'       => true,
		'show_fax'           => true,
		'show_email'         => true,
		'show_url'           => false,
		'show_opening_hours' => false,
		'hide_closed'        => false,
		'oneline'            => false,
		'echo'               => false
	), $atts ) );

	// Don' show any data when post_type is not activated. This function/shortcode makes no sense for single location
	if ( ! wpseo_has_multiple_locations() )
		return '';

	$output    = '';
	$tax_query = array();

	if( '' != $atts['term_id'] ) {
		$tax_query[] = array(
			'taxonomy' => 'wpseo_locations_category',
			'field' => 'term_id',
			'terms' => $atts['term_id'],
		);
	}

	$locations = new WP_Query( array(
		'post_type'      => 'wpseo_locations',
		'posts_per_page' => $atts['number'],
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
		'fields'		 => 'ids',
		'tax_query'		 => $tax_query,
	) );

	if ( $locations->post_count > 0 ) :
		$output .= '<div class="wpseo-all-locations">';
		foreach( $locations->posts as $location_id ) :
		
			$location = apply_filters( 'wpseo_all_locations_location', wpseo_local_show_address( array(
				'id'                 => $location_id,
				'show_state'         => $atts['show_state'],
				'show_country'       => $atts['show_country'],
				'show_phone'         => $atts['show_phone'],
				'show_phone_2'       => $atts['show_phone_2'],
				'show_fax'           => $atts['show_fax'],
				'show_email'         => $atts['show_email'],
				'show_url'           => $atts['show_url'],
				'show_opening_hours' => $atts['show_opening_hours'],
				'hide_closed'        => $atts['hide_closed'],
				'oneline'            => $atts['oneline'],
				'echo'               => false
			) ) );

			$output .= $location;

		endforeach;

		$output .= '</div>';

	else :
		echo '<p>' . __('There are no locations to show.', 'yoast-local-seo') . '</p>';
	endif;

	if ( $atts['echo'] )
		echo $output;

	return $output;
}

/**
 * Maps shortcode handler
 *
 * @since 0.1
 *
 * @param array $atts Array of shortcode parameters
 *
 * @return string
 */
function wpseo_local_show_map( $atts ) {
	global $map_counter, $wpseo_enqueue_geocoder, $wpseo_map;

	$options = get_option( 'wpseo_local' );
	$tax_query = array();

	// Backwards compatibility for scrollable / zoomable functions
	if( is_array( $atts ) && ! array_key_exists( 'zoomable', $atts ) ) {
		$atts['zoomable'] = isset( $atts['scrollable'] ) ? $atts['scrollable'] : true;
	}

	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'               => '',
		'term_id'		   => '',
		'center'           => '',
		'width'            => 400,
		'height'           => 300,
		'zoom'             => - 1,
		'show_route'       => true,
		'show_state'       => true,
		'show_country'     => false,
		'show_url'         => false,
		'show_email'	   => false,
		'map_style'        => isset( $options['map_view_style'] ) ? $options['map_view_style'] : 'ROADMAP',
		'scrollable'       => true,
		'draggable'        => true,
		'show_route_label' => isset( $options['show_route_label'] ) && ! empty( $options['show_route_label'] ) ? $options['show_route_label'] : __( 'Show route', 'yoast-local-seo' ),
		'from_sl'          => false,
		'echo'             => false
	), $atts ) );

	if ( ! isset( $map_counter ) ) {
		$map_counter = 0;
	}
	else {
		$map_counter ++;
	}

	$location_array     = $lats = $longs = array();
	$location_array_str = '';

	$default_custom_marker = '';
	if( isset( $options['custom_marker'] ) && intval( $options['custom_marker'] ) ) {
		$default_custom_marker = wp_get_attachment_url( $options['custom_marker'] );
	}

	if ( ! wpseo_has_multiple_locations() ) {
		$atts['id'] = '';

		$location_array[] = array(
			'location_name'    	=> $options['location_name'],
			'location_url'     	=> wpseo_xml_sitemaps_base_url( '' ),
			'location_email'    => $options['location_email'],
			'location_address' 	=> $options['location_address'],
			'location_city'    	=> $options['location_city'],
			'location_state'   	=> $options['location_state'],
			'location_zipcode' 	=> $options['location_zipcode'],
			'location_country' 	=> $options['location_country'],
			'location_phone'   	=> $options['location_phone'],
			'location_phone_2nd'=> $options['location_phone_2nd'],
			'coordinates_lat'  	=> $options['location_coords_lat'],
			'coordinates_long' 	=> $options['location_coords_long'],
			'custom_marker' 	=> $default_custom_marker
		);

	}
	else {
		if ( get_post_type() == 'wpseo_locations' ) {
			if ( ( $atts['id'] == '' || $atts['id'] == 'current' ) && ! is_post_type_archive() )
				$atts['id'] = get_queried_object_id();

			if ( is_post_type_archive() && ( $atts['id'] == '' || $atts['id'] == 'current' ) )
				return '';

			if( $atts['id'] == 'all' && $atts['term_id'] != '' ) {
				$tax_query[] = array(
					'taxonomy' => 'wpseo_locations_category',
					'field' => 'term_id',
					'terms' => $atts['term_id'],
				);
			}
		}
		else if ( $atts['id'] != 'all' && empty( $atts['id'] ) ) {
			return is_singular('wpseo_locations') ? __( 'Please provide a post ID when using this shortcode outside a Locations singular page', 'yoast-local-seo' ) : '';
		}

		$location_ids = explode( ',', $atts['id'] );
		if( $atts['id'] == 'all' || ( $atts['id'] != 'all' && count( $location_ids ) > 1 ) ) {
			$args = array(
				'post_type'      => 'wpseo_locations',
				'posts_per_page' => $atts['id'] == 'all' ? -1 : count( $location_ids ),
				'fields' => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_wpseo_business_address',
						'value'   => '',
						'compare' => '!=',
					)
				),
				'tax_query'		  => $tax_query,
			);

			if( count( $location_ids ) > 1 ) {
				$args['post__in'] = $location_ids;
			}

			$location_ids = get_posts( $args );
		}

		foreach( $location_ids as $location_id ) {

			$custom_marker = wpseo_local_get_custom_marker( $location_id, 'wpseo_locations_category' );

			$tmp_array = array(
				'location_name'    	=> get_the_title( $location_id ),
				'location_url'     	=> get_post_meta( $location_id, '_wpseo_business_url', true ),
				'location_email' 	=> get_post_meta( $location_id, '_wpseo_business_email', true ),
				'location_address' 	=> get_post_meta( $location_id, '_wpseo_business_address', true ),
				'location_city'    	=> get_post_meta( $location_id, '_wpseo_business_city', true ),
				'location_state'   	=> get_post_meta( $location_id, '_wpseo_business_state', true ),
				'location_zipcode' 	=> get_post_meta( $location_id, '_wpseo_business_zipcode', true ),
				'location_country' 	=> get_post_meta( $location_id, '_wpseo_business_country', true ),
				'location_phone'   	=> get_post_meta( $location_id, '_wpseo_business_phone', true ),
				'location_phone_2nd'=> get_post_meta( $location_id, '_wpseo_business_phone_2nd', true ),
				'coordinates_lat'  	=> get_post_meta( $location_id, '_wpseo_coordinates_lat', true ),
				'coordinates_long' 	=> get_post_meta( $location_id, '_wpseo_coordinates_long', true ),
				'custom_marker' 	=> $custom_marker != '' ? $custom_marker : $default_custom_marker
			);

			if( empty( $tmp_array['location_url'] ) )
				$tmp_array['location_url'] = get_permalink( $location_id );

			$location_array[] = $tmp_array;
		}

	}

	$noscript_output = '<ul>';
	foreach ( $location_array as $key => $location ) {

		if ( $location['coordinates_lat'] != '' && $location['coordinates_long'] != '' ) {
			$location_array_str .= "location_data.push( {
				'name': '" . wpseo_cleanup_string( $location["location_name"] ) . "',
				'url': '" . wpseo_cleanup_string( $location["location_url"] ) . "',
				'zip_city': '" . wpseo_local_get_address_format( wpseo_cleanup_string( $location["location_address"] ), false, $location["location_zipcode"], $location["location_city"], $location["location_state"], $atts['show_state'], true ) . "',
				'country': '" . WPSEO_Local_Frontend::get_country( $location['location_country'] ) . "',
				'show_country': " . ( $atts['show_country'] ? 'true' : 'false' ) . ",
				'url': '" . esc_url( $location['location_url'] ) . "',
				'show_url': " . ( $atts['show_url'] ? 'true' : 'false' ) . ",
				'email': '" . $location['location_email'] . "',
				'show_email': " . ( $atts['show_email'] ? 'true' : 'false' ) . ",
				'phone': '" . wpseo_cleanup_string( $location['location_phone'] ) . "',
				'phone_2nd': '" . wpseo_cleanup_string( $location['location_phone_2nd'] ) . "',
				'lat': " . wpseo_cleanup_string( $location['coordinates_lat'] ) . ",
				'long': " . wpseo_cleanup_string( $location['coordinates_long'] ) . ",
				'custom_marker': '" . wpseo_cleanup_string( $location['custom_marker'] ) . "'
			} );\n";
		}

		$noscript_output .= '<li><a href="' . $location['location_url'] . '">' . $location['location_name'] . '</a></li>';
		$noscript_output .= '<li><a href="mailto:' . $location['location_email'] . '">' . $location['location_email'] . '</a></li>';

		$full_address = $location['location_address'] . ', ' . $location['location_city'] . ( strtolower( $location['location_country'] ) == 'us' ? ', ' . $location['location_state'] : '' ) . ', ' . $location['location_zipcode'] . ', ' . WPSEO_Local_Frontend::get_country( $location['location_country'] );

		$location_array[$key]['full_address'] = $full_address;

		$lats[]  = $location['coordinates_lat'];
		$longs[] = $location['coordinates_long'];
	}
	$noscript_output .= '</ul>';

	$map                    = '';
	$wpseo_enqueue_geocoder = true;

	if( ! is_array( $lats ) || empty( $lats ) || ! is_array( $longs ) || empty( $longs ) ) {
		return;
	}

	if( $atts['center'] === '' ) {
		$center_lat  = min( $lats ) + ( ( max( $lats ) - min( $lats ) ) / 2 );
		$center_long = min( $longs ) + ( ( max( $longs ) - min( $longs ) ) / 2 );
	} else {
		$center_lat = get_post_meta( $atts['center'], '_wpseo_coordinates_lat', true );
		$center_long = get_post_meta( $atts['center'], '_wpseo_coordinates_long', true );
	}

	// Default to zoom 10 if there's only one location as a center + bounds would zoom in far too much.
	if ( - 1 == $atts['zoom'] && 1 === count( $location_array ) )
		$atts['zoom'] = 10;

	if ( $location_array_str != '' ) {
		$wpseo_map .= '<script type="text/javascript">
			var map_' . $map_counter . ';
			var directionsDisplay_' . $map_counter . ';
			function wpseo_map_init' . ( $map_counter != 0 ? '_' . $map_counter : '' ) . '() {
				var location_data = new Array();' . PHP_EOL .
				$location_array_str . '
				directionsDisplay_' . $map_counter . ' = wpseo_show_map( location_data, ' . $map_counter . ', directionsDisplay_' . $map_counter . ', ' . $center_lat . ', ' . $center_long . ', ' . $atts['zoom'] . ', "' . $atts['map_style'] . '", "' . $atts['show_route'] . '", "' . $atts['scrollable'] . '", "' . $atts['draggable'] . '" );
			}

			if( window.addEventListener )
				window.addEventListener( "load", wpseo_map_init' . ( $map_counter != 0 ? '_' . $map_counter : '' ) . ', false );
			else if(window.attachEvent )
				window.attachEvent( "onload", wpseo_map_init' . ( $map_counter != 0 ? '_' . $map_counter : '' ) . ');
		</script>' . PHP_EOL;

		// Override(reset) the setting for images inside the map
		$map .= '<div id="map_canvas' . ( $map_counter != 0 ? '_' . $map_counter : '' ) . '" class="wpseo-map-canvas" style="max-width: 100%; width: ' . $atts['width'] . 'px; height: ' . $atts['height'] . 'px;">' . $noscript_output . '</div>';

		$route_tag = apply_filters( 'wpseo_local_location_route_title_name', 'h3' );

		if ( $atts['show_route'] && ( ( $atts['id'] != 'all' && strpos( $atts['id'], ',' ) === false ) || $atts['from_sl'] ) ) {
			$map .= '<div id="wpseo-directions-wrapper"' . ( $atts['from_sl'] ? ' style="display: none;"' : '' ) . '>';
			$map .= '<' . esc_html( $route_tag ) . ' id="wpseo-directions" class="wpseo-directions-heading">' . __( 'Route', 'yoast-local-seo' ) . '</' . esc_html( $route_tag ) . '>';
			$map .= '<form action="" method="post" class="wpseo-directions-form" id="wpseo-directions-form' . ( $map_counter != 0 ? '_' . $map_counter : '' ) . '" onsubmit="wpseo_calculate_route( directionsDisplay_' . $map_counter . ', ' . $location_array[0]['coordinates_lat'] . ', ' . $location_array[0]['coordinates_long'] . ', ' . $map_counter . '); return false;">';
			$map .= '<p>';
			$map .= __( 'Your location', 'yoast-local-seo' ) . ': <input type="text" size="20" id="origin' . ( $map_counter != 0 ? '_' . $map_counter : '' ) . '" value="' . ( !empty( $_REQUEST['wpseo-sl-search'] ) ? esc_attr( $_REQUEST['wpseo-sl-search'] ) : '' ) . '" />';
			$map .= '<input type="submit" class="wpseo-directions-submit" value="' . $atts['show_route_label'] . '">';
			$map .= '<span id="wpseo-noroute" style="display: none;">' . __('No route could be calculated.', 'yoast-local-seo') . '</span>';
			$map .= '</p>';
			$map .= '</form>';
			$map .= '<div id="directions' . ( $map_counter != 0 ? '_' . $map_counter : '' ) . '"></div>';
			$map .= '</div>';
		}
	}

	if ( $atts['echo'] )
		echo $map;

	return $map;
}

/**
 * Opening hours shortcode handler
 *
 * @since 0.1
 *
 * @param array $atts        Array of shortcode parameters
 *
 * @param bool  $show_schema choose to show schema.org HTMl or not
 *
 * @return string
 */
function wpseo_local_show_opening_hours( $atts, $show_schema = true ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'          => '',
		'hide_closed' => false,
		'echo'        => false,
		'comment'     => ''
	), $atts ) );

	$options = get_option('wpseo_local');
	if( isset( $options['hide_opening_hours'] ) && $options['hide_opening_hours'] == 'on' ) {
		return false;
	}

	if ( wpseo_has_multiple_locations() ) {
		if ( get_post_type() == 'wpseo_locations' ) {
			if ( ( $atts['id'] == '' || $atts['id'] == 'current' ) && ! is_post_type_archive() )
				$atts['id'] = get_queried_object_id();

			if ( is_post_type_archive() && ( $atts['id'] == '' || $atts['id'] == 'current' ) )
				return '';
		}
	}
	else {
		$atts['id'] = '';
	}

	$options = get_option( 'wpseo_local' );

	$days = array(
		'monday'    => __( 'Monday', 'yoast-local-seo' ),
		'tuesday'   => __( 'Tuesday', 'yoast-local-seo' ),
		'wednesday' => __( 'Wednesday', 'yoast-local-seo' ),
		'thursday'  => __( 'Thursday', 'yoast-local-seo' ),
		'friday'    => __( 'Friday', 'yoast-local-seo' ),
		'saturday'  => __( 'Saturday', 'yoast-local-seo' ),
		'sunday'    => __( 'Sunday', 'yoast-local-seo' )
	);

	$output = '<table class="wpseo-opening-hours">';

	foreach ( $days as $key => $day ) {
		$multiple_opening_hours = isset( $options['multiple_opening_hours'] ) && $options['multiple_opening_hours'] == 'on';
		$day_abbr               = ucfirst( substr( $key, 0, 2 ) );

		if ( wpseo_has_multiple_locations() ) {
			$field_name        = '_wpseo_opening_hours_' . $key;
			$value_from        = get_post_meta( $atts['id'], $field_name . '_from', true );
			$value_to          = get_post_meta( $atts['id'], $field_name . '_to', true );
			$value_second_from = get_post_meta( $atts['id'], $field_name . '_second_from', true );
			$value_second_to   = get_post_meta( $atts['id'], $field_name . '_second_to', true );

			$multiple_opening_hours = get_post_meta( $atts['id'], '_wpseo_multiple_opening_hours', true );
			$multiple_opening_hours = ! empty( $multiple_opening_hours );
		}
		else {
			$field_name        = 'opening_hours_' . $key;
			$value_from        = isset( $options[$field_name . '_from'] ) ? esc_attr( $options[$field_name . '_from'] ) : '';
			$value_to          = isset( $options[$field_name . '_to'] ) ? esc_attr( $options[$field_name . '_to'] ) : '';
			$value_second_from = isset( $options[$field_name . '_second_from'] ) ? esc_attr( $options[$field_name . '_second_from'] ) : '';
			$value_second_to   = isset( $options[$field_name . '_second_to'] ) ? esc_attr( $options[$field_name . '_second_to'] ) : '';
		}

		if ( $value_from == 'closed' && $atts['hide_closed'] ) {
			continue;
		}

		$value_from_formatted        = $value_from;
		$value_to_formatted          = $value_to;
		$value_second_from_formatted = $value_second_from;
		$value_second_to_formatted   = $value_second_to;

		if ( ! isset( $options['opening_hours_24h'] ) || $options['opening_hours_24h'] != 'on' ) {
			$value_from_formatted        = date( 'g:i A', strtotime( $value_from ) );
			$value_to_formatted          = date( 'g:i A', strtotime( $value_to ) );
			$value_second_from_formatted = date( 'g:i A', strtotime( $value_second_from ) );
			$value_second_to_formatted   = date( 'g:i A', strtotime( $value_second_to ) );
		}

		$output .= '<tr>';
		$output .= '<td class="day">' . $day . '&nbsp;</td>';
		$output .= '<td class="time">';

		$output_time = '';
		if ( $value_from != 'closed' && $value_to != 'closed' )
			$output_time .= '<time ' . ( $show_schema ? 'itemprop="openingHours"' : '' ) . ' content="' . $day_abbr . ' ' . $value_from . '-' . $value_to . '">' . $value_from_formatted . ' - ' . $value_to_formatted . '</time>';
		else
			$output_time .= __( 'Closed', 'yoast-local-seo' );

		if ( $multiple_opening_hours ) {
			if ( $value_from != 'closed' && $value_to != 'closed' && $value_second_from != 'closed' && $value_second_to != 'closed' ) {
				$output_time .= '<span class="openingHoursAnd"> ' . __( 'and', 'yoast-local-seo' ) . ' </span> ';
				$output_time .= '<time ' . ( $show_schema ? 'itemprop="openingHours"' : '' ) . ' content="' . $day_abbr . ' ' . $value_second_from . '-' . $value_second_to . '">' . $value_second_from_formatted . ' - ' . $value_second_to_formatted . '</time>';
			}
			else {
				$output_time .= '';
			}
		}

		$output_time = apply_filters( 'wpseo_opening_hours_time', $output_time, $day, $value_from, $value_to );
		$output .= $output_time;
		$output .= '</td>';
		$output .= '</tr>';
	}

	$output .= '</table>';

	if ( $atts['comment'] != '' )
		$output .= '<div class="wpseo-extra-comment">' . wpautop( html_entity_decode( $atts['comment'] ) ) . '</div>';

	if ( $atts['echo'] )
		echo $output;

	return $output;
}

/**
 * Get the location details
 *
 * @param string $location_id Optional. Only use this when multiple locations are enabled in the website
 *
 * @return array|bool Array with location details.
 */
function wpseo_get_location_details( $location_id = '' ) {
	$options          = get_option( 'wpseo_local' );
	$location_details = array();

	if ( wpseo_has_multiple_locations() && $location_id == '' ) {
		return false;
	}
	else if ( wpseo_has_multiple_locations() ) {
		if ( $location_id == null ) {
			return false;
		}

		$location_details = array(
			'business_address'     => get_post_meta( $location_id, '_wpseo_business_address', true ),
			'business_city'        => get_post_meta( $location_id, '_wpseo_business_city', true ),
			'business_state'       => get_post_meta( $location_id, '_wpseo_business_state', true ),
			'business_zipcode'     => get_post_meta( $location_id, '_wpseo_business_zipcode', true ),
			'business_country'     => get_post_meta( $location_id, '_wpseo_business_country', true ),
			'business_phone'       => get_post_meta( $location_id, '_wpseo_business_phone', true ),
			'business_phone_2nd'   => get_post_meta( $location_id, '_wpseo_business_phone_2nd', true ),
			'business_coords_lat'  => get_post_meta( $location_id, '_wpseo_coordinates_lat', true ),
			'business_coords_long' => get_post_meta( $location_id, '_wpseo_coordinates_long', true )
		);
	}
	else if ( wpseo_has_multiple_locations() ) {
		$location_details = array(
			'business_address'     => $options['location_address'],
			'business_city'        => $options['location_city'],
			'business_state'       => $options['location_state'],
			'business_zipcode'     => $options['location_zipcode'],
			'business_country'     => $options['location_country'],
			'business_phone'       => $options['location_phone'],
			'business_phone_2nd'   => isset( $options['location_phone_2nd'] ) ? $options['location_phone_2nd'] : '',
			'business_coords_lat'  => $options['location_coords_lat'],
			'business_coords_long' => $options['location_coords_long']
		);
	}

	return $location_details;
}

/**
 * Checks whether website uses multiple location (Custom Post Type) or not (info from options)
 *
 * @return bool Multiple locations enbaled or not
 */
function wpseo_has_multiple_locations() {
	$options = get_option( 'wpseo_local' );

	return isset( $options['use_multiple_locations'] ) && $options['use_multiple_locations'] == 'on';
}

/**
 * @param bool $use_24h True if time should be displayed in 24 hours. False if time should be displayed in AM/PM mode.
 * @param int  $default default time for dropdown
 *
 * @return string Complete dropdown with all options
 */
function wpseo_show_hour_options( $use_24h = false, $default = 9 ) {
	$output = '<option value="closed">' . __( 'Closed', 'yoast-local-seo' ) . '</option>';

	for ( $i = 0; $i < 24; $i ++ ) {
		$time                = strtotime( sprintf( '%1$02d', $i ) . ':00' );
		$time_quarter        = strtotime( sprintf( '%1$02d', $i ) . ':15' );
		$time_half           = strtotime( sprintf( '%1$02d', $i ) . ':30' );
		$time_threequarters  = strtotime( sprintf( '%1$02d', $i ) . ':45' );
		$value               = date( 'H:i', $time );
		$value_quarter       = date( 'H:i', $time_quarter );
		$value_half          = date( 'H:i', $time_half );
		$value_threequarters = date( 'H:i', $time_threequarters );

		$time_value               = date( 'g:i A', $time );
		$time_quarter_value       = date( 'g:i A', $time_quarter );
		$time_half_value          = date( 'g:i A', $time_half );
		$time_threequarters_value = date( 'g:i A', $time_threequarters );

		if ( $use_24h ) {
			$time_value               = date( 'H:i', $time );
			$time_quarter_value       = date( 'H:i', $time_quarter );
			$time_half_value          = date( 'H:i', $time_half );
			$time_threequarters_value = date( 'H:i', $time_threequarters );
		}

		$output .= '<option value="' . $value . '"' . selected( $value, $default, false ) . '>' . $time_value . '</option>';
		$output .= '<option value="' . $value_quarter . '" ' . selected( $value_quarter, $default, false ) . '>' . $time_quarter_value . '</option>';
		$output .= '<option value="' . $value_half . '" ' . selected( $value_half, $default, false ) . '>' . $time_half_value . '</option>';
		$output .= '<option value="' . $value_threequarters . '" ' . selected( $value_threequarters, $default, false ) . '>' . $time_threequarters_value . '</option>';
	}

	return $output;
}


/**
 * @param string $business_zipcode
 * @param string $business_city
 * @param string $business_state
 * @param bool   $show_state
 * @param bool   $escape_output
 * @param bool   $use_tags
 *
 * @return string
 */
function wpseo_local_get_address_format( $business_address = '', $oneline = false, $business_zipcode = '', $business_city = '', $business_state = '', $show_state = false, $escape_output = false, $use_tags = true ) {
	$output               = '';
	$options              = get_option( 'wpseo_local' );
	$address_format       = ! empty( $options['address_format'] ) ? $options['address_format'] : 'address-state-postal';
	$business_city_string = $business_city;
	if ( $use_tags )
		$business_city_string = '<span class="locality" itemprop="addressLocality"> ' . $business_city . '</span>';
	$business_state_string = $business_state;
	if ( $use_tags )
		$business_state_string = '<span  class="region" itemprop="addressRegion">' . $business_state . '</span>';
	$business_zipcode_string = $business_zipcode;
	if ( $use_tags )
		$business_zipcode_string = '<span class="postal-code" itemprop="postalCode">' . $business_zipcode . '</span>';

	if ( in_array( $address_format, array( '', 'address-state-postal', 'address-state-postal-comma', 'address-postal-city-state', 'address-postal', 'address-postal-comma', 'address-city' ) ) ) {
		if( $use_tags ) {
			$output .= '<' . ( $oneline	 ? 'span' : 'div' ) . ' class="street-address" itemprop="streetAddress">' . $business_address . '</' . ( $oneline	 ? 'span' : 'div' ) . '>' . ( $oneline	 ? ', ' : '' );
		} else {
			$output .= $business_address . ' ';
		}

		if ( $address_format == 'address-postal-city-state' && ! empty( $business_zipcode ) ) {
			$output .= $business_zipcode_string . ' ';
		}

		if ( ! empty( $business_city ) ) {
			$output .= $business_city_string;

			if ( in_array( $address_format, array( 'address-state-postal', 'address-state-postal-comma', 'address-postal-city-state' ) ) ) {
				$output .= ', ';
			}
			else if ( $address_format != 'address-postal-comma' ) {
				$output .= ' ';
			}
		}

		if ( in_array( $address_format, array( 'address-state-postal', 'address-state-postal-comma', 'address-postal-city-state' ) ) ) {
			if ( $show_state && ! empty( $business_state ) ) {
				$output .= $business_state_string;
				$output .= $address_format != 'address-state-postal-comma' ? ' ' : '';
			}
		}

		if ( ! empty( $business_zipcode ) && $address_format != 'address-postal-city-state'  && $address_format != 'address-city' ) {
			if ( ( $address_format == 'address-state-postal-comma' || $address_format == 'address-postal-comma' ) && false !== $show_state ) {
				$output .= ', ';
			}
			$output .= $business_zipcode_string;
		}
	}
	else {
		if ( ! empty( $business_zipcode ) ) {
			$output .= $business_zipcode_string;
			$output .= ( $oneline ? ', ' : ' ' );
		}
		if ( $show_state && ! empty( $business_state ) ) {
			$output .= ' ' . $business_state_string;
			$output .= ( $oneline ? ', ' : ' ' );
		}
		if ( ! empty( $business_city ) ) {
			$output .= ' ' . $business_city_string;
		}
		if( $use_tags ) {
			$output .= ( $oneline ? ', ' : ' ' );
			$output .= '<' . ( $oneline	 ? 'span' : 'div' ) . ' class="street-address" itemprop="streetAddress">' . $business_address . '</' . ( $oneline	 ? 'span' : 'div' ) . '>' . ( $oneline	 ? ', ' : '' );
		} else {
			$output .= ', ' . $business_address;
		}
	}

	if ( $escape_output )
		$output = addslashes( $output );

	return $output;
}

/**
 * Geocode the given address
 *
 * @param string $address
 *
 * @return array|WP_Error
 */
function wpseo_geocode_address( $address ) {
	$geocode_url = "https://maps.google.com/maps/api/geocode/json?address=" . urlencode( $address ) . "&oe=utf8&sensor=false";
	$options  = get_option( "wpseo_local" );
	if( !empty( $options['api_key'] ) ) {
		$geocode_url.= "&key=" . $options['api_key'];
	}

	$response = wp_remote_get( $geocode_url );

	if ( is_wp_error( $response ) || $response['response']['code'] != 200 || empty( $response['body'] ) )
		return new WP_Error( 'wpseo-no-response', "Didn't receive a response from Maps API" );

	$response_body = json_decode( $response['body'] );
	
	if ( "OK" != $response_body->status ) {
		$error_code = 'wpseo-zero-results';
		if( $response_body->status == 'OVER_QUERY_LIMIT' )
			$error_code = 'wpseo-query-limit';

		return new WP_Error( $error_code, $response_body->status );
	}

	return $response_body;
}

/**
 * Checks whether array keys are meant to mean false but aren't set to false.
 *
 * @param $atts array Array to check
 *
 * @return array
 */
function wpseo_check_falses( $atts ) {
	if ( ! is_array( $atts ) )
		return $atts;

	foreach ( $atts as $key => $value ) {
		if ( $value === 'false' || $value === 'off' || $value === 'no' || $value === '0' ) {
			$atts[$key] = false;
		}
		else if ( $value === 'true' || $value === 'on' || $value === 'yes' || $value === '1' ) {
			$atts[$key] = true;
		}
	}

	return $atts;
}

// Set the global to false, if the script is needed, the global will be set to true.
$wpseo_enqueue_geocoder = false;
/**
 * Places scripts in footer for Google Maps use.
 */
function wpseo_enqueue_geocoder() {
	global $wpseo_enqueue_geocoder, $wpseo_map;

	if( is_admin() && 'wpseo_locations' == get_post_type() ) {
		global $wpseo_enqueue_geocoder;

		$wpseo_enqueue_geocoder = true;
	}

	if ( $wpseo_enqueue_geocoder ) {
		$locale   = get_locale();
		$locale   = explode( '_', $locale );
		$language = isset( $locale[1] ) ? $locale[1] : $locale[0];
		$options  = get_option( "wpseo_local" );

		wp_enqueue_script( 'maps-geocoder', '//maps.google.com/maps/api/js?sensor=false' . ( ! empty( $language ) ? '&language=' . strtolower( $language ) : '' ), array(), null, true );

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			wp_enqueue_script( 'wpseo-local-frontend', plugins_url( 'js/wp-seo-local-frontend.js', dirname( __FILE__ ) ), '', WPSEO_LOCAL_VERSION, true );
		} else {
			wp_enqueue_script( 'wpseo-local-frontend', plugins_url( 'js/wp-seo-local-frontend.min.js', dirname( __FILE__ ) ), '', WPSEO_LOCAL_VERSION, true );
		}

		wp_localize_script( 'wpseo-local-frontend', 'wpseo_local_data', array(
			'ajaxurl' => 'admin-ajax.php',
			'has_multiple_locations' => wpseo_has_multiple_locations(),
			'unit_system'            => ! empty( $options['unit_system'] ) ? $options['unit_system'] : 'METRIC'
		) );

		echo '<style type="text/css">.wpseo-map-canvas img { max-width: none !important; }</style>' . PHP_EOL;
	}

	echo $wpseo_map;
}

add_action( 'wp_footer', 'wpseo_enqueue_geocoder' );
add_action( 'admin_footer', 'wpseo_enqueue_geocoder' );

/**
 * This function will clean up the given string and remove all unwanted characters
 *
 * @param $string String that has to be cleaned
 *
 * @uses wpseo_utf8_to_unicode() to convert string to array of unicode characters
 * @uses wpseo_unicode_to_utf8() to convert the unicode array back to a regular string
 * @return string The clean string
 */
function wpseo_cleanup_string( $string ) {
	$string = esc_attr( $string );

	// First generate array of all unicodes of this string
	$unicode_array = wpseo_utf8_to_unicode( $string );
	foreach ( $unicode_array as $key => $unicode_item ) {
		// Remove unwanted unicode characters
		if ( in_array( $unicode_item, array( 8232 ) ) )
			unset( $unicode_array[$key] );
	}

	// Revert back to normal string
	$string = wpseo_unicode_to_utf8( $unicode_array );

	return $string;
}

/**
 * Converts a string to array of unicode characters
 *
 * @param $str String that has to be converted to unicde array
 *
 * @return array Array of unicode characters
 */
function wpseo_utf8_to_unicode( $str ) {
	$unicode     = array();
	$values      = array();
	$looking_for = 1;

	for ( $i = 0; $i < strlen( $str ); $i ++ ) {
		$this_value = ord( $str[$i] );

		if ( $this_value < 128 ) {
			$unicode[] = $this_value;
		}
		else {
			if ( count( $values ) == 0 )
				$looking_for = ( $this_value < 224 ) ? 2 : 3;

			$values[] = $this_value;
			if ( count( $values ) == $looking_for ) {
				$number = ( $looking_for == 3 ) ?
						( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ) :
						( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );

				$unicode[]   = $number;
				$values      = array();
				$looking_for = 1;
			}
		}
	}

	return $unicode;
}

/**
 * Converts unicode character array back to regular string
 *
 * @param $string_array Array of unicode characters
 *
 * @return string Converted string
 */
function wpseo_unicode_to_utf8( $string_array ) {
	$utf8 = '';

	foreach ( $string_array as $unicode ) {
		if ( $unicode < 128 ) {
			$utf8 .= chr( $unicode );
		}
		elseif ( $unicode < 2048 ) {
			$utf8 .= chr( 192 + ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
			$utf8 .= chr( 128 + ( $unicode % 64 ) );
		}
		else {
			$utf8 .= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
			$utf8 .= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
			$utf8 .= chr( 128 + ( $unicode % 64 ) );
		}
	}

	return $utf8;
}


/**
 * Run the upgrade procedures.
 */
function wpseo_local_do_upgrade( $db_version ) {
	
	if ( version_compare( $db_version, '1.3.1', '<' ) ) {
		$options = get_option( 'wpseo_local' );

		if( ! is_array( $options ) ) {
			return;
		}

		// Convert checkbox values from "1" to "on"
		foreach( $options as $key => $value ) {
			if( ! in_array( $key, array( 'use_multiple_locations', 'opening_hours_24h', 'multiple_opening_hours' ) ) ) {
				continue;
			}

			if( $value == '1' ) {
				$options[ $key ] = 'on';
			}
		}

		// Update current version in database
		$options['version'] = WPSEO_LOCAL_VERSION;

		update_option( 'wpseo_local', $options );
	}

}

/**
 * Retrieves excerpt from specific post
 */
function wpseo_local_get_excerpt( $post_id ) {
	global $post;  

	$original_post = $post;
	$post = get_post( $post_id );
	setup_postdata( $post );
	
	$output = get_the_excerpt();

	// Set back original $post;
	$post = $original_post;
	wp_reset_postdata();

	return $output;
}

/**
 * Create an upload field for an image
 */
function wpseo_local_upload_image() {

	$output = '';

	$output = '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'If you want the map to display a custom marker pin for your locations, please upload it here.', 'yoast-local-seo' ) . '</p>';

	$output .= '<label for="upload_image">';
    $output .= '<input id="upload_image" type="text" size="36" name="ad_image" value="http://" /> ';
    $output .= '<input id="upload_image_button" class="button" type="button" value="Upload Image" />';
    $output .= '<br />Enter a URL or upload an image';
	$output .= '</label>';
	$output .= '<br class="clear"/>';

	return $output;
}


/**
 * Get the custom marker from categories or general Local SEO settings.
 *
 * @param $post_id Int the post id
 * @param $taxonomy String The taxonomy name from the location category
 */
function wpseo_local_get_custom_marker( $post_id = null, $taxonomy = '' ) {

	$custom_marker = '';

	if( !empty( $post_id ) && ! empty( $taxonomy ) ) {
		$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		$terms = apply_filters( 'wpseo_local_custom_marker_order', $terms );

		if( ! empty( $terms ) ) {
			foreach( $terms as $term_id ) {
				$tax_meta = WPSEO_Taxonomy_Meta::get_term_meta( (int) $term_id, $taxonomy );

				if( isset( $tax_meta['wpseo_local_custom_marker'] ) && ! empty( $tax_meta['wpseo_local_custom_marker'] ) ) {
					$custom_marker = wp_get_attachment_url( $tax_meta['wpseo_local_custom_marker'] );
				}

				break;
			}
		}
	}
	else {
		$options = get_option( 'wpseo_local' );
		if( isset( $options['custom_marker'] ) && intval( $options['custom_marker'] ) ) {
			$custom_marker = wp_get_attachment_url( $options['custom_marker'] );
		}		
	}

	return $custom_marker;
}

function wpseo_local_sanitize_business_types( &$value, &$key ) {
	$value = str_replace('&mdash;', '', $value );
	$value = trim( $value );
}
