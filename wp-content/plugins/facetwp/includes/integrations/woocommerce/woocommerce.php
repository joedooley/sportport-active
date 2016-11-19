<?php

class FacetWP_Integration_WooCommerce
{

    public $variations;


    function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
        add_filter( 'facetwp_facet_sources', array( $this, 'facet_sources' ) );
        add_filter( 'facetwp_indexer_query_args', array( $this, 'index_variations' ) );
        add_filter( 'facetwp_indexer_post_facet', array( $this, 'index_woo_values' ), 10, 2 );
        add_filter( 'facetwp_index_row', array( $this, 'attribute_variations' ), 1 );
        add_filter( 'facetwp_wpdb_sql', array( $this, 'wpdb_sql' ), 10, 2 );
        add_filter( 'facetwp_wpdb_get_col', array( $this, 'wpdb_get_col' ), 10, 3 );
        add_filter( 'facetwp_filtered_post_ids', array( $this, 'process_variations' ) );
        add_filter( 'facetwp_facet_where', array( $this, 'facet_where' ), 10, 2 );
    }


    /**
     * Run WooCommerce handlers on facetwp-refresh
     * @since 2.0.9
     */
    function front_scripts() {
        FWP()->display->assets['query-string.js'] = FACETWP_URL . '/assets/js/src/query-string.js';
        FWP()->display->assets['woocommerce.js'] = FACETWP_URL . '/includes/integrations/woocommerce/woocommerce.js';
    }


    /**
     * Add WooCommerce-specific data sources
     * @since 2.1.4
     */
    function facet_sources( $sources ) {
        $sources['woocommerce'] = array(
            'label' => __( 'WooCommerce', 'fwp' ),
            'choices' => array(
                'woo/price'             => __( 'Price' ),
                'woo/sale_price'        => __( 'Sale Price' ),
                'woo/regular_price'     => __( 'Regular Price' ),
                'woo/average_rating'    => __( 'Average Rating' ),
                'woo/stock_status'      => __( 'Stock Status' ),
                'woo/on_sale'           => __( 'On Sale' ),
            )
        );
        return $sources;
    }


    /**
     * Index product variations
     * @since 2.7
     */
    function index_variations( $args ) {

        // Saving a single product
        if ( ! empty( $args['p'] ) ) {
            if ( 'product' == get_post_type( $args['p'] ) ) {
                $product = wc_get_product( $args['p'] );
                if ( 'variable' == $product->product_type ) {
                    $children = $product->get_children();
                    $args['post_type'] = array( 'product', 'product_variation' );
                    $args['post__in'] = $children;
                    $args['post__in'][] = $args['p'];
                    $args['posts_per_page'] = -1;
                    unset( $args['p'] );
                }
            }
        }
        // Force product variations to piggyback products
        else {
            $pt = (array) $args['post_type'];

            if ( in_array( 'any', $pt ) ) {
                $pt = get_post_types();
            }
            if ( in_array( 'product', $pt ) ) {
                $pt[] = 'product_variation';
            }

            $args['post_type'] = $pt;
        }

        return $args;
    }


    /**
     * When indexing product variations, attribute its parent product
     * @since 2.7
     */
    function attribute_variations( $params ) {
        $post_id = (int) $params['post_id'];

        if ( 'product_variation' == get_post_type( $post_id ) ) {
            $params['post_id'] = wp_get_post_parent_id( $post_id );
            $params['variation_id'] = $post_id;
        }

        return $params;
    }


    /**
     * Hijack filter_posts() to grab variation IDs
     * @since 2.7
     */
    function wpdb_sql( $sql, $facet ) {
        $sql = str_replace(
            'DISTINCT post_id',
            'DISTINCT post_id, GROUP_CONCAT(variation_id) AS variation_ids',
            $sql
        );

        $sql .= ' GROUP BY post_id';

        return $sql;
    }


    /**
     * Store a facet's variation IDs
     * @since 2.7
     */
    function wpdb_get_col( $result, $sql, $facet ) {
        global $wpdb;

        $facet_name = $facet['name'];
        $variations = $wpdb->get_col( $sql, 1 ); // variation IDs as arrays of comma-separated strings
        $variations = implode( ',', $variations ); // combine the arrays into a single string
        $variations = explode( ',', $variations ); // convert to an array of variation IDs

        if ( isset( $this->variations[ $facet_name ] ) ) {
            $temp = $this->variations[ $facet_name ];
            $this->variations[ $facet_name ] = array_intersect( $temp, $variations );
        }
        else {
            $this->variations[ $facet_name ] = $variations;
        }

        return $result;
    }


    /**
     * Determine valid variation IDs
     * @since 2.7
     */
    function process_variations( $post_ids ) {
        if ( isset( $this->variations ) ) {
            $batch = array();
            $key = 0;
            foreach ( $this->variations as $facet_name => $id_array ) {
                $batch = ( $key > 0 ) ? array_intersect( $batch, $id_array ) : $id_array;
                $key++;
            }
            $this->variation_ids = implode( ',', array_unique( $batch ) );
        }

        return $post_ids;
    }


    /**
     * Apply variation IDs to load_values() method
     * @since 2.7
     */
    function facet_where( $where_clause, $facet ) {
        if ( ! empty( $this->variation_ids ) ) { // add "0" to support non-variable products
            $where_clause .= ' AND variation_id IN (0,' . $this->variation_ids . ')';
        }

        return $where_clause;
    }


    /**
     * Index WooCommerce-specific values
     * @since 2.1.4
     */
    function index_woo_values( $return, $params ) {
        $facet = $params['facet'];
        $defaults = $params['defaults'];
        $post_id = (int) $defaults['post_id'];

        if ( 'product' != get_post_type( $post_id ) ) {
            return $return;
        }

        if ( isset( $facet['source'] ) && 0 === strpos( $facet['source'], 'woo' ) ) {
            $product = wc_get_product( $post_id );

            // Price
            if ( 'woo/price' == $facet['source'] ) {
                $price = $product->get_price();
                $defaults['facet_value'] = $price;
                $defaults['facet_display_value'] = $price;
                FWP()->indexer->index_row( $defaults );
            }

            // Sale Price
            elseif ( 'woo/sale_price' == $facet['source'] ) {
                $price = $product->get_sale_price();
                $defaults['facet_value'] = $price;
                $defaults['facet_display_value'] = $price;
                FWP()->indexer->index_row( $defaults );
            }

            // Regular Price
            elseif ( 'woo/regular_price' == $facet['source'] ) {
                $price = $product->get_regular_price();
                $defaults['facet_value'] = $price;
                $defaults['facet_display_value'] = $price;
                FWP()->indexer->index_row( $defaults );
            }

            // Average Rating
            elseif ( 'woo/average_rating' == $facet['source'] ) {
                $rating = $product->get_average_rating();
                $defaults['facet_value'] = $rating;
                $defaults['facet_display_value'] = $rating;
                FWP()->indexer->index_row( $defaults );
            }

            // Stock Status
            elseif ( 'woo/stock_status' == $facet['source'] ) {
                $in_stock = $product->is_in_stock();
                $defaults['facet_value'] = (int) $in_stock;
                $defaults['facet_display_value'] = $in_stock ? __( 'In Stock', 'fwp' ) : __( 'Out of Stock', 'fwp' );
                FWP()->indexer->index_row( $defaults );
            }

            // On Sale
            elseif ( 'woo/on_sale' == $facet['source'] ) {
                if ( $product->is_on_sale() ) {
                    $defaults['facet_value'] = 1;
                    $defaults['facet_display_value'] = __( 'On Sale', 'fwp' );
                    FWP()->indexer->index_row( $defaults );
                }
            }

            return true;
        }

        return $return;
    }
}


if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    new FacetWP_Integration_WooCommerce();
}
