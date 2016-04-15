<?php

function woocommerce_swatches_get_template( $template_name, $args = array() ) {
	global $woocommerce_swatches;
	return wc_get_template( $template_name, $args, 'woocommerce-swatches/', $woocommerce_swatches->plugin_dir() . '/templates/' );
}

function woocommerce_swatches_get_variation_form_args() {
	global $woocommerce, $product, $post;

	$is_wc_24 = version_compare( WC_Swatches_Compatibility::get_wc_version(), '2.4.0', '>=' );
	$is_product_wc_24 = version_compare( get_post_meta( $product->id, '_product_version', true ), '2.4.0', '>=' );

	$attributes = $product->get_variation_attributes();
	$attributes_renamed = array();
	$hashed_attributes = array();
	$attribute_map = array();

	foreach ( $attributes as $attribute => $values ) {
		$attributes_renamed['attribute_' . sanitize_title( $attribute )] = array_values( array_map( 'md5', array_map( 'sanitize_title', array_map( 'strtolower', $values ) ) ) );
		$hashed_attributes[$attribute] = array_map( 'md5', $values );

		foreach ( $values as $value ) {
			if ( $is_product_wc_24 ) {
				$attribute_map['attribute_' . sanitize_title( $attribute )][md5( sanitize_title( $value ) )] = ( $value );
			} else {
				if ( $is_wc_24 ) {
					$attribute_map['attribute_' . sanitize_title( $attribute )][md5( sanitize_title( $value ) )] = ( $value );
				} else {
					$attribute_map['attribute_' . sanitize_title( $attribute )][md5( sanitize_title( $value ) )] = sanitize_title( $value );
				}
			}
		}
	}

	$default_attributes = (array) maybe_unserialize( $product->get_variation_default_attributes() );
	$selected_attributes = apply_filters( 'woocommerce_product_default_attributes', $default_attributes );

	$selected_attributes = array();
	foreach ( $default_attributes as $sk => $s ) {
		$selected_attributes[md5( ( $sk ) )] = $s;
	}

	// Put available variations into an array and put in a Javascript variable (JSON encoded)
	$available_variations = array();
	$available_variations_flat = array();

	foreach ( $product->get_children() as $child_id ) {

		$variation = $product->get_child( $child_id );

		if ( $variation instanceof WC_Product_Variation ) {

			if ( get_post_status( $variation->get_variation_id() ) != 'publish' )
				continue; // Disabled

			if ( empty( $variation->variation_id ) || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && !$variation->is_in_stock() ) ) {
				continue;
			}

			$variation_attributes = $variation->get_variation_attributes();
			$hva = array();
			foreach ( $variation_attributes as $vak => $va ) {
				if ( !empty( $va ) ) {
					if ( $is_product_wc_24 ) {
						$hva[$vak] = md5( sanitize_title( $va ) );
					} else {
						if ( $is_wc_24 && !$is_product_wc_24 ) {
							$hva[$vak] = md5( ( sanitize_title( $va ) ) );
						} else {
							$hva[$vak] = md5( ( $va ) );
						}
					}
				} else {
					$hva[$vak] = '';
				}
			}

			$available_variations_flat[] = $hva;
		}
	}


	$av = $product->get_available_variations();

	$result = array(
	    'available_variations' => $av,
	    'available_variations_flat' => $available_variations_flat,
	    'attributes' => $hashed_attributes,
	    'attributes_renamed' => $attributes_renamed,
	    'selected_attributes' => $selected_attributes,
	    'variations_map' => $attribute_map
	);

	return $result;
}

function woocommerce_swatches_get_variation_form_args1() {
	global $woocommerce, $product, $post;

	$attributes = $product->get_variation_attributes();
	$attributes_renamed = array();

	foreach ( $attributes as $attribute => $values ) {
		$attributes_renamed['attribute_' . sanitize_title( $attribute )] = array_values( array_map( 'sanitize_title', array_map( 'strtolower', $values ) ) );
	}

	$default_attributes = (array) maybe_unserialize( get_post_meta( $post->ID, '_default_attributes', true ) );
	$selected_attributes = apply_filters( 'woocommerce_product_default_attributes', $default_attributes );

	// Put available variations into an array and put in a Javascript variable (JSON encoded)
	$available_variations = array();
	$available_variations_flat = array();

	foreach ( $product->get_children() as $child_id ) {

		$variation = $product->get_child( $child_id );

		if ( $variation instanceof WC_Product_Variation ) {

			if ( get_post_status( $variation->get_variation_id() ) != 'publish' )
				continue; // Disabled

			if ( !$variation->is_purchasable() ) {
				continue; // Visible setting - may be hidden if out of stock
			}

			$variation_attributes = $variation->get_variation_attributes();

			$hva = array();
			foreach ( $variation_attributes as $vak => $va ) {
				if ( !empty( $va ) ) {
					$hva[$vak] = md5( $va );
				} else {
					$hva[$vak] = '';
				}
			}

			$available_variations_flat[] = $hva;
		}
	}

	$av = $product->get_available_variations();
	return array(
	    'available_variations' => $av,
	    'available_variations_flat' => $available_variations_flat,
	    'attributes' => $attributes,
	    'attributes_renamed' => $attributes_renamed,
	    'selected_attributes' => $product->get_variation_default_attributes(),
	);
}

?>