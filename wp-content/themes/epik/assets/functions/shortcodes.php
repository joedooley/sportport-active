<?php
/**
 * This file contains functions for shortcodes.php
 *
 * @package     ${NAMESPACE}
 * @since       1.0.0
 * @author      Joe Dooley <hello@developingdesigns.com>
 * @link        https://www.developingdesigns.com/
 * @license     GNU General Public License 2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_shortcode( 'limited_post_categories', 'spa_limited_post_categories' );
/**
 * Post categories shortcode to limit categories to one
 *
 * @param $atts
 *
 * @return mixed|string|void
 */
function spa_limited_post_categories( $atts ) {
	global $wp_rewrite;

	$thelist = '';
	$rel     = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';

	$defaults = array(
		'sep'     => ', ',
		'before'  => __( 'Filed Under: ', 'genesis' ),
		'after'   => '',
		'limit'   => 1,
		'exclude' => '',
	);

	$atts = shortcode_atts( $defaults, $atts, 'post_categories' );

	//* Fetching the categories of current post
	$categories = get_the_terms( false, 'category' );
	if ( ! $categories || is_wp_error( $categories ) ) {
		$categories = array();
	}

	$categories = array_values( $categories );

	foreach ( array_keys( $categories ) as $key ) {
		//* excluding some terms from array
		if ( ! empty( $atts['exclude'] ) || $atts['exclude'] != '' ) {
			$exclude_cats = explode( ',', $atts['exclude'] );
			if ( in_array( $categories[ $key ]->term_id, (array) $exclude_cats ) ) {
				unset( $categories[ $key ] );
				continue;
			}
		}

		_make_cat_compat( $categories[ $key ] );

	}

	//* Removing the extra portion
	$cats = array_splice( $categories, 0, $atts['limit'] );

	//* Do nothing if no cats
	if ( ! $cats ) {
		return '';
	}

	//* Making the list
	$i = 0;
	foreach ( $cats as $category ) {
		if ( 0 < $i ) {
			$thelist .= $atts['sep'];
		}

		$thelist .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" ' . $rel . '>' . $category->name . '</a>';

		++ $i;
	}

	if ( genesis_html5() ) {
		$output = sprintf( '<span %s>', genesis_attr( 'entry-categories' ) ) . $atts['before'] . $thelist . $atts['after'] . '</span>';
	} else {
		$output = '<span class="categories">' . $atts['before'] . $thelist . $atts['after'] . '</span>';
	}

	return apply_filters( 'genesis_post_categories_shortcode', $output, $atts );
}
