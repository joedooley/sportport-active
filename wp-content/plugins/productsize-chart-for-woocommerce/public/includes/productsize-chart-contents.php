 <?php 
 /**
 * The public-facing functionality of the plugin.
 *
 * @package    productsize-chart-for-woocommerce
 * @subpackage productsize-chart-for-woocommerce/public/includes
 * @author     Nabaraj Chapagain <nabarajc6@gmail.com>
 */

 if(!defined('ABSPATH'))
 {
  exit; //exit if accessed directly
}

$title_wrapper=$this->default_assets['productsize-chart-title'];
$title=!empty($title_wrapper) ? $title_wrapper : "h2";
$enable_additional_chart= $this->default_assets['productsize-chart-enable-additional-chart'];

printf('<%1$s id="modal1Title">%2$s</%3$s>', $title, __($assets['label'],$this->plugin_name), $title);

$post_data=get_post($chart_id);  

$pimg=get_post_meta($post_data->ID,'primary-chart-image',true);
if($pimg):
	$position=get_post_meta($post_data->ID,'primary-image-position',true);
$pimg=wp_get_attachment_image_src($pimg,'full'); 

echo '<div class="chart-1-image image-'.$position.'">
<img src="'.$pimg[0].'" alt="'.__($post->post_title,$this->plugin_name).'"
title="'.($title).'" />
</div>';

endif; 
if($post_data->post_content):
	$content = apply_filters('the_content', $post_data->post_content); 
echo $content;   
endif;	

if($assets['chart-table']):
	$this->productsize_chart_display_table($assets['chart-table']); 
endif; 

     // chart 1 content goes here 
if($enable_additional_chart==1):

	$title_additional=$this->default_assets['productsize-chart-additional-title'];
$title2=!empty($title_additional) ? $title_additional : "h3";

if($assets['chart-1']):
$title_c1 = $assets['chart-1'][0]['chart-title'];
$image_c1 = $assets['chart-1'][0]['chart-image'];
$content_c1 = $assets['chart-1'][0]['chart-content'];
$position_c1 = $assets['chart-1'][0]['image-position']=='left' ? 'image-left' : 'image-right';
$chart_c1 = $assets['chart-1'][0]['chart-table'];

echo '<div class="add-chart-1">';
printf('<%1$s id="modal1Title">%2$s</%3$s>', $title2, __($title_c1,$this->plugin_name), $title2);

if($image_c1):
	$img=wp_get_attachment_image_src($image_c1,'full'); 
echo '<div class="chart-1-image '.$position_c1.'"><img src="'.$img[0].'" alt="'.$title_c1.'" 
title="'.$title_c1.'" />
</div>';
endif;

if($content_c1) 
echo apply_filters('the_content',$content_c1);

if($chart_c1)
$this->productsize_chart_display_table($chart_c1); 

echo '</div>
<div class="clear"></div>';

endif; 

if($assets['chart-2']):

$title_c2=$assets['chart-2'][0]['chart-title-1'];
$image_c2=$assets['chart-2'][0]['chart-image-1'];
$content_c2=$assets['chart-2'][0]['chart-content-1'];
$position_c2=$assets['chart-2'][0]['image-position-1']=='left' ? 'image-left' : 'image-right';
$chart_c2=$assets['chart-2'][0]['chart-table-1'];

echo '<div class="add-chart-2">';
printf('<%1$s id="modal1Title">%2$s</%3$s>', $title2, __($title_c2,$this->plugin_name), $$title2);
if($image_c2): 
	$img=wp_get_attachment_image_src($image_c2,'full'); 
echo '<div class="chart-2-image '.$position_c2.'">
<img src="'.$img[0].'" alt="'.$title_c2.' ?>" 
title="'.$title_c2.'" />
</div>';
endif; 

if($content_c2)
echo apply_filters('the_content',$content_c2); 

if($chart_c2)
$this->productsize_chart_display_table($chart_c2); 

echo '</div>';

endif; 
endif; 

?>
