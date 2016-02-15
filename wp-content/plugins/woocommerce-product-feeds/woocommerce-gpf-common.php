<?php

/**
 * Common class.
 *
 * Holds the config about what fields are available.
 */
class WoocommerceGpfCommon {

	private $settings = array();
	private $category_cache = array();
	public $product_fields = array();

	/**
	 * Constructor - set up the available product fields
	 *
	 * @access public
	 */
	function __construct() {

		$this->settings = get_option( 'woocommerce_gpf_config' );
		$this->product_fields = array(

			'availability' => array(
				'desc'        => __( 'Availability', 'woocommerce_gpf' ),
				'full_desc'   => __( 'What status to send for in stock items. Out of stock products will always show as "Out of stock" irrespective of this setting.', 'woocommerce_gpf' ),
				'callback'    => 'render_availability',
				'can_default' => true,
				'feed_types'  => array( 'google', 'googleinventory', 'bing' ),
			),

			'availability_date' => array(
				'desc'        => __( 'Availability date', 'woocommerce_gpf' ),
				'full_desc'   => __( 'If you are accepting orders for products that are available for preorder, use this attribute to indicate when the product becomes available for delivery.', 'woocommerce_gpf' ),
				'callback'    => 'render_availability_date',
				'feed_types'  => array( 'google' ),
			 ),

			'condition' => array(
				'desc'        => __( 'Condition', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Condition or state of items', 'woocommerce_gpf' ),
				'callback'    => 'render_condition',
				'can_default' => true,
				'feed_types'  => array( 'google', 'bing' ),
			 ),

			'brand' => array(
				'desc'        => __( 'Brand', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Brand of the items', 'woocommerce_gpf' ),
				'can_default' => true,
				'feed_types'  => array( 'google', 'bing' ),
				'google_len'  => 70,
			),

			'mpn' => array(
				'desc'        => __( 'Manufacturer Part Number (MPN)', 'woocommerce_gpf' ),
				'full_desc'   => __( 'This code uniquely identifies the product to its manufacturer', 'woocommerce_gpf' ),
				'feed_types'  => array( 'google', 'bing' ),
				'google_len'  => 70,
			),

			'product_type' => array(
				'desc'        => __( 'Product Type', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Your category of the items', 'woocommerce_gpf' ),
				'callback'    => 'render_product_type',
				'can_default' => true,
				'feed_types'  => array( 'google' ),
				'google_len'  => 750,
			),

			'google_product_category' => array(
				'desc'        => __( 'Google Product Category', 'woocommerce_gpf' ),
				'full_desc'   => __( "Google's category of the item", 'woocommerce_gpf' ),
				'callback'    => 'render_product_type',
				'can_default' => true,
				'feed_types'  => array( 'google' ),
				'google_len'  => 750,
			),

			'gtin' => array(
				'desc'        => __( 'Global Trade Item Number (GTIN)', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Global Trade Item Numbers (GTINs) for your items. These identifiers include UPC (in North America), EAN (in Europe), JAN (in Japan), and ISBN (for books)', 'woocommerce_gpf' ),
				'feed_types'  => array( 'google' ),
				'google_len' => 50,
			),

			'gender' => array(
				'desc'        => __( 'Gender', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Target gender for the item', 'woocommerce_gpf' ),
				'callback'    => 'render_gender',
				'can_default' => true,
				'feed_types'  => array( 'google' ),
			),

			'age_group' => array(
				'desc'        => __( 'Age Group', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Target age group for the item', 'woocommerce_gpf' ),
				'callback'    => 'render_age_group',
				'can_default' => true,
				'feed_types'  => array( 'google' ),
			),

			'color' => array(
				'desc'        => __( 'Colour', 'woocommerce_gpf' ),
				'full_desc'   => __( "Item's Colour", 'woocommerce_gpf' ),
				'feed_types'  => array( 'google' ),
				'google_len'  => 100,
			),

			'size' => array(
				'desc'       => __( 'Size', 'woocommerce_gpf' ),
				'full_desc'  => __( 'Size of the items', 'woocommerce_gpf' ),
				'feed_types' => array( 'google' ),
				'google_len' => 100,
			),

			'size_type' => array(
				'desc'        => __( 'Size type', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Size type of the items', 'woocommerce_gpf' ),
				'feed_types'  => array( 'google' ),
				'can_default' => true,
				'callback'    => 'render_size_type',
			),

			'size_system' => array(
				'desc'        => __( 'Size system', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Size system', 'woocommerce_gpf' ),
				'feed_types'  => array( 'google' ),
				'can_default' => true,
				'callback'    => 'render_size_system',
			),

			'identifier_exists' => array(
				'desc'        => __( 'Identifier exists flag', 'woocommerce_gpf' ),
				'full_desc'   => __( "Whether to include 'Identifier exists - false' when products don't have the relevant identifiers", 'woocommerce_gpf' ),
				'callback'    => 'render_i_exists',
				'can_default' => true,
				'feed_types'  => array( 'google' ),
			),

			'adwords_grouping' => array(
				'desc'        => __( 'Adwords grouping filter', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Used to group products in an arbitrary way. It can be used for Product Filters to limit a campaign to a group of products or Product Targets, to bid differently for a group of products. This is a required field if the advertiser wants to bid differently to different sub-sets of products in the CPC or CPA % version. It can only hold one value.', 'woocommerce_gpf' ),
				'can_default' => true,
				'feed_types'  => array( 'google' ),
			),

			'adwords_labels' => array(
				'desc'        => __( 'Adwords labels', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Very similar to adwords_grouping, but it will only work on CPC. You can enter multiple values here, separating them with a comma (,). e.g. "widget,box".', 'woocommerce_gpf' ),
				'can_default' => true,
				'feed_types'  => array( 'google' ),
				'multiple'    => true,
			),

			'bing_category' => array(
				'desc'        => __( 'Bing Category', 'woocommerce_gpf' ),
				'full_desc'   => __( "Bing's category of the item", 'woocommerce_gpf' ),
				'callback'    => 'render_b_category',
				'can_default' => true,
				'feed_types'  => array( 'bing' ),
			),

			'upc' => array(
				'desc'       => __( 'Universal Product Code', 'woocommerce_gpf' ),
				'full_desc'  => __( 'Universal Product Code. Only 8 and 12 digit codes are supported.', 'woocommerce_gpf' ),
				'feed_types' => array( 'bing' ),
			),

			'isbn' => array(
				'desc'       => __( 'International Standard Book Number', 'woocommerce_gpf' ),
				'full_desc'  => __( "10 or 13 digit ISBNs. The ISBN is matched to other offers with the identical ISBN - significantly improving your customer's ability to locate your product. Use for books, CDs, DVD.", 'woocommerce_gpf' ),
				'feed_types' => array( 'bing' ),
			),

			'delivery_label' => array(
				'desc'        => __( 'Delivery label', 'woocommerce_gpf' ),
				'full_desc'   => __( 'You can use this to control which shipping rules from your Merchant Centre account are applied to this product.', 'woocommerce_gpf' ),
				'can_default' => true,
				'callback'    => 'render_textfield',
				'feed_types'  => array( 'google' ),
				'google_len'  => 100,
			),

			'custom_label_0' => array(
				'desc'        => __( 'Custom label 0', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Can be used to segment your products when setting up shopping campaigns in Adwords.', 'woocommerce_gpf' ),
				'can_default' => true,
				'callback'    => 'render_textfield',
				'feed_types'  => array( 'google' ),
				'google_len'  => 100,
			),

			'custom_label_1' => array(
				'desc'        => __( 'Custom label 1', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Can be used to segment your products when setting up shopping campaigns in Adwords.', 'woocommerce_gpf' ),
				'can_default' => true,
				'callback'    => 'render_textfield',
				'feed_types'  => array( 'google' ),
				'google_len'  => 100,
			),

			'custom_label_2' => array(
				'desc'        => __( 'Custom label 2', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Can be used to segment your products when setting up shopping campaigns in Adwords.', 'woocommerce_gpf' ),
				'can_default' => true,
				'callback'    => 'render_textfield',
				'feed_types'  => array( 'google' ),
				'google_len'  => 100,
			),

			'custom_label_3' => array(
				'desc'        => __( 'Custom label 3', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Can be used to segment your products when setting up shopping campaigns in Adwords.', 'woocommerce_gpf' ),
				'can_default' => true,
				'callback'    => 'render_textfield',
				'feed_types'  => array( 'google' ),
				'google_len'  => 100,
			),

			'custom_label_4' => array(
				'desc'        => __( 'Custom label 4', 'woocommerce_gpf' ),
				'full_desc'   => __( 'Can be used to segment your products when setting up shopping campaigns in Adwords.', 'woocommerce_gpf' ),
				'can_default' => true,
				'callback'    => 'render_textfield',
				'feed_types'  => array( 'google' ),
				'google_len'  => 100,
			),

			'promotion_id' => array(
				'desc'        => __( 'Promotion ID', 'woocommerce_gpf' ),
				'full_desc'   => __( 'The unique ID of a promotion.' ),
				'can_default' => true,
				'callback'    => 'render_textfield',
				'feed_types'  => array( 'google' ),
			),

		);

		$this->product_fields = apply_filters( 'woocommerce_gpf_all_product_fields', $this->product_fields );
	}



	/**
	 * Helper function to remove blank array elements
	 *
	 * @access public
	 * @param array $array The array of elements to filter
	 * @return array The array with blank elements removed
	 */
	private function remove_blanks( $array ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return $array;
		}
		foreach ( array_keys( $array ) as $key ) {
			if ( empty( $array[ $key ] ) || empty( $this->settings['product_fields'][ $key ] ) ) {
				unset( $array[ $key ] );
			}
		}
		return $array;
	}



	/**
	 * Helper function to remove items not needed in this feed type
	 *
	 * @access public
	 * @param array $array The list of fields to be filtered
	 * @param string $feed_format The feed format that should have its fields maintained
	 * @return array The list of fields filtered to only contain elements that apply to the selectedd $feed_format
	 */
	private function remove_other_feeds( $array, $feed_format ) {
		if ( empty( $array ) || ! is_array( $array ) ) {
			return $array;
		}
		foreach ( array_keys( $array ) as $key ) {
			if ( empty( $this->product_fields[ $key ] ) || ! in_array( $feed_format, $this->product_fields[ $key ]['feed_types'] ) ) {
				unset ( $array[ $key ] );
			}
		}
		return $array;
	}



	/**
	 * Retrieve the values that should be output for a particular product
	 * Takes into account store defaults, category defaults, and per-product
	 * settings
	 *
	 * @access public
	 * @param  int  $product_id       The ID of the product to retrieve info for
	 * @param  string  $feed_format   The feed format being generated
	 * @param  boolean $defaults_only Whether to retrieve the
							*         store/category defaults only
	 * @return array                  The values for the product
	 */
	public function get_values_for_product( $product_id = null, $feed_format = 'all', $defaults_only = false ) {

		if ( ! $product_id ) {
			return false;
		}

		// Get Store defaults
		if ( ! isset($this->settings['product_defaults']) ) {
			$this->settings['product_defaults'] = array();
		}
		$settings = $this->remove_blanks( $this->settings['product_defaults'] );

		// Merge category settings
		$categories = wp_get_object_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

		foreach ( $categories as $category_id ) {
			$category_settings = $this->get_values_for_category( $category_id );
			$category_settings = $this->remove_blanks( $category_settings );
			if ( 'all' != $feed_format ) {
				$category_settings = $this->remove_other_feeds( $category_settings, $feed_format );
			}
			if ( $category_settings ) {
				$settings = array_merge( $settings, $category_settings );
			}
		}

		if ( $defaults_only ) {
			return $settings;
		}

		// Merge product settings
		$product_settings = get_post_meta( $product_id, '_woocommerce_gpf_data', true );
		if ( $product_settings ) {
			$product_settings = $this->remove_blanks( $product_settings );
			$settings = array_merge( $settings, $product_settings );
		}

		if ( 'all' != $feed_format ) {
			$settings = $this->remove_other_feeds( $settings, $feed_format );
		}

		return $settings;
	}



	/**
	 * Retrieve category defaults for a specific category
	 *
	 * @access public
	 * @param  int $category_id The category ID to retrieve information for
	 * @return array            The category data
	 */
	private function get_values_for_category( $category_id ) {
		if ( ! $category_id ) {
			return false;
		}
		if ( isset ( $this->category_cache[ $category_id ] ) ) {
			return $this->category_cache[ $category_id ];
		}
		$values = get_metadata( 'woocommerce_term', $category_id, '_woocommerce_gpf_data', true );
		$this->category_cache[ $category_id ] = &$values;
		return $this->category_cache[ $category_id ];

	}

}

global $woocommerce_gpf_common;
$woocommerce_gpf_common = new WoocommerceGpfCommon();
