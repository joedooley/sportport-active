<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    productsize-chart-for-woocommerce
 * @subpackage productsize-chart-for-woocommerce/public
 * @author     Nabaraj Chapagain <nabarajc6@gmail.com>
 */
class productsize_chart_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
		/**
	 * The elements or assets of plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $assets    The assets/settings of plugin like color,content etc.
	 */
		private $assets;
		
	/**
	 * The default elements or assets of plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $default_assets    The default assets/settings of plugin like color,content etc.
	 */
	private $default_assets;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->default_assets=get_option( 'productsize_chart_settings' );

	}
	
	/**
	 * convert hex to rgba
	 *
	 * @since    1.0.0
	 * @param      string    $color      color code for e.g. #000000
	 * @param      string    $opacity    opacity for rgba
	 */	
	public  function productsize_chart_hex2rgba($color, $opacity = false)
	{
		
		$default = 'rgb(0,0,0)';
 		//Return default if no color provided
		if(empty($color))
			return $default; 
	 	//Sanitize $color if "#" is provided 
		if ($color[0] == '#' ) {
			$color = substr( $color, 1 );
		}
		
        //Check if color has 6 or 3 characters and get values
		if (strlen($color) == 6) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}
		
        //Convert hexadec to rgb
		$rgb =  array_map('hexdec', $hex);
		
        //Check if opacity is set(rgba or rgb)
		if($opacity){
			if(abs($opacity) > 1)
				$opacity = 1.0;
			$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
		} else {
			$output = 'rgb('.implode(",",$rgb).')';
		}
        //Return rgb(a) color string
		return $output;
	}

	/**
	 * convert hex to rgba
	 *
	 * @since    1.0.0
	 * @param      string    $chart_content    display chart details with table
	 */	
	public function productsize_chart_display_table($chart_content)
	{
		
		$chart= json_decode($chart_content);
		if(sizeof($chart)>1)
		{	
			echo "<table id='size-chart'>";
			$first=0;
			foreach($chart as $chart_details){
				
				echo "<tr>";
				for($i=0; $i<count($chart_details); $i++)
				{
					
					echo ($first==0) ? "<th>".__($chart_details[$i],$this->plugin_name)."</th>" : "<td>".__($chart_details[$i],$this->plugin_name)."</td>";
				}
				echo "</tr>";
				$first++;
			}
			echo "</table>";
			
		}

	}
	
	/**
	 * chart assets
	 *
	 * @since    1.0.0
	 */	
	
	public function productsize_chart_assets($chart_id)
	{

		$this->assets=array(
			'label'=> get_post_meta($chart_id,'label',true),
			'chart'=>$chart_id,
			'position'=> get_post_meta($chart_id,'position',true),
			'content'=> get_post_meta($chart_id,'chart-content',true),
			'text-color'=> get_post_meta($chart_id,'text-color',true),
			'title-color'=> get_post_meta($chart_id,'title-color',true),
			'overlay'=> get_post_meta($chart_id,'overlay-color',true),
			'table-style'=> get_post_meta($chart_id,'table-style',true),
			'padding'=> get_post_meta($chart_id,'chart-padding',true),
			'chart-table'=> get_post_meta($chart_id,'chart-table',true),
			'chart-1'=> get_post_meta($chart_id,'chart-1',false),
			'chart-2'=> get_post_meta($chart_id,'chart-2',false),
			);
		
		$but_pos=get_post_meta($chart_id,'position',true);
		if($but_pos && $but_pos=='popup')
		{
			$button_position=get_post_meta($chart_id,'button-position',true); 
			$this->assets=array_merge(array('button-position'=>$button_position),$this->assets); 
		}
		
		return $this->assets;
		
	}

	/**
	 * Check if product belongs to a category that has a chart assigned 
	 *
	 * @since    1.0.0
	 */	 
	public function productsize_chart_id_by_categories($id){
		
		global $wpdb;				
		$terms = wp_get_post_terms( $id, 'product_cat');	
		$chart_terms=array(); 
		if($terms):
			foreach($terms as $term)
			{
				$chart_terms[]=$term->term_id; 
			}	
			endif;
			$productsize_chart_category_args = array(
				'post_type' => 'chart',
				'posts_per_page' => '-1',
				'posts_status' => 'publish',
				'orderby'=>'ID',
				'order'=>'DESC',
				
				);
			$productsize_chart_array = get_posts( $productsize_chart_category_args );
			$arr=array();
			$chart_id='';
			if(sizeof($productsize_chart_array)>0):
				foreach($productsize_chart_array as $new_chart_details)
				{
					$chart_categories=get_post_meta($new_chart_details->ID,'chart-categories',true);
					if($chart_categories):
						foreach($chart_categories as $key=>$value)
						{
							$arr[$key]=$value;
						}
						endif;
						if(sizeof($chart_terms)>0 && sizeof($arr)>0):
							if(array_intersect($arr,$chart_terms))
							{
								$chart_id=$new_chart_details->ID;
								
							}
							if($chart_id)
								break;
							endif;
						}
						endif;
						
						return $chart_id;
					}
					
	/**
	 * check product chart avialable or not
	 *
	 * @since    1.0.0
	 */	  
	
	public function productsize_chart_check( $id )
	{
		$chart_id=get_post_meta($id,'prod-chart',true);
		if($chart_id)
			return $chart_id;
		
	}
	
	
	/**
	 * check product chart avialable or not
	 *
	 * @since    1.0.0
	 */	  
	
	public function productsize_chart_id( $id )
	{
		
		
		if($this->productsize_chart_check($id)):
			$chart_id=$this->productsize_chart_check($id);
		else:
			$chart_id=$this->productsize_chart_id_by_categories($id);
		endif;
		return $chart_id;
		
	}
	
	
	/**
	 * check popu up button position
	 *
	 * @since    1.0.0
	 */	  
	
	public function productsize_chart_check_popup_button_position()
	{
		global $post;
		$chart_id=$this->productsize_chart_id($post->ID);
		$assets=$this->productsize_chart_assets($chart_id); 

		if(!is_array($assets) || !array_key_exists('button-position', $assets))
			return;

		$position=$assets['button-position'];
		if($position=='before-summary-text'):
			$hook='woocommerce_single_product_summary';
		$priority=11;
		endif;
		if($position=='after-add-to-cart'):
			$hook='woocommerce_single_product_summary';
		$priority=31;
		endif;
		if($position=='before-add-to-cart'):
			$hook='woocommerce_single_product_summary';
		$priority=29;
		endif;
		if($position=='after-product-meta'):
			$hook='woocommerce_single_product_summary';
		$priority=41;
		endif;
		if(!$position):
			$hook='woocommerce_single_product_summary';
		$priority=31;
		endif;
		
		add_action($hook,array($this,'productsize_chart_button'),$priority);
		
	}
	

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */	  
	
	public function productsize_chart_new_product_tab( $tabs )
	{
		global $post;
		
		$chart_id=$this->productsize_chart_id($post->ID);
		$assets=$this->productsize_chart_assets($chart_id);
		
		if(!is_array($assets) || !array_key_exists('chart', $assets)) 
			return;
		
		$default_assets=$this->default_assets; 
		
		if($assets['position']=='tab')
		{
			$tabs['custom_tab'] = array(
				'title' 	=> __( $default_assets['productsize-chart-button-label'], $this->plugin_name ),
				'priority' 	=> 50,
				'callback' 	=> array( $this, 'productsize_chart_new_product_tab_content' ),
				);

			return $tabs;
		}
		
	}
	
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */			
	
	public function productsize_chart_new_product_tab_content() {
		global $post;
		
		$chart_id=$this->productsize_chart_id($post->ID);
		$assets=$this->productsize_chart_assets($chart_id);

		if(!is_array($assets) || !array_key_exists('chart', $assets)) 
			return;
		
		require("includes/productsize-chart-contents.php"); 	
	}
	
	/**
	 * hook to display chart button after add to cart button for modal	
	 *
	 * @since    1.0.0
	 */	
	
	public function productsize_chart_button(){
		global $post;
		
		$chart_id=$this->productsize_chart_id($post->ID);
		$assets=$this->productsize_chart_assets($chart_id);
		
		if(!is_array($assets) || !array_key_exists('position', $assets)) 
			return;
		
		if($assets['position']=='popup')
		{ 
			$default_assets=$this->default_assets;     
			?>
			<div class="button-wrapper"><a href="#modal" class="<?php echo !empty($default_assets['productsize-chart-button-class']) ? $default_assets['productsize-chart-button-class'] : 'chart-button'; ?>" id="chart-button"><?php _e($default_assets['productsize-chart-button-label'],$this->plugin_name); ?></a></div>
			<div class="remodal" data-remodal-id="modal" role="dialog" aria-labelledby="modal1Title" aria-describedby="modal1Desc">
				<button data-remodal-action="close" class="remodal-close" aria-label="Close"></button>
				<div>
					<?php require("includes/productsize-chart-contents.php"); ?>
				</div>
			</div>
			<?php
		}		

	}
	
	
	public function productsize_chart_custom_style()
	{
		global $post;
		
		$chart_id=$this->productsize_chart_id($post->ID);
		$assets=$this->productsize_chart_assets($chart_id);

		$default_assets=$this->default_assets;
		$title_color=!empty($assets['title-color']) ? $assets['title-color'] : $default_assets['productsize-chart-title-color'];
		$color=!empty($assets['text-color']) ? $assets['text-color'] : $default_assets['productsize-chart-text-color'];
		$table_style=!empty($assets['table-style']) ? $assets['table-style'] : $default_assets['productsize-chart-table-style'];
		$overlay= !empty($assets['overlay']) ? $assets['overlay'] : $default_assets['productsize-chart-overlay-color'];
		$opacity=$default_assets['productsize-chart-overlay-opacity'];
		$padding=!empty($assets['padding']) ? $assets['padding']."px" : '35px';
		$overlay_bg=!empty($assets['overlay']) ? $this->productsize_chart_hex2rgba($overlay,$opacity) :
		$this->productsize_chart_hex2rgba($overlay,$opacity);
		$button_bg=$default_assets['productsize-chart-button-bg-color'];
		$button_color=$default_assets['productsize-chart-button-text-color'];
		$button_hover_bg=$default_assets['productsize-chart-button-hover-bg'];
		
		?>
		<style type="text/css">
			#size-chart {clear:both; margin:10px 0; width:100%}#size-chart tr th{font-weight:bold;}
			#size-chart tr td,#size-chart tr th{color:<?php  echo $color; ?>;
			padding:8px; text-align:left;}
			.remodal p{color:<?php echo $color; ?>; text-align:justify;}
				#modal1Title{color:<?php echo $title_color; ?>; margin-bottom:15px; font-size:25px; text-align:left}
			<?php if($table_style=='style-1'){?>
					#size-chart tr:nth-child(odd){background:#ebe9eb;	}
				<?php }
				else { ?>
						#size-chart tr th{background:#000000; color:#ffffff; text-align:center;}
						#size-chart tr td,#size-chart tr th{border:1px solid #CCCCCC; text-align:center;}
					<?php } ?>
					.remodal-overlay {background:<?php echo $overlay_bg;  ?> !important; z-index:9999;}
					.remodal{padding:<?php echo $padding; ?>;}
					.button-wrapper{margin:7px 0;}
						#chart-button{background:<?php echo $button_bg; ?>; color:<?php echo $button_color; ?>; padding:7px 10px;font-weight: 700;
					border-radius: 3px; -webkit-border-radius: 3px;-moz-border-radius: 3px; text-decoration:none; }
							#chart-button:hover{background:<?php echo $button_hover_bg; ?>;  }
				</style>    
				<?php
			}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function productsize_chart_public_enqueue_styles() {

		if(is_singular("product")){
			wp_enqueue_style( $this->plugin_name."-jquery-modal", plugin_dir_url( __FILE__ ) . 'css/remodal.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name."-jquery-modal-default-theme", plugin_dir_url( __FILE__ ) . 'css/remodal-default-theme.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function productsize_chart_public_enqueue_scripts() {

		if(is_singular("product")){
			wp_enqueue_script( $this->plugin_name."-jquery-modal", plugin_dir_url( __FILE__ ) . 'js/remodal.js', array( 'jquery' ), $this->version, false );
		}

	}

}
