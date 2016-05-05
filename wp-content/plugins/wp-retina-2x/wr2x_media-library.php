<?php

add_filter( 'manage_media_columns', 'wr2x_manage_media_columns' );
add_action( 'manage_media_custom_column', 'wr2x_manage_media_custom_column', 10, 2 );

/**
 *
 * MEDIA LIBRARY
 *
 */

function wr2x_manage_media_columns( $cols ) {
	$cols["Retina"] = "Retina";
	return $cols;
}

function wr2x_manage_media_custom_column( $column_name, $id ) {
	if ( $column_name == 'Retina' ) {
    echo '<div id="wr2x-info-' . $id . '">';
    $info = wr2x_retina_info( $id );
    echo wpr2x_html_get_basic_retina_info( $id, $info );
    echo '</div>';
    echo "<a style='position: relative; top: -1px; margin-left: 4px;' onclick='wr2x_generate(" . $id . ", true)' id='wr2x_generate_button_" . $id . "' class='wr2x-button'>" . __( "GENERATE", 'wp-retina-2x' ) . "</a>";
  }
  else if ( $column_name == 'Retina-Actions' ) {
  }
}

?>
