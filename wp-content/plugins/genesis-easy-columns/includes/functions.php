<?php
/**
 * Gnesis Columns Add WordPress Editor Buttons
 */
	
	//Check user permissions and add TinyMice Button
	function gc_add_button() {  
	   if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') )  
	   {  
		  	add_filter('mce_external_plugins', 'gc_add_plugin');  
			add_filter('mce_buttons', 'gc_register_button'); 
	   }  
	}  

	add_action('init', 'gc_add_button');  

	//Register TinyMce Buttons
	function gc_register_button($buttons) {  
	   array_push($buttons, "column", "one-half", "one-third", "one-fourth", "one-fifth", "one-sixth", "clear", "clear-line");  
	   return $buttons;  
	}  

	//Registers TinyMCE Plugins
	function gc_add_plugin($plugin_array) {  
	   $plugin_array['column'] = plugins_url('genesis-easy-columns').'/includes/shortcodes.js';
	   $plugin_array['one-half'] = plugins_url('genesis-easy-columns').'/includes/shortcodes.js'; 
	   $plugin_array['one-third'] = plugins_url('genesis-easy-columns').'/includes/shortcodes.js'; 
	   $plugin_array['one-fourth'] = plugins_url('genesis-easy-columns').'/includes/shortcodes.js'; 
	   $plugin_array['one-fifth'] = plugins_url('genesis-easy-columns').'/includes/shortcodes.js'; 
	   $plugin_array['one-sixth'] = plugins_url('genesis-easy-columns').'/includes/shortcodes.js'; 
	   $plugin_array['clear'] = plugins_url('genesis-easy-columns').'/includes/shortcodes.js'; 
	   $plugin_array['clear-line'] = plugins_url('genesis-easy-columns').'/includes/shortcodes.js'; 
	   return $plugin_array;  
	}  
	
	//Enqueue Optional CSS Stylesheet
	function gc_optional_css()  { 

		wp_register_style( 'optional-css', 
		plugins_url('genesis-easy-columns') . '/optional-css/column-style.css', 
		array(), 
		'all' );

		// enqueing:
		wp_enqueue_style( 'optional-css' );
	}
		//Disables style-custom.php if disable checkbox in Theme Options is selected.
		//$disable = of_get_option('disable_stylesheet');
		//echo $gc_options['enabled'];
		if (isset($gc_options['enabled']) && ($gc_options['enabled'] == true))
		{
			add_action('wp_enqueue_scripts', 'gc_optional_css');

		}