<?php
/**
 * This file contains markup for the homepage
 *
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

// Template Name: FAQ

/**
 * Add faq class to $classes body class array
 *
 * @param $classes | CSS classes
 * @return array | All CSS classes on the HTML body element
 */
add_action( 'body_class', function( $classes ) {
	$classes[] = 'faq';
	return $classes;
}  );


/**
 * Remove Entry Header and Entry Title
 */
remove_all_actions( 'genesis_entry_header' );


/** Remove default Genesis loop */
remove_action( 'genesis_entry_content', 'genesis_do_loop' );
add_action( 'genesis_entry_content', 'sp_acf_accordion' );
/**
 * Outputs ACF Repeator for Accordion.
 */
function sp_acf_accordion() {

	if ( ! is_page_template( 'templates/faq.php' ) ) {
		return;
	}

	echo '<div id="accordion">';

	while ( have_rows( 'accordion_container' ) ) : the_row();

		if ( get_row_layout() === 'section_headers' ) {

			$section_heading = get_sub_field( 'section_header' );

			echo '<h3 class = "section-header heading">' . $section_heading . '</h3>';

		} elseif ( get_row_layout() === 'accordion_section' ) {

			if ( have_rows( 'accordion' ) ) :

				while ( have_rows( 'accordion' ) ) : the_row();

					echo '<div class = "accordion-item">';


					$heading = get_sub_field( 'header' );
					$content = get_sub_field( 'hidden_content' );

					if ( $heading ) {
						echo '<h2 class = "accordion-heading heading">' . $heading . '</h2>';
					}

					if ( $content ) {
						echo '<div class = "accordion-content">' . $content . '</div>';
					}

					echo '</div>';


				endwhile;


			endif;

		}

	endwhile;

	echo '</div>';

}




/**
 * Enqueue single page script accordion.js
 *
 * @return     void
 */
add_action( 'wp_enqueue_scripts', function () {

	if ( ! is_page_template( 'templates/faq.php' ) ) {
		return;
	}
	wp_enqueue_script( 'accordion-js', get_stylesheet_directory_uri() . '/assets/js/custom/single/accordion.js', array( 'jquery' ), CHILD_THEME_VERSION, true );

} );

genesis();
