<?php

function woogle_admin_scripts() {
	wp_enqueue_style( 'woogle-admin', plugin_dir_url( woogle_get_plugin_file() ) . 'css/admin.css', array(), Woogle_Version );

	// jquery-ui-autocomplete
	wp_enqueue_script( 'jquery-ui' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	
	wp_enqueue_script( 'woogle-admin', plugin_dir_url( woogle_get_plugin_file() ) . 'js/admin.js', array( 'jquery' ), Woogle_Version );

	// Post edit
	if ( strpos( $_SERVER['REQUEST_URI'], '/post.php' ) > 0 && strpos( $_SERVER['REQUEST_URI'], '&action=edit' ) > 0 ) {
		// Register admin script
		wp_register_script(
			'woogle-product',
			plugin_dir_url( woogle_get_plugin_file() ) . 'js/product.js',
			array( 'jquery-ui-autocomplete' ),
			Woogle_Version
		);
		// Category options
		$category_options = array();
		$category_top_level_options = array();
		$category_option_groups = array();
		$category_list_file = apply_filters(
			'woogle-category-list-file',
			plugin_dir_path( woogle_get_plugin_file() ) . '/resources/taxonomy.en-US.txt'
		);
		$category_list = file_get_contents( $category_list_file );
		if ( ! empty( $category_list ) ) {
			$delim = ( strpos( $category_list, "\r\n" ) !== false ) ? "\r\n" : "\n";
			$categories = explode( $delim, $category_list );
			$count = count( $categories );
			$top_level_category = '';
			for ( $c = 1; $c < $count; $c++ ) {
				$category_options[] = $categories[ $c ];
				if ( strpos( $categories[ $c ], '>' ) === false ) {
					$category_top_level_options[] = $categories[ $c ];
					$top_level_category = $categories[ $c ];
					$category_option_groups[ $top_level_category ] = array();
				} else {
					$category_option_groups[ $top_level_category ][] = $categories[ $c ];
				}
			}
		}
		// Color options
		$color_options = array(
			'Black',
			'Blue',
			'Brown',
			'Gray',
			'Green',
			'Orange',
			'Pink',
			'Purple',
			'Red',
			'White',
			'Yellow'
		);
		// Size options
		$size_options = array(
			'XXS', 'XS', 'S', 'M', 'L', 'XL', '1XL', '2XL', '3XL', '4XL', '5XL', '6XL',
			'00', '0', '02', '04', '06', '08', '10', '12', '14', '16', '18', '20', '22', '24', '26', '28', '30', '32', '34',
			'23', '24', '26', '27', '28', '29', '30', '32', '34', '36', '38', '40', '42', '44'
		);
		// Localize admin script
		wp_localize_script(
			'woogle-product',
			'Woogle_Product',
			array(
				'category_options' => $category_options,
				'category_top_level_options' => $category_top_level_options,
				'category_option_groups' => $category_option_groups,
				'color_options' => $color_options,
				'size_options' => $size_options
			)
		);
		wp_enqueue_script( 'woogle-product' );
	}
}
add_action( 'admin_enqueue_scripts', 'woogle_admin_scripts' );