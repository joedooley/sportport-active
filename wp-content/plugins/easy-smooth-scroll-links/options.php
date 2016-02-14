<div class="wrap">
    <?php screen_icon(); ?> 
	<form action="options.php" method="post" id="<?php echo $plugin_id; ?>_options_form" name="<?php echo $plugin_id; ?>_options_form">
 
	<?php settings_fields($plugin_id.'_options'); ?>
    
    <h2>Easy Smooth Scroll Links &raquo; Settings</h2>
    <table class="widefat">
		<thead>
		   <tr>
			 <th><a target="_blank" href="http://www.jeriffcheng.com/wordpress-plugins/easy-smooth-scroll-links">FAQs</a> - <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/easy-smooth-scroll-links?filter=5#postform">Rate it</a> - <a target="_blank" href="http://wordpress.org/support/plugin/easy-smooth-scroll-links">Support Forum</a>    ---- Suggestions? <a target="_blank" href="http://www.jeriffcheng.com/contact">Contact Me</a><br><br><strong  style="color: #ff0000;">Everyone should Read these articles: <br><a target="_blank" href="http://www.jeriffcheng.com/html-5-using-attribute-id-instead-of-attribute-name-for-anchors.html">HTML 5 – Using attribute ‘id’ instead of attribute ‘name’ for Anchors</a></strong></th>
			 <th>
		</thead>

		<tbody>
		   <tr>
			 <td style="padding:5px;font-family:Verdana, Geneva, sans-serif;color:#666;">
                 <label for="essl_speed">
                     <p>Scroll Speed ( smaller number, faster, default is 900 )</p>
                     <p><input  size="10" id="speed" type="text" name="essl_speed" value="<?php echo get_option('essl_speed'); ?>" /></p>
                 </label>
             </td>
		   </tr>
		   <tr>
			 <td style="padding:5px;font-family:Verdana, Geneva, sans-serif;color:#666;">
                 <label for="essl_offset">
                     <p>Offset ( default is 20 )</p>
                     <p><input  size="10"  id="offset" type="text" name="essl_offset" value="<?php echo get_option('essl_offset'); ?>" /></p>
                 </label>
             </td>
		   </tr>
		   <tr>
			 <td style="padding:5px;font-family:Verdana, Geneva, sans-serif;color:#666;">
                 <label for="essl_easing">
                     <p>Scrolling Animation Effects ( default is easeInQuint ) </p>
                     <p>
<select name="essl_easing">
    <option disabled="disabled" >Choose an Animation Effects</option>
    <option name="essl_easing" value="easeInBounce" <?php echo (get_option('essl_easing') == "easeInBounce") ? 'selected="selected"' : ''; ?>>easeInBounce</option>
    <option name="essl_easing" value="easeOutBounce"  <?php echo (get_option('essl_easing') == "easeOutBounce") ? 'selected="selected"' : ''; ?>>easeOutBounce</option>
    <option name="essl_easing" value="easeInOutBounce"  <?php echo (get_option('essl_easing') == "easeInOutBounce") ? 'selected="selected"' : ''; ?>>easeInOutBounce</option>
    <option name="essl_easing" value="easeInElastic"  <?php echo (get_option('essl_easing') == "easeInElastic") ? 'selected="selected"' : ''; ?>>easeInElastic</option>
    <option name="essl_easing" value="easeOutElastic"  <?php echo (get_option('essl_easing') == "easeOutElastic") ? 'selected="selected"' : ''; ?>>easeOutElastic</option>
    <option name="essl_easing" value="easeInOutElastic"  <?php echo (get_option('essl_easing') == "easeInOutElastic") ? 'selected="selected"' : ''; ?>>easeInOutElastic</option>
    <option name="essl_easing" value="easeInCirc"  <?php echo (get_option('essl_easing') == "easeInCirc") ? 'selected="selected"' : ''; ?>>easeInCirc</option>
    <option name="essl_easing" value="easeOutCirc"  <?php echo (get_option('essl_easing') == "easeOutCirc") ? 'selected="selected"' : ''; ?>>easeOutCirc</option>
    <option name="essl_easing" value="easeInOutCirc"  <?php echo (get_option('essl_easing') == "easeInOutCirc") ? 'selected="selected"' : ''; ?>>easeInOutCirc</option>
    <option name="essl_easing" value="easeInBack"  <?php echo (get_option('essl_easing') == "easeInBack") ? 'selected="selected"' : ''; ?>>easeInBack</option>
    <option name="essl_easing" value="easeOutBack"  <?php echo (get_option('essl_easing') == "easeOutBack") ? 'selected="selected"' : ''; ?>>easeOutBack</option>
    <option name="essl_easing" value="easeInOutBack"  <?php echo (get_option('essl_easing') == "easeInOutBack") ? 'selected="selected"' : ''; ?>>easeInOutBack</option>
    <option name="essl_easing" value="easeInQuint"  <?php echo (get_option('essl_easing') == "easeInQuint") ? 'selected="selected"' : ''; ?>>easeInQuint</option>
    <option name="essl_easing" value="easeOutQuint"  <?php echo (get_option('essl_easing') == "easeOutQuint") ? 'selected="selected"' : ''; ?>>easeOutQuint</option>
    <option name="essl_easing" value="easeInOutQuint"  <?php echo (get_option('essl_easing') == "easeInOutQuint") ? 'selected="selected"' : ''; ?>>easeInOutQuint</option>
    <option name="essl_easing" value="easeInExpo"  <?php echo (get_option('essl_easing') == "easeInExpo") ? 'selected="selected"' : ''; ?>>easeInExpo</option>
    <option name="essl_easing" value="easeOutExpo"  <?php echo (get_option('essl_easing') == "easeOutExpo") ? 'selected="selected"' : ''; ?>>easeOutExpo</option>
    <option name="essl_easing" value="easeInOutExpo"  <?php echo (get_option('essl_easing') == "easeInOutExpo") ? 'selected="selected"' : ''; ?>>easeInOutExpo</option>
    <option name="essl_easing" value="easeInCubic"  <?php echo (get_option('essl_easing') == "easeInCubic") ? 'selected="selected"' : ''; ?>>easeInCubic</option>
    <option name="essl_easing" value="easeOutCubic"  <?php echo (get_option('essl_easing') == "easeOutCubic") ? 'selected="selected"' : ''; ?>>easeOutCubic</option>
    <option name="essl_easing" value="easeInOutCubic"  <?php echo (get_option('essl_easing') == "easeInOutCubic") ? 'selected="selected"' : ''; ?>>easeInOutCubic</option>
    <option name="essl_easing" value="easeInQuart"  <?php echo (get_option('essl_easing') == "easeInQuart") ? 'selected="selected"' : ''; ?>>easeInQuart</option>
    <option name="essl_easing" value="easeOutQuart"  <?php echo (get_option('essl_easing') == "easeOutQuart") ? 'selected="selected"' : ''; ?>>easeOutQuart</option>
    <option name="essl_easing" value="easeInOutQuart"  <?php echo (get_option('essl_easing') == "easeInOutQuart") ? 'selected="selected"' : ''; ?>>easeInOutQuart</option>
    <option name="essl_easing" value="easeInSine"  <?php echo (get_option('essl_easing') == "easeInSine") ? 'selected="selected"' : ''; ?>>easeInSine</option>
    <option name="essl_easing" value="easeOutSine"  <?php echo (get_option('essl_easing') == "easeOutSine") ? 'selected="selected"' : ''; ?>>easeOutSine</option>
    <option name="essl_easing" value="easeInOutSine"  <?php echo (get_option('essl_easing') == "easeInOutSine") ? 'selected="selected"' : ''; ?>>easeInOutSine</option>
    <option name="essl_easing" value="easeInQuad"  <?php echo (get_option('essl_easing') == "easeInQuad") ? 'selected="selected"' : ''; ?>>easeInQuad</option>
    <option name="essl_easing" value="easeOutQuad"  <?php echo (get_option('essl_easing') == "easeOutQuad") ? 'selected="selected"' : ''; ?>>easeOutQuad</option>
    <option name="essl_easing" value="easeInOutQuad"  <?php echo (get_option('essl_easing') == "easeInOutQuad") ? 'selected="selected"' : ''; ?>>easeInOutQuad</option>
</select>
					 </p>
                 </label>
             </td>
		   </tr>	


<tr>  
	<td style="padding:5px;font-family:Verdana, Geneva, sans-serif;color:#666;">            
			<label for="essl_exclude_begin"><p>Exclude Anchors Beginning With ( default is #tab   #quicktab  #pane )
				</p> 
				<input id="essl_exclude_begin_1" type="text" name="essl_exclude_begin_1"  placeholder="#beginwith1" value="<?php  echo get_option('essl_exclude_begin_1'); ?>" /> 
				<input id="essl_exclude_begin_2" type="text" name="essl_exclude_begin_2"  placeholder="#beginwith2" value="<?php  echo get_option('essl_exclude_begin_2'); ?>" /> 
				<input id="essl_exclude_begin_3" type="text" name="essl_exclude_begin_3"  placeholder="#beginwith3" value="<?php  echo get_option('essl_exclude_begin_3'); ?>" /> 
				<input id="essl_exclude_begin_4" type="text" name="essl_exclude_begin_4"  placeholder="#beginwith4" value="<?php  echo get_option('essl_exclude_begin_4'); ?>" /> 
				<input id="essl_exclude_begin_5" type="text" name="essl_exclude_begin_5"  placeholder="#beginwith5" value="<?php  echo get_option('essl_exclude_begin_5'); ?>" />
				</p>                 
			</label>             
	</td>    
</tr>        

<tr>  
	<td style="padding:5px;font-family:Verdana, Geneva, sans-serif;color:#666;">                 
		<label for="essl_offset"><p>Exclude Anchors Exactly Match</p>
			<p>
			<input id="offset" type="text" name="essl_exclude_match_1"  placeholder="#exactmatch1" value="<?php echo get_option('essl_exclude_match_1'); ?>" />   
			<input id="offset" type="text" name="essl_exclude_match_2"  placeholder="#exactmatch2" value="<?php echo get_option('essl_exclude_match_2'); ?>" />   
			<input id="offset" type="text" name="essl_exclude_match_3"  placeholder="#exactmatch3" value="<?php echo get_option('essl_exclude_match_3'); ?>" />   
			<input id="offset" type="text" name="essl_exclude_match_4"  placeholder="#exactmatch4" value="<?php echo get_option('essl_exclude_match_4'); ?>" />   
			<input id="offset" type="text" name="essl_exclude_match_5"  placeholder="#exactmatch5" value="<?php echo get_option('essl_exclude_match_5'); ?>" />
			</p>
		</label>
	</td>
</tr>

<tr>
	<td style="padding:25px;font-family:Verdana, Geneva, sans-serif;color:#666;">	 
		<label><strong>Enable Scrolling from One Page to Another ?</strong> </label><input type="checkbox" name="enable_essl_aggressive" value="1"<?php checked(1,get_option('enable_essl_aggressive'));?> /><br> <strong>Warning</strong>: This feature is experimental, might cause conflict with some slider plugins, or blank pages.
	</td>
</tr>
		   
		</tbody>
		

		<tfoot>
		   <tr>
			 <th><input type="submit" name="submit" value="Save Settings" class="button button-primary" onClick="return empty()"  /></th>
		   </tr>
		</tfoot>
	</table>

	</form>
	
	
    
   
</div>