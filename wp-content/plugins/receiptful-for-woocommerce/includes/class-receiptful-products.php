<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Receiptful_Products.
 *
 * Class to sync products with the Receiptful API.
 * Products are synchronised with Receiptful to give customers
 * really good 'similar products' recommendations.
 *
 * @class		Receiptful_Products
 * @version		1.0.0
 * @author		Receiptful
 * @since		1.1.1
 */
class Receiptful_Products {


	/**
	 * Constructor.
	 *
	 * @since 1.1.1
	 */
	public function __construct() {

		// Create/Update product
		add_action( 'publish_post', array( $this, 'update_product' ), 20 );
		add_action( 'save_post', array( $this, 'update_product' ), 20 );

		// Trash product
		add_action( 'trash_product', array( $this, 'delete_product' ), 10, 2 );

		// Update product on sale price start/expire
		add_action( 'woocommerce_scheduled_sales', array( $this, 'update_product_sale_change' ), 9 );

	}


	/**
	 * Update product.
	 *
	 * Update a product when its being saved/published. When a download gets
	 * updated, the data will be send to Receiptful to keep the data synced.
	 *
	 * @since 1.1.1
	 *
	 * @param 	int 			$post_id 	ID of the post currently being saved.
	 * @return	array|WP_Error				Returns the API response, or WP_Error when API call fails.
	 */
	public function update_product( $post_id ) {

		// Bail if its not a product / if its trashed
		if ( 'product' !== get_post_type( $post_id ) || 'trash' == get_post_status( $post_id ) ) {
			return;
		}

		$args 		= $this->get_formatted_product( $post_id );
		$response 	= Receiptful()->api->update_product( $post_id, $args );

		if ( is_wp_error( $response ) || in_array( $response['response']['code'], array( '401', '500', '503' ) ) ) {
			$queue 							= get_option( '_receiptful_queue', array() );
			$queue['products'][ $post_id ] 	= array( 'id' => $post_id, 'action' => 'update' );
			update_option( '_receiptful_queue', $queue );
		} elseif ( in_array( $response['response']['code'], array( '200' ) ) ) {
			update_post_meta( $post_id, '_receiptful_last_update', time() );
		}

		return $response;

	}


	/**
	 * Update products.
	 *
	 * Update multiple products at once. A product update
	 * will send data to Receiptful to keep in sync.
	 *
	 * @since 1.1.1
	 *
	 * @param 	array 			$product_ids 	List of product IDs to sync with Receiptful.
	 * @return	array|WP_Error					Returns the API response, or WP_Error when API call fails.
	 */
	public function update_products( $product_ids = array() ) {

		// Prepare product args
		$args = array();
		foreach ( $product_ids as $product_id ) {
			$args[] = $this->get_formatted_product( $product_id );
		}

		// Update products
		$response = Receiptful()->api->update_products( array_values( array_filter( $args ) ) );

		// Process response
		if ( is_wp_error( $response ) || in_array( $response['response']['code'], array( '401', '500', '503' ) ) ) {

			$queue = get_option( '_receiptful_queue', array() );
			foreach ( $product_ids as $product_id ) {
				$queue['products'][ $product_id ] = array( 'id' => $product_id, 'action' => 'update' );
			}
			update_option( '_receiptful_queue', $queue );

		} elseif ( in_array( $response['response']['code'], array( '200', '202' ) ) ) {

			$failed_ids = array();
			$body 		= json_decode( $response['body'], 1 );
			foreach ( $body['errors'] as $error ) {
				$failed_ids[] = isset( $error['error']['product_id'] ) ? $error['error']['product_id'] : null;
			}

			foreach ( $product_ids as $product_id ) {
				if ( ! in_array( $product_id, $failed_ids ) ) {
					update_post_meta( $product_id, '_receiptful_last_update', time() );
				}
			}

		}

		return $response;

	}


	/**
	 * Formatted product.
	 *
	 * Get the formatted product arguments for the Receiptful API
	 * to update the product.
	 *
	 * @since 1.1.1
	 *
	 * @param	int		$product_id	ID of the product to update.
	 * @return	array				Formatted array according Receiptful standards with product data.
	 */
	public function get_formatted_product( $product_id ) {

		// Bail if the ID is not a product ID
		if ( 'product' != get_post_type( $product_id ) ) {
			return;
		}

		// Don't update auto drafts
		if ( 'auto-draft' == get_post_status( $product_id ) ) {
			return;
		}

		$product 	= wc_get_product( $product_id );
		$images 	= $this->get_formatted_images( $product->id );
		$categories	= $this->get_formatted_categories( $product->id );
		$tags		= wp_get_post_terms( $product->id, 'product_tag', array( 'fields' => 'names' ) );
		$variants	= $this->get_formatted_variants( $product->id );

		if ( 'publish' != $product->post->post_status ) :
			$hidden = true;
		elseif ( 'hidden' == get_post_meta( $product_id, '_visibility', true ) ) :
			$hidden = true;
		elseif ( ! empty( $product->post->post_password ) ) :
			$hidden = true;
		elseif ( 'outofstock' === $product->stock_status ) :
			$hidden = true;
		else :
			$hidden = false;
		endif;

		$args = apply_filters( 'receiptful_update_product_args', array(
			'product_id'	=> (string) $product->id,
			'title'			=> $product->get_title(),
			'description'	=> strip_shortcodes( $product->post->post_content ),
			'hidden'		=> $hidden,
			'url'			=> get_permalink( $product->id ),
			'images'		=> $images,
			'tags'			=> $tags,
			'categories'	=> $categories,
			'variants'		=> $variants,
		), $product->id );

		return $args;

	}


	/**
	 * Formatted categories.
	 *
	 * Get the formatted categories array. The return values
	 * will be according the Receiptful API endpoint specs.
	 *
	 * @since 1.1.1
	 *
	 * @param 	int 	$product_id 	ID of the product currently processing.
	 * @return 	array					List of product categories formatted according Receiptful specs.
	 */
	public function get_formatted_categories( $product_id ) {

		$categories 	= array();
		$product_cats	= wp_get_post_terms( $product_id, 'product_cat' );

		if ( $product_cats ) {
			foreach ( $product_cats as $category ) {
				$categories[] = array(
					'category_id'	=> (string) $category->term_id,
					'title'			=> $category->name,
					'description'	=> $category->description,
					'url'			=> get_term_link( $category->term_id, 'product_cat' ),
				);
			}
		}

		return $categories;

	}


	/**
	 * Formatted images.
	 *
	 * Get the formatted images array. The return value
	 * will be according the Receiptful API endpoint specs.
	 *
	 * This method gets the featured image + all the gallery images.
	 *
	 * @since 1.1.1
	 *
	 * @param 	int 	$product_id 	ID of the product currently processing.
	 * @return 	array					List of product images formatted according Receiptful specs.
	 */
	public function get_formatted_images( $product_id ) {

		$images 		= array();
		$product 		= wc_get_product( $product_id );
		$featured_id	= $product->get_image_id();
		$image_ids 		= $product->get_gallery_attachment_ids();

		// Featured image
		if ( ! empty( $featured_id ) && 0 !== $featured_id && wp_get_attachment_url( $featured_id ) ) {
			$images[] = array(
				'position' 	=> count( $images ),
				'url'		=> wp_get_attachment_url( $featured_id ),
			);
		}

		// Image gallery
		if ( $image_ids ) {
			foreach ( $image_ids as $image_id ) {

				if ( wp_get_attachment_url( $image_id ) ) {
					$images[] = array(
						'position' 	=> count( $images ),
						'url'		=> wp_get_attachment_url( $image_id ),
					);
				}

			}
		}

		return $images;

	}


	/**
	 * Formatted variants.
	 *
	 * Get the formatted variants array. Variants in Receiptful
	 * are the prices.
	 *
	 * @since 1.1.1
	 *
	 * @param 	int 	$product_id 	ID of the product currently processing.
	 * @return 	array					List of product prices formatted according Receiptful specs.
	 */
	public function get_formatted_variants( $product_id )  {

		$variants 	= array();
		$product	= wc_get_product( $product_id );

		if ( 'variable' == $product->product_type ) {

			foreach ( $product->get_available_variations() as $key => $variation ) {

				if ( ! $variation['is_purchasable'] ) {
					continue;
				}

				$variants[] = array(
					'price'	=> (float) number_format( (float) $variation['display_price'], 2, '.', '' ),
				);

			}

		} elseif ( null != $product->get_price() ) {
			$variants[] = array(
				'price'	=> (float) number_format( (float) $product->get_price(), 2, '.', '' ),
			);
		}

		return $variants;

	}


	/**
	 * Delete product.
	 *
	 * Delete the product from Receiptful when its deleted in the shop.
	 *
	 * @since 1.1.1
	 *
	 * @param 	int 			$post_id 	ID of the post (product) currently being deleted.
	 * @param 	WP_Post			$post 		WP_Post object containing post data.
	 * @return	array|WP_Error				Returns the API response, or WP_Error when API call fails.
	 */
	public function delete_product( $post_id, $post = '' ) {

		// Bail if its not a product
		if ( 'product' !== get_post_type( $post_id ) ) {
			return;
		}

		$response = Receiptful()->api->delete_product( $post_id );

		if ( is_wp_error( $response ) || in_array( $response['response']['code'], array( '401', '500', '503' ) ) ) {
			$queue 							= get_option( '_receiptful_queue', array() );
			$queue['products'][ $post_id ] 	= array( 'id' => $post_id, 'action' => 'delete' );
			update_option( '_receiptful_queue', $queue );
		}

		return $response;

	}


	/**
	 * Processes product queue.
	 *
	 * Process the products that are in the queue.
	 *
	 * @since 1.1.1
	 * @since 1.1.12 - Limit queue processing up to 225 products per batch
	 */
	public function process_queue() {

		$queue = get_option( '_receiptful_queue', array() );

		// Process products
		if ( isset( $queue['products'] ) && is_array( $queue['products'] ) ) {
			foreach ( array_slice( $queue['products'], 0, 225, true ) as $key => $product ) {

				if ( 'delete' == $product['action'] ) {
					$response = $this->delete_product( $product['id'] );
				} else {
					$response = $this->update_product( $product['id'] );
				}

				if ( ! is_wp_error( $response ) && in_array( $response['response']['code'], array( '200', '204', '400', '404' ) ) ) { // Unset from queue when appropiate
					unset( $queue['products'][ $key ] );
				}

			}
		}

		update_option( '_receiptful_queue', $queue );

	}


	/**
	 * Update on sale change.
	 *
	 * Send a product update to Receiptful when a product goes
	 * into or out of a sale period (automatically via a event).
	 *
	 * @since 1.1.11
	 */
	public function update_product_sale_change() {

		global $wpdb;

		// Sales which are due to start
		$start_sale_product_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->postmeta} as postmeta_2 ON postmeta.post_id = postmeta_2.post_id
			LEFT JOIN {$wpdb->postmeta} as postmeta_3 ON postmeta.post_id = postmeta_3.post_id
			WHERE postmeta.meta_key = '_sale_price_dates_from'
			AND postmeta_2.meta_key = '_price'
			AND postmeta_3.meta_key = '_sale_price'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND postmeta_2.meta_value != postmeta_3.meta_value
		", current_time( 'timestamp' ) ) );

		// Sales which are due to end
		$end_sale_product_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->postmeta} as postmeta_2 ON postmeta.post_id = postmeta_2.post_id
			LEFT JOIN {$wpdb->postmeta} as postmeta_3 ON postmeta.post_id = postmeta_3.post_id
			WHERE postmeta.meta_key = '_sale_price_dates_to'
			AND postmeta_2.meta_key = '_price'
			AND postmeta_3.meta_key = '_regular_price'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND postmeta_2.meta_value != postmeta_3.meta_value
		", current_time( 'timestamp' ) ) );

		// Process the WooCommerce function manually
		if ( function_exists( 'woocommerce_scheduled_sales' ) ) {
			wc_scheduled_sales();
		}

		if ( $start_sale_product_ids ) {
			foreach ( $start_sale_product_ids as $product_id ) {
				$this->update_product( $product_id );
			}
		}

		if ( $end_sale_product_ids ) {
			foreach ( $end_sale_product_ids as $product_id ) {
				$this->update_product( $product_id );
			}
		}

	}


}
