<?php
/*
  Plugin Name: WooSwatches - Woocommerce Color or Image Variation Swatches
  Plugin URI: http://phppoet.com
  Description: Convert variable select box into color or image select.
  Version: 2.2.7
  Author: parbat chaudhari
  Author URI: http://phppoet.com
  Requires at least: 3.3
  Tested up to: 4.3
  
*/

    if( !defined( 'wcva_PLUGIN_URL' ) )
          define( 'wcva_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	  
 
    if( !defined( 'wcva_base_url' ) )
          define( 'wcva_base_url', plugin_basename(__FILE__) );
	/*
	 * localization
	 */
    load_plugin_textdomain( 'wcva', false, basename( dirname(__FILE__) ).'/languages' );


    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    
	/**
	 * Check if quick view plugin is enabled
	 */
   	if (is_plugin_active( 'woocommerce-better-quick-view/woocommerce-better-quick-view.php' ) ) {
	   
	   define( 'wcva_quick_view_mode', 'on' );
	
	} else {
		
	   define( 'wcva_quick_view_mode', 'off' );
	   
	}
	
   /**
    * check weather woocommerce is active or not
    */

    if (is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
 
          
          require 'classes/class_create_variations_metabox.php';
		  require 'classes/class_override_woocommerce_variable_tamplate.php';
		  require 'classes/class_wcva_register_scripts_styles.php';
		  require 'classes/class_attribute_global_values.php';
		  require 'classes/class_shop_page_swatchs.php';
		  require 'includes/wcva_common_functions.php';
		  require 'includes/wcva_swatch_form_fields.php';
		  require 'includes/wcva_direct_variation_link.php';
		  require 'includes/wcva_add_layered_navigation_widget.php';
		  require 'includes/admin/class_add_plugin_settings_field.php';
 
    } else {
    
    /**
	 * Display Notice if woocommerce is not installed
	 */
     
     function wcva_installation_notice() {
         echo '<div class="updated" style="padding:15px; position:relative;"><a href="http://wordpress.org/plugins/woocommerce/">'.__('Woocommerce','dpta').'</a>  must be installed and activated before using this plugin. </div>';
       }

        add_action('admin_notices', 'wcva_installation_notice');
       return;

    }
	
	

	 
    /*
	 * Gets absolute path for plugin
	 */
    function wcva_plugin_path() {
  
       return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }
    
	/*
	 * Get woocommerce version 
	 */
	function wcva_get_woo_version_number() {
       
	   if ( ! function_exists( 'get_plugins' ) )
		 require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
       
	   $plugin_folder = get_plugins( '/' . 'woocommerce' );
	   $plugin_file = 'woocommerce.php';
	
	
	   if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
		  return $plugin_folder[$plugin_file]['Version'];

	   } else {
	
		return NULL;
	   }
    }
	
	/**
     * Plugin Update Checker
     */

    require dirname( __FILE__ ) . '/plugin-update-checker/plugin-update-checker.php';
       $MyUpdateChecker = Wcva_PucFactory::buildUpdateChecker(
          'http://phppoet.com/updates/?action=get_metadata&slug=woocommerce-colororimage-variation-select', //Metadata URL.
           __FILE__, //Full path to the main plugin file.
          'woocommerce-colororimage-variation-select' //Plugin slug. Usually it's the same as the name of the directory.
    );




?>