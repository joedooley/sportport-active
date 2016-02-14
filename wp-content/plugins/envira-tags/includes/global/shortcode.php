<?php
/**
 * Shortcode class.
 *
 * @since 1.3.0
 *
 * @package Envira_Tags
 * @author  Tim Carr
 */
class Envira_Tags_Shortcode {

    /**
     * Holds the class object.
     *
     * @since 1.3.0
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.3.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Primary class constructor.
     *
     * @since 1.3.0
     */
    public function __construct() {

        add_filter( 'envira_gallery_custom_gallery_data', array( $this, 'gallery_data' ), 10, 3 );
        add_filter( 'envira_gallery_pre_data', array( $this, 'maybe_filter_by_tag' ), 10, 2 );
        add_filter( 'envira_gallery_output_start', array( $this, 'filter_links' ), 1, 2 );
        add_filter( 'envira_gallery_output_item_data', array( $this, 'item_data' ), 1, 4 );
        add_filter( 'envira_gallery_output_item_classes', array( $this, 'filter_classes' ), 10, 4 );
        add_action( 'envira_gallery_api_enviratope', array( $this, 'filter_enviratope' ) );

    }

    /**
     * Possibly retrieves a custom gallery based on tags.
     *
     * @since 1.0.0
     *
     * @param bool $bool   Boolean for determining custom gallery data.
     * @param array $atts  Array of shortcodes attributes.
     * @param object $post The current post object.
     * @return bool|array  False if no custom data is to be loaded, custom data otherwise.
     */
    function gallery_data( $bool, $atts, $post ) {

        // If our custom attributes do not exist, return early.
        if ( ! isset( $atts['tags'] ) && ! isset( $atts['tags_id'] ) ) {
            return $bool;
        }

        // Since this is a dynamic gallery. If there is no gallery set as a default for config, use the first gallery returned.
        $config = array();
        if ( isset( $atts['config'] ) ) {
            $gallery = Envira_Gallery::get_instance()->get_gallery( (int) $atts['config'] );
            if ( ! $gallery ) {
                return $bool;
            } else {
                $config = $gallery['config'];
            }
        } else {
            $galleries = Envira_Gallery::get_instance()->get_galleries();

            // If we have a gallery, use that gallery config. Otherwise, return false.
            if ( ! empty( $galleries[0] ) && isset( $galleries[0]['id'] ) ) {
                $gallery = Envira_Gallery::get_instance()->get_gallery( $galleries[0]['id'] );
                $config  = $gallery['config'];
            } else {
                return $bool;
            }
        }

        // If the config is still empty, return.
        if ( empty( $config ) ) {
            return $bool;
        }

        // Check tags comparison operator
        if ( isset( $atts['operator'] ) ) {
            $config['tags_operator'] = $atts['operator'];
        }

        // If tags is *, get all tags
        if ( $atts['tags'] == '*' ) {
            $tags = array();
            $terms = get_terms( 'envira-tag' );
            foreach ( $terms as $term ) {
                $tags[] = $term->slug;
            }
        } else {
            $tags = explode( ',', (string) $atts['tags'] );
        }

        // Now that we know we want to grab a gallery based on tags, lets do that now.
        $id   = str_replace( '-', '_', $atts['tags_id'] );
        $data = $this->get_gallery_by_tags( $tags, $config, $id );

        // If our data is not returned, return our boolean value, otherwise return the data.
        if ( ! $data ) {
            return $bool;
        } else {
            return apply_filters( 'envira_tags_custom_gallery_data', $data, $atts, $post, $tags, $config, $id );
        }

    }

    /**
     * Maybe filter the Gallery Data by a Tag, if the Tag is present in the URL
     *
     * @since 1.1.1
     *
     * @param array $data Gallery Data
     * @param int $gallery_id Gallery ID
     * @return array Gallery Data
     */
    function maybe_filter_by_tag( $data, $gallery_id ) {

        // Check a tag exists
        $tag = get_query_var( 'envira-tag' );
        if ( empty( $tag ) ) {
            return $data;
        }

        // Filter data by that tag
        foreach ( $data['gallery'] as $attachment_id => $item ) {
            if ( ! has_term( $tag, 'envira-tag', $attachment_id ) ) {
                unset ( $data['gallery'][ $attachment_id ] );
                continue;
            }
        }


        return $data;

    }

    /**
     * Outputs the tag filter links at the top of the gallery.
     *
     * @since 1.0.0
     *
     * @param string $gallery  The HTML output for the gallery.
     * @param array $data      Data for the Envira gallery.
     * @return string $gallery Amended gallery HTML.
     */
    function filter_links( $gallery, $data ) {

        // If tag filtering is not enabled, return early.
        if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'tags', $data ) ) {
            return $gallery;
        }

        // Now we need to ensure that we actually have tags to process. If we have no tags, return early.
        $tags = $this->get_tags_from_gallery( $data );
        if ( ! $tags ) {
            return $gallery;
        }

        // Append the tag filter markup.
        $gallery .= $this->get_filter_markup( $tags, $data );

        // Filter to allow other addons to add their own filtering
        $gallery = apply_filters( 'envira_tags_filter_links', $gallery, $data );

        // Return the amended gallery HTML.
        return $gallery;

    }

    /**
     * Adds taxonomy terms to $item, so envira_tags_filter_classes can
     * output taxonomy term classes against the $item
     *
     * @since 1.0.5
     * @param array $item     Array of item data.
     * @param int $id         Item ID
     * @param array $data     Array of gallery data.
     * @param int $i          The current position in the gallery.
     * @return array $item Amended item.
     */
    function item_data( $item, $id, $data, $i ) {

        // If no more tags, return the classes.
        $terms = wp_get_object_terms( $id, 'envira-tag' );
        if ( count( $terms ) == 0 ) {
            return $item;
        }

        // Loop through tags and output them as custom classes.
        foreach ( $terms as $term ) {
            // Set new array key if it doesn't exist
            if ( !isset($item['tags'] )) {
                $item['tags'] = array();
            }

            // Add term to array key
            $item['tags'][$term->term_id] = $term->name;
        }

        // Filter to allow other addons to add their own taxonomy terms
        $item = apply_filters( 'envira_tags_item_data', $item, $id, $data, $i );

        return $item;

    }

    /**
     * Outputs the filter classes on the gallery item.
     *
     * @since 1.0.0
     *
     * @param array $classes  Current item classes.
     * @param array $item     Array of item data.
     * @param int $i          The current position in the gallery.
     * @param array $data     Array of gallery data.
     * @return array $classes Amended item classes.
     */
    function filter_classes( $classes, $item, $i, $data ) {

        // If filtering is not enabled, do nothing.
        if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'tags', $data ) ) {
            return $classes;
        }

        // All items need to have envira-tag-all for filtering, even if no classes are attached to the item.
        $classes[] = 'envira-tag-all';

        // If no more tags, return the classes.
        if ( !isset( $item['tags'] ) || count( $item['tags'] ) == 0 ) {
            return $classes;
        }

        // Loop through tags and output them as custom classes.
        foreach ( $item['tags'] as $termID => $termName ) {
            $classes[] = 'envira-tag-' . sanitize_html_class( $termName );
        }

        // Filter to allow other addons to add their own class terms
        $classes = apply_filters( 'envira_tags_filter_classes', $classes, $item, $i, $data );

        return $classes;

    }

    /**
     * Animates the filter process when a filter tag is selected.
     *
     * @since 1.0.0
     *
     * @param array $data Array of gallery data.
     * @return null Return early if no tags are available.
     */
    function filter_enviratope( $data ) {

        if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'tags', $data ) ) {
            return;
        }

        // Make filtering happen on when a filter item is clicked.
        ob_start();
        ?>
        $('#envira-tags-filter-list-<?php echo $data['id']; ?>').on('click', 'a.envira-tags-filter-link', function(e){
            e.preventDefault();

            <?php
            // Prepare variables.
            ?>
            var $this    = $(this),
                selector = $this.attr('data-envira-filter'),
                filter   = $('#envira-tags-filter-list-<?php echo $data['id']; ?>');

            <?php
            // If the item is already active, do nothing.
            ?>
            if ( $this.hasClass('envira-tags-filter-active') ) {
                return;
            }

            <?php
            // Do filtering.
            ?>
            envira_container_<?php echo $data['id']; ?>.enviratope( {
                <?php do_action( 'envira_gallery_api_enviratope_config', $data ); ?>
                filter: selector,
                itemSelector: '.envira-gallery-item',
                masonry: {
                    columnWidth: '.envira-gallery-item'
                }
            });
            
            <?php
            // Reset classes properly.
            ?>
            filter.find('.envira-tags-filter-active').removeClass('envira-tags-filter-active');
            $this.addClass('envira-tags-filter-active');

            <?php
            // Iterate through each gallery image, removing the rel attribute if it doesn't
            // match the chosen tag
            ?>
            selector = selector.slice(1);
            $('#envira-gallery-<?php echo $data['id']; ?> > div.envira-gallery-item').each(function() {
                <?php
                // Check if this item has the selector we want
                ?>
                if ($(this).hasClass(selector)) {
                    $('a', $(this)).attr('rel', 'enviragallery<?php echo $data['id']; ?>');
                } else {
                    $('a', $(this)).attr('rel', '');
                }
            });        
        });
        <?php
        echo ob_get_clean();

    }

    /**
     * Queries a custom gallery set based on tags.
     *
     * @since 1.0.0
     *
     * @param array $tags     Array of tags to use for querying the gallery.
     * @param array $config   Array of gallery config to use.
     * @param string $tags_id Custom ID for this gallery.
     * @return bool|array     False if fails to get data, array of data otherwise.
     */
    function get_gallery_by_tags( $tags, $config, $tags_id ) {

        // Attempt to return the transient first, otherwise generate the new query to retrieve the data.
        if ( false === ( $gallery = get_transient( '_eg_tags_' . $tags_id ) ) ) {
            $gallery = $this->_get_gallery_by_tags( $tags, $config, $tags_id );
            if ( $gallery ) {
                $expiration = Envira_Gallery_Common::get_instance()->get_transient_expiration_time( 'envira-tags' );
                set_transient( '_eg_tags_' . $tags_id, $gallery, $expiration );
            }
        }

        // Return the gallery data.
        return $gallery;

    }

    /**
     * Internal function that queries a custom gallery set based on tags.
     *
     * @since 1.0.0
     *
     * @param array $tags     Array of tags to use for querying the gallery.
     * @param array $config   Array of gallery config to use.
     * @param string $tags_id Custom ID for this gallery.
     * @return bool|array     False if fails to get data, array of data otherwise.
     */
    function _get_gallery_by_tags( $tags, $config, $tags_id ) {

        // Retrieve galleries.
        $galleries = Envira_Gallery::get_instance()->get_galleries();
        if ( ! $galleries ) {
            return false;
        }

        // Get comparison operator
        $operator = ( isset( $config['tags_operator'] ) ? $config['tags_operator'] : 'OR' );
        
        // Loop through the galleries and pluck out any images that match our tag selection.
        $images = array();
        foreach ( (array) $galleries as $i => $gallery ) {
            foreach ( (array) $gallery['gallery'] as $id => $item ) {
                // If there are no tags, keep going.
                $terms = wp_get_object_terms( $id, 'envira-tag' );
                
                if ( count( $terms ) == 0 ) {
                    continue;
                }

                // Loop through the tags to see if we have a match.
                switch ( $operator ) {
                    /**
                    * Image must have all tags
                    */
                    case 'AND':
                        $matched = true;

                        // Build array of terms
                        $terms_arr = array();
                        foreach ( $terms as $term ) {
                            $terms_arr[] = $term->slug;
                        }

                        // Iterate through requested tags
                        foreach ( $tags as $tag ) {
                            // Does this tag exist in this image?
                            if ( ! in_array( $tag, $terms_arr ) ) {
                                // No, it doesn't - bail
                                $matched = false;
                                break;
                            }
                        }

                        // If here and $matched, all tags exist in this image
                        if ( $matched ) {
                            $images[ $id ] = $galleries[$i]['gallery'][$id];
                        }

                        break;
                    
                    /**
                    * Image can have any tag(s)
                    */
                    case 'OR':
                    default:
                        foreach ( $terms as $term ) {
                            if ( in_array( $term->name, $tags ) || in_array( $term->slug, $tags ) ) {
                                $images[$id] = $galleries[$i]['gallery'][$id];
                                break; // Break foreach
                            }
                        }
                        break; // Break switch
                }
            }
        }

        // If the images array is still empty, return false, otherwise return the images.
        if ( empty( $images ) ) {
            return false;
        } else {
            // We are good to go. Prepare the data and return it with a filter.
            $data['id']      = $tags_id;
            $data['config']  = $config;
            $data['gallery'] = $images;

            return apply_filters( 'envira_tags_get_gallery_by_tags', $data, $config, $tags_id );
        }

    }

    /**
     * Retrieves a unique list of tags for a gallery.
     *
     * @since 1.0.0
     *
     * @param array $gallery_data   Array of gallery data to use.
     * @param string $taxonomy      Taxonomy to check
     * @return bool|array           False if no tags are found, array of tags otherwise.
     */
    private function get_tags_from_gallery( $gallery_data, $taxonomy = 'envira-tag' ) {

        // Loop through the images in the gallery and grab tags.
        $tags     = array();
        $has_tags = false;
        
        foreach ( (array) $gallery_data['gallery'] as $id => $item ) {
            // If there are no tags, keep going.
            $terms = wp_get_object_terms( $id, $taxonomy );
            if ( count($terms) == 0 ) {
                continue;
            }

            // Store the tags and set our flag to true.
            foreach ( $terms as $term ) {
                $tags[ $term->slug ] = $term->name;
            }

            $has_tags = true;
        }

        // If we have no tags, return false
        if ( ! $has_tags ) {
            return false;
        }
        
        // If the gallery specifies the "Tags to include in Filtering" option (tags_filter), only return those tags in the tag list
        if ( $taxonomy == 'envira-tag' && isset( $gallery_data['config']['tags_filter'] ) && !empty( $gallery_data['config']['tags_filter'] ) ) {
            // Get filtered tags and check we have at least one tag specified
            $filtered_tags = explode( ',', $gallery_data['config']['tags_filter'] );
            
            if ( count( $filtered_tags) > 0 ) {
                $image_tags = $tags;
                $tags = array();

                // Iterate through filtered tags and check if each tag exists in $image_tags array
                // If so, add to our final $tags array
                foreach ( $filtered_tags as $tag ) {
                    if ( in_array( $tag, $image_tags ) ) {
                        // Add image tag slug and name to array of tags for filtering
                        foreach ( $image_tags as $tag_slug => $tag_name ) {
                            if ( $tag_name == $tag ) {
                                $tags[ $tag_slug ] = $tag_name;
                            }
                        }
                    }
                }
            }
        }

        // Sort tags
        asort( $tags );

        // Return filtered tags
        return apply_filters( 'envira_tags_gallery_tags', array_unique( $tags ), $gallery_data );

    }

    /**
     * Retrieves the custom markup for the tag filter list.
     *
     * @since 1.0.0
     *
     * @param array $tags Array of tags to use for filtering.
     * @param array $data Array of gallery data.
     * @return string     Custom markup for the tag filter list.
     */
    private function get_filter_markup( $tags, $data ) {

        global $post;

        // Get instance
        $instance = Envira_Gallery_Shortcode::get_instance();

        $markup  = '<ul id="envira-tags-filter-list-' . sanitize_html_class( $data['id'] ) . '" class="envira-tags-filter-list envira-clear">';

        // Go ahead and all the "All" tag filter.
        $markup .= '<li id="envira-tag-filter-all" class="envira-tag-filter">';
            $markup .= '<a href="' . get_permalink( $post->ID ) . '" class="envira-tags-filter-link envira-tags-filter-active" title="' . __( 'Filter by All', 'envira-tags' ) . '" data-envira-filter=".envira-tag-all">' . $instance->get_config( 'tags_all', $data ) . '</a>';
        $markup .= '</li>';

        // Loop through the tags and add them to the filter list.
        foreach ( $tags as $slug => $tag ) {
            // Build non-JS URL
            $url = add_query_arg( array(
                'envira-tag' => sanitize_html_class( $slug ) 
            ), get_permalink( $post->ID ) );

            $markup .= '<li id="envira-tag-filter-' . sanitize_html_class( $slug ) . '" class="envira-tags-filter">';
                $markup .= '<a href="' . $url . '" class="envira-tags-filter-link" title="' . sprintf( __( 'Filter by %s', 'envira-tags' ), $tag ) . '" data-envira-filter=".envira-tag-' . sanitize_html_class( $tag ) . '">';
                    $markup .= $tag;
                $markup .= '</a>';
            $markup .= '</li>';
        }

        // Close up the markup.
        $markup .= '</ul>';

        return apply_filters( 'envira_tags_filter_markup', $markup, $tags, $data );

    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.3.0
     *
     * @return object The Envira_Tags_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Shortcode ) ) {
            self::$instance = new Envira_Tags_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_tags_shortcode = Envira_Tags_Shortcode::get_instance();