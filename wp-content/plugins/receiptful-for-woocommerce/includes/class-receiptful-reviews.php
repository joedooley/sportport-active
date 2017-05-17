<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Review.
 *
 * Class to manage product review related business.
 *
 * @class		Receiptful_Reviews
 * @version		1.3.3
 * @author		Conversio
 * @since		1.3.3
 */
class Receiptful_Reviews {


	/**
	 * Constructor.
	 *
	 * @since 1.3.3
	 */
	public function __construct() {

		// Recommendation shortcode
		add_shortcode( 'rf_reviews', array( $this, 'reviews_shortcode' ) );

	}


	/**
	 * Get review.
	 *
	 * Get a generated <div> HTML tag that is required to load the review widget in.
	 *
	 * @since 1.3.3
	 *
	 * @param	array	$args	List of arguments for the review.
	 * @return	string			HTML code with the appropriate attributes.
	 */
	public function get_reviews( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'slug' => null,
		) );

		// Sanitize & format custom attributes
		$attributes = array();
		foreach ( $args as $k => $v ) :
			if ( ! is_null( $v ) ) :
				$attributes[] = 'data-' . esc_attr( str_replace( '_', '-', $k ) ) . '="' . esc_attr( $v ) . '"';
			endif;
		endforeach;
		$attributes = implode( ' ', $attributes );

		return '<div class="rf-reviews" ' . $attributes . '></div>';

	}


	/**
	 * Display review widget.
	 *
	 * Display the review widget HTML.
	 *
	 * @since 1.3.3
	 *
	 * @param	array	$args	Feedback widget arguments.
	 */
	public function display_reviews( $args = array() ) {
		echo $this->get_reviews( $args );
	}


	/**
	 * Product reviews shortcode.
	 *
	 * Shortcode to simply display a div with a class where the
	 * review will be loaded in via JS.
	 *
	 * @since 1.3.3
	 *
	 * @param	array	$atts	Review arguments.
	 * @return	string			HTML for displaying product reviews.
	 */
	public function reviews_shortcode( $atts = array() ) {

		$args = shortcode_atts( array(
			'slug' => null,
		), $atts );

		return $this->get_reviews( $args );

	}


}
