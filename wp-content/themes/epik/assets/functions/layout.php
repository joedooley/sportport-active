<?php
/**
 * Theme layout and structure
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


/**
 * Output Free Shipping Notification into before header hook
 *
 * @since   1.0.0
 *
 * @return  null if the free_shipping_notification is empty
 */
add_action( 'genesis_before', function() {

	echo '<section class="before-header"><div class="wrap">';

	genesis_widget_area( 'before-header-left', array(
		'before' => '<div class="before-header-left-container"><div class="one-third first before-header-widget before-header-left">',
		'after'  => '</div></div>',
	) );

	if ( get_field( 'free_shipping_notification', 'option' ) ) {
		$free_shipping = get_field( 'free_shipping_notification', 'option' );

		echo '<div class="before-header-middle-container"><div class="before-header-widget one-third before-header-middle">';

		echo '<h5 class="free-shipping-notification">' . $free_shipping  . '</h5>';

		echo '</div></div>';

	}

	genesis_widget_area( 'before-header-right', array(
		'before' => '<div class="before-header-right-container"><div class="one-third before-header-widget before-header-right">',
		'after'  => '</div></div>',
	) );

	echo '</div></section>';

});


add_filter( 'genesis_footer_creds_text', 'spa_personalize_footer_creds' );
/**
 * Personalize the copyright output in the footer
 *
 * @param $output
 *
 */
function spa_personalize_footer_creds( $output ) {

	if ( get_field( 'footer_copyright', 'option' ) ) {
		$footer_copyright = get_field( 'footer_copyright', 'option' );

		echo '<div class="footer-copyright">' . $footer_copyright . '</div>';

	}

}

/**
 * Add after post Call To Action section on all post types except for
 * Pages and any WooCoomerce page or product page.
 *
 * @return void
 */
add_action('genesis_after_entry', function () {
	if ( is_single() ) {

		$cta_headline = get_field( 'cta_headline' );
		$cta          = get_field( 'call_to_action' );

		echo '<section class = "after-post-cta"><div class = "wrap">';

		if ( $cta_headline ) {
			echo '<h2 class="cta-headline">' . $cta_headline . '</h2>';
		}
		if ( $cta ) {
			echo $cta;
		}

		echo '</div></section>';
	}
}, 5);
