<?php

// Register widget areas

// Before Header

genesis_register_sidebar( array(
	'id'            => 'before-header-left',
	'name'          => __( 'Before Header Left', 'epik' ),
	'description'   => __( 'This is the Before Header Left widget area', 'epik' ),
	'before_widget' => '<div class="one-third first before-header-widget before-header-left">',
	'after_widget'  => '</div>',
) );
genesis_register_sidebar( array(
	'id'            => 'before-header-right',
	'name'          => __( 'Before Header Right', 'epik' ),
	'description'   => __( 'This is the Before Header Right widget area', 'epik' ),
	'before_widget' => '<div class="one-third before-header-widget before-header-right">',
	'after_widget'  => '</div>',
) );

// After Entry
genesis_register_sidebar( array(
	'id'          => 'after-entry',
	'name'        => __( 'After Entry', 'epik' ),
	'description' => __( 'This widget will show up at the very end of each post.', 'epik' ),
) );



//* Hooks after-entry widget area to single posts
add_action( 'genesis_after_entry', 'after_entry_widget', 5 );
function after_entry_widget() {

	if ( ! is_singular( 'post' ) ) {
		return;
	}

	genesis_widget_area( 'after-entry', array(
		'before' => '<div class="after-entry widget-area"><div class="wrap">',
		'after'  => '</div></div>',
	) );

}



