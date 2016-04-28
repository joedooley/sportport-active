 <?php 
 /**
 * The public-facing functionality of the plugin.
 *
 * @package    productsize-chart-for-woocommerce
 * @subpackage productsize-chart-for-woocommerce/public/includes
 * @author     Nabaraj Chapagain <nabarajc6@gmail.com>
 */
 $title_wrapper=$this->default_assets['productsize-chart-title'];
 $enable_additional_chart= $this->default_assets['productsize-chart-enable-additional-chart'];
 
 ?>
 <<?php echo !empty($title_wrapper) ? $title_wrapper : 'h2'; ?> id="modal1Title"><?php _e($assets['label'],$this->plugin_name); ?></<?php  echo !empty($title_wrapper) ? $title_wrapper : 'h2'; ?>>
	<?php  $post_data=get_post($chart_id);  
	$pimg=get_post_meta($post_data->ID,'primary-chart-image',true);
	if($pimg):
	$position=get_post_meta($post_data->ID,'primary-image-position',true);
	$pimg=wp_get_attachment_image_src($pimg,'full'); 
	 ?>
    <div class="chart-1-image image-<?php echo $position; ?>">
    <img src="<?php echo esc_attr($pimg[0]); ?> " alt="<?php esc_attr(__($post->post_title,$this->plugin_name)); ?>"
     title="<?php esc_attr($title); ?>" />
    </div> 
    <?php endif; ?>
    <?php
	 if($post->post_content):
	   $content = apply_filters('the_content', $post_data->post_content); 
	   echo $content;   
	   endif;	
	 ?>
   
    <?php

		if($assets['chart-table']):
			$this->productsize_chart_display_table($assets['chart-table']); ?>
   		<?php endif; ?>
         
    <?php // chart 1 content goes here 
	if($enable_additional_chart==1):
	$title_additional=$this->default_assets['productsize-chart-additional-title'];
	if($assets['chart-1']):
	 $title=$assets['chart-1'][0]['chart-title'];
	 $image=$assets['chart-1'][0]['chart-image'];
	 $content=$assets['chart-1'][0]['chart-content'];
	 $position=$assets['chart-1'][0]['image-position']=='left' ? 'image-left' : 'image-right';
	 $chart=$assets['chart-1'][0]['chart-table'];
	 ?>
    <div class="add-chart-1">
    <<?php echo !empty($title_additional) ? $title_additional : 'h3'; ?> id="modal1Title"><?php _e($title,$this->plugin_name); ?></<?php echo !empty($title_additional) ? $title_additional : 'h3';  ?>>
    <?php if($image): ?>
	<?php $img=wp_get_attachment_image_src($image,'full'); ?>
    <div class="chart-1-image <?php echo $position; ?>"><img src="<?php echo esc_attr($img[0]); ?> " alt="<?php esc_attr($title); ?>" 
    title="<?php esc_attr($title); ?>" />
    </div>
    <?php endif; ?>
    <?php echo apply_filters('the_content',$content); ?>
    <?php $this->productsize_chart_display_table($chart); ?>
     </div>
     <div class="clear"></div>
     <?php endif; ?>
     <?php // chart 2 content goes here ?>
     <?php
	 if($assets['chart-2']):
	 $title=$assets['chart-2'][0]['chart-title-1'];
	 $image=$assets['chart-2'][0]['chart-image-1'];
	 $content=$assets['chart-2'][0]['chart-content-1'];
	 $position=$assets['chart-2'][0]['image-position-1']=='left' ? 'image-left' : 'image-right';
	 $chart=$assets['chart-2'][0]['chart-table-1'];
	 ?>
     <div class="add-chart-2">
     <<?php echo !empty($title_additional) ? $title_additional : 'h3'; ?> id="modal1Title"><?php _e($title,$this->plugin_name); ?></<?php echo !empty($title_additional) ? $title_additional : 'h3'; ?>>
      <?php if($image): ?>
	 <?php $img=wp_get_attachment_image_src($image,'full'); ?>
     <div class="chart-2-image <?php echo $position; ?>"><img src="<?php echo esc_attr($img[0]); ?> " alt="<?php esc_attr($title); ?>" 
     title="<?php esc_attr($title); ?>" />
     </div>
     <?php endif; ?>
      <?php echo apply_filters('the_content',$content); ?>
     <?php $this->productsize_chart_display_table($chart); ?>
     </div>
     <?php endif; ?>
     <?php endif; ?>
	