<?php

if ( ! defined( 'WPSEO_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
/** @var WPSEO_Local_Core */
global $wpseo_local_core;

$options = get_option( 'wpseo_local' );
$hide_tools = isset( $options['hide_tools_section'] ) && $options['hide_tools_section'] == 'on';

WPSEO_Local_Admin_Wrappers::admin_header( true, 'yoast_wpseo_local_options', 'wpseo_local' );

?>
<?php if( false == $hide_tools ) { ?>
<h2 class="nav-tab-wrapper" id="wpseo-tabs">
	<a class="nav-tab" id="general-tab" href="#top#general"><?php _e('General settings', 'yoast-local-seo'); ?></a>
	<a class="nav-tab" id="tools-tab" href="#top#tools"><?php _e('Local SEO tools', 'yoast-local-seo'); ?></a>
</h2>
<?php } ?>

<div id="general" class="wpseotab active">
	<div id="local" class="yoastbox">
	<h2> <?php echo __( 'Local SEO settings', 'yoast-local-seo' ); ?></h2>
<?php

echo '<div id="select-multiple-locations" style="">' . __( 'If you have more than one location, you can enable this feature. Yoast SEO will create a new Custom Post Type for you where you can manage your locations. If it\'s not enabled you can enter your address details below. These fields will be ignored when you enable this option.', 'yoast-local-seo' ) . '<br>';
WPSEO_Local_Admin_Wrappers::checkbox( 'use_multiple_locations', '', __( 'Use multiple locations', 'yoast-local-seo' ) );
echo '</div>';

echo '<div id="show-single-location" style="clear: both; ' . ( wpseo_has_multiple_locations() ? 'display: none;' : '' ) . '">';
WPSEO_Local_Admin_Wrappers::textinput( 'location_name', __( 'Business name', 'yoast-local-seo' ) );

WPSEO_Local_Admin_Wrappers::select( 'business_type', __( 'Business type:', 'yoast-local-seo' ), $wpseo_local_core->get_local_business_types() );
echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . sprintf( __( 'If your business type is not listed, please read %sthe FAQ entry%s.', 'yoast-local-seo' ), '<a href="http://kb.yoast.com/article/49-my-business-is-not-listed-can-you-add-it" target="_blank">', '</a>' ) . '</p>';

WPSEO_Local_Admin_Wrappers::textinput( 'location_address', __( 'Business address', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_city', __( 'Business city', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_state', __( 'Business state', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_zipcode', __( 'Business zipcode', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::select( 'location_country', __( 'Business country', 'yoast-local-seo' ) . ':', WPSEO_Local_Frontend::get_country_array() );
WPSEO_Local_Admin_Wrappers::textinput( 'location_phone', __( 'Business phone', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_phone_2nd', __( '2nd Business phone', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_fax', __( 'Business fax', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_email', __( 'Business email', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_url', __( 'URL', 'yoast-local-seo' ), '', array( 'placeholder' => wpseo_xml_sitemaps_base_url( '' ) ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_vat_id', __( 'VAT ID', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_tax_id', __( 'Tax ID', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_coc_id', __( 'Chamber of Commerce ID', 'yoast-local-seo' ) );

// Calculate lat/long coordinates when address is entered.
if ( empty( $options['location_coords_lat'] ) || empty( $options['location_coords_long']  ) ) {
	$location_coordinates = $wpseo_local_core->get_geo_data( array(
		'_wpseo_business_address' => isset( $options['location_address'] ) ? esc_attr( $options['location_address'] ) : '',
		'_wpseo_business_city'    => isset( $options['location_city'] ) ? esc_attr( $options['location_city'] ) : '',
		'_wpseo_business_state'   => isset( $options['location_state'] ) ? esc_attr( $options['location_state'] ) : '',
		'_wpseo_business_zipcode' => isset( $options['location_zipcode'] ) ? esc_attr( $options['location_zipcode'] ) : '',
		'_wpseo_business_country' => isset( $options['location_country'] ) ? esc_attr( $options['location_country'] ) : ''
	), true );
	if ( !empty( $location_coordinates['coords'] ) ) {
		$options['location_coords_lat']  = $location_coordinates['coords']['lat'];
		$options['location_coords_long'] = $location_coordinates['coords']['long'];
		update_option( 'wpseo_local', $options );
	}
}

echo '<p>' . __( 'You can enter the lat/long coordinates yourself. If you leave them empty they will be calculated automatically. If you want to re-calculate these fields, please make them blank before saving this location.', 'yoast-local-seo') . '</p>';
WPSEO_Local_Admin_Wrappers::textinput( 'location_coords_lat', __( 'Latitude', 'yoast-local-seo' ) );
WPSEO_Local_Admin_Wrappers::textinput( 'location_coords_long', __( 'Longitude', 'yoast-local-seo' ) );
echo '</div><!-- #show-single-location -->';

echo '<div id="show-multiple-locations" style="clear: both; ' . ( wpseo_has_multiple_locations() ? '' : 'display: none;' ) . '">';
WPSEO_Local_Admin_Wrappers::textinput( 'locations_slug', __( 'Locations slug', 'yoast-local-seo' ) );
echo '<p class="desc label" style="border: 0; margin-bottom: 0; padding-bottom: 0;">' . __( 'The slug for your location pages. Default slug is <code>locations</code>.', 'yoast-local-seo' ) . '<br>';
echo '<a href="' . get_post_type_archive_link( 'wpseo_locations' ) . '" target="_blank">' . __( 'View them all', 'yoast-local-seo' ) . '</a> ' . __( 'or', 'yoast-local-seo' ) . ' <a href="' . admin_url( 'edit.php?post_type=wpseo_locations' ) . '">' . __( 'edit them', 'yoast-local-seo' ) . '</a>';
echo '</p>';
WPSEO_Local_Admin_Wrappers::textinput( 'locations_label_singular', __( 'Locations label singular', 'yoast-local-seo' ) );
echo '<p class="desc label" style="border: 0; margin-bottom: 0; padding-bottom: 0;">' . __( 'The singular label for your location pages. Default label is <code>Location</code>.', 'yoast-local-seo' ) . '<br>';
echo '</p>';
WPSEO_Local_Admin_Wrappers::textinput( 'locations_label_plural', __( 'Locations label plural', 'yoast-local-seo' ) );
echo '<p class="desc label" style="border: 0; margin-bottom: 0; padding-bottom: 0;">' . __( 'The plural label for your location pages. Default label is <code>Locations</code>.', 'yoast-local-seo' ) . '<br>';
echo '</p>';
WPSEO_Local_Admin_Wrappers::textinput( 'locations_taxo_slug', __( 'Locations category slug', 'yoast-local-seo' ) );
echo '<p class="desc label" style="border: 0; margin-bottom: 0; padding-bottom: 0;">' . __( 'The slug for your location categories. Default slug is <code>locations-category</code>.', 'yoast-local-seo' ) . '<br>';
echo '<a href="' . admin_url( 'edit-tags.php?taxonomy=wpseo_locations_category&post_type=wpseo_locations' ) . '">' . __( 'Edit the categories', 'yoast-local-seo' ) . '</a>';
echo '</p>';
echo '</div><!-- #show-multiple-locations -->';

echo '<h2>' . __( 'Opening hours', 'yoast-local-seo' ) . '</h2>';
WPSEO_Local_Admin_Wrappers::checkbox( 'hide_opening_hours', '', __( 'Hide opening hours option', 'yoast-local-seo' ) );

$hide_opening_hours = isset( $options['hide_opening_hours'] ) && $options['hide_opening_hours'] == 'on';
echo '<div id="hide-opening-hours" style="clear: both; display:' . ( $hide_opening_hours ? 'none' : 'block' ) . ';">';
WPSEO_Local_Admin_Wrappers::checkbox( 'opening_hours_24h', '', __( 'Use 24h format', 'yoast-local-seo' ) );
echo '<br class="clear">';

echo '<div id="show-opening-hours" ' . ( wpseo_has_multiple_locations() ? ' class="hidden"' : '' ) . '>';

echo '<div id="opening-hours-multiple">';
WPSEO_Local_Admin_Wrappers::checkbox( 'multiple_opening_hours', '', __( 'I have two sets of opening hours per day', 'yoast-local-seo' ) );
echo '</div>';
echo '<br class="clear">';

if ( !isset( $options['opening_hours_24h'] ) )
	$options['opening_hours_24h'] = false;

foreach ( $wpseo_local_core->days as $key => $day ) {
	$field_name        = 'opening_hours_' . $key;
	$value_from        = isset( $options[$field_name . '_from'] ) ? esc_attr( $options[$field_name . '_from'] ) : '09:00';
	$value_to          = isset( $options[$field_name . '_to'] ) ? esc_attr( $options[$field_name . '_to'] ) : '17:00';
	$value_second_from = isset( $options[$field_name . '_second_from'] ) ? esc_attr( $options[$field_name . '_second_from'] ) : '09:00';
	$value_second_to   = isset( $options[$field_name . '_second_to'] ) ? esc_attr( $options[$field_name . '_second_to'] ) : '17:00';

	echo '<div class="clear opening-hours">';

	echo '<label class="textinput">' . $day . ':</label>';
	echo '<select class="openinghours_from" style="width: 100px;" id="' . $field_name . '_from" name="wpseo_local[' . $field_name . '_from]">';
	echo wpseo_show_hour_options( $options['opening_hours_24h'], $value_from );
	echo '</select><span id="' . $field_name . '_to_wrapper"> - ';
	echo '<select class="openinghours_to" style="width: 100px;" id="' . $field_name . '_to" name="wpseo_local[' . $field_name . '_to]">';
	echo wpseo_show_hour_options( $options['opening_hours_24h'], $value_to );
	echo '</select>';

	echo '<div class="clear opening-hour-second ' . ( empty( $options['multiple_opening_hours'] ) || $options['multiple_opening_hours'] != 'on' ? 'hidden' : '' ) . '">';
	echo '<label class="textinput">&nbsp;</label>';
	echo '<select class="openinghours_from_second" style="width: 100px;" id="' . $field_name . '_second_from" name="wpseo_local[' . $field_name . '_second_from]">';
	echo wpseo_show_hour_options( $options['opening_hours_24h'], $value_second_from );
	echo '</select><span id="' . $field_name . '_second_to_wrapper"> - ';
	echo '<select class="openinghours_to_second" style="width: 100px;" id="' . $field_name . '_second_to" name="wpseo_local[' . $field_name . '_second_to]">';
	echo wpseo_show_hour_options( $options['opening_hours_24h'], $value_second_to );
	echo '</select>';
	echo '</div>';

	echo '</div>';
}

echo '</div><!-- #show-opening-hours -->';
echo '</div><!-- #hide-opening-hours -->';

echo '<h2>' . __( 'Store locator settings', 'yoast-local-seo' ) . '</h2>';
WPSEO_Local_Admin_Wrappers::textinput( 'sl_num_results', __( 'Number of results', 'yoast-local-seo' ) );

echo '<h2>' . __( 'Advanced settings', 'yoast-local-seo' ) . '</h2>';

WPSEO_Local_Admin_Wrappers::textinput( 'api_key', __( 'Google Maps API key<br>(not required)', 'yoast-local-seo' ) );
echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . sprintf( __( 'When you have a lot of locations, you could hit the limits of the Geocoding API. In that case you can enter your Google Maps API key to prevent that. To obtain an API key, please read more about it at the %sGoogle Maps documentation%s (make sure the Geocoding API is enabled in the Services section).', 'yoast-local-seo' ), '<a href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key">', '</a>' ) . '</p>';


WPSEO_Local_Admin_Wrappers::select( 'unit_system', __( 'Unit System', 'yoast-local-seo' ), array(
	'METRIC' => __( 'Metric', 'yoast-local-seo' ),
	'IMPERIAL' => __( 'Imperial', 'yoast-local-seo' )
) );
WPSEO_Local_Admin_Wrappers::select( 'map_view_style', __( 'Default map style', 'yoast-local-seo' ), array(
	'HYBRID' => __('Hybrid', 'yoast-local-seo'),
	'SATELLITE' => __('Satellite', 'yoast-local-seo'),
	'ROADMAP' => __('Roadmap', 'yoast-local-seo'),
	'TERRAIN' => __('Terrain', 'yoast-local-seo')
) );
WPSEO_Local_Admin_Wrappers::select( 'address_format', __( 'Address format', 'yoast-local-seo' ), array(
	'address-state-postal' => '{address} {city}, {state} {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, NY 12345 )',
	'address-state-postal-comma' => '{address} {city}, {state}, {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, NY, 12345 )',
	'address-postal-city-state' => '{address} {zipcode} {city}, {state} &nbsp;&nbsp;&nbsp;&nbsp; (12345 New York, NY )',
	'address-postal' => '{address} {city} {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York 12345 )',
	'address-postal-comma' => '{address} {city}, {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, 12345 )',
	'address-city' => '{address} {city} &nbsp;&nbsp;&nbsp;&nbsp; (London)',
	'postal-address' => '{zipcode} {state} {city} {address} &nbsp;&nbsp;&nbsp;&nbsp; (12345, NY, New York)',
) );

echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . sprintf( __( 'A lot of countries have their own address format. Please choose one that matches yours. If you have something completely different, please let us know via %s.', 'yoast-local-seo' ), '<a href="mailto:pluginsupport@yoast.com">pluginsupport@yoast.com</a>' ) . '</p>';

WPSEO_Local_Admin_Wrappers::select( 'default_country', __( 'Default country', 'yoast-local-seo' ), WPSEO_Local_Frontend::get_country_array() );

echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'If you\'re having multiple locations and they\'re all in one country, you can select your default country here. This country will be used in the storelocator search to improve the search results.', 'yoast-local-seo' ) . '</p>';
WPSEO_Local_Admin_Wrappers::textinput( 'show_route_label', __( '"Show route" label', 'yoast-local-seo' ), '', array( 'placeholder' => __( 'Show route', 'yoast-local-seo' ) ) );

echo '<br class="clear">';
echo '<label class="textinput" for="custom_marker">' . __( 'Custom marker', 'yoast-local-seo' ) . ':</label>';
echo '<img src="' . ( isset( $options['custom_marker'] ) ? wp_get_attachment_url( $options['custom_marker'] ) : '' ) . '" id="custom_marker" />';
WPSEO_Local_Admin_Wrappers::hidden( 'custom_marker' );
echo '<button class="set_custom_images button">' . __( 'Set custom marker image', 'yoast-local-seo' ) . '</button>';
if( isset( $options['custom_marker'] ) && '' != $options['custom_marker'] ) {
	echo '<br /><button id="remove_marker">' . __( 'Remove marker', 'yoast-local-seo' ) . '</button>';
	$wpseo_local_core->check_custom_marker_size( $options['custom_marker'] );
} else {
	echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'The custom marker should be 100x100 px. If the image exceeds those dimensions it could (partially) cover the info popup.', 'yoast-local-seo' ) . '</p>';
}

echo '<br class="clear">';
WPSEO_Local_Admin_Wrappers::checkbox( 'hide_tools_section', '', __( 'Hide tools section', 'yoast-local-seo' ) );

echo "</div>";

do_action( 'wpseo_local_config' );

echo '</div><!-- #general-tab -->';

if( false == $hide_tools ) :
	// Tools tab
	echo '<div id="tools" class="wpseotab">';
	echo '<div id="local-tools" class="yoastbox">';
	echo '<h2>' . __( 'Local SEO tools', 'yoast-local-seo' ) . '</h2>';

	echo '<p>' . __('In this section we will highlight some tools which you can use to further improve your Local SEO. We\'ve tested all these tools ourselves and are really happy about them. We arranged some nice deals for you, so you won\'t pay the full price as a Yoast Local SEO user.', 'yoast-local-seo') . '</p>';

	$tools = array(
		'lseo' => 'LSEO',
		'whitespark' => 'Whitespark Local SEO tools',
		//'moz' =>' Moz Local'
	);

	// Shuffle Tools
	$tools_keys = array_keys( $tools );
	shuffle( $tools_keys );
	$tools_array = array();
	foreach( $tools_keys as $key ) {
	    $tools_array[ $key ] = $tools[ $key ];
	}

	foreach( $tools_array as $key => $label ) {
		echo '<div id="' . $key . '-wrapper" class="lseo-tool-wrapper">';
		echo '<h2>' . $label . '</h2>';

		switch( $key ) {
			case 'lseo':
				echo '<p><a href="http://yoa.st/lseo" target="_blank"><img style="float: left; margin-right: 10px;" src="' . plugins_url( '../images/logo-lseo.png', dirname( __FILE__ ) ) . '" alt="LSEO" height="45"></a>' . sprintf( __('LSEO is a complete local search engine optimization solution for businesses. Answer 10 short questions below to find out how visible your website is likely to be on Google. Along with a free LSEO Score you will also get free tips and solutions for how to improve your local ranking. Brought to you by %sLSEO.com%s.', 'yoast-local-seo'), '<a href="http://yoa.st/lseo" target="_blank">', '</a>' ) . '</p>';
				echo '<p>' . sprintf( __('To receive a %1$s14 Day FREE Trial%2$s, please go to %3$s.', 'yoast-local-seo'), '<strong>', '</strong>', '<a href="http://yoa.st/lseo" target="_blank">www.lseo.com/getstarted</a>' ) . '</p>';
				echo '<iframe name="yoast_lseo" src="//lseo.com/widget/2/1008/' . esc_attr( $_SERVER['HTTP_HOST'] ) . '"  height="165px" width="100%" scrolling="no" border="0" frameborder="0" allowtransparency="true"></iframe>';

				break;

			case 'whitespark':
				echo '<p><a href="http://yoa.st/lrank" target="_blank"><img style="float: left; margin-right: 10px;" src="' . plugins_url( '../images/logo-whitespark.png', dirname( __FILE__ ) ) . '" alt="Whitespark"></a></p><br class="clear">' . '<h3>' . __('Save 20% on Whitespark Local SEO Tool Plans', 'yoast-local-seo') . '</h3>';
				echo '<ul>';
				echo '<li>' . __('Whitespark offers reliable and accurate tools for Local SEO', 'yoast-local-seo') . '</li>';
				echo '<li>' . __('Get more customers, improve local rankings, track your competitors and manage your local SEO', 'yoast-local-seo') . '</li>';
				echo '<li>' . __('Gain access to our free Review Handout Generator, Offline Conversion Tracker and other essential local search resources', 'yoast-local-seo') . '</li>';
				echo '<li>' . sprintf( __('Save 20%% on your Local Citation Finder & Local Rank Tracker subscriptions with coupon code: %sYOASTDEAL2014%s', 'yoast-local-seo'), '<code>', '</code>' ) . '</li>';
				echo '</ul>';
				echo '<p>' . sprintf( __('Go to %s and get this deal!', 'yoast-local-seo'), '<a href="http://yoa.st/lrank" target="_blank">www.whitespark.ca/local-rank-tracker</a>' ) . '</p>';

				break;

			default: ''; break;
		}

		echo '</div>';
		echo '<br class="clear">';
	}


	echo '<div class="lseo-hide-tools">';
	WPSEO_Local_Admin_Wrappers::checkbox( 'hide_tools_section', '', __( 'Hide tools section', 'yoast-local-seo' ) );
	echo '</div>';
	echo '</div>';

	echo '</div><!-- #tools-tab -->';

endif;

WPSEO_Local_Admin_Wrappers::admin_footer();
?>
