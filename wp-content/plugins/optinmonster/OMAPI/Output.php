<?php
/**
 * Output class.
 *
 * @since 1.0.0
 *
 * @package OMAPI
 * @author  Thomas Griffin
 */
class OMAPI_Output {

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
     * Holds the meta fields used for checking output statuses.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $fields = array( 'enabled', 'global', 'automatic', 'users', 'never', 'only', 'categories', 'taxonomies', 'show', 'type', 'test', 'shortcode', 'shortcode_output', 'mailpoet' );

    /**
     * Flag for determining if localized JS variable is output.
     *
     * @since 1.0.0
     *
     * @var bool
     */
    public $localized = false;

    /**
     * Holds JS slugs for maybe parsing shortcodes.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $slugs = array();

    /**
     * Holds shortcode output.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $shortcodes = array();

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

	    // Set our object.
	    $this->set();

		// Load actions and filters.
		add_action( 'wp_enqueue_scripts', array( $this, 'api_script' ) );
        add_action( 'wp_footer', array( $this, 'localize' ), 9999 );
        add_action( 'wp_footer', array( $this, 'maybe_parse_shortcodes' ), 11 );

        // Maybe load OptinMonster.
        $this->maybe_load_optinmonster();

    }

    /**
     * Sets our object instance and base class instance.
     *
     * @since 1.0.0
     */
    public function set() {

        self::$instance = $this;
        $this->base 	= OMAPI::get_instance();
        $this->fields   = apply_filters( 'optin_monster_api_output_fields', $this->fields );

    }

    /**
     * Enqueues the OptinMonster API script.
     *
     * @since 1.0.0
     */
    public function api_script() {

        wp_enqueue_script( $this->base->plugin_slug . '-api-script', OPTINMONSTER_API, array( 'jquery' ), $this->base->version );

        if ( version_compare( get_bloginfo( 'version' ), '4.1.0', '>=' ) ) {
	        add_filter( 'script_loader_tag', array( $this, 'filter_api_script' ), 10, 2 );
        } else {
	        add_filter( 'clean_url', array( $this, 'filter_api_url' ) );
	    }

    }

    /**
     * Filters the API script tag to add a custom ID.
     *
     * @since 1.0.0
     *
     * @param string $tag 	 The HTML script output.
     * @param string $handle The script handle to target.
     * @return string $tag   Amended HTML script with our ID attribute appended.
     */
    public function filter_api_script( $tag, $handle ) {

        // If the handle is not ours, do nothing.
        if ( $this->base->plugin_slug . '-api-script' !== $handle ) {
	        return $tag;
	    }

	    // Adjust the output to add our custom script ID.
	    return str_replace( ' src', ' data-cfasync="false" id="omapi-script" src', $tag );

    }

    /**
     * Filters the API script tag to add a custom ID.
     *
     * @since 1.0.0
     *
     * @param string $url  The URL to filter.
     * @return string $url Amended URL with our ID attribute appended.
     */
    public function filter_api_url( $url ) {

        // If the handle is not ours, do nothing.
        if ( false === strpos( $url, 'a.optnmnstr.com/app/js/api.min.js' ) ) {
	        return $url;
	    }

	    // Adjust the URL to add our custom script ID.
	    return "$url' id='omapi-script";

    }

    /**
     * Set the default query arg filter for OptinMonster.
     *
     * @since 1.0.0
     *
     * @param bool $bool Whether or not to alter the query arg filter.
     * @return bool      True or false based on query arg detection.
     */
    public function query_filter( $bool ) {

        // If "omhide" is set, the query filter exists.
        if ( isset( $_GET['omhide'] ) && $_GET['omhide'] ) {
            return true;
        }

        return $bool;

    }

    /**
     * Conditionally loads the OptinMonster optin based on the query filter detection.
     *
     * @since 1.0.0
     */
    public function maybe_load_optinmonster() {

        // If a URL suffix is set to not load optinmonster, don't do anything.
        $bool = apply_filters( 'optin_monster_query_filter', false ); // Deprecated.
        $bool = apply_filters( 'optin_monster_api_query_filter', false );
        if ( $bool ) {
	        // Default the global cookie to 30 days.
	        $global_cookie = 30;
	        $global_cookie = apply_filters( 'optin_monster_query_cookie', $global_cookie ); // Deprecated.
            $global_cookie = apply_filters( 'optin_monster_api_query_cookie', $global_cookie );
            if ( $global_cookie ) {
                setcookie( 'om-global-cookie', 1, time() + 3600 * 24 * (int) $global_cookie, COOKIEPATH, COOKIE_DOMAIN, false );
            }

            return;
        }

        // Add the hook to allow OptinMonster to process.
        add_action( 'pre_get_posts', array( $this, 'load_optinmonster_inline' ), 9999 );
        add_action( 'wp_footer', array( $this, 'load_optinmonster' ) );

    }

    /**
     * Loads an inline optin form (sidebar and after post) by checking against the current query.
     *
     * @since 1.0.0
     *
     * @param object $query The current main WP query object.
     */
    public function load_optinmonster_inline( $query ) {

        // If we are not on the main query, do nothing.
        if ( ! $query->is_main_query() ) {
            return;
        }

		$priority = apply_filters( 'optin_monster_post_priority', 999 ); // Deprecated.
        $priority = apply_filters( 'optin_monster_api_post_priority', 999 );
        add_filter( 'the_content', array( $this, 'load_optinmonster_inline_content' ), $priority );

    }

    /**
     * Filters the content to output an optin form.
     *
     * @since 1.0.0
     *
     * @param string $content  The current HTML string of main content.
     * @return string $content Amended content with possibly an optin.
     */
    public function load_optinmonster_inline_content( $content ) {

        global $post;

        // If the global $post is not set or the post status is not published, return early.
        if ( empty( $post ) || isset( $post->ID ) && 'publish' !== get_post_status( $post->ID ) ) {
           	return $content;
        }

        // Don't do anything for excerpts.
        // This prevents the optin accidentally being output when get_the_excerpt() or wp_trim_excerpt() is
        // called by a theme or plugin, and there is no excerpt, meaning they call the_content and break us.
		global $wp_current_filter;

        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }

        if ( in_array( 'wp_trim_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }

        // Prepare variables.
        $post_id = get_queried_object_id();
        if ( ! $post_id ) {
	        if ( 'page' == get_option( 'show_on_front' ) ) {
            	$post_id = get_option( 'page_for_posts' );
	        }
	    }
        $optins = $this->base->get_optins();
        $fields	= array();

        // If no optins are found, return early.
        if ( ! $optins ) {
        	return $content;
        }

        // Loop through each optin and optionally output it on the site.
        foreach ( $optins as $optin ) {
	        // Grab all the fields to check against.
	        foreach ( (array) $this->fields as $field ) {
		        $fields[ $field ] = get_post_meta( $optin->ID, '_omapi_' . $field, true );
	        }

	        // Ensure the optin is enabled. If not, pass over it.
	        if ( empty( $fields['enabled'] ) || ! $fields['enabled'] ) {
		        continue;
	        }

            // If in test mode but not logged in, skip over the optin.
            if ( isset( $fields['test'] ) && $fields['test'] && ! is_user_logged_in() ) {
                continue;
            }

            // If the type is a sidebar or after post optin, pass over it.
            if ( isset( $fields['type'] ) && 'post' !== $fields['type'] ) {
                continue;
            }

            // If the optin is to be shown only to logged in users but is not logged in, pass over it.
            if ( isset( $fields['users'] ) && 'in' == $fields['users'] && ! is_user_logged_in() ) {
                continue;
            }

            // If the optin is to be shown only to visitors but is logged in, pass over it.
            if ( isset( $fields['users'] ) && 'out' == $fields['users'] && is_user_logged_in() ) {
                continue;
            }

            // Check to see if we need to load the WP API helper script.
            if ( isset( $fields['mailpoet'] ) && $fields['mailpoet'] ) {
	            $this->wp_helper();
            }

            // Prepare the optin campaign.
            $html = trim( html_entity_decode( stripslashes( $optin->post_content ), ENT_QUOTES ), '\'' );

            // If the optin is only to be shown on specific post IDs, get the code and break.
            if ( ! empty( $fields['only'] ) ) {
                if ( $post_id && in_array( $post_id, (array) $fields['only'] ) ) {
                    $content .= $html;
                    $this->set_slug( $optin );
                    continue;
                }
            }

            // Exclude posts/pages from optin display.
            if ( ! empty( $fields['never'] ) ) {
                if ( $post_id && in_array( $post_id, (array) $fields['never'] ) ) {
                    continue;
                }
            }

            // If the optin is only to be shown on particular categories, get the code and break.
            if ( ! empty( $fields['categories'] ) && ( 'post' == get_post_type() ) ) {
                // If this is the home page, check to see if they have decided to load on certain archive pages.
                if ( is_home() ) {
                    // Run a check for archive-type pages.
                    if ( ! empty( $fields['show'] ) ) {
                        // If showing on index pages and we are on an index page, show the optin.
                        if ( in_array( 'index', (array) $fields['show'] ) ) {
                            if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                                $content .= $html;
                                $this->set_slug( $optin );
                                continue;
                            }
                        }

                        // Check if we should show on a selected post type.
                        if ( in_array( 'post', (array) $fields['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                            $content .= $html;
                            $this->set_slug( $optin );
                            continue;
                        }
                    }
                }

                $categories = wp_get_object_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
                foreach ( (array) $categories as $category_id ) {
                    if ( $category_id && in_array( $category_id, $fields['categories'] ) && ! is_archive() ) {
                        $content .= $html;
                        $this->set_slug( $optin );
                        continue 2;
                    }
                }
            }

            // If the optin is only to be shown on particular taxonomies, get the code and break.
		    if ( ! empty( $fields['taxonomies'] ) && ( is_singular() ) ) {
		        // If this is the home page, check to see if they have decided to load on certain archive pages.
		        if ( is_home() ) {
		            // Run a check for archive-type pages.
                    if ( ! empty( $fields['show'] ) ) {
                        // If showing on index pages and we are on an index page, show the optin.
                        if ( in_array( 'index', (array) $fields['show'] ) ) {
                            if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                                $content .= $html;
                                $this->set_slug( $optin );
                                continue;
                            }
                        }

                        // Check if we should show on a selected post type.
                        if ( in_array( 'post', (array) $fields['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                            $content .= $html;
                            $this->set_slug( $optin );
                            continue;
                        }
                    }
		        }

		        $taxonomies                = get_taxonomies( array( 'public' => true, '_builtin' => false ) );
		        $taxonomies['post_tag']    = 'post_tag';
		        $taxonomies['post_format'] = 'post_format';
		        $taxonomies 			   = wp_get_object_terms( $post_id, $taxonomies, array( 'fields' => 'ids' ) );
		        foreach ( $fields['taxonomies'] as $taxonomy => $taxonomy_id ) {
			        $tax_ids = explode(',', $taxonomy_id[0] );
			        foreach ( $tax_ids as $tax_id ) {
				        if ( $post_id && $tax_id && has_term( $tax_id, $taxonomy, $post_id ) ) {
					        $content .= $html;
					        $this->set_slug( $optin );
					        continue 2;
				        }
			        }
		        }
		    }

            // Run a check for specific post types.
            if ( ! empty( $fields['show'] ) ) {
                // Check if we should show on a selected post type.
                if ( in_array( get_post_type(), (array) $fields['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                    $content .= $html;
                    $this->set_slug( $optin );
                    continue;
                }
            }

            // If the optin is set to be automatically displayed, show it.
            if ( isset( $fields['automatic'] ) && $fields['automatic'] && is_singular( 'post' ) ) {
                $content .= $html;
                $this->set_slug( $optin );
                continue;
            }
        }

        // Return the content.
        return $content;

    }

    /**
     * Possibly loads an optin on a page.
     *
     * @since 1.0.0
     */
    public function load_optinmonster() {

        // Prepare variables.
        global $post;
        $post_id = get_queried_object_id();
        if ( ! $post_id ) {
	        if ( 'page' == get_option( 'show_on_front' ) ) {
            	$post_id = get_option( 'page_for_posts' );
	        }
	    }
        $optins = $this->base->get_optins();
        $init   = array();
        $fields = array();

        // If no optins are found, return early.
        if ( ! $optins ) {
            return;
        }

        // Loop through each optin and optionally output it on the site.
        foreach ( $optins as $optin ) {
	        // Grab all the fields to check against.
	        foreach ( (array) $this->fields as $field ) {
		        $fields[ $field ] = get_post_meta( $optin->ID, '_omapi_' . $field, true );
	        }

            // Ensure the optin is enabled. If not, pass over it.
	        if ( empty( $fields['enabled'] ) || ! $fields['enabled'] ) {
		        continue;
	        }

            // If in test mode but not logged in, skip over the optin.
            if ( isset( $fields['test'] ) && $fields['test'] && ! is_user_logged_in() ) {
                continue;
            }

            // If the type is a sidebar or after post optin, pass over it.
            if ( isset( $fields['type'] ) && ( 'post' == $fields['type'] || 'sidebar' == $fields['type'] ) ) {
                continue;
            }

            // If the optin is to be shown only to logged in users but is not logged in, pass over it.
            if ( isset( $fields['users'] ) && 'in' == $fields['users'] && ! is_user_logged_in() ) {
                continue;
            }

            // If the optin is to be shown only to visitors but is logged in, pass over it.
            if ( isset( $fields['users'] ) && 'out' == $fields['users'] && is_user_logged_in() ) {
                continue;
            }

            // Check to see if we need to load the WP API helper script.
            if ( isset( $fields['mailpoet'] ) && $fields['mailpoet'] ) {
	            $this->wp_helper();
            }

            // Prepare the optin campaign.
            $html = trim( html_entity_decode( stripslashes( $optin->post_content ), ENT_QUOTES ), '\'' );

            // If the optin is only to be shown on specific post IDs, get the code and break.
            if ( ! empty( $fields['only'] ) ) {
                if ( $post_id && in_array( $post_id, (array) $fields['only'] ) ) {
                    $init[ $optin->post_name ] = $html;
                    $this->set_slug( $optin );
                    continue;
                }
            }

            // Exclude posts/pages from optin display.
            if ( ! empty( $fields['never'] ) ) {
                if ( $post_id && in_array( $post_id, (array) $fields['never'] ) ) {
                    continue;
                }
            }

            // If the optin is only to be shown on particular categories, get the code and break.
            if ( ! empty( $fields['categories'] ) && ( 'post' == get_post_type() ) ) {
                // Don't try to load on the blog home page even if a category that is selected appears in the loop.
                if ( is_home() ) {
                    // Run a check for archive-type pages.
                    if ( ! empty( $fields['show'] ) ) {
                        // If showing on index pages and we are on an index page, show the optin.
                        if ( in_array( 'index', (array) $fields['show'] ) ) {
                            if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                                $init[ $optin->post_name ] = $html;
                                $this->set_slug( $optin );
                                continue;
                            }
                        }

                        // Check if we should show on the 'post' post type.
                        if ( in_array( 'post', (array) $fields['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                            $init[ $optin->post_name ] = $html;
                            $this->set_slug( $optin );
                            continue;
                        }
                    }
                }

                $categories = wp_get_object_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
                foreach ( (array) $categories as $category_id ) {
                    if ( $category_id && in_array( $category_id, $fields['categories'] ) && ! is_archive() ) {
                        $init[ $optin->post_name ] = $html;
                        $this->set_slug( $optin );
                        continue 2;
                    }
                }
            }

            // If the optin is only to be shown on particular taxonomies, get the code and break.
		    if ( ! empty( $fields['taxonomies'] ) && ( is_singular() ) ) {
		        // If this is the home page, check to see if they have decided to load on certain archive pages.
		        if ( is_home() ) {
                    // Run a check for archive-type pages.
                    if ( ! empty( $fields['show'] ) ) {
                        // If showing on index pages and we are on an index page, show the optin.
                        if ( in_array( 'index', (array) $fields['show'] ) ) {
                            if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                                $init[ $optin->post_name ] = $html;
                                $this->set_slug( $optin );
                                continue;
                            }
                        }

                        // Check if we should show on the 'post' post type.
                        if ( in_array( 'post', (array) $fields['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                            $init[ $optin->post_name ] = $html;
                            $this->set_slug( $optin );
                            continue;
                        }
                    }
                }

		        $taxonomies                = get_taxonomies( array( 'public' => true, '_builtin' => false ) );
		        $taxonomies['post_tag']    = 'post_tag';
		        $taxonomies['post_format'] = 'post_format';
		        $taxonomies 			   = wp_get_object_terms( $post_id, $taxonomies, array( 'fields' => 'ids' ) );
		        foreach ( $fields['taxonomies'] as $taxonomy => $taxonomy_id ) {
			        $tax_ids = explode(',', $taxonomy_id[0] );
			        foreach ( $tax_ids as $tax_id ) {
				        if ( $post_id && $tax_id && has_term( $tax_id, $taxonomy, $post_id ) ) {
					        $init[ $optin->post_name ] = $html;
					        $this->set_slug( $optin );
					        continue 2;
				        }
			        }
		        }
		    }

            // Run a check for archive-type pages.
            if ( ! empty( $fields['show'] ) ) {
                // If showing on index pages and we are on an index page, show the optin.
                if ( in_array( 'index', (array) $fields['show'] ) ) {
                    if ( is_front_page() || is_home() || is_archive() || is_search() ) {
                        $init[ $optin->post_name ] = $html;
                        $this->set_slug( $optin );
                        continue;
                    }
                }

                // Check if we should show on a selected post type.
                if ( in_array( get_post_type(), (array) $fields['show'] ) && ! ( is_front_page() || is_home() || is_archive() || is_search() ) ) {
                    $init[ $optin->post_name ] = $html;
                    $this->set_slug( $optin );
                    continue;
                }
            }

            // Finally, check the global scope to load an optin.
            if ( isset( $fields['global'] ) && $fields['global'] ) {
                $init[ $optin->post_name ] = $html;
                $this->set_slug( $optin );
                continue;
            }

            // Allow devs to filter the final output for more granular control over optin targeting.
	        // Devs should return the value for the slug key as false if the conditions are not met.
	        $init = apply_filters( 'optinmonster_output', $init ); // Deprecated.
	        $init = apply_filters( 'optin_monster_output', $init, $optin, $fields, $post_id ); // Deprecated.
	        $init = apply_filters( 'optin_monster_api_output', $init, $optin, $fields, $post_id );
        }

        // Run a final filter for all items.
        $init = apply_filters( 'optin_monster_api_final_output', $init, $post_id );

        // If the init code is empty, do nothing.
        if ( empty( $init ) ) {
            return;
        }

        // Load the optins.
        foreach ( (array) $init as $optin ) {
            if ( $optin ) {
                echo $optin;
            }
        }

	}

	/**
     * Sets the slug for possibly parsing shortcodes.
     *
     * @since 1.0.0
     *
     * @param object $optin The optin object.
     */
	public function set_slug( $optin ) {

		$shortcode = get_post_meta( $optin->ID, '_omapi_shortcode', true );
		if ( $shortcode ) {
			$this->slugs[] 		= str_replace( '-', '_', $optin->post_name );
			$this->shortcodes[] = get_post_meta( $optin->ID, '_omapi_shortcode_output', true );
		}

	}

	/**
     * Maybe outputs the JS variables to parse shortcodes.
     *
     * @since 1.0.0
     */
	public function maybe_parse_shortcodes() {

		// If no slugs have been set, do nothing.
		if ( empty( $this->slugs ) ) {
			return;
		}

		// Loop through any shortcodes and output them.
		foreach ( $this->shortcodes as $shortcode_string ) {
			if ( empty( $shortcode_string ) ) {
				continue;
			}

			$all_shortcode = explode( ',', $shortcode_string );
			foreach ( $all_shortcode as $shortcode ) {
				if ( empty( $shortcode ) ) {
					continue;
				}

				echo '<div style="position:absolute;overflow:hidden;clip:rect(0 0 0 0);height:1px;width:1px;margin:-1px;padding:0;border:0">';
					echo '<div class="omapi-shortcode-helper">' . html_entity_decode( $shortcode, ENT_COMPAT ) . '</div>';
					echo '<div class="omapi-shortcode-parsed">' . do_shortcode( html_entity_decode( $shortcode, ENT_COMPAT ) ) . '</div>';
				echo '</div>';
			}
		}

		// Output the JS variables to signify shortcode parsing is needed.
	    ?>
	    <script type="text/javascript"><?php foreach ( $this->slugs as $slug ) { echo 'var ' . $slug . '_shortcode = true;'; } ?></script>
	    <?php

	}

	/**
     * Possibly localizes a JS variable for output use.
     *
     * @since 1.0.0
     */
    public function localize() {

	    // If already localized, do nothing.
	    if ( $this->localized ) {
		    return;
	    }

	    // Set flag to true.
	    $this->localized = true;

	    // Output JS variable.
	    ?>
	    <script type="text/javascript">var omapi_localized = { ajax: '<?php echo esc_url_raw( add_query_arg( 'optin-monster-ajax-route', true, admin_url( 'admin-ajax.php' ) ) ); ?>', nonce: '<?php echo wp_create_nonce( 'omapi' ); ?>' };</script>
	    <?php

	}

	/**
     * Outputs the WP helper script for storing local optins.
     *
     * @since 1.0.0
     */
	public function wp_helper() {

		wp_enqueue_script(
            $this->base->plugin_slug . '-wp-helper',
            plugins_url( 'assets/js/helper.js', $this->base->file ),
            array( 'jquery'),
            $this->base->version,
            true
        );

	}

}