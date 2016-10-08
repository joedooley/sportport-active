<?php
/**
 * functions.php
 *
 * @package    Theme Name
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */


/**
 * Enqueue scripts and styles
 */
function theme_name_enqueue_scripts_styles() {
	wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css' );
	wp_enqueue_script( 'global', dynamik_get_stylesheet_location( 'url' ) . 'global.js', array( 'jquery' ), CHILD_THEME_VERSION, true );

	if ( is_front_page() ) {
		wp_enqueue_script( 'backstretch', get_bloginfo( 'url' ) . '/wp-content/js/jquery.backstretch.min.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'backstretch-init', get_bloginfo( 'url' ) . '/wp-content/js/backstretch-init.js', array( 'jquery', 'backstretch' ), '1.0.0', true );
	}
}


add_action( 'after_setup_theme', 'theme_name_add_theme_support' );
/**
 * Add theme support features on after-theme-setup hook
 *
 * @author Joe Dooley
 *
 */
function theme_name_add_theme_support() {

	// Remove the header right widget area
	unregister_sidebar( 'header-right' );

	// Remove the primary navigation menu
	remove_action( 'genesis_after_header', 'genesis_do_nav' );
	add_action( 'genesis_header', 'genesis_do_nav' );

	// Enqueue scripts and styles
	add_action( 'wp_enqueue_scripts', 'theme_name_enqueue_scripts_styles' );

	// Add class for screen readers to site description
	add_filter( 'genesis_attr_site-description', 'genesis_attributes_screen_reader_class' );


}




add_filter( 'wp_nav_menu_items', 'theme_menu_extras', 10, 2 );
/**
 * Filter HTML menu list items to append a search form.
 *
 * @param $menu (string) - Primary Nav list items.
 * @param $args (object) - An object containing wp_nav_menu() arguments.
 *
 * @return string - Primary Navigation with appended menu items.
 */
function theme_menu_extras( $menu, $args ) {
	if ( 'primary' !== $args->theme_location ) {
		return $menu;
	}

	ob_start();
	$nav_shortcode = do_shortcode( '[nav_social_menu]' );
	echo '<li class="social">' . $nav_shortcode . '</li>';

	$social = ob_get_clean();
	$menu .= $social;
	$menu .= '<li class = "search"><a id = "main-nav-search-link" class = "icon-search"></a><div class = "search-div">' . get_search_form( false )
	         . '</div></li>';

	return $menu;

}



add_filter( 'genesis_search_button_text', 'sp_search_button_text' );
/**
 * Customize search form input button text.
 *
 * @param $text
 * @return string|void
 */
function sp_search_button_text( $text ) {
	return esc_attr( 'Go' );
}


