<?php

/**
 * Provide a admin area form view for meta fields
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

 		// Add an nonce field so we can check for it later.
wp_nonce_field( 'productsize_chart_inner_custom_box', 'productsize_chart_inner_custom_box' );

		// Use get_post_meta to retrieve an existing value from the database.e
$chart_label = get_post_meta( $post->ID, 'label', true );
$chart_img = get_post_meta( $post->ID, 'primary-chart-image', true );
$chart_img_position = get_post_meta( $post->ID, 'primary-image-position', true );
$title_color = get_post_meta( $post->ID, 'title-color', true );
$text_color = get_post_meta( $post->ID, 'text-color', true );
$overlay_color = get_post_meta( $post->ID, 'overlay-color', true );
$table_style = get_post_meta( $post->ID, 'table-style', true );
$chart_padding = get_post_meta( $post->ID, 'chart-padding', true );
$chart_position = get_post_meta( $post->ID, 'position', true );
$button_position = get_post_meta( $post->ID, 'button-position', true );
$chart_categories = (array) get_post_meta( $post->ID, 'chart-categories', true );
$chart_table = get_post_meta( $post->ID, 'chart-table', true );

?>
<div id="productsize-chart-meta-fields" class="chart-0"> 
  <div id="field">
    <div class="field-title"><h4><?php _e( 'Label', $this->plugin_name); ?></h4></div> 
    <div class="field-description"><?php _e( 'Chart Label', $this->plugin_name); ?></div>
    <div class="field-item"><input type="text" id="label" name="label" value="<?php echo  $chart_label; ?>" /></div>
  </div>
  <div id="field">
    <div class="field-title"><h4><?php _e( 'Primary Chart Image', $this->plugin_name); ?></h4></div> 
    <div class="field-description"><?php _e( 'Add/Edit primary chart image below', $this->plugin_name); ?></div>
    <div class="field-item"> 
     <input type="hidden" name="primary-chart-image" id="primary-chart-image" value="<?php echo $chart_img; ?>" /></div>
     <?php 
     $img=wp_get_attachment_image_src($chart_img,'thumbnail'); ?>
     <div id="field-image">
        <span style="display: <?php echo $img ? 'block' : 'none'; ?>;" class="_img_remove" data-placeholder="<?php echo plugins_url( 'images/chart-img-placeholder.jpg', dirname(__FILE__) ); ?>" data-id="primary-chart-image">×</span>
     <img class="chart_img" src="<?php echo !empty($img[0]) ? $img[0] :  plugins_url( 'images/chart-img-placeholder.jpg', dirname(__FILE__) ); ?>" width="<?php echo $img[1]; ?>" height="<?php echo $img[2]; ?>"  id="meta_img" /></div>
     <div class="field-item"><input type="button" id="meta-image-button" class="button" value="<?php _e( 'Upload', $this->plugin_name )?>" /></div>
   </div>
   <div id="field">
    <div class="field-title"><h4><?php _e( 'Image Position', $this->plugin_name); ?></h4></div> 
    <div class="field-description"><?php _e( 'Primary chart mage position', $this->plugin_name); ?></div>
    <div class="field-item">
      <select name="primary-image-position" id="primary-image-position">
        <option value="left" <?php echo $chart_img_position=="left" ? 'selected="selected"' : ''; ?> ><?php _e( 'Left',$this->plugin_name); ?></option>
        <option value="right" <?php echo $chart_img_position=="right" ? 'selected="selected"' : ''; ?>><?php _e( 'Right',$this->plugin_name); ?></option>
      </select>
    </div>
  </div>
  <div id="field">
    <div class="field-title"><h4><?php _e( 'Title Color', $this->plugin_name); ?></h4></div> 
    <div class="field-description"><?php _e( 'Pick text color for the chart text', $this->plugin_name); ?></div>
    <div class="field-item"><input type="text" id="title-color" name="title-color" value="<?php echo  $title_color; ?>" /></div>
  </div>
  <div id="field">
    <div class="field-title"><h4><?php _e( 'Text Color', $this->plugin_name); ?></h4></div> 
    <div class="field-description"><?php _e( 'Pick text color for the chart text', $this->plugin_name); ?></div>
    <div class="field-item"><input type="text" id="text-color" name="text-color" value="<?php echo  $text_color; ?>" /></div>
  </div>
  <div id="field">
   <div class="field-title"><h4><?php _e( 'Overlay Color', $this->plugin_name); ?></h4></div> 
   <div class="field-description"><?php _e( 'Pick overlay background color for modal', $this->plugin_name); ?></div>
   <div class="field-item">
    <input type="text" id="overlay-color" name="overlay-color" value="<?php echo  $overlay_color; ?>" /></div>
  </div>
  <div id="field">
   <div class="field-title"><h4><?php _e( 'Chart Table Style', $this->plugin_name); ?></h4></div> 
   <div class="field-description"><?php _e( 'Chart Table Styles (Default Style 1)', $this->plugin_name); ?></div>
   <div class="field-item">
     <select name="table-style" id="table-style">
      <option value="style-1" <?php echo $table_style=="style-1" ? 'selected="selected"' : ''; ?> ><?php _e( 'Style 1',$this->plugin_name); ?></option>
      <option value="style-2" <?php echo $table_style=="style-2" ? 'selected="selected"' : ''; ?>><?php _e( 'Style 2',$this->plugin_name); ?></option>
    </select>
  </div>
</div>
<div id="field">
 <div class="field-title"><h4><?php _e( 'Padding (e.g. 10px)', $this->plugin_name); ?></h4></div> 
 <div class="field-description"><?php _e( 'Pick overlay background color for modal', $this->plugin_name); ?></div>
 <div class="field-item">
  <input type="text" id="chart-padding" name="chart-padding" value="<?php echo  $chart_padding; ?>" /></div>
</div>
<div id="field">
 <div class="field-title"><h4><?php _e( 'Chart Position', $this->plugin_name); ?></h4></div> 
 <div class="field-description"><?php _e( 'Select if the chart will display as a popup or as a additional tab', $this->plugin_name); ?></div>
 <div class="field-item">
  <select name="position" id="position">
    <option value="tab" <?php echo $chart_position=="tab" ? 'selected="selected"' : ''; ?> ><?php _e( 'Additional Tab',$this->plugin_name); ?></option>
    <option value="popup" <?php echo $chart_position=="popup" ? 'selected="selected"' : ''; ?>><?php _e( 'Modal Pop Up',$this->plugin_name); ?></option>
  </select>
</div>
</div>
<div style="clear:both"></div>
<div id="field" class="tab-or-modal" <?php echo $chart_position=="tab" ? "style='display:none;'" : ""; ?>>
 <div class="field-title"><h4><?php _e( 'Chart Popup Button Position', $this->plugin_name); ?></h4></div> 
 <div class="field-description"><?php _e( 'Select where the pop up button displays', $this->plugin_name); ?></div>
 <div class="field-item">
  <select name="button-position" id="button-position">
    <option value="before-summary-text" <?php echo $button_position=="before-summary-text" ? 'selected="selected"' : ''; ?> ><?php _e( 'Before Summary Text',$this->plugin_name); ?></option>
    <option  value="after-add-to-cart" <?php echo $button_position=="after-add-to-cart" ? 'selected="selected"' : ''; ?>><?php _e( 'After Add to Cart',$this->plugin_name); ?></option>
    <option value="before-add-to-cart" <?php echo $button_position=="before-add-to-cart" ? 'selected="selected"' : ''; ?>><?php _e( 'Before Add to Cart',$this->plugin_name); ?></option>
    <option value="after-product-meta" <?php echo $button_position=="after-product-meta" ? 'selected="selected"' : ''; ?>><?php _e( 'After Product Meta',$this->plugin_name); ?></option>
  </select></div>
</div>
<div id="field">
 <div class="field-title"><h4><?php _e( 'Chart Categories', $this->plugin_name); ?></h4></div> 
 <div class="field-description"><?php _e( 'Select categories for chart to appear on.', $this->plugin_name); ?></div>
 <div class="field-item">
  <select name="chart-categories[]" id="chart-categories" multiple="multiple" >
    <?php $term=get_terms( 'product_cat', array() ); ?>
    <?php if($term): foreach($term as $cat){ ?>
      <option value="<?php echo $cat->term_id; ?>" <?php echo in_array($cat->term_id,$chart_categories) ? 'selected="selected"' : ''; ?> ><?php _e( $cat->name,$this->plugin_name); ?></option>
      <?php } endif;?>

    </select></div>
  </div>
  <div id="field">
   <div class="field-title"><h4><?php _e( 'Chart Table', $this->plugin_name); ?></h4></div> 
   <div class="field-description"><?php _e( 'Add/Edit chart below', $this->plugin_name); ?></div>
   <div class="field-item">
    <textarea  id="chart-table" name="chart-table"><?php echo  $chart_table; ?></textarea></div>
  </div>

</div>