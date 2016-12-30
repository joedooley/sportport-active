<?php
/**
 * Default code for a Hero Flexible Content field
 *
 * @package    SportPort Active
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */


$hero_image       = get_sub_field( 'hero_image' );
$add_hero_heading = get_sub_field( 'add_hero_heading' );
$add_two_buttons  = get_sub_field( 'add_two_buttons' );

wp_localize_script(
	'backstretch',
	'PrimaryBackstretchHero',
	[
		'primary_hero' => $hero_image['url'],
	]
);

?>


<section class="primary-hero <?php the_sub_field( 'css_class' ); ?>" style="background: url( <?php echo $hero_image['url'] ?> ) no-repeat;">
	<div class="wrap">

		<?php

		if ( $add_hero_heading ) {
			the_sub_field( 'hero_text' );
		}

		if ( get_sub_field( 'display_cta_button' ) ) { ?>

			<div class="cta-container">

				<a href="<?php the_sub_field( 'hero_cta_button_url_1' ) ?>"
				   class="button double-button"
				   target="blank"><?php echo the_sub_field( 'hero_cta_button_text_1' ); ?></a>

				<?php

				if ( $add_two_buttons ) { ?>

					<a href="<?php the_sub_field( 'hero_cta_button_url_2' ) ?>"
					   class="button double-button"
					   target="blank"><?php echo the_sub_field( 'hero_cta_button_text_2' ); ?></a>

				<?php } ?>

			</div>

		<?php } ?>

	</div>
</section>


