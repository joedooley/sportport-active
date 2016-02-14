<?php
/*
Plugin Name: Easy Smooth Scroll Links
Plugin URI: http://www.jeriffcheng.com/wordpress-plugins/easy-smooth-scroll-links
Description: Create anchors and add up to to 30 scrolling animation effects to links that link to page anchors. You can set scroll speed and offset value. 
Version: 2.0
Author: Jeriff Cheng
Author URI: http://www.jeriffcheng.com/
*/

/*
Copyright 2014  Jeriff Cheng ( Email:hschengyongtao@gmail.com )

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
*/

//Anchor Button to TinyMCE Editor
global $wp_version;
if ( $wp_version < 3.9 ) {
	if ( ! function_exists('enable_anchor_button') ) {
		function enable_anchor_button($buttons) {
		  $buttons[] = 'anchor';
		  return $buttons;
		}
	}
	add_filter("mce_buttons_2", "enable_anchor_button");
} else {
	add_action( 'init', 'anchor_button' );
	function anchor_button() {
		add_filter( "mce_external_plugins", "anchor_add_button" );
		add_filter( 'mce_buttons_2', 'anchor_register_button' );
	}
	function anchor_add_button( $plugin_array ) {
		$plugin_array['anchor'] = $dir = plugins_url( '/anchor/plugin.min.js', __FILE__ );
		return $plugin_array;
	}
	function anchor_register_button( $buttons ) {
		array_push( $buttons, 'anchor' );
		return $buttons;
	}
}

//Shortcode
if ( ! function_exists('essl_shortcode') ) {
function essl_shortcode( $atts, $content = null ) {
   return '<a id="' . $content . '">';
}
add_shortcode( 'anchor', 'essl_shortcode' );
}


/* 
Registering Options Page
*/	
if(!class_exists('ESSLPluginOptions')) :

// DEFINE PLUGIN ID
define('ESSLPluginOptions_ID', 'essl-plugin-options');
// DEFINE PLUGIN NICK
define('ESSLPluginOptions_NICK', 'ESSL Settings');

    class ESSLPluginOptions
    {
		/** function/method
		* Usage: return absolute file path
		* Arg(1): string
		* Return: string
		*/
		public static function file_path($file)
		{
			return ABSPATH.'wp-content/plugins/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).$file;
		}
		/** function/method
		* Usage: hooking the plugin options/settings
		* Arg(0): null
		* Return: void
		*/
		public static function register()
		{
			register_setting(ESSLPluginOptions_ID.'_options', 'enable_essl_aggressive');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_speed');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_offset');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_easing');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_begin_1');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_begin_2');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_begin_3');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_begin_4');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_begin_5');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_match_1');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_match_2');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_match_3');
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_match_4');			
			register_setting(ESSLPluginOptions_ID.'_options', 'essl_exclude_match_5');				
		}
		/** function/method
		* Usage: hooking (registering) the plugin menu
		* Arg(0): null
		* Return: void
		*/
		public static function menu()
		{
			// Create menu tab
			add_options_page(ESSLPluginOptions_NICK.' Plugin Options', ESSLPluginOptions_NICK, 'manage_options', ESSLPluginOptions_ID.'_options', array('ESSLPluginOptions', 'options_page'));
		}
		/** function/method
		* Usage: show options/settings form page
		* Arg(0): null
		* Return: void
		*/
		public static function options_page()
		{ 
			if (!current_user_can('manage_options')) 
			{
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			$plugin_id = ESSLPluginOptions_ID;
			// display options page
			include(self::file_path('options.php'));
		}
		
    }
	
	
	// Add settings link on plugin page
	function essl_plugin_action_links($links) { 
	  $settings_link = '<a href="options-general.php?page=essl-plugin-options_options">Settings</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}
	 
	$plugin = plugin_basename(__FILE__); 
	add_filter("plugin_action_links_$plugin", 'essl_plugin_action_links' );


	if ( is_admin() )
	{
		add_action('admin_init', array('ESSLPluginOptions', 'register'));
		add_action('admin_menu', array('ESSLPluginOptions', 'menu'));
		
	}
	
	if ( !is_admin() )
	{

		add_action('wp_enqueue_scripts', 'essl_enqueue_jquery', 999 );
		add_action('wp_footer', 'essl_script',100);
		
		
		function essl_enqueue_jquery() {
			wp_deregister_script( 'jquery-easing' );
			wp_register_script( 'jquery-easing', '//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js',array( 'jquery' ) );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script('jquery-easing');		
		}		

		function essl_script() {			
			$essl_exclude_begin_1=$essl_exclude_begin_2=$essl_exclude_begin_3=$essl_exclude_begin_4=$essl_exclude_begin_5=$essl_exclude_match_1=$essl_exclude_match_2=$essl_exclude_match_3=$essl_exclude_match_4=$essl_exclude_match_5='';			
			if(get_option('essl_exclude_begin_1')){ $essl_exclude_begin_1=":not([href^='".get_option('essl_exclude_begin_1')."'])"; }			
			if(get_option('essl_exclude_begin_2')){ $essl_exclude_begin_2=":not([href^='".get_option('essl_exclude_begin_2')."'])"; }			
			if(get_option('essl_exclude_begin_3')){ $essl_exclude_begin_3=":not([href^='".get_option('essl_exclude_begin_3')."'])"; }			
			if(get_option('essl_exclude_begin_4')){ $essl_exclude_begin_4=":not([href^='".get_option('essl_exclude_begin_4')."'])"; }			
			if(get_option('essl_exclude_begin_5')){ $essl_exclude_begin_5=":not([href^='".get_option('essl_exclude_begin_5')."'])"; }			
			if(get_option('essl_exclude_match_1')){ $essl_exclude_match_1=":not([href='".get_option('essl_exclude_match_1')."'])";}			
			if(get_option('essl_exclude_match_2')){ $essl_exclude_match_2=":not([href='".get_option('essl_exclude_match_2')."'])";}			
			if(get_option('essl_exclude_match_3')){ $essl_exclude_match_3=":not([href='".get_option('essl_exclude_match_3')."'])";}			
			if(get_option('essl_exclude_match_4')){ $essl_exclude_match_4=":not([href='".get_option('essl_exclude_match_4')."'])";}			
			if(get_option('essl_exclude_match_5')){ $essl_exclude_match_5=":not([href='".get_option('essl_exclude_match_5')."'])";}						
			$essl_exclude_begin= $essl_exclude_begin_1. $essl_exclude_begin_2. $essl_exclude_begin_3. $essl_exclude_begin_4. $essl_exclude_begin_5;
			$essl_exclude_match= $essl_exclude_match_1. $essl_exclude_match_2. $essl_exclude_match_3. $essl_exclude_match_4. $essl_exclude_match_5;		

			if(get_option('enable_essl_aggressive')=='1'){ ?>	
			<script type="text/javascript">
				jQuery.noConflict();
				(function($){
				  
					var jump=function(e)
					{
					   if (e){
						   var target = $(this).attr("href");
					   }else{
						   var target = location.hash;
					   }
					   
						var scrollToPosition = $(target).offset().top - <?php if (get_option('essl_offset')!='') {echo get_option('essl_offset');} else {echo '20';} ?>;
					
					   $('html,body').animate({scrollTop: scrollToPosition },<?php if (get_option('essl_speed')!='') {echo get_option('essl_speed');} else {echo '900';} ?> ,'<?php echo  get_option('essl_easing','easeInQuint');?>' );

					}

					$('html, body').hide()

					$(document).ready(function()
					{
						$("area[href*=#],a[href*=#]:not([href=#]):not([href^='#tab']):not([href^='#quicktab']):not([href^='#pane'])<?php if($essl_exclude_begin) echo $essl_exclude_begin; ?><?php if($essl_exclude_match) echo $essl_exclude_match; ?>").bind("click", jump);

						if (location.hash){
							setTimeout(function(){
								$('html, body').scrollTop(0).show()
								jump()
							}, 0);
						}else{
						  $('html, body').show()
						}
					});
				  
				})(jQuery)
			</script>
				<?php  } else {  ?>
			<script type="text/javascript">
				jQuery.noConflict();
				(function( $ ) {
					$(function() {
						// More code using $ as alias to jQuery
						$("area[href*=#],a[href*=#]:not([href=#]):not([href^='#tab']):not([href^='#quicktab']):not([href^='#pane'])<?php if($essl_exclude_begin) echo $essl_exclude_begin; ?><?php if($essl_exclude_match) echo $essl_exclude_match; ?>").click(function() {
							if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
								var target = $(this.hash);
								target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
								if (target.length) {
								$('html,body').animate({
								scrollTop: target.offset().top - <?php if (get_option('essl_offset')!='') {echo get_option('essl_offset');} else {echo '20';} ?>  
								},<?php if (get_option('essl_speed')!='') {echo get_option('essl_speed');} else {echo '900';} ?> ,'<?php echo  get_option('essl_easing','easeInQuint');?>');
								return false;
								}
							}
						});
					});
				})(jQuery);	
			</script>				
				<?php }	
		}					
	}	
endif;