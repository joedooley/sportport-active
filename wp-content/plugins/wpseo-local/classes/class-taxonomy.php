<?php
/**
 * @package WPSEO_Local\Main
 * @since   1.3.2
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Taxonomy' ) ) {

	/**
	 * WPSEO_Local_Taxonomy class.
	 *
	 * Handles metaboxes/metadata for the Location categories custom taxonomy.
	 *
	 * @since   1.3.2
	 */
	class WPSEO_Local_Taxonomy {
		/**
		 * WPSEO_Local_Taxonomy constructor.
		 */
		function __construct() {
			if ( is_admin() && ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] !== '' ) && ( ! isset( $options[ 'hideeditbox-tax-' . $_GET['taxonomy'] ] ) || $options[ 'hideeditbox-tax-' . $_GET['taxonomy'] ] === false ) ) {
				add_action( 'wpseo_locations_category_edit_form', array( $this, 'term_seo_form' ), 10, 1 );
			}

			WPSEO_Taxonomy_Meta::$defaults_per_term['wpseo_local_custom_marker'] = '';
		}

		/**
		 * Show the SEO inputs for term.
		 *
		 * @param object $term Term to show the edit boxes for.
		 */
		function term_seo_form( $term ) {

			global $wpseo_taxonomy;

			$tax_meta = WPSEO_Taxonomy_Meta::get_term_meta( (int) $term->term_id, $term->taxonomy );

			echo '<h2>' . __( 'Local SEO Settings', 'yoast-local-seo' ) . '</h2>';
			echo '<table class="form-table wpseo-local-taxonomy-form">';
			echo '<tr class="form-field">';
			echo '<th scope="row">';
			echo '<label class="textinput" for="">' . __( 'Custom marker', 'yoast-local-seo' ) . ':</label>';
			echo '</th>';
			echo '<td>';
			echo '<img src="' . ( isset( $tax_meta['wpseo_local_custom_marker'] ) ? wp_get_attachment_url( $tax_meta['wpseo_local_custom_marker'] ) : '' ) . '" id="custom_marker" />';
			echo '<button class="set_custom_images button">' . __( 'Set custom marker image', 'yoast-local-seo' ) . '</button>';
			echo '<p class="description">' . __( 'A custom marker can be set per category. If no marker is set here, the global marker will be used.', 'yoast-local-seo' ) . '</p>';
			if ( isset( $tax_meta['wpseo_local_custom_marker'] ) && '' != $tax_meta['wpseo_local_custom_marker'] ) {
				echo '<br /><button id="remove_marker">' . __( 'Remove marker', 'yoast-local-seo' ) . '</button>';
			}
			echo '<input type="hidden" id="hidden_custom_marker" name="wpseo_local_custom_marker" value="' . ( ( isset( $tax_meta['wpseo_local_custom_marker'] ) && $tax_meta['wpseo_local_custom_marker'] !== '' ) ? esc_url( $tax_meta['wpseo_local_custom_marker'] ) : '' ) . '">';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
		}
	}
}

?>
