<?php

/**

 * Define the metabox and field configurations.

 *

 * @param  array $meta_boxes

 * @return array

 */

function cmb_chart_metaboxes( array $meta_boxes ) {
	
	$plugin_name='productsize-chart-for-woocommerce';

		$chart_details = array(
   array( 'id' => 'label', 'name' => __('Chart Label',$plugin_name), 'type' => 'text','desc'=>__('(label for tab or  modal popup box)',$plugin_name)),
   	array( 'id' => 'primary-chart-image',  'name' => __('Primary Chart Image',$plugin_name), 'type' => 'image','desc'=>__('Add/Edit image below',$plugin_name) ),
	array( 'id' => 'primary-image-position', 'name' => __('Image Position',$plugin_name),'desc'=>__('Primary chart mage position',$plugin_name), 'type' => 'select', 'options' => array( 'left' => __('Float Left',$plugin_name), 'right' => __('Float Right',$plugin_name))),
   	array( 'id' => 'title-color', 'name' => __('Title Color',$plugin_name), 'type' => 'colorpicker','desc'=>__('(Pick text color for the chart text)',$plugin_name) ),
	array( 'id' => 'text-color', 'name' => __('Text Color',$plugin_name), 'type' => 'colorpicker','desc'=>__('(Pick text color for the chart text)',$plugin_name) ),
		array( 'id' => 'overlay-color', 'name' => __('Modal Overlay Color',$plugin_name), 'type' => 'colorpicker','desc'=>__('(Pick overlay background color for modal)',$plugin_name) ),
		array( 'id' => 'chart-padding', 'name' => __('Padding (e.g. 10px)',$plugin_name), 'type' => 'text','desc'=>__('(Padding for chart)',$plugin_name) ),
	array( 'id' => 'position', 'name' => __('Chart Position',$plugin_name),'desc'=>__('(Select if the chart will display as a popup or as a additional tab)',$plugin_name), 'type' => 'select', 'options' => array( 'tab' => __('Additional Tab',$plugin_name), 'popup' => __('Modal pop up',$plugin_name))),
array( 'id' => 'chart-table',  'name' => __('Chart Table',$plugin_name), 'type' => 'textarea','desc'=>__('Add/Edit chart below',$plugin_name) ),
	
	
	);
	
	
		$meta_boxes[] = array(
		'title' => __('Chart Settings',$plugin_name),
		'pages' => 'chart',
		'fields' => $chart_details
	);
	
	
	
		$chart_content1= array(
		
		array( 'id' => 'chart-1', 'name' => __('Chart 1',$plugin_name), 'type' => 'group',  'fields' => array(

	array( 'id' => 'chart-title',  'name' => __('Title',$plugin_name), 'type' => 'text','desc'=>__('Add/Edit chart below',$plugin_name) ),
	array( 'id' => 'chart-image',  'name' => __('Image',$plugin_name), 'type' => 'image','desc'=>__('Add/Edit image below',$plugin_name) ),
	array( 'id' => 'image-position', 'name' => __('Image Position',$plugin_name),'desc'=>__('image position',$plugin_name), 'type' => 'select', 'options' => array( 'left' => __('Float Left',$plugin_name), 'right' => __('Float Right',$plugin_name))),
	array( 'id' => 'chart-content',  'name' => __('Content',$plugin_name), 'type' => 'wysiwyg','options' => array( 'editor_height' => '100' ),'desc'=>__('Add/Edit chart below',$plugin_name) ),
		array( 'id' => 'chart-table',  'name' => __('Chart Table',$plugin_name), 'type' => 'textarea','desc'=>__('Add/Edit chart below',$plugin_name) ),

	)),
	array( 'id' => 'chart-2', 'name' => __('Chart 2',$plugin_name), 'type' => 'group',  'fields' => array(

	array( 'id' => 'chart-title-1',  'name' => __('Title',$plugin_name), 'type' => 'text','desc'=>__('Add/Edit chart below',$plugin_name) ),
	array( 'id' => 'chart-image-1',  'name' => __('Image',$plugin_name), 'type' => 'image','desc'=>__('Add/Edit image below',$plugin_name) ),
	array( 'id' => 'image-position-1', 'name' => __('Image Position',$plugin_name),'desc'=>'image position', 'type' => 'select', 'options' => array( 'left' => __('Float Left',$plugin_name), 'right' => __('Float Right',$plugin_name))),
	array( 'id' => 'chart-content-1',  'name' => __('Content',$plugin_name), 'type' => 'wysiwyg','options' => array( 'editor_height' => '100' ),'desc'=>__('Add/Edit chart below',$plugin_name) ),
		array( 'id' => 'chart-table-1',  'name' => __('Chart Table',$plugin_name), 'type' => 'textarea','desc'=>__('Add/Edit chart below',$plugin_name) ),


	))
	
	
	);
	
	
		$meta_boxes[] = array(
		'title' => __('Additional Charts',$plugin_name),
		'pages' => 'chart',
		'fields' => $chart_content1
	);
	
	
	
		$chart_product = array(
  
   array( 'id' => 'prod-chart', 'name' => '', 'type' => 'post_select', 'use_ajax' => true,'query' => array( 'post_type' => 'chart' ), ),
	);
	
	
		$meta_boxes[] = array(
		'title' => __('Select Chart',$plugin_name),
		'pages' => 'product',
		'fields' => $chart_product,
		'context' =>'side'
	);
	
	

    return $meta_boxes;

}

add_filter( 'CMB_Chart_Meta_Boxes', 'cmb_chart_metaboxes' );





