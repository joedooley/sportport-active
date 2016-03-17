<?php
/**
 * Welcome class.
 *
 * @since 1.1.4
 *
 * @package OMAPI
 * @author  Devin Vinson
 */
class OMAPI_Welcome {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.4.2
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.4.2
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.1.4.2
	 *
	 * @var object
	 */
	public $base;


	/**
	 * Holds the welcome slug.
	 *
	 * @since 1.1.4.2
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.1.4.2
	 */
	public function __construct() {

		// Set our object.
		$this->set();

		//Load the Welcome screen
		add_action('admin_menu', array($this, 'register_welcome_page') );

		//maybe redirect
		add_action('admin_init', array( $this, 'maybe_welcome_redirect' ) );
	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.1.4.2
	 */
	public function set() {

		self::$instance = $this;
		$this->base 	= OMAPI::get_instance();
		$this->view     = isset( $_GET['optin_monster_api_view'] ) ? stripslashes( $_GET['optin_monster_api_view'] ) : $this->base->get_view();

	}

	/**
	 * Maybe Redirect new users to the welcome page after install.
	 *
	 * @since 1.1.4.2
	 */
	public function maybe_welcome_redirect() {

		$options = $this->base->get_option();

		//Check for the new option
		if ( isset( $options['welcome']['status'] ) ){

			//Check if they have been welcomed
			if ( $options['welcome']['status'] === 'none'  ) {

				//Update the option
				$options['welcome']['status'] = 'welcomed';
				update_option('optin_monster_api', $options );

				//If this was not a bulk activate send them to the page
				if(!isset($_GET['activate-multi']))
				{
					wp_redirect('admin.php?page=optin-monster-api-welcome');
				}

			}

		} else {
			//welcome option didn't exist so must be pre-existing user updating
			$options['welcome']['status'] = 'welcomed';
			update_option('optin_monster_api', $options );
		}


	}

	/**
	 * Loads the OptinMonster admin menu.
	 *
	 * @since 1.1.4.2
	 */
	public function register_welcome_page() {

		$this->hook = add_submenu_page(
			__( 'OptinMonster', 'optin-monster-api' ), //parent slug
			__( 'Welcome to OptinMonster', 'optin-monster-api' ), //page title,
			__( 'Welcome', 'optin-monster-api'),
			apply_filters( 'optin_monster_api_menu_cap', 'manage_options' ), //cap
			'optin-monster-api-welcome', //slug
			array($this, 'callback_to_display_page') //callback
		);

		// Load settings page assets.
		if ( $this->hook ) {
			add_action( 'load-' . $this->hook, array( $this, 'assets' ) );
		}

	}

	/**
	 * Outputs the OptinMonster settings page.
	 *
	 * @since 1.1.4.2
	 */
	public function callback_to_display_page() {

	?>
		<div class="wrap omapi-page">

			<div id="welcome-panel" class="welcome-panel">

				<div class="welcome-panel-content">
					<div class="logo"><h2>Welcome to OptinMonster</h2></div>

					<div class="welcome-panel-column-container">

						<p class="intro-description"><?php _e('Welcome to OptinMonster - the best plugin for growing your email list and getting more subscribers.','optin-monster-api') ?></p>
						<p><?php _e('OptinMonster is a powerful conversion optimization tool that integrates with all web platforms including WordPress.','optin-monster-api') ?></p>
						<p><?php _e('In order to take advantage of this plugin, you must have an OptinMonster account.','optin-monster-api') ?></p>

						<h4><?php _e('Already have an account?','optin-monster-api') ?></h4>
						<p><a class="button button-primary button-hero" href="<?php echo esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ?>"><?php _e('Get Connected','optin-monster-api');?></a></p>

						<h4><?php _e('New to OptinMonster?','optin-monster-api') ?></h4>
						<p><a class="button button-primary button-hero" href="http://optinmonster.com/pricing/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard" target="_blank"><?php _e('Create Your Account','optin-monster-api') ?></a> or <a href="http://optinmonster.com/how-it-works/?utm_source=orgplugin&utm_medium=link&utm_campaign=wpdashboard" target="_blank"><?php _e('See How it Works','optin-monster-api') ?></a>.</p>

					</div>
				</div>

			</div>


		</div>
	<?php

	}

	/**
	 * Loads assets for the settings page.
	 *
	 * @since 1.1.4.2
	 */
	public function assets() {

		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_filter( 'admin_footer_text', array( $this, 'footer' ) );

	}

	/**
	 * Register and enqueue settings page specific CSS.
	 *
	 * @since 1.1.4.2
	 */
	public function styles() {

		wp_register_style( $this->base->plugin_slug . '-settings', plugins_url( '/assets/css/settings.css', $this->base->file ), array(), $this->base->version );
		wp_enqueue_style( $this->base->plugin_slug . '-settings' );


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





}