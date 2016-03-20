<?php
/**
 * This file contains markup for the homepage
 *
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

// Function displaying Flexible Content Field
//function sk_display_fc() {
//	// loop through the rows of data
//	while ( have_rows( 'flexible_content' ) ) : the_row();
//
//		// "Hero" Layout
//		if ( get_row_layout() == 'hero' ) {
//
//			$hero_image = get_sub_field( 'hero_image' ); ?>
<!---->
<!--			<section class="hero" style="background-image: url(--><?php //the_sub_field( 'hero_image' ); ?>/*);">*/
/*				<div class="dark-overlay">*/
/*					<div class="wrap">*/
/*						*/<?php //the_sub_field( 'hero_text' );
//						if ( get_sub_field( 'display_cta_button' ) ) { ?>
<!--							<a href="--><?php //the_sub_field( 'hero_cta_button_url' ) ?><!--" class="button">--><?php //echo the_sub_field( 'hero_cta_button_text' ); ?><!--</a>-->
<!--						--><?php //} ?>
<!--					</div>-->
<!--				</div>-->
<!--			</section>-->
<!---->
<!--			--><?php
//
//		} elseif ( get_row_layout() == 'image_-_text' ) { // "Image - Text" Layout
//
//			$left_image = get_sub_field( 'left_image' ); ?>
<!--			<section class="row image-text --><?php //the_sub_field( 'css_class' ); ?><!--">-->
<!--				<div class="left-half"><img src="--><?php //echo $left_image['url'] ?><!--" alt="--><?php //echo $left_image['alt'] ?><!--" /></div>-->
<!--				<div class="right-half">--><?php //the_sub_field( 'right_text' ); ?><!--</div>-->
<!--			</section>-->
<!---->
<!--		--><?php //} elseif ( get_row_layout() == 'text_-_image' ) { // "Text - Image" Layout
//
//			$right_image = get_sub_field( 'right_image' ); ?>
<!--			<section class="row text-image --><?php //the_sub_field( 'css_class' ); ?><!--">-->
<!--				<div class="left-half">--><?php //the_sub_field( 'left_text' ); ?><!--</div>-->
<!--				<div class="right-half"><img src="--><?php //echo $right_image['url'] ?><!--" alt="--><?php //echo $right_image['alt'] ?><!--" /></div>-->
<!--			</section>-->
<!---->
<!--		--><?php //}
//
//	endwhile;
//}
//
//add_action( 'get_header', 'sk_fc_check' );
//function sk_fc_check() {
//	// If "Flexible Content" field has rows of data
//	if ( have_rows( 'flexible_content' ) ) {
//		add_action( 'wp_enqueue_scripts', 'sk_flexbox_support_check' );
//
//		// Force full width content
//		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
//
//		// Remove the default Page content
//		remove_action( 'genesis_loop', 'genesis_do_loop' );
//
//		// Show Flexible Content field in the content area
//		add_action( 'genesis_loop', 'sk_display_fc' );
//
//		// Remove wrap from .site-inner
//		add_theme_support( 'genesis-structural-wraps', array(
//			'header',
//			'nav',
//			'subnav',
//			// 'site-inner',
//			'footer-widgets',
//			'footer',
//		) );
//
//
//	}
//}



genesis();
