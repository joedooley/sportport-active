<?php
/**
 * General Settings Panel template
 *
 * @package   facet_stack/widget_panels
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */


	$show_titles = isset( $instance['show_titles'] ) ? 'checked="checked"' : null;
	$load_style = isset( $instance['load_style'] ) ? 'checked="checked"' : null;
	$multi_stack = isset( $instance['multi_stack'] ) ? 'checked="checked"' : null;

	echo '<div class="facet-stack-section-wrapper">';
		
		echo '<h4>' . esc_html__( 'Display Settings', 'facet-stack' ) . ' <span class="facet-stack-section-toggle dashicons dashicons-arrow-down"></span></h4>';			
		
		echo '<div class="facet-stack-section">';

			// Show Titles
			echo '<p><input type="checkbox" id="' . $this->get_field_id( 'show_titles' ) . '" name="' . $this->get_field_name( 'show_titles' ) . '" class="checkbox facet-stack-checkbox" ' . $show_titles . '>';
			echo '<label for="' . $this->get_field_id( 'show_titles' ) . '">' . esc_html__( 'Show Titles', 'facet-stack' ) . '</label></p>';

			// Alt Loader
			echo '<p><input type="checkbox" id="' . $this->get_field_id( 'load_style' ) . '" name="' . $this->get_field_name( 'load_style' ) . '" class="checkbox facet-stack-checkbox" ' . $load_style . '>';
			echo '<label for="' . $this->get_field_id( 'load_style' ) . '">' . esc_html__( 'Alternate Load Styles', 'facet-stack' ) . '</label></p>';

			// Stack Style
			echo '<p><input type="checkbox" id="' . $this->get_field_id( 'multi_stack' ) . '" name="' . $this->get_field_name( 'multi_stack' ) . '" class="checkbox facet-stack-checkbox" ' . $multi_stack . '>';
			echo '<label for="' . $this->get_field_id( 'multi_stack' ) . '">' . esc_html__( 'Multi-Widget Stack', 'facet-stack' ) . '</label>';
			echo '<span class="description" style="margin-left: 18px;">' . esc_html__( "Each Facet is it's own widget", 'facet-stack' ) . '</span><p>';


		echo '</div>';

	echo '</div>';
