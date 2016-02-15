<?php

function woogle_update() {
	// Merchant ID
	$merchant_id = get_option( 'woogle_merchant_id' );
	// Google Client
	$client = woogle_create_client();
	// OAuth token
	$token = woogle_get_oauth_token();

	if ( isset( $_GET['code'] ) ) {
		try {
			$token = $client->authenticate( $_GET['code'] );
			woogle_set_oauth_token( $token );
			$refresh_token = $client->getRefreshToken();
			woogle_set_oauth_refresh_token( $refresh_token );
		} catch( Exception $e ) {
			woogle_delete_oauth_token();
			woogle_delete_oauth_refresh_token();
			woogle_add_message( __(
			'Your Google Merchant account could not be authorized',
			'woogle'
					), 'error' );
					woogle_add_message( __(
					esc_html( $e->getMessage() ),
					'woogle'
							), 'error' );
							header( 'Location: ' . admin_url( 'admin.php?page=wc-settings&tab=products&section=woogle') );
							exit;
		}
	} elseif ( isset( $_GET['error'] ) ) {
		woogle_delete_oauth_token();
		woogle_delete_oauth_refresh_token();
		woogle_add_message( __(
		'Your Google Merchant account could not be authorized: ' . esc_html( $_GET['error'] ),
		'woogle'
				), 'error' );
				header( 'Location: ' . admin_url( 'admin.php?page=wc-settings&tab=products&section=woogle') );
				exit;
	}
	if ( empty( $token ) ) {
		header( 'Location: ' . $client->createAuthUrl() );
		exit;
	}
	$client->setAccessToken( $token );
	if ( $client->isAccessTokenExpired() ) {
		$new_token = woogle_refresh_oauth_token( $client );
		if ( empty( $new_token ) ) {
			header( 'Location: ' . $client->createAuthUrl() );
			exit;
		}
	}

	// Get products
	$wc_products = woogle_get_pending_wc_products();
	// Upload products
	$product_batch_results = woogle_upload_wc_products( $wc_products, $client, $merchant_id );
	// Process batch result
	$batch_has_errors = false;
	$update_count = 0;
	foreach ( $product_batch_results as $result ) {
		if ( $result instanceof Google_Service_Exception ) {
			$batch_has_errors = true;
			$error_message = $result->getMessage();
			woogle_add_message( $error_message, 'error' );
		} elseif ( $result instanceof Google_Service_ShoppingContent_Product ) {
			$customAttributes = $result->getCustomAttributes();
			foreach ( $customAttributes as $attribute ) { /** @var Google_Service_ShoppingContent_ProductCustomAttribute $attribute */
				if ( $attribute->getName() == 'woocommerce id' || $attribute->getName() == 'woocommerce_id' ) {
					$post_id = $attribute->getValue();
					$expiration_time = time() + 2592000; // 30 days
					update_post_meta( $post_id, '_woogle_expiration_time', $expiration_time );
					break;
				}
			}
			$update_count++;
		}
	}

	if ( $batch_has_errors ) {
		woogle_add_message( __(
			'Errors were encountered updating your products in the Google Merchant Center!',
			'woogle'
		), 'error' );
	} else {
		woogle_add_message( __(
			"{$update_count} products have been updated in the Google Merchant Center!",
			'woogle'
		) );
	}

	header( 'Location: ' . admin_url( 'admin.php?page=wc-settings&tab=products&section=woogle') );
	exit;
}
add_action( 'wp_ajax_update_google_shopping_api', 'woogle_update' );

/**
 * Upload WooCommerce products to the Google Merchant Center
 * @param WC_Product[] $wc_products
 * @param Google_Client $client
 * @param string $merchant_id
 * @return array Product batch results
 */
 function woogle_upload_wc_products( $wc_products, $client, $merchant_id ) {
	// Shopping Content Service
	require_once( plugin_dir_path( woogle_get_plugin_file() ) . 'vendor/google-api-php-client/src/Google/Service/ShoppingContent.php' );
	$service = new Google_Service_ShoppingContent( $client );

	// Batch
	$client->setUseBatch( true );
	require_once( plugin_dir_path( woogle_get_plugin_file() ) . 'vendor/google-api-php-client/src/Google/Http/Batch.php' );
	$product_batch = new Google_Http_Batch( $client );

	// Loop through products
	foreach ( $wc_products as $post ) {
		// Get WC Product
		$wc_product = ( $post instanceof WC_Product ) ? $post : wc_get_product( $post );
		// Get Google Product
		if ( $wc_product->is_type( 'variable') ) {
			$variations = $wc_product->get_available_variations();
			foreach ( $variations as $variation ) {
				$wc_product_variable = wc_get_product( $variation['variation_id'] );
				$product = woogle_build_product( $wc_product_variable );
				// Add request to batch
				$request = $service->products->insert( $merchant_id, $product );
				$product_batch->add( $request, $variation['variation_id'] );
			}
		} else {
			$product = woogle_build_product( $wc_product );
			// Add request to batch
			$request = $service->products->insert( $merchant_id, $product );
			$product_batch->add( $request, $wc_product->id );
		}
		// Product updated
		$update_count++;
	}

	// Execute batch
	$product_batch_results = $product_batch->execute();
	return $product_batch_results;
}

function woogle_save_product( $product_id ) {
	if ( wp_is_post_revision( $product_id ) ) {
		return;
	}

	// Get the post type
	$post_type = get_post_type( $product_id );
	// If this is not a WC product, return
	if ( $post_type != 'product' ) {
		return;
	}

	// Check if Woogle is enabled for this product
	$api_enabled = get_post_meta( $product_id, '_woogle_enabled', true );
	if ( $api_enabled != 'yes' ) {
		return;
	}

	// OAuth token
	$token = woogle_get_oauth_token();
	if ( empty( $token ) ) {
		woogle_delete_oauth_token();
		woogle_delete_oauth_refresh_token();
		return;
	}

	// Google Client
	$client = woogle_create_client();
	$client->setAccessToken( $token );
	if ( $client->isAccessTokenExpired() ) {
		$new_token = woogle_refresh_oauth_token( $client );
		if ( empty( $new_token ) ) {
			return;
		}
	}

	// Merchant ID
	$merchant_id = get_option( 'woogle_merchant_id' );

	// Get WC Product
	$wc_product = wc_get_product( $product_id );
	// Upload product
	$product_batch_results = woogle_upload_wc_products( array( $wc_product ), $client, $merchant_id );
	// Process batch results
	$batch_has_errors = false;
	foreach ( $product_batch_results as $result ) {
		if ( $result instanceof Google_Service_Exception ) {
			$batch_has_errors = true;
			$error_message = $result->getMessage();
			woogle_add_message( $error_message, 'error' );
		}
	}
	if ( $batch_has_errors ) {
		delete_post_meta( $product_id, '_woogle_expiration_time' );
		woogle_add_message( __(
			'Errors were encountered updating your product in the Google Merchant Center!',
			'woogle'
		), 'error' );
	} else {
		$expiration_time = time() + 2592000; // 30 days
		update_post_meta( $product_id, '_woogle_expiration_time', $expiration_time );
		woogle_add_message( __(
			'Your product has been updated in the Google Merchant Center!',
			'woogle'
		) );
	}
}
add_action( 'save_post', 'woogle_save_product', 100, 1 );

/**
 * Upload products from an order
 * @param WC_Order $order
 */
function woogle_process_order( $order ) {
	// Check if update on checkout is enabled
	$update_on_checkout = get_option( 'woogle_update_on_checkout' );
	if ( $update_on_checkout != 'yes' ) {
		return;
	}

	// OAuth token
	$token = woogle_get_oauth_token();
	if ( empty( $token ) ) {
		woogle_delete_oauth_token();
		woogle_delete_oauth_refresh_token();
		return;
	}

	// Google Client
	$client = woogle_create_client();
	$client->setAccessToken( $token );
	if ( $client->isAccessTokenExpired() ) {
		$new_token = woogle_refresh_oauth_token( $client );
		if ( empty( $new_token ) ) {
			return;
		}
	}

	// Merchant ID
	$merchant_id = get_option( 'woogle_merchant_id' );

	// Process order items
	$items = $order->get_items();
	$wc_products = array();
	foreach ( $items as $item ) {
		$product_id = $item['product_id'];
		// Check if Woogle is enabled for this product
		$api_enabled = get_post_meta( $product_id, '_woogle_enabled', true );
		if ( $api_enabled != 'yes' ) {
			continue;
		}
		$wc_products[] = wc_get_product( $product_id );
	}
	if ( empty( $wc_products ) ) {
		return;
	}

	// Upload products
	$product_batch_results = woogle_upload_wc_products( $wc_products, $client, $merchant_id );
	// Process batch results
	foreach ( $product_batch_results as $result ) {
		if ( $result instanceof Google_Service_Exception ) {
			// Error
		} elseif ( $result instanceof Google_Service_ShoppingContent_Product ) {
			// Success
			$customAttributes = $result->getCustomAttributes();
			foreach ( $customAttributes as $attribute ) { /** @var Google_Service_ShoppingContent_ProductCustomAttribute $attribute */
				if ($attribute->getName() == 'woocommerce id' || $attribute->getName() == 'woocommerce_id' ) {
					$post_id = $attribute->getValue();
					$expiration_time = time() + 2592000; // 30 days
					update_post_meta( $post_id, '_woogle_expiration_time', $expiration_time );
					break;
				}
			}
		}
	}
}
add_action( 'woocommerce_reduce_order_stock', 'woogle_process_order', 10, 2 );

/**
 * Refresh the OAuth token
 * @param Google_Client $client
 * @return string New token
 */
function woogle_refresh_oauth_token( $client ) {
	try {
		$refresh_token = woogle_get_oauth_refresh_token();
		if ( empty( $refresh_token ) ) {
			return '';
		}
		$client->refreshToken( $refresh_token );
		$new_token = $client->getAccessToken();
		woogle_set_oauth_token( $new_token );
		return $new_token;
	} catch( Exception $e ) {
		woogle_delete_oauth_token();
		woogle_delete_oauth_refresh_token();
		update_option( 'woogle_oauth_access_token_error', $e->getMessage() );
	}
	return '';
}

function woogle_get_pending_wc_products() {
	// Products query
	$product_query_args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => '_woogle_enabled',
				'value' => 'yes',
				'compare' => '='
			),
			array(
				'relation' => 'OR',
				array(
					'key' => '_woogle_expiration_time',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key' => '_woogle_expiration_time',
					'value' => time(),
					'compare' => '<'
				)
			)
		)
	);
	$products_result = get_posts( $product_query_args );
	return $products_result;
}

/**
 * Build a Google Shopping Content Product from a WC Product
 * @param WC_Product $wc_product
 * @return Google_Service_ShoppingContent_Product
 */
function woogle_build_product( $wc_product ) {
	// Create Google Shopping Content Product
	require_once( plugin_dir_path( woogle_get_plugin_file() ) . 'vendor/google-api-php-client/src/Google/Service/ShoppingContent.php' );
	$product = new Google_Service_ShoppingContent_Product();

	// Custom Attributes
	$product_custom_attributes = array();

	// Product identifiers
	$sku = $wc_product->get_sku();
	if ( empty( $sku ) ) {
		if ( $wc_product->is_type( 'variation' ) ) {
			$product->setOfferId( $wc_product->variation_id );
		} else {
			$product->setOfferId( $wc_product->id );
		}
	} else {
		$product->setOfferId( $sku );
	}
	if ( $wc_product->is_type( 'variation' ) ) {
		$product->setItemGroupId( $wc_product->parent->id );
	}
	$woocommerce_id_attribute = new Google_Service_ShoppingContent_ProductCustomAttribute();
	$woocommerce_id_attribute->setName( 'woocommerce_id' );
	$woocommerce_id_attribute->setValue( $wc_product->id );
	$woocommerce_id_attribute->setType( 'int' );
	$product_custom_attributes[] = $woocommerce_id_attribute;

	// Title
	$woogle_title = get_post_meta( $wc_product->id, '_woogle_title', true );
	if ( ! empty( $woogle_title ) ) {
		$product->setTitle( html_entity_decode( strip_tags( $woogle_title ) ) );
	} else {
		$product->setTitle( html_entity_decode( strip_tags( $wc_product->get_title() ) ) );
	}
	
	// Description
	$woogle_description = get_post_meta( $wc_product->id, '_woogle_description', true );
	if ( ! empty( $woogle_description ) ) {
		$product->setDescription( html_entity_decode( strip_tags( $woogle_description ) ) );
	} else {
		$product->setDescription( html_entity_decode( strip_tags( $wc_product->post->post_content ) ) );
	}

	$product->setLink( $wc_product->get_permalink() );

	$image = $wc_product->get_image();
	$post_thumbnail_id = get_post_thumbnail_id( $wc_product->id );
	if ( $post_thumbnail_id ) {
		$image_src = wp_get_attachment_image_src( $post_thumbnail_id );
		$product->setImageLink( @ $image_src[0] );
	}

	$product->setContentLanguage( substr( get_locale(), 0, 2 ) );
	$product->setTargetCountry( WC()->countries->get_base_country() );

	$product->setChannel( 'online' );

	$product->setAvailability( $wc_product->is_in_stock() ? 'in stock' : 'out of stock' );

	// Condition
	$condition = get_post_meta( $wc_product->id, '_woogle_condition', true );
	if ( ! empty( $condition ) ) {
		$product->setCondition( $condition );
	} else {
		$product->setcondition( 'new' );
	}

	// Category
	$category = get_post_meta( $wc_product->id, '_woogle_category', true );
	if ( ! empty( $category ) ) {
		$product->setGoogleProductCategory( $category );
	}

	// Brand
	$brand = get_post_meta( $wc_product->id, '_woogle_brand', true );
	if ( ! empty( $brand ) ) {
		$product->setBrand( $brand );
	}

	// GTIN
	$gtin = get_post_meta( $wc_product->id, '_woogle_gtin', true );
	if ( ! empty( $gtin ) ) {
		$product->setGtin( $gtin );
	}

	// MPN
	$mpn = get_post_meta( $wc_product->id, '_woogle_mpn', true );
	if ( ! empty( $mpn ) ) {
		$product->setMpn( $mpn );
	}

	if ( empty( $gtin ) && empty( $mpn ) ) {
		$product->setIdentifierExists( false );
	}

	// Price
	$price = new Google_Service_ShoppingContent_Price();
	$price->setValue( $wc_product->get_regular_price() );
	$price->setCurrency( get_woocommerce_currency() );
	$product->setPrice( $price );

	// Sale price
	$wc_sale_price = $wc_product->get_sale_price();
	if ( ! empty( $wc_sale_price ) ) {
		$sale_price = new Google_Service_ShoppingContent_Price();
		$sale_price->setValue( $wc_sale_price );
		$sale_price->setCurrency( get_woocommerce_currency() );
		$product->setSalePrice( $sale_price );
		$post_id = $wc_product->is_type( 'variation' ) ? $wc_product->variation_id : $wc_product->id;
		$sale_price_dates_from = get_post_meta( $post_id, '_sale_price_dates_from', true );
		$sale_price_dates_to = get_post_meta( $post_id, '_sale_price_dates_to', true );
		if ( ! empty( $sale_price_dates_from ) && ! empty( $sale_price_dates_to ) ) {
			$effective_date_start = date( 'c', intval( $sale_price_dates_from ) );
			$effective_date_end = date( 'c', intval( $sale_price_dates_to ) );
			$product->setSalePriceEffectiveDate( "{$effective_date_start}/{$effective_date_end}" );
		}
	}
	
	// Shipping fields
	
	// Shipping weight
	$wc_product_weight = $wc_product->get_weight();
	if ( ! empty( $wc_product_weight ) ) {
		$shipping_weight = new Google_Service_ShoppingContent_ProductShippingWeight();
		$shipping_weight->setValue( $wc_product_weight );
		$shipping_weight->setUnit( get_option( 'woocommerce_weight_unit', 'kg' ) );
		$product->setShippingWeight( $shipping_weight );
	}
	// Shipping dimensions
	$wc_dimension_unit = get_option( 'woocommerce_dimension_unit', 'cm' );
	$wc_product_length = $wc_product->get_length();
	if ( ! empty( $wc_product_length ) ) {
		$dimension = new Google_Service_ShoppingContent_ProductShippingDimension();
		$dimension->setValue( $wc_product_length );
		$dimension->setUnit( $wc_dimension_unit );
		$product->setShippingLength( $dimension );
	}
	$wc_product_width = $wc_product->get_width();
	if ( ! empty( $wc_product_width) ) {
		$dimension = new Google_Service_ShoppingContent_ProductShippingDimension();
		$dimension->setValue( $wc_product_width );
		$dimension->setUnit( $wc_dimension_unit );
		$product->setShippingWidth( $dimension );
	}
	$wc_product_height = $wc_product->get_height();
	if ( ! empty( $wc_product_height ) ) {
		$dimension = new Google_Service_ShoppingContent_ProductShippingDimension();
		$dimension->setValue( $wc_product_height );
		$dimension->setUnit( $wc_dimension_unit );
		$product->setShippingHeight( $dimension );
	}
	

	// Attributes
	$color = null;
	$material = null;
	$pattern = null;
	$size = null;
	$age_group = null;
	$gender = null;
	$adult = null;
	if ( $wc_product->is_type( 'variation' ) ) {
		// Variable product
		$wc_variation_attributes = $wc_product->get_variation_attributes();
		foreach ( $wc_variation_attributes as $name => $value ) {
			if ( ! empty( $value ) ) {
				$attribute = new Google_Service_ShoppingContent_ProductCustomAttribute();
				$attribute->setName( $name );
				$attribute->setValue( $value );
				$attribute->setType( 'text' );
				$product_custom_attributes[] = $attribute;
				// Google attributes
				if ( empty( $color ) ) {
					$color = woogle_maybe_get_attribute( 'color', $name, $value );
				}
				if ( empty( $material ) ) {
					$material = woogle_maybe_get_attribute( 'material', $name, $value );
				}
				if ( empty( $pattern ) ) {
					$pattern = woogle_maybe_get_attribute( 'pattern', $name, $value );
				}
				if ( empty( $size ) ) {
					$size = woogle_maybe_get_attribute( 'size', $name, $value );
				}
				if ( empty( $age_group ) ) {
					$age_group = woogle_maybe_get_attribute( 'age-group', $name, $value );
				}
				if ( empty( $gender ) ) {
					$gender = woogle_maybe_get_attribute( 'gender', $name, $value );
				}
				if ( empty( $adult ) ) {
					$adult = woogle_maybe_get_attribute( 'adult', $name, $value );
				}
			}
		}
	} else {
		// Simple product
		$wc_attributes = $wc_product->get_attributes();
		foreach ( $wc_attributes as $name => $wc_attribute ) {
			if ( $wc_attribute['is_taxonomy'] ) {
				$term_values = array();
				$terms = wp_get_post_terms( $wc_product->id, $name );
				foreach ( $terms as $term ) {
					$term_values[] = $term->slug;
				}
				if ( ! empty( $term_values ) ) {
					$value = implode( ',', $term_values );
					$attribute = new Google_Service_ShoppingContent_ProductCustomAttribute();
					$attribute->setName( $name );
					$attribute->setValue( $value );
					$attribute->setType( 'text' );
					$product_custom_attributes[] = $attribute;
					// Google attributes
					if ( empty( $color ) ) {
						$color = woogle_maybe_get_attribute( 'color', $name, $value );
					}
					if ( empty( $material ) ) {
						$material = woogle_maybe_get_attribute( 'material', $name, $value );
					}
					if ( empty( $pattern ) ) {
						$pattern = woogle_maybe_get_attribute( 'pattern', $name, $value );
					}
					if ( empty( $size ) ) {
						$size = woogle_maybe_get_attribute( 'size', $name, $value );
					}
					if ( empty( $age_group ) ) {
						$age_group = woogle_maybe_get_attribute( 'age-group', $name, $value );
					}
					if ( empty( $gender ) ) {
						$gender = woogle_maybe_get_attribute( 'gender', $name, $value );
					}
					if ( empty( $adult ) ) {
						$adult = woogle_maybe_get_attribute( 'adult', $name, $value );
					}
				}
			}
		}
	}

	/*
	 * Physical properties
	 */

	// Color
	if ( empty( $color ) ) {
		$color = get_post_meta( $wc_product->id, '_woogle_color', true );
	}
	if ( ! empty( $color ) ) {
		$product->setColor( $color );
	}
	// Material
	if ( empty( $material ) ) {
		$material = get_post_meta( $wc_product->id, '_woogle_material', true );
	}
	if ( ! empty( $material ) ) {
		$product->setMaterial( $material );
	}
	// Pattern
	if ( empty( $pattern ) ) {
		$pattern = get_post_meta( $wc_product->id, '_woogle_pattern', true );
	}
	if ( ! empty( $pattern ) ) {
		$product->setPattern( $pattern );
	}
	// Size
	if ( empty( $size ) ) {
		$size = get_post_meta( $wc_product->id, '_woogle_size', true );
	}
	if ( ! empty( $size ) ) {
		$product->setSizes( explode( ', ', $size ) );
		$product->setSizeSystem( WC()->countries->get_base_country() );
	}

	/*
	 * Target consumer
	 */

	// Age Group
	if ( empty( $age_group) ) {
		$age_group = get_post_meta( $wc_product->id, '_woogle_age_group', true );
	}
	if ( ! empty( $age_group ) ) {
		$product->setAgeGroup( $age_group );
	}

	// Gender
	if ( empty( $gender ) ) {
		$gender = get_post_meta( $wc_product->id, '_woogle_gender', true );
	}
	if ( ! empty( $gender ) ) {
		$product->setGender( $gender );
	}

	// Gender
	if ( empty( $adult ) ) {
		$adult = get_post_meta( $wc_product->id, '_woogle_adult', true );
	}
	if ( ! empty( $adult ) && $adult != 'no' ) {
		$product->setAdult( true );
	}

	$product->setCustomAttributes( $product_custom_attributes );

	return $product;
}

function woogle_maybe_get_attribute( $name, $attribute_name, $value ) {
	if ( strpos( $attribute_name, $name ) !== false ) {
		return $value;
	}
	return null;
}

function woogle_revoke_access() {
	woogle_delete_oauth_token();
	woogle_delete_oauth_refresh_token();
	woogle_add_message( __( 'Access to the Google Shopping Content API has been revoked.', 'woogle' ) );
	header( 'Location: ' . admin_url( 'admin.php?page=wc-settings&tab=products&section=woogle') );
	exit;
}
add_action( 'wp_ajax_google_shopping_api_revoke_access', 'woogle_revoke_access' );

function woogle_update_notifications() {
	if ( ! is_admin() ) {
		return;
	}
	if ( is_ajax() ) {
		return;
	}

	// Products query
	$product_query_args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => '_woogle_enabled',
				'value' => 'yes',
				'compare' => '='
			),
			array(
				'relation' => 'OR',
				array(
					'key' => '_woogle_expiration_time',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key' => '_woogle_expiration_time',
					'value' => time(),
					'compare' => '<'
				)
			)
		)
	);
	$products_result = get_posts( $product_query_args );
	$count = count( $products_result );
	if ( $count > 0 ) {
		woogle_add_message( 
			__( "You have $count products to update in your Google Merchant Center. "
				. '<a href="'. admin_url( '/admin.php?page=wc-settings&tab=products&section=woogle' ) . '#update-google-shopping-api-button">'
				. 'Update them now'
				. '</a>',
				'woogle'
			),
			'update-nag'
		);
	}
}
add_action( 'plugins_loaded', 'woogle_update_notifications' );

/**
 * Create Google client
 * @return Google_Client
 */
function woogle_create_client() {
	$application_name = get_option( 'woogle_application_name' );
	$client_id = get_option( 'woogle_client_id' );
	$client_secret = get_option( 'woogle_client_secret' );
	
	require_once( plugin_dir_path( woogle_get_plugin_file() ) . 'vendor/google-api-php-client/src/Google/Client.php' );
	$client = new Google_Client();
	$client->setApplicationName( $application_name );
	$client->setClientId( $client_id );
	$client->setClientSecret( $client_secret );
	$client->setRedirectUri( admin_url( 'admin-ajax.php?action=update_google_shopping_api' ) );
	$client->setScopes( 'https://www.googleapis.com/auth/content' );
	$client->setAccessType( 'offline' );
	$client->setApprovalPrompt( 'force' );
	return $client;
}

/**
 * Get OAuth token
 * @return string
 */
function woogle_get_oauth_token() {
	return get_option( 'woogle_oauth_token' );
}

/**
 * Set OAuth token
 * @param string $token
 */
function woogle_set_oauth_token( $token ) {
	update_option( 'woogle_oauth_token', $token );
}

/**
 * Delete OAuth refresh token
 */
function woogle_delete_oauth_token() {
	delete_option( 'woogle_oauth_token' );
}

/**
 * Get OAuth refresh token
 * @return string
 */
function woogle_get_oauth_refresh_token() {
	return get_option( 'woogle_oauth_refresh_token' );
}

/**
 * Set OAuth refresh token
 * @param string $refresh_token
 */
function woogle_set_oauth_refresh_token( $refresh_token ) {
	update_option( 'woogle_oauth_refresh_token', $refresh_token );
}

/**
 * Delete OAuth refresh token
 */
function woogle_delete_oauth_refresh_token() {
	delete_option( 'woogle_oauth_refresh_token' );
}