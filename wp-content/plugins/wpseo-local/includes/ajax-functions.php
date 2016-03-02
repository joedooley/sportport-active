<?php

function wpseo_copy_location_callback() {

	$days = array(
		'monday'    => __( 'Monday', 'yoast-local-seo' ),
		'tuesday'   => __( 'Tuesday', 'yoast-local-seo' ),
		'wednesday' => __( 'Wednesday', 'yoast-local-seo' ),
		'thursday'  => __( 'Thursday', 'yoast-local-seo' ),
		'friday'    => __( 'Friday', 'yoast-local-seo' ),
		'saturday'  => __( 'Saturday', 'yoast-local-seo' ),
		'sunday'    => __( 'Sunday', 'yoast-local-seo' ),
	);
	$ret_array = array(
		'success' => true,
		'location' => array()
	);
	
	check_ajax_referer( 'wpseo-local-secnonce', 'security', false );

	if( empty( $_POST['location_id'] ) )
		return $ret_array;

	$location_id = absint( $_POST['location_id'] );

	$location = array(
		'business_type' => get_post_meta( $location_id, '_wpseo_business_type', true ),
		'business_address' => get_post_meta( $location_id, '_wpseo_business_address', true ),
		'business_city' => get_post_meta( $location_id, '_wpseo_business_city', true ),
		'business_state' => get_post_meta( $location_id, '_wpseo_business_state', true ),
		'business_zipcode' => get_post_meta( $location_id, '_wpseo_business_zipcode', true ),
		'business_country' => get_post_meta( $location_id, '_wpseo_business_country', true ),
		'business_phone' => get_post_meta( $location_id, '_wpseo_business_phone', true ),
		'business_phone_2nd' => get_post_meta( $location_id, '_wpseo_business_phone_2nd', true ),
		'business_fax' => get_post_meta( $location_id, '_wpseo_business_fax', true ),
		'business_email' => get_post_meta( $location_id, '_wpseo_business_email', true ),
		'coordinates_lat' => get_post_meta( $location_id, '_wpseo_coordinates_lat', true ),
		'coordinates_long' => get_post_meta( $location_id, '_wpseo_coordinates_long', true ),
		'is_postal_address' => get_post_meta( $location_id, '_wpseo_is_postal_address', true ),
		'multiple_opening_hours' => get_post_meta( $location_id, '_wpseo_multiple_opening_hours', true ),
	);

	foreach ( $days as $key => $day ) {
		$field_name = '_wpseo_opening_hours_' . $key;
		$value_from = get_post_meta( $location_id, $field_name . '_from', true );
		if ( !$value_from )
			$value_from = '09:00';
		$value_to = get_post_meta( $location_id, $field_name . '_to', true );
		if ( !$value_to )
			$value_to = '17:00';
		$value_second_from = get_post_meta( $location_id, $field_name . '_second_from', true );
		if ( !$value_second_from )
			$value_second_from = '09:00';
		$value_second_to = get_post_meta( $location_id, $field_name . '_second_to', true );
		if ( !$value_second_to )
			$value_second_to = '17:00';

		$location[ $field_name . '_from'] = $value_from;
		$location[ $field_name . '_to'] = $value_to;
		$location[ $field_name . '_second_from'] = $value_second_from;
		$location[ $field_name . '_second_to'] = $value_second_to;
	}

	$ret_array['location'] = $location;

	echo json_encode( $ret_array );
	
	return false;
	exit;
}
add_action('wp_ajax_wpseo_copy_location', 'wpseo_copy_location_callback');
add_action('wp_ajax_nopriv_wpseo_copy_location', 'wpseo_copy_location_callback');

?>