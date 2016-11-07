<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Widgets.
 *
 * Class to manage any widgets in the Widget collection.
 *
 * @class		Receiptful_Widgets
 * @version		1.3.3
 * @author		Receiptful
 * @since		1.3.3
 */
class Receiptful_Widget {
    /**
    * Constructor.
    *
    * @since 1.3.3
    */
    public function __construct() {
        add_shortcode( 'rf_widget', array( $this, 'widget_shortcode' ) );
    }

    /**
    * Get widget.
    *
    * Get a generated <div> HTML tag that is required to load the widget in.
    *
    * @since 1.3.3
    *
    * @param	array	$args	List of arguments for the widget.
    * @return	string		HTML code with the appropriate attributes.
    */
    public function get_widget( $args = array() ) {
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

        return '<div class="rf-widget" ' . $attributes . '></div>';
    }

    /**
    * Display widget.
    *
    * Display the widget HTML.
    *
    * @since 1.3.3
    *
    * @param	array	$args	Widget arguments.
    */
    public function display_widget( $args = array() ) {
        echo $this->get_widget( $args );
    }

    /**
    * Widget shortcode.
    *
    * Shortcode to simply display a div with a class where the
    * widget will be loaded in via JS.
    *
    * @since 1.3.3
    *
    * @param	array	$attrs	Widget arguments.
    * @return	string	        HTML for displaying widget.
    */
    public function widget_shortcode( $attrs = array() ) {
        $args = shortcode_atts( array(
                'slug' => null,
        ), $attrs );

        return $this->get_widget( $args );
    }
}
