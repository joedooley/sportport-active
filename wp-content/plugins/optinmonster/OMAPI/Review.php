<?php
/**
 * Pointers class.
 *
 * @since 1.1.4.5
 *
 * @package OMAPI
 * @author  Devin Vinson
 */
class OMAPI_Review {

	/**
	 * Holds the class object.
	 *
	 * @since 1.1.4.5
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.1.4.5
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the review slug.
	 *
	 * @since 1.1.4.5
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.1.4.5
	 *
	 * @var object
	 */
	public $base;

	/**
	 * Current API route.
	 *
	 * @since 1.1.4.5
	 *
	 * @var bool|string
	 */
	public $route = 'optinmonster.com/wp-json/optinmonster/v1/pluginreview/';

	/**
	 * API Username.
	 *
	 * @since 1.1.4.5
	 *
	 * @var bool|string
	 */
	public $user = false;

	/**
	 * API Key.
	 *
	 * @since 1.0.0
	 *
	 * @var bool|string
	 */
	public $key = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.1.4.5
	 */
	public function __construct() {

		// Set default class properties
		$this->protocol = is_ssl() ? 'https://' : 'http://';
		$this->url      = $this->protocol . $this->route;

		// Set our object.
		$this->set();
		// Scripts
		add_action('admin_enqueue_scripts', array($this, 'maybe_enqueue_pointer') );
		add_action('admin_enqueue_scripts', array($this, 'maybe_enqueue_reminder_pointer') );
		// Options
		add_action('wp_ajax_set_omapi_review_reminder', array($this, 'set_user_review_reminder') );
		// Pages
		add_action('admin_menu', array($this, 'register_review_page') );
		// Action
		add_action( 'admin_post_omapi_send_review', array( $this, 'omapi_send_review') );
		// Admin Notices
		add_action('admin_notices', array($this, 'notices') );

	}

	/**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.1.4.5
	 */
	public function set() {

		self::$instance = $this;
		$this->base 	= OMAPI::get_instance();

	}

	/**
	 * Loads the OptinMonster admin menu.
	 *
	 * @since 1.1.4.5
	 */
	public function register_review_page() {

		$this->hook = add_submenu_page(
			__( 'OptinMonster', 'optin-monster-api' ), //parent slug
			__( 'Review OptinMonster', 'optin-monster-api' ), //page title,
			__( 'Thank you for your Review', 'optin-monster-api'),
			apply_filters( 'optin_monster_api_menu_cap', 'manage_options' ), //cap
			'optin-monster-api-review', //slug
			array($this, 'callback_to_display_page') //callback
		);

		// Load settings page assets.
		if ( $this->hook ) {
			add_action( 'load-' . $this->hook, array( $this, 'assets' ) );
		}

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
	 * @since 1.1.4.5
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
	 * Outputs the Review Page.
	 *
	 * @since 1.1.4.5
	 */
	public function callback_to_display_page() {


		// Get any saved meta
		$review_meta = get_user_meta( get_current_user_id(), 'omapi_review_data', true );

		// Get autofill details
		$current_user           = wp_get_current_user();
		$current_usermail       = isset ( $review_meta['user-email'] ) ? $review_meta['user-email'] : $current_user->user_email;
		$current_userfullname   = isset ( $review_meta['user-name'] ) ? $review_meta['user-name'] : $current_user->user_firstname . ' ' . $current_user->user_lastname;
		$current_credentials    = isset ( $review_meta['user-creds'] ) ? $review_meta['user-creds'] : '';
		$current_source         = isset ( $review_meta['user-source'] ) ? $review_meta['user-source'] : get_site_url();
		$user_review            = isset ( $review_meta['user-review'] ) ? $review_meta['user-review'] : '';
		$review_status          = isset ( $review_meta['status'] ) ? $review_meta['status'] : 'unfinished';

		?>
		<div class="wrap omapi-page">
		<h2><?php echo esc_html( get_admin_page_title() ); ?> <span><?php printf( __( 'v%s', 'optin-monster-api' ), $this->base->version ); ?></span></h2>
		<div class="omapi-ui">

			<div id="welcome-panel" class="welcome-panel">
				<?php if ($review_status == 'finished') : ?>
					<p><?php _e('Thank you for sending us your review. Please consider leaving us a review on WordPress.org as well. We do really appreciate your time.', 'optin-monster-api'); ?></p>
				<?php else: ?>
					<p><?php _e('Thank you for taking a minute to send us a review.', 'optin-monster-api'); ?></p>
				<?php endif; ?>
				<div class="omapi-ui">
						<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
							<input type="hidden" name="action" value="omapi_send_review">
							<?php wp_nonce_field('omapi-submit-review','omapi-review-nonce') ?>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label for="user-review"><?php _e('Review (required)', 'optin-monster-api'); ?></label>
									<textarea id="user-review" tabindex="1" rows="5" name="user-review"><?php echo $user_review; ?></textarea>
									<span class="omapi-field-desc"><?php _e('Be as specific as you would like (140 to 1000 characters).', 'optin-monster-api'); ?></span>
								</div>
							</div>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label for="user-email"><?php _e('Email (required)', 'optin-monster-api'); ?></label>
									<input type="text" id="user-email" placeholder="<?php _e('Email', 'optin-monster-api')?>" tabindex="1" name="user-email" value="<?php echo $current_usermail ?>">
								</div>
							</div>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label><?php _e('Your Name (required)', 'optin-monster-api'); ?></label>
									<input type="text" id="user-name" placeholder="<?php _e('Your Name', 'optin-monster-api')?>" tabindex="1" name="user-name" value="<?php echo $current_userfullname ?>">
									<span class="omapi-field-desc"><?php _e('The name you would like shown if featured.', 'optin-monster-api'); ?></span>
								</div>
							</div>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label><?php _e('Title and Company', 'optin-monster-api'); ?></label>
									<input type="text" id="user-credentials" placeholder="<?php _e('Title, Company Name', 'optin-monster-api')?>" tabindex="1" name="user-credentials" value="<?php echo $current_credentials ?>">
									<span class="omapi-field-desc"><?php _e('Professional Title and Company', 'optin-monster-api'); ?></span>
								</div>
							</div>
							<div class="omapi-field-box">
								<div class="omapi-field-wrap">
									<label><?php _e('Where are you using OptinMonster', 'optin-monster-api'); ?></label>
									<input type="text" id="user-source" placeholder="http://" tabindex="1" name="user-source" value="<?php echo $current_source; ?>">
									<span class="omapi-field-desc"><?php _e('May be shown if featured.', 'optin-monster-api'); ?></span>
								</div>
							</div>
							<p class="submit">
								<?php if ($review_status !== 'finished') : ?>
								<button class="button button-primary" type="submit"><?php _e('Send Review', 'optin-monster-api'); ?></button>
								<?php endif; ?>
							</p>

						</form>
				</div>
			</div>

		</div>
	<?php

	}

	/**
	 * Handle review submission
	 *
	 * This is called via admin_post_{action} when form is submitted.
	 *
	 * @since 1.1.4.5
	 *
	 */
	public function omapi_send_review() {

		// Check our form nonce
		if ( ! wp_verify_nonce( $_POST['omapi-review-nonce'], 'omapi-submit-review' ) ) {
			die( 'Unable to process request');
		}

		$user_id = get_current_user_id();

		// Setup empty defaults
		$user_review    = '';
		$user_email     = '';
		$user_name      = '';
		$user_creds     = '';
		$user_source    = '';
		$user_api       = $this->base->get_api_credentials();

		if ( isset( $_POST['user-review'] ) ) {
			$user_review = sanitize_text_field( $_POST['user-review'] );
		}
		if ( isset( $_POST['user-email'] ) ) {
			$user_email = sanitize_email( $_POST['user-email'] );
		}
		if ( isset( $_POST['user-name'] ) ) {
			$user_name = sanitize_text_field( $_POST['user-name'] );
		}
		if ( isset( $_POST['user-credentials'] ) ) {
			$user_creds = sanitize_text_field( $_POST['user-credentials'] );
		}
		if ( isset( $_POST['user-source'] ) ) {
			$user_source = esc_url( $_POST['user-source'] );
		}

		// Add data into query
		$data_array = array(
			'user-review'   => $user_review,
			'user-email'    => $user_email,
			'user-name'     => $user_name,
			'user-creds'    => $user_creds,
			'user-source'   => $user_source,
			'user-key'      => $user_api['key'],
			'user-id'       => $user_api['user']
		);

		// Save everything passed in to user meta as well
		update_user_meta( $user_id, 'omapi_review_data', $data_array );


		// Check for Name, Review, Email
		if (  $user_name === '' || $user_review === '' || $user_email === '' ) {
			$message = 'required-fields';
			wp_redirect( add_query_arg( 'action', $message , admin_url( 'admin.php?page=optin-monster-api-review' ) ) );
			exit;
		}

		// All good, make it pretty for the request
		$request_body = http_build_query( $data_array, '', '&' );

		// Build the headers of the request.
		$headers = array(
			'Content-Type'   => 'application/x-www-form-urlencoded',
			'Content-Length' => strlen( $request_body ),
			'Cache-Control'  => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
			'Pragma'		 => 'no-cache',
			'Expires'		 => 0,
			'OMAPI-Referer'  => site_url(),
			'OMAPI-Sender'   => 'WordPress'
		);

		// Setup data to be sent to the API.
		$data = array(
			'headers'   => $headers,
			'body'      => $request_body,
			'timeout'   => 3000,
			'sslverify' => false
		);

		// Perform the query and retrieve the response.
		$response      = wp_remote_post( esc_url_raw( $this->url ), $data );
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( is_wp_error( $response_body ) ) {
			//$action_query = 'error';
		}

		$message = isset($action_query) ? $action_query : 'success';

		// Stop the review pointers from showing since they tried submitting a valid form
		$this->dismiss_all_the_pointers();

		// Update array
		$data_array['status'] = 'finished';

		// Add status to review meta
		update_user_meta( $user_id, 'omapi_review_data', $data_array );

		//reload review page and end things
		wp_redirect( add_query_arg( 'action', $message , admin_url( 'admin.php?page=optin-monster-api-review' ) ) );
		exit;
	}

	/**
	 * Maybe add in our pointer
	 *
	 * @since 1.1.4.5
	 * @param $hook_suffix
	 */
	public function maybe_enqueue_pointer( $hook_suffix ) {

		$enqueue_pointer_script_style = false;

		$current_screen = get_current_screen();
		$show_on = array('dashboard', 'plugins', 'toplevel_page_optin-monster-api-settings' );

		if(  ! in_array( $current_screen->id, $show_on ) ) {
			return;
		}

		// Get array list of dismissed pointers for current user and convert it to array
		$dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		// Check if our pointer is not among dismissed ones and that the user should see this
		if( !in_array( 'omapi_review_pointer', $dismissed_pointers ) && current_user_can('activate_plugins')  ) {
			$enqueue_pointer_script_style = true;

			// Add footer scripts using callback function
			add_action( 'admin_print_footer_scripts', array( $this, 'pointer_review_content') );
		}

		// Enqueue pointer CSS and JS files, if needed
		if( $enqueue_pointer_script_style ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
		}

	}

	/**
	 * Maybe add in our reminder pointer
	 *
	 * @since 1.1.4.5
	 * @param $hook_suffix
	 */
	public function maybe_enqueue_reminder_pointer( $hook_suffix ) {

		$enqueue_pointer_script_style = false;

		$current_screen = get_current_screen();
		$show_on = array('dashboard', 'plugins', 'toplevel_page_optin-monster-api-settings' );

		if(  ! in_array( $current_screen->id, $show_on ) ) {
			return;
		}

		$reminder_time = get_user_meta( get_current_user_id(), 'omapi_reminder', true );
		if ( $reminder_time === '') {
			return;
		}

		// Get array list of dismissed pointers for current user and convert it to array
		$dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

		// Make sure the initial pointer has been viewed and user still has permissions
		if( in_array( 'omapi_review_pointer', $dismissed_pointers ) && !in_array( 'omapi_reminder_pointer', $dismissed_pointers) && current_user_can('activate_plugins')  ) {

			if ( current_time('timestamp') > $reminder_time ) {
				$enqueue_pointer_script_style = true;

				// Add footer scripts using callback function
				add_action( 'admin_print_footer_scripts', array( $this, 'pointer_review_content_reminder') );
			}

		}

		// Enqueue pointer CSS and JS files, if needed
		if( $enqueue_pointer_script_style ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
		}

	}

	/**
	 * Ask for OptinMonster review
	 *
	 * @since 1.1.4.5
	 */
	public function pointer_review_content() {

		$pointer_content  = '<h3 class="omapi-pointer_review">' . __( 'Rate OptinMonster', 'optin-monster-api' ) . '</h3>';
		$pointer_content .= '<p><strong>' . __("Thank you for using OptinMonster.","optin-monster-api") . '</strong></p>';
		$pointer_content .= '<p>' . __("Would you mind taking a moment to rate it? It wont take more than two minutes.", "optin-monster-api") . '</p>';
		$pointer_content .= '<p><strong>' . __("Thanks for your support!", "optin-monster-api") . '</strong></p>';
		$pointer_content .= '<p><a class="button button-primary button-hero omapi-pointer_button" href="' . esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-review' ) ) . '">' . __("Rate OptinMonster Now","optin-monster-api") . '</a></p>';
		$pointer_content .= '<p><a id="omapi-pointer_remind-me-later" class="button button-secondary button-hero omapi-pointer_button" href="#" >' . __("Remind me later","optin-monster-api") . '</a></p>';
		$nothanks = __( 'No, thanks', 'optin-monster-api' );
		?>

		<script type="text/javascript">
			jQuery(document).ready( function($) {
				var nothanks = '<?php echo $nothanks; ?>';
				$('#toplevel_page_optin-monster-api-settings').pointer({
					content: '<?php echo $pointer_content; ?>',
					position: {
						edge: 'left',
						align: 'middle'
					},
					pointerWidth:	270,
					buttons: function( event, t ) {
						var button = $('<a class="close omapi-pointer_close-override" href="#">' + nothanks + '</a>');

						return button.bind( 'click.pointer', function(e) {
							e.preventDefault();
							t.element.pointer('close');
						});
					},
					close: function() {
						$.post( ajaxurl, {
							pointer: 'omapi_review_pointer',
							action: 'dismiss-wp-pointer'
						});
					}
				}).pointer('open');

				$('#omapi-pointer_remind-me-later').click(function( event ) {
					event.preventDefault();
					//Set the pointer to be closed for this user
					$.post( ajaxurl, {
						pointer: 'omapi_review_pointer',
						action: 'dismiss-wp-pointer'
					});
					$.post( ajaxurl, {
						omapi_reminder: 'omapi_review_pointer',
						action: 'set_omapi_review_reminder'
					});
					$('#omapi-pointer_remind-me-later').parents('.wp-pointer').remove();
				});
			});
		</script>
		<style type="text/css">
			.wp-pointer-content h3.omapi-pointer_review {
				background: #0085BA;
			}
			.wp-pointer-content h3.omapi-pointer_review::before {
				content: "\f529";
				color: #fff;
				background: transparent;
			}
			.wp-pointer-content .omapi-pointer_button {
				width: 100%;
				text-align: center;
			}
			.wp-pointer-buttons .close.omapi-pointer_close-override {
				float: left;
				margin-left: 15px;
			}
		</style>
	<?php
	}

	/**
	 * Requested reminder to review OptinMonster
	 *
	 * @since 1.1.4.5
	 */
	public function pointer_review_content_reminder(){

		$pointer_content  = '<h3 class="omapi-pointer_review">' . __( 'Rate OptinMonster', 'optin-monster-api' ) . '</h3>';
		$pointer_content .= '<p><strong>' . __("Thank you for using OptinMonster.","optin-monster-api") . '</strong></p>';
		$pointer_content .= '<p>' . __("You asked to be reminded to review OptinMonster.","optin-monster-api") . '</p>';
		$pointer_content .= '<p><strong>' . __("Thanks for your support!","optin-monster-api") . '</strong></p>';
		$pointer_content .= '<p><a id="omapi-pointer_review-now" class="button button-primary button-hero omapi-pointer_button" href="' . esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-review' ) ) . '">' . __("Rate OptinMonster Now","optin-monster-api") . '</a></p>';
		$pointer_content .= '<p><a id="omapi-pointer_remind-me-later" class="button button-secondary button-hero omapi-pointer_button" href="#" >' . __("Remind me later","optin-monster-api") . '</a></p>';
		$nothanks = __( 'No, thanks', 'optin-monster-api' );
		?>

		<script type="text/javascript">
			jQuery(document).ready( function($) {
				var nothanks = '<?php echo $nothanks; ?>';
				$('#toplevel_page_optin-monster-api-settings').pointer({
					content: '<?php echo $pointer_content; ?>',
					position: {
						edge: 'left',
						align: 'middle'
					},
					pointerWidth:	270,
					buttons: function( event, t ) {
						var button = $('<a class="close omapi-pointer_close-override" href="#">' + nothanks + '</a>');

						return button.bind( 'click.pointer', function(e) {
							e.preventDefault();
							t.element.pointer('close');
						});
					},
					close: function() {
						$.post( ajaxurl, {
							pointer: 'omapi_reminder_pointer',
							action: 'dismiss-wp-pointer'
						});
					}
				}).pointer('open');

				$('#omapi-pointer_remind-me-later').click(function( event ) {
					event.preventDefault();
					$.post( ajaxurl, {
						omapi_reminder: 'omapi_review_pointer',
						action: 'set_omapi_review_reminder'
					});
					$('#omapi-pointer_remind-me-later').parents('.wp-pointer').remove();
				});

				$('#omapi-pointer_review-now').click(function( event ) {
					event.preventDefault();
					$.post( ajaxurl, {
						omapi_reminder: 'omapi_review_pointer',
						action: 'set_omapi_review_reminder'
					});
					//Set the pointer to be closed for this user
					$.post( ajaxurl, {
						pointer: 'omapi_review_pointer',
						action: 'dismiss-wp-pointer'
					});
					$('#omapi-pointer_remind-me-later').parents('.wp-pointer').remove();
				});
			});
		</script>
		<style type="text/css">
			.wp-pointer-content h3.omapi-pointer_review {
				background: #0085BA;
			}
			.wp-pointer-content h3.omapi-pointer_review::before {
				content: "\f529";
				color: #fff;
				background: transparent;
			}
			.wp-pointer-content .omapi-pointer_button {
				width: 100%;
				text-align: center;
			}
			.wp-pointer-buttons .close.omapi-pointer_close-override {
				float: left;
				margin-left: 15px;
			}
		</style>
	<?php

	}

	/**
	 * Set the review reminder user_meta
	 *
	 * @since 1.1.4.5
	 */
	public function set_user_review_reminder() {

		//set reminder time 1 week from now
		$reminder_time = (int) strtotime("+1 week");

		//update user_meta with request
		update_user_meta( get_current_user_id(), 'omapi_reminder', $reminder_time);

		wp_die();

	}

	/**
	 * Add admin notices as needed for review
	 *
	 * @since 1.1.4.5
	 *
	 */
	public function notices() {

		if ( ! isset ( $_GET['action'] ) ) {
			return;
		}

		if ( 'success' === $_GET['action'] ) {
			echo '<div class="notice notice-success"><p>' . __( 'Review has been sent.', 'optin-monster-api' );
			echo '<a href="' . esc_url_raw( admin_url( 'admin.php?page=optin-monster-api-settings' ) ) . '" class="button button-primary button-large omapi-new-optin" title="Go to OptinMonster overview" style="margin-left: 15px;">Return to OptinMonster</a></p></div>';

		}
		if ( 'required-fields' === $_GET['action'] ) {
			echo '<div class="error is-dismissible"><p>' . __( 'Your Name, Review, and Email address are required to submit your review.', 'optin-monster-api' ) . '</p></div>';
		}

	}

	/**
	 * Add all review pointers as read for current user
	 *
	 * @since 1.1.4.5
	 *
	 */
	public function dismiss_all_the_pointers() {

		$dismissed = array_filter( explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) ) );
		$review_pointers = array('omapi_review_pointer', 'omapi_reminder_pointer');

		$dismissed = array_merge( $dismissed, $review_pointers);
		$dismissed = implode( ',', $dismissed);

		update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed );

	}

}