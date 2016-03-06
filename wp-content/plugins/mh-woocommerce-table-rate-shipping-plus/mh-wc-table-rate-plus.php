<?php
/*
Copyright (c) 2015 John Currie

Plugin Name: WooCommerce Table Rate Shipping Plus
Plugin URI: http://mangohour.com/plugins/woocommerce-table-rate-shipping
Description: Calculate shipping costs based on destination, weight and price.
Version: 1.6.0
Author: mangohour
Author URI: http://mangohour.com/
Text Domain: mhtr
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('MHTRP_DOMAIN', 'mhtr');
define('MHTRP_EDD_URL', 'http://edd.hrexu.co.uk');
define('MHTRP_EDD_ITEM_NAME', 'Table Rate Shipping Plus');
define('MHTRP_EDD_ITEM_ID', 9);
define('MHTRP_VERSION', '1.6.0');

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function mh_wc_table_rate_plus_updater() {

	$key = trim( get_option( 'mh_wc_table_rate_plus_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( MHTRP_EDD_URL, __FILE__, array( 
			'version' 	=> MHTRP_VERSION,
			'license' 	=> $key,
			'item_name' => MHTRP_EDD_ITEM_NAME,
			'item_id'	=> MHTRP_EDD_ITEM_ID,
			'author' 	=> 'mangohour'
		)
	);

}
add_action( 'admin_init', 'mh_wc_table_rate_plus_updater', 0 );

/**
 * Check if WooCommerce is active
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	function mh_wc_table_rate_plus_init() {
		if ( ! class_exists( 'MH_Table_Rate_Plus_Shipping_Method' ) ) {
			class MH_Table_Rate_Plus_Shipping_Method extends WC_Shipping_Method {

				/**
				 * Setup defaults and option locations.
				 */
				public function __construct() {
					$this->id                 			= 'mh_wc_table_rate_plus'; // Id for your shipping method. Should be uunique.
					$this->table_rate_option  			= $this->id.'_table_rates';
					$this->last_table_rate_id_option	= $this->id.'_last_table_rate_id';
					$this->zones_option 	  			= $this->id.'_zones';
					$this->last_zone_id_option 			= $this->id.'_last_zone_id';
					$this->services_option 	  			= $this->id.'_services';
					$this->last_service_id_option 		= $this->id.'_last_service_id';
					$this->first_run_option 			= $this->id.'_first_run';
					$this->shipping_class_order_option 	= $this->id.'_shipping_class_order';
					$this->key_option 					= $this->id.'_key';
					$this->key_status_option 			= $this->id.'_key_status';
					$this->method_title       			= __( 'Table Rate Plus', MHTRP_DOMAIN );  // Title shown in admin
					$this->method_description 			= __( 'A table rate shipping plugin', MHTRP_DOMAIN ); // Description shown in admin
					
					$this->weight_unit = get_option('woocommerce_weight_unit');
			   		$this->curr_symbol = get_woocommerce_currency_symbol();
					
					// Import settings from other versions on first run
					$this->first_run();

					// Set table arrays and last ids
					$this->get_zones();
					$this->get_last_zone_id();
					
					$this->get_services();
					$this->get_last_service_id();
					
					$this->get_table_rates();
					$this->get_last_table_rate_id();
					
					$this->get_shipping_class_order();

					$this->init();
				}

				/**
				 * Initialises plugin.
				 */
				function init() {
			
					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
					
					$this->enabled = $this->settings['enabled'];
					$this->title = 'Table Rate Shipping'; // Shown in drop down and admin order screen
					
					$this->backup_file = WP_PLUGIN_DIR.'/mh-woocommerce-table-rate-shipping-plus/backup.json';
					$this->has_backup_file = (file_exists($this->backup_file) ? true : false);

					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_zones' ) );
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_services' ) );
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_table_rates' ) );
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_shipping_class_order' ) );
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_import' ) );
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'update_licence' ) );

				}
				
				/**
				 * Initialise form fields using WooCommerce API
				 */
				function init_form_fields() {
					$this->form_fields = array(
	                    'enabled' => array(
                            'title' 		=> __( 'Enable', MHTRP_DOMAIN ),
                            'type' 			=> 'checkbox',
                            'label' 		=> __( 'Enable this shipping method', MHTRP_DOMAIN ),
                            'default' 		=> 'no',
						),
	                    'licence_key' => array(
                            'title' 		=> __( 'License Key', MHTRP_DOMAIN ),
                            'description' 		=> __( 'Enter your license key, as shown in your confirmation email', MHTRP_DOMAIN ),
                            'type' 			=> 'text',
                            'desc_tip'     => true,
						),
						'tax_status' => array(
							'title' 		=> __( 'Tax Status', MHTRP_DOMAIN ),
							'type' 			=> 'select',
							'default' 		=> 'taxable',
							'options'		=> array(
								'taxable' 	=> __( 'Taxable', MHTRP_DOMAIN ),
								'none' 		=> __( 'None', MHTRP_DOMAIN ),
							),
						),
	                    'handling_fee' => array(
                            'title' 		=> __( 'Handling Fee', MHTRP_DOMAIN ),
                            'description' 		=> __( 'Fee, excluding tax. Leave blank to disable.', MHTRP_DOMAIN ),
                            'type' 			=> 'price',
                            'default' 		=> '0.00',
                            'desc_tip'     => true,
						),
						'calc_type' => array(
							'title' 		=> __( 'Calculation Type', MHTRP_DOMAIN ),
							'description' 		=> __( '<strong>Per order</strong> - shipping will be calculated based on whole cart, using the highest priority shipping class.<br><br><strong>Per shipping class</strong> - shipping will be calculated for each shipping class, then added together', MHTRP_DOMAIN ),
							'type' 			=> 'select',
							'default' 		=> 'per_order',
							'options'		=> array(
								'per_order' 	=> __( 'Per order', MHTRP_DOMAIN ),
								'per_class' 	=> __( 'Per shipping class', MHTRP_DOMAIN ),
							),
							'desc_tip'     => true,
						),
						'shipping_class_order_table' => array(
							'type'				=> 'shipping_class_order_table'
						),
						'zones_table' => array(
							'type'				=> 'zones_table',
						),
						'services_table' => array(
							'type'				=> 'services_table',
						),
						'table_rates_table' => array(
							'type'				=> 'table_rates_table'
						),
						'import' => array(
							'type'				=> 'import'
						),
					);
				}
				
				/**
				 * Import old settings if first run of plugin
				 */
				function first_run() {
				
					$free_id = 'mh_wc_table_rate';
				
					if (get_option($this->first_run_option) != '1') {
					
						// Import settings
						
						$free_id = 'mh_wc_table_rate';
						
						$free_zones = get_option( $free_id.'_zones' );
						$free_last_zone_id = get_option( $free_id.'_last_zone_id' );
						update_option( $this->zones_option, $free_zones );
						update_option( $this->last_zone_id_option, $free_last_zone_id );
						
						$free_table_rates = get_option($free_id.'_table_rates');
						$free_last_table_rate_id = get_option($free_id.'_last_table_rate_id');
						update_option( $this->table_rate_option, $free_table_rates );
						update_option( $this->last_table_rate_id_option, $free_last_table_rate_id );
						
						update_option( $this->first_run_option, 1 );
						
						// Create default shipping service
						
						$services = array();
						$services[] = array(
								'id' => '1',
								'name' => __( 'Standard Shipping', MHTRP_DOMAIN),
								'priority' => '10',
								'enabled' => '1',
							);
							
						update_option( $this->services_option, $services );
						update_option( $this->last_service_id_option, 1 );
					}
				}
				
				/**
				 * Update licence key
				 */
				function update_licence() {
					if (isset($_POST[$this->id .'_old_licence_key'])) {
						$old_licence_key = trim($_POST[$this->id .'_old_licence_key']);
						$new_licence_key = trim($this->settings['licence_key']);
						
						if (empty($new_licence_key)) {
							update_option( $this->key_option, '' );
							update_option( $this->key_status_option, 'invalid' );
						} else if (($new_licence_key != $old_licence_key)
							|| (get_option($this->key_status_option) != 'valid')) {
							update_option( $this->key_option, $new_licence_key );
							$this->activate_licence($new_licence_key);
						}

					}
				}

				/**
				 * Acticate licence key
				 */
				function activate_licence($licence_key=false) {

					// Get licence key from DB if not passed
					if ($licence_key == false) {
						$licence_key = get_option( $this->key_option );
					}

					$licence_key = trim($licence_key);

					// data to send in our API request
					$api_params = array( 
						'edd_action'=> 'activate_license', 
						'license' 	=> $licence_key, 
						'item_name' => urlencode( MHTRP_EDD_ITEM_NAME ), 
						'item_id'	=> MHTRP_EDD_ITEM_ID,
						'url'       => home_url()
					);
					
					// Call the custom API.
					$response = wp_remote_get( add_query_arg( $api_params, MHTRP_EDD_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

					// make sure the response came back okay
					if ( is_wp_error( $response ) ) {
						update_option( $this->key_status_option, 'invalid' );
						return false;
					}

					// decode the license data
					$license_data = json_decode( wp_remote_retrieve_body( $response ) );
					
					// $license_data->license will be either "valid" or "invalid"

					update_option( $this->key_status_option, $license_data->license );

				}
				
				/**
				* Returns current plugin version
				*/
				function plugin_get_version() {
					$plugin_data = get_plugin_data( __FILE__ );
					$plugin_version = $plugin_data['Version'];
					return $plugin_version;
				}
				
				/**
				* Print licence key error message
				*/
				function admin_message_invalid() {
				    ?>
				    <div class="error">
				        <p><strong><?php _e('You have not entered a valid license key for Table Rate Shipping Plus.', MHTRP_DOMAIN); ?></strong></p>
				    </div>
				    <?php
				}
				
				/**
				 * Generates dropdown options
				 */
				function generate_options() {
				
					$option_arr = array();
				
					// ####### ZONES
					foreach($this->zones as $option):
						$option_arr['table_rate_zone'][esc_attr($option['id'])] = esc_js($option['name']);
			   		endforeach;
			   		
			   		$option_arr['table_rate_zone']['0'] = __( 'Everywhere Else', MHTRP_DOMAIN);
			   		
					// ####### SERVICES
					foreach($this->services as $option):
						$option_arr['service'][esc_attr($option['id'])] = esc_js($option['name']);
			   		endforeach;
			   		
					// ####### SHIPPING CLASSES
					$option_arr['shipping_class']['0'] = __( 'Any', MHTRP_DOMAIN);
					$option_arr['shipping_class']['-1'] = __( 'None', MHTRP_DOMAIN);
					
			   		if ( WC()->shipping->get_shipping_classes() ) {
			   			foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) {	
			   				$option_arr['shipping_class'][esc_attr($shipping_class->slug)] = esc_js($shipping_class->name);
			   			}
			   		}
			   		
			   		// ####### COUNTRIES
					foreach (WC()->countries->get_shipping_countries() as $id => $value) :
		   				$option_arr['country'][esc_attr($id)] = esc_js($value);
					endforeach;
					
					// ####### STATES
					foreach (WC()->countries->get_shipping_country_states() as $id => $value) :
			
		   				if (!empty($value)) {
		   					$option_arr['country_with_state'][esc_attr($id)] = $option_arr['country'][esc_attr($id)];
		   				}

						foreach ($value as $state_id => $state_value) {
							$option_arr['state'][esc_attr($id)][esc_attr($state_id)] = esc_js($state_value);
		   				}

					endforeach;
					
					if (empty($option_arr['country_with_state'])) {
						$option_arr['country_with_state']['XX'] = __( 'No countries with states available', MHTRP_DOMAIN );
						$option_arr['state']['XX']['1'] = __( 'Unavailable', MHTRP_DOMAIN );
					}
					
					// ####### TABLE RATE BASIS
			   		$option_arr['rate_basis']['weight'] = sprintf(__( 'Weight (%s)', 'MHTRP_DOMAIN' ), $this->weight_unit);
			   		$option_arr['rate_basis']['price'] = sprintf(__( 'Total (%s)', 'MHTRP_DOMAIN' ), $this->curr_symbol);
			   		$option_arr['rate_basis']['item_count'] = __( 'Item count', MHTRP_DOMAIN);
			   		
			   		// ####### ZONE TYPE
			   		$option_arr['zone_type']['country'] = __( 'Country', MHTRP_DOMAIN);
			   		$option_arr['zone_type']['state'] = __( 'State', MHTRP_DOMAIN);
			   		$option_arr['zone_type']['postcode'] = __( 'Post/zip code', MHTRP_DOMAIN);
			   		
			   		return $option_arr;
				}
				
				/**
				 * Generates HTML for top of settings page.
				 */
				function admin_options() {
				
					//print_r($this->generate_options());
				?>
					<script type="text/javascript">
					
						// Sorting
						jQuery('table.wc_shipping tbody').sortable({
							items:'tr',
							cursor:'move',
							axis:'y',
							handle: 'td',
							scrollSensitivity:40,
							helper:function(e,ui){
								ui.children().each(function(){
									jQuery(this).width(jQuery(this).width());
								});
								ui.css('left', '0');
								return ui;
							},
							start:function(event,ui){
								ui.item.css('background-color','#f6f6f6');
							},
							stop:function(event,ui){
								ui.item.removeAttr('style');
							}
						});
							   		
				   		var options = <?php echo json_encode($this->generate_options()); ?>;
				   						   		
				   		function generateSelectOptionsHtml(options, selected) {
				   			var html;
				   			var selectedHtml;
				   
							for (var key in options) {
								var value = options[key];
								
								if (selected instanceof Array) {
									if (selected.indexOf(key) != -1) {
										selectedHtml = ' selected="selected"';
									} else {
										selectedHtml = '';
									}
								} else {
									if (key == selected) {
										selectedHtml = ' selected="selected"';
									} else {
										selectedHtml = '';
									}
								}
								
								html += '<option value="' + key +'"' + selectedHtml + '>' + value + '</option>';
							}
					   		
					   		return html;
				   		}
				   		
					</script>
					<style>
						.debug-col {
							display: none;
						}
						table.shippingrows tr th {
							padding-left: 10px;
						}
						.zone td {
							vertical-align: top;
						}
						.zone textarea {
							width: 100%;
						}
					</style>
					<h2><?php _e('Table Rate Shipping Plus',MHTRP_DOMAIN); ?></h2>
					<p>For support and updates, visit the <a href="http://mangohour.com/plugins/woocommerce-table-rate-shipping">Mangohour</a> website.</p>
					<p><a class="button" href="./admin.php?page=mh_wc_table_rate_plus_export"><?php _e('Export/Backup Settings',MHTRP_DOMAIN); ?></a></p>
					<input type="hidden" name="<?php echo $this->id; ?>_old_licence_key" id="<?php echo $this->id; ?>_old_licence_key" value="<?php echo $this->settings['licence_key']; ?>" />
					<table class="form-table">
					<?php $this->generate_settings_html(); ?>
					</table>
					
				<?php
				}
				
				/**
				 * Generates HTML for zone settings table.
				 */
				function generate_zones_table_html() {
					ob_start();
					?>
					<tr valign="top">
						<th scope="row" class="titledesc"><?php _e( 'Shipping Zones', MHTRP_DOMAIN ); ?></th>
						<td class="forminp" id="<?php echo $this->id; ?>_zones">
							<p style="padding-bottom: 10px;"><?php _e( 'After adding a shipping zone, hit "Save changes" so that it appears as an option in the table rate section.', MHTRP_DOMAIN ); ?></p>
							<table class="shippingrows widefat" cellspacing="0">
						        <col style="width:0%">
						        <col style="width:0%">
						        <col style="width:0%">
						        <col style="width:100%">
								<thead>
									<tr>
										<th class="check-column"><input type="checkbox"></th>
										<!--<th class="debug-col"><?php _e( 'ID', MHTRP_DOMAIN ); ?></th>-->
										<th><div style="width: 200px"><?php _e( 'Name', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Shipping zone name, will appear in table rates table.', MHTRP_DOMAIN ); ?>">[?]</a></div></th>
										<th><div style="width: 120px"><?php _e( 'Zone Type', MHTRP_DOMAIN ); ?></div></th>
										<th><?php _e( 'Values', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Enter the values for the zone. For more information please see the support documentation.', MHTRP_DOMAIN ); ?>">[?]</a></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th colspan="4"><a href="#" class="add button"><?php _e( 'Add Shipping Zone', MHTRP_DOMAIN ); ?></a> <a href="#" class="remove button"><?php _e( 'Delete Selected Zones', MHTRP_DOMAIN ); ?></a></th>
									</tr>
								</tfoot>
								<tbody class="zones">
									<tr class="zone">
										<th></th>
										<!--<td class="debug-col">0</td>-->
										<td><?php _e( 'Default Zone (everywhere else)', MHTRP_DOMAIN ); ?></td>
										<td><?php _e( 'Country', MHTRP_DOMAIN ); ?></td>
										<td><em><?php _e( 'All allowed countries', MHTRP_DOMAIN ); ?></em></td>
									</tr>
								</tbody>
							</table>
						   	<script type="text/javascript">
						   	
						   		var lastZoneId = <?php echo $this->last_zone_id; ?>;

						   		<?php
						   		foreach ($this->zones as $zone): 
						   			$js_array = json_encode($zone);
						   			echo "jQuery('#{$this->id}_zones table tbody tr:last').before(addZoneRowHtml(false, {$js_array}));\n";
						   		endforeach;
						   		?>
						  
						  		
								jQuery("select.zone_type").each(function(i, el){
									switchZoneType(this);
									jQuery(el).change(function(){
										switchZoneType(this);
									});
								});
								
								jQuery("select.zone_s_country").each(function(i, el){
									switchZoneCountry(this, false);
									jQuery(el).change(function(){
										switchZoneCountry(this, true);
									});
								});
								
								
								function switchZoneType(el) {
									row = jQuery(el).parent().parent();
									switch (jQuery(el).val()) {
										case 'country':
											jQuery(row).find('.<?php echo $this->id; ?>_zone_c').show();
											jQuery(row).find('.<?php echo $this->id; ?>_zone_s').hide();
											jQuery(row).find('.<?php echo $this->id; ?>_zone_p').hide();
											break;
										case 'state':
											jQuery(row).find('.<?php echo $this->id; ?>_zone_c').hide();
											jQuery(row).find('.<?php echo $this->id; ?>_zone_s').show();
											jQuery(row).find('.<?php echo $this->id; ?>_zone_p').hide();
											break;
										case 'postcode':
											jQuery(row).find('.<?php echo $this->id; ?>_zone_c').hide();
											jQuery(row).find('.<?php echo $this->id; ?>_zone_s').hide();
											jQuery(row).find('.<?php echo $this->id; ?>_zone_p').show();
											break;
									}
								}
								
								function editZone(el) {
									row = jQuery(el).parent().parent();
									jQuery(row).find('.<?php echo $this->id; ?>_zone_edit').show();
									jQuery(row).find('.<?php echo $this->id; ?>_zone_description').hide();
								}
								
								function switchZoneCountry(el, resetStates) {
									cc = jQuery(el).val();
									if (resetStates) {
										jQuery(el).parent().find('select.zone_s_states').empty();
									}
									jQuery(el).parent().find('select.zone_s_states').append(generateSelectOptionsHtml(options['state'][cc], null));
									jQuery("select.chosen_select").trigger("chosen:updated");
								}
						   		
						   		function addZoneRowHtml(isNew, rowArr) {

						   			if (isNew) {	
						   				lastZoneId++;
						   				rowArr = {};
							   			rowArr['id'] = lastZoneId;
							   			rowArr['name'] = '';
							   			rowArr['country'] = '';
							   			rowArr['type'] = 'country';
							   			rowArr['values'] = '';
							   			rowArr['enabled'] = '1';
							   			rowArr['p_postcode'] = ''; // so 'undefined' isn't shown
						   			}
						   			
						   			switch (rowArr['type']) {
							   			case 'country':
							   				rowArr['c_country'] = rowArr['country'];
							   				break;
							   			case 'state':
							   				rowArr['s_country'] = rowArr['country'];
							   				rowArr['s_states'] = rowArr['values'];							   				
							   				break;
							   			case 'postcode':
							   				rowArr['p_country'] = rowArr['country'];
							   				rowArr['p_postcode'] = rowArr['values'];
							   				if (rowArr['exclude'] == '1') {
								   				rowArr['p_exclude_checked'] = 'checked';
							   				}
							   				break;
						   			}
						   			
						   			

						   			var size = jQuery('#<?php echo $this->id; ?>_zones tbody .zone').size();
						   			var html = '\
						   					<tr class="zone">\
						   						<input type="hidden" name="<?php echo $this->id; ?>_zone_id[' + size + ']" value="' + rowArr['id'] + '" />\
						   						<input type="hidden" name="<?php echo $this->id; ?>_zone_exclude[' + size + ']" value="' + rowArr['exclude'] + '" />\
						   						<input type="hidden" name="<?php echo $this->id; ?>_zone_enabled[' + size + ']" value="' + rowArr['enabled'] + '" />\
												<th class="check-column"><input type="checkbox" name="select" /></th>\
												<!--<td class="debug-col">\
													' + rowArr['id'] + '\
												</td>-->\
												<td>\
													<input type="text" name="<?php echo $this->id; ?>_zone_name[' + size + ']" value="' + rowArr['name'] + '" placeholder="<?php _e( 'Enter zone name', MHTRP_DOMAIN ); ?>" />\
												</td>\
												<td>\
													<select name="<?php echo $this->id; ?>_zone_type[' + size + ']" class="zone_type">\
														' + generateSelectOptionsHtml(options['zone_type'], rowArr['type']) + '\
													</select>\
												</td>\
												<td style="overflow:visible;">\
													<div class="<?php echo $this->id; ?>_zone_edit" style="display:none;">\
														<div class="<?php echo $this->id; ?>_zone_c">\
															<select multiple="multiple" name="<?php echo $this->id; ?>_zone_c_country[' + size + '][]" class="multiselect chosen_select" data-placeholder="<?php _e( 'Select one or more countries', MHTRP_DOMAIN ); ?>">\
																' + generateSelectOptionsHtml(options['country'], rowArr['c_country']) + '\
															</select>\
														</div>\
														<div class="<?php echo $this->id; ?>_zone_s">\
															<select name="<?php echo $this->id; ?>_zone_s_country[' + size + ']" class="chosen_select zone_s_country">\
																' + generateSelectOptionsHtml(options['country_with_state'], rowArr['s_country']) + '\
															</select><br>\
															<select multiple="multiple" name="<?php echo $this->id; ?>_zone_s_states[' + size + '][]" class="multiselect chosen_select zone_s_states" data-placeholder="<?php _e( 'Select one or more states', MHTRP_DOMAIN ); ?>">\
																' + generateSelectOptionsHtml(options['state'][rowArr['s_country']], rowArr['s_states']) + '\
															</select>\
														</div>\
														<div class="<?php echo $this->id; ?>_zone_p">\
															<select name="<?php echo $this->id; ?>_zone_p_country[' + size + ']" class="chosen_select">\
																' + generateSelectOptionsHtml(options['country'], rowArr['p_country']) + '\
															</select><br>\
															<textarea name="<?php echo $this->id; ?>_zone_p_postcode[' + size + ']" placeholder="<?php _e( 'Enter one postal code or range per line', MHTRP_DOMAIN ); ?>" style="width: 350px;" rows="5">' + rowArr['p_postcode'] + '</textarea><br>\
															<input type="checkbox" name="<?php echo $this->id; ?>_zone_p_exclude[' + size + ']" id="<?php echo $this->id; ?>_zone_p_exclude[' + size + ']" value="1" ' + rowArr['p_exclude_checked'] + ' /> <label for="<?php echo $this->id; ?>_zone_p_exclude[' + size + ']"><?php _e( 'Exclude', MHTRP_DOMAIN ); ?></label> <strong><a class="tips" data-tip="<?php _e( 'If checked, the above postal codes will be excluded from the shipping zone. All other postal codes will be accepted.', MHTRP_DOMAIN ); ?>">[?]</a></strong>\
														</div>\
													</div>\
													<div class="<?php echo $this->id; ?>_zone_description">\
														<a href="#" onclick="javascript:editZone(this);return false;"><strong><?php _e( 'Edit zone settings', MHTRP_DOMAIN ); ?>...</strong></a>\
													</div>\
												</td>\
											</tr>';
									return html;
						   		}
						   	
								jQuery(function() {
			
									jQuery('#<?php echo $this->id; ?>_zones').on( 'click', 'a.add', function(){

										jQuery('#<?php echo $this->id; ?>_zones table tbody tr:last').before(addZoneRowHtml(true, false));
										
										var row = jQuery('#<?php echo $this->id; ?>_zones table tbody tr:nth-last-child(2)');
										
										var select = jQuery(row).find("select.zone_type");
										switchZoneType(select);
										jQuery(select).change(function(){
											switchZoneType(this);
										});
								
										select = jQuery(row).find("select.zone_s_country");
										switchZoneCountry(select, true);
										jQuery(select).change(function(){
											switchZoneCountry(this, true);
										});
										
										jQuery(row).find('.<?php echo $this->id; ?>_zone_edit').show();
										jQuery(row).find('.<?php echo $this->id; ?>_zone_description').hide();
			
										if (jQuery().chosen) {
											jQuery("select.chosen_select").chosen({
												width: '350px',
												disable_search_threshold: 5
											});
										} else {
											jQuery("select.chosen_select").select2();
										}

										return false;
									});
			
									// Remove row
									jQuery('#<?php echo $this->id; ?>_zones').on( 'click', 'a.remove', function(){
										
										var answer = confirm("<?php _e( 'Delete the selected zones?', MHTRP_DOMAIN ); ?>");
										if (answer) {
											jQuery('#<?php echo $this->id; ?>_zones table tbody tr th.check-column input:checked').each(function(i, el){
												jQuery(el).closest('tr').remove();
											});
										}
										return false;
									});
			
								});
							</script>
						</td>
					</tr>
					<?php
					return ob_get_clean();
				}
	
				/**
				 * Generates HTML for service settings table.
				 */
				function generate_services_table_html() {
					ob_start();
					?>
					<tr valign="top">
						<th scope="row" class="titledesc"><?php _e( 'Shipping Services', MHTRP_DOMAIN ); ?></th>
						<td class="forminp" id="<?php echo $this->id; ?>_services">
							<p style="padding-bottom: 10px;"><?php _e( 'After adding a shipping service, hit "Save changes" so that it appears as an option in the table rate section.', MHTRP_DOMAIN ); ?></p>
							<table class="shippingrows widefat" cellspacing="0">
						        <col style="width:0%">
						        <col style="width:100%">
						         <col style="width:0%">
						          <col style="width:0%">
								<thead>
									<tr>
										<th class="check-column"><input type="checkbox"></th>
										<th class="debug-col"><?php _e( 'ID', MHTRP_DOMAIN ); ?></th>
										<th><?php _e( 'Name', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Name of shipping service. This will be displayed to customers when choosing a shipping service.', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th class="debug-col"><?php _e( 'Priority', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Priority will control order in which shipping service are displayed.', MHTRP_DOMAIN ); ?>">[?]</a></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th colspan="4"><a href="#" class="add button"><?php _e( 'Add Shipping Service', MHTRP_DOMAIN ); ?></a> <a href="#" class="remove button"><?php _e( 'Delete Selected Services', MHTRP_DOMAIN ); ?></a></th>
									</tr>
								</tfoot>
								<tbody class="services">

								</tbody>
							</table>
						   	<script type="text/javascript">
						   	
						   		var lastServiceId = <?php echo $this->last_service_id; ?>;

						   		<?php
						   		foreach ($this->services as $service): 
						   			$js_array = json_encode($service);
						   			echo "jQuery(addServiceRowHtml(false, {$js_array})).appendTo('#{$this->id}_services table tbody');\n";
						   		endforeach;
						   		?>
						   		
						   		function addServiceRowHtml(isNew, rowArr) {
						   		
						   			if (isNew) {	
						   				lastServiceId++;
						   				rowArr = {};
							   			rowArr['id'] = lastServiceId;
							   			rowArr['name'] = '';
							   			rowArr['priority'] = '10';
							   			rowArr['enabled'] = '1';
						   			}

						   			var size = jQuery('#<?php echo $this->id; ?>_services tbody .service').size();
						   			var html = '\
						   					<tr class="service">\
						   						<input type="hidden" name="<?php echo $this->id; ?>_service_id[' + size + ']" value="' + rowArr['id'] + '" />\
						   						<input type="hidden" name="<?php echo $this->id; ?>_service_enabled[' + size + ']" value="' + rowArr['enabled'] + '" />\
												<th class="check-column"><input type="checkbox" name="select" /></th>\
												<td class="debug-col">\
													' + rowArr['id'] + '\
												</td>\
												<td>\
													<input type="text" name="<?php echo $this->id; ?>_service_name[' + size + ']" value="' + rowArr['name'] + '" placeholder="" />\
												</td>\
												<td class="debug-col">\
													<input type="text" name="<?php echo $this->id; ?>_service_priority[' + size + ']" value="' + rowArr['priority'] + '" placeholder="" />\
												</td>\
											</tr>';
									return html;
						   		}
						   	
								jQuery(function() {
			
									jQuery('#<?php echo $this->id; ?>_services').on( 'click', 'a.add', function(){
			
										jQuery(addServiceRowHtml(true, false)).appendTo('#<?php echo $this->id; ?>_services table tbody');
			
										return false;
									});
			
									// Remove row
									jQuery('#<?php echo $this->id; ?>_services').on( 'click', 'a.remove', function(){
										var answer = confirm("<?php _e( 'Delete the selected services?', MHTRP_DOMAIN ); ?>");
										if (answer) {
											jQuery('#<?php echo $this->id; ?>_services table tbody tr th.check-column input:checked').each(function(i, el){
												jQuery(el).closest('tr').remove();
											});
										}
										return false;
									});
			
								});
							</script>
						</td>
					</tr>
					<?php
					return ob_get_clean();
				}
				
				/**
				 * Generates HTML for table_rate settings table.
				 */
				function generate_table_rates_table_html() {
					ob_start();
					?>
					<tr valign="top">
						<th scope="row" class="titledesc"><?php _e( 'Shipping Rates', MHTRP_DOMAIN ); ?></th>
						<td class="forminp" id="<?php echo $this->id; ?>_table_rates">
							<table class="shippingrows widefat" cellspacing="0">
								<thead>
									<tr>
										<th class="check-column"><input type="checkbox"></th>
										<th class="debug-col"><?php _e( 'ID', MHTRP_DOMAIN ); ?></th>
										<th><?php _e( 'Zone', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Shipping zone, as defined in Shipping Zones table.', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th><?php _e( 'Service', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Name of shipping service. If a shipping service is shared by multiple table rates, the cheapest applicable table rate will be given to the customer.', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th><?php _e( 'Class', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Shipping class, as defined in WooCommerce settings.', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th><?php _e( 'Condition', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Choose which metric to base your table rate on.', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th><?php _e( 'Min', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Minimum, in decimal format. Inclusive.', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th><?php _e( 'Max', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Maximum, in decimal format. Inclusive. To impose no upper limit, use *".', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th><?php _e( 'Cost', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Flat rate cost, excluding tax.', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th><?php _e( 'Cost Per Item', MHTRP_DOMAIN ); ?> <a class="tips" data-tip="<?php _e( 'Additional cost per item, excluding tax.', MHTRP_DOMAIN ); ?>">[?]</a></th>
										<th><?php printf(__( 'Cost Per %s', 'MHTRP_DOMAIN' ), $this->weight_unit); ?> <a class="tips" data-tip="<?php _e( 'Additional cost per weight unit, excluding tax.', MHTRP_DOMAIN ); ?>">[?]</a></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th colspan="10"><a href="#" class="add button"><?php _e( 'Add Table Rate', MHTRP_DOMAIN ); ?></a> <a href="#" class="remove button"><?php _e( 'Delete Selected Rates', MHTRP_DOMAIN ); ?></a></th>
									</tr>
								</tfoot>
								<tbody class="table_rates">

								</tbody>
							</table>
						   	<script type="text/javascript">
						   	
						   		var lastTableRateId = <?php echo $this->last_table_rate_id; ?>;

						   		<?php
						   		foreach ($this->table_rates as $table_rate): 
						   			$js_array = json_encode($table_rate);
						   			echo "jQuery(addTableRateRowHtml(false, {$js_array})).appendTo('#{$this->id}_table_rates table tbody');\n";
						   		endforeach;
						   		?>
						   		
						   		function addTableRateRowHtml(isNew, rowArr) {
						   		
						   			if (isNew) {	
						   				lastTableRateId++;
						   				rowArr = {};
							   			rowArr['id'] = lastTableRateId;
							   			rowArr['service'] = '';
							   			rowArr['zone'] = '<?php echo (!empty($this->zones[0]['id'])) ? $this->zones[0]['id'] : 0; ?>';
							   			rowArr['class'] = '0';
							   			rowArr['basis'] = 'weight';
							   			rowArr['min'] = '0';
							   			rowArr['max'] = '*';
							   			rowArr['cost'] = '0';
							   			rowArr['item_cost'] = '0';
							   			rowArr['weight_cost'] = '0';
							   			rowArr['enabled'] = '1';
						   			}
						   			
						   			if (rowArr['item_cost'] == undefined) {
							   			rowArr['item_cost'] = 0;
							   			rowArr['weight_cost'] = 0;
						   			}

						   			var size = jQuery('#<?php echo $this->id; ?>_table_rates tbody .table_rate').size();
						   			var html = '\
						   					<tr class="table_rate">\
						   						<input type="hidden" name="<?php echo $this->id; ?>_table_rate_id[' + size + ']" value="' + rowArr['id'] + '" />\
						   						<input type="hidden" name="<?php echo $this->id; ?>_table_rate_enabled[' + size + ']" value="' + rowArr['enabled'] + '" />\
												<th class="check-column"><input type="checkbox" name="select" /></th>\
												<td class="debug-col">\
													' + rowArr['id'] + '\
												</td>\
												<td>\
													<select name="<?php echo $this->id; ?>_table_rate_zone[' + size + ']">\
														' + generateSelectOptionsHtml(options['table_rate_zone'], rowArr['zone']) + '\
													</select>\
												</td>\
												<td>\
													<select name="<?php echo $this->id; ?>_table_rate_service[' + size + ']">\
														' + generateSelectOptionsHtml(options['service'], rowArr['service']) + '\
													</select>\
												</td>\
												<td>\
													<select name="<?php echo $this->id; ?>_table_rate_class[' + size + ']">\
														' + generateSelectOptionsHtml(options['shipping_class'], rowArr['class']) + '\
													</select>\
												</td>\
												<td>\
													<select name="<?php echo $this->id; ?>_table_rate_basis[' + size + ']">\
														' + generateSelectOptionsHtml(options['rate_basis'], rowArr['basis']) + '\
													</select>\
												</td>\
												<td>\
													<input type="text" name="<?php echo $this->id; ?>_table_rate_min[' + size + ']" value="' + rowArr['min'] + '" placeholder="0" size="4" />\
												</td>\
												<td>\
													<input type="text" name="<?php echo $this->id; ?>_table_rate_max[' + size + ']" value="' + rowArr['max'] + '" placeholder="*" size="4" />\
												</td>\
												<td>\
													<input type="text" name="<?php echo $this->id; ?>_table_rate_cost[' + size + ']" value="' + rowArr['cost'] + '" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" size="4" class="wc_input_price" />\
												</td>\
												<td>\
													<input type="text" name="<?php echo $this->id; ?>_table_rate_item_cost[' + size + ']" value="' + rowArr['item_cost'] + '" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" size="4" class="wc_input_price" />\
												</td>\
												<td>\
													<input type="text" name="<?php echo $this->id; ?>_table_rate_weight_cost[' + size + ']" value="' + rowArr['weight_cost'] + '" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" size="4" class="wc_input_price" />\
												</td>\
											</tr>';
									return html;
						   		}
						   	
								jQuery(function() {
			
									jQuery('#<?php echo $this->id; ?>_table_rates').on( 'click', 'a.add', function(){
			
										jQuery(addTableRateRowHtml(true, false)).appendTo('#<?php echo $this->id; ?>_table_rates table tbody');
			
										return false;
									});
			
									// Remove row
									jQuery('#<?php echo $this->id; ?>_table_rates').on( 'click', 'a.remove', function(){
										var answer = confirm("<?php _e( 'Delete the selected rates?', MHTRP_DOMAIN ); ?>");
										if (answer) {
											jQuery('#<?php echo $this->id; ?>_table_rates table tbody tr th.check-column input:checked').each(function(i, el){
												jQuery(el).closest('tr').remove();
											});
										}
										return false;
									});
									
									/*
									// Duplicate row
									jQuery('#<?php echo $this->id; ?>_table_rates').on( 'click', 'a.duplicate', function(){
									
										jQuery('#<?php echo $this->id; ?>_table_rates table tbody tr th.check-column input:checked').each(function(i, el){
											jQuery(el).(')
										});
											
									});
									*/
			
								});
							</script>
						</td>
					</tr>
					<?php
					return ob_get_clean();
				}
				
				/**
				 * Generates HTML for service settings table.
				 */
				function generate_shipping_class_order_table_html() {
					ob_start();
					if ( WC()->shipping->get_shipping_classes() ) {
					?>
					<tr valign="top">
						<th scope="row" class="titledesc"><?php _e( 'Class Priority', MHTRP_DOMAIN ); ?></th>
						<td class="forminp" id="<?php echo $this->id; ?>_shipping_classes">
							<table class="wc_shipping widefat" cellspacing="0" style="width: 50%;">
								<tfoot>
									<tr>
										<th colspan="1"><span class="description"><?php _e( 'Drag and drop the above shipping classes to control their priority. This is only used for the <strong>per order</strong> calculation type.', MHTRP_DOMAIN ); ?></span></th>
									</tr>
								</tfoot>
								<tbody>
<?php


	$default_order = array();
	
	foreach ( WC()->shipping->get_shipping_classes() as $shipping_class ) {
		$default_order[] = esc_attr($shipping_class->slug);
		$options[esc_attr($shipping_class->slug)] = esc_js($shipping_class->name);
	}
	
	if (empty($this->shipping_class_order)) {
		$order_list = $default_order;
	} else {
		$order_list = $this->shipping_class_order;
		
		
		
		// Append any classes added since last save
		foreach ($default_order as $class) {
			if (in_array($class, $order_list) == false) {
				$order_list[] = $class;
			}
			
			
		}
	}
	
	foreach ($order_list as $slug) {
		if (isset($options[$slug])) {
?>
									<tr class="shipping_class">
										<td class="name"><input type="hidden" name="<?php echo $this->id; ?>_shipping_class_order[]" value="<?php echo esc_attr($slug); ?>" /><?php echo esc_js($options[$slug]); ?></td>
									</tr>
<?php
		}
	}

?>
								</tbody>
							</table>
						</td>
					</tr>
					<?php
					}
					return ob_get_clean();
				}
				
				
				/**
				 * Generates HTML for import checkbox
				 */
				function generate_import_html() {
					ob_start();
					if ( $this->has_backup_file ) {
					?>
					<tr valign="top">
						<th scope="row" class="titledesc"><?php _e( 'Import Settings', MHTRP_DOMAIN ); ?></th>
						<td class="forminp">
							<input type="checkbox" name="<?php echo $this->id; ?>_import" id="<?php echo $this->id; ?>_import" value="1"> <label for="<?php echo $this->id; ?>_import"><?php _e( 'Import settings from backup file', MHTRP_DOMAIN ); ?></label>
						</td>
					</tr>
					<?php
					}
					return ob_get_clean();
				}
				
				/**
				 * Import backup file from plugin directory.
				 */
				function process_import() {
					if (($this->has_backup_file)
						&& (isset($_POST[ $this->id . '_import']))
						&& ($_POST[ $this->id . '_import'] == 1)) {
							
						$data = file_get_contents($this->backup_file);
						$options = json_decode($data, true);

						foreach($options as $key => $value) {
							update_option( $key, $value );
						}
						
					}
				}

				/**
				 * Process and save submitted zones.
				 */
 				function process_zones() {
					// Save the rates
					$zone_id = array();
					$zone_name = array();
					$zone_type = array();
					$zone_enabled = array();
					$zone_exclude = array();
					
					$zone_c_country = array();
					$zone_s_country = array();
					$zone_s_states = array();
					$zone_p_country = array();
					$zone_p_postcode = array();
					$zone_p_exclude = array();

					$zones = array();
					
					if ( isset( $_POST[ $this->id . '_zone_id'] ) ) $zone_id = array_map( 'wc_clean', $_POST[ $this->id . '_zone_id'] );
					if ( isset( $_POST[ $this->id . '_zone_name'] ) ) $zone_name = array_map( 'wc_clean', $_POST[ $this->id . '_zone_name'] );
					if ( isset( $_POST[ $this->id . '_zone_type'] ) ) $zone_type = array_map( 'wc_clean', $_POST[ $this->id . '_zone_type'] );
					if ( isset( $_POST[ $this->id . '_zone_enabled'] ) ) $zone_enabled = array_map( 'wc_clean', $_POST[ $this->id . '_zone_enabled'] );
					
					if ( isset( $_POST[ $this->id . '_zone_c_country'] ) ) $zone_c_country  = $_POST[ $this->id . '_zone_c_country'];
					if ( isset( $_POST[ $this->id . '_zone_s_country'] ) ) $zone_s_country  = array_map( 'wc_clean', $_POST[ $this->id . '_zone_s_country'] );
					if ( isset( $_POST[ $this->id . '_zone_s_states'] ) ) $zone_s_states  = $_POST[ $this->id . '_zone_s_states'];
					if ( isset( $_POST[ $this->id . '_zone_p_country'] ) ) $zone_p_country  = array_map( 'wc_clean', $_POST[ $this->id . '_zone_p_country'] );
					if ( isset( $_POST[ $this->id . '_zone_p_postcode'] ) ) $zone_p_postcode  = $_POST[ $this->id . '_zone_p_postcode']; // wc_clean removes '\n'
					if ( isset( $_POST[ $this->id . '_zone_p_exclude'] ) ) $zone_p_exclude  = array_map( 'wc_clean', $_POST[ $this->id . '_zone_p_exclude'] );
					
					// Get max key
					$theValues = $zone_id;
					ksort( $theValues );
					$theValue = end( $theValues );
					$key = key( $theValues );
					
					for ( $i = 0; $i <= $key; $i++ ) {
						if ( isset( $zone_id[ $i ] ) 
							&& ! empty( $zone_name[ $i ] )
							&& isset( $zone_type[ $i ] )) {
							
							$country = null;
							$values = '';
							$exclude = '';
							
							switch ($zone_type[$i]) {
								case 'country':
									$country = $zone_c_country[$i];
									$values = '';
									break;
								case 'state':
									$country = $zone_s_country[$i];
									if (isset($zone_s_states[$i])) $values = $zone_s_states[$i];
									break;
								case 'postcode':
									$country = $zone_p_country[$i];
									$postcodes = $zone_p_postcode[$i];
									$postcodes = explode("\n", $postcodes);
									$postcodes = array_map('trim', $postcodes);
									$postcodes = array_map('strtoupper', $postcodes);
									$postcodes = array_filter($postcodes , 'strlen'); // remove empty, keep '0'
									$values = implode("\n", $postcodes);
									if ((isset($zone_p_exclude[$i])) && ($zone_p_exclude[$i] == '1')) {
										$exclude = 1;
									}
									break;
							}

							// Add to flat rates array
							$zones[] = array(
								'id' => $zone_id[ $i ],
								'name' => $zone_name[ $i ],
								'country' => $country,
								'values' => $values,
								'exclude' => $exclude,
								'type' => $zone_type[ $i ],
								'enabled' => $zone_enabled[ $i ],
							);
						}
					}
					
					if ( (!empty($zone_id[$key]))
						&& ($zone_id[$key] > $this->last_zone_id)
						&& (is_numeric($zone_id[$key]))) {
						$highest_zone_id = $zone_id[$key];
						update_option( $this->last_zone_id_option, $highest_zone_id );
					}
					
					update_option( $this->zones_option, $zones );
					
					$this->get_zones();
					
				}
				
				/**
				 * Retrieves zones array from database.
				 */
				function get_zones() {
					$this->zones = array_filter( (array) get_option( $this->zones_option ) );
				}
				
				/**
				 * Retrieves last zone id from database.
				 */
				function get_last_zone_id() {
					$this->last_zone_id = (int)get_option( $this->last_zone_id_option );
				}
				
				/**
				 * Process and save submitted services.
				 */
 				function process_services() {
					// Save the rates
					$service_id = array();
					$service_name = array();
					$service_priority = array();
					$service_enabled = array();

					$services = array();
					
					if ( isset( $_POST[ $this->id . '_service_id'] ) ) $service_id = array_map( 'wc_clean', $_POST[ $this->id . '_service_id'] );
					if ( isset( $_POST[ $this->id . '_service_name'] ) ) $service_name = array_map( 'wc_clean', $_POST[ $this->id . '_service_name'] );
					if ( isset( $_POST[ $this->id . '_service_priority'] ) ) $service_priority = array_map( 'wc_clean', $_POST[ $this->id . '_service_priority'] );
					if ( isset( $_POST[ $this->id . '_service_enabled'] ) ) $service_enabled = array_map( 'wc_clean', $_POST[ $this->id . '_service_enabled'] );
					
					/*
					print_r($service_id);
					echo 'br';
					print_r($service_name);
					echo 'br';	
					print_r($service_priority);
					echo 'br';
					*/	
					
					// Get max key
					$values = $service_id;
					ksort( $values );
					$value = end( $values );
					$key = key( $values );
					
					for ( $i = 0; $i <= $key; $i++ ) {
						if ( isset( $service_id[ $i ] ) 
							&& ! empty( $service_name[ $i ] )
							&& ! empty( $service_priority[ $i ] )
							&& isset($service_enabled[ $i ] ) ){

							// Add services to array
							$services[] = array(
								'id' => $service_id[ $i ],
								'name' => $service_name[ $i ],
								'priority' => $service_priority[ $i ],
								'enabled' => $service_enabled[ $i ],
							);

							// Register string for WPML translation
							if (function_exists('icl_register_string')) {
								icl_register_string('Table Rate Shipping Plus', 'shipping service: ' . $service_name[ $i ], $service_name[ $i ]);
							}
						}
					}
					
					if ( (!empty($service_id[$key]))
						&& ($service_id[$key] > $this->last_service_id)
						&& (is_numeric($service_id[$key]))) {
						$highest_service_id = $service_id[$key];
						update_option( $this->last_service_id_option, $highest_service_id );
					}
					
					update_option( $this->services_option, $services );
					
					$this->get_services();
					
				}
				
				/**
				 * Retrieves settings array from database.
				 */
				function get_services() {
					$this->services = array_filter( (array) get_option( $this->services_option ) );
				}
				
				/**
				 * Retrieves last service id from database.
				 */
				function get_last_service_id() {
					$this->last_service_id = (int)get_option( $this->last_service_id_option );
				}
 
 				/**
				 * Process and save submitted table_rates.
				 */
				function process_table_rates() {
					// Save the rates
					$table_rate_id = array();
					$table_rate_service = array();
					$table_rate_zone = array();
					$table_rate_class = array();
					$table_rate_basis = array();
					$table_rate_min = array();
					$table_rate_max = array();
					$table_rate_cost = array();
					$table_rate_item_cost = array();
					$table_rate_weight_cost = array();
					$table_rate_enabled = array();
					
					$table_rates = array();
					
					if ( isset( $_POST[ $this->id . '_table_rate_id'] ) ) $table_rate_id = array_map( 'wc_clean', $_POST[ $this->id . '_table_rate_id'] );
					if ( isset( $_POST[ $this->id . '_table_rate_service'] ) ) $table_rate_service = array_map( 'wc_clean', $_POST[ $this->id . '_table_rate_service'] );
					if ( isset( $_POST[ $this->id . '_table_rate_zone'] ) ) $table_rate_zone = array_map( 'wc_clean', $_POST[ $this->id . '_table_rate_zone'] );
					if ( isset( $_POST[ $this->id . '_table_rate_class'] ) ) $table_rate_class = array_map( 'wc_clean', $_POST[ $this->id . '_table_rate_class'] );
					if ( isset( $_POST[ $this->id . '_table_rate_basis'] ) ) $table_rate_basis = array_map( 'wc_clean', $_POST[ $this->id . '_table_rate_basis'] );
					if ( isset( $_POST[ $this->id . '_table_rate_min'] ) )   $table_rate_min   = array_map( 'stripslashes', $_POST[ $this->id . '_table_rate_min'] );
					if ( isset( $_POST[ $this->id . '_table_rate_max'] ) )   $table_rate_max   = array_map( 'stripslashes', $_POST[ $this->id . '_table_rate_max'] );
					if ( isset( $_POST[ $this->id . '_table_rate_cost'] ) )  $table_rate_cost  = array_map( 'stripslashes', $_POST[ $this->id . '_table_rate_cost'] );
					if ( isset( $_POST[ $this->id . '_table_rate_item_cost'] ) )  $table_rate_item_cost  = array_map( 'stripslashes', $_POST[ $this->id . '_table_rate_item_cost'] );
					if ( isset( $_POST[ $this->id . '_table_rate_weight_cost'] ) )  $table_rate_weight_cost  = array_map( 'stripslashes', $_POST[ $this->id . '_table_rate_weight_cost'] );
					if ( isset( $_POST[ $this->id . '_table_rate_enabled'] ) ) $table_rate_enabled = array_map( 'wc_clean', $_POST[ $this->id . '_table_rate_enabled'] );
										
					// Get max key
					$values = $table_rate_id;
					ksort( $values );
					$value = end( $values );
					$key = key( $values );
					
					for ( $i = 0; $i <= $key; $i++ ) {
						if ( isset( $table_rate_id[ $i ] ) 
							&& isset( $table_rate_service[ $i ] )
							&& isset( $table_rate_zone[ $i ] )
							&& isset( $table_rate_class[ $i ] )
							&& isset( $table_rate_basis[ $i ] )
							&& isset( $table_rate_min[ $i ] )
							&& isset( $table_rate_max[ $i ] )
							&& isset( $table_rate_cost[ $i ] )
							&& isset( $table_rate_item_cost[ $i ] )
							&& isset( $table_rate_weight_cost[ $i ] )
							&& isset( $table_rate_enabled[ $i ] ) ) {
					
							$table_rate_cost[ $i ] = wc_format_decimal( $table_rate_cost[$i] );
					
							// Add table_rates to array
							$table_rates[] = array(
								'id' => $table_rate_id[ $i ],
								'service' => $table_rate_service[ $i ],
								'zone' => $table_rate_zone[ $i ],
								'class' => $table_rate_class[ $i ],
								'basis' => $table_rate_basis[ $i ],
								'min' => $table_rate_min[ $i ],
								'max' => $table_rate_max[ $i ],
								'cost' => $table_rate_cost[ $i ],
								'item_cost' => $table_rate_item_cost[ $i ],
								'weight_cost' => $table_rate_weight_cost[ $i ],
								'enabled' => $table_rate_enabled[ $i ],
							);
						}
					}
					
					
					if ( (!empty($table_rate_id[$key]))
						&& ($table_rate_id[$key] > $this->last_table_rate_id)
						&& (is_numeric($table_rate_id[$key]))) {
						$highest_table_rate_id = $table_rate_id[$key];
						update_option( $this->last_table_rate_id_option, $highest_table_rate_id );
					}
					
					if (!empty($table_rates)) {
						$table_rates = $this->sort_table_rates($table_rates);
					}
					
					update_option( $this->table_rate_option, $table_rates );
					
					$this->get_table_rates();
				}
				
				/**
				 * Sorts an array of table rates
				 */
				function sort_table_rates($data) {

					// Take out '*'
					foreach ($data as $key => $row) {
						if (isset($row['max'])
							&& ($row['max'] == '*')) {
							$data[$key]['max'] = '999999999';
						}	
					}
					
					// Obtain a list of columns
					foreach ($data as $key => $row) {
					    $service[$key]  = $row['service'];
					    $zone[$key] = $row['zone'];
					    $class[$key] = $row['class'];
					    $basis[$key] = $row['basis'];
					    $max[$key] = $row['max'];
					    $min[$key] = $row['min'];
					    $cost[$key] = $row['cost'];
					}
					
					// Sort the array
					array_multisort($zone, SORT_DESC, $service, SORT_ASC, $class, SORT_ASC,
						$basis, SORT_ASC, $max, SORT_ASC, $cost, SORT_ASC, $data);
					
					// Reinsert *
					foreach ($data as $key => $row) {
						if (isset($row['max'])
							&& ($row['max'] == '999999999')) {
							$data[$key]['max'] = '*';
						}	
					}
					
					return $data;
				}
 
				/**
				 * Retrieves table_rates array from database.
				 */
				function get_table_rates() {
					$this->table_rates = array_filter( (array) get_option( $this->table_rate_option ) );
				}
				
				/**
				 * Process and save shipping class priorities.
				 */
 				function process_shipping_class_order() {
 				
 					if (isset($_POST[$this->id.'_shipping_class_order'])) {
	 					update_option($this->shipping_class_order_option, $_POST[$this->id.'_shipping_class_order']);
 					}	
 					
 					$this->get_shipping_class_order();
				}
				
				/**
				 * Retrieves table_rates array from database.
				 */
				function get_shipping_class_order() {
					$this->shipping_class_order = array_filter( (array) get_option( $this->shipping_class_order_option ) );
				}
				
				/**
				 * Retrieves last table_rate id from database.
				 */
				function get_last_table_rate_id() {
					$this->last_table_rate_id = (int)get_option( $this->last_table_rate_id_option );
				}
				
				/*
					Retrieves available zone ids for supplied shipping address
				*/
				function get_available_zones($package) {
					
					$destination_country = $package['destination']['country'];
					$destination_state = $package['destination']['state'];
					$destination_postcode = strtoupper($package['destination']['postcode']);
					$available_zones = array();
					
					$uk_postcode_countries = array('GB', 'IM', 'GG', 'JE');
					
					// Loop through every zone
					foreach ($this->zones as $zone):
						if ($zone['type'] == 'country') { // COUNTRY
							if ( !empty($zone['country']) && in_array($destination_country, $zone['country']) ) {
								$available_zones[] = $zone['id'];
							}
						} else if ($zone['type'] == 'state') { // STATE
							if (($destination_country == $zone['country'])
								&& !empty($zone['values'])
								&& in_array($destination_state, $zone['values'])) {
								$available_zones[] = $zone['id'];
							}
						} else if ($zone['type'] == 'postcode') { // POSTCODE
							if ($destination_country == $zone['country']) {
							
								$excluded = !empty($zone['exclude']);
								$zone_match = false;
								$postcodes = explode("\n", $zone['values']);

								foreach ($postcodes as $postcode) {
								
									$found = false;

									if ($destination_postcode == $postcode) { // EXACT MATCH
										
										$found = true;
										
									} else if (strpos($postcode, '*') !== FALSE) { // WILDCARD POSTCODE
									
										$pattern = str_replace('*', '.*', $postcode);
										$found = preg_match("/^$pattern$/i", $destination_postcode);
										
									} else if (in_array($destination_country, $uk_postcode_countries)) { // UK POSTCODES NEED SPECIAL ATTENTION
									
										// Remove 3 last characters and any spaces
										$trimmed_destination_postcode = trim(substr($destination_postcode, 0, -3)); 
										
										if (preg_match("/^([A-Z]{1,2}[0-9]{0,2}[A-Z]?)$/i", $postcode, $matches)) { // UK POSTCODE ZONE
										
											$pattern = $matches[1];
											
											if ($trimmed_destination_postcode == $pattern) {
												$found = true;
											}
											
										} else if (preg_match("/^([A-Z]{1,2})([0-9]{1,2})-([0-9]{1,2})$/i", $postcode, $matches)) { // UK POSTCODE RANGE

											$letter = $matches[1];
											$min = $matches[2];
											$max = $matches[3];
											$patterns = array();
											
											// Expand postcode range
											for ($i=$min; $i<=$max; $i++) {
												$patterns[] = "{$letter}{$i}";
											}
											
											foreach ($patterns as $pattern) {
												if ($trimmed_destination_postcode == $pattern) {
													$found = true;
													break;
												}
											}
											
										}
										
									} else if (preg_match("/^([0-9]{1,10})-([0-9]{1,10})$/i", $postcode, $matches)) { // OTHER POSTCODE RANGE
									
										$min = $matches[1];
										$max = $matches[2];
										
										if ((is_numeric($destination_postcode))
										 && ($destination_postcode >= $min)
										 && ($destination_postcode <= $max)) {
											$found = true;
										}
							
									}

									if ($found && $excluded) { 
										$zone_match = true;
										break;
									} else if ($found && !$excluded) {
										$available_zones[] = $zone['id'];
										$zone_match = true;
										break;
									}
								}
								
								if (!$zone_match && $excluded) {
									$available_zones[] = $zone['id'];
								}
								
							}
						}
					endforeach;
					
					if (empty($available_zones)) {
						$found = false;
						foreach (WC()->countries->get_shipping_countries() as $id => $value):
							if ($destination_country == $id) {
								$found = true;
							}
						endforeach;
						if ($found) {						
							$available_zones[] = '0'; // "Everywhere else" zone	
						}
					}
					
					return $available_zones;
				}
				
				/*
					Retrieves shipping services which have not been disabled
				*/
				function get_available_services() {

					$available_services = array();
					
					foreach ($this->services as $service):
						//if ($service['enabled']) {
							$available_services[] = $service['id'];
						//}
					endforeach;

					return $available_services;
				}
				
				/*
					Retrieves available table_rates for cart and supplied shipping addresss
				*/
				function get_available_table_rates($package, $shipping_class, $calc_type) {
					
					$available_zones = $this->get_available_zones($package);
					$available_services = $this->get_available_services();
					$available_table_rates = array();
					
					if ($calc_type == 'per_class') {
						$weight = $this->get_cart_stat_by_shipping_class($shipping_class, 'weight');
						$total = $this->get_cart_stat_by_shipping_class($shipping_class, 'total');
						$item_count = $this->get_cart_stat_by_shipping_class($shipping_class, 'qty');
					} else { // 'per_order'
						$weight = WC()->cart->cart_contents_weight;
						$total = WC()->cart->cart_contents_total;
						$item_count = WC()->cart->cart_contents_count;
					}
					
					foreach ($this->table_rates as $table_rate):
						
						// Is table_rate for an available zone?
						$zone_pass = (in_array($table_rate['zone'], $available_zones));
						
						// Is table_rate for an available service?
						$service_pass = (in_array($table_rate['service'], $available_services));
						
						// Is table_rate valid for basket weight?
						if ($table_rate['basis'] == 'weight') {
							$weight_pass = (($weight >= $table_rate['min']) && ($this->is_less_than($weight, $table_rate['max'])));
						} else {
							$weight_pass = true;
						}
						
						// Is table_rate valid for basket total?
						if ($table_rate['basis'] == 'price') {
							$total_pass = (($total >= $table_rate['min']) && ($this->is_less_than($total, $table_rate['max'])));
						} else {
							$total_pass = true;
						}
						
						// Is table_rate valid for basket item count?
						if ($table_rate['basis'] == 'item_count') {
							$item_count_pass = (($item_count >= $table_rate['min']) && ($this->is_less_than($item_count, $table_rate['max'])));
						} else {
							$item_count_pass = true;
						}
						
						// Is table_rate valid for shipping class?
						if ($table_rate['class'] == $shipping_class) {
							$class_pass = true;
						} else if ($table_rate['class'] == '-1') { // None
							if ($shipping_class == false) {
								$class_pass = true;
							} else {
								$class_pass =false;
							}
						} else if ($table_rate['class'] == '0') { // Any
							$class_pass = true;
						} else {
							$class_pass = false;
						}
						
						// Calculate additional per item cost
						if (!empty($table_rate['item_cost'])) {
							$item_cost = $table_rate['item_cost'] * $item_count;
							$table_rate['cost'] += $item_cost;
						}
						
						// Calculate additional per [weight unit] cost
						if (!empty($table_rate['weight_cost'])) {
							$weight_cost = $table_rate['weight_cost'] * $weight;
							$table_rate['cost'] += $weight_cost;
						}
						
						// Accept table_rate if passes all tests
						if ($zone_pass && $service_pass && $weight_pass && $total_pass && $item_count_pass && $class_pass) {
							$available_table_rates[] = $table_rate;
						}
						
					endforeach;
					
					return $available_table_rates;
				}
				
				/*
					Retrieves the highest priority shipping class in cart
				*/
				function get_cart_shipping_classes() {
				
					$classes_found = array();
				
					// Loop through cart items
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$product = $cart_item['data'];
						$class = $product->get_shipping_class();
						
						// Add class to array if not already added
						if (in_array($class, $classes_found) == false) {
							$classes_found[] = $class;
						}
					}
					
					return $classes_found;
				}
				
				/*
					Retrieves the highest priority shipping class in cart
				*/
				function get_cart_highest_shipping_class() {
				
					$classes_found = $this->get_cart_shipping_classes();
					
					// Find highest class in cart
					$found = false;
					$i = 0;
					$highest = false;
					while (($found == false) && ($i < count($this->shipping_class_order))) {
					
					
						if (in_array($this->shipping_class_order[$i], $classes_found)) {
							$found = true;
							$highest = $this->shipping_class_order[$i];
						} else {
							$i++;
						}
					}
					
					return $highest;
				}
				
				/*
					Retrieves a cart start based on a shipping class
				*/
				function get_cart_stat_by_shipping_class($shipping_class, $stat) {
				
					$total = 0;
					
					// Loop through cart items
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

						$product = $cart_item['data'];
						$product_shipping_class = $product->get_shipping_class();
						
						if ($product_shipping_class == $shipping_class) {
						
							if ($stat == 'weight') {
								$total += ($product->get_weight() * $cart_item['quantity']);
							} else if ($stat == 'total') {
								$total += ($product->get_price() * $cart_item['quantity']);
							} else if ($stat == 'qty') {
								$total += $cart_item['quantity'];
							}
							
						}
						
					}
					
					return $total;
				}
				
				/*
					Return true if value less than max, incl. "*"
				*/
				function is_less_than($value, $max) {
					if ($max == '*') {
						return true;
					} else {
						return ($value <= $max);
					}
				}
				
				/*
					Retrieves an array item by searching for an id value
				*/
				function find_by_id($array, $id) {
					foreach($array as $a):
						if ($a['id'] == $id) {
							return $a;
						}
					endforeach;
					return false;
				}
				
				/*
					Retrieves cheapest rates from a list of table_rates. Will only return a maximum of one table_rate per service
				*/
				function pick_cheapest_table_rates($table_rates) {
				
					$cheapest_table_rates = array();
				
					$available_services = $this->get_available_services();
					
					foreach($available_services as $service):
						$cheapest = $this->pick_cheapest_table_rate_by_service($table_rates, $service);
						if ($cheapest != false) {
							$cheapest_table_rates[] = $cheapest;
						}
					endforeach;
					
					return $cheapest_table_rates;
				}
				
				/*
					Retrieves single cheapest rate from a list of table_rates, for
					specified service
				*/
				function pick_cheapest_table_rate_by_service($table_rates, $service) {
				
					$cheapest = false;
				
					foreach ($table_rates as $table_rate):
						if ($table_rate['service'] == $service) {
							if ($cheapest == false) {
								$cheapest = $table_rate;
							} else {
								if ($table_rate['cost'] < $cheapest['cost']) {
									$cheapest = $table_rate;
								}
							}
						}
					endforeach;
					
					return $cheapest;
				}
				
				/* 
					Print a debug message
				*/
				function debug_msg($msg) {
					error_log($msg, 0);
				}
				
				/* 
					Calculate shipping per order. Called by calculate_shipping()
				*/
				public function calculate_per_order( $package ) {
				
					$highest_shipping_class = $this->get_cart_highest_shipping_class();
				
					$available_table_rates = $this->get_available_table_rates($package, $highest_shipping_class, 'per_order');
					
					$cheapest_table_rates = $this->pick_cheapest_table_rates($available_table_rates);
					
					return $cheapest_table_rates;
					
				}
				
				/* 
					Calculate shipping per shipping class. Called by calculate_shipping()
				*/
				public function calculate_per_shipping_class( $package ) {

					$table_rates = array();
					$table_rates2 = array();
					$services = $this->get_available_services();
					$shipping_classes = $this->get_cart_shipping_classes();

					foreach ($shipping_classes as $shipping_class) {
						$table_rates[$shipping_class] = $this->get_available_table_rates($package, $shipping_class, 'per_class');
					}
					
					foreach ($services as $service) {
					
						$cost = 0;
						$is_valid = true;
						$dummy_rate = false; // Held as a dummy amalgamated table rate
		
						foreach ($shipping_classes as $shipping_class) {
						
							if ($is_valid) {
								$cheapest_table_rate = $this->pick_cheapest_table_rate_by_service($table_rates[$shipping_class], $service);
							
								if ($cheapest_table_rate) {
									$cost = $cost + $cheapest_table_rate['cost'];
									$dummy_rate = $cheapest_table_rate;
								} else {
									$is_valid = false;
								}
							}
							
						}
						
						if ($is_valid) {
							$dummy_rate['cost'] = $cost;
							$table_rates2[] = $dummy_rate;
						}
						
					}
					
					return $table_rates2;
					
				}
				
				/* 
					Calculate shipping cost. This is called by WooCommerce
				*/
				public function calculate_shipping( $package ) {
				
					//$this->show_debug();

					if ($this->settings['calc_type'] == 'per_class') {
						$table_rates = $this->calculate_per_shipping_class($package);
					} else if ($this->settings['calc_type'] == 'per_item') {
						//$this->calculate_per_item($package);
					} else {
						$table_rates = $this->calculate_per_order($package);
					}
					
					if ($this->settings['tax_status'] == 'none') {
						$tax = false;
					} else {
						$tax = '';
					}
					
					foreach ($table_rates as $table_rate):
					
						$cost = $table_rate['cost'] + $this->settings['handling_fee'];
						$service = $this->find_by_id($this->services, $table_rate['service']); // Assign to temp variable for PHP 5.3
						
						if (function_exists('icl_translate')) {
							$service_name = icl_translate('Table Rate Shipping Plus', 'shipping service: ' . $service['name'], $service['name']);
						} else {
							$service_name = $service['name'];
						}

						$rate = array(
							'id' => $this->id.'_'.$table_rate['id'],
							'label' => $service_name,
							'cost' => $cost,
							'taxes' => $tax,
							'calc_tax' => 'per_order'
						);
	
						// Register the rate
						$this->add_rate( $rate );
						
					endforeach;

				}
				
				public function show_debug() {
				
					$debug_msgs = array();
					
					$debug_msgs['Cart weight'] = WC()->cart->cart_contents_weight;
					$debug_msgs['Cart total'] = WC()->cart->cart_contents_total;
					$debug_msgs['Cart item count'] = WC()->cart->cart_contents_count;
					
					$cart_shipping_classes = $this->get_cart_shipping_classes();
					$debug_msgs['Shipping class order'] = implode($this->shipping_class_order, ', ');
					$debug_msgs['Classes in cart'] = implode($cart_shipping_classes, ', ');
					$debug_msgs['Highest class in cart'] = $this->get_cart_highest_shipping_class();
					
					foreach ($cart_shipping_classes as $shipping_class) {
						$debug_msgs["'{$shipping_class}' weight"] = $this->get_cart_stat_by_shipping_class($shipping_class, 'weight');
						$debug_msgs["'{$shipping_class}' total"] = $this->get_cart_stat_by_shipping_class($shipping_class, 'total');
						$debug_msgs["'{$shipping_class}' item count"] = $this->get_cart_stat_by_shipping_class($shipping_class, 'qty');
					}
					
					foreach ($debug_msgs as $key => $value) {
						echo "{$key} => {$value}<br>";
					}
					
				}

			}
		}
	}
	add_action( 'woocommerce_shipping_init', 'mh_wc_table_rate_plus_init' );

	function add_mh_wc_table_rate_plus( $methods ) {
		$methods[] = 'MH_Table_Rate_Plus_Shipping_Method';
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'add_mh_wc_table_rate_plus' );
	
	function mh_wc_table_rate_plus_textdomain() {
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( MHTRP_DOMAIN, false, $plugin_dir );
	}
	add_action('plugins_loaded', 'mh_wc_table_rate_plus_textdomain');
	
	// BACKUP
	
	function setup_mh_wc_table_rate_plus_export_page() {
		add_submenu_page(
			null,
			'TRSP Export',
			'TRSP Export',
			'manage_options',
			'mh_wc_table_rate_plus_export',
			'mh_wc_table_rate_plus_export'
		);
	}
	
	function mh_wc_table_rate_plus_export(){
	    global $pagenow;
	    if( 'admin.php' == $pagenow
	    && 'mh_wc_table_rate_plus_export' == $_GET['page'] ){
	    	    
			$id = 'mh_wc_table_rate_plus';
			
			$options = array(
				$id.'_table_rates',
				$id.'_last_table_rate_id',
				$id.'_zones',
				$id.'_last_zone_id',
				$id.'_services',
				$id.'_last_service_id',
				$id.'_first_run',
				$id.'_shipping_class_order',
				'woocommerce_'.$id.'_settings',
			);
	    
			$backup = array();
			foreach($options as $option) {
				$backup[$option] = get_option( $option );
			}
			
			$content = json_encode($backup);

			$length = strlen($content);
			
			header('Content-Description: File Transfer');
			header('Content-Type: application/json');
			header('Content-Disposition: attachment; filename=backup.json');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . $length);
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Expires: 0');
			header('Pragma: public');
			
			echo $content;
			exit;
	    }
	}
	
	add_action('admin_menu', 'setup_mh_wc_table_rate_plus_export_page');
	add_action('admin_init', 'mh_wc_table_rate_plus_export');
	
}