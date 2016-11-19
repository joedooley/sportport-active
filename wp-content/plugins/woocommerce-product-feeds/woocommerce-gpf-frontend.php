<?php
/**
 * Frontend class.
 *
 * Handles grabbing the products and invoking the relevant feed class to render the feed.
 */
class WoocommerceGpfFrontend {

	protected $feed        = null;
	protected $feed_format = '';
	protected $settings    = array();
	protected $image_style = 'full';

	/**
	 * Constructor. Grab the settings, and add filters if we have stuff to do
	 *
	 * @access public
	 */
	public function __construct() {

		global $wp_query;

		if ( 'google' == $wp_query->query_vars['woocommerce_gpf'] ) {
			$this->feed = new WoocommerceGpfFeedGoogle();
			$this->feed_format = 'google';
		} elseif ( 'googleinventory' == $wp_query->query_vars['woocommerce_gpf'] ) {
			$this->feed = new WoocommerceGpfFeedGoogleInventory();
			$this->feed_format = 'googleinventory';
		} elseif ( 'bing' == $wp_query->query_vars['woocommerce_gpf'] ) {
			$this->feed = new WoocommerceGpfFeedBing();
			$this->feed_format = 'bing';
		}
		$this->settings = get_option( 'woocommerce_gpf_config', array() );
		if ( ! empty( $this->feed ) ) {
			add_action( 'woocommerce_gpf_elements', array( $this, 'general_elements' ), 10, 3 );
			add_action( 'woocommerce_gpf_elements_google', array( $this, 'shipping_height_elements' ), 10, 3 );
			add_action( 'woocommerce_gpf_elements_google', array( $this, 'shipping_width_elements' ), 10, 3 );
			add_action( 'woocommerce_gpf_elements_google', array( $this, 'shipping_length_elements' ), 10, 3 );
			add_action( 'woocommerce_gpf_elements_google', array( $this, 'all_or_nothing_shipping_elements' ), 11, 3 );
			add_action( 'woocommerce_gpf_feed_item', array( $this, 'general_feed_item' ), 10, 1 );
			add_action( 'template_redirect', array( $this, 'render_product_feed' ), 15 );
		}
		$this->image_style = apply_filters( 'woocommerce_gpf_image_style', $this->image_style );
	}

	/**
	 * Retrieve Post Thumbnail URL
	 *
	 * @param int     $post_id (optional) Optional. Post ID.
	 * @param string  $size    (optional) Optional. Image size.  Defaults to 'post-thumbnail'.
	 * @return string|bool Image src, or false if the post does not have a thumbnail.
	 */
	protected function get_the_post_thumbnail_src( $post_id = null, $size = 'post-thumbnail' ) {

		$post_thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $post_thumbnail_id ) {
			return false;
		}

		list( $src ) = wp_get_attachment_image_src( $post_thumbnail_id, $size, false );
		return $src;
	}

	/**
	 * Helper function to retrieve a custom field from a product, compatible
	 * both with WC < 2.0 and WC >= 2.0
	 *
	 * @param WC_Product $product the product object
	 * @param string $field_name the field name, without a leading underscore
	 *
	 * @return mixed the value of the member named $field_name, or null
	 */
	private function get_product_meta( $product, $field_name ) {

		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0' ) >= 0 ) {
			// even in WC >= 2.0 product variations still use the product_custom_fields array apparently
			if ( $product->variation_id && isset( $product->product_custom_fields[ '_' . $field_name ][0] ) && $product->product_custom_fields[ '_' . $field_name ][0] !== '' ) {
				return $product->product_custom_fields[ '_' . $field_name ][0];
			}
			// use magic __get
			return $product->$field_name;
		} else {
			// variation support: return the value if it's defined at the variation level
			if ( isset( $product->variation_id ) && $product->variation_id ) {
				if ( ( $value = get_post_meta( $product->variation_id, '_' . $field_name, true ) ) !== '' ) {
					return $value;
				}
				// otherwise return the value from the parent
				return get_post_meta( $product->id, '_' . $field_name, true );
			}
			// regular product
			return isset( $product->product_custom_fields[ '_' . $field_name ][0] ) ? $product->product_custom_fields[ '_' . $field_name ][0] : null;
		}

	}

	/**
	 * Helper function for WooCommerce v2.0.x
	 * Checks if a variation is visible or not.
	 */
	private function variation_is_visible( $variation ) {
		if ( method_exists( $variation, 'variation_is_visible' ) ) {
			return $variation->variation_is_visible();
		}
		$visible = true;
		// Published == enabled checkbox
		if ( 'publish' != get_post_status( $variation->variation_id ) ) {
			$visible = false;
		}
		// Out of stock visibility
		elseif ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) {
			$visible = false;
		}
		// Price not set
		elseif ( $variation->get_price() === '' ) {
			$visible = false;
		}
		return $visible;
	}

	/**
	 * Determines the lowest price (inc & ex. VAT) for a product, taking into account its child
	 * products as well as the main product price.
	 */
	private function get_product_prices( &$feed_item, $woocommerce_product ) {

		// Grab the price of the main product.
		$prices = $this->generate_prices_for_product( $woocommerce_product );

		// Adjust the price if there are cheaper child products.
		$prices = $this->adjust_prices_for_children( $prices, $woocommerce_product );

		// Set the selected prices into the feed item.
		$feed_item->regular_price_ex_tax  = $prices->regular_price_ex_tax;
		$feed_item->regular_price_inc_tax = $prices->regular_price_inc_tax;
		$feed_item->sale_price_ex_tax     = $prices->sale_price_ex_tax;
		$feed_item->sale_price_inc_tax    = $prices->sale_price_inc_tax;
		$feed_item->price_ex_tax          = $prices->price_ex_tax;
		$feed_item->price_inc_tax         = $prices->price_inc_tax;
		$feed_item->sale_price_start_date = $prices->sale_price_start_date;
		$feed_item->sale_price_end_date   = $prices->sale_price_end_date;
	}

	/**
	 * Generates the inc, and ex. tax prices for both the regular, and sale price for a specific
	 * product.
	 */
	private function generate_prices_for_product( $woocommerce_product ) {

		$prices = new stdClass();
		$prices->sale_price_ex_tax     = null;
		$prices->sale_price_inc_tax    = null;
		$prices->regular_price_ex_tax  = null;
		$prices->regular_price_inc_tax = null;
		$prices->sale_price_start_date = null;
		$prices->sale_price_end_date   = null;

		// Grab the regular price of the base product.
		$regular_price                 = $woocommerce_product->get_regular_price();
		if ( '' != $regular_price ) {
			$prices->regular_price_ex_tax  = $woocommerce_product->get_price_excluding_tax( 1, $regular_price );
			$prices->regular_price_inc_tax = $woocommerce_product->get_price_including_tax( 1, $regular_price );
		}

		// Grab the sale price of the base product.
		$sale_price                        = $woocommerce_product->get_sale_price();
		if ( $sale_price != '' ) {
			$prices->sale_price_ex_tax     = $woocommerce_product->get_price_excluding_tax( 1, $sale_price );
			$prices->sale_price_inc_tax    = $woocommerce_product->get_price_including_tax( 1, $sale_price );
			$prices->sale_price_start_date = $woocommerce_product->sale_price_dates_from;
			$prices->sale_price_end_date   = $woocommerce_product->sale_price_dates_to;
		}

		// Populate a "price", using the sale price if there is one, the actual price if not.
		if ( null != $prices->sale_price_ex_tax ) {
			$prices->price_ex_tax  = $prices->sale_price_ex_tax;
			$prices->price_inc_tax = $prices->sale_price_inc_tax;
		} else {
			$prices->price_ex_tax  = $prices->regular_price_ex_tax;
			$prices->price_inc_tax = $prices->regular_price_inc_tax;
		}
		return $prices;
	}

	/**
	 * Adjusts the prices of the feed item according to child products.
	 */
	private function adjust_prices_for_children( $prices, $woocommerce_product ) {
		if ( ! $woocommerce_product->has_child() ) {
			return $prices;
		}

		$children = $woocommerce_product->get_children();
		foreach ( $children as $child ) {
			$child_product = $woocommerce_product->get_child( $child );
			if ( ! $child_product ) {
				continue;
			}
			if ( 'variation' == $child_product->product_type ) {
				$child_is_visible = $this->variation_is_visible( $child_product );
			} else {
				$child_is_visible = $child_product->is_visible();
			}
			if ( ! $child_is_visible ) {
				continue;
			}
			$child_prices = $this->generate_prices_for_product( $child_product );
			if ( ( 0 == $prices->price_inc_tax ) && ( $child_prices->price_inc_tax > 0 ) ) {
				$prices = $child_prices;
			} elseif ( ($child_prices->price_inc_tax > 0) && ($child_prices->price_inc_tax < $prices->price_inc_tax) ) {
				$prices = $child_prices;
			}
		}
		return $prices;
	}

	/**
	 * Set a number of optimsiations to make sure the plugin is usable on lower end setups.
	 *
	 * We stop plugins trying to cache, or compress the output since that causes everything to be
	 * held in memory and causes memory issues. We also tell WP not to add loaded objects to the
	 * cache since on setups without a persistent object store that would result in everything being
	 * in memory again.
	 */
	private function set_optimisations() {

		global $wpdb;

		// Don't cache feed under WP Super-Cache.
		define( 'DONOTCACHEPAGE', true );

		// Cater for large stores.
		$wpdb->hide_errors();
		@set_time_limit( 0 );
		while ( ob_get_level() ) {
			@ob_end_clean();
		}
	}

	/**
	 * Work out if a feed item should be excluded from the feed.
	 *
	 * @param  Object  $woocommerce_product The WooCommerce product object.
	 * @return bool                         True if the product should be excluded. False otherwise.
	 */
	private function product_is_excluded( $woocommerce_product ) {
		$excluded = false;
		// Check to see if the product is set as Hidden within WooCommerce.
		if ( 'hidden' == $woocommerce_product->visibility ) {
			$excluded = true;
		}
		// Check to see if the product has been excluded in the feed config.
		if ( $tmp_product_data = $this->get_product_meta( $woocommerce_product, 'woocommerce_gpf_data' ) ) {
			$tmp_product_data = maybe_unserialize( $tmp_product_data );
		} else {
			$tmp_product_data = array();
		}
		if ( ! empty( $tmp_product_data['exclude_product'] ) ) {
			$excluded = true;
		}
		return apply_filters( 'woocommerce_gpf_exclude_product', $excluded, $woocommerce_product->id, $this->feed_format );
	}

	/**
	 * Add additional images to the feed item.
	 *
	 * @param  Object  &$feed_item  The feed item.
	 */
	private function get_additional_images( &$feed_item ) {
		// Look for additional images.
		$feed_item->additional_images = array();
		$excluded_ids[] = get_post_meta( $feed_item->ID, '_thumbnail_id', true );

		// List product gallery images first.
		if ( apply_filters( 'woocommerce_gpf_include_product_gallery_images', true ) ) {
			$product_gallery_images = get_post_meta( $feed_item->ID, '_product_image_gallery', true );
			$product_gallery_images = explode( ',', $product_gallery_images );
			if ( is_array( $product_gallery_images ) && count( $product_gallery_images ) ) {
				foreach ( $product_gallery_images as $product_gallery_image_id ) {
					if ( in_array( $product_gallery_image_id, $excluded_ids ) ) {
						continue;
					}
					$full_image_src = wp_get_attachment_image_src( $product_gallery_image_id, $this->image_style );
					$feed_item->additional_images[] = $full_image_src[0];
					$excluded_ids[] = $product_gallery_image_id;
				}
			}
		}
		if ( apply_filters( 'woocommerce_gpf_include_attached_images', true ) ) {
			$images = get_children(
				array(
					'post_parent' => $feed_item->ID,
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'post_mime_type' => 'image',
					'order' => 'ASC',
					'orderby' => 'menu_order',
				)
			);
			if ( is_array( $images ) && count( $images ) ) {
				foreach ( $images as $image ) {
					if ( in_array( $image->ID, $excluded_ids ) ) {
						continue;
					}
					$full_image_src = wp_get_attachment_image_src( $image->ID, $this->image_style );
					$feed_item->additional_images[] = $full_image_src[0];
				}
			}
		}
	}

	/**
	 * Load the product using the relevant WC functions.
	 *
	 * @param  Object  $post  WordPress post object.
	 * @return Object         WooCommerce Product object.
	 */
	public function load_product( $post ) {
		if ( function_exists( 'wc_get_product' ) ) {
			// 2.2 compat.
			return wc_get_product( $post );
		} else if ( function_exists( 'get_product' ) ) {
			// 2.0 compat.
			return get_product( $post );
		} else {
			return new WC_Product( $post->ID );
		}
	}

	/**
	 * Render the product feed requests - calls the sub-classes according
	 * to the feed required.
	 *
	 * @access public
	 */
	public function render_product_feed() {

		global $wp_query, $post, $_wp_using_ext_object_cache;

		$this->set_optimisations();
		$this->feed->render_header();

		// Query for the products
		$chunk_size = apply_filters( 'woocommerce_gpf_chunk_size', 10 );

		$args['post_type']   = 'product';
		$args['numberposts'] = $chunk_size;
		$args['offset']      = isset( $wp_query->query_vars['gpf_start'] ) ?
		                       (int) $wp_query->query_vars['gpf_start'] :
		                       0;
		$gpf_limit           = isset( $wp_query->query_vars['gpf_limit'] ) ?
		                       (int) $wp_query->query_vars['gpf_limit'] :
		                       false;

		$output_count = 0;
		$products     = get_posts( $args );

		while ( count( $products ) ) {
			foreach ( $products as $post ) {
				if ( $this->process_product( $post ) ) {
					$output_count++;
				}
				// Quit if we've done all of the products
				if ( $gpf_limit && $output_count == $gpf_limit ) {
					break;
				}
			}
			if ( $gpf_limit && $output_count == $gpf_limit ) {
				break;
			}
			$args['offset'] += $chunk_size;

			// If we're using the built in object cache then flush it every chunk so
			// that we don't keep churning through memory.
			if ( ! $_wp_using_ext_object_cache ) {
				wp_cache_flush();
			}
			$products = get_posts( $args );
		}
		$this->feed->render_footer();
	}


	/**
	 * Process a product, outputting its information.
	 *
	 * Uses process_simple_product() to process simple products, or all products if variation
	 * support is disabled. Uses process_variable_product() to process variable products.
	 *
	 * @param  object  $post  WordPress post object.
	 * @return bool           True if one or more products were output, false otherwise.
	 */
	private function process_product($post) {
		setup_postdata( $post );
		$woocommerce_product = $this->load_product( $post );
		if ( $this->product_is_excluded( $woocommerce_product ) ) {
			return false;
		}
		if ( empty( $this->settings['include_variations'] ) ||
		     $woocommerce_product->is_type( 'simple' ) ) {
			return $this->process_simple_product( $post, $woocommerce_product );
		} elseif ( $woocommerce_product->is_type( 'variable' ) ) {
			return $this->process_variable_product( $post, $woocommerce_product );
		}
	}

	/**
	 * Process a simple product, and output its elements.
	 *
	 * @param  object  $post                 WordPress post object
	 * @param  object  $woocommerce_product  WooCommerce Product Object (May not be Simple)
	 * @return bool                          True if one or more products were output, false
	 *                                       otherwise.
	 */
	private function process_simple_product( $post, $woocommerce_product ) {
		$feed_item = new stdClass();
		$this->get_product_prices( $feed_item, $woocommerce_product );

		// Get main item information
		$feed_item->ID = $post->ID;
		$feed_item->guid = 'woocommerce_gpf_' . $post->ID;
		$feed_item->title = apply_filters(
			'woocommerce_gpf_title',
			get_the_title( $feed_item->ID ),
			$feed_item->ID
		);
		$feed_item->description = apply_filters(
			'woocommerce_gpf_description',
			apply_filters(
				'the_content',
				$post->post_content
			),
			$feed_item->ID,
			null
		);
		$feed_item->image_link          = $this->get_the_post_thumbnail_src( $feed_item->ID, $this->image_style );
		$feed_item->purchase_link       = get_permalink( $feed_item->ID );
		$feed_item->shipping_weight     = apply_filters( 'woocommerce_gpf_shipping_weight', $woocommerce_product->get_weight(), $feed_item->ID );
		$feed_item->is_in_stock         = $woocommerce_product->is_in_stock();
		$feed_item->sku                 = $woocommerce_product->get_sku();
		$feed_item->categories          = get_the_terms( $feed_item->ID, 'product_cat' );
		if ( false === $feed_item->categories ) {
			$feed_item->categories = array();
		}

		// General, or feed-specific items
		$feed_item->additional_elements = apply_filters( 'woocommerce_gpf_elements', array(), $feed_item->ID, null );
		$feed_item->additional_elements = apply_filters( 'woocommerce_gpf_elements_' . $this->feed_format, $feed_item->additional_elements, $feed_item->ID, null );

		$this->get_additional_images( $feed_item );

		// Allow other plugins to modify the item before its rendered to the feed
		$feed_item = apply_filters( 'woocommerce_gpf_feed_item', $feed_item );
		$feed_item = apply_filters( 'woocommerce_gpf_feed_item_' . $this->feed_format, $feed_item );

		return $this->feed->render_item( $feed_item );
	}

	/**
	 * Process a variable product, and output its elements.
	 *
	 * @param  object  $post                 WordPress post object
	 * @param  object  $woocommerce_product  WooCommerce Product Object
	 * @return bool                          True if one or more products were output, false
	 *                                       otherwise.
	 */
	private function process_variable_product( $post, $woocommerce_product ) {
		$success    = false;
		$variations = $woocommerce_product->get_available_variations();
		$factory    = new WC_Product_Factory();
		foreach ( $variations as $variation ) {
			$feed_item         = new stdClass();
			$variation_id      = $variation['variation_id'];
			$variation_product = $factory->get_product( $variation_id );
			if ( $this->product_is_excluded( $variation_product ) ) {
				continue;
			}
			$this->get_product_prices( $feed_item, $variation_product );
			// Get main item information
			$feed_item->ID    = $variation_id;
			$feed_item->guid  = 'woocommerce_gpf_' . $variation_id;
			$feed_item->title = $variation_product->get_title();
			$suffix           = $variation_product->get_formatted_variation_attributes(true);
			if ( ! empty( $suffix ) ) {
				$feed_item->title .= ' (' . $suffix . ')';
			}

			$feed_item->title = apply_filters(
				'woocommerce_gpf_title',
				$feed_item->title,
				$variation_id
			);
			// Use the variation description if possible, main product description if not.
			$feed_item->description = $variation_product->get_variation_description();

			if ( empty( $feed_item->description ) ) {
				$feed_item->description = $post->post_content;
			}
			$feed_item->description = apply_filters(
				'woocommerce_gpf_description',
				apply_filters(
					'the_content',
					$feed_item->description
				),
				$feed_item->ID,
				$variation_id
			);
			// Try and get the image from the variation.
			$feed_item->image_link          = $this->get_the_post_thumbnail_src( $variation_id, $this->image_style );
			if ( empty( $feed_item->image_link ) ) {
				$feed_item->image_link = $this->get_the_post_thumbnail_src( $post->ID, $this->image_style );
			}
			$feed_item->purchase_link       = $variation_product->get_permalink();
			$feed_item->shipping_weight     = apply_filters( 'woocommerce_gpf_shipping_weight', $variation_product->get_weight(), $variation_id );
			$feed_item->is_in_stock         = $variation_product->is_in_stock();
			$feed_item->sku                 = $variation_product->get_sku();
			$feed_item->categories          = get_the_terms( $post->ID, 'product_cat' );
			if ( false === $feed_item->categories ) {
				$feed_item->categories = array();
			}

			// General, or feed-specific items
			$feed_item->additional_elements = apply_filters( 'woocommerce_gpf_elements', array(), $post->ID, $variation_id );
			$feed_item->additional_elements = apply_filters( 'woocommerce_gpf_elements_' . $this->feed_format, $feed_item->additional_elements, $post->ID, $variation_id );

			$feed_item->additional_images   = array();
			// Allow other plugins to modify the item before its rendered to the feed
			$feed_item = apply_filters( 'woocommerce_gpf_feed_item', $feed_item );
			$feed_item = apply_filters( 'woocommerce_gpf_feed_item_' . $this->feed_format, $feed_item );

			$success |= $this->feed->render_item( $feed_item );
		}
		return $success;
	}


	/**
	 * Add the "advanced" information to the field based on either the per-product settings, category settings, or store defaults
	 *
	 * @access public
	 * @param array $elements    The current elements for the product.
	 * @param int $product_id    The product ID to retrieve information for.
	 * @param int $variation_id  The product variation ID to retrieve information for.
	 * @return array             The data for the product.
	 */
	public function general_elements( $elements, $product_id, $variation_id = null ) {

		global $woocommerce_gpf_common;

		// Retrieve the info set against the product by this plugin.
		$product_values   = $woocommerce_gpf_common->get_values_for_product( $product_id, $this->feed_format );
		// Merge variation values over the top if this is a variation.
		if ( $variation_id !== null ) {
			$variation_values = $woocommerce_gpf_common->get_values_for_variation( $variation_id, $this->feed_format );
			$product_values = array_merge( $product_values, $variation_values );
		}
		if ( ! empty ( $product_values ) ) {
			foreach ( $product_values as $key => $value ) {
				// Deal with fields that can have multiple, comma separated values
				if ( isset( $woocommerce_gpf_common->product_fields[ $key ]['multiple'] ) && $woocommerce_gpf_common->product_fields[ $key ]['multiple'] && ! is_array( $value ) ) {
					$value = explode( ',', $value );
				}
				$elements[ $key ] = (array) $value;
			}
		}
		return $elements;
	}

	/**
	 * Send all shipping measurements, or none.
	 *
	 * Make sure that *if* we have length, width or height, that we send all three. If we're
	 * missing any then we send none of them.
	 *
	 * @param  array  $elements   The current feed item elements.
	 * @param  int    $product_id The product to get the length of.
	 * @return array              The modified feed item elements.
	 */
	public function all_or_nothing_shipping_elements( $elements, $product_id, $variation_id = null ) {
		if ( !empty( $elements['shipping_width'] ) ||
			 !empty( $elements['shipping_length'] ) ||
			 !empty( $elements['shipping_height'] ) ) {
			if ( empty( $elements['shipping_width'] ) ||
			     empty( $elements['shipping_length'] ) ||
			     empty( $elements['shipping_height'] ) ) {
				unset( $elements['shipping_length'] );
			    unset( $elements['shipping_width'] );
			    unset( $elements['shipping_height'] );
			}
		}
		return $elements;
	}

	public function general_feed_item( $feed_item ) {
		if ( ! $feed_item->is_in_stock && empty( $feed_item->additional_elements['availability'] ) ) {
			$feed_item->additional_elements['availability'] = array( 'out of stock' );
		}
		return $feed_item;
	}

	/**
	 * Retrieve a measurement for a product in inches.
	 *
	 * @param  int     $product_id  The product ID to retrieve the measurement for.
	 * @param  string  $dimension   The dimension to retrieve. "length", "width" or "height"
	 * @return float                The requested dimension for the given product.
	 */
	private function get_shipping_dimension( $product_id, $dimension ) {
		$product = $this->load_product( $product_id );
		switch ( $dimension ) {
			case 'length':
			case 'width':
			case 'height':
				$measurement = $product->$dimension;
				break;
			default:
				return null;
		}
		if ( empty( $measurement ) ) {
			return null;
		}
		$measurement = wc_get_dimension( $measurement, 'in' );
		return $measurement;
	}

	/**
	 * Add shipping_length to the elements array if the product has a length configured.
	 *
	 * @param  array  $elements   The current feed item elements.
	 * @param  int    $product_id The product to get the length of.
	 * @return array              The modified feed item elements.
	 */
	public function shipping_length_elements( $elements, $product_id, $variation_id = null ) {
		if ( $variation_id !== null ) {
			$length = $this->get_shipping_dimension( $variation_id, 'length' );
		} else {
			$length = $this->get_shipping_dimension( $product_id, 'length' );
		}
		if ( empty( $length ) ) {
			return $elements;
		}

		$elements['shipping_length'] = array( "$length in" );
		return $elements;
	}

	/**
	 * Add shipping_width to the elements array if the product has a width configured.
	 *
	 * @param  array  $elements   The current feed item elements.
	 * @param  int    $product_id The product to get the width of.
	 * @return array              The modified feed item elements.
	 */
	public function shipping_width_elements( $elements, $product_id, $variation_id = null ) {
		if ( $variation_id !== null ) {
			$width = $this->get_shipping_dimension( $variation_id, 'width' );
		} else {
			$width = $this->get_shipping_dimension( $product_id, 'width' );
		}
		if ( empty( $width ) ) {
			return $elements;
		}
		$elements['shipping_width'] = array( "$width in" );
		return $elements;
	}

	/**
	 * Add shipping_height to the elements array if the product has a height configured.
	 *
	 * @param  array  $elements   The current feed item elements.
	 * @param  int    $product_id The product to get the height of.
	 * @return array              The modified feed item elements.
	 */
	public function shipping_height_elements( $elements, $product_id, $variation_id = null ) {
		if ( $variation_id !== null ) {
			$height = $this->get_shipping_dimension( $variation_id, 'height' );
		} else {
			$height = $this->get_shipping_dimension( $product_id, 'height' );
		}
		if ( empty( $height ) ) {
			return $elements;
		}
		$elements['shipping_height'] = array( "$height in" );
		return $elements;
	}
}

global $woocommerce_gpf_frontend;
$woocommerce_gpf_frontend = new WoocommerceGpfFrontend();
