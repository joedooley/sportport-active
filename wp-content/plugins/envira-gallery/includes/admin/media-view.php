<?php
/**
 * Media View class.
 *
 * @since 1.0.3
 *
 * @package Envira_Gallery
 * @author  Tim Carr
 */
class Envira_Gallery_Media_View {

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

        // Base
        $this->base = Envira_Gallery::get_instance();

        // Modals
        add_filter( 'envira_gallery_media_view_strings', array( $this, 'media_view_strings' ) );
        add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );

    }

    /**
    * Adds media view (modal) strings
    *
    * @since 1.0.3
    *
    * @param    array   $strings    Media View Strings
    * @return   array               Media View Strings
    */ 
    public function media_view_strings( $strings ) {

        return $strings;

    }

    /**
    * Outputs backbone.js wp.media compatible templates, which are loaded into the modal
    * view
    *
    * @since 1.0.3
    */
    public function print_media_templates() {

    	// Get the Gallery Post and Config
    	global $post;
    	if ( isset( $post ) ) {
    		$post_id = absint( $post->ID );
    	} else {
    		$post_id = 0;
    	}

    	// Bail if we're not editing an Envira Gallery
    	if ( get_post_type( $post_id ) != 'envira' ) {
    		return;
    	}

        // Meta Editor
        // Use: wp.media.template( 'envira-meta-editor' )
        ?>
        <script type="text/html" id="tmpl-envira-meta-editor">
			<div class="edit-media-header">
				<button class="left dashicons"><span class="screen-reader-text"><?php _e( 'Edit previous media item' ); ?></span></button>
				<button class="right dashicons"><span class="screen-reader-text"><?php _e( 'Edit next media item' ); ?></span></button>
			</div>
			<div class="media-frame-title">
				<h1><?php _e( 'Edit Item', 'envira-gallery' ); ?></h1>
			</div>
			<div class="media-frame-content">
				<div class="attachment-details save-ready">
					<!-- Left -->
	                <div class="attachment-media-view portrait">
	                    <div class="thumbnail thumbnail-image">
	                        <img class="details-image" src="{{ data.src }}" draggable="false" />
	                    </div>
	                </div>
	                
	                <!-- Right -->
	                <div class="attachment-info">
	                    <!-- Details -->
	                    <div class="details">
	                        <?php
	                        do_action( 'envira_gallery_before_meta_help_items', $post_id ); 
	                        ?>
	                         
	                        <div class="filename">
								<strong><?php _e( 'Title', 'envira-gallery' ); ?></strong>
								<?php _e( 'Image titles can take any type of HTML. You can adjust the position of the titles in the main Lightbox settings.', 'envira-gallery' ); ?>
								<br /><br />
	                        </div>
	                        
	                        <div class="filename">
								<strong><?php _e( 'Caption', 'envira-gallery' ); ?></strong>
								<?php _e( 'Caption can take any type of HTML, and are displayed when an image is clicked.', 'envira-gallery' ); ?>
								<br /><br />
	                        </div>
	                        
	                        <div class="filename">
								<strong><?php _e( 'Alt Text', 'envira-gallery' ); ?></strong>
								<?php _e( 'Very important for SEO, the Alt Text describes the image.', 'envira-gallery' ); ?>
								<br /><br />
	                        </div>
	                        
	                        <div class="filename">
								<strong><?php _e( 'URL', 'envira-gallery' ); ?></strong>
								<?php _e( 'Enter a hyperlink if you wish to link this image to somewhere other than its full size image.', 'envira-gallery' ); ?>
								<br /><br />
	                        </div>
	                        
	                        <?php
	                        do_action( 'envira_gallery_after_meta_help_items', $post_id ); 
	                        ?>
	                    </div>

	                    <!-- Settings -->
	                    <div class="settings">
	                    	<!-- Attachment ID -->
	                    	<input type="hidden" name="id" value="{{ data.id }}" />
	                        
	                        <!-- Image Title -->
	                        <label class="setting">
	                            <span class="name"><?php _e( 'Title', 'envira-gallery' ); ?></span>
	                            <input type="text" name="title" value="{{ data.title }}" />
	                        </label>
	                        
	                        <!-- Caption -->
	                        <div class="setting">
	                            <span class="name"><?php _e( 'Caption', 'envira-gallery' ); ?></span>	
	                            <?php 
                                wp_editor( '', 'caption', array( 
                                	'media_buttons' => false, 
                                	'wpautop' 		=> false, 
                                	'tinymce' 		=> false, 
                                	'textarea_name' => 'caption', 
                                	'quicktags' => array( 
                                		'buttons' => 'strong,em,link,ul,ol,li,close' 
                                	),
                                ) ); 
                                ?>
	                        </div>
	                        
	                        <!-- Alt Text -->
	                        <label class="setting">
	                            <span class="name"><?php _e( 'Alt Text', 'envira-gallery' ); ?></span>
	                            <input type="text" name="alt" value="{{ data.alt }}" />
	                        </label>
	                        
	                        <!-- Link -->
	                        <label class="setting">
	                            <span class="name"><?php _e( 'URL', 'envira-gallery' ); ?></span>
	                            <input type="text" name="link" value="{{ data.link }}" />
	                            <# if ( typeof( data.id ) === 'number' ) { #>
		                            <span class="buttons">
		                            	<button class="button button-small media-file"><?php _e( 'Media File', 'envira-gallery' ); ?></button>
										<button class="button button-small attachment-page"><?php _e( 'Attachment Page', 'envira-gallery' ); ?></button>
									</span>
								<# } #>
							</label>
							
							<!-- Link in New Window -->
                            <label class="setting">
                            	<span class="name"><?php _e( 'Open URL in New Window?', 'envira-gallery' ); ?></span>
								<input type="checkbox" name="link_new_window" value="1"<# if ( data.link_new_window == '1' ) { #> checked <# } #> />
                            </label>

							<!-- Addons can populate the UI here -->
							<div class="addons"></div>
	                    </div>
	                    <!-- /.settings -->     
	                   
	                    <!-- Actions -->
	                    <div class="actions">
	                        <a href="#" class="envira-gallery-meta-submit button media-button button-large button-primary media-button-insert" title="<?php esc_attr_e( 'Save Metadata', 'envira-gallery' ); ?>">
	                        	<?php _e( 'Save Metadata', 'envira-gallery' ); ?>
	                        </a>

							<!-- Save Spinner -->
	                        <span class="settings-save-status">
		                        <span class="spinner"></span>
		                        <span class="saved"><?php _e( 'Saved.', 'envira-gallery' ); ?></span>
	                        </span>
	                    </div>
	                    <!-- /.actions -->
	                </div>
	            </div>
			</div>
		</script> 

        <?php

    }
	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Gallery_Media_View object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gallery_Media_View ) ) {
            self::$instance = new Envira_Gallery_Media_View();
        }

        return self::$instance;

    }

}

// Load the media class.
$envira_gallery_media_view = Envira_Gallery_Media_View::get_instance();