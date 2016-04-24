<?php
/**
 * This file contains markup for the homepage
 *
 * @author     Joe Dooley
 * @package    SportPort Active Theme
 * @subpackage Customizations
 */

// Function displaying Flexible Content Field
function spa_display_fc() {
	// loop through the rows of data
	while ( have_rows( 'flexible_content' ) ) : the_row();

		// "Hero" Layout
		if ( get_row_layout() == 'hero' ) {


			//global $hero_image;
			$hero_image = get_sub_field( 'hero_image' );
			?>

			<section class="hero" style="background: url( <?php echo $hero_image['url'] ?> ) no-repeat;">

				<div class="wrap">
					<?php the_sub_field( 'hero_text' );
					if ( get_sub_field( 'display_cta_button' ) ) { ?>
						<a href="<?php the_sub_field( 'hero_cta_button_url_1' ) ?>" class="button  double-button"><?php echo the_sub_field( 'hero_cta_button_text_1' ); ?></a>
						<a href="<?php the_sub_field( 'hero_cta_button_url_2' ) ?>" class="button  double-button"><?php echo the_sub_field( 'hero_cta_button_text_2' ); ?></a>
					<?php } ?>
				</div>

			</section>

		<?php } elseif ( get_row_layout() == 'two_columns' ) {

			$left_image = get_sub_field( 'left_image' );
			$right_image = get_sub_field( 'right_image' );

			wp_localize_script(
				'backstretch-set',
				'BackStretchImg',
				[
					'hero'          => $hero_image['url'],
					'featuredLeft'  => $left_image['url'],
					'featuredRight' => $right_image['url'],
				]
			);



			?>

			<section class="row  <?php the_sub_field( 'css_class' ); ?>">

				<?php the_sub_field( 'section_heading' ); ?>

				<div class="featured-images">
					<div class="featured-image featured-image-left" style="background: url( <?php echo $left_image['url'] ?> ) no-repeat;"><a href="<?php the_sub_field( 'links_to_1' ) ?>"><?php the_sub_field( 'left_text' ); ?></a></div>
					<div class="featured-image featured-image-right" style="background: url( <?php echo $right_image['url'] ?> ) no-repeat;"><a href="<?php the_sub_field( 'links_to_2' ) ?>"><?php the_sub_field( 'right_text' ); ?></a></div>
				</div>

			</section>

		<?php } elseif ( get_row_layout() == 'four_featured_posts' ) {

			$posts = get_sub_field( 'featured_posts' ); ?>

			<section class="row  <?php the_sub_field( 'css_class' ); ?>">

					<?php the_sub_field( 'section_heading' );

					if ( $posts ) : ?>

							<div class="article-container">
								<?php foreach ( $posts as $p ) : ?>


										<article class="featured-posts">
											<a href="<?php echo the_permalink( $p->ID, 'featured-posts' ); ?>">
												<?php echo get_the_post_thumbnail( $p->ID, 'featured-posts' ); ?>
											<h2 class="image-overlay  featured-post--overlay"><?php the_field( 'featured_image_overlay', $p ); ?></h2></a>
										</article>

								<?php endforeach; ?>
							</div>

					<?php endif; ?>

			</section>

		<?php } elseif ( get_row_layout() == 'four_columns' ) { ?>

			<section class="row  <?php the_sub_field( 'css_class' ); ?>">

					<?php the_sub_field( 'section_heading' ); ?>

					<div class="featured-links">
						<?php the_sub_field( 'first_column' ); ?>
						<?php the_sub_field( 'second_column' ); ?>
						<?php the_sub_field( 'third_column' ); ?>
						<?php the_sub_field( 'fourth_column' ); ?>
					</div>
			</section>

		<?php }

	endwhile;
}

add_action( 'get_header', 'spa_fc_check' );
function spa_fc_check() {
	// If "Flexible Content" field has rows of data
	if ( have_rows( 'flexible_content' ) ) {

		// Force full width content
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

		// Remove the default Page content
		remove_action( 'genesis_loop', 'genesis_do_loop' );

		// Show Flexible Content field in the content area
		add_action( 'genesis_loop', 'spa_display_fc' );

		// Remove wrap from .site-inner
		add_theme_support( 'genesis-structural-wraps', array(
			'header',
			'nav',
			'subnav',
			// 'site-inner',
			'footer-widgets',
			'footer',
		) );


	}
}



add_action( 'wp_enqueue_scripts', function() {

	wp_enqueue_script(
		'backstretch',
		get_stylesheet_directory_uri() . '/assets/js/vendors/single/jquery.backstretch.min.js',
		array( 'jquery' ),
		'2.0.4',
		true
	);

	wp_enqueue_script(
		'backstretch-set',
		get_stylesheet_directory_uri() . '/assets/js/custom/single/backstretch-set.js',
		array( 'jquery', 'backstretch' ),
		CHILD_THEME_VERSION,
		true
	);

});



genesis();
