<?php

/**
 * This section is to display available charts in the woocommerce product page
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    productsize-chart-for-woocommerce
 * @subpackage productsize-chart-for-woocommerce/admin/includes
 */

if(!defined('ABSPATH'))
{
  exit; //exit if accessed directly
}

	// Use get_post_meta to retrieve an existing value of chart 1 from the database.
$chart_id= get_post_meta( $post->ID, 'prod-chart', true );

$args = array(
	'posts_per_page'   => -1,
	'offset'           => 0,
	'category'         => '',
	'category_name'    => '',
	'orderby'          => 'date',
	'order'            => 'DESC',
	'include'          => '',
	'exclude'          => '',
	'meta_key'         => '',
	'meta_value'       => '',
	'post_type'        => 'chart',
	'post_mime_type'   => '',
	'post_parent'      => '',
	'author'	   => '',
	'post_status'      => 'publish',
	'suppress_filters' => true 
	);
$posts_array = get_posts( $args ); 
if(!is_array($posts_array) || sizeof($posts_array)==0):
	printf(__('No charts found.click <a href="%s">here</a> to create you first chart',$this->plugin_name),admin_url('edit.php?post_type=chart'));
return;
endif
?>
<div id="productsize-chart-meta-fields"> 
	<div id="field">
		<div class="field-item">
			<select name="prod-chart" id="prod-chart">
				<option value=""><?php _e('Select Chart',$this->plugin_name); ?></option>
				<?php foreach($posts_array as $posts){ ?>
					<option value="<?php echo $posts->ID; ?>" <?php echo $posts->ID==$chart_id ? 'selected="selected"' : ''; ?> ><?php _e( $posts->post_title,$this->plugin_name); ?></option>
					<?php } ?>
				</select>
			</div>
		</div> 
	</div>
	<?php 	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'productsize_chart_select_custom_box', 'productsize_chart_select_custom_box' );
	?>