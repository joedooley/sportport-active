<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Recommendations.
 *
 * Class to manage recommendation related business.
 *
 * @class		Receiptful_Recommendations
 * @version		1.1.6
 * @author		Receiptful
 * @since		1.1.6
 */
class Receiptful_Recommendations {


	/**
	 * Constructor.
	 *
	 * @since 1.1.6
	 */
	public function __construct() {

		// Recommendation shortcode
		add_shortcode( 'rf_recommendations', array( $this, 'recommendation_shortcode' ) );

	}


	/**
	 * Get recommendations.
	 *
	 * Get the recommendations HTML.
	 *
	 * @since 1.1.6
	 *
	 * @param	array	$args	List of arguments for the recommendations.
	 * @return	string			HTML code with the appropriate attributes.
	 */
	public function get_recommendations( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'name'		  			=> null,
			'show_header' 			=> null,
			'header_text' 			=> null,
			'header_type' 			=> null,
			'show_title'  			=> null,
			'show_price'  			=> null,
			'price_format'			=> null,
			'number_of_products'	=> null,
			'styles'				=> null,
		) );

		if ( 'false' == $args['show_title'] || '0' == $args['show_title'] ) :
			$args['show_title'] = '';
		endif;

		if ( 'false' == $args['show_price'] || '0' == $args['show_price'] ) :
			$args['show_price'] = '';
		endif;

		if ( 'false' == $args['show_header'] || '0' == $args['show_header'] ) :
			$args['show_header'] = '';
		endif;

		// Sanitize & format custom attributes
		$attributes = array();
		foreach ( $args as $k => $v ) :
			if ( ! is_null( $v ) ) :
				$attributes[] = 'data-' . esc_attr( str_replace( '_', '-', $k ) ) . '="' . esc_attr( $v ) . '"';
			endif;
		endforeach;
		$attributes = implode( ' ', $attributes );

		return '<div class="rf-recommendations" ' . $attributes . '></div>';

	}


	/**
	 * Display recommendations.
	 *
	 * Display the recommendations HTML.
	 *
	 * @since 1.1.6
	 *
	 * @param	array	$args	Recommendation arguments.
	 */
	public function display_recommendations( $args = array() ) {

		echo $this->get_recommendations( $args );

	}

	/**
	 * Recommendation shortcode.
	 *
	 * Shortcode to simply display a div with a class where the
	 * recommendations will be loaded in via JS.
	 *
	 * @since 1.1.6
	 *
	 * @param	array	$args	Recommendation arguments.
	 * @return	string			HTML for displaying recommendations.
	 */
	public function recommendation_shortcode( $atts = array() ) {

		$args = shortcode_atts( array(
			'name'		  			=> null,
			'show_header' 			=> null,
			'header_text' 			=> null,
			'header_type' 			=> null,
			'show_title'  			=> null,
			'show_price'  			=> null,
			'price_format'			=> null,
			'number_of_products'	=> null,
			'styles'				=> null,
		), $atts );

		return $this->get_recommendations( $args );

	}


}
