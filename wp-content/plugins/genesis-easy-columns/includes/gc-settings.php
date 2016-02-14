<?php

function gc_admin_menu() {
    $page_title = 'Genesis Easy Columns';
    $menu_title = 'Easy Columns';
    $capability = 'manage_options';
    $menu_slug = 'gc-settings';
    $function = 'gc_myplugin_settings';
    add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
}
add_action('admin_menu', 'gc_admin_menu');

function gc_myplugin_settings() {

	global $gc_options;
	
	ob_start(); ?>
		<div style="padding-bottom:50px;">
			<form method="post" action="options.php">
				<?php settings_fields('gc_settings_group'); ?>
				<h2>Genesis Easy Columns Settings</h2>
				<h4>Non-Genesis Users Include Optional CSS</h4>
				<table>
					<tr>
						<td style="padding:15px;"><label class="description" for="gc_settings[enabled]">Enable Optional CSS: </label></td>
						<td style="padding:15px;"><input id="gc_settings[enabled]" name="gc_settings[enabled]" type="checkbox" value="1" <?php checked(1, isset($gc_options['enabled'])); ?> />
					</tr>
					<tr>
						<td style="padding:15px;"><input type="submit" class="button-primary" value="Save Options" /></td>
					</tr>
				</table>
			</form>
		</div>
		<div style="padding-top:50px;">
			<h4>WordPress Design & WordPress Themes</h4>
			<p>
				<a href="http://www.code96wd.com"><img src="http://www.code96wd.com/wp-content/uploads/2013/03/Code-96-WordPress-Design.png"></a>
			</p>
		</div>

<?php
	
	echo ob_get_clean();

}

function gc_register_settings() {
	register_setting('gc_settings_group', 'gc_settings');
}
add_action('admin_init', 'gc_register_settings');