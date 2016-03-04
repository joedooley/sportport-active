<?php
/**
 * This file contains all Genesis specific functions
 *
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'spa_add_theme_support' );
/**
 * Add theme support features on after-theme-setup hook
 *
 * @author Joe Dooley
 *
 */
function spa_add_theme_support() {

	add_theme_support( 'html5' );
	add_theme_support( 'genesis-responsive-viewport' );
	add_theme_support( 'custom-background' );
	add_theme_support( 'genesis-footer-widgets', 3 );
	add_theme_support( 'genesis-connect-woocommerce' );
	add_theme_support( 'woocommerce' );


	add_theme_support( 'genesis-structural-wraps', array(
		'header',
		'nav',
		'subnav',
		'inner',
		'footer-widgets',
		'footer',
	) );


	add_action( 'wp_enqueue_scripts', 'spa_scripts_styles' );

	//* Remove the site description
	remove_action( 'genesis_site_description', 'genesis_seo_site_description' );

	//* Remove header right widget area
	unregister_sidebar( 'header-right' );

	//* Reposition secondary navigation menu
	remove_action( 'genesis_after_header', 'genesis_do_nav' );
	add_action( 'genesis_header', 'genesis_do_nav', 12 );

}


add_action( 'init', 'spa_register_custom_image_sizes' );
/**
 * Register custom image sizes
 */
function spa_register_custom_image_sizes() {

	add_image_size( 'featured-img', 730, 420, true );
	add_image_size( 'featured-page', 341, 173, true );
	add_image_size( 'portfolio-thumbnail', 264, 200, true );

}


// Customize search form input box text
add_filter( 'genesis_search_text', 'custom_search_text' );
function custom_search_text( $text ) {
	return esc_attr( 'Search...' );
}

add_action( 'admin_menu', 'epik_theme_settings_init', 15 );
/**
 * This is a necessary go-between to get our scripts and boxes loaded
 * on the theme settings page only, and not the rest of the admin
 */
function epik_theme_settings_init() {
	global $_genesis_admin_settings;

	add_action( 'load-' . $_genesis_admin_settings->pagehook, 'epik_add_portfolio_settings_box', 20 );
}

// Add Portfolio Settings box to Genesis Theme Settings
function epik_add_portfolio_settings_box() {
	global $_genesis_admin_settings;

	add_meta_box( 'genesis-theme-settings-epik-portfolio', __( 'Portfolio Page Settings', 'epik' ), 'epik_theme_settings_portfolio', $_genesis_admin_settings->pagehook, 'main' );
}

/**
 * Adds Portfolio Options to Genesis Theme Settings Page
 */
function epik_theme_settings_portfolio() { ?>

	<p><?php _e( "Display which category:", 'genesis' ); ?>
		<?php wp_dropdown_categories( array( 'selected'        => genesis_get_option( 'epik_portfolio_cat' ),
		                                     'name'            => GENESIS_SETTINGS_FIELD . '[epik_portfolio_cat]',
		                                     'orderby'         => 'Name',
		                                     'hierarchical'    => 1,
		                                     'show_option_all' => __( "All Categories", 'genesis' ),
		                                     'hide_empty'      => '0'
		) ); ?></p>

	<p><?php _e( "Exclude the following Category IDs:", 'genesis' ); ?><br />
		<input type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_cat_exclude]"
		       value="<?php echo esc_attr( genesis_get_option( 'epik_portfolio_cat_exclude' ) ); ?>" size="40" /><br />
		<small><strong><?php _e( "Comma separated - 1,2,3 for example", 'genesis' ); ?></strong></small>
	</p>

	<p><?php _e( 'Number of Posts to Show', 'genesis' ); ?>:
		<input type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_cat_num]"
		       value="<?php echo esc_attr( genesis_option( 'epik_portfolio_cat_num' ) ); ?>" size="2" /></p>

	<p><span
			class="description"><?php _e( '<b>NOTE:</b> The Portfolio Page displays the "Portfolio Page" image size plus the excerpt or full content as selected below.', 'epik' ); ?></span>
	</p>

	<p><?php _e( "Select one of the following:", 'genesis' ); ?>
		<select name="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content]">
			<option style="padding-right:10px;"
			        value="full" <?php selected( 'full', genesis_get_option( 'epik_portfolio_content' ) ); ?>><?php _e( "Display post content", 'genesis' ); ?></option>
			<option style="padding-right:10px;"
			        value="excerpts" <?php selected( 'excerpts', genesis_get_option( 'epik_portfolio_content' ) ); ?>><?php _e( "Display post excerpts", 'genesis' ); ?></option>
		</select></p>

	<p><label for="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content_archive_limit]"><?php _e( 'Limit content to', 'genesis' ); ?></label> <input
			type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content_archive_limit]"
			id="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content_archive_limit]"
			value="<?php echo esc_attr( genesis_option( 'epik_portfolio_content_archive_limit' ) ); ?>" size="3" /> <label
			for="<?php echo GENESIS_SETTINGS_FIELD; ?>[epik_portfolio_content_archive_limit]"><?php _e( 'characters', 'genesis' ); ?></label></p>

	<p><span
			class="description"><?php _e( '<b>NOTE:</b> Using this option will limit the text and strip all formatting from the text displayed. To use this option, choose "Display post content" in the select box above.', 'genesis' ); ?></span>
	</p>
	<?php
}

