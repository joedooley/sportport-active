<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    productsize-chart-for-woocommerce
 * @subpackage productsize-chart-for-woocommerce/public
 * @author     Nabaraj Chapagain <nabarajc6@gmail.com>
 */

if(!defined('ABSPATH')){

	exit; // exit if directly accessed.
}

class productsize_chart_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	
	/**
	 * The productsize chart settings
	 * * @since      1.0.0
	 * @access   private
	 * @var      string    $version    The current version of the plugin.
	 */	
	
	private $productsize_chart_settings;
	
	
	/**
	 * The productsize chart default settings
	 * * @since      1.0.0
	 * @access   private
	 * @var      string    $version    The current version of the plugin.
	 */	
	
	private $productsize_chart_default_settings;


	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */


	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		if(get_option( 'productsize_chart_settings' ))
		{
			$this->productsize_chart_settings=get_option( 'productsize_chart_settings' );
		}
		else
		{
			$this->productsize_chart_default_settings();
		}

	}
	

/**
* Register admin menu for the plugin
* of the plugin.
* * @since      1.0.0
* @access   private
*/	

public function  productsize_chart_menu(){

	$settings=add_submenu_page('edit.php?post_type=chart', __('NC Plugins',$this->plugin_name), __('Settings',$this->plugin_name), 'manage_options', 'productsize_chart', array($this, 'productsize_chart_settings_form'));

	add_action( "load-{$settings}", array($this,'productsize_chart_settings_page') );
}


	/**
	 * Register a new post type called chart
	 *
	 * @since    1.0.0
	 */	
	
	public function productsize_chart_register_post_type_chart() {
		

		$labels = array(
			'name'               => _x( 'Charts', 'post type general name', $this->plugin_name ),
			'singular_name'      => _x( 'Chart', 'post type singular name', $this->plugin_name ),
			'menu_name'          => _x( 'Size Chart', 'admin menu', $this->plugin_name ),
			'name_admin_bar'     => _x( 'Chart', 'add new on admin bar', $this->plugin_name ),
			'add_new'            => _x( 'Add Chart', 'chart', $this->plugin_name ),
			'add_new_item'       => __( 'Add New Chart', $this->plugin_name ),
			'new_item'           => __( 'New Chart', $this->plugin_name ),
			'edit_item'          => __( 'Edit Chart', $this->plugin_name ),
			'view_item'          => __( 'View Chart', $this->plugin_name ),
			'all_items'          => __( 'Size Charts', $this->plugin_name ),
			'search_items'       => __( 'Search Charts', $this->plugin_name ),
			'parent_item_colon'  => __( 'Parent Charts:', $this->plugin_name ),
			'not_found'          => __( 'No chart found.', $this->plugin_name ),
			'not_found_in_trash' => __( 'No charts found in Trash.', $this->plugin_name )
			);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', $this->plugin_name ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'chart' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-media-spreadsheet',
			'supports'           => array( 'title', 'editor' )
			);

		register_post_type( 'chart', $args );
	}


	
	/**
	 * Adds the meta box container.
	 *
	 * @since    1.0.0
	 */	
	public function productsize_chart_add_meta_box( $post_type ) {
		
		$post_types_chart = array('chart','product');   //limit meta box to chart post type
		if ( in_array( $post_type, $post_types_chart )) {

		// chart setting meta box
			add_meta_box('chart-settings',__( 'Chart Settings', $this->plugin_name ),
				array( $this, 'productsize_chart_render_meta_box_content' ),'chart','advanced','high'
				);
			
			if($this->productsize_chart_settings['productsize-chart-enable-additional-chart']==1){
		//additional meta box	
				add_meta_box('additional-chart',__( 'Additional Chart', $this->plugin_name ),
					array( $this, 'productsize_chart_render_add_meta_box_content' ),'chart','advanced','high'
					);
			}
		//meta box to select chart in product page	
			add_meta_box('additional-chart',__( 'Select Chart', $this->plugin_name ),
				array( $this, 'productsize_chart_render_select_chart_content' ),'product','side','default'
				);
		}
	}


	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function productsize_chart_save( $post_id ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST['productsize_chart_inner_custom_box'] ) )
			return $post_id;

		$nonce = $_POST['productsize_chart_inner_custom_box'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'productsize_chart_inner_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'chart' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */
		
		if(isset($_POST['prod-chart'])){
			$chart_id=sanitize_text_field($_POST['prod-chart']);
			update_post_meta( $post_id, 'prod-chart', $chart_id );
			return;
			
		}

		// Sanitize the user input.
		$chart_label = sanitize_text_field( $_POST['label'] );
		$chart_img = sanitize_text_field( $_POST['primary-chart-image'] );
		$chart_img_position = sanitize_text_field( $_POST['primary-image-position'] );
		$title_color = sanitize_text_field( $_POST['title-color'] );
		$text_color = sanitize_text_field( $_POST['text-color'] );
		$overlay_color = sanitize_text_field( $_POST['overlay-color'] );
		$table_style = sanitize_text_field( $_POST['table-style'] );
		$chart_padding = sanitize_text_field( $_POST['chart-padding'] );
		$chart_position = sanitize_text_field( $_POST['position'] );
		$button_position = sanitize_text_field( $_POST['button-position'] );
		$chart_categories = $_POST['chart-categories'] ;
		$chart_table = sanitize_text_field( $_POST['chart-table'] );

		//update chart meta
		update_post_meta( $post_id, 'label', $chart_label );
		update_post_meta( $post_id, 'primary-chart-image', $chart_img );
		update_post_meta( $post_id, 'primary-image-position', $chart_img_position );
		update_post_meta( $post_id, 'title-color', $title_color );
		update_post_meta( $post_id, 'text-color', $text_color );
		update_post_meta( $post_id, 'overlay-color', $overlay_color );
		update_post_meta( $post_id, 'table-style', $table_style );
		update_post_meta( $post_id, 'chart-padding', $chart_padding );
		update_post_meta( $post_id, 'position', $chart_position );
		
		if($chart_position!='tab')
			update_post_meta( $post_id, 'button-position', $button_position );
		update_post_meta( $post_id, 'chart-categories', $chart_categories );
		update_post_meta( $post_id, 'chart-table', $chart_table );
		
		if($this->productsize_chart_settings['productsize-chart-enable-additional-chart']==1){

		//chart 1
			$chart_1=array();
			$chart_1['chart-title']=sanitize_text_field( $_POST['chart-title-1'] );	
			$chart_1['chart-image']= $_POST['chart-image-1'] ;	
			$chart_1['chart-position']=sanitize_text_field( $_POST['chart-position-1'] );
			$chart_1['chart-content']=sanitize_text_field( $_POST['chart-content-1'] );
			$chart_1['chart-table']=sanitize_text_field( $_POST['chart-table-1'] );
			update_post_meta( $post_id, 'chart-1', $chart_1 );


		//chart 2
			$chart_2=array();
			$chart_2['chart-title-1']=sanitize_text_field( $_POST['chart-title-2'] );	
			$chart_2['chart-image-1']= $_POST['chart-image-2'];	
			$chart_2['chart-position-1']=sanitize_text_field( $_POST['chart-position-2'] );	
			$chart_2['chart-content-1']=sanitize_text_field( $_POST['chart-content-2'] );	
			$chart_2['chart-table-1']=sanitize_text_field( $_POST['chart-table-2'] );	
			update_post_meta( $post_id, 'chart-2', $chart_2 );	
			
		}
	}


	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function productsize_chart_select_save( $post_id ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST['productsize_chart_select_custom_box'] ) )
			return $post_id;

		$nonce = $_POST['productsize_chart_select_custom_box'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'productsize_chart_select_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'product' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */
		
		if(isset($_POST['prod-chart'])){
			$chart_id=sanitize_text_field($_POST['prod-chart']);
			update_post_meta( $post_id, 'prod-chart', $chart_id );
			return;
			
		}

	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function productsize_chart_render_meta_box_content( $post ) {

		require_once('includes/productsize-chart-meta-form.php'); 

	}
	
	/**
	 * Render Meta Box content for additional chart.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function productsize_chart_render_add_meta_box_content( $post ) {

		require_once('includes/productsize-chart-add-meta-form.php'); 

	}
	
	
	/**
	 * Render Meta Box content to select chart on product page.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function productsize_chart_render_select_chart_content( $post ) {

		require_once('includes/productsize-chart-select-chart-form.php'); 

	}
	
	
	
	/**
 	* Loads the image management javascript
 	*/
 	public function productsize_chart_image_enqueue() {
 		global $typenow;
 		if( $typenow == 'chart' ) {
 			wp_enqueue_media();

        // Registers and enqueues the required javascript.
 			wp_register_script( 'meta-box-image', plugin_dir_url( __FILE__ ) . '/assets/js/custom-script.js', array( 'jquery' ) );
 			wp_localize_script( 'meta-box-image', 'meta_image',
 				array(
 					'title' => __( 'Upload an Image', $this->plugin_name ),
 					'button' => __( 'Use this image', $this->plugin_name ),
 					)
 				);
 			wp_enqueue_script( 'meta-box-image' );
 		}
 	}



     /**
	 * Default setting values for size chart
	 * * @since      1.0.0
	 */	
     public function productsize_chart_default_settings (){

     	$this->productsize_chart_default_settings=array();
     	$this->productsize_chart_default_settings['productsize-chart-title']='h2';
     	$this->productsize_chart_default_settings['productsize-chart-enable-additional-chart']='1';
     	$this->productsize_chart_default_settings['productsize-chart-additional-title']='';
     	$this->productsize_chart_default_settings['productsize-chart-title-color']='#000000';
     	$this->productsize_chart_default_settings['productsize-chart-text-color']='#000000';
     	$this->productsize_chart_default_settings['productsize-chart-table-style']='style-1';
     	$this->productsize_chart_default_settings['productsize-chart-button-bg-color']='#000000';
     	$this->productsize_chart_default_settings['productsize-chart-button-hover-bg']='#333333';
     	$this->productsize_chart_default_settings['productsize-chart-button-text-color']='#ffffff';
     	$this->productsize_chart_default_settings['productsize-chart-overlay-color']='#000000';
     	$this->productsize_chart_default_settings['productsize-chart-overlay-opacity']='0.7';
     	$this->productsize_chart_default_settings['productsize-chart-button-class']='';
     	$this->productsize_chart_default_settings['productsize-chart-button-label']='Size Guide';
     	update_option( "productsize_chart_settings", $this->productsize_chart_default_settings );

     }


	/**
	 *  productsize chart settings and redirection
	 * of the plugin.
	 * * @since      1.0.0
	 * @access   private
	 */				

	public function productsize_chart_settings_page() {
		if (isset($_POST["productsize_chart_submit"])) {
			check_admin_referer( "productsize_chart_page" );
			$this->productsize_chart_save_settings();
			wp_redirect(admin_url('edit.php?post_type=chart&page=productsize_chart'));
			exit;
		}
	}		


	
	/**
	 *  productsize chart settings form
	 * of the plugin.
	 * * @since      1.0.0
	 * @access   private
	 */		
	public function productsize_chart_settings_form(){

		include_once('includes/productsize-chart-settings-form.php');
	}
	

	/**
	 *  productsize chart POST values and update options
	 * of the plugin.
	 * * @since      1.0.0
	 * @access   private
	 */			

	public function productsize_chart_save_settings(){

		$this->productsize_chart_settings=array();
		if ( isset ( $_GET['page'] )=='productsize_chart') {
			$this->productsize_chart_settings=array();
			$this->productsize_chart_settings['productsize-chart-title']=$_POST['productsize-chart-title'];
			$this->productsize_chart_settings['productsize-chart-enable-additional-chart']=$_POST['productsize-chart-enable-additional-chart'];
			$this->productsize_chart_settings['productsize-chart-additional-title']=$_POST['productsize-chart-additional-title'];
			$this->productsize_chart_settings['productsize-chart-title-color']=$_POST['productsize-chart-title-color'];
			$this->productsize_chart_settings['productsize-chart-text-color']=$_POST['productsize-chart-text-color'];
			$this->productsize_chart_settings['productsize-chart-table-style']=$_POST['productsize-chart-table-style'];
			$this->productsize_chart_settings['productsize-chart-button-bg-color']=$_POST['productsize-chart-button-bg-color'];
			$this->productsize_chart_settings['productsize-chart-button-hover-bg']=$_POST['productsize-chart-button-hover-bg'];
			$this->productsize_chart_settings['productsize-chart-button-text-color']=$_POST['productsize-chart-button-text-color'];
			$this->productsize_chart_settings['productsize-chart-overlay-color']=$_POST['productsize-chart-overlay-color'];
			$this->productsize_chart_settings['productsize-chart-overlay-opacity']=$_POST['productsize-chart-overlay-opacity'];
			$this->productsize_chart_settings['productsize-chart-button-class']=$_POST['productsize-chart-button-class'];
			$this->productsize_chart_settings['productsize-chart-button-label']=$_POST['productsize-chart-button-label'];

		 //update option
			update_option( "productsize_chart_settings", $this->productsize_chart_settings );

		}	
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function productsize_chart_admin_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( 'wp-color-picker' ); 
		wp_enqueue_style( $this->plugin_name."-jquery-editable-style", plugin_dir_url( __FILE__ ) . 'css/jquery.edittable.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name."-select2", plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name."-meta-box", plugin_dir_url( __FILE__ ) . 'css/metabox.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function productsize_chart_admin_enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name."-jquery-editable-js", plugin_dir_url( __FILE__ ) . 'js/jquery.edittable.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name."-jquery-select2", plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'-custom-script', plugins_url( 'js/custom-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true ); 

	}

}
