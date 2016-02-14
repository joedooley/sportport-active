<?php
/**
 * Imagga class.
 *
 * Interacts with the Imagga API:
 * http://docs.imagga.com/
 *
 * @since 1.3.1
 *
 * @package Envira_Tags
 * @author  Tim Carr
 */
class Envira_Tags_Imagga {

    /**
     * The Imagga API endpoint
     *
     * @since 1.3.1
     *
     * @var string
     */
    private $endpoint = 'http://api.imagga.com/v1/';

    /**
     * The Imagga API Authorization Code
     *
     * @since 1.3.1
     *
     * @var string
     */
    private $authorization_code = '';

    /**
     * Primary class constructor.
     *
     * @since 1.3.1
     *
     * @param string $code Imagga Authorization Code
     */
    public function __construct( $code ) {

        // Define the authorization code for later use
        $this->authorization_code = $code;

    }

    /**
     * Check if an authorization code was specified
     *
     * @since 1.3.1
     *
     * @return bool
     */
    private function check_authorization_code() {

        if ( empty( $this->authorization_code ) ) {
            return false;
        }

        return true;

    }

    /**
     * Defines the headers to send with each GET and POST request to the Imagga API
     *
     * @since 1.3.1
     *
     * @return  array   Headers
     */
    private function get_headers() {

        // Define required headers for requests
        $headers = array(
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . $this->authorization_code,
        );

        // Return
        return $headers;

    }

    /**
     * Performs a wp_remote_get() call on the endpoint function, with the supplied optional arguments
     *
     * @since 1.3.1
     *
     * @param   string  $function   Function (e.g. 'tagging')
     * @param   array   $args       Arguments (optional)
     * @return  mixed               false | WP_Error | Tags Array
     */
    private function get( $function, $args = false ) {

        // Check auth code
        if ( ! $this->check_authorization_code() ) {
            return false;
        }

        // Build the GET URL
        $url = $this->endpoint . $function;

        // If arguments are specified, append them to the URL
        if ( is_array( $args ) && count( $args ) > 0 ) {
            $url .= '?' . http_build_query( $args );
        }

        // Append version=2
        $url .= '&version=2';

        // Get Headers
        $headers = $this->get_headers();

        // Send the GET request
        $response = wp_remote_get( $url, array(
            'headers' => $headers,
        ) );

        // Check response
        if ( isset( $response['response'] ) ) {
            if ( $response['response']['code'] != 200 ) {
                return new WP_Error( 'envira_tags_imagga_get', $response['response']['code'] . ' ' . $response['response']['message'] );
            }
        }

        // Check body
        if ( ! isset( $response['body'] ) ) {
            return new WP_Error( 'envira_tags_imagga_get', __( 'No response from Imagga', 'envira-tags' ) );
        }

        // Attempt to JSON decode
        $results = json_decode( $response['body'] );
        if ( ! is_object( $results ) || ! isset( $results->results ) ) {
            return new WP_Error( 'envira_tags_imagga_get', __( 'Invalid JSON response from Imagga', 'envira-tags' ) );
        }

        // Check if the results contain an error
        if ( isset( $results->unsuccessful ) ) {
            return new WP_Error( 'envira_tags_imagga_get', $results->unsuccessful[0]->image . ': ' . $results->unsuccessful[0]->message );
        }
        
        // Return results
        return $results->results[0];

    }

    /**
     * Calls the /tagging function, which returns tags for the supplied image
     *
     * @since 1.3.1
     *
     * @param   string  $image_url  Image URL
     * @param   string  $language   Language
     * @return  mixed               false | WP_Error | Tags array
     */
    public function get_image_tags( $image_url, $language = 'en' ) {

        // Get results
        $results = $this->get( 'tagging', array(
            'url'       => $image_url,
            'language'  => $language,
        ) );

        // If a WP_Error or no results found, return
        if ( is_wp_error( $results ) || ! $results ) {
            return $results;
        }

        // Return the tags part of the resultset
        return $results->tags;

    }

}