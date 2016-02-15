<?php
/**
 * woocommerce-gpf-feed-bing.php
 *
 * @package default
 */
class WoocommerceGpfFeedBing extends WoocommerceGpfFeed {

	private $old_locale = 'en_US';

	/**
	 * Constructor. Grab the settings, and add filters if we have stuff to do
	 *
	 * @access public
	 */
	function __construct() {
		parent::__construct();
		$this->store_info->feed_url = add_query_arg( 'feed_format', 'bing', $this->store_info->feed_url_base );
		// Bing doesn't like forreign chars
		$this->old_locale = get_locale();
	}


	/**
	 * Render the feed header information
	 *
	 * @access public
	 */
	public function render_header() {

		// Bing doesn't like foreign chars
		setlocale( LC_CTYPE, 'en_US.UTF-8' );

		if ( isset ( $_REQUEST['feeddownload'] ) ) {
			header( 'Content-Disposition: attachment; filename="E-Commerce_Product_List.txt"' );
		} else {
			header( 'Content-Disposition: inline; filename="E-Commerce_Product_List.txt"' );
		}

		// Mandatory fields
		echo "MPID\tTitle\tProductURL\tPrice\tDescription\tImageURL\tMerchantCategory\tSKU\tShippingWeight";

		// Optional fields
		if ( isset ( $this->settings['product_fields']['brand'] ) ) {
			echo "\tBrand";
		}
		if ( isset ( $this->settings['product_fields']['mpn'] ) ) {
			echo "\tMPN";
		}
		if ( isset ( $this->settings['product_fields']['upc'] ) ) {
			echo "\tUPC";
		}
		if ( isset ( $this->settings['product_fields']['isbn'] ) ) {
			echo "\tISBN";
		}
		if ( isset ( $this->settings['product_fields']['availability'] ) ) {
			echo "\tAvailability";
		}
		if ( isset ( $this->settings['product_fields']['bing_category'] ) ) {
			echo "\tB_Category";
		}
		if ( isset ( $this->settings['product_fields']['condition'] ) ) {
			echo "\tCondition";
		}
		echo "\r\n";

	}



	/**
	 * Helper function used to output a value in a warnings-safe way
	 *
	 * @access public
	 * @param  object $feed_item The information about the item
	 * @param  string $key       The particular attribute to output
	 */
	private function output_element( &$feed_item, $key ) {
		if ( isset( $this->settings['product_fields'][ $key ] ) ) {
			if ( isset( $feed_item->additional_elements[ $key ] ) ) {
				foreach ( $feed_item->additional_elements[ $key ] as $data ) {
					echo "\t" . $this->tsvescape( $data );
				}
			} else {
				echo "\t";
			}
		}
	}



	/**
	 * Generate the output for an individual item
	 *
	 * @access public
	 * @param  object $feed_item The information about the item
	 */
	function render_item( $feed_item ) {

		if ( empty ( $feed_item->price_inc_tax ) ) {
			return false;
		}

		// MPID
		echo 'woocommerce_gpf_' . $feed_item->ID . "\t";

		// Title
		echo $this->tsvescape( substr( $feed_item->title, 0, 255 ) ) . "\t";

		// ProductURL
		echo $this->tsvescape( $feed_item->purchase_link ) . "\t";

		// Price
		$price = number_format( $feed_item->price_ex_tax, 2, '.', '' );
		echo $this->tsvescape( $price )."\t";

		// Description - Bing doesn't allow HTML in descriptions
		$description = wp_filter_nohtml_kses( $feed_item->description );
		$description = substr( $description, 0, 5000 );
		echo $this->tsvescape( $description ) . "\t";

		// ImageURL
		if ( ! empty ( $feed_item->image_link ) ) {
			echo $this->tsvescape( $feed_item->image_link )."\t";
		} else {
			echo "\t";
		}

		// MerchantCategory
		if ( count( $feed_item->categories ) ) {
			// Get the hierarchy of the first category
			$category = $feed_item->categories[0];
			$hierarchy = get_ancestors( $category->term_id, 'product_cat' );
			$hierarchy = array_reverse( $hierarchy );
			$hierarchy[] = $category->term_id;
			foreach ( $hierarchy as $cat ) {
				$term = get_term( $cat, 'product_cat' );
				$merchant_categories[] = $term->name;
			}
			echo $this->tsvescape( implode( ' > ', $merchant_categories ) ) . "\t";
		} else {
			echo "\t";
		}

		// SKU
		if ( ! empty ( $feed_item->sku ) ) {
			echo $this->tsvescape( $feed_item->sku ) . "\t";
		} else {
			echo "\t";
		}

		// ShippingWeight - NOTE NO TRAILING TAB
		if ( $feed_item->shipping_weight ) {
			if ( 'lbs' == $this->store_info->weight_units ) {
				echo $this->tsvescape( $feed_item->shipping_weight );
			} else {
				// Convert and output
				$weight = woocommerce_get_weight( $feed_item->shipping_weight, 'lbs' );
				echo $this->tsvescape( $weight );
			}
		}

		$this->output_element( $feed_item, 'brand' );
		$this->output_element( $feed_item, 'mpn' );
		$this->output_element( $feed_item, 'upc' );
		$this->output_element( $feed_item, 'isbn' );

		if ( isset( $this->settings['product_fields']['availability'] ) ) {
			if ( $feed_item->is_in_stock ) {
				if ( isset( $feed_item->additional_elements['availability'][0] ) ) {
					//  Out of Stock; Pre-Order; Back-Order
					switch ( $feed_item->additional_elements['availability'][0] ) {
						case 'out of stock':
							echo "\tOut Of Stock";
							break;
						case 'preorder':
							echo "\tPre-Order";
							break;
						case 'available for order':
							echo "\tBack-Order";
							break;
						case 'in stock':
							echo "\tIn Stock";
							break;
						default:
							echo "\tIn Stock";
							break;
					}
				} else {
					echo "\tIn Stock";
				}
			} else {
				echo "\tOut Of Stock";
			}
		}

		$this->output_element( $feed_item, 'bing_category' );

		if ( isset( $this->settings['product_fields']['condition'] ) ) {
			if ( isset( $feed_item->additional_elements['condition'][0] ) ) {
				switch ( $feed_item->additional_elements['condition'][0] ) {
					case 'new':
						echo "\t" . $this->tsvescape( 'New' );
						break;
					case 'refurbished':
						echo "\t" . $this->tsvescape( 'Refurbished' );
						break;
					case 'used':
						echo "\t" . $this->tsvescape( 'Used' );
						break;
				}
			} else {
				echo "\t";
			}
		}
		echo "\r\n";
		return true;
	}

	/**
	 * Output the feed footer
	 *
	 * @access public
	 */
	public function render_footer() {
		// Restore original locale - for completeness if anything else ever happens here.
		setlocale( LC_CTYPE, $this->old_locale );
		exit();
	}
}
