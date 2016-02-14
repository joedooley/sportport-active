<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_WooCommerce
 * @author  Tim Carr
 */
class Envira_WooCommerce_Shortcode {

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
     * Holds the order object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $order;
    
    /**
     * Holds success and error messages when saving/submitting orders
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $messages = array();

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
	    
	    // Load the base class object.
        $this->base = Envira_WooCommerce::get_instance();
	    
	    // Register CSS
        wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/envira-woocommerce.css', $this->base->file ), array(), $this->base->version );
	    
        // Register JS
        wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/envira-woocommerce.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );

	    // Gallery
        add_action( 'envira_gallery_before_output', array( $this, 'output_css_js' ) );
        add_filter( 'envira_gallery_output_start', array( $this, 'output_messages' ), 10, 2 );
        add_filter( 'envira_gallery_output_after_link', array( $this, 'output_add_to_cart' ), 10, 5 );

        // Lightbox
        add_action( 'envira_gallery_api_after_show', array( $this, 'gallery_lightbox_classes' ) );
        add_action( 'envira_gallery_api_after_show', array( $this, 'gallery_lightbox_html' ) );
        
    }

    /** 
	* Enqueue CSS and JS if Proofing is enabled
	*
	* @since 1.0.0
	*
	* @param array $data Gallery Data
	*/
	public function output_css_js( $data ) {

		// Check if WooCommerce is enabled
		if ( ! $this->get_config( 'woocommerce', $data ) && ! $this->get_config( 'lightbox_woocommerce', $data ) ) {
            return false;
        }

        // Enqueue CSS + JS
		wp_enqueue_style( $this->base->plugin_slug . '-style' );
        wp_enqueue_script( $this->base->plugin_slug . '-script' );

        // Enqueue WooCommerce JS
        wp_enqueue_script( 'woocommerce' );
        wp_enqueue_script( 'wc-add-to-cart-variation' );
        wp_enqueue_script( 'wc-single-product' );
	}

    /**
    * Includes the WooCommerce templates/notices/success.php file, which will then output
    * any "Added to Cart" messages if the user has added a WC Product to their cart
    *
    * @since 1.0.0
    *
    * @param string $html Gallery HTML
    * @param array $data Gallery Data
    * @return Gallery HTML
    */
    public function output_messages( $html, $data ) {

        // Bail if WC notices function doesn't exist
        if ( ! function_exists( 'wc_print_notices' ) ) {
            return $html;
        }

        // Include template and store in variable
        ob_start();
        wc_print_notices();
        $message_html = ob_get_clean();

        // Add message HTML to gallery output
        $html .= $message_html;

        // Return
        return $html;

    }

    /**
     * Outputs the WooCommerce Add to Cart option if the given image has a WooCommerce Product ID
     * specified.
     *
     * @since 1.0.0
     *
     * @param string $image Image HTML
     * @return string Amended Image HTML
     */
    public function output_add_to_cart( $output, $id, $item, $data, $i ) {

        // Check if item has a WooCommerce Product assigned to it
        if ( ! isset( $item['woocommerce_product'] ) ) {
            return $output;
        }
        if ( empty( $item['woocommerce_product'] ) ) {
            return $output;
        }
        
        // Get Product
        global $product, $attributes;
        $product = wc_get_product( $item['woocommerce_product'] );
        $html = '';

        // Depending on the Product Type, get some more information
        switch ( $product->product_type ) {
            /**
            * Simple
            */
            case 'simple':
                // Nothing more to do
                break;

            /**
            * External
            */
            case 'external':
                // Define product URL and button text for WC template
                global $product_url, $button_text;
                $product_url = $product->get_product_url();
                $button_text = $product->get_button_text();
                break;

            /**
            * Grouped
            */
            case 'grouped':
                // Define grouped products for WC template
                global $grouped_products, $quantites_required;
                $grouped_products = array();
                $quantites_required = array();
                break;

            /**
            * Variable
            */
            case 'variable':
                // Define variations and attributes for WC template
                $wc_product_variable = new WC_Product_Variable( $product );
                $available_variations = $wc_product_variable->get_available_variations();
                $attributes = $product->get_variation_attributes();
                break;
        }

        // Include the WooCommerce Plugin template file and capture its output into our $html var 
        ob_start();
        include( WP_PLUGIN_DIR . '/woocommerce/templates/single-product/price.php' );
        include( WP_PLUGIN_DIR . '/woocommerce/templates/single-product/add-to-cart/' . $product->product_type . '.php' );
        $html = ob_get_clean();

        // Check if WooCommerce is enabled
        // If not, we'll hide this markup
        if ( ! $this->get_config( 'woocommerce', $data ) ) {
            $css_class = ' envira-woocommerce-hidden';
        } else {
            $css_class = '';
        }

        // Return
        return $output . '<div class="envira-woocommerce' . $css_class . '">' . $html . '</div>';

    }

    /**
     * Appends the envirabox-proofing class to the main Lightbox wrapper
     *
     * @since 1.0
     *
     * @param array $data Gallery Data
     */
    public function gallery_lightbox_classes( $data ) {

        // Check if Proofing for Lightbox is enabled
        if ( ! $this->get_config( 'lightbox_woocommerce', $data ) ) {
            return;
        }
        ?>
        $('.envirabox-wrap').addClass('envirabox-woocommerce');
        <?php

    }

    /**
    * Appends a checkbox and other fields (if applicable) to the end of each image when viewed in a Lightbox
    *
    * @param array $data Gallery Data
    * @return JS
    */
    public function gallery_lightbox_html( $data ) {
        
        // Check if Proofing for Lightbox is enabled
        if ( ! $this->get_config( 'lightbox_woocommerce', $data ) ) {
            return;
        }

        // Add Output to Lightbox 
        ?>
        var envira_gallery_item_id = $('img.envirabox-image').data('envira-item-id'),
            envira_woocommerce_lightbox_html = $('.envira-woocommerce', $('#envira-gallery-item-' + envira_gallery_item_id)).html();
        $('.envirabox-inner .envira-woocommerce').remove();
        $('.envirabox-inner').append('<div class="envira-woocommerce"></div>');
        $('.envirabox-inner .envira-woocommerce').append(envira_woocommerce_lightbox_html);
        <?php

        // For variations, we need to rebind JS listeners
        ?>
        $( '.variations_form' ).wc_variation_form();
        $( '.variations_form .variations select' ).change();
        <?php

    }
    
    /**
     * Helper method for retrieving gallery config values.
     *
     * @since 1.0.0
     *
     * @param string $key The config key to retrieve.
     * @param array $data The gallery data to use for retrieval.
     * @return string     Key value on success, default if not set.
     */
    public function get_config( $key, $data ) {

        $instance = Envira_Gallery_Shortcode::get_instance();
        return $instance->get_config( $key, $data );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_WooCommerce_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_WooCommerce_Shortcode ) ) {
            self::$instance = new Envira_WooCommerce_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_woocommerce_shortcode = Envira_WooCommerce_Shortcode::get_instance();