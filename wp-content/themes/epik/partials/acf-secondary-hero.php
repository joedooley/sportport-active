<?php
/**
 * Default code for a ACF Flexible Content Secondary
 * Hero field.
 *
 * @package    SportPort Active
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */


$sec_hero_image = get_sub_field( 'sec_hero_image' );
$add_overlay = get_sub_field( 'add_overlay_content' );
$hero_overlay  = get_sub_field( 'hero_overlay' );

wp_localize_script(
	'backstretch',
	'SecondaryBackstretchHero',
	[
		'secondary_hero' => $sec_hero_image['url'],
	]
);

?>


<section class="secondary-hero <?php the_sub_field( 'css_class' ); ?>" style="background: url( <?php echo $sec_hero_image['url'] ?> ) no-repeat;">
	<div class="wrap">

		<?php

		if ( $add_overlay ) { ?>
			<div class="secondary-hero-overlay"><?php echo $hero_overlay; ?></div>
		<?php } ?>

	</div>
</section>




