<?php
function woogle_admin_menu() {
	add_menu_page( 'Woogle', 'Woogle', 'manage_woocommerce', 'woogle', 'woogle_render_admin_page', 'dashicons-tag' );
}
add_action( 'admin_menu', 'woogle_admin_menu' );

function woogle_render_admin_page() {
	?>
	<p>Redirecting...</p>
	<script>
	window.location = <?php echo json_encode( admin_url( '/admin.php?page=wc-settings&tab=products&section=woogle') ); ?>;
	</script>
	<?php
}

function woogle_load_admin() {
	if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'woogle' ) {
		wp_redirect( admin_url( '/admin.php?page=wc-settings&tab=products&section=woogle') );
	}
}
add_action( 'admin_init', 'woogle_load_admin' );

function woogle_settings_section( $sections ) {
	$sections['woogle'] = __( 'Woogle', 'woogle' );
	return $sections;
}
add_filter( 'woocommerce_get_sections_products', 'woogle_settings_section' );

function woogle_get_settings( $settings, $current_section ) {
	// Check the current section is Woogle
	if ( $current_section == 'woogle' ) {

		$section_settings = array();

		// Add Title to the Settings
		$section_settings[] = array( 
			'name' => __( 'Woogle Settings', 'woogle' ), 
			'type' => 'title', 
			'desc' => __( 'The following options are used to configure Woogle for WooCommerce', 'woogle' ), 
			'id' => 'woogle'
		);
		
		// Add thickboxes for settings
		$section_settings[] = array(
			'type' => 'woogle_thickboxes'
		);
		
		// Add messages section
		$section_settings[] = array(
			'type' => 'woogle_settings_messges'
		);
		
		// License Key
		$section_settings[] = array(
			'name' => __( 'License Key', 'woogle' ),
			'desc_tip' => __( 'The license key issued after purchase.', 'woogle' ),
			'id' => 'woogle_license_key',
			'type' => 'text',
			'desc' => __( 'The license key issued after purchase.', 'woogle' ),
			'class' => 'woogle-input'
		);

		// Merchant ID option
		$section_settings[] = array(
			'name' => __( 'Merchant ID', 'woogle' ),
			'desc_tip' => __( 'Your Google Merchant ID', 'woogle' ),
			'id' => 'woogle_merchant_id',
			'type' => 'text',
			'desc' => __( 'Your Google Merchant ID. <a title="Where is my Merchant ID?" href="#TB_inline?width=600&height=550&inlineId=woogle-thickbox-merchant-id" class="thickbox">Where do I find this?</a>', 'woogle' ),
			'class' => 'woogle-input'
		);
		
		// Application Name option
		$section_settings[] = array(
			'name' => __( 'Application Name', 'woogle' ),
			'desc_tip' => __( 'Your Google Shopping Content API Application Name', 'woogle' ),
			'id' => 'woogle_application_name',
			'type' => 'text',
			'desc' => __( 'Your Google Shopping Content API Application Name. Choose a name for this application.', 'woogle' ),
			'class' => 'woogle-input'
		);
		
		// Client ID option
		$section_settings[] = array(
			'name' => __( 'Client ID', 'woogle' ),
			'desc_tip' => __( 'Your Google Shopping Content API Client ID', 'woogle' ),
			'id' => 'woogle_client_id',
			'type' => 'text',
			'desc' => __( 'Your Google Shopping Content API Client ID. <a title="Where is my Client ID?" href="#TB_inline?width=600&height=550&inlineId=woogle-thickbox-client-id" class="thickbox">Where do I find this?</a>', 'woogle' ),
			'class' => 'woogle-input'
		);
		
		// Client Secret option
		$section_settings[] = array(
			'name' => __( 'Client Secret', 'woogle' ),
			'desc_tip' => __( 'Your Google Shopping Content API Client Secret', 'woogle' ),
			'id' => 'woogle_client_secret',
			'type' => 'text',
			'desc' => __( 'Your Google Shopping Content API Client Secret. <a title="Where is my Client Secret?" href="#TB_inline?width=600&height=550&inlineId=woogle-thickbox-client-secret" class="thickbox">Where do I find this?</a>', 'woogle' ),
			'class' => 'woogle-input'
		);
		
		// Add update button
		$section_settings[] = array(
			'type' => 'woogle_update_products_button'
		);
		
		// Add revoke access button
		$section_settings[] = array(
			'type' => 'woogle_revoke_access_button'
		);
		
		// Client Secret option
		$section_settings[] = array(
			'name' => __( 'Update on Checkout', 'woogle' ),
			'id' => 'woogle_update_on_checkout',
			'type' => 'checkbox',
			'desc' => __( 'Automatically update products when they are purchased at checkout.', 'woogle' ),
			'class' => 'woogle-input'
		);

		$section_settings[] = array( 'type' => 'sectionend', 'id' => 'woogle' );

		return $section_settings;
	}
	
	// Default settings
	return $settings;
}
add_filter( 'woocommerce_get_settings_products', 'woogle_get_settings', 10, 2 );

function woogle_save_license_key() {
	woogle_update_license_key_status();
}
add_action( 'add_option_woogle_license_key', 'woogle_save_license_key' );
add_action( 'update_option_woogle_license_key', 'woogle_save_license_key' );

function woogle_thickboxes() {
	include( plugin_dir_path( woogle_get_plugin_file() ) . 'templates/settings/thickboxes.php' );
}
add_action( 'woocommerce_admin_field_woogle_thickboxes', 'woogle_thickboxes' );

function woogle_settings_messages() {
	woogle_print_messages();
}
add_action( 'woocommerce_admin_field_woogle_settings_messages', 'woogle_settings_messages' );

function woogle_update_products_button() {
	include( plugin_dir_path( woogle_get_plugin_file() ) . 'templates/settings/update-products-button.php' );
}
add_action( 'woocommerce_admin_field_woogle_update_products_button', 'woogle_update_products_button' );

function woogle_revoke_access_button() {
	include( plugin_dir_path( woogle_get_plugin_file() ) . 'templates/settings/revoke-access-button.php' );
}
add_action( 'woocommerce_admin_field_woogle_revoke_access_button', 'woogle_revoke_access_button' );

function woogle_get_messages() {
	if ( empty( $_COOKIE['woogle_messages'] ) ) {
		return array();
	}
	$messages = unserialize( base64_decode( $_COOKIE['woogle_messages'] ) );
	return $messages;
}

function woogle_add_message( $message, $type = 'updated' ) {
	$messages = woogle_get_messages();

	foreach ( $messages as $existing_message ) {
		if ( $existing_message['message'] == $message && $existing_message['type'] == $type ) {
			// Message already exists
			return;
		}
	}
	
	$messages[] = array(
		'message' => $message,
		'type' => $type
	);
	
	$messages_cookie = base64_encode( serialize( $messages ) );
	setcookie( 'woogle_messages', $messages_cookie, time() + 30 );
	$_COOKIE['woogle_messages'] = $messages_cookie;
}

function woogle_print_messages() {
	if ( is_ajax() ) {
		return;
	}
	
	$messages = woogle_get_messages();
	if ( empty( $messages ) ) {
		return;
	}

	foreach ( $messages as $message ) {
		?>
		<div class="<?php echo esc_attr( $message['type'] ); ?>">
			<p><?php echo $message['message']; ?></p>
		</div>
		<?php
	}
	if ( headers_sent() ) {
		echo '<script>woogleDeleteMessages()</script>';
	} else {
		setcookie( 'woogle_messages', '', time() - 3600 );
	}
	$_COOKIE['woogle_messages'] = '';
}
add_action( 'admin_notices', 'woogle_print_messages' );