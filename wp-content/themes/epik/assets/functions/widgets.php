<?php

// Before Header
genesis_register_sidebar( array(
	'id'            => 'before-header-left',
	'name'          => __( 'Before Header Left', 'epik' ),
	'description'   => __( 'This is the Before Header Left widget area', 'epik' ),

) );
genesis_register_sidebar( array(
	'id'            => 'before-header-right',
	'name'          => __( 'Before Header Right', 'epik' ),
	'description'   => __( 'This is the Before Header Right widget area', 'epik' ),

) );


// Before Footer Widgets
genesis_register_sidebar( array(
	'id'          => 'before-footer-left',
	'name'        => __( 'Before Footer Left', 'epik' ),
	'description' => __( 'This widget will show up the homepage above the footer widgets.', 'epik' ),
) );
genesis_register_sidebar( array(
	'id'          => 'before-footer-right',
	'name'        => __( 'Before Footer Right', 'epik' ),
	'description' => __( 'This widget will show up the homepage above the footer widgets.', 'epik' ),
) );


// After Entry
genesis_register_sidebar( array(
	'id'          => 'after-entry',
	'name'        => __( 'After Entry', 'epik' ),
	'description' => __( 'This widget will show up at the very end of each post.', 'epik' ),
) );

//* Hooks after-entry widget area to single posts
add_action( 'genesis_after_entry', function() {

	if ( ! is_singular( 'post' ) ) {
		return;
	}

	genesis_widget_area( 'after-entry', array(
		'before' => '<div class="after-entry widget-area"><div class="wrap">',
		'after'  => '</div></div>',
	) );

});



add_action( 'genesis_before_footer', function() {

	echo '<section class="above-footer-widgets"><div class="above-footer-container wrap">';

	genesis_widget_area( 'before-footer-left', array(
		'before' => '<div class="before-footer-left widget-area"><div class="wrap">',
		'after'  => '</div></div>',
	) );

	genesis_widget_area( 'before-footer-right', array(
		'before' => '<div class="before-footer-right widget-area"><div class="wrap">',
		'after'  => '</div></div>',
	) );

	echo '</div></section>';

}, 9);
