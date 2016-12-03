<?php

class FacetWP_Integration_WooCommerce
{

    function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
        add_filter( 'facetwp_facet_sources', array( $this, 'facet_sources' ) );
        add_filter( 'facetwp_indexer_post_facet', array( $this, 'index_woo_values' ), 10, 2 );
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
