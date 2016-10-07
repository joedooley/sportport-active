<?php
/**
 * Default code for a ACF Flexible Content Four
 * Featured Posts field
 *
 * @package    SportPort Active
 * @author     Developing Designs - Joe Dooley
 * @link       https://www.developingdesigns.com
 * @copyright  Joe Dooley, Developing Designs
 * @license    GPL-2.0+
 */


$posts = get_sub_field( 'featured_posts' );
$add_heading = get_sub_field( 'add_section_heading' );

?>


<section class="row  <?php the_sub_field( 'css_class' ); ?>">
	<div class="wrap">

	<?php

	if ( $add_heading ) {
		the_sub_field( 'section_heading' );
	}

	if ( $posts ) { ?>

		<div class = "article-container">
			<?php foreach ( $posts as $p ) : ?>

				<article class = "featured-posts">
					<a href = "<?php echo the_permalink( $p->ID, 'featured-posts' ); ?>">
						<?php echo get_the_post_thumbnail( $p->ID, 'featured-posts' ); ?>
						<h2 class = "featured-posts-overlay"><?php the_field( 'featured_image_overlay', $p ); ?></h2></a>
				</article>

			<?php endforeach; ?>
		</div>

	<?php } ?>

	</div>
</section>


