<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Feedback.
 *
 * Class to manage feedback related business.
 *
 * @class		Receiptful_Widgets
 * @version		1.2.6
 * @author		Conversio
 * @since		1.2.6
 */
class Receiptful_Feedback {


	/**
	 * Constructor.
	 *
	 * @since 1.2.6
	 */
	public function __construct() {

		// Recommendation shortcode
		add_shortcode( 'rf_feedback', array( $this, 'feedback_shortcode' ) );

	}


	/**
	 * Get feedback.
	 *
	 * Get a generated <div> HTML tag that is required to load the feedback widget in.
	 *
	 * @since 1.2.6
	 *
	 * @param	array	$args	List of arguments for the feedback.
	 * @return	string			HTML code with the appropriate attributes.
	 */
	public function get_feedback( $args = array() ) {

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

		return '<div class="rf-feedback" ' . $attributes . '></div>';

	}


	/**
	 * Display feedback widget.
	 *
	 * Display the feedback widget HTML.
	 *
	 * @since 1.2.6
	 *
	 * @param	array	$args	Feedback widget arguments.
	 */
	public function display_feedback( $args = array() ) {
		echo $this->get_feedback( $args );
	}

	/**
	 * Feedback shortcode.
	 *
	 * Shortcode to simply display a div with a class where the
	 * feedback will be loaded in via JS.
	 *
	 * @since 1.2.6
	 *
	 * @param	array	$atts	Feedback arguments.
	 * @return	string			HTML for displaying recommendations.
	 */
	public function feedback_shortcode( $atts = array() ) {

		$args = shortcode_atts( array(
			'slug' => null,
		), $atts );

		return $this->get_feedback( $args );

	}


}
