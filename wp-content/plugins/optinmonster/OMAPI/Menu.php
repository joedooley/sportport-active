<?php
/**
 * Menu class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Menu {

	/**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

	/**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var OMAPI
     */
    public $base;

    /**
     * Holds the admin menu slug.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $hook;

    /**
     * Holds a tabindex counter for easy navigation through form fields.
     *
     * @since 1.0.0
     *
     * @var int
     */
    public $tabindex = 429;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

	    // Set our object.
	    $this->set();

		// Load actions and filters.
        add_action( 'admin_menu', array( $this, 'menu' ) );

    }

    /**
     * Sets our object instance and base class instance.
     *
     * @since 1.0.0
     */
    public function set() {

        self::$instance = $this;
        $this->base 	= OMAPI::get_instance();
        $this->view     = isset( $_GET['optin_monster_api_view'] ) ? stripslashes( $_GET['optin_monster_api_view'] ) : $this->base->get_view();

    }

    /**
     * Loads the OptinMonster admin menu.
     *
     * @since 1.0.0
     */
    public function menu() {

        $this->hook = add_menu_page(
	        __( 'OptinMonster', 'optin-monster-api' ),
            __( 'OptinMonster', 'optin-monster-api' ),
            apply_filters( 'optin_monster_api_menu_cap', 'manage_options' ),
            'optin-monster-api-settings',
            array( $this, 'page' ),
            'none',
            579
        );

        // Load global icon font styles.
        add_action( 'admin_head', array( $this, 'icon' ) );

        // Load settings page assets.
        if ( $this->hook ) {
	        add_action( 'load-' . $this->hook, array( $this, 'assets' ) );
        }

    }

    /**
     * Loads the custom Archie icon.
     *
     * @since 1.0.0
     */
    public function icon() {

	    ?>
	    <style type="text/css">@font-face{font-family: 'archie';src:url('<?php echo plugins_url( '/assets/fonts/archie.eot?velzrt', OMAPI_FILE ); ?>');src:url('<?php echo plugins_url( '/assets/fonts/archie.eot?#iefixvelzrt', OMAPI_FILE ); ?>') format('embedded-opentype'),url('<?php echo plugins_url( '/assets/fonts/archie.woff?velzrt', OMAPI_FILE ); ?>') format('woff'),url('<?php echo plugins_url( '/assets/fonts/archie.ttf?velzrt', OMAPI_FILE ); ?>') format('truetype'),url('<?php echo plugins_url( '/assets/fonts/archie.svg?velzrt#archie', OMAPI_FILE ); ?>') format('svg');font-weight: normal;font-style: normal;}#toplevel_page_optin-monster-api-settings .dashicons-before,#toplevel_page_optin-monster-api-settings .dashicons-before:before {font-family: 'archie';speak: none;font-style: normal;font-weight: normal;font-variant: normal;text-transform: none;line-height: 1;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;}#toplevel_page_optin-monster-api-settings .dashicons-before:before {content: "\e600";font-size: 38px;margin-top: -9px;margin-left: -8px;}</style>
	    <?php

    }

    /**
     * Loads assets for the settings page.
     *
     * @since 1.0.0
     */
    public function assets() {

        add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
        add_filter( 'admin_footer_text', array( $this, 'footer' ) );

    }

    /**
     * Register and enqueue settings page specific CSS.
     *
     * @since 1.0.0
     */
    public function styles() {

		wp_register_style( $this->base->plugin_slug . '-select2', plugins_url( '/assets/css/select2.min.css', OMAPI_FILE ), array(), $this->base->version );
	    wp_enqueue_style( $this->base->plugin_slug . '-select2' );
	    wp_register_style( $this->base->plugin_slug . '-settings', plugins_url( '/assets/css/settings.css', OMAPI_FILE ), array(), $this->base->version );
	    wp_enqueue_style( $this->base->plugin_slug . '-settings' );

        // Run a hook to load in custom styles.
        do_action( 'optin_monster_api_admin_styles', $this->view );

    }

    /**
     * Register and enqueue settings page specific JS.
     *
     * @since 1.0.0
     */
    public function scripts() {

		wp_register_script( $this->base->plugin_slug . '-select2', plugins_url( '/assets/js/select2.min.js', OMAPI_FILE ), array( 'jquery' ), $this->base->version, true );
	    wp_enqueue_script( $this->base->plugin_slug . '-select2' );
	    wp_register_script( $this->base->plugin_slug . '-settings', plugins_url( '/assets/js/settings.js', OMAPI_FILE ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', $this->base->plugin_slug . '-select2' ), $this->base->version, true );
	    wp_enqueue_script( $this->base->plugin_slug . '-settings' );
	    wp_register_script( $this->base->plugin_slug . '-clipboard', plugins_url( '/assets/js/clipboard.min.js', OMAPI_FILE ), array( $this->base->plugin_slug . '-settings' ), $this->base->version, true );
	    wp_enqueue_script( $this->base->plugin_slug . '-clipboard' );
	    wp_register_script( $this->base->plugin_slug . '-tooltip', plugins_url( '/assets/js/tooltip.min.js', OMAPI_FILE ), array( $this->base->plugin_slug . '-settings' ), $this->base->version, true );
	    wp_enqueue_script( $this->base->plugin_slug . '-tooltip' );
	    wp_register_script( $this->base->plugin_slug . '-jspdf', plugins_url( '/assets/js/jspdf.min.js', OMAPI_FILE ), array( $this->base->plugin_slug . '-settings' ), $this->base->version, true );
	    wp_enqueue_script( $this->base->plugin_slug . '-jspdf' );
	    wp_localize_script(
		    $this->base->plugin_slug . '-settings',
		    'omapi',
		    array(
			   	'ajax'	      => admin_url( 'admin-ajax.php' ),
			   	'nonce'       => wp_create_nonce( 'omapi-query-nonce' ),
			    'confirm'     => __( 'Are you sure you want to reset these settings?', 'optin-monster-api' ),
			    'date_format' => 'F j, Y',
			    'supportData' => $this->get_support_data(),
			)
	    );

        // Run a hook to load in custom styles.
        do_action( 'optin_monster_api_admin_scripts', $this->view );

    }

	/**
	 * Combine Support data together to pass into localization
	 *
	 * @since 1.1.5
	 * @return array
	 */
	public function get_support_data() {
		$server_data = '';
		$optin_data = '';

		if ( isset($_GET['optin_monster_api_view']) && $_GET['optin_monster_api_view'] == 'support') {
			$optin_data = $this->get_optin_data();
			$server_data = $this->get_server_data();
		}
		$data = array(
			'server' => $server_data,
			'optins' => $optin_data
		);

		return $data;
	}

	/**
	 * Build Current Optin data array to localize
	 *
	 * @since 1.1.5
	 *
	 * @return array
	 */
	private function get_optin_data() {

		$optins = $this->base->get_optins();
		$optin_data = array();

		if ( $optins ) {
			foreach ( $optins as $optin ) {
				$optin = get_post( $optin->ID );
				$slug = $optin->post_name;

				$optin_data[ $slug ] = array(
					'Optin Type'                       => get_post_meta( $optin->ID, '_omapi_type', true ),
					'Associated IDs'                   => get_post_meta( $optin->ID, '_omapi_ids', true ),
					'Current Status'                   => get_post_meta( $optin->ID, '_omapi_enabled', true ),
					'Automatic Output Status'          => get_post_meta( $optin->ID, '_omapi_automatic', true ),
					'User Settings'                    => get_post_meta( $optin->ID, '_omapi_users', true ),
					'Pages to Never show on'           => get_post_meta( $optin->ID, '_omapi_never', true ),
					'Pages to Only show on'            => get_post_meta( $optin->ID, '_omapi_only', true ),
					'Categories'                       => get_post_meta( $optin->ID, '_omapi_categories', true ),
					'Taxonomies'                       => get_post_meta( $optin->ID, '_omapi_taxonomies', true ),
					'Template types to Show on'        => get_post_meta( $optin->ID, '_omapi_show', true ),
					'Shortcodes Synced and Recognized' => get_post_meta( $optin->ID, '_omapi_shortecode', true ),
				);
			}
		}
		return $optin_data;
	}

	/**
	 * Build array of server information to localize
	 *
	 * @since 1.1.5
	 *
	 * @return array
	 */
	private function get_server_data() {

		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;

		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$used_plugins   = "\n";
		$api_ping       = wp_remote_request( 'http://api.optinmonster.com/v1/ping' );
		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( ! in_array( $plugin_path, $active_plugins ) ) {
				continue;
			}
			$used_plugins .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
		}


		$array = array(
			'Server Info'        => esc_html( $_SERVER['SERVER_SOFTWARE'] ),
			'PHP Version'        => function_exists( 'phpversion' ) ? esc_html( phpversion() ) : 'Unable to check.',
			'Error Log Location' => function_exists( 'ini_get' ) ? ini_get( 'error_log' ) : 'Unable to locate.',
			'Default Timezone'   => date_default_timezone_get(),
			'WordPress Home URL' => get_home_url(),
			'WordPress Site URL' => get_site_url(),
			'WordPress Version'  => get_bloginfo( 'version' ),
			'Multisite'          => is_multisite() ? 'Multisite Enabled' : 'Not Multisite',
			'Language'           => get_locale(),
			'API Ping Response'  => wp_remote_retrieve_response_code( $api_ping ),
			'Active Theme'       => $theme,
			'Active Plugins'     => $used_plugins,

		);

		return $array;
	}

    /**
     * Customizes the footer text on the OptinMonster settings page.
     *
     * @since 1.0.0
     *
     * @param string $text  The default admin footer text.
     * @return string $text Amended admin footer text.
     */
    public function footer( $text ) {

        $new_text = sprintf( __( 'Thank you for using <a href="%1$s" target="_blank">OptinMonster</a>!', 'optin-monster-api' ),
			'http://optinmonster.com'
		);
		return str_replace( '</span>', '', $text ) . ' | ' . $new_text . '</span>';

    }

    /**
     * Outputs the OptinMonster settings page.
     *
     * @since 1.0.0
     */
    public function page() {

        ?>
        <div class="wrap omapi-page">
	        <h2><?php echo esc_html( get_admin_page_title() ); ?> <span><?php printf( __( 'v%s', 'optin-monster-api' ), $this->base->version ); ?></span> <a href="https://app.optinmonster.com/account/create/" class="button button-primary button-large omapi-new-optin" title="<?php esc_attr_e( 'Create New Optin', 'optin-monster-api' ); ?>" target="_blank"><?php _e( 'Create New Optin', 'optin-monster-api' ); ?></a></h2>
	        <div class="omapi-ui">
		        <div class="omapi-tabs">
			        <ul class="omapi-panels">
				        <?php
					        $i = 0; foreach ( $this->get_panels() as $id => $panel ) :
					        $first  = 0 == $i ? ' omapi-panel-first' : '';
					        $active = $id == $this->view ? ' omapi-panel-active' : '';
					    ?>
				        <li class="omapi-panel omapi-panel-<?php echo sanitize_html_class( $id ); ?><?php echo $first . $active; ?>"><a href="<?php echo esc_url_raw( add_query_arg( 'optin_monster_api_view', $id, admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ); ?>" class="omapi-panel-link" data-panel="<?php echo $id; ?>" data-panel-title="<?php echo $panel; ?>"><?php echo $panel; ?></a></li>
				        <?php $i++; endforeach; ?>
			        </ul>
		        </div>
		        <div class="omapi-tabs-content">
			        <?php
				        foreach ( $this->get_panels() as $id => $panel ) :
				        $active = $id == $this->view ? ' omapi-content-active' : '';
				    ?>
			        <div class="omapi-content omapi-content-<?php echo sanitize_html_class( $id ); ?><?php echo $active; ?>"><?php do_action( 'optin_monster_api_content_before', $id, $panel, $this ); do_action( 'optin_monster_api_content_' . $id, $panel, $this ); do_action( 'optin_monster_api_content_after', $id, $panel, $this ); ?></div>
			        <?php endforeach; ?>
		        </div>
	        </div>
        </div>
        <?php

    }

    /**
     * Retrieves the available tab panels.
     *
     * @since 1.0.0
     *
     * @return array $panels Array of tab panels.
     */
    public function get_panels() {

		// Only load the API panel if no API credentials have been set.
		$panels 	 = array();
		$creds  	 = $this->base->get_api_credentials();
	    $can_migrate = $this->base->can_migrate();
	    $is_legacy_active = $this->base->is_legacy_active();

		// Set panels requiring credentials.
		if ( $creds ) {
			$panels['optins'] = __( 'Optins', 'optin-monster-api' );
		}

		// Set default panels.
		$panels['api'] 	= __( 'API Credentials', 'optin-monster-api' );

		// Set the settings panel.
		//$panels['settings'] = __( 'Settings', 'optin-monster-api' );

	    // Set the Support panel
	    $panels['support'] = __( 'Support', 'optin-monster-api' );

	    // Set the migration panel.
	    if ( $creds && $can_migrate && $is_legacy_active ) {
		    $panels['migrate'] = __( 'Migration', 'optin-monster-api' );
	    }

		return apply_filters( 'optin_monster_api_panels', $panels );

    }

    /**
     * Retrieves the setting UI for the setting specified.
     *
     * @since 1.0.0
     *
     * @param string $id 	  The optin ID to target.
     * @param string $setting The possible subkey setting for the option.
     * @return string		  HTML setting string.
     */
    public function get_setting_ui( $id, $setting = '' ) {

	    // Prepare variables.
	    $ret      = '';
	    $optin_id = isset( $_GET['optin_monster_api_id'] ) ? absint( $_GET['optin_monster_api_id'] ) : 0;
	    $value 	  = 'optins' == $id ? get_post_meta( $optin_id, '_omapi_' . $setting, true ) : $this->base->get_option( $id, $setting );
	    $optin = get_post( $optin_id);

	    // Load the type of setting UI based on the option.
	    switch ( $id ) {
		    case 'api' :
		    	switch ( $setting ) {
				    case 'user' :
				    	$ret = $this->get_password_field( $setting, $value, $id, __( 'API Username', 'optin-monster-api' ), __( 'The API Username found in your OptinMonster Settings area.', 'optin-monster-api' ), __( 'Enter your API Username here...', 'optin-monster-api' ) );
				    break 2;

				    case 'key' :
				    	$ret = $this->get_password_field( $setting, $value, $id, __( 'API Key', 'optin-monster-api' ), __( 'The API Key found in your OptinMonster Settings area.', 'optin-monster-api' ), __( 'Enter your API Key here...', 'optin-monster-api' ) );
				    break 2;
				}
			break;

			case 'settings' :
		    	switch ( $setting ) {
				    case 'cookies' :
					    $ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Clear local cookies on optin update?', 'optin-monster-api' ), __( 'If checked, local cookies will be cleared for all optins after optin settings are adjusted and saved.', 'optin-monster-api' ) );
				    break 2;
				}
			break;

		    case 'support' :
			    switch ( $setting ) {
				    case 'video' :
					    $ret = '<div class="omapi-half-column"><div class="omapi-video-container"><iframe width="640" height="360" src="https://www.youtube.com/embed/QweP8BHMNRw?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe></div></div>';
					    break 2;

				    case 'links' :
					    $ret = $this->get_support_links( $setting, 'Helpful Links' );
					    break 2;

				    case 'server-report';
					    $ret = $this->get_plugin_report($setting, 'Server / Plugin Report');
					    break 2;
			    }
			    break;

		    case 'toggle' :
				switch ( $setting ) {
					case 'advanced-start' :
						$ret = $this->get_toggle_start( $setting, __( 'Advanced Settings', 'optin-monster-api'), __('More specific settings available for campaign visibility.', 'optin-monster-api') );
					break 2;
					case 'advanced-end' :
						$ret = $this->get_toggle_end();
					break 2;
				}
		    break;

		    case 'optins' :
		    	switch ( $setting ) {
					case 'enabled' :
				    	$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Enable optin on site?', 'optin-monster-api' ), __( 'The optin will not be displayed on this site unless this setting is checked.', 'optin-monster-api' ) );
				    break 2;

				    case 'automatic' :
				    	$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Automatically add after post?', 'optin-monster-api' ), sprintf( __( 'Automatically adds the optin after each post. You can turn this off and add it manually to your posts by <a href="%s" target="_blank">clicking here and viewing the tutorial.</a>', 'optin-monster-api' ), 'https://optinmonster.com/docs/manually-add-after-post-optin/' ), array('omapi-after-post-auto-select') );
				    break 2;
				    case 'automatic_shortcode' :
						$full_shortcode ='[optin-monster-shortcode id="'. $optin->post_name .'"]';
					    $ret = $this->get_text_field(
						    $setting,
						    $full_shortcode,
						    $id,
						    __( 'Shortcode for this optin', 'optin-monster-api' ),
						    sprintf( __( 'Use the shortcode to manually add this optin to inline to a post or page. <a href="%s" title="Click here to learn more about how this work" target="_blank">Click here to learn more about how this works.</a>', 'optin-monster-api' ), 'https://optinmonster.com/docs/how-to-manually-add-an-after-post-or-inline-optin/' ),
						    false,
						    array(),
						    true
					    );
				    break 2;

				    case 'users' :
				    	$ret = $this->get_dropdown_field( $setting, $value, $id, $this->get_user_output(), __( 'Who should see this optin?', 'optin-monster-api' ), sprintf( __( 'Determines who should be able to view this optin. Want to hide for newsletter subscribers? <a href="%s" target="_blank">Click here to learn how.</a>', 'optin-monster-api' ), 'https://optinmonster.com/docs/how-to-hide-optinmonster-from-existing-newsletter-subscribers/' ) );
				    break 2;

				    case 'never' :
				    	$val = is_array( $value ) ? implode( ',', $value ) : $value;
				    	$ret = $this->get_custom_field( $setting, '<input type="hidden" value="' . $val . '" id="omapi-field-' . $setting . '" class="omapi-select-ajax" name="omapi[' . $id . '][' . $setting . ']" data-placeholder="' . esc_attr__( 'Type to search and select post(s)...', 'optin-monster-api' ) . '">', __( 'Never load optin on:', 'optin-monster-api' ), __( 'Never loads the optin on the selected posts and/or pages. Does not disable automatic Global output.', 'optin-monster-api' ) );
				    break 2;

				    case 'only' :
				    	$val = is_array( $value ) ? implode( ',', $value ) : $value;
				    	$ret = $this->get_custom_field( $setting, '<input type="hidden" value="' . $val . '" id="omapi-field-' . $setting . '" class="omapi-select-ajax" name="omapi[' . $id . '][' . $setting . ']" data-placeholder="' . esc_attr__( 'Type to search and select post(s)...', 'optin-monster-api' ) . '">', __( 'Load optin specifically on:', 'optin-monster-api' ), __( 'Loads the optin on the selected posts and/or pages.', 'optin-monster-api' ) );
				    break 2;

				    case 'categories' :
				        $categories = get_categories();
				        if ( $categories ) {
				            ob_start();
				            wp_category_checklist( 0, 0, (array) $value, false, null, true );
				            $cats = ob_get_clean();
				            $ret  = $this->get_custom_field( 'categories', $cats, __( 'Load optin on post categories:', 'optin-monster-api' ) );
				        }
				    break;

				    case 'taxonomies' :
				    	// Attempt to load post tags.
				    	$html = '';
				    	$tags = get_taxonomy( 'post_tag' );
				    	if ( $tags ) {
					    	$tag_terms = get_tags();
					    	if ( $tag_terms ) {
						    	$display = (array) $value;
						        $display = isset( $display['post_tag'] ) ? implode( ',', $display['post_tag'] ) : '';
						        $html    = $this->get_custom_field( $setting, '<input type="hidden" value="' . $display . '" id="omapi-field-' . $setting . '" class="omapi-select-ajax" name="tax_input[post_tag][]" data-placeholder="' . esc_attr__( 'Type to search and select post tag(s)...', 'optin-monster-api' ) . '">', __( 'Load optin on post tags:', 'optin-monster-api' ), __( 'Loads the optin on the selected post tags.', 'optin-monster-api' ) );
					    	}
				    	}

				    	// Possibly load taxonomies setting if they exist.
					    $taxonomies                = get_taxonomies( array( 'public' => true, '_builtin' => false ) );
					    $taxonomies['post_format'] = 'post_format';
					    $data                      = array();
					    if ( $taxonomies ) {
					        foreach ( $taxonomies as $taxonomy ) {
						        $terms = get_terms( $taxonomy );
						        if ( $terms ) {
						            ob_start();
						            $display = (array) $value;
						            $display = isset( $display[ $taxonomy ] ) ? $display[ $taxonomy ] : array();
						            $tax     = get_taxonomy( $taxonomy );
						            $args    = array(
						                'descendants_and_self' => 0,
						                'selected_cats'        => (array) $display,
						                'popular_cats'         => false,
						                'walker'               => null,
						                'taxonomy'             => $taxonomy,
						                'checked_ontop'        => true
						            );
						            wp_terms_checklist( 0, $args );
						            $output = ob_get_clean();
						            if ( ! empty( $output ) ) {
						                $data[ $taxonomy ] = $this->get_custom_field( 'taxonomies', $output, __( 'Load optin on ' . strtolower( $tax->labels->name ) . ':', 'optin-monster-api' ) );
						            }
						        }
					        }
					    }

					    // If we have taxonomies, add them to the taxonomies key.
					    if ( ! empty( $data ) ) {
					        foreach ( $data as $setting ) {
					            $html .= $setting;
					        }
					    }

					    // Return the data.
					    $ret = $html;
				    break;

				    case 'show' :
				        $ret = $this->get_custom_field( 'show', $this->get_show_fields( $value ), __( 'Load optin on post types and archives:', 'optin-monster-api' ) );
				    break;

				    case 'mailpoet' :
				    	$ret = $this->get_checkbox_field( $setting, $value, $id, __( 'Save lead to MailPoet?', 'optin-monster-api' ), __( 'If checked, successful optin leads will be saved to MailPoet.', 'optin-monster-api' ) );
				    break 2;

				    case 'mailpoet_list' :
				    	$ret = $this->get_dropdown_field( $setting, $value, $id, $this->get_mailpoet_lists(), __( 'Add lead to this MailPoet list:', 'optin-monster-api' ), __( 'All successful leads for the optin will be added to this particular MailPoet list.', 'optin-monster-api' ) );
				    break 2;
		    	}
		    break;
		    case 'note' :
		        switch ( $setting ) {
			        case 'sidebar_widget_notice' :
			            $ret = $this->get_optin_type_note( $setting, __('Use Widgets to set Sidebar output', 'optin-monster-api'), __('You can set this campaign to show in your sidebars using the OptinMonster widget within your sidebars.', 'optin-monster-api'), 'widgets.php', __('Go to Widgets', 'optin-monster-api') );
			        break 2;
		        }
		    break;
	    }

		// Return the setting output.
	    return apply_filters( 'optin_monster_api_setting_ui', $ret, $setting, $id );

    }

    /**
     * Returns the user output settings available for an optin.
     *
     * @since 1.0.0
     *
     * @return array An array of user dropdown values.
     */
    public function get_user_output() {

	    return apply_filters( 'optin_monster_api_user_output',
	    	array(
		    	array(
			    	'name'  => __( 'Show optin to all visitors and users', 'optin-monster-api' ),
			    	'value' => 'all'
			    ),
			    array(
			    	'name'  => __( 'Show optin to only visitors (not logged-in)', 'optin-monster-api' ),
			    	'value' => 'out'
			    ),
			    array(
			    	'name'  => __( 'Show optin to only users (logged-in)', 'optin-monster-api' ),
			    	'value' => 'in'
			    )
			)
		);

    }

    /**
     * Returns the available MailPoet lists.
     *
     * @since 1.0.0
     *
     * @return array An array of MailPoet lists.
     */
    public function get_mailpoet_lists() {

	    // Prepare variables.
	    $mailpoet = WYSIJA::get( 'list', 'model' );
	    $lists	  = $mailpoet->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );
	    $ret	  = array();

	    // Add default option.
	    $ret[]	  = array(
		    'name'  => __( 'Select your MailPoet list...', 'optin-monster-api' ),
		    'value' => 'none'
	    );

	    // Loop through the list data and add to array.
	    foreach ( (array) $lists as $list ) {
		    $ret[] = array(
			    'name'  => $list['name'],
			    'value' => $list['list_id']
		    );
	    }

	    return apply_filters( 'optin_monster_api_mailpoet_lists', $ret, $lists, $mailpoet );

    }

    /**
     * Retrieves the UI output for the single posts show setting.
     *
     * @since 2.0.0
     *
     * @param array $value  The meta index value for the show setting.
     * @return string $html HTML representation of the data.
     */
    public function get_show_fields( $value ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        $output  = '<label for="omapi-field-show-index" class="omapi-custom-label">';
        $output .= '<input type="checkbox" id="omapi-field-show-index" name="omapi[optins][show][]" value="index"' . checked( in_array( 'index', (array) $value ), 1, false ) . ' /> ' . __( 'Front Page and Search Pages', 'optin-monster-api' ) . '</label><br />';
        $post_types = get_post_types( array( 'public' => true ) );
        foreach ( (array) $post_types as $show ) {
            $pt_object = get_post_type_object( $show );
            $label     = $pt_object->labels->name;
            $output   .= '<label for="omapi-field-show-' . esc_html( strtolower( $label ) ) . '" class="omapi-custom-label">';
            $output   .= '<input type="checkbox" id="omapi-field-show-' . esc_html( strtolower( $label ) ) . '" name="omapi[optins][show][]" tabindex="' . $this->tabindex . '" value="' . $show . '"' . checked( in_array( $show, (array) $value ), 1, false ) . ' /> ' . esc_html( $label ) . '</label><br />';

            // Increment the global tabindex counter and iterator.
            $this->tabindex++;
        }

        return $output;

    }

    /**
     * Retrieves the UI output for a plain text input field setting.
     *
     * @since 1.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $id	  The setting ID to target for name field.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param string $place   Placeholder text for the field.
     * @param array $classes  Array of classes to add to the field.
     * @param boolean $copy   Turn on clipboard copy button and make field readonly
     * @return string $html   HTML representation of the data.
     */
	public function get_text_field( $setting, $value, $id, $label, $desc = false, $place = false, $classes = array(), $copy = false ) {

		// Increment the global tabindex counter.
		$this->tabindex++;

		// Check for copy set
		$readonly_output = $copy ? 'readonly' : '';

		// Build the HTML.
		$field  = '<div class="omapi-field-box omapi-text-field omapi-field-box-' . $setting . ' omapi-clear">';
				$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
				$field .= '<input type="text" id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . esc_attr( $value ) . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . $readonly_output .' />';
				if ( $copy ) {
					$field .= '<span class="omapi-copy-button button"  data-clipboard-target="#omapi-field-' . $setting . '">Copy to clipboard</span>';
				}
				if ( $desc ) {
					$field .= '<br /><span class="omapi-field-desc">' . $desc . '</span>';
				}
				$field .= '</p>';
		$field .= '</div>';

		// Return the HTML.
		return apply_filters( 'optin_monster_api_text_field', $field, $setting, $value, $id, $label );

	}


    /**
     * Retrieves the UI output for a password input field setting.
     *
     * @since 1.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $id	  The setting ID to target for name field.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param string $place   Placeholder text for the field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_password_field( $setting, $value, $id, $label, $desc = false, $place = false, $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // Build the HTML.
        $field  = '<div class="omapi-field-box omapi-password-field omapi-field-box-' . $setting . ' omapi-clear">';
            $field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<input type="password" id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . ' />';
                if ( $desc ) {
                    $field .= '<br /><span class="omapi-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_api_password_field', $field, $setting, $value, $id, $label );

    }

    /**
     * Retrieves the UI output for a hidden input field setting.
     *
     * @since 1.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $id	  The setting ID to target for name field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_hidden_field( $setting, $value, $id, $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // Build the HTML.
        $field  = '<div class="omapi-field-box omapi-hidden-field omapi-field-box-' . $setting . ' omapi-clear omapi-hidden">';
        $field .= '<input type="hidden" id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '" />';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_api_hidden_field', $field, $setting, $value, $id );

    }
    /**
     * Retrieves the UI output for a plain textarea field setting.
     *
     * @since 1.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $id	  The setting ID to target for name field.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param string $place   Placeholder text for the field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_textarea_field( $setting, $value, $id, $label, $desc = false, $place = false, $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // Build the HTML.
        $field  = '<div class="omapi-field-box omapi-textarea-field omapi-field-box-' . $setting . ' omapi-clear">';
            $field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<textarea id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" rows="5" tabindex="' . $this->tabindex . '"' . ( $place ? ' placeholder="' . $place . '"' : '' ) . '>' . $value . '</textarea>';
                if ( $desc ) {
                    $field .= '<br /><span class="omapi-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_api_textarea_field', $field, $setting, $value, $id, $label );

    }

    /**
     * Retrieves the UI output for a checkbox setting.
     *
     * @since 1.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $id	  The setting ID to target for name field.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_checkbox_field( $setting, $value, $id, $label, $desc = false, $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // Build the HTML.
        $field  = '<div class="omapi-field-box omapi-checkbox-field omapi-field-box-' . $setting . ' omapi-clear">';
            $field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<input type="checkbox" id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '" value="' . $value . '"' . checked( $value, 1, false ) . ' /> ';
                if ( $desc ) {
                    $field .= '<span class="omapi-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_api_checkbox_field', $field, $setting, $value, $id, $label );

    }

    /**
     * Retrieves the UI output for a dropdown field setting.
     *
     * @since 1.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $id	  The setting ID to target for name field.
     * @param array $data     The data to be used for option fields.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @param array $classes  Array of classes to add to the field.
     * @return string $html   HTML representation of the data.
     */
    public function get_dropdown_field( $setting, $value, $id, $data, $label, $desc = false, $classes = array() ) {

        // Increment the global tabindex counter.
        $this->tabindex++;

        // Build the HTML.
        $field  = '<div class="omapi-field-box omapi-dropdown-field omapi-field-box-' . $setting . ' omapi-clear">';
            $field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label><br />';
                $field .= '<select id="omapi-field-' . $setting . '" class="' . implode( ' ', (array) $classes ) . '" name="omapi[' . $id . '][' . $setting . ']" tabindex="' . $this->tabindex . '">';
                foreach ( $data as $i => $info ) {
                    $field .= '<option value="' . $info['value'] . '"' . selected( $info['value'], $value, false ) . '>' . $info['name'] . '</option>';
                }
                $field .= '</select>';
                if ( $desc ) {
                    $field .= '<br /><span class="omapi-field-desc">' . $desc . '</span>';
                }
            $field .= '</p>';
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'omapi_dropdown_field', $field, $setting, $value, $id, $label, $data );

    }

    /**
     * Retrieves the UI output for a field with a custom output.
     *
     * @since 1.0.0
     *
     * @param string $setting The name of the setting to be saved to the DB.
     * @param mixed $value    The value of the setting.
     * @param string $label   The label of the input field.
     * @param string $desc    The description for the input field.
     * @return string $html   HTML representation of the data.
     */
    public function get_custom_field( $setting, $value, $label, $desc = false ) {

        // Build the HTML.
        $field = '<div class="omapi-field-box omapi-custom-field omapi-field-box-' . $setting . ' omapi-clear">';
            $field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label></p>';
            $field .= $value;
            if ( $desc ) {
                $field .= '<br /><span class="omapi-field-desc">' . $desc . '</span>';
            }
        $field .= '</div>';

        // Return the HTML.
        return apply_filters( 'optin_monster_api_custom_field', $field, $setting, $value, $label );

    }

	/**
	 * Starts the toggle wrapper for a toggle section.
	 *
	 * @since 1.1.5
	 *
	 * @param $label
	 * @param $desc
	 *
	 * @return mixed|void
	 */
	public function get_toggle_start( $setting, $label, $desc ) {
		$field = '<div class="omapi-ui-toggle-controller">';
			$field .= '<p class="omapi-field-wrap"><label for="omapi-field-' . $setting . '">' . $label . '</label></p>';
			if ( $desc ) {
				$field .= '<span class="omapi-field-desc">' . $desc . '</span>';
			}
		$field .= '</div>';
		$field .= '<div class="omapi-ui-toggle-content">';

		return apply_filters( 'optin_monster_api_toggle_start_field', $field, $label, $desc  );
	}

	/**
	 * Closes toggle wrapper.
	 *
	 * @since 1.1.5
	 * @return string HTML end for toggle start
	 */
	public function get_toggle_end(){

		$field = '</div>';

		return apply_filters( 'optin_monster_api_toggle_end_field', $field );
	}

	/**
	 *  Helper note output with title, text, and admin linked button.
	 *
	 * @since 1.1.5
	 *
	 * @param $setting
	 * @param $title
	 * @param $text
	 * @param $admin_page
	 * @param $button
	 *
	 * @return mixed|void
	 */
	public function get_optin_type_note( $setting, $title, $text, $admin_page, $button ) {

		$field = '<div class="omapi-field-box  omapi-inline-notice omapi-field-box-' . $setting . ' omapi-clear">';
		if ($title ) {
			$field .= '<p class="omapi-notice-title">' . $title . '</p>';
		}
		if ($text) {
			$field .= '<p class="omapi-field-desc">' . $text . '</p>';
		}
		if ( $admin_page && $button ) {
			// Increment the global tabindex counter.
			$this->tabindex++;
			$field .= '<a href="' . esc_url_raw( admin_url( $admin_page ) ) . '" class="button button-small" title="' . $button . '" target="_blank">' . $button . '</a>';
		}
		$field .= '</div>';

		return apply_filters('optin_monster_api_inline_note_display', $field, $title, $text, $admin_page, $button );
	}

	/**
	 * Support Link output
	 *
	 * @param $setting
	 *
	 * @return mixed|void HTML of the list filtered as needed
	 */
	public function get_support_links( $setting, $title ) {

		$field ='';

		$field .= '<div class="omapi-support-links ' . $setting . '"><h3>' . $title . '</h3><ul>';
		$field .= '<li><a target="_blank" href="' . esc_url( 'http://optinmonster.com/docs/' ) . '">'. __('Documentation','optin-monster-api') . '</a></li>';
		$field .= '<li><a target="_blank" href="' . esc_url( 'https://wordpress.org/plugins/optinmonster/changelog/' ) . '">'. __('Changelog','optin-monster-api') . '</a></li>';
		$field .= '<li><a target="_blank" href="' . esc_url( 'https://app.optinmonster.com/account/support/' ) . '">'. __('Create a Support Ticket','optin-monster-api') . '</a></li>';
		$field .= '</ul></div>';

		return apply_filters( 'optin_monster_api_support_links', $field, $setting);
	}

	public function get_plugin_report( $setting, $title ) {

		$field ='';

		$field .= '<div class="omapi-support-data ' . $setting . '"><h3>' . $title . '</h3>';
		$link = 'https://app.optinmonster.com/account/support/';
		$field .= '<p>' . sprintf( wp_kses( __( 'Download the report and attach to your <a href="%s">support ticket</a> to help speed up the process.', 'my-text-domain' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $link ) ) . '</p>';
		$field .= '<a href="' . esc_url_raw( '#' ) . '" id="js--omapi-support-pdf" class="button button-primary button-large omapi-support-data-button" title="Download a PDF Report for Support" target="_blank">Download PDF Report</a>';
		$field .= '</div>';

		return apply_filters( 'optin_monster_api_support_data', $field, $setting, $title );
	}
}