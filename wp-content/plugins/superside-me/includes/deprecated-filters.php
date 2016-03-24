<?php

/**
 * Deprecated filters (for previous users of SuperSidr)
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2015-2016 Robin Cornett
 * @license   GPL-2.0+
 *
 * source: http://mikejolley.com/2013/12/deprecating-plugin-functions-hooks-woocommmerce/
 */

add_action( 'init', 'supersideme_map_deprecated_filters' );
function supersideme_map_deprecated_filters() {
	global $superside_map_deprecated_filters;

	$superside_map_deprecated_filters = array(
		'supersideme_navigation_options' => 'supersidrme_navigation_options',
		'supersideme_modify_display_css' => 'supersidrme_modify_display_css',
		'supersideme_modify_menu_css'    => 'supersidrme_modify_menu_css',
		'supersideme_default_css'        => 'supersidr_default_css',
		'supersideme_fontawesome_css'    => 'supersidr_fontawesome_css',
		'supersideme_menu_output'        => 'supersidrme_menu_output',
	);

	foreach ( $superside_map_deprecated_filters as $new => $old ) {
		add_filter( $new, 'superside_deprecated_filter_mapping' );
	}
}

function superside_deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	global $superside_map_deprecated_filters;

	$filter = current_filter();

	if ( isset( $superside_map_deprecated_filters[ $filter ] ) ) {
		if ( has_filter( $superside_map_deprecated_filters[ $filter ] ) ) {
			$data = apply_filters( $superside_map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );
			_deprecated_function( 'The ' . esc_attr( $superside_map_deprecated_filters[ $filter ] ) . ' filter', '1.4.0', esc_attr( $filter ) );
		}
	}

	return $data;
}
