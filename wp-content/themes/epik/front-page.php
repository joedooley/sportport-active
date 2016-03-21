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

			global $hero_image;
			$hero_image = get_sub_field( 'hero_image' ); ?>

			<section class="hero" style="background: url(<?php the_sub_field( 'hero_image' ); ?>) no-repeat;">

				<div class="wrap">
					<?php the_sub_field( 'hero_text' );
					if ( get_sub_field( 'display_cta_button' ) ) { ?>
						<a href="<?php the_sub_field( 'hero_cta_button_url_1' ) ?>" class="button"><?php echo the_sub_field( 'hero_cta_button_text_1' ); ?></a>
						<a href="<?php the_sub_field( 'hero_cta_button_url_2' ) ?>" class="button"><?php echo the_sub_field( 'hero_cta_button_text_2' ); ?></a>
					<?php } ?>
				</div>

			</section>

			<?php

		} elseif ( get_row_layout() == 'two_columns' ) {

			$left_image = get_sub_field( 'left_image' );
			$right_image = get_sub_field( 'right_image' ); ?>

			<section class="row  <?php the_sub_field( 'css_class' ); ?>">
				<div class="wrap">

				<?php the_sub_field( 'section_heading' ); ?>

						<div class="one-half first  featured-image" style="background: url( <?php echo $left_image['url'] ?> ) no-repeat;"><?php the_sub_field( 'left_text' ); ?></div>
						<div class="one-half  featured-image" style="background: url( <?php echo $right_image['url'] ?> ) no-repeat;"><?php the_sub_field( 'right_text' ); ?></div>


				</div>
			</section>

		<?php } elseif ( get_row_layout() == 'four_featured_posts' ) {

			$posts = get_sub_field( 'featured_posts' ); ?>

			<section class="row  <?php the_sub_field( 'css_class' ); ?>">
				<div class="wrap">

					<?php the_sub_field( 'section_heading' );

					if ( $posts ) : ?>

							<div class="article-container">
								<?php foreach ( $posts as $p ) : ?>
									<article class="featured-posts">
										<?php echo get_the_post_thumbnail( $p->ID, 'featured-posts' ); ?>
									</article>
								<?php endforeach; ?>
							</div>

					<?php endif; ?>

				</div>
			</section>

		<?php } elseif ( get_row_layout() == 'four_columns' ) { ?>

			<section class="row  <?php the_sub_field( 'css_class' ); ?>">
				<div class="wrap">

					<?php the_sub_field( 'section_heading' ); ?>

					<div class="featured-boxes">
						<div class="one-fourth  first"><?php the_sub_field( 'first_column' ); ?></div>
						<div class="one-fourth"><?php the_sub_field( 'second_column' ); ?></div>
						<div class="one-fourth"><?php the_sub_field( 'third_column' ); ?></div>
						<div class="one-fourth"><?php the_sub_field( 'fourth_column' ); ?></div>
					</div>
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

	global $hero_image;
	$backstretch_src = $hero_image;

	wp_localize_script( 'backstretch-set', 'BackStretchImg', $backstretch_src );

});



genesis();
