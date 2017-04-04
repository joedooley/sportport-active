<?php

/**
 * Provide a admin area form view for the plugin
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
$chart1_assets = get_post_meta( $post->ID, 'chart-1', false );
$chart1_title= sizeof($chart1_assets)>0 ? $chart1_assets[0]['chart-title'] : '';
$chart1_img= sizeof($chart1_assets)>0 ? $chart1_assets[0]['chart-image'] : '';
$chart1_img_position=sizeof($chart1_assets)>0 ? $chart1_assets[0]['chart-position'] : '';
$chart1_content=sizeof($chart1_assets)>0 ? $chart1_assets[0]['chart-content'] : '';
$chart1_table=sizeof($chart1_assets)>0 ? $chart1_assets[0]['chart-table'] : '';


		// Use get_post_meta to retrieve an existing value of chart 2 from the database.
$chart2_assets = get_post_meta( $post->ID, 'chart-2', false );

$chart2_title=sizeof($chart2_assets)>0 ? $chart2_assets[0]['chart-title-1'] : '';
$chart2_img=sizeof($chart2_assets)>0 ? $chart2_assets[0]['chart-image-1'] : '';
$chart2_img_position=sizeof($chart2_assets)>0 ? $chart2_assets[0]['chart-position-1'] : '';
$chart2_content=sizeof($chart2_assets)>0 ? $chart2_assets[0]['chart-content-1'] : '';
$chart2_table=sizeof($chart2_assets)>0 ? $chart2_assets[0]['chart-table-1'] : '';


		// Display the form, using the current value.
?>
<div id="productsize-chart-meta-fields" class="chart-1"> 
 <div class="title-wrap"> <h2>Chart 1</h2></div>
 <div id="field">
  <div class="field-title"><h4><?php _e( 'Chart Title', $this->plugin_name); ?></h4></div> 
  <div class="field-description"><?php _e( 'Add/Edit chart title below', $this->plugin_name); ?></div>
  <div class="field-item"><input type="text" id="chart-title-1" name="chart-title-1" value="<?php echo  $chart1_title ; ?>" /></div>
</div>

<div id="field">
  <div class="field-title"><h4><?php _e( 'Chart Image', $this->plugin_name); ?></h4></div> 
  <div class="field-description"><?php _e( 'Add/Edit chart image below', $this->plugin_name); ?></div>
  <div class="field-item"> 
   <input type="hidden" name="chart-image-1" id="chart-image-1" value="<?php echo $chart1_img; ?>" /></div>
   <?php 
   $img=wp_get_attachment_image_src($chart1_img,'thumbnail'); ?>
   <div id="field-image">
    <span style="display: <?php echo $img ? 'block' : 'none'; ?>;" class="_img_remove" data-placeholder="<?php echo plugins_url( 'images/chart-img-placeholder.jpg', dirname(__FILE__) ); ?>" data-id="primary-chart-image">×</span>
   <img class="chart_img" src="<?php echo !empty($img[0]) ? $img[0] :  plugins_url( 'images/chart-img-placeholder.jpg', dirname(__FILE__) ); ?>" width="<?php echo $img[1]; ?>" height="<?php echo $img[2]; ?>"  id="meta_img_1" /></div>
   <div class="field-item"><input type="button" id="meta-image-button-1" class="button" value="<?php _e( 'Upload', $this->plugin_name )?>" /></div>
 </div>
 

 <div id="field">
  <div class="field-title"><h4><?php _e( 'Image Position', $this->plugin_name); ?></h4></div> 
  <div class="field-description"><?php _e( 'Primary chart image position', $this->plugin_name); ?></div>
  <div class="field-item">
    <select name="image-position-1" id="image-position-1">
      <option value="left" <?php echo $chart1_img_position=="left" ? 'selected="selected"' : ''; ?> ><?php _e( 'Left',$this->plugin_name); ?></option>
      <option value="right" <?php echo $chart1_img_position=="right" ? 'selected="selected"' : ''; ?>><?php _e( 'Right',$this->plugin_name); ?></option>
    </select>
  </div>
</div>


<div id="field">
  <div class="field-title"><h4><?php _e( 'Content', $this->plugin_name); ?></h4></div> 
  <div class="field-description"><?php _e( 'Chart 1 content', $this->plugin_name); ?></div>
  <div class="field-item">
   <?php
   wp_editor( $chart1_content, 'chart-content-1',array('editor_height'=>200) );
   ?>
 </div>
</div>
<div id="field">
 <div class="field-title"><h4><?php _e( 'Chart Table', $this->plugin_name); ?></h4></div> 
 <div class="field-description"><?php _e( 'Add/Edit chart below', $this->plugin_name); ?></div>
 <div class="field-item">
  <textarea  id="chart-table-1" name="chart-table-1"><?php echo $chart1_table  ; ?></textarea></div>
</div>

</div>
<div id="productsize-chart-meta-fields" class="chart-2"> 
 <div class="title-wrap"> <h2>Chart 2</h2></div>
 <div id="field">
  <div class="field-title"><h4><?php _e( 'Chart Title', $this->plugin_name); ?></h4></div> 
  <div class="field-description"><?php _e( 'Add/Edit chart title below', $this->plugin_name); ?></div>
  <div class="field-item"><input type="text" id="chart-title-2" name="chart-title-2" value="<?php echo $chart2_title  ; ?>" /></div>
</div>

<div id="field">
  <div class="field-title"><h4><?php _e( 'Chart Image', $this->plugin_name); ?></h4></div> 
  <div class="field-description"><?php _e( 'Add/Edit chart image below', $this->plugin_name); ?></div>
  <div class="field-item"> 
   <input type="hidden" name="chart-image-2" id="chart-image-2" value="<?php $chart2_img; ?>" /></div>
   <?php 
   $img=wp_get_attachment_image_src($chart2_img,'thumbnail'); ?>
   <div id="field-image">
     <span style="display: <?php echo $img ? 'block' : 'none'; ?>;" class="_img_remove" data-placeholder="<?php echo plugins_url( 'images/chart-img-placeholder.jpg', dirname(__FILE__) ); ?>" data-id="primary-chart-image">×</span>
     <img class="chart_img" src="<?php echo !empty($img[0]) ? $img[0] : plugins_url( 'images/chart-img-placeholder.jpg', dirname(__FILE__) ); ?>" width="<?php echo $img[1]; ?>" height="<?php echo $img[2]; ?>" id="meta_img_2" /></div>
     <div class="field-item"><input type="button" id="meta-image-button-2" class="button" value="<?php _e( 'Upload', $this->plugin_name )?>" /></div>
   </div>
   <div id="field">
    <div class="field-title"><h4><?php _e( 'Image Position', $this->plugin_name); ?></h4></div> 
    <div class="field-description"><?php _e( 'Primary chart image position', $this->plugin_name); ?></div>
    <div class="field-item">
      <select name="image-position-2" id="image-position-2">
        <option value="left" <?php echo $chart2_img_position=="left" ? 'selected="selected"' : ''; ?> ><?php _e( 'Left',$this->plugin_name); ?></option>
        <option value="right" <?php echo $chart2_img_position=="right" ? 'selected="selected"' : ''; ?>><?php _e( 'Right',$this->plugin_name); ?></option>
      </select>
    </div>
  </div>
  <div id="field">
    <div class="field-title"><h4><?php _e( 'Content', $this->plugin_name); ?></h4></div> 
    <div class="field-description"><?php _e( 'Chart 2 content', $this->plugin_name); ?></div>
    <div class="field-item">
     <?php
     wp_editor( $chart2_content, 'chart-content-2',array('editor_height'=>200) );
     ?>
   </div>
 </div>
 <div id="field">
   <div class="field-title"><h4><?php _e( 'Chart Table', $this->plugin_name); ?></h4></div> 
   <div class="field-description"><?php _e( 'Add/Edit chart below', $this->plugin_name); ?></div>
   <div class="field-item">
    <textarea  id="chart-table-2" name="chart-table-2"><?php echo  $chart2_table ; ?></textarea></div>
  </div>
</div>