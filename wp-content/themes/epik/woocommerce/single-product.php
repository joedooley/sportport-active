<?php
/**
 * This template displays the single Product
 *
 * @package genesis_connect_woocommerce
 * @version 0.9.8
 *
 * Note for customisers/users: Do not edit this file!
 * ==================================================
 * If you want to customise this template, copy this file (keep same name) and place the
 * copy in the child theme's woocommerce folder, ie themes/my-child-theme/woocommerce
 * (Your theme may not have a 'woocommerce' folder, in which case create one.)
 * The version in the child theme's woocommerce folder will override this template, and
 *
 *
 * any future updates to this plugin won't wipe out your customisations.
 *
 */

/** Remove default Genesis loop */
remove_action( 'genesis_loop', 'genesis_do_loop' );

/** Remove WooCommerce breadcrumbs */
//remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/** Uncomment the below line of code to add back WooCommerce breadcrumbs */
//add_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb', 10, 0 );

remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
add_action( 'woocommerce_single_product_summary', 'genesis_do_breadcrumbs', 4 );

/** Remove Woo #container and #content divs */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );


/**
 * Outputs ACF Repeator for Accordion.
 */
function acf_accordion() {

	if ( have_rows( 'accordion' ) ) :

		echo '<div id="accordion">';

		while ( have_rows( 'accordion' ) ) : the_row();

			$heading = get_sub_field( 'header' );
			$content = get_sub_field( 'hidden_content' ); ?>

				<div class="accordion-item">

				<?php if ( $heading ) : ?>
					<h2 class="accordion-heading heading"><?php echo $heading; ?></h2>
				<?php endif; ?>

				<div class="accordion-content"><?php echo $content; ?></div>

				</div>

			<?php

			endwhile;

		echo '</div>';

	endif;

}


/**
 * Enqueue single page script accordion.js
 *
 * @return     void
 */
add_action( 'wp_enqueue_scripts', function() {

	if ( is_product() ) {

		wp_enqueue_script(
			'accordion-js',
			get_stylesheet_directory_uri() . '/assets/js/custom/single/accordion.js',
			array( 'jquery' ),
			CHILD_THEME_VERSION,
			true
		);

		if ( ! wp_is_mobile() ) {

			wp_enqueue_script(
				'scrolltofixed-init',
				get_stylesheet_directory_uri() . '/assets/js/custom/single/scrolltofixed-init.js',
				array( 'jquery' ),
				CHILD_THEME_VERSION,
				true
			);

		}

	}
});


add_action( 'genesis_loop', 'gencwooc_single_product_loop' );
/**
 * Displays single product loop
 *
 * Uses WooCommerce structure and contains all existing WooCommerce hooks
 *
 * Code based on WooCommerce 1.5.5 woocommerce_single_product_content()
 * @see woocommerce/woocommerce-template.php
 *
 * @since 0.9.0
 */
function gencwooc_single_product_loop() {

	do_action( 'woocommerce_before_main_content' );

	// Let developers override the query used, in case they want to use this function for their own loop/wp_query
	$wc_query = false;

	// Added a hook for developers in case they need to modify the query
	$wc_query = apply_filters( 'gencwooc_custom_query', $wc_query );

	if ( ! $wc_query) {

		global $wp_query;

		$wc_query = $wp_query;
	}

	if ( $wc_query->have_posts() ) while ( $wc_query->have_posts() ) : $wc_query->the_post(); ?>

		<?php do_action('woocommerce_before_single_product'); ?>

		<div itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

			<?php do_action( 'woocommerce_before_single_product_summary' ); ?>

			<div class="summary">
				<div class="product-essential">

				<?php do_action( 'woocommerce_single_product_summary' ); ?>

				<?php echo acf_accordion(); ?>

				<?php wc_get_template_part( 'product', 'sociallinks' ); ?>

			<?php do_action( 'woocommerce_after_single_product_summary' ); ?>

				</div>
			</div>
		</div>

		<?php do_action( 'woocommerce_after_single_product' );

	endwhile;

	do_action( 'woocommerce_after_main_content' );
}

genesis();