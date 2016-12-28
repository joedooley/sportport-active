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


echo '<'.$title.' id="modal1Title">'.__($assets['label'],$this->plugin_name).'</'.$title.'>';

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
	$title=$assets['chart-1'][0]['chart-title'];
$image=$assets['chart-1'][0]['chart-image'];
$content=$assets['chart-1'][0]['chart-content'];
$position=$assets['chart-1'][0]['image-position']=='left' ? 'image-left' : 'image-right';
$chart=$assets['chart-1'][0]['chart-table'];

echo '<div class="add-chart-1">
<'.$title2.' id="modal1Title">'.__($title,$this->plugin_name); $title2.' >';

if($image):
	$img=wp_get_attachment_image_src($image,'full'); 
echo '<div class="chart-1-image '.$position.'"><img src="'.$img[0].'" alt="'.$title.'" 
title="'.$title.'" />
</div>';
endif;

if($content) 
echo apply_filters('the_content',$content);

if($chart)
$this->productsize_chart_display_table($chart); 

echo '</div>
<div class="clear"></div>';

endif; 

if($assets['chart-2']):

$title2=$assets['chart-2'][0]['chart-title-1'];
$image2=$assets['chart-2'][0]['chart-image-1'];
$content2=$assets['chart-2'][0]['chart-content-1'];
$position2=$assets['chart-2'][0]['image-position-1']=='left' ? 'image-left' : 'image-right';
$chart2=$assets['chart-2'][0]['chart-table-1'];

echo '<div class="add-chart-2">
<'.$title2.' id="modal1Title">'.__($title,$this->plugin_name).'</'.$title2.'>';

if($image): 
	$img=wp_get_attachment_image_src($image2,'full'); 
echo '<div class="chart-2-image '.$position2.'">
<img src="'.$img[0].'" alt="'.$title2.' ?>" 
title="'.$title.'" />
</div>';
endif; 

if($content2)
echo apply_filters('the_content',$content2); 

if($chart2)
$this->productsize_chart_display_table($chart2); 

echo '</div>';

endif; 
endif; 

?>
