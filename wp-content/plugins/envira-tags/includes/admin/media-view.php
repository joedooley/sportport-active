<?php
/**
 * Media View class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Tim Carr
 */
class Envira_Tags_Media_View {

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
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );

    }

    /**
    * Outputs backbone.js wp.media compatible templates, which are loaded into the modal
    * view
    *
    * @since 1.0.3
    */
    public function print_media_templates() {

        // Meta Editor
        // Use: wp.media.template( 'envira-meta-editor' )
        ?>
        <script type="text/html" id="tmpl-envira-meta-editor-tags">
            <label class="setting">
                <span class="name"><?php _e( 'Tags', 'envira-tags' ); ?></span>
                <input type="text" name="tags" value="{{ data.tags }}" />

                <?php
                // Get popular tags
                $tags = get_terms( 'envira-tag', array( 
                    'number'    => 5, 
                    'orderby'   => 'count', 
                    'order'     => 'DESC' 
                ) );
                foreach ( $tags as $key => $tag ) {
                    $tags[ $key ]->link = '#';
                }

                // Output tag cloud
                ?>
                <p class="the-tagcloud">
                    <?php
                    echo wp_generate_tag_cloud( $tags, array(
                        'filter' => 0
                    ) );
                    ?>
                </p>
            </label>
        </script>
        <?php

    }
	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Tags_Media_View object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Media_View ) ) {
            self::$instance = new Envira_Tags_Media_View();
        }

        return self::$instance;

    }

}

// Load the media view class.
$envira_tags_media_view = Envira_Tags_Media_View::get_instance();