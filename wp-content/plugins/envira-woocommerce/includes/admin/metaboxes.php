<?php
/** 
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_WooCommerce
 * @author  Tim Carr
 */
class Envira_WooCommerce_Metaboxes {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

    	// Load the base class object.
        $this->base = Envira_WooCommerce::get_instance();

        // Settings
        add_action( 'envira_gallery_metabox_scripts', array( $this, 'js' ) );
        add_action( 'envira_gallery_config_box', array( $this, 'config_tab' ) );
        add_action( 'envira_gallery_lightbox_box', array( $this, 'lightbox_tab' ) );
        add_filter( 'envira_gallery_save_settings', array( $this, 'save' ), 10, 2 );

        // Individual Image Settings
    	add_action( 'envira_gallery_after_meta_help_items', array( $this, 'meta_help_items' ) );
        add_action( 'print_media_templates', array( $this, 'meta_settings' ), 10, 3 );

    }

    /**
     * Enqueues the Media Editor script, which is used when editing a gallery image
     * This outputs the WooCommerce settings for each individual image
     *
     * @since 1.0.4
    */
    public function js() {

        wp_enqueue_script( $this->base->plugin_slug . '-media-edit', plugins_url( 'assets/js/media-edit.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        
    }

    /**
     * Outputs options for enabling/disabling WooCommerce Add to Cart
     * functionality on the gallery grid
     *
     * @since 1.0.0
     *
     * @param WP_Post $post Gallery Post
     */
    public function config_tab( $post ) {

        // Get instance
        $instance = Envira_Gallery_Metaboxes::get_instance();

        ?>
         <tr id="envira-config-woocommerce-box">
            <th scope="row">
                <label for="envira-config-woocommerce"><?php _e( 'Enable WooCommerce?', 'envira-woocommerce' ); ?></label>
            </th>
            <td>
                <input id="envira-config-woocommerce" type="checkbox" name="_envira_gallery[woocommerce]" value="<?php echo $instance->get_config( 'woocommerce', $instance->get_config_default( 'woocommerce' ) ); ?>" <?php checked( $instance->get_config( 'woocommerce', $instance->get_config_default( 'woocommerce' ) ), 1 ); ?> />
                <span class="description"><?php _e( 'Enables WooCommerce Add to Cart functionality for each image in the gallery grid, if the image is assigned to a WooCommerce Product.', 'envira-woocommerce' ); ?></span>
            </td>
        </tr>
        <?php

    }

    /**
     * Outputs options for enabling/disabling WooCommerce Add to Cart
     * functionality on the gallery lightbox
     *
     * @since 1.0.0
     *
     * @param WP_Post $post Gallery Post
     */
    public function lightbox_tab( $post ) {

        // Get instance
        $instance = Envira_Gallery_Metaboxes::get_instance();

        ?>
         <tr id="envira-config-woocommerce-box">
            <th scope="row">
                <label for="envira-config-lightbox-woocommerce"><?php _e( 'Enable WooCommerce?', 'envira-woocommerce' ); ?></label>
            </th>
            <td>
                <input id="envira-config-lightbox-woocommerce" type="checkbox" name="_envira_gallery[lightbox_woocommerce]" value="<?php echo $instance->get_config( 'lightbox_woocommerce', $instance->get_config_default( 'lightbox_woocommerce' ) ); ?>" <?php checked( $instance->get_config( 'lightbox_woocommerce', $instance->get_config_default( 'lightbox_woocommerce' ) ), 1 ); ?> />
                <span class="description"><?php _e( 'Enables WooCommerce Add to Cart functionality for each image in the Lightbox view, if the image is assigned to a WooCommerce Product.', 'envira-woocommerce' ); ?></span>
            </td>
        </tr>
        <?php

    }

    /**
     * Saves the addon's settings for Galleries.
     *
     * @since 1.0.0
     *
     * @param array $settings  Array of settings to be saved.
     * @param int $pos_tid     The current post ID.
     * @return array $settings Amended array of settings to be saved.
     */
    function save( $settings, $post_id ) {

        $settings['config']['woocommerce']                = ( isset( $_POST['_envira_gallery']['woocommerce'] ) ? 1 : 0 );
        $settings['config']['lightbox_woocommerce']       = ( isset( $_POST['_envira_gallery']['lightbox_woocommerce'] ) ? 1 : 0 );

        return $settings;
    
    }

    /**
     * Outputs help text in the modal window when editing an existing image,
     * telling the user how to choose which WooCommerce Product/Variant to
     * link the image to.
     *
     * @since 1.0.0
     *
     * @param int $post_id The current post ID.
     */
    public function meta_help_items( $post_id ) {

        ?>
        <div class="filename">
            <strong><?php _e( 'WooCommerce', 'envira-woocommerce' ); ?></strong>
            <?php _e( 'Choose a WooCommerce Product which relates to this image.  Visitors will then be able to order the selected product in the gallery and/or lightbox views.', 'envira-woocommerce' ); ?>
            <br /><br />
        </div>
        <?php

    }

    /**
     * Outputs fields in the modal window when editing an existing image,
     * allowing the user to choose which WooCommerce Product/Variant to
     * link the image to.
     *
     * @since 1.0.0
     *
     * @param int $id      The ID of the item to retrieve.
     * @param array $data  Array of data for the item.
     * @param int $post_id The current post ID.
     */
    public function meta_settings( $post_id ) {

        // Get WooCommerce Products
        $args = array(
            'post_type'     => 'product',
            'posts_per_page'=> -1,
        );
        $products = new WP_Query( $args );

        // WooCommerce Meta Editor
        // Use: wp.media.template( 'envira-meta-editor-woocommerce' )
        ?>
        <script type="text/html" id="tmpl-envira-meta-editor-woocommerce">
            <label class="setting">
                <span class="name"><?php _e( 'WooCommerce Product', 'envira-woocommerce' ); ?></span>
                <select name="woocommerce_product" size="1">
                    <option value="0"><?php _e( '(No Product)', 'envira-woocommerce' ); ?></option>
                    <?php
                    if ( $products->have_posts() ) {
                        foreach ( $products->posts as $product ) {
                            ?>
                            <option value="<?php echo $product->ID; ?>"><?php echo $product->post_title; ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </label>
        </script>
        <?php

    }
	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_WooCommerce_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_WooCommerce_Metaboxes ) ) {
            self::$instance = new Envira_WooCommerce_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$envira_woocmmerce_metaboxes = Envira_WooCommerce_Metaboxes::get_instance();