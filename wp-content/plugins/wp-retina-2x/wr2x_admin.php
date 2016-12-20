<?php

include "common/meow_admin.php";

class WR2X_Admin extends Meow_Admin {

	public function __construct() {
		parent::__construct( 'wr2x', 'retina' );

		$method = get_option( 'wr2x_method', 666 );
		if ( $method == 666 )
			$this->initial_setup();

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'app_menu' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	function admin_notices() {
		if ( delete_transient( 'wr2x_flush_rules' ) ) {
			global $wp_rewrite;
			WR2X_Admin::generate_rewrite_rules( $wp_rewrite, true );
		}
	}

	function initial_setup() {
		$ignore_sizes = $this->old_getoption( 'ignore_sizes', 'wr2x_basics', array() );
		update_option( 'wr2x_ignore_sizes', $ignore_sizes, false );
		$auto_generate = $this->old_getoption( 'auto_generate', 'wr2x_basics', true );
		update_option( 'wr2x_auto_generate', $auto_generate, true );
		$method = $this->old_getoption( 'method', 'wr2x_advanced', 'Picturefill' );
		update_option( 'wr2x_method', $method, true );
		$full_size = $this->old_getoption( 'full_size', 'wr2x_basics', false );
		update_option( 'wr2x_full_size', $full_size, false );
		$disable_responsive = $this->old_getoption( 'disable_responsive', 'wr2x_basics', false );
		update_option( 'wr2x_disable_responsive', $disable_responsive, false );
		$disable_medium_large = $this->old_getoption( 'disable_medium_large', 'wr2x_basics', true );
		update_option( 'wr2x_disable_medium_large', $disable_medium_large, false );
		$debug = $this->old_getoption( 'debug', 'wr2x_advanced', false );
		update_option( 'wr2x_debug', $debug, false );
		$cdn_domain = $this->old_getoption( 'cdn_domain', 'wr2x_advanced', '' );
		update_option( 'wr2x_cdn_domain', $cdn_domain, false );
		$picturefill_keep_src = $this->old_getoption( 'picturefill_keep_src', 'wr2x_advanced', false );
		update_option( 'wr2x_picturefill_keep_src', $picturefill_keep_src, false );
		$picturefill_lazysizes = $this->old_getoption( 'picturefill_lazysizes', 'wr2x_advanced', false );
		update_option( 'wr2x_picturefill_lazysizes', $picturefill_lazysizes, false );
		$picturefill_noscript = $this->old_getoption( 'picturefill_noscript', 'wr2x_advanced', false );
		update_option( 'wr2x_picturefill_noscript', $picturefill_noscript, false );
		$hide_retina_column = $this->old_getoption( 'hide_retina_column', 'wr2x_advanced', false );
		update_option( 'wr2x_hide_retina_column', $hide_retina_column, false );
		$hide_retina_dashboard = $this->old_getoption( 'hide_retina_dashboard', 'wr2x_advanced', false );
		update_option( 'wr2x_hide_retina_dashboard', $hide_retina_dashboard, false );
		delete_option( 'wr2x_basics' );
		delete_option( 'wr2x_advanced' );
		delete_option( 'wr2x_pro' );
	}

	static function generate_rewrite_rules( $wp_rewrite, $flush = false ) {
		global $wp_rewrite;
		$method = get_option( "wr2x_method" );
		if ( $method == "Retina-Images" ) {

			// MODIFICATION: docwhat
			// get_home_url() -> trailingslashit(site_url())
			// REFERENCE: http://wordpress.org/support/topic/plugin-wp-retina-2x-htaccess-generated-with-incorrect-rewriterule

			// MODIFICATION BY h4ir9
			// .*\.(jpg|jpeg|gif|png|bmp) -> (.+.(?:jpe?g|gif|png))
			// REFERENCE: http://wordpress.org/support/topic/great-but-needs-a-little-update

			$handlerurl = str_replace( trailingslashit( site_url()), '', plugins_url( 'wr2x_image.php', __FILE__ ) );
			add_rewrite_rule( '(.+.(?:jpe?g|gif|png))', $handlerurl, 'top' );
		}
		if ( $flush == true ) {
			$wp_rewrite->flush_rules();
		}
	}

	function common_url( $file ) {
		return trailingslashit( plugin_dir_url( __FILE__ ) ) . 'common/' . $file;
	}

	function app_menu() {

		// SUBMENU > Settings
		add_submenu_page( 'meowapps-main-menu', 'Retina', 'Retina', 'manage_options',
			'wr2x_settings-menu', array( $this, 'admin_settings' ) );

			// SUBMENU > Settings > Basic Settings
			add_settings_section( 'wr2x_settings', null, null, 'wr2x_settings-menu' );
			add_settings_field( 'wr2x_ignore_sizes', "Disabled Sizes",
				array( $this, 'admin_ignore_sizes_callback' ),
				'wr2x_settings-menu', 'wr2x_settings' );
			add_settings_field( 'wr2x_auto_generate', "Auto Generate",
				array( $this, 'admin_auto_generate_callback' ),
				'wr2x_settings-menu', 'wr2x_settings' );
			add_settings_field( 'wr2x_method', "Method",
				array( $this, 'admin_method_callback' ),
				'wr2x_settings-menu', 'wr2x_settings' );
			add_settings_field( 'wr2x_full_size', "Full Size Retina<br />(Pro)",
				array( $this, 'admin_full_size_callback' ),
				'wr2x_settings-menu', 'wr2x_settings' );
			add_settings_field( 'wr2x_method', "Method",
				array( $this, 'admin_method_callback' ),
				'wr2x_settings-menu', 'wr2x_settings' );

			register_setting( 'wr2x_settings', 'wr2x_ignore_sizes' );
			register_setting( 'wr2x_settings', 'wr2x_auto_generate' );
			register_setting( 'wr2x_settings', 'wr2x_full_size' );
			register_setting( 'wr2x_settings', 'wr2x_method' );

			// SUBMENU > Settings > Advanced Settings
			add_settings_section( 'wr2x_advanced_settings', null, null, 'wr2x_advanced_settings-menu' );
			add_settings_field( 'wr2x_disable_responsive', "Disable Responsive",
				array( $this, 'admin_disable_responsive_callback' ),
				'wr2x_advanced_settings-menu', 'wr2x_advanced_settings' );
			add_settings_field( 'wr2x_disable_medium_large', "Disable Medium Large",
				array( $this, 'admin_disable_medium_large_callback' ),
				'wr2x_advanced_settings-menu', 'wr2x_advanced_settings' );
			add_settings_field( 'wr2x_cdn_domain', "Custom CDN Domain<br />(Pro)",
				array( $this, 'admin_cdn_domain_callback' ),
				'wr2x_advanced_settings-menu', 'wr2x_advanced_settings' );
			add_settings_field( 'wr2x_over_http_check', "Over HTTP Check<br />(Pro)",
				array( $this, 'admin_over_http_check_callback' ),
				'wr2x_advanced_settings-menu', 'wr2x_advanced_settings' );
			add_settings_field( 'wr2x_debug', "Debug",
				array( $this, 'admin_debug_callback' ),
				'wr2x_advanced_settings-menu', 'wr2x_advanced_settings' );

			register_setting( 'wr2x_advanced_settings', 'wr2x_disable_responsive' );
			register_setting( 'wr2x_advanced_settings', 'wr2x_disable_medium_large' );
			register_setting( 'wr2x_advanced_settings', 'wr2x_cdn_domain' );
			register_setting( 'wr2x_advanced_settings', 'wr2x_over_http_check' );
			register_setting( 'wr2x_advanced_settings', 'wr2x_debug' );

			// SUBMENU > Settings > PictureFill
			add_settings_section( 'wr2x_picturefill_settings', null, null, 'wr2x_picturefill_settings-menu' );
			add_settings_field( 'wr2x_picturefill_keep_src', "Keep IMG SRC",
				array( $this, 'admin_picturefill_keep_src_callback' ),
				'wr2x_picturefill_settings-menu', 'wr2x_picturefill_settings' );
			add_settings_field( 'wr2x_picturefill_lazysizes', "Lazy Retina<br />(Pro)",
				array( $this, 'admin_picturefill_lazysizes_callback' ),
				'wr2x_picturefill_settings-menu', 'wr2x_picturefill_settings' );
			add_settings_field( 'wr2x_picturefill_css_background', "CSS Background<br />(Pro)",
				array( $this, 'admin_picturefill_css_background_callback' ),
				'wr2x_picturefill_settings-menu', 'wr2x_picturefill_settings' );
			add_settings_field( 'wr2x_picturefill_noscript', "Polyfill Script",
				array( $this, 'admin_picturefill_noscript_callback' ),
				'wr2x_picturefill_settings-menu', 'wr2x_picturefill_settings' );

			register_setting( 'wr2x_picturefill_settings', 'wr2x_picturefill_keep_src' );
			register_setting( 'wr2x_picturefill_settings', 'wr2x_picturefill_lazysizes' );
			register_setting( 'wr2x_picturefill_settings', 'wr2x_picturefill_css_background' );
			register_setting( 'wr2x_picturefill_settings', 'wr2x_picturefill_noscript' );

			// SUBMENU > Settings > Admin UI
			add_settings_section( 'wr2x_ui_settings', null, null, 'wr2x_ui_settings-menu' );
			add_settings_field( 'wr2x_hide_retina_column', "Retina Column",
				array( $this, 'admin_hide_retina_column_callback' ),
				'wr2x_ui_settings-menu', 'wr2x_ui_settings' );
			add_settings_field( 'wr2x_hide_retina_dashboard', "Retina Dashboard",
				array( $this, 'admin_hide_retina_dashboard_callback' ),
				'wr2x_ui_settings-menu', 'wr2x_ui_settings' );

			register_setting( 'wr2x_ui_settings', 'wr2x_hide_retina_column' );
			register_setting( 'wr2x_ui_settings', 'wr2x_hide_retina_dashboard' );
	}

	function admin_settings() {
		$method = get_option( "wr2x_method", 'Picturefill' );
		?>
		<div class="wrap">
			<?php echo $this->display_title( "WP Retina 2x" );  ?>

			<div class="meow-row">
				<div class="meow-box meow-col meow-span_2_of_2">
					<div class="inside">
						<?php
							if ( $method == 'none' )
								echo "<p><span>" . __( "Current method:", 'wp-retina-2x' ) . " <u>" . __( "None", 'wp-retina-2x' ) . "</u>.</span>";
							if ( $method == 'HTML Rewrite' )
								echo "<p><span>" . __( "Current method:", 'wp-retina-2x' ) . " <u>" . __( "HTML Rewrite", 'wp-retina-2x' ) . "</u>.</span>";
							if ( $method == 'retina.js' )
								echo "<p><span>" . __( "Current method:", 'wp-retina-2x' ) . " <u>" . __( "Retina.js", 'wp-retina-2x' ) . "</u>.</span>";
						  if ( $method == 'Picturefill' )
						      echo "<p><span>" . __( "Current method:", 'wp-retina-2x' ) . " <u>" . __( "PictureFill", 'wp-retina-2x' ) . "</u>.</span>";
						  if ( $method == 'Responsive' )
						      echo "<p><span>" . __( "Current method:", 'wp-retina-2x' ) . " <u>" . __( "Responsive Images", 'wp-retina-2x' ) . "</u>.</span>";
							if ( $method == 'Retina-Images' ) {
				        echo "<p><span>" . __( "Current method:", 'wp-retina-2x' ) . " <u>" . __( "HTML Rewrite", 'wp-retina-2x' ) . "</u>.</span>";
				        if ( defined( 'MULTISITE' ) && MULTISITE == true  ) {
				            if ( get_site_option( 'ms_files_rewriting' ) ) {
				                // MODIFICATION: Craig Foster
				                // 'ms_files_rewriting' support
				                echo " <span style='color: red;'>" . __( "By the way, you are using a <b>WordPress Multi-Site installation</b>! You must edit your .htaccess manually and add '<b>RewriteRule ^files/(.+) wp-content/plugins/wp-retina-2x/wr2x_image.php?ms=true&file=$1 [L]</b>' as the first RewriteRule if you want the server-side to work.", 'wp-retina-2x' ) . "</span>";
				            }
				            else
				                echo " <span style='color: red;'>" . __( "By the way, you are using a <b>WordPress Multi-Site installation</b>! You must edit your .htaccess manually and add '<b>RewriteRule ^(wp-content/.+\.(png|gif|jpg|jpeg|bmp|PNG|GIF|JPG|JPEG|BMP)) wp-content/plugins/wp-retina-2x/wr2x_image.php?ms=true&file=$1 [L]</b>' as the first RewriteRule if you want the server-side to work.", 'wp-retina-2x' ) . "</span>";
				        }
								echo "</p>";
								if ( !get_option('permalink_structure') )
									echo "<p><span style='color: red;'>" . __( "The permalinks are not enabled. They need to be enabled in order to use the server-side method.", 'wp-retina-2x' ) . "</span>";
							}
						?>
					</div>
				</div>
			</div>

			<div class="meow-row">
				<div class="meow-box meow-col meow-span_2_of_2">
					<h3>How to use</h3>
					<div class="inside">
						<?php echo _e( 'This plugin works out of the box, the default settings are the best for most installs. However, you should have a look at the <a target="_blank" href="https://meowapps.com/wp-retina-2x/tutorial/">tutorial</a>.', 'meow-gallery' ) ?>
					</div>
				</div>
			</div>

			<div class="meow-row">

					<div class="meow-col meow-span_1_of_2">

						<div class="meow-box">
							<h3>Basic Settings</h3>
							<div class="inside">
								<form method="post" action="options.php">
									<?php settings_fields( 'wr2x_settings' ); ?>
							    <?php do_settings_sections( 'wr2x_settings-menu' ); ?>
							    <?php submit_button(); ?>
								</form>
							</div>
						</div>

						<div class="meow-box">
							<h3>Advanced Settings</h3>
							<div class="inside">
								<form method="post" action="options.php">
									<?php settings_fields( 'wr2x_advanced_settings' ); ?>
							    <?php do_settings_sections( 'wr2x_advanced_settings-menu' ); ?>
							    <?php submit_button(); ?>
								</form>
							</div>
						</div>

					</div>

					<div class="meow-col meow-span_1_of_2">

						<?php $this->display_serialkey_box( "https://meowapps.com/wp-retina-2x/" ); ?>

						<?php if ( get_option( 'wr2x_method', 'none' ) == 'Picturefill' ): ?>
						<div class="meow-box">
							<h3>PictureFill</h3>
							<div class="inside">
								<form method="post" action="options.php">
									<?php settings_fields( 'wr2x_picturefill_settings' ); ?>
									<?php do_settings_sections( 'wr2x_picturefill_settings-menu' ); ?>
									<?php submit_button(); ?>
								</form>
							</div>
						</div>
						<?php endif; ?>

						<div class="meow-box">
							<h3>Admin UI</h3>
							<div class="inside">
								<form method="post" action="options.php">
									<?php settings_fields( 'wr2x_ui_settings' ); ?>
									<?php do_settings_sections( 'wr2x_ui_settings-menu' ); ?>
									<?php submit_button(); ?>
								</form>
							</div>
						</div>

					</div>

			</div>

		</div>
		<?php
	}

	/*
		OPTIONS CALLBACKS
	*/

	function admin_ignore_sizes_callback( $args ) {
		$ignore_sizes = get_option( 'wr2x_ignore_sizes' );
		if ( empty( $ignore_sizes ) )
			$ignore_sizes = array();
		$wpsizes = wr2x_get_image_sizes();
		$sizes = array();
		$html = "";
		foreach ( $wpsizes as $name => $attr ) {
			$html .= '<input type="checkbox" name="wr2x_ignore_sizes[' . $name . ']" value="1" ' .
				( array_key_exists( $name, $ignore_sizes ) ? 'checked' : '' ) . '/>' . sprintf( "<label><div style='float: left; text-align: right; margin-right: 5px; width: 20px;'>%s</div> <b>%s</b></label> <small>(Normal: %dx%d, Retina: %dx%d)</small>", Meow_Admin::size_shortname( $name ), $name, $attr['width'], $attr['height'], $attr['width'] * 2, $attr['height'] * 2 ) . '<br>';
		}
		$html .= '<br /><small class="description">'  .
			__( 'The selected sizes will <b>not</b> have their retina equivalent generated.', 'wp-retina-2x' ) . '</small>';
		echo $html;
	}

	function admin_auto_generate_callback( $args ) {
    $value = get_option( 'wr2x_auto_generate', null );
		$html = '<input type="checkbox" id="wr2x_auto_generate" name="wr2x_auto_generate" value="1" ' .
			checked( 1, get_option( 'wr2x_auto_generate' ), false ) . '/>';
    $html .= '<label>Generate on new upload and regenerate</label><br /><small>The <i>Disabled Sizes</i> will be skipped.</small>';
    echo $html;
  }

	function admin_disable_responsive_callback( $args ) {
    $value = get_option( 'wr2x_disable_responsive', null );
		$html = '<input type="checkbox" id="wr2x_disable_responsive" name="wr2x_disable_responsive" value="1" ' .
			checked( 1, get_option( 'wr2x_disable_responsive' ), false ) . '/>';
    $html .= '<label>Disable the Responsive Images feature.</label><br /><small>Get back control over your HTML if you need.</small>';
    echo $html;
  }

	function admin_disable_medium_large_callback( $args ) {
    $value = get_option( 'wr2x_disable_medium_large', null );
		$html = '<input type="checkbox" id="wr2x_disable_medium_large" name="wr2x_disable_medium_large" value="1" ' .
			checked( 1, get_option( 'wr2x_disable_medium_large' ), false ) . '/>';
    $html .= '<label>Remove the "Medium Large" image size.</label><br /><small>You probably don\'t need this.</small>';
    echo $html;
  }

	function admin_full_size_callback( $args ) {
    $value = get_option( 'wr2x_full_size', null );
		$html = '<input ' . disabled( $this->is_pro(), false, false ) . ' type="checkbox" id="wr2x_full_size" name="wr2x_full_size" value="1" ' .
			checked( 1, get_option( 'wr2x_full_size' ), false ) . '/>';
    $html .= '<label>Enable</label><br /><small>Checks for retina for full-size will be enabled and upload features made available in the <i>Retina Dashboard</i>.</small>';
    echo $html;
  }

	function admin_method_callback( $args ) {
    $value = get_option( 'wr2x_method', 'none' );
		$html = '<select id="wr2x_method" name="wr2x_method">
		  <option ' . selected( 'Picturefill', $value, false ) . 'value="Picturefill">Recommended: Picturefill</option>
		  <option ' . selected( 'Responsive', $value, false ) . 'value="Responsive">Responsive-Images (Native WP 4.4+)</option>
		  <option ' . selected( 'retina.js', $value, false ) . 'value="retina.js">Retina.js (Client-side)</option>
		  <option ' . selected( 'HTML Rewrite', $value, false ) . 'value="HTML Rewrite">HTML Rewrite</option>
			<option ' . selected( 'Retina-Images', $value, false ) . 'value="Retina-Images">Retina-Images</option>
			<option ' . selected( 'none', $value, false ) . 'value="none">None</option>
		</select><small><br />' . __( 'In all cases (including "None"), Retina support will be added to the Responsive Images. Check the <a target="_blank" href="http://meowapps.com/wp-retina-2x/retina-methods/">Retina Methods</a> page if you want to know more about those methods.', 'wp-retina-2x' ) . '</small>';
    echo $html;
  }

	function admin_debug_callback( $args ) {
    $value = get_option( 'wr2x_debug', null );
		$html = '<input type="checkbox" id="wr2x_debug" name="wr2x_debug" value="1" ' .
			checked( 1, get_option( 'wr2x_debug' ), false ) . '/>';
    $html .= __( '<label>Force Retina + Logging</label><br /><small>Displays retina and creates a <a href="' . plugins_url( "wp-retina-2x" ) . '/wp-retina-2x.log">log file</a> in the plugin folder.</small>', 'wp-retina-2x' );
    echo $html;
  }

	function admin_cdn_domain_callback( $args ) {
    $value = get_option( 'wr2x_cdn_domain', null );
		$html = '<input ' . disabled( $this->is_pro(), false, false ) . ' type="text" id="wr2x_cdn_domain" name="wr2x_cdn_domain" value="' . $value . '" />';
    $html .= __( '<br /><small>If not empty, your site domain will be replaced with this CDN domain (PictureFill and HTML Rewrite only).</small>', 'wp-retina-2x' );
    echo $html;
  }

	function admin_over_http_check_callback( $args ) {
    $value = get_option( 'wr2x_over_http_check', null );
		$html = '<input type="checkbox" id="wr2x_over_http_check" name="wr2x_over_http_check" value="1" ' .
			checked( 1, get_option( 'wr2x_over_http_check' ), false ) . '/>';
    $html .= __( '<label>Enable</label><br /><small>Normally, the plugin checks if the Retina files exists through your filesystem. With this option, it will check using HTTP requests, that will enable Retina on exotic WordPress installs and also for images hosted on different servers.</small>', 'wp-retina-2x' );
    echo $html;
  }

	function admin_picturefill_keep_src_callback( $args ) {
    $value = get_option( 'wr2x_picturefill_keep_src', null );
		$html = '<input type="checkbox" id="wr2x_picturefill_keep_src" name="wr2x_picturefill_keep_src" value="1" ' .
			checked( 1, get_option( 'wr2x_picturefill_keep_src' ), false ) . '/>';
    $html .= __( '<label>Enable</label><br /><small>With PictureFill, <b>src</b> tags are replaced by <b>src-set</b> tags and consequently search engines might not be able to find and reference those images. This option is better for SEO, but Retina devices will download both normal and retina. Lazy Retina option is recommended with this.</small>', 'wp-retina-2x' );
    echo $html;
  }

	function admin_picturefill_lazysizes_callback( $args ) {
    $value = get_option( 'wr2x_picturefill_lazysizes', null );
		$html = '<input ' . disabled( $this->is_pro(), false, false ) . ' type="checkbox" id="wr2x_picturefill_lazysizes"
			name="wr2x_picturefill_lazysizes" value="1" ' .
			checked( 1, get_option( 'wr2x_picturefill_lazysizes' ), false ) . '/>';
    $html .= __( '<label>Enabled</label><br /><small>Retina images will not be loaded until the visitor gets close to them. HTML will be rewritten and the lazysizes script will be also loaded. </small>', 'wp-retina-2x' );
    echo $html;
  }

	function admin_picturefill_noscript_callback( $args ) {
    $value = get_option( 'wr2x_picturefill_noscript', null );
		$html = '<input ' . disabled( $this->is_pro(), false, false ) . ' type="checkbox" id="wr2x_picturefill_noscript"
			name="wr2x_picturefill_noscript" value="1" ' .
			checked( 1, get_option( 'wr2x_picturefill_noscript' ), false ) . '/>';
    $html .= __( '<label>Disable</label><br /><small>Only <a href="http://caniuse.com/#feat=srcset" target="_blank">the browsers with src-set support</a> will display Retina images. You can also choose this if you want to load the Picturefill Polyfill script manually or if it is already loaded by your theme.</small>', 'wp-retina-2x' );
    echo $html;
  }

	function admin_picturefill_css_background_callback( $args ) {
    $value = get_option( 'wr2x_picturefill_css_background', null );
		$html = '<input type="checkbox" id="wr2x_picturefill_css_background" name="wr2x_picturefill_css_background" value="1" ' .
			checked( 1, get_option( 'wr2x_picturefill_css_background' ), false ) . '/>';
    $html .= __( '<label>Retina-ize</label><br /><small>In your HTML, inline CSS Background will be replaced by the Retina version of the image.</small>', 'wp-retina-2x' );
    echo $html;
	}

	function admin_hide_retina_column_callback( $args ) {
    $value = get_option( 'wr2x_hide_retina_column', null );
		$html = '<input type="checkbox" id="wr2x_hide_retina_column" name="wr2x_hide_retina_column" value="1" ' .
			checked( 1, get_option( 'wr2x_hide_retina_column' ), false ) . '/>';
    $html .= __( '<label>Hide</label><br /><small>Hide the <i>Retina Column</i> in the Media Library.</small>', 'wp-retina-2x' );
    echo $html;
  }

	function admin_hide_retina_dashboard_callback( $args ) {
    $value = get_option( 'wr2x_hide_retina_dashboard', null );
		$html = '<input type="checkbox" id="wr2x_hide_retina_dashboard" name="wr2x_hide_retina_dashboard" value="1" ' .
			checked( 1, get_option( 'wr2x_hide_retina_dashboard' ), false ) . '/>';
    $html .= __( '<label>Hide</label><br /><small>Disable <i>Retina Dashboard</i> menu and tools.</small>', 'wp-retina-2x' );
    echo $html;
  }

	/**
	 *
	 * GET / SET OPTIONS (TO REMOVE)
	 *
	 */

	function old_getoption( $option, $section, $default = '' ) {
		$options = get_option( $section );
		if ( isset( $options[$option] ) ) {
	        if ( $options[$option] == "off" ) {
	            return false;
	        }
	        if ( $options[$option] == "on" ) {
	            return true;
	        }
			return $options[$option];
	    }
		return $default;
	}

}

?>
