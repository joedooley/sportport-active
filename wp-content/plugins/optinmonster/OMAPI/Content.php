<?php
/**
 * Content class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Content {

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
     * @var object
     */
    public $base;

	/**
	 * The current view slug
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $view;

	/**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

	    // Set our object.
	    $this->set();

		// Load actions and filters.
		add_action( 'optin_monster_api_content_before', array( $this, 'form_start' ), 0, 2 );
		add_action( 'optin_monster_api_content_after', array( $this, 'form_end' ), 9999 );
        add_action( 'optin_monster_api_content_api', array( $this, 'api' ), 10, 2 );
        add_action( 'optin_monster_api_content_optins', array( $this, 'optins' ), 10, 2 );
        add_action( 'optin_monster_api_content_settings', array( $this, 'settings' ), 10, 2 );
	    add_action( 'optin_monster_api_content_migrate', array( $this, 'migrate' ), 10, 2 );

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
        $this->optin    = isset( $_GET['optin_monster_api_id'] ) ? $this->base->get_optin( absint( $_GET['optin_monster_api_id'] ) ) : false;

    }

    /**
     * Loads the starting form HTML for the panel content.
     *
     * @since 1.0.0
     *
     * @param string $id    The panel ID we are targeting.
     * @param string $panel The panel name we are targeting.
     */
    public function form_start( $id, $panel ) {

	    ?>
	    <form id="omapi-form-<?php echo sanitize_html_class( $id ); ?>" class="omapi-form" method="post" action="<?php echo esc_attr( stripslashes( $_SERVER['REQUEST_URI'] ) ); ?>">
		    <?php wp_nonce_field( 'omapi_nonce_' . $id, 'omapi_nonce_' . $id ); ?>
		    <input type="hidden" name="omapi_panel" value="<?php echo $id; ?>" />
		    <input type="hidden" name="omapi_save" value="true" />
		    <?php if ( 'settings' == $this->view ) : ?>
		    <input type="hidden" name="omapi[<?php echo esc_attr( $this->view ); ?>][wpform]" value="true" />
		    <?php endif; ?>
		    <h3>
			    <?php if ( isset( $_GET['optin_monster_api_action'] ) && 'edit' == $_GET['optin_monster_api_action'] ) : ?>
				<?php printf( __( 'Output Settings for %s', 'optin-monster-api' ), esc_html( $this->optin->post_title ) ); ?>
			    <span class="omapi-back"><a class="button button-secondary button-small" href="<?php echo esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => 'optins' ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ); ?>" title="<?php esc_attr_e( 'Back to optin overview', 'optin-monster-api' ); ?>"><?php _e( 'Back to Overview', 'optin-monster-api' ); ?></a></span>
			    <?php else : ?>
			    <?php echo esc_html( $panel ); ?>
			    <?php endif; ?>
			</h3>
	    <?php

		// Action to load success/reset messages.
		do_action( 'optin_monster_api_messages_' . $id );

    }

    /**
     * Loads the ending form HTML for the panel content.
     *
     * @since 1.0.0
     */
    public function form_end() {

		// Load different form buttons based on if credentials have been supplied or not.
		if ( ! $this->base->get_api_credentials() ) :
		?>
	    	<p class="submit">
		    	<input class="button button-primary" type="submit" name="omapi_submit" value="<?php esc_attr_e( 'Connect to OptinMonster', 'optin-monster-api' ); ?>" tabindex="749" />
	    	</p>
	    </form>
	    <?php
		elseif ( 'optins' == $this->view ) :
			if ( isset( $_GET['optin_monster_api_action'] ) && 'edit' == $_GET['optin_monster_api_action'] ) :
			?>
		    	<p class="submit">
			    	<input class="button button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save Settings', 'optin-monster-api' ); ?>" tabindex="749" />
		    	</p>
		    </form>
		    <?php
			else :
		    ?>
		    	<p class="submit">
			    	<input class="button button-primary" type="submit" name="omapi_refresh" value="<?php esc_attr_e( 'Refresh Optins', 'optin-monster-api' ); ?>" tabindex="749" />
			    	<a class="button button-secondary" href="<?php echo wp_nonce_url( esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'cookies' ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ), 'omapi-action' ); ?>" title="<?php esc_attr_e( 'Clear Local Cookies', 'optin-monster-api' ); ?>"><?php _e( 'Clear Local Cookies', 'optin-monster-api' ); ?></a>
		    	</p>
		    </form>
		    <?php
			endif;
		elseif ( 'migrate' == $this->view ) :
			?>
	    </form>
	    <?php
		else :
	    ?>
	    	<p class="submit">
		    	<input class="button button-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Save Settings', 'optin-monster-api' ); ?>" tabindex="749" />
	    	</p>
	    </form>
	    <?php
		endif;

    }

    /**
     * Loads the content output for the API panel.
     *
     * @since 1.0.0
     *
     * @param string $panel  The panel name we are targeting.
     * @param object $object The menu object (useful for settings helpers).
     */
    public function api( $panel, $object ) {

		if ( ! $this->base->get_api_credentials() ) : ?>
		<p class="omapi-red"><strong><?php _e( 'You must authenticate your OptinMonster account before you can use OptinMonster on this site.', 'optin-monster-api' ); ?></strong></p>
		<p><em><?php printf( __( 'Need an OptinMonster account? <a href="%s" title="Click here to view OptinMonster plans and pricing" target="_blank">Click here to view OptinMonster plans and pricing.</a>', 'optin-monster-api' ), 'https://optinmonster.com/pricing/' ); ?></em></p>
		<?php endif; ?>
        <?php echo $object->get_setting_ui( 'api', 'user' ); ?>
        <?php echo $object->get_setting_ui( 'api', 'key' ); ?>
        <?php

    }

    /**
     * Loads the content output for the Database panel.
     *
     * @since 1.0.0
     *
     * @param string $panel  The panel name we are targeting.
     * @param object $object The menu object (useful for settings helpers).
     */
    public function optins( $panel, $object ) {

        $optin_view = isset( $_GET['optin_monster_api_action'] ) && 'edit' == $_GET['optin_monster_api_action'] ? 'edit' : 'overview';
        if ( 'edit' == $optin_view ) {
	        $this->optin_edit( $object );
        } else {
	        $this->optin_overview( $object );
        }

    }

    /**
     * Shows the optins loaded on the site.
     *
     * @since 1.0.0
     *
     * @param object $object The menu object (useful for settings helpers).
     */
    public function optin_overview( $object ) {

        $optins = $this->base->get_optins();
        $i 	    = 0;
        if ( $optins ) :
        ?>
        <?php foreach ( $optins as $optin ) : $class = 0 == $i ? ' omapi-optin-first' : '';
	        $status = (bool) get_post_meta( $optin->ID, '_omapi_enabled', true ) ? '<span class="omapi-green">' . __( 'Live', 'optin-monster-api' ) . '</span>' : '<span class="omapi-red">' . __( 'Disabled', 'optin-monster-api' ) . '</span>';
	        $test = (bool) get_post_meta( $optin->ID, '_omapi_test', true );
	        $test_class = $test ? ' omapi-test-mode' : '';
	    ?>
        <p class="omapi-optin<?php echo $class . $test_class; ?>">
	        <a href="<?php echo esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'edit', 'optin_monster_api_id' => $optin->ID ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ); ?>" title="<?php printf( esc_attr__( 'Manage output settings for %s', 'optin-monster-api' ), $optin->post_title ); ?>"><?php echo $optin->post_title; ?></a>
	        <?php if ( $test ) : ?>
	        <span class="omapi-test"><?php _e( 'Test Mode', 'optin-monster-api' ); ?></span>
	        <?php endif; ?>
	        <span class="omapi-status"><?php echo $status; ?></span><br>
	        <span class="omapi-slug"><?php echo $optin->post_name; ?></span>
	        <span class="omapi-links"><?php echo $this->get_optin_links( $optin->ID ); ?></span>
        </p>
        <?php $i++; endforeach; ?>
        <?php else : ?>
        <p><strong><?php _e( 'No optins could be retrieved for this site.', 'optin-monster-api' ); ?></strong></p>
        <?php
	    endif;

    }

    /**
     * Loads the content output for the Settings panel.
     *
     * @since 1.0.0
     *
     * @param string $panel  The panel name we are targeting.
     * @param object $object The menu object (useful for settings helpers).
     */
    public function settings( $panel, $object ) {

        echo $object->get_setting_ui( 'settings', 'cookies' );

    }

    /**
     * Shows the editing interface for optins.
     *
     * @since 1.0.0
     *
     * @param object $object The menu object (useful for settings helpers).
     */
    public function optin_edit( $object ) {

	    //Check for existing optins
        if ( $this->optin ) {
	        $type = get_post_meta( $this->optin->ID, '_omapi_type', true );
			echo $object->get_setting_ui( 'optins', 'enabled' );

			if ( 'sidebar' !== $type ) {
				if ( 'post' == $type ) {
					echo $object->get_setting_ui( 'optins', 'automatic' );
				} else {
					echo $object->get_setting_ui( 'optins', 'global' );
				}
				echo $object->get_setting_ui( 'optins', 'users' );
				echo $object->get_setting_ui( 'optins', 'never' );
				echo $object->get_setting_ui( 'optins', 'only' );
				echo $object->get_setting_ui( 'optins', 'categories' );
				echo $object->get_setting_ui( 'optins', 'taxonomies' );
				echo $object->get_setting_ui( 'optins', 'show' );
			}
	        echo $object->get_setting_ui( 'optins', 'shortcode' );
	        echo $object->get_setting_ui( 'optins', 'shortcode_output' );

			// Add support for MailPoet if the plugin is active.
			if ( class_exists( 'WYSIJA' ) ) {
				echo $object->get_setting_ui( 'optins', 'mailpoet' );
				echo $object->get_setting_ui( 'optins', 'mailpoet_list' );
			}
        } else {
        	?>
	        <p><strong><?php _e( 'No optin could be retrieved for the ID specified.', 'optin-monster-api' ); ?></strong></p>
	        <?php
	    }

    }

    /**
     * Returns the action links for the optin.
     *
     * @since 1.0.0
     *
     * @param int $optin_id  The optin ID to target.
     * @return string $links HTML string of action links.
     */
    public function get_optin_links( $optin_id ) {

		$test   	 = get_post_meta( $optin_id, '_omapi_test', true );
		$ids		 = get_post_meta( $optin_id, '_omapi_ids', true );
		$edit_id	 = absint( $ids[0] );
		$test_link   = $test ? __( 'Disable Test Mode', 'optin-monster-api' ) : __( 'Enable Test Mode', 'optin-monster-api' );
		$test_desc   = $test ? esc_attr__( 'Disable test mode for this optin', 'optin-monster-api' ) : esc_attr__( 'Enable test mode for this optin', 'optin-monster-api' );
		$status 	 = (bool) get_post_meta( $optin_id, '_omapi_enabled', true );
		$status_link = $status ? __( 'Disable', 'optin-monster-api' ) : __( 'Go Live', 'optin-monster-api' );
		$status_desc = $status ? esc_attr__( 'Disable this optin', 'optin-monster-api' ) : esc_attr__( 'Go live with this optin', 'optin-monster-api' );
		$links  	 = array();
		$links['editd']  = '<a href="' . esc_url_raw( add_query_arg( array( 'om_optin_id' => $edit_id ), 'https://app.optinmonster.com/account/edit/' ) ) . '" title="' . esc_attr__( 'Edit this optin on the OptinMonster App', 'optin-monster-api' ) . '" target="_blank">Edit Design</a>';
		$links['edito']  = '<a href="' . esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'edit', 'optin_monster_api_id' => $optin_id ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ) . '" title="' . esc_attr__( 'Edit the output settings for this optin', 'optin-monster-api' ) . '">Edit Output Settings</a>';
		$links['status'] = '<a href="' . wp_nonce_url( esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'status', 'optin_monster_api_id' => $optin_id ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ), 'omapi-action' ) . '" title="' . $status_desc . '">' . $status_link . '</a>';
		$links['test'] = '<a href="' . wp_nonce_url( esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'test', 'optin_monster_api_id' => $optin_id ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ), 'omapi-action' ) . '" title="' . $test_desc . '">' . $test_link . '</a>';
        $links['delete'] = '<a class="omapi-red" href="' . wp_nonce_url( esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'delete', 'optin_monster_api_id' => $optin_id ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ), 'omapi-action' ) . '" title="' . esc_attr__( 'Delete this optin locally', 'optin-monster-api' ) . '">Delete</a>';

        $links = apply_filters( 'optin_monster_api_action_links', $links, $optin_id );
        return implode( ' | ', (array) $links );

    }

	public function migrate() {

		$migration_data = get_option( '_om_migration_data' );
		?>
		<p><?php _e( 'You can migrate all of your existing OptinMonster data (optin forms, settings & integrations) to the new hosted platform. Just click the "Migrate" button below.', 'optin-monster-api' ); ?></p>
		<p class="submit">
			<a class="button button-primary" href="<?php echo wp_nonce_url( esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'migrate' ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ), 'omapi-action' ); ?>"><?php _e( 'Migrate', 'optin-monster-api' ); ?></a>
		</p>

		<?php
		if ( $migration_data ) : ?>
			<h3><?php _e( 'Migration Results', 'optin-monster-api' ); ?></h3>
			<hr />
			<?php if ( ! empty( $migration_data['errors'] ) ) : ?>
				<h4><?php _e( 'Migration Errors', 'optin-monster-api' ); ?></h4>
				<ul>
					<?php foreach ( $migration_data['errors'] as $error ) : ?>
						<li><span class="dashicons dashicons-no"></span> <?php echo $error; ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( isset( $migration_data['site'] ) ) : ?>
				<h4><?php _e( 'Site Information', 'optin-monster-api' ); ?></h4>
				<span class="dashicons dashicons-yes"></span> <?php printf( __( '%s has been registered.', 'optin-monster-api' ), $migration_data['site']->name ); ?>
			<?php endif; ?>
			<h4><?php _e( 'Optin Forms', 'optin-monster-api' ); ?></h4>
			<ul>
				<?php foreach ( $migration_data['migrated_optins'] as $optin_id ) : $optin = get_post( $optin_id ); ?>
				<?php if ( $optin ) : ?>
				<li><span class="dashicons dashicons-yes"></span> <?php echo $optin->post_title; ?></li>
				<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<?php if ( isset( $migration_data['integrations'] ) ) : ?>
				<h4>Integrations</h4>
				<p><span class="dashicons dashicons-yes"></span> <?php echo $migration_data['integrations']; ?></p>
			<?php endif; ?>

			<h3><?php _e( 'Reset Migration', 'optin-monster-api' ); ?></h3>
			<hr />
			<p><?php _e( 'If your optin forms, site information or integrations did not migrate properly you can reset the migration and try again. Please note that this can cause some data duplication in your account.', 'optin-monster-api' ); ?></p>
			<p class="submit">
				<a class="button button-secondary" href="<?php echo wp_nonce_url( esc_url_raw( add_query_arg( array( 'optin_monster_api_view' => $this->view, 'optin_monster_api_action' => 'migrate-reset' ), admin_url( 'admin.php?page=optin-monster-api-settings' ) ) ), 'omapi-action' ); ?>"><?php _e( 'Reset Migration', 'optin-monster-api' ); ?></a>
			</p>

		<?php endif;
	}

}