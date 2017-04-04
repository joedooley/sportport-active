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

global $pagenow; 

?>
<div class="wrap ajax_cart">
	<h1><?php _e('Size Chart Settings',$this->plugin_name); ?></h1>
	<form method="post" action="<?php admin_url( 'admin.php?page=productsize_chart' ); ?>" enctype="multipart/form-data">
		<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
			<a href="<?php echo admin_url("admin.php?page=productsize_chart&tab=pop-up"); ?>" class="nav-tab nav-tab-active"><?php _e('Default Settings',$this->plugin_name); ?></a>
		</h2>
		<table class="form-table">
			
			<?php wp_nonce_field( "productsize_chart_page" ); 
			
			if ( $pagenow == 'edit.php' && $_GET['page'] == 'productsize_chart' ){ ?>
				
				<tr>
					<th><?php _e('Chart Heading',$this->plugin_name); ?></th>
					<td>
						<select name="productsize-chart-title">
							<option <?php if($this->productsize_chart_settings['productsize-chart-title']=='h1') echo "selected='selected'"; ?> value="h1">H1</option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-title']=='h2') echo "selected='selected'"; ?> value="h2">H2</option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-title']=='h3') echo "selected='selected'"; ?> value="h3">H3</option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-title']=='h4') echo "selected='selected'"; ?> value="h4">H4</option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-title']=='h5') echo "selected='selected'"; ?> value="h5">H5</option> 
							
						</select>
						
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Enable Additional Chart',$this->plugin_name); ?></th>
					<td>
						
						<input type="checkbox" name="productsize-chart-enable-additional-chart"  value="1" <?php echo $this->productsize_chart_settings['productsize-chart-enable-additional-chart']== 1 ? 'checked="checked"' : '';?>/>
					</td>
				</tr>       
				<tr>
					<th><?php _e('Additional Chart Heading',$this->plugin_name); ?></th>
					<td>
						<select name="productsize-chart-additional-title">
							<option <?php if($this->productsize_chart_settings['productsize-chart-additional-title']=='h1') echo "selected='selected'"; ?> value="h1">H1</option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-additional-title']=='h2') echo "selected='selected'"; ?> value="h2">H2</option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-additional-title']=='h3') echo "selected='selected'"; ?> value="h3">H3</option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-additional-title']=='h4') echo "selected='selected'"; ?> value="h4">H4</option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-additional-title']=='h5') echo "selected='selected'"; ?> value="h5">H5</option> 
							
						</select>
						
						
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Title Color',$this->plugin_name); ?></th>
					<td>
						<input type="hidden" name="productsize-chart-title-color" id="color-picker1" value="<?php echo $this->productsize_chart_settings['productsize-chart-title-color'];?>"/>
						
					</td>
				</tr>

				<tr>
					<th><?php _e('Text Color',$this->plugin_name); ?></th>
					<td>
						<input type="hidden" name="productsize-chart-text-color" id="color-picker1" value="<?php echo $this->productsize_chart_settings['productsize-chart-text-color'];?>"/>
						
					</td>
				</tr>
				<tr>
					<th><?php _e('Default Table Style',$this->plugin_name); ?></th>
					<td>
						<select name="productsize-chart-table-style">
							<option <?php if($this->productsize_chart_settings['productsize-chart-table-style']=='style-1') echo "checked='checked'"; ?> value="style-1"><?php _e('Style 1',$this->plugin_name); ?></option> 
							<option <?php if($this->productsize_chart_settings['productsize-chart-table-style']=='style-2') echo "checked='checked'"; ?> value="style-2"><?php _e('Style 2',$this->plugin_name); ?></option> 
							
							
						</select>
						
						
					</td>
				</tr>
				<tr>
					<th colspan="2"><h2><?php _e('Pop Up Settings',$this->plugin_name); ?></h2></th>   
				</tr>
				
				<tr>
					<th><?php _e('Button Background Color',$this->plugin_name); ?></th>
					<td>
						<input type="hidden" name="productsize-chart-button-bg-color" id="color-picker1" value="<?php echo $this->productsize_chart_settings['productsize-chart-button-bg-color'];?>"/>
						
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Button Hover Background Color',$this->plugin_name); ?></th>
					<td>
						<input type="hidden" name="productsize-chart-button-hover-bg" id="color-picker1" value="<?php echo $this->productsize_chart_settings['productsize-chart-button-hover-bg'];?>"/>
						
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Button Text Color',$this->plugin_name); ?></th>
					<td>
						<input type="hidden" name="productsize-chart-button-text-color" id="color-picker1" value="<?php echo $this->productsize_chart_settings['productsize-chart-button-text-color'];?>"/>
						
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Modal Overlay Color',$this->plugin_name); ?></th>
					<td>
						<input type="hidden" name="productsize-chart-overlay-color" id="color-picker1" value="<?php echo $this->productsize_chart_settings['productsize-chart-overlay-color'];?>"/>
						
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Overlay Opacity',$this->plugin_name); ?></th>
					<td>
						<input type="text" name="productsize-chart-overlay-opacity"  value="<?php echo $this->productsize_chart_settings['productsize-chart-overlay-opacity'];?>"/>
						
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Button Class',$this->plugin_name); ?></th>
					<td>
						<input type="text" name="productsize-chart-button-class"  value="<?php echo $this->productsize_chart_settings['productsize-chart-button-class'];?>"/>
						
					</td>
				</tr>
				
				<tr>
					<th><?php _e('Button Label',$this->plugin_name); ?></th>
					<td>
						<input type="text" name="productsize-chart-button-label"  value="<?php echo $this->productsize_chart_settings['productsize-chart-button-label'];?>"/>
					</td>
				</tr>
				<?php	
			}
			?>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" name="productsize_chart_submit" value="<?php _e('Save Changes',$this->plugin_name) ?>" />
			
		</p>
		
	</form>