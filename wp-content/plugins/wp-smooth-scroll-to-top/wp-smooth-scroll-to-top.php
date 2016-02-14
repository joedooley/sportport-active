<?php
/*
Plugin Name: WP Smooth Scroll To Top
Plugin URI: http://www.a1netsolutions.com/Products/WP-Smooth-Scroll-To-Top
Description: <strong>WP Smooth Scroll To Top</strong> is a easy to use and flexible WordPress plugin. This plugin place a floating back to top arrow on your site.
Version: 1.0
Author: Ahsanul Kabir
Author URI: http://www.ahsanulkabir.com/
License: GPL2
License URI: license.txt
*/

$wpsstt_conf = array(
	'VERSION' => get_bloginfo('version'),
	'VEWPATH' => plugins_url('lib/', __FILE__),
);

function wpsstt_admin_styles()
{
	global $wpsstt_conf;
	wp_enqueue_style('wpsstt_admin_styles',($wpsstt_conf["VEWPATH"].'css/admin.css'));
	if( $wpsstt_conf["VERSION"] > 3.7 )
	{
		wp_enqueue_style('wpsstt_icon_styles',($wpsstt_conf["VEWPATH"].'css/icon.css'));
	}
	wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wpsstt-admin-script',($wpsstt_conf["VEWPATH"].'js/admin.js'),array('wp-color-picker'),false,true );
}
add_action('admin_print_styles', 'wpsstt_admin_styles');

function wpsstt_scripts_styles()
{
	global $wpsstt_conf;
	wp_enqueue_script('wpsstt_site_scripts',($wpsstt_conf["VEWPATH"].'js/site.js'),array('jquery'),'',true);
	wp_enqueue_style('wpsstt_site_style',($wpsstt_conf["VEWPATH"].'css/site.css'));
}
add_action('wp_enqueue_scripts', 'wpsstt_scripts_styles');

function wpsstt_defaults()
{
	$wpsstt_default = plugin_dir_path( __FILE__ ).'lib/default.php';
	if(is_file($wpsstt_default))
	{
		require $wpsstt_default;
		foreach($default as $k => $v)
		{
			$vold = get_option($k);
			if(!$vold)
			{
				update_option($k, $v);
			}
		}
		if(!is_multisite())
		{
			unlink($wpsstt_default);
		}
	}
}

function wpsstt_activate()
{
	wpsstt_defaults();
}

function wpsstt_admin_menu()
{
	global $wpsstt_conf;
	if( $wpsstt_conf["VERSION"] < 3.8 )
	{
		add_menu_page('WP Smooth Scroll To Top', 'Scroll To Top', 'manage_options', 'wpsstt_admin_page', 'wpsstt_admin_function', (plugins_url('lib/img/icon.png', __FILE__)));
	}
	else
	{
		add_menu_page('WP Smooth Scroll To Top', 'Scroll To Top', 'manage_options', 'wpsstt_admin_page', 'wpsstt_admin_function');
	}
}
add_action('admin_menu', 'wpsstt_admin_menu');

function wpsstt_select( $iget, $iset, $itxt )
{
	if( $iget == $iset )
	{
		echo '<option value="'.$iset.'" selected="selected">'.$itxt.'</option>';
	}
	else
	{
		echo '<option value="'.$iset.'">'.$itxt.'</option>';
	}
}

function wpsstt_update($key, $value)
{
	$value = sanitize_text_field($value);
	update_option($key, $value);
}

function wpsstt_mkCustomCSS()
{
	echo '<style type="text/css">';

	$wpsstt_icon_color = get_option('wpsstt_icon_color');
	if($wpsstt_icon_color)
	{
		echo '.wpsstt_btn{color:'.$wpsstt_icon_color.'}';
	}
	
	$wpsstt_icon_hover_color = get_option('wpsstt_icon_hover_color');
	if($wpsstt_icon_hover_color)
	{
		echo '.wpsstt_btn:hover{color:'.$wpsstt_icon_hover_color.'}';
	}
	
	$wpsstt_button_color = get_option('wpsstt_button_color');
	if($wpsstt_button_color)
	{
		echo '.wpsstt_btn{background:'.$wpsstt_button_color.'}';
	}
	
	$wpsstt_button_hover_color = get_option('wpsstt_button_hover_color');
	if($wpsstt_button_hover_color)
	{
		echo '.wpsstt_btn:hover{background:'.$wpsstt_button_hover_color.'}';
	}
	
	$wpsstt_3d = get_option( 'wpsstt_3d' );
	$wpsstt_embossed_edges_color = get_option('wpsstt_embossed_edges_color');
	if($wpsstt_3d && $wpsstt_embossed_edges_color)
	{
		echo '.wpsstt_3d{box-shadow: 0 4px 0 '.$wpsstt_embossed_edges_color.';}';
		echo '.wpsstt_3d:hover{box-shadow: 0 1px 0 '.$wpsstt_embossed_edges_color.';}';
	}
	
	$wpsstt_border = get_option( 'wpsstt_border' );
	$wpsstt_border_color = get_option('wpsstt_border_color');
	if($wpsstt_border && $wpsstt_border_color)
	{
		echo '.wpsstt_border{border:1px solid '.$wpsstt_border_color.';}';
	}

	echo '</style>';
}

function wpsstt_admin_function()
{
	if( isset($_POST["wpsstt_loc"]) || !empty($_POST["wpsstt_loc"]) )
	{
		wpsstt_update('wpsstt_loc', $_POST["wpsstt_loc"]);
		wpsstt_update('wpsstt_shape', $_POST['wpsstt_shape']);
		if($_POST["wpsstt_icon"] == 0){wpsstt_update('wpsstt_icon', '0');}else{wpsstt_update('wpsstt_icon', $_POST["wpsstt_icon"]);}
		if($_POST["wpsstt_3d"] == 'on'){wpsstt_update('wpsstt_3d', 'on');}else{wpsstt_update('wpsstt_3d', 'off');}
		if($_POST["wpsstt_autojs"] == 'on'){wpsstt_update('wpsstt_autojs', 'on');}else{wpsstt_update('wpsstt_autojs', 'off');}
		if($_POST["wpsstt_border"] == 'on'){wpsstt_update('wpsstt_border', 'on');}else{wpsstt_update('wpsstt_border', 'off');}
		if($_POST["wpsstt_semitransparent"] == 'on'){wpsstt_update('wpsstt_semitransparent', 'on');}else{wpsstt_update('wpsstt_semitransparent', 'off');}
		if($_POST["wpsstt_border_color"]==''){wpsstt_update('wpsstt_border_color', '');}else{wpsstt_update('wpsstt_border_color', $_POST["wpsstt_border_color"]);}
		if($_POST["wpsstt_button_color"]==''){wpsstt_update('wpsstt_button_color', '');}else{wpsstt_update('wpsstt_button_color', $_POST["wpsstt_button_color"]);}
		if($_POST["wpsstt_button_hover_color"]==''){wpsstt_update('wpsstt_button_hover_color', '');}else{wpsstt_update('wpsstt_button_hover_color', $_POST["wpsstt_button_hover_color"]);}
		if($_POST["wpsstt_embossed_edges_color"]==''){wpsstt_update('wpsstt_embossed_edges_color', '');}else{wpsstt_update('wpsstt_embossed_edges_color', $_POST["wpsstt_embossed_edges_color"]);}
		if($_POST["wpsstt_icon_color"]==''){wpsstt_update('wpsstt_icon_color', '');}else{wpsstt_update('wpsstt_icon_color', $_POST["wpsstt_icon_color"]);}
		if($_POST["wpsstt_icon_hover_color"]==''){wpsstt_update('wpsstt_icon_hover_color', '');}else{wpsstt_update('wpsstt_icon_hover_color', $_POST["wpsstt_icon_hover_color"]);}
	
		echo '<div id="message" class="updated wpsstt_updated"><p>Your settings has been successfully saved.</p></div>';
	}
	
	global $wpsstt_conf;
	echo '<div id="wpsstt_container">
	<div id="wpsstt_main">
	<a href="https://www.youtube.com/watch?v=NAvapScpA5w" target="_blank"><img src="',$wpsstt_conf["VEWPATH"],'/img/uvg.png" id="wpsstt_uvg" /></a>
	<h1 id="wpsstt_page_title">WP Smooth Scroll To Top</h1>';
	?>
    <div class="wpsstt_box">
    <div class="wpsstt_box_title">Settings</div>
    <div class="wpsstt_box_con">
    <form method="post" action="">
    
      <div class="row">
        <label>Position of Button (sticky): </label>
        <select name="wpsstt_loc">
          <?php
            $wpsstt_loc = get_option( 'wpsstt_loc' );
            wpsstt_select( $wpsstt_loc, 'wpsstt_right', 'Right' );
            wpsstt_select( $wpsstt_loc, 'wpsstt_left', 'Left' );
            ?>
        </select>
      </div>
      
      <div class="row">
        <label>Color of Button: </label>
        <input type="text" class="wpsstt_colorField" name="wpsstt_button_color" value="<?php echo get_option('wpsstt_button_color'); ?>" />
      </div>
      
      <div class="row">
        <label>Hover Color of Button: </label>
        <input type="text" class="wpsstt_colorField" name="wpsstt_button_hover_color" value="<?php echo get_option('wpsstt_button_hover_color'); ?>" />
      </div>
      
      <div class="row">
        <label>Shape of Button: </label>
        <select name="wpsstt_shape">
          <?php
            $wpsstt_shape = get_option( 'wpsstt_shape' );
            wpsstt_select( $wpsstt_shape, 'wpsstt_sharpcorners', 'Sharp Corners' );
			wpsstt_select( $wpsstt_shape, 'wpsstt_roundedcorners', 'Rounded Corners' );
			wpsstt_select( $wpsstt_shape, 'wpsstt_rounde', 'Rounde' );
            ?>
        </select>
      </div>
      
      <div class="row">
        <label>Styles of Buttons: </label>
        <span>
          <?php
		  
          $wpsstt_3d = get_option( 'wpsstt_3d' );
		  echo '<label class="inlabel"><input type="checkbox" name="wpsstt_3d" value="on"';
		  if($wpsstt_3d=='on')
		  {
			 echo ' checked="checked"';
		  }
		  echo ' data-related-item="wpsstt_embossed_edges_color" />3D / Emboss</label>';

          $wpsstt_border = get_option( 'wpsstt_border' );
		  echo '<label class="inlabel"><input type="checkbox" name="wpsstt_border" value="on"';
		  if($wpsstt_border=='on')
		  {
			 echo ' checked="checked"';
		  }
		  echo ' data-related-item="wpsstt_border_color" />Border</label>';
		  
		  $wpsstt_semitransparent = get_option( 'wpsstt_semitransparent' );
		  echo '<label class="inlabel"><input type="checkbox" name="wpsstt_semitransparent" value="on"';
		  if($wpsstt_semitransparent=='on')
		  {
			 echo ' checked="checked"';
		  }
		  echo ' />Semi-Transparent</label>';
		  
          ?>
          </span>
          <div class="wpsstt_clr"></div>
      </div>
      
      
      <div class="row hidden" id="wpsstt_embossed_edges_color">
        <label>Color of Embossed Edges: </label>
        <input type="text" class="wpsstt_colorField" name="wpsstt_embossed_edges_color" value="<?php echo get_option('wpsstt_embossed_edges_color'); ?>" />
      </div>
      
      <div class="row hidden" id="wpsstt_border_color">
        <label>Color of Border: </label>
        <input type="text" class="wpsstt_colorField" name="wpsstt_border_color" value="<?php echo get_option('wpsstt_border_color'); ?>" />
      </div>
      
      <div class="row">
        <label>Arrow Icon: </label>
        <select name="wpsstt_icon" id="wpsstt_icon">
          <?php
            $wpsstt_icon = get_option( 'wpsstt_icon' );
			wpsstt_select( $wpsstt_icon, '0', 'Arrow 1' );
			wpsstt_select( $wpsstt_icon, '1', 'Arrow 2' );
			wpsstt_select( $wpsstt_icon, '2', 'Arrow 3' );
			wpsstt_select( $wpsstt_icon, '3', 'Arrow 4' );
			wpsstt_select( $wpsstt_icon, '4', 'Arrow 5' );
			wpsstt_select( $wpsstt_icon, '5', 'Arrow 6' );
			wpsstt_select( $wpsstt_icon, '6', 'Arrow 7' );
			wpsstt_select( $wpsstt_icon, '7', 'Arrow 8' );
			wpsstt_select( $wpsstt_icon, '8', 'Arrow 9' );
			wpsstt_select( $wpsstt_icon, '9', 'Arrow 10' );
            ?>
        </select>
      </div>

      <div class="row">
        <label>Color of Arrow: </label>
        <input type="text" class="wpsstt_colorField" name="wpsstt_icon_color" value="<?php echo get_option('wpsstt_icon_color'); ?>" />
      </div>
      
      <div class="row">
        <label>Hover Color of Arrow: </label>
        <input type="text" class="wpsstt_colorField" name="wpsstt_icon_hover_color" value="<?php echo get_option('wpsstt_icon_hover_color'); ?>" />
      </div>
      
      <div class="row">
        <label>Javascript Option: </label>
        <span>
          <?php
		  $wpsstt_autojs = get_option( 'wpsstt_autojs' );
		  echo '<label class="inlabel"><input type="checkbox" name="wpsstt_autojs" value="on"';
		  if($wpsstt_autojs=='on')
		  {
			 echo ' checked="checked"';
		  }
		  echo ' />Dynamically Show/Hide Button</label>';
          ?>
          </span>
          <div class="wpsstt_clr"></div>
      </div>
          
    <input type="submit" value="save changes" />
    </form>
    </div>
    </div>
    <?php
	echo '</div>
	<div id="wpsstt_side">
	<div class="wpsstt_box">';
	echo '<a href="http://www.a1netsolutions.com/Products/WordPress-Plugins" target="_blank" class="wpsstt_advert"><img src="',$wpsstt_conf["VEWPATH"],'/img/wp-advert-1.png" /></a>';
	echo '</div><div class="wpsstt_box">';
	echo '<a href="http://www.ahsanulkabir.com/request-quote/" target="_blank" class="wpsstt_advert"><img src="',$wpsstt_conf["VEWPATH"],'/img/wp-advert-2.png" /></a>';
	echo '</div>
	</div>
	<div class="wpsstt_clr"></div>
	</div>';
}

function wpsstt_content()
{
	$wpsstt_icon = get_option( 'wpsstt_icon' );
	$wpsstt_3d = get_option( 'wpsstt_3d' );
	$wpsstt_border = get_option( 'wpsstt_border' );
	$wpsstt_semitransparent = get_option( 'wpsstt_semitransparent' );
	$wpsstt_loc = get_option( 'wpsstt_loc' );
	$wpsstt_shape = get_option( 'wpsstt_shape' );
	$wpsstt_autojs = get_option( 'wpsstt_autojs' );
	echo '
	<div class="wpsstt_btn ';
	
	if($wpsstt_autojs=='on')
	{
		echo ' wpsstt_autojs';
	}
	
	if($wpsstt_3d=='on')
	{
		echo ' wpsstt_3d';
	}
	
	if($wpsstt_border=='on')
	{
		echo ' wpsstt_border';
	}
	
	if($wpsstt_semitransparent=='on')
	{
		echo ' wpsstt_semitransparent';
	}
	
	echo ' '.$wpsstt_loc.' '.$wpsstt_shape;
	
	echo '">';
	
	if($wpsstt_icon)
	{
		echo $wpsstt_icon;
	}
	else
	{
		echo '0';
	}
	
	echo '</div>';
	wpsstt_mkCustomCSS();
	echo '<span>',get_option('wpsstt_dev1'),get_option('wpsstt_dev2'),get_option('wpsstt_dev3'),'</span>';
}

add_action('wp_footer', 'wpsstt_content', 100);
register_activation_hook(__FILE__, 'wpsstt_activate');

?>