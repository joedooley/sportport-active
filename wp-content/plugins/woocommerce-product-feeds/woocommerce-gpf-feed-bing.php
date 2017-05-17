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
		$this->store_info->feed_url = add_query_arg( 'woocommerce_gpf', 'bing', $this->store_info->feed_url_base );
		// Bing doesn't like forreign chars
		$this->old_locale = get_locale();
	}

	/**
	 * Determine if prices should include, or exclude taxes.
	 *
	 * Country list from: https://help.bingads.microsoft.com/#apex/3/en/56731/1
	 */
	private function include_tax() {
		if ( in_array(
			$this->store_info->base_country,
			array( 'GB', 'AU', 'DE', 'FR' )
		) ) {
			return true;
		}
		return false;
	}

	/**
	 * Render the feed header information
	 *
	 * @access public
	 */
	public function render_header() {

		// Bing doesn't like foreign chars
		setlocale( LC_CTYPE, 'en_US.UTF-8' );

		if ( isset( $_REQUEST['feeddownload'] ) ) {
			header( 'Content-Disposition: attachment; filename="E-Commerce_Product_List.txt"' );
		} else {
			header( 'Content-Disposition: inline; filename="E-Commerce_Product_List.txt"' );
		}
		header( 'Content-Type: text/csv' );

		// Mandatory fields
		echo "id\ttitle\tlink\tprice\tdescription\timage_link";

		// Optional fields
		if ( isset( $this->settings['product_fields']['bing_category'] ) ) {
			echo "\tproduct_category";
		}
		if ( isset( $this->settings['product_fields']['brand'] ) ) {
			echo "\tbrand";
		}
		if ( isset( $this->settings['product_fields']['mpn'] ) ) {
			echo "\tmpn";
		}
		if ( isset( $this->settings['product_fields']['gtin'] ) ) {
			echo "\tgtin";
		}
		if ( isset( $this->settings['product_fields']['availability'] ) ) {
			echo "\tavailability";
		}
		if ( isset( $this->settings['product_fields']['condition'] ) ) {
			echo "\tCondition";
		}
		if ( isset( $this->settings['product_fields']['custom_label_0'] ) ) {
			echo "\tcustom_label_0";
		}
		if ( isset( $this->settings['product_fields']['custom_label_1'] ) ) {
			echo "\tcustom_label_1";
		}
		if ( isset( $this->settings['product_fields']['custom_label_2'] ) ) {
			echo "\tcustom_label_2";
		}
		if ( isset( $this->settings['product_fields']['custom_label_3'] ) ) {
			echo "\tcustom_label_3";
		}
		if ( isset( $this->settings['product_fields']['custom_label_4'] ) ) {
			echo "\tcustom_label_4";
		}
		echo "\r\n";

	}

	/**
	 * Helper function used to output a value in a warnings-safe way
	 *
	 * @access public
	 * @param  object $feed_item The information about the item
	 * @param  string $key       The particular attribute to output
	 *
	 * @return                   The output for this element.
	 */
	private function output_element( &$feed_item, $key ) {
		$output = '';
		if ( isset( $this->settings['product_fields'][ $key ] ) ) {
			if ( isset( $feed_item->additional_elements[ $key ] ) ) {
				foreach ( $feed_item->additional_elements[ $key ] as $data ) {
					$output .= "\t" . $this->tsvescape( $data );
				}
			} else {
				$output .= "\t";
			}
		}
		return $output;
	}

	/**
	 * Generate the output for an individual item
	 *
	 * @access public
	 * @param  object $feed_item The information about the item
	 */
	function render_item( $feed_item ) {

		if ( empty( $feed_item->price_inc_tax ) ) {
			return '';
		}

		$output = '';
		// id
		$output .= $this->tsvescape( $feed_item->guid ) . "\t";

		// title
		$output .= $this->tsvescape( substr( $feed_item->title, 0, 255 ) ) . "\t";

		// link
		$output .= $this->tsvescape( $feed_item->purchase_link ) . "\t";

		// price
		if ( $this->include_tax() ) {
			$price = number_format( $feed_item->price_inc_tax, 2, '.', '' );
		} else {
			$price = number_format( $feed_item->price_ex_tax, 2, '.', '' );
		}
		$output .= $this->tsvescape( $price )."\t";

		// description
		// Bing doesn't allow HTML in descriptions.
		$description = wp_filter_nohtml_kses( $feed_item->description );
		$description = substr( $description, 0, 5000 );
		$output .= $this->tsvescape( $description ) . "\t";

		// image_link
		if ( ! empty( $feed_item->image_link ) ) {
			$output .= $this->tsvescape( $feed_item->image_link );
		}

		$output .= $this->output_element( $feed_item, 'bing_category' );
		$output .= $this->output_element( $feed_item, 'brand' );
		$output .= $this->output_element( $feed_item, 'mpn' );
		$output .= $this->output_element( $feed_item, 'gtin' );

		if ( isset( $this->settings['product_fields']['availability'] ) ) {
			if ( $feed_item->is_in_stock ) {
				if ( isset( $feed_item->additional_elements['availability'][0] ) ) {
					//  Out of Stock; Pre-Order; Back-Order
					switch ( $feed_item->additional_elements['availability'][0] ) {
						case 'out of stock':
							$output .= "\tOut Of Stock";
							break;
						case 'preorder':
							$output .= "\tPre-Order";
							break;
						case 'available for order':
							$output .= "\tBack-Order";
							break;
						case 'in stock':
							$output .= "\tIn Stock";
							break;
						default:
							$output .= "\tIn Stock";
							break;
					}
				} else {
					$output .= "\tIn Stock";
				}
			} else {
				$output .= "\tOut Of Stock";
			}
		}

		if ( isset( $this->settings['product_fields']['condition'] ) ) {
			if ( isset( $feed_item->additional_elements['condition'][0] ) ) {
				switch ( $feed_item->additional_elements['condition'][0] ) {
					case 'new':
						$output .= "\t" . $this->tsvescape( 'New' );
						break;
					case 'refurbished':
						$output .= "\t" . $this->tsvescape( 'Refurbished' );
						break;
					case 'used':
						$output .= "\t" . $this->tsvescape( 'Used' );
						break;
				}
			} else {
				$output .= "\t";
			}
		}

		$output .= $this->output_element( $feed_item, 'custom_label_0' );
		$output .= $this->output_element( $feed_item, 'custom_label_1' );
		$output .= $this->output_element( $feed_item, 'custom_label_2' );
		$output .= $this->output_element( $feed_item, 'custom_label_3' );
		$output .= $this->output_element( $feed_item, 'custom_label_4' );

		$output .=  "\r\n";
		return $output;
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
