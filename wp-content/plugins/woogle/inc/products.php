<?php

function woogle_flag_product( $post_id ) {
	// If this is just a revision, return
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Get the post type
	$post_type = get_post_type( $post_id );
	// If this is not a WC product, return
	if ( $post_type != 'product' ) {
		return;
	}

	// Flag this product to be updated in the google merchant center
	delete_post_meta( $post_id, '_woogle_expiration_time' );
}
add_action( 'save_post', 'woogle_flag_product' );

function woogle_get_wc_product( $post, $variation_id ) {
	$product_id = $this->get_product_id();
	$variation_id = $this->get_variation_id();
	if ( ! empty( $variation_id ) && 'product_variation' === get_post_type( $variation_id ) ) {
		return wc_get_product( $variation_id );
	} elseif ( ! empty( $product_id  ) ) {
		return wc_get_product( $product_id );
	}
}

function woogle_product_tab( $tabs ) {
	$tabs['woogle'] = array(
		'label'  => __( 'Google Shopping', 'woogle' ),
		'target' => 'woogle_product_data',
		'class'  => array( 'show_if_simple', 'show_if_variable', 'hide_if_grouped' )
	);

	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'woogle_product_tab' );

function woogle_product_tab_content() {
	?>
	<div id="woogle_product_data" class="panel woocommerce_options_panel">
		<div class="options_group show_if_simple show_if_variable hide_if_grouped">
			<?php do_action( 'woocommerce_product_options_woogle_product_data' ); ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'woogle_product_tab_content' );

function woogle_custom_general_fields() {
	global $post;

	echo '<div class="options_group woogle-options-group">';
	
	// Enable Google Shopping
	woocommerce_wp_checkbox( array(
		'id' => '_woogle_enabled',
		'label' => __( 'Enable Google Shopping', 'woogle' ),
		'desc_tip'  => true,
		'description' => __( 'Select to upload this product to your Google Merchant Center', 'woogle' )
	) );
	
	echo __( '<h3>Product Description</h3>', 'woogle' );
	
	// Title
	woocommerce_wp_text_input( array(
		'id' => '_woogle_title',
		'label' => __( 'Woogle Title', 'woogle' ),
		'placeholder' => '(Limit 150 characters)',
		'desc_tip'  => false,
		'description' => __( 'Enter a custom value to override the title for this product.<br />(Limit 150 characters)', 'woogle' )
	) );
	
	// Description
	woocommerce_wp_textarea_input( array(
		'id' => '_woogle_description',
		'label' => __( 'Woogle Description', 'woogle' ),
		'placeholder' => '(Limit 5000 characters)',
		'desc_tip'  => false,
		'description' => __( 'Enter a custom value to override the description for this product.<br />(Limit 5000 characters)', 'woogle' )
	) );
	
	echo __( '<h3>Product Identification</h3>', 'woogle' );
		
	// Condition
	woocommerce_wp_select( array(
		'id' => '_woogle_condition',
		'label' => __( 'Condition', 'woogle' ),
		'options' => array (
			'new' => __( 'New', 'woogle' ),
			'used' => __( 'Used', 'woogle' ),
			'refurbished' => __( 'Refurbished', 'woogle' )
		),
		'desc_tip'  => true,
		'description' => __( 'Condition of the item', 'woogle' )
	) );
	
	// Category
	woocommerce_wp_text_input( array(
		'id' => '_woogle_category',
		'label' => __( 'Product Category', 'woogle' ),
		'placeholder' => '',
		'desc_tip'  => true,
		'description' => __( 'Product Category from Google Shopping Content Category Taxonomy.', 'woogle' )
	) );
	
	// Brand
	woocommerce_wp_text_input( array(
		'id' => '_woogle_brand',
		'label' => __( 'Brand', 'woogle' ),
		'placeholder' => '',
		'desc_tip'  => true,
		'description' => __( 'The brand of the product.', 'woogle' )
	) );
	
	// GTIN
	woocommerce_wp_text_input( array(
		'id' => '_woogle_gtin',
		'label' => __( 'GTIN', 'woogle' ),
		'placeholder' => '',
		'desc_tip'  => true,
		'description' => __( 'Global Trade Item Number', 'woogle' )
	) );
	
	// MPN
	woocommerce_wp_text_input( array(
		'id' => '_woogle_mpn',
		'label' => __( 'MPN', 'woogle' ),
		'placeholder' => '',
		'desc_tip'  => true,
		'description' => __( 'Manufacturer Part Number', 'woogle' )
	) );
	
	echo __( '<h3>Physical Properties</h3>', 'woogle' );
	
	// Color
	woocommerce_wp_text_input( array(
		'id' => '_woogle_color',
		'label' => __( 'Color', 'woogle' ),
		'placeholder' => '',
		'desc_tip'  => true,
		'description' => __( 
			'The color of the product. If you are trying to indicate multiple colors for a '
			. 'single item (not product variants), specify a primary color, optionally followed '
			. 'by at most two secondary colors, separated by a \'/\'. Here replace RedPinkBlue '
			. 'with Red/Pink/Blue', 
			'woogle'
		)
	) );
	
	// Material
	woocommerce_wp_text_input( array(
		'id' => '_woogle_material',
		'label' => __( 'Material', 'woogle' ),
		'placeholder' => '',
		'desc_tip'  => true,
		'description' => __( 'The material of the product.', 'woogle' )
	) );
	
	// Pattern
	woocommerce_wp_text_input( array(
		'id' => '_woogle_pattern',
		'label' => __( 'Pattern', 'woogle' ),
		'placeholder' => '',
		'desc_tip'  => true,
		'description' => __( 'The pattern of the product.', 'woogle' )
	) );
	
	// Size
	woocommerce_wp_text_input( array(
		'id' => '_woogle_size',
		'label' => __( 'Size', 'woogle' ),
		'placeholder' => '',
		'desc_tip'  => true,
		'description' => __( 'The size of the product.', 'woogle' )
	) );
	
	echo __( '<h3>Target Consumer</h3>', 'woogle' );
	
	// Age Group
	woocommerce_wp_select( array(
		'id' => '_woogle_age_group',
		'label' => __( 'Age Group', 'woogle' ),
		'options' => array(
			'' => __( '&mdash;SELECT&mdash;', 'woogle' ),
			'adult' => __( 'Adult', 'woogle' ),
			'infant' => __( 'Infant', 'woogle' ),
			'kids' => __( 'Kids', 'woogle' ),
			'newborn' => __( 'Newborn', 'woogle' ),
			'toddler' => __( 'Toddler', 'woogle' )
		),
		'desc_tip'  => true,
		'description' => __( 'The target age group of the product.', 'woogle' )
	) );
	
	// Gender
	woocommerce_wp_select( array(
		'id' => '_woogle_gender',
		'label' => __( 'Gender', 'woogle' ),
		'options' => array(
			'' => __( '&mdash;SELECT&mdash;', 'woogle' ),
			'female' => __( 'Female', 'woogle' ),
			'male' => __( 'Male', 'woogle' ),
			'unisex' => __( 'Unisex', 'woogle' )
		),
		'desc_tip'  => true,
		'description' => __( 'The target gender of the product.', 'woogle' )
	) );
	
	// Adult
	woocommerce_wp_checkbox( array(
		'id' => '_woogle_adult',
		'label' => __( 'Adult (18+)', 'woogle' ),
		'desc_tip'  => true,
		'description' => __( 'This item is targeted towards adults.', 'woogle' )
	) );

	echo '</div>';

}
add_action( 'woocommerce_product_options_woogle_product_data', 'woogle_custom_general_fields' );

function woogle_custom_general_fields_save( $post_id ) {
	$field_names = array(
		'_woogle_enabled',
		'_woogle_title',
		'_woogle_description',
		'_woogle_condition',
		'_woogle_category',
		'_woogle_brand',
		'_woogle_gtin',
		'_woogle_mpn',
		'_woogle_color',
		'_woogle_material',
		'_woogle_pattern',
		'_woogle_size',
		'_woogle_age_group',
		'_woogle_gender',
		'_woogle_adult'
	);
	foreach ( $field_names as $name ) {
		if ( isset( $_POST[ $name ] ) ) {
			update_post_meta( $post_id, $name, $_POST[ $name ] );
		} else {
			update_post_meta( $post_id, $name, '' );
		}
	}
}
add_action( 'woocommerce_process_product_meta', 'woogle_custom_general_fields_save' );

function woogle_product_columns( $columns ) {

	$columns['woogle_status'] = '<img src="' . plugin_dir_url( woogle_get_plugin_file() ) . 'images/updated.svg" '
		. 'class="woogle-icon" '
		. 'alt="' . esc_html( __( 'Google Shopping Status', 'woogle' ) ) . '" '
		. 'title="' . esc_html( __( 'Google Shopping Status', 'woogle' ) ) . '" '
		. ' />';

	return $columns;
}
add_filter( 'manage_edit-product_columns', 'woogle_product_columns', 15 );

function woogle_status_column( $column, $post_id ) {
	if ( $column == 'woogle_status' ) {
		$icon_base_url = plugin_dir_url( woogle_get_plugin_file() );
		$api_enabled = get_post_meta( $post_id, '_woogle_enabled', true );
		if ( $api_enabled == 'yes' ) {
			$time = time();
			$expiration_time = intval( get_post_meta( $post_id, '_woogle_expiration_time', true ) );
			if ( $expiration_time == 0 ) {
				// Not updated
				echo "<img src=\"{$icon_base_url}images/not-updated.svg\" class=\"woogle-status not-updated\" alt=\"Not updated\" title=\"Not updated\" />";
			} elseif ( $expiration_time > time() ) {
				// Updated
				echo "<img src=\"{$icon_base_url}images/updated.svg\" class=\"woogle-status updated\" alt=\"Updated\" title=\"Updated\" />";
			} else {
				// Expired
				echo "<img src=\"{$icon_base_url}images/expired.svg\" class=\"woogle-status expired\" alt=\"Expired\" title=\"Expired\" />";
			}
		} else {
			// Not applicable
			echo "<img src=\"{$icon_base_url}images/n-a.svg\" class=\"woogle-status n-a\" alt=\"Not enabled\" title=\"Not enabled\" />";
		}
	}
}
add_action( 'manage_product_posts_custom_column', 'woogle_status_column', 10, 2 );