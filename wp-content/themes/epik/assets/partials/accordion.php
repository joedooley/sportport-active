<?php
/**
 * This partial contains code for accordion.php.
 *
 * @package    SportPort Active
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( have_rows( 'accordion' ) ) :
	while ( have_rows( 'accordion' ) ) : the_row();
		echo '<div id="faq_container">
			      <div class="accordion" id="accordion">
			          <div class="accordion-heading"><span class="heading">' . get_sub_field( 'heading' ) . '</span><span class="accordion-button-icon fa fa-plus"></span></div>
			          <div class="accordion-content-container">
					      <div class="accordion-container"><span>' . get_sub_field( 'hidden_content' ) . '</span></div>
				      </div>
		          </div>
	          </div>';
	endwhile;
else :
	// no rows found
endif;
