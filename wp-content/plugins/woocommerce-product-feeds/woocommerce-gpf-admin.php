<?php

/**
 * Admin class.
 *
 * Handles the display of the settings page, product meta boxes, and category meta.
 */
class WoocommerceGpfAdmin {

	private $settings        = array();
	private $product_fields  = array();
	private $template_loader = null;

	/**
	 * Constructor - set up the relevant hooks actions, and load the
	 * settings
	 *
	 * @access public
	 */
	function __construct() {

		global $woocommerce_gpf_common;

		$this->settings          = get_option( 'woocommerce_gpf_config', array() );
		$this->product_fields    = $woocommerce_gpf_common->product_fields;
		$this->template_loader   = new WoocommerceGpfTemplateLoader();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ), 11 );
		add_action( 'admin_print_styles', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_print_scripts', array( $this, 'enqueue_scripts' ) );

		// Extend category admin page.
		add_action( 'product_cat_add_form_fields', array( $this, 'category_meta_box' ), 99, 2 ); // After left-col
		add_action( 'product_cat_edit_form_fields', array( $this, 'category_meta_box' ), 99, 2 ); // After left-col
		add_action( 'created_product_cat', array( $this, 'save_category' ), 15 , 2 ); //After created
		add_action( 'edited_product_cat', array( $this, 'save_category' ), 15 , 2 ); //After saved

		// Variation form input.
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variation_input_fields'), 90, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation' ), 10, 2 );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_woocommerce_settings_tab' ), 99 );
		add_action( 'woocommerce_settings_tabs_gpf', array( $this, 'config_page' ) );
		add_action( 'woocommerce_update_options_gpf', array( $this, 'save_settings' ) );
	}

	/**
	 * Instantiate the render cache.
	 */
	public function plugins_loaded() {
		$this->cache             = new WoocommerceGpfCache();
		$this->cache_invalidator = new WoocommerceGpfCacheInvalidator( $this->cache );
	}

	/**
	 * Handle ajax callbacks for Google and bing category lookups
	 * Set up localisation
	 *
	 * @access public
	 */
	function init() {

		// Handle ajax requests for the google taxonomy search
		if ( isset( $_GET['woocommerce_gpf_search'] ) ) {
			$this->ajax_handler( $_GET['query'] );
			exit();
		}

		// Handle ajax requests for the bing taxonomy search
		if ( isset( $_GET['woocommerce_gpf_bing_search'] ) ) {
			$this->bing_ajax_handler( $_GET['query'] );
			exit();
		}

		$this->product_fields = apply_filters( 'woocommerce_wpf_product_fields', $this->product_fields );

		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce_gpf' );
		load_textdomain( 'woocommerce_gpf', WP_LANG_DIR.'/woocommerce-google-product-feed/woocommerce_gpf-'.$locale.'.mo' );
		load_plugin_textdomain( 'woocommerce_gpf', false, basename( dirname( __FILE__ ) ) . '/languages/' );

	}




	/**
	 * Extend Product Edit Page
	 *
	 * @access public
	 */
	function admin_init() {
		add_action( 'save_post', array( $this, 'save_product' ) );
		if ( isset( $this->settings['product_fields'] ) && count( $this->settings['product_fields'] ) ) {
			add_meta_box(
				'woocommerce-gpf-product-fields',
				__( 'Product Feed Information', 'woocommerce_gpf' ),
				array( $this, 'product_meta_box' ),
				'product',
				'advanced',
				'high'
			);
		}
		if ( isset( $_GET['gpf_action'] ) && $_GET['gpf_action'] === 'rebuild_cache' ) {
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'gpf_rebuild_cache' ) ) {
				$this->cache->flush_all();
			}
			wp_redirect(
				add_query_arg(
					array(
						'page'       => 'wc-settings',
						'tab'        => 'gpf',
					),
					admin_url('admin.php')
				)
			);
			exit;
		}
		$this->check_db();
	}

	/**
	 * Check the database version, and upgrade if required.
	 */
	private function check_db() {
		$current_version = (int) get_option( 'woocommerce_gpf_db_version', 1 );
		if ( $current_version >= WOOCOMMERCE_GPF_DB_VERSION ) {
			return;
		}
		// Otherwise, check for, and run updates.
		foreach ( range( $current_version + 1, WOOCOMMERCE_GPF_DB_VERSION ) as $version ) {
			if ( is_callable( array( $this, 'upgrade_db_to_' . $version ) ) ) {
				$this->{'upgrade_db_to_' . $version}();
			}
			update_option( 'woocommerce_gpf_db_version', $version );
		}
	}

	/**
	 * Upgrade the DB schema to v2.
	 *
	 * Creates render cache table.
	 */
	private function upgrade_db_to_2() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE `" . $wpdb->prefix . "wc_gpf_render_cache` (
		    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		    `post_id` bigint(20) unsigned NOT NULL,
		    `name` varchar(32) NOT NULL,
		    `value` text NOT NULL,
		    UNIQUE KEY composite_cache_idx (`post_id`, `name`)
		) $charset_collate";
		dbDelta( $sql );
	}

	/**
	 * Handle ajax requests for the google taxonomy search. Returns a JSON encoded list of matches
	 * and terminates execution.
	 *
	 * @access public
	 * @param  string $query The user input to search for
	 * @return json
	 */
	function ajax_handler( $query ) {
		global $wpdb, $table_prefix;
		// Make sure the taxonomy is up to date
		$this->refresh_google_taxonomy();
		$sql = "SELECT taxonomy_term FROM ${table_prefix}woocommerce_gpf_google_taxonomy WHERE search_term LIKE %s";
		$results = $wpdb->get_results( $wpdb->prepare( $sql, '%' . strtolower( $query ) . '%' ) );
		$suggestions = array();
		foreach ( $results as $match ) {
			$suggestions[] = $match->taxonomy_term;
		}
		$results = array( 'query' => $query, 'suggestions' => $suggestions, 'data' => $suggestions );
		echo json_encode( $results );
		exit();
	}



	/**
	 * Handle ajax requests for the Bing taxonomy search. eturns a JSON encoded list of matches
	 * and terminates execution.
	 *
	 * @access public
	 * @param  string $query The user input to search for
	 */
	function bing_ajax_handler( $query ) {

		$taxonomy = array(
			'Arts & Crafts',
			'Baby & Nursery',
			'Beauty & Fragrance',
			'Books & Magazines',
			'Cameras & Optics',
			'Car & Garage',
			'Clothing & Shoes',
			'Collectibles & Memorabilia',
			'Computing',
			'Electronics'.
			'Flowers',
			'Gourmet Food & Chocolate',
			'Health & Wellness',
			'Home Furnishings',
			'Jewelry & Watches',
			'Kitchen & Housewares',
			'Lawn & Garden',
			'Miscellaneous',
			'Movies',
			'Music',
			'Musical Instruments',
			'Office Products',
			'Pet Supplies',
			'Software',
			'Sports & Outdoors',
			'Tools & Hardware',
			'Toys',
			'Travel',
			'Vehicles',
			'Video Games',
		);

		$suggestions = array();

		foreach ( $taxonomy as $b_cat ) {
			if ( stristr( $b_cat, $query ) ) {
				$suggestions[] = $b_cat;
			}
		}

		$results = array( 'query' => $query, 'suggestions' => $suggestions, 'data' => $suggestions );
		echo json_encode( $results );
		exit();
	}



	/*
	 * Enqueue CSS needed for product pages
	 *
	 * @access public
	 */
	function enqueue_styles() {
		wp_enqueue_style( 'woocommerce_gpf_admin', plugins_url( basename( dirname( __FILE__ ) ) ) . '/css/woocommerce-gpf.css' );
		wp_enqueue_style( 'wooautocomplete', plugins_url( basename( dirname( __FILE__ ) ) ) . '/js/jquery.autocomplete.css' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}



	/**
	 * Enqueue javascript for product_type / google_product_category selector
	 *
	 * @access public
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'wooautocomplete', plugins_url( basename( dirname( __FILE__ ) ) ) . '/js/jquery.autocomplete.js', array( 'jquery', 'jquery-ui-core' ) );
		wp_enqueue_script( 'woocommerce_gpf', plugins_url( basename( dirname( __FILE__ ) ) ) . '/js/woocommerce-gpf.js', array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}



	/**
	 * Render the form to allow users to set defaults per-category
	 *
	 * @access public
	 * @param unknown $termortax
	 * @param unknown $taxonomy  (optional)
	 */
	function category_meta_box( $termortax, $taxonomy = null ) {

		// So we can use the same function for add and edit forms
		if ( $taxonomy === null ) {
			$taxonomy = $termortax;
			$term = null;
		} else {
			$term = $termortax;
		}
		if ( $term ) {
			$current_data = get_woocommerce_term_meta( $term->term_id, '_woocommerce_gpf_data', true );
		} else {
			$current_data = array();
		}
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'meta-edit-intro', array( 'loop_idx' => '' ) );

		foreach ( $this->product_fields as $key => $fieldinfo ) {

			// Skip fields which haven't been enabled in the main settings.
			if ( ! isset( $this->{'settings'}['product_fields'][ $key ] ) ) {
				continue;
			}

			$header_vars              = array();
			$def_vars                 = array();
			$row_vars                 = array();

			$header_vars['row_title'] = esc_html( $fieldinfo['desc'] );
			$header_vars['key']       = esc_html( $key );

			$header_vars['default_text'] = '';
			$placeholder                 = '';
			if ( isset( $fieldinfo['can_default'] ) && ! empty ( $this->settings['product_defaults'][ $key ] ) ) {
				$header_vars['default_text'] .= '<span class="woocommerce_gpf_default_label">(' .
					__( 'Default: ', 'woocommerce_gpf' ) .
					esc_html( $this->settings['product_defaults'][ $key ] ) .
					')</span>';
				$placeholder = __( 'Use default', 'woo_gpf' );
			}
			$row_vars['header_content'] = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'meta-field-row-header',
				$header_vars
			);

			$current_value = ! empty( $current_data[ $key ] ) ? $current_data[ $key ] : '';
			$def_vars['defaultinput'] = $this->render_field_default_input( $key, $current_value, $placeholder, null );
			$def_vars['key']          = $key;
			$variables['defaults']    = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'meta-field-row-defaults',
				$def_vars
			);
			$row_vars['data_content'] = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'meta-field-row-data',
				$variables
			);
			$this->template_loader->output_template_with_variables(
				'woo-gpf',
				'meta-field-row',
				$row_vars
			);
		}

	}



	/**
	 * Store the per-category defaults
	 *
	 * @access public
	 * @param unknown $term_id
	 */
	function save_category( $term_id ) {
		if ( isset( $_POST['_woocommerce_gpf_data'] ) ) {
			foreach ( $_POST['_woocommerce_gpf_data'] as $key => $value ) {
				$_POST['_woocommerce_gpf_data'][ $key ] = stripslashes( $value );
			}
			update_woocommerce_term_meta( $term_id, '_woocommerce_gpf_data', $_POST['_woocommerce_gpf_data'] );
		}
	}


	public function variation_input_fields( $loop_idx, $variation_data, $variation ) {

		global $woocommerce_gpf_common;

		echo '<div class="wc_gpf_metabox closed">';
		echo '<h2><strong>';
		echo __( 'Product Feed Information', 'woocommerce_gpf' );
		echo '</strong><div class="handlediv" aria-label="Click to toggle"></div>';
		echo '</h2>';
		echo '<div class="wc_gpf_metabox_content" style="display:none;">';
		echo '<p>' . __( 'Set values here if you want to override the information for this specific variation. If information should apply to all variations, then set it against the main product.', 'woocommerce_gpf' ) . '</p>';
		$current_data     = get_post_meta( $variation->ID, '_woocommerce_gpf_data', true );
		$product_defaults = $woocommerce_gpf_common->get_values_for_product( $variation->ID, 'all', true );

		$this->render_exclude_product(
			'exclude_product',
			! empty( $current_data['exclude_product'] ) ? true : false,
			null,
			$loop_idx
		);

		$this->template_loader->output_template_with_variables(
			'woo-gpf',
			'product-meta-edit-intro',
			array( 'loop_idx' => $loop_idx)
		);
		foreach ( $this->product_fields as $key => $fieldinfo ) {
			if ( ! isset( $this->settings['product_fields'][ $key ] ) ) {
				continue;
			}
			$variables                      = $this->default_field_variables( $key, $loop_idx );
			$variables['field_description'] = esc_html( $fieldinfo['desc'] );
			$variables['field_defaults']    = '<br>';
			$placeholder                    = '';
			if ( isset( $fieldinfo['can_prepopulate'] ) && ! empty( $this->settings['product_prepopulate'][ $key ] ) ) {
				$prepopulate_vars             = array();
				$prepopulate_vars['label']    = $this->get_prepopulate_label( $this->settings['product_prepopulate'][ $key ] );
				$variables['field_defaults'] .= $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'product-meta-prepopulate-text',
					$prepopulate_vars
				);
			}
			if ( isset( $fieldinfo['can_default'] ) && ! empty( $product_defaults[ $key ] ) ) {
				$variables['field_defaults'] .= $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'variation-meta-default-text',
					array(
						'default' => sprintf(
							'Defaults to value from main product, or &quot;%s&quot;.',
							esc_html( $product_defaults[ $key ] )
						),
					)
				);
				$placeholder = __( 'Use default', 'woo_gpf' );
			}
			if ( ! isset( $fieldinfo['callback'] ) || ! is_callable( array( &$this, $fieldinfo['callback'] ) ) ) {
				$current_value = ! empty ( $current_data[ $key ] ) ? $current_data[ $key ] : '';
				$variables['field_input'] = $this->render_field_default_input(
					$key,
					$current_value,
					$placeholder,
					$loop_idx
				);
			} else {
				if ( isset( $current_data[ $key ] ) ) {
					$variables['field_input'] = call_user_func(
						array( $this, $fieldinfo['callback'] ),
						$key,
						$current_data[ $key ],
						$placeholder,
						$loop_idx
					);
				} else {
					$variables['field_input'] = call_user_func(
						array( $this, $fieldinfo['callback'] ),
						$key,
						null,
						$placeholder,
						$loop_idx
					);
				}
			}
			$this->template_loader->output_template_with_variables( 'woo-gpf', 'product-meta-field-row', $variables );
		}
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'product-meta-edit-footer', array() );
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Meta box on product pages for setting per-product information
	 *
	 * @access public
	 */
	public function product_meta_box() {

		global $post, $woocommerce_gpf_common;

		$current_data     = get_post_meta( $post->ID, '_woocommerce_gpf_data', true );
		$product_defaults = $woocommerce_gpf_common->get_values_for_product( $post->ID, 'all', true );

		$this->render_exclude_product(
			'exclude_product',
			! empty( $current_data['exclude_product'] ) ? true : false
		);

		$this->template_loader->output_template_with_variables( 'woo-gpf', 'product-meta-edit-intro', array( 'loop_idx' => '' ) );
		foreach ( $this->product_fields as $key => $fieldinfo ) {
			if ( ! isset( $this->settings['product_fields'][ $key ] ) ) {
				continue;
			}

			$variables                      = $this->default_field_variables( $key );
			$variables['field_description'] = esc_html( $fieldinfo['desc'] );
			$variables['field_defaults']    = '<br>';
			$placeholder                    = '';
			if ( isset( $fieldinfo['can_prepopulate'] ) && ! empty( $this->settings['product_prepopulate'][ $key ] ) ) {
				$prepopulate_vars             = array();
				$prepopulate_vars['label']    = $this->get_prepopulate_label( $this->settings['product_prepopulate'][ $key ] );
				if ( ! empty( $prepopulate_vars['label'] ) ) {
					$variables['field_defaults'] .= $this->template_loader->get_template_with_variables(
						'woo-gpf',
						'product-meta-prepopulate-text',
						$prepopulate_vars
					);
				}
			}
			if ( isset( $fieldinfo['can_default'] ) && ! empty( $product_defaults[ $key ] ) ) {
				$variables['field_defaults'] .= $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'product-meta-default-text',
					array(
						'default' => '(' . __( 'Default: ', 'woocommerce_gpf' ) . esc_html( $product_defaults[ $key ] ) . ')'
					)
				);
				$placeholder = __( 'Use default', 'woo_gpf' );
			}
			if ( ! isset( $fieldinfo['callback'] ) || ! is_callable( array( &$this, $fieldinfo['callback'] ) ) ) {
				$current_value = ! empty ( $current_data[ $key ] ) ? $current_data[ $key ] : '';
				$variables['field_input'] = $this->render_field_default_input(
					$key,
					$current_value,
					$placeholder,
					null
				);
			} else {
				if ( isset( $current_data[ $key ] ) ) {
					$variables['field_input'] = call_user_func(
						array( $this, $fieldinfo['callback'] ),
						$key,
						$current_data[ $key ],
						$placeholder,
						null
					);
				} else {
					$variables['field_input'] = call_user_func(
						array( $this, $fieldinfo['callback'] ),
						$key,
						null,
						$placeholder,
						null
					);
				}
			}
			$this->template_loader->output_template_with_variables( 'woo-gpf', 'product-meta-field-row', $variables );
		}
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'product-meta-edit-footer', array() );
	}



	/**
	 * Store the per-product meta information. Called from wpsc_edit_product which has already checked we're not in an AUTOSAVE
	 *
	 * @access public
	 * @param unknown $product_id
	 */
	function save_product( $product_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( empty ( $_POST['_woocommerce_gpf_data'] ) ) {
			return;
		}
		$current_data = get_post_meta( $product_id, '_woocommerce_gpf_data', true );
		if ( ! $current_data ) {
			$current_data = array();
		}
		// Remove entries that are blanked out
		foreach ( $_POST['_woocommerce_gpf_data'] as $key => $value ) {
			if ( is_numeric( $key ) ) {
				// This is the variation data, we can ignore it here.
				continue;
			}
			if ( empty ( $value ) ) {
				unset( $_POST['_woocommerce_gpf_data'][ $key ] );
				if ( isset( $current_data[ $key ] ) ) {
					unset( $current_data[ $key ] );
				}
			} else {
				$_POST['_woocommerce_gpf_data'][ $key ] = stripslashes( $value );
			}
		}
		// Including missing checkboxes
		if ( ! isset( $_POST['_woocommerce_gpf_data']['exclude_product'] ) ) {
			unset( $current_data['exclude_product'] );
		}
		if ( ! isset ( $_POST['_woocommerce_gpf_data']['is_bundle'] ) ) {
			unset ( $current_data['is_bundle'] );
		}
		$current_data = array_merge( $current_data, $_POST['_woocommerce_gpf_data'] );
		update_post_meta( $product_id, '_woocommerce_gpf_data', $current_data );
	}

	/**
	 * Store GPF data set specifically against the variation.
	 */
	function save_variation( $product_id, $idx ) {

		if ( empty ( $_POST['_woocommerce_gpf_data'][ $idx ] ) ) {
			return;
		}
		$current_data = get_post_meta( $product_id, '_woocommerce_gpf_data', true );
		if ( ! $current_data ) {
			$current_data = array();
		}
		// Remove entries that are blanked out
		foreach ( $_POST['_woocommerce_gpf_data'][ $idx ] as $key => $value ) {
			if ( empty ( $value ) ) {
				unset( $_POST['_woocommerce_gpf_data'][ $idx ][ $key ] );
				if ( isset( $current_data[ $key ] ) ) {
					unset( $current_data[ $key ] );
				}
			} else {
				$_POST['_woocommerce_gpf_data'][ $idx ][ $key ] = stripslashes( $value );
			}
		}
		// Including missing checkboxes
		if ( ! isset( $_POST['_woocommerce_gpf_data'][ $idx ]['exclude_product'] ) ) {
			unset( $current_data['exclude_product'] );
		}
		$current_data = array_merge( $current_data, $_POST['_woocommerce_gpf_data'][ $idx ] );
		update_post_meta( $product_id, '_woocommerce_gpf_data', $current_data );
	}

	/**
	 * Produce a default variables array for passing to a field's default template.
	 *
	 * @param  string $key  The key being processed.
	 *
	 * @return array        The default variables array.
	 */
	private function default_field_variables($key, $loop_idx = null) {
		$variables = array();
		$variables['raw_key'] = esc_attr( $key );
		if ( $loop_idx === null ) {
			$variables['key'] = esc_attr( $key );
		} else {
			$variables['key'] = $loop_idx . '][' . esc_attr( $key );
		}
		if ( isset( $_REQUEST['post'] ) || isset( $_REQUEST['taxonomy'] ) || $loop_idx !== null) {
			$variables['emptytext'] = __( 'Use default', 'woocommerce_gpf' );
		} else {
			$variables['emptytext'] = __( 'No default', 'woocommerce_gpf' );
		}
		return $variables;
	}

	/**
	 * Loop through the available choices and set a variable for each choice indicating if it is
	 * selected or not.
	 *
	 * @param  array  $choices      Array of choice values.
	 * @param  string $current_data The current selected value.
	 * @param  array  $variables     The template variables array to add to.
	 *
	 * @return array                The modified template variables array.
	 */
	private function default_selected_choices( $choices, $current_data, $variables ) {
		foreach ( $choices as $choice ) {
			if ( $choice == $current_data ) {
				$variables[ $choice . '-selected' ] = ' selected';
			} else {
				$variables[ $choice . '-selected' ] = '';
			}
		}
		return $variables;
	}

	/**
	 * Render the options for the exclude product checkbox.
	 *
	 * @access private
	 * @param  string  $key          The key being processed.
	 * @param  string  $current_data The current value of this key.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_exclude_product( $key, $current_data = false, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key );
		$variables['checked'] = '';
		if ( $current_data ) {
			$variables['checked'] = ' checked="checked"';
		}
		$variables['hide_product_text'] = __( 'Hide this product from the feed', 'woocommerce_gpf' );
		if ( $loop_idx !== null ) {
			$variables['loop_idx'] = '[' . $loop_idx . ']';
			$variables['loop_num'] = $loop_idx;
		} else {
			$variables['loop_idx'] = '';
			$variables['loop_num'] = '';
		}
		$this->template_loader->output_template_with_variables(
			'woo-gpf',
			'meta-exclude-product',
			$variables
		);
	}

	/**
	 * Render the options for the identifier_exists attribute
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_i_exists( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables = $this->default_selected_choices(
			array( 'included', 'not-included' ),
			$current_data,
			$variables
		);
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-iexists',
			$variables
		);
	}

	/**
	 * Used to render the drop-down of valid gender options
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_gender( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables = $this->default_selected_choices(
			array( 'male', 'female', 'unisex' ),
			$current_data,
			$variables
		);
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-gender',
			$variables
		);
	}

	/**
	 * Used to render the drop-down of valid conditions
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_condition( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables = $this->default_selected_choices(
			array( 'new', 'refurbished', 'used' ),
			$current_data,
			$variables
		);
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-condition',
			$variables
		);
	}



	/**
	 * Used to render the drop-down of valid availability
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_availability( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables = $this->default_selected_choices(
			array( 'in stock', 'available for order', 'preorder', 'out of stock' ),
			$current_data,
			$variables
		);
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-availability',
			$variables
		);
	}



	/**
	 * Let people choose whether a product is_bundle.
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_is_bundle( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables['value'] = checked( 'on', $current_data, false );
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-is-bundle',
			$variables
		);
	}



	/**
	 * Let people choose an availability date for a product.
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_availability_date( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables['value'] = esc_attr( $current_data );
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-availability-date',
			$variables
		);
	}



	/**
	 * Used to render the drop-down of valid age groups
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_age_group( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables = $this->default_selected_choices(
			array( 'newborn', 'infant', 'toddler', 'kids', 'adult' ),
			$current_data,
			$variables
		);
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-age-group',
			$variables
		);
	}



	/**
	 * Used to render the drop-down of valid size types
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_size_type( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables = $this->default_selected_choices(
			array( 'regular', 'petite', 'plus', 'big and tall', 'maternity' ),
			$current_data,
			$variables
		);
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-size-type',
			$variables
		);
	}



	/**
	 * Used to render the drop-down of valid size systems
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_size_system( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables = $this->default_selected_choices(
			array( 'US', 'UK', 'EU', 'AU', 'BR', 'CN', 'FR', 'DE', 'IT', 'JP', 'MEX' ),
			$current_data,
			$variables
		);
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-size-system',
			$variables
		);
	}


	private function get_google_taxonomy_locale() {
		global $woocommerce;
		$available_locales = array(
			'DK' => 'da-DK',
			'DE' => 'de-DE',
			'US' => 'en-US',
			'GB' => 'en-GB',
			'ES' => 'es-ES',
			'FR' => 'fr-FR',
			'IT' => 'it-IT',
			'NL' => 'nl-NL',
			'NO' => 'no-NO',
			'PL' => 'pl-PL',
			'BR' => 'pt-BR',
			'SE' => 'sv-SE',
			'TR' => 'tr-TR',
			'CZ' => 'cs-CZ',
			'RU' => 'ru-RU',
			'CN' => 'zh-CN',
			'JP' => 'ja-JP',
		);
		$base_country = $woocommerce->countries->get_base_country();
		if ( isset( $available_locales[ $base_country ] ) ) {
			return $available_locales[ $base_country ];
		} else {
			return 'en-US';
		}
	}
	/**
	 * Retrieve the Google taxonomy list to allow users to choose from it
	 *
	 * @access private
	 * @return unknown
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function refresh_google_taxonomy() {

		global $wpdb, $table_prefix;

		$locale = $this->get_google_taxonomy_locale();
		$cache_key = 'woocommerce_gpf_tax_' . $locale;

		// Retrieve from cache - avoid hitting Google.com too much because they might mind :)
		$taxonomies_cached = get_transient( $cache_key );
		if ( $taxonomies_cached ) {
			return true;
		}
		set_transient( $cache_key, true, time() + ( 60 * 60 * 24 * 14 ) );
		$request = wp_remote_get( 'http://www.google.com/basepages/producttype/taxonomy.' . $locale . '.txt' );
		if ( is_wp_error( $request ) || ! isset( $request['response']['code'] ) || '200' != $request['response']['code'] ) {
			return array();
		}
		$taxonomies = explode( "\n", $request['body'] );
		// Strip the comment at the top
		array_shift( $taxonomies );
		// Strip the extra newline at the end
		array_pop( $taxonomies );

		$sql = 'DELETE FROM ' . $table_prefix . 'woocommerce_gpf_google_taxonomy';
		$wpdb->query( $sql );

		$cnt = 0;
		$values = array();
		foreach ( $taxonomies as $term ) {
			$values[] = $term;
			$values[] = strtolower( $term );
			$cnt++;
			if ( $cnt == 250 ) {
				$sql = "INSERT INTO ${table_prefix}woocommerce_gpf_google_taxonomy VALUES ";
				$sql .= str_repeat( '(%s,%s),', $cnt - 1 ) . '(%s,%s)';
				$wpdb->query( $wpdb->prepare( $sql, $values ) );
				$cnt = 0;
				$values = array();
			}
		}
		if ( $cnt ) {
			$sql = "INSERT INTO ${table_prefix}woocommerce_gpf_google_taxonomy VALUES ";
			$sql .= str_repeat( '(%s,%s),', $cnt - 1 ) . '(%s,%s)';
			$wpdb->query( $wpdb->prepare( $sql, $values ) );
		}
		return true;
	}



	/**
	 * Let people choose from the Google taxonomy for the product_type tag
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_product_type( $key, $current_data = null, $placeholder = '', $loop_idx = null ) {
		$this->refresh_google_taxonomy();
		$variables = $this->default_field_variables( $key, $loop_idx );
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}
		$variables['current_data'] = esc_attr( $current_data );
		$variables['current_data'] = esc_attr( $current_data );
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-product-type',
			$variables
		);
	}



	/**
	 * Let people choose from the Bing taxonomy for the bing_category tag
	 *
	 * @access private
	 * @param  string  $key          The key being processed
	 * @param  string  $current_data The current value of this key
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function render_b_category( $key, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables['current_data'] = esc_attr( $current_data );
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}
		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-bing-category',
			$variables
		);
	}


	/**
	 * Add a tab to the WooCommerce settings pages
	 *
	 * @access public
	 * @param  array $tabs The current list of settings tabs
	 *
	 * @return array       The tabs array with the new item added
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	function add_woocommerce_settings_tab( $tabs ) {
		$tabs['gpf'] = __( 'Product Feeds', 'woocommerce_gpf' );
		return $tabs;
	}

	/**
	 * Generate the feed icons for the field on the main settings page.
	 */
	private function feed_images_for_field( $key ) {
		$results = '';
		foreach ( $this->product_fields[ $key ]['feed_types'] as $feed_type ) {
			if ( 'googleinventory' == $feed_type ) {
				continue;
			}
			$results .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'admin-feed-image',
				array(
					'image_url' => esc_url( plugins_url( "images/$feed_type.png", __FILE__ ) ),
					'alt_text'  => esc_attr( $feed_type ),
				)
			);
		}
		return $results;
	}

	/**
	 * Show the preopulate selector for a field.
	 *
	 * @param  string $key The key of the current field.
	 *
	 * @return string      The markup for the selector.
	 */
	private function prepopulate_selector_for_field( $key ) {

		global $woocommerce_gpf_common;

		if ( empty( $this->product_fields[ $key ]['can_prepopulate'] ) ) {
			return '';
		}
		$options          = $woocommerce_gpf_common->get_prepopulate_options();
		$selected_value   = ! empty( $this->settings['product_prepopulate'][ $key ] ) ?
							$this->settings['product_prepopulate'][ $key ] :
							'' ;
		$variables['key'] = esc_attr( $key );
		$variables['options'] = '';
		foreach ( $options as $key => $value ) {
			if ( 0 === stripos( $key, 'disabled' ) ) {
				$disabled = ' disabled';
			} else {
				$disabled = '';
			}
			$variables['options'] .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-prepopulate-option',
				array(
					'key'      => esc_attr( $key ),
					'value'    => esc_html( $value ),
					'disabled' => $disabled,
					'selected' => selected( $key, $selected_value, false ),
				)
			);
		}
		$variables['none'] = __( 'No', 'woo_gpf' );
		$variables['intro_text'] = __( 'Use value from existing product field: ', 'woo_gpf' );
		return $this->template_loader->get_template_with_variables( 'woo-gpf', 'field-row-prepopulate', $variables );
	}

	/**
	 * Get the label descriptor for a given field.
	 *
	 * @param  string  $field  The field to find the label for.
	 *
	 * @return string          The field label.
	 */
	private function get_label_descriptor_for_field( $field ) {

		global $woocommerce_gpf_common;

		$prepopulate_options = $woocommerce_gpf_common->get_prepopulate_options();
		if ( ! empty( $prepopulate_options[ 'field:' . $field ] ) ) {
			return $prepopulate_options[ 'field:' . $field ];
		} else {
			return $field;
		}
	}
	/**
	 * Get the label describing how a field is prepopulated.
	 *
	 * @param  string $key  The prepopulate value for the field
	 *
	 * @return string       The label text.
	 */
	private function get_prepopulate_label( $key ) {
		list( $type, $value ) = explode( ':', $key );
		switch ( $type ) {
			case 'tax':
				$taxonomy = get_taxonomy( $value );
				if ( $taxonomy ) {
					$descriptor = sprintf( __( '<em>%s</em> taxonomy', 'woo_gpf' ), $taxonomy->labels->singular_name );
				}
				break;
			case 'field':
				$label = $this->get_label_descriptor_for_field( $value );
				$descriptor = sprintf( __( '<em>%s</em> field', 'woo_gpf' ), $label );
				break;
		}
		if ( empty( $descriptor ) ) {
			return '';
		}
		return sprintf(
			__( 'Uses value from %s if set.', 'woo_gpf' ),
			$descriptor
		);
	}

	/**
	 * Show config page, and process form submission
	 *
	 * @access public
	 */
	function config_page() {

		global $wpdb, $table_prefix;

		// Output the header.
		$variables = array();
		$variables['inventory_text']              = __( 'product inventory feed also available.', 'woo_gpf' );
		$variables['bing_url']                    = esc_url(
			add_query_arg( array( 'woocommerce_gpf' => 'bing', 'f' => 'f.txt' ), get_home_url( null, '/' ) )
		);
		$variables['google_url']                  = esc_url(
			add_query_arg( array( 'woocommerce_gpf' => 'google' ), get_home_url( null, '/' ) )
		);
		$variables['inventory_url']               = esc_url(
			add_query_arg( array( 'woocommerce_gpf' => 'googleinventory' ), get_home_url( null, '/' ) )
		);
		if ( $this->cache->is_enabled() ) {
			// Work out how many products we have cached per-feed type.
			$status = array(
				'google'          => 0,
				'googleinventory' => 0,
				'bing'            => 0,
			);
			$results = $wpdb->get_results(
				"SELECT `name`,
				        COUNT(DISTINCT(post_id)) AS total
				   FROM {$table_prefix}wc_gpf_render_cache
			   GROUP BY `name`",
			   OBJECT_K
			);
			$results = wp_list_pluck( $results, 'total' );
			$status = array_merge( $status, $results );
			// Work out the total number of eligible products.
			$args = array(
				'status'      => array( 'publish' ),
				'type'        => array( 'simple', 'composite', 'variable', 'bundle' ),
				'limit'       => 1,
				'offset'      => 0,
				'return'      => 'ids',
				'paginate'    => true,
			);
			$results = wc_get_products( $args );
			$total_cache = $results->total;

			$settings_url = add_query_arg(
				array(
					'page'       => 'wc-settings',
					'tab'        => 'gpf',
				),
				admin_url('admin.php')
			);
			$rebuild_url = wp_nonce_url(
				add_query_arg(
					array(
						'gpf_action' => 'rebuild_cache',
					),
					$settings_url
				),
				'gpf_rebuild_cache'
			);
			$cache_status_variables = array(
				'google_cache_status' => sprintf( __( '<strong>%d</strong> / <strong>%d</strong> generated', 'woocommerce_gpf' ), $status['google'], $total_cache ),
				'google_inventory_cache_status' => sprintf( __( '<strong>%d</strong> / <strong>%d</strong> generated', 'woocommerce_gpf' ), $status['googleinventory'], $total_cache ),
				'bing_cache_status' => sprintf( __( '<strong>%d</strong> / <strong>%d</strong> generated', 'woocommerce_gpf' ), $status['bing'], $total_cache ),
				'rebuild_url' => $rebuild_url,
				'settings_url' => $settings_url,
			);
			$variables['cache_status'] = $this->template_loader->get_template_with_variables( 'woo-gpf', 'admin-cache-status', $cache_status_variables );
		} else {
			$variables['cache_status'] = '';
		}
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'admin-intro', $variables );

		// Output the fields.
		foreach ( $this->product_fields as $key => $info ) {

			$variables                 = $row_vars = $def_vars = array();
			$variables['row_title']    = esc_html( $info['desc'] );
			$variables['feed_images']  = $this->feed_images_for_field( $key );

			$row_vars['header_content'] = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-header',
				$variables
			);

			$variables = array();
			$variables['key'] = esc_attr( $key );
			if ( isset( $this->settings['product_fields'][ $key ] ) ) {
				$variables['checked'] = 'checked="checked"';
			} else {
				$variables['checked'] = '';
			}
			$variables['full_desc'] = esc_html( $info['full_desc'] );

			if ( isset( $this->product_fields[ $key ]['can_default'] ) ) {
				$def_vars['defaultinput'] = __( 'Store default: <br>', 'woocommerce_gpf' ) . $this->render_field_default_input( $key );
			} else {
				$def_vars['defaultinput'] = '';
			}
			$def_vars['prepopulates'] = $this->prepopulate_selector_for_field( $key );
			$def_vars['key'] = $key;
			if ( ! isset( $this->settings['product_fields'][ $key ] ) ) {
				$def_vars['displaynone'] = ' style="display:none;"';
			} else {
				$def_vars['displaynone'] = '';
			}
			$variables['defaults']    = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-defaults',
				$def_vars
			);
			$row_vars['data_content'] = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-data',
				$variables
			);
			$this->template_loader->output_template_with_variables(
				'woo-gpf',
				'field-row',
				$row_vars
			);
		}
		$variables = array();
		$variables['include_variations_selected'] = checked(
			'on',
			isset($this->settings['include_variations']) ? $this->settings['include_variations'] : '',
			false
		);
		if ( version_compare( WOOCOMMERCE_VERSION, '2.4.0', '>' ) ) {
			$variables['include_variations'] = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'admin-include-variations',
				$variables
			);
		} else {
			$variables['include_variations'] = '';
		}
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'admin-footer', $variables );
	}

	/**
	 * Renders the output for the "default" box for a field.
	 *
	 * @param string $key           The field being rendered.
	 * @param string $current_data  The current value. If not provided, the default will be used
	 *                              from the store wide settings.
	 * @param string $placeholder    Placeholder text to use, leave blank for no placeholder.
	 */
	private function render_field_default_input( $key, $current_data = false, $placeholder = '', $loop_idx = null ) {
		$variables = array();
		if ( $loop_idx === null ) {
			$variables['key'] = $key;
		} else {
			$variables['key'] = $loop_idx . '][' . $key;
		}
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}
		if ( false === $current_data ) {
			$current_data = ! empty( $this->settings['product_defaults'][ $key ] ) ? $this->settings['product_defaults'][ $key ] : '';
		}
		if ( ! isset( $this->{'product_fields'}[ $key ]['callback'] ) ||
			 ! is_callable( array( $this, $this->{'product_fields'}[ $key ]['callback'] ) ) ) {
			$variables['defaultvalue'] = esc_attr( $current_data );
			return $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-generic',
				$variables
			);
		} else {
			return call_user_func(
				array(
					$this,
					$this->{'product_fields'}[ $key ]['callback'],
				),
				$key,
				$current_data,
				$placeholder,
				$loop_idx
			);
		}
	}



	/**
	 * Save the settings from the config page
	 *
	 * @access public
	 */
	function save_settings() {

		// Check nonce
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'woocommerce-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'woothemes' ) );
		}

		if ( ! $this->settings ) {
			$this->settings = array();
			add_option( 'woocommerce_gpf_config', $this->settings, '', 'yes' );
		}

		if ( ! empty( $_POST['_woocommerce_gpf_data'] ) ) {
			// We do these so we can re-use the same form field rendering code for the fields
			foreach ( $_POST['_woocommerce_gpf_data'] as $key => $value ) {
				$_POST['_woocommerce_gpf_data'][ $key ] = stripslashes( $value );
			}
			$_POST['woocommerce_gpf_config']['product_defaults'] = $_POST['_woocommerce_gpf_data'];
			unset( $_POST['_woocommerce_gpf_data'] );
		}

		if ( ! empty( $_POST['_woocommerce_gpf_prepopulate'] ) ) {
			// We do these so we can re-use the same form field rendering code for the fields
			foreach ( $_POST['_woocommerce_gpf_prepopulate'] as $key => $value ) {
				$_POST['_woocommerce_gpf_prepopulate'][ $key ] = stripslashes( $value );
			}
			$_POST['woocommerce_gpf_config']['product_prepopulate'] = $_POST['_woocommerce_gpf_prepopulate'];
			unset( $_POST['_woocommerce_gpf_prepopulate'] );
		}

		$this->settings = $_POST['woocommerce_gpf_config'];
		update_option( 'woocommerce_gpf_config', $this->settings );
	}
}

global $woocommerce_gpf_admin;
$woocommerce_gpf_admin = new WoocommerceGpfAdmin();
