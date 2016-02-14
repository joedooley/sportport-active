<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Pagination
 * @author  Tim Carr
 */
class Envira_Social_Shortcode {

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
     * Holds a flag to determine whether metadata has been set
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $meta_data_set = false;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
	    
	    // Load the base class object.
        $this->base = Envira_Social::get_instance();
	    
	    // Register CSS
        wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/envira-social.css', $this->base->file ), array(), $this->base->version );
	    
        // Register JS
        wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-social-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );

	    // Gallery
        add_action( 'wp_head', array( $this, 'metadata' ) );
        add_action( 'envira_gallery_before_output', array( $this, 'gallery_output_css_js' ) );
        add_filter( 'envira_gallery_output_before_link', array( $this, 'gallery_output_html' ), 10, 5 );
        add_action( 'envira_gallery_api_after_show', array( $this, 'gallery_output_lightbox_html' ) );

        // Album
        add_action( 'envira_albums_before_output', array( $this, 'albums_output_css_js' ) );
        add_action( 'envira_albums_api_after_show', array( $this, 'gallery_output_lightbox_html' ) );

    }

    /**
    * Set Open Graph and Twitter Card metadata to share the chosen gallery and image
    * The Gallery ID and Gallery Item ID will be specified in the URL
    *
    * @since 1.0.5
    */
    public function metadata() {

        global $post;

        // Bail if metadata already set
        if ( $this->meta_data_set ) {
            return;
        }

        // Get gallery ID and gallery item ID
        $gallery_id      = ( isset( $_GET['envira_social_gallery_id'] ) ? sanitize_text_field( $_GET['envira_social_gallery_id'] ) : '' );
        $gallery_item_id = ( isset( $_GET['envira_social_gallery_item_id'] ) ? sanitize_text_field ( $_GET['envira_social_gallery_item_id'] ) : '' );

        // Bail if either ID are missing
        if ( empty( $gallery_id ) || empty( $gallery_item_id ) ) {
            return;
        }

        // Get gallery
        $data = Envira_Gallery::get_instance()->get_gallery( $gallery_id );
        if ( ! $data ) {
            return;
        }

        // Get gallery item
        if ( ! isset( $data['gallery'][ $gallery_item_id ] ) ) {
            return;
        }
        $item = $data['gallery'][ $gallery_item_id ];

        // If here, we have an item
        // Get instance
        $common = Envira_Social_Common::get_instance();

        // Set metadata
        ?>
        <link rel="image_src" href="<?php echo $item['src']; ?>" />

        <meta property="og:title" content="<?php echo $item['title']; ?>" />
        <meta property="og:image" content="<?php echo $item['src']; ?>" />
        <meta property="og:description" content="<?php echo $item['caption']; ?>" />

        <meta name="twitter:card" content="photo" />
        <meta name="twitter:site" content="<?php echo $common->get_setting( 'twitter_username' ); ?>" />
        <meta name="twitter:title" content="<?php echo $item['title']; ?>" />
        <meta name="twitter:description" content="<?php echo $item['caption']; ?>" />
        <meta name="twitter:image" content="<?php echo $item['src']; ?>" />
        <meta name="twitter:url" content="<?php echo get_permalink( $post->ID ); ?>" />
        <?php

        // Mark our metadata as loaded
        $this->meta_data_set = true;

    }

    /**
	* Enqueue CSS and JS if Social Sharing is enabled
	*
	* @since 1.0.0
	*
	* @param array $data Gallery Data
	*/
	public function gallery_output_css_js( $data ) {

		// Check if Social Sharing Buttons output is enabled
        if ( ! $this->get_config( 'social', $data ) && ! $this->get_config( 'social_lightbox', $data ) ) {
			return;
		}

        // Get instance
        $common = Envira_Social_Common::get_instance();
		
		// Enqueue CSS + JS
		wp_enqueue_style( $this->base->plugin_slug . '-style' );
        wp_enqueue_script( $this->base->plugin_slug . '-script' );
        wp_localize_script( $this->base->plugin_slug . '-script', 'envira_social', array(
            'facebook_app_id' => $common->get_setting( 'facebook_app_id' ),
        ) );
		
	}

    /**
    * Enqueue CSS and JS for Albums if Social Sharing is enabled
    *
    * @since 1.0.3
    *
    * @param array $data Album Data
    */
    public function albums_output_css_js( $data ) {

        global $post;
        
        // Check if Social Sharing Buttons output is enabled
        if ( ! $this->get_config( 'social_lightbox', $data ) ) {
            return;
        }

        // Get instance
        $common = Envira_Social_Common::get_instance();
        
        // Enqueue CSS + JS
        wp_enqueue_style( $this->base->plugin_slug . '-style' );
        wp_enqueue_script( $this->base->plugin_slug . '-script' );
        wp_localize_script( $this->base->plugin_slug . '-script', 'envira_social', array(
            'facebook_app_id' => $common->get_setting( 'facebook_app_id' ),
        ) );
    }

    
    /**
	* Outputs Social Media Sharing HTML for the Gallery thumbnail
	*
	* @since 1.0.0
	* 
	* @param string $output HTML Output
	* @param int $id Attachment ID
	* @param array $item Image Item
	* @param array $data Gallery Config
	* @param int $i Image number in gallery
	* @return string HTML Output
	*/
    public function gallery_output_html( $output, $id, $item, $data, $i ) {

        // Check if Social Sharing Buttons output is enabled
        if ( ! $this->get_config( 'social', $data ) ) {
            return $output;
        }

        // Prepend Button(s)
	    $buttons = $this->get_social_sharing_buttons( $id, $item, $data, $i );

		return $output . $buttons;
	    
    }
    
    /**
	* Gallery: Outputs EXIF Lightbox data when a lightbox image is displayed from a Gallery
	*
	* @param array $data Gallery Data
	* @return JS
	*/
    public function gallery_output_lightbox_html( $data ) {
	    
        // Check if Social Sharing Buttons output is enabled
        if ( ! $this->get_config( 'social_lightbox', $data ) ) {
            return;
        }

        // Get Button(s)
        $buttons = $this->get_lightbox_social_sharing_buttons( $data );
	    ?>
        $('.envirabox-inner .envira-social').remove();
        $('.envirabox-inner').append('<div class="envira-social"></div>');
        $('.envirabox-inner .envira-social').append('<?php echo $buttons; ?>');
		<?php
			    
    }

    /**
    * Helper to output social sharing buttons for an image
    *
    * @since 1.0.0
    *
    * @global object $post Gallery
    *
    * @param int   $id   Image ID
    * @param array $item Image Data
    * @param array $data Gallery Data
    * @param int $i Index
    * @return string HTML
    */
    function get_social_sharing_buttons( $id, $item, $data, $i ) {
        
        global $post;

        // Get instance
        $common = Envira_Social_Common::get_instance();

        // Start
        $buttons = '<div class="envira-social-buttons position-' . $this->get_config( 'social_position', $data ) . ' orientation-' . $this->get_config( 'social_orientation', $data ) . '">';

        // Get the Post/Page/CPT we're viewing
        $post_url = get_permalink( $post->ID );

        // Allow devs to filter the title and caption
        // Don't worry about url encoding - we'll handle this
        $title = apply_filters( 'envira_social_sharing_title', $item['title'], $id, $item, $data, $i );
        $caption = apply_filters( 'envira_social_sharing_caption', $item['caption'], $id, $item, $data, $i );

        // Iterate through networks, adding a button if enabled in the settings
        foreach ( $common->get_networks() as $network => $name ) {
            // Unset vars that might have been set in a previous loop
            unset($url, $width, $height);

            // Skip network if not enabled
            if ( ! $this->get_config( 'social_' . $network, $data ) ) {
                continue;
            }

            // Define sharing URL and popup window dimensions
            switch ( $network ) {

                /**
                * Facebook
                */
                case 'facebook':
                    // Get App ID
                    $app_id = $common->get_setting( 'facebook_app_id' );
                    $url = 'https://www.facebook.com/dialog/feed?app_id=' . $app_id . '&display=popup&link=' . urlencode( $post_url ) . '&picture=' . urlencode( $item['src'] ) . '&name=' . urlencode( strip_tags( $title ) ) . '&caption=' . urlencode( strip_tags( $caption ) )  . '&description=' . $item['alt'] . '&redirect_uri=' . urlencode( $post_url . '#envira_social_sharing_close' );
                    $width = 626;
                    $height = 436;
                    break;

                /**
                * Twitter
                */
                case 'twitter':
                    $url = 'https://twitter.com/intent/tweet?text=' . urlencode( strip_tags( $caption ) ) . '&url=' . urlencode( $post_url . '?envira_social_gallery_id=' . $data['id'] . '&envira_social_gallery_item_id=' . $id );
                    $width = 500;
                    $height = 300;
                    break;
                /**
                * Google
                */
                case 'google':
                    $url = 'https://plus.google.com/share?url=' . urlencode( $post_url . '?envira_social_gallery_id=' . $data['id'] . '&envira_social_gallery_item_id=' . $id );
                    $width = 500;
                    $height = 400;
                    break;

                /**
                * Pinterest
                */
                case 'pinterest':
                    $url = 'http://pinterest.com/pin/create/button/?url=' . urlencode( $post_url ) . '&media=' . urlencode( $item['src'] ) . '&description=' . urlencode( strip_tags( $caption ) );
                    $width = 500;
                    $height = 400;
                    break;

                /**
                * Email
                */
                case 'email':
                    $url = 'mailto:?subject=' . urlencode( $title ) . '&body=' . urlencode( $item['src'] );
                    $width = 500;
                    $height = 400;
                    break;

            }
            
            // Build Button HTML
            $buttons .= '<div class="envira-social-network ' . $network . '" data-width="' . $width . '" data-height="' . $height . '" data-network="' . $network . '">
                <a href="' . $url . '" class="envira-social-button">'.__( 'Share', 'envira-social' ).' <span>on ' . $name . '</span></a>
            </div>';
        }
        
        // Close button HTML
        $buttons .= '
        </div>';

        // Return
        return $buttons; 
    }

    /**
    * Helper to output social sharing buttons for the lightbox
    *
    * @since 1.0.0
    *
    * @param array $data Gallery Data
    * @return string HTML
    */
    function get_lightbox_social_sharing_buttons( $data ) {
        
        // Get instance
        $common = Envira_Social_Common::get_instance();

        // Start
        $buttons = '<div class="envira-social-buttons position-' . $this->get_config( 'social_lightbox_position', $data ) . ' ' . ( ( $this->get_config( 'social_lightbox_outside', $data ) == 1 ) ? 'outside' : 'inside' ) . ' orientation-' . $this->get_config( 'social_lightbox_orientation', $data ) . '">';

        // Iterate through networks, adding a button if enabled in the settings
        foreach ( $common->get_networks() as $network => $name ) {
            // Unset vars that might have been set in a previous loop
            unset($url, $width, $height);

            // Skip network if not enabled
            if ( ! $this->get_config( 'social_lightbox_' . $network, $data ) ) {
                continue;
            }

            // Define sharing URL and popup window dimensions
            switch ( $network ) {

                /**
                * Facebook
                */
                case 'facebook':
                    $url = 'https://www.facebook.com/sharer/sharer.php?s=100&';
                    $width = 626;
                    $height = 436;
                    break;

                /**
                * Twitter
                */
                case 'twitter':
                    $url = 'https://twitter.com/intent/tweet?';
                    $width = 500;
                    $height = 300;
                    break;
                /**
                * Google
                */
                case 'google':
                    $url = 'https://plus.google.com/share?';
                    $width = 500;
                    $height = 400;
                    break;

                /**
                * Pinterest
                */
                case 'pinterest':
                    $url = 'http://pinterest.com/pin/create/button/?';
                    $width = 500;
                    $height = 400;
                    break;

                /**
                * Email
                */
                case 'email':
                    $url = 'mailto:?';
                    $width = 500;
                    $height = 400;
                    break;

            }
            
            // Build Button HTML
            $buttons .= '<div class="envira-social-network ' . $network . '" data-width="' . $width . '" data-height="' . $height . '" data-network="' . $network . '">
                <a href="#" class="envira-social-button">' . __( 'Share', 'envira-social' ) . ' <span>on ' . $name . '</span></a>
            </div>';
        }
        
        // Close button HTML
        $buttons .= '
        </div>';

        // Return
        return str_replace( "\n", "", $buttons );
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

        // Determine whether data is for a gallery or album
        $post_type = get_post_type( $data['id'] );

        // If post type is false, we're probably on a dynamic gallery/album
        // Grab the ID from the config
        if ( ! $post_type && isset( $data['config']['id'] ) ) {
            $post_type = get_post_type( $data['config']['id'] );
        }

        switch ( $post_type ) {
            case 'envira':
                $instance = Envira_Gallery_Shortcode::get_instance();
                break;
            case 'envira_album':
                $instance = Envira_Albums_Shortcode::get_instance();
                break;
        }

        // If no instance was set, bail
        if ( ! isset( $instance ) ) {
            return false;
        }

        // Return value
        return $instance->get_config( $key, $data );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Social_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Social_Shortcode ) ) {
            self::$instance = new Envira_Social_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_social_shortcode = Envira_Social_Shortcode::get_instance();