<?php
/**
 * Amazon Payments Advanced API class
 */
class WC_Amazon_Payments_Advanced_API {

	/**
	 * Login App setup - Client ID Retrieval Instruction URLs
	 *
	 * @var array
	 */
	protected static $client_id_instructions = array(
		'US' => 'https://payments.amazon.com/documentation/express/201728550',
		'GB' => 'https://amazonpayments.s3.amazonaws.com/documents/Get_Your_Login_with_Amazon_Client_ID_EU_ENG.pdf?ld=APUSLPADefault',
		'DE' => 'https://amazonpayments.s3.amazonaws.com/documents/Get_Your_Login_with_Amazon_Client_ID_EU_DE.pdf?ld=APUSLPADefault',
		//'JP' => ''
	);

	/**
	 * API Endpoints
	 *
	 * @var array
	 */
	protected static $endpoints = array(
		'sandbox' => array(
			'US' => 'https://mws.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01/',
			'GB' => 'https://mws-eu.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01/',
			'DE' => 'https://mws-eu.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01/',
			'JP' => 'https://mws.amazonservices.jp/OffAmazonPayments_Sandbox/2013-01-01/'
		),
		'production' => array(
			'US' => 'https://mws.amazonservices.com/OffAmazonPayments/2013-01-01/',
			'GB' => 'https://mws-eu.amazonservices.com/OffAmazonPayments/2013-01-01/',
			'DE' => 'https://mws-eu.amazonservices.com/OffAmazonPayments/2013-01-01/',
			'JP' => 'https://mws.amazonservices.jp/OffAmazonPayments/2013-01-01/'
		)
	);

	/**
	 * Register URLs
	 *
	 * @var array
	 */
	protected static $register_urls = array(
		'US' => 'https://sellercentral.amazon.com/hz/me/sp/signup?solutionProviderOptions=mws-acc%3B&marketplaceId=AGWSWK15IEJJ7&solutionProviderToken=AAAAAQAAAAEAAAAQ1XU19m0BwtKDkfLZx%2B03RwAAAHBZVsoAgz2yhE7DemKr0y26Mce%2F9Q64kptY6CRih871XhB7neN0zoPX6c1wsW3QThdY6g1Re7CwxJkhvczwVfvZ9BvjG1V%2F%2FHrRgbIf47cTrdo5nNT8jmYSIEJvFbSm85nWxpvHjSC4CMsVL9s%2FPsZt&solutionProviderId=A1BVJDFFHQ7US4',
		'GB' => 'https://sellercentral-europe.amazon.com/gp/on-board/workflow/Registration/login.html?passthrough%2Fsource=internal-landing-select&passthrough%2F*entries*=0&passthrough%2FmarketplaceID=A2WQPBGJ59HSXT&passthrough%2FsuperSource=OAR&passthrough%2F*Version*=1&passthrough%2Fld=APRPWOOCOMMERCE&passthrough%2Faccount=cba&passthrough%2FwaiveFee=1',
		'DE' => 'https://sellercentral-europe.amazon.com/gp/on-board/workflow/Registration/login.html?passthrough%2Fsource=internal-landing-select&passthrough%2F*entries*=0&passthrough%2FmarketplaceID=A1OCY9REWJOCW5&passthrough%2FsuperSource=OAR&passthrough%2F*Version*=1&passthrough%2Fld=APRPWOOCOMMERCE&passthrough%2Faccount=cba&passthrough%2FwaiveFee=1',
		'JP' => 'https://payments.amazon.co.jp/contactsales'
	);

	/**
	 * Widgets URLs
	 *
	 * @var array
	 */
	protected static $widgets_urls = array(
		'US' => 'https://static-na.payments-amazon.com/OffAmazonPayments/us/%sjs/Widgets.js',
		'GB' => 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/%sjs/Widgets.js',
		'DE' => 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/%sjs/Widgets.js',
		'JP' => 'https://static-fe.payments-amazon.com/OffAmazonPayments/jp/%sjs/Widgets.js'
	);

	/**
	 * Get settings
	 *
	 * @return array
	 */
	public static function get_settings() {
		$settings = get_option( 'woocommerce_amazon_payments_advanced_settings', array() );
		$default  = array(
			'enabled'                       => 'no',
			'title'                         => __( 'Amazon', 'woocommerce-gateway-amazon-payments-advanced' ),
			'seller_id'                     => '',
			'mws_access_key'                => '',
			'secret_key'                    => '',
			'enable_login_app'              => 'no',
			'app_client_id'                 => '',
			'app_client_secret'             => '',
			'sandbox'                       => 'yes',
			'payment_capture'               => 'no',
			'cart_button_display_mode'      => 'button',
			'button_type'                   => 'LwA',
			'button_size'                   => 'small',
			'button_color'                  => 'Gold',
			'button_language'               => '',
			'hide_standard_checkout_button' => 'no',
			'debug'                         => 'no',
			'hide_button_mode'              => 'no',
		);

		return apply_filters( 'woocommerce_amazon_pa_settings', array_merge( $default, $settings ) );
	}

	/**
	 * Get reference ID.
	 *
	 * @return string
	 */
	public static function get_reference_id() {
		$reference_id = ! empty( $_REQUEST['amazon_reference_id'] ) ? $_REQUEST['amazon_reference_id'] : '';

		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $post_data );

			if ( isset( $post_data['amazon_reference_id'] ) ) {
				$reference_id = $post_data['amazon_reference_id'];
			}
		}

		return self::check_session( 'amazon_reference_id', $reference_id );
	}

	/**
	 * Get Access token.
	 *
	 * @return string
	 */
	public static function get_access_token() {
		$access_token = ! empty( $_REQUEST['access_token'] ) ? $_REQUEST['access_token'] : '';

		return self::check_session( 'access_token', $access_token );
	}

	/**
	 * Check WC session for reference ID or access token.
	 *
	 * @since 1.6.0
	 *
	 * @param string $key   Key from query string in URL
	 * @param string $value Value from query string in URL
	 *
	 * @return string
	 */
	public static function check_session( $key, $value ) {
		if ( ! in_array( $key, array( 'amazon_reference_id', 'access_token' ) ) ) {
			return $value;
		}

		// Since others might call the get_reference_id or get_access_token
		// too early, WC instance may not exists.
		if ( ! function_exists( 'WC' ) ) {
			return $value;
		}
		if ( ! is_a( WC()->session, 'WC_Session' ) ) {
			return $value;
		}

		if ( false === strstr( $key, 'amazon_' ) ) {
			$key = 'amazon_' . $key;
		}

		// Set and unset reference ID or access token to/from WC session.
		if ( ! empty( $value ) ) {
			// Set access token or reference ID in session after redirected
			// from Amazon Payments window.
			if ( ! empty( $_GET['amazon_payments_advanced'] ) ) {
				WC()->session->{ $key } = $value;
			}
		} else {
			// Don't get anything in URL, check session.
			if ( ! empty( WC()->session->{ $key } ) ) {
				$value = WC()->session->{ $key };
			}
		}

		return $value;
	}

	/**
	 * Get location
	 *
	 * @return string
	 */
	public static function get_location() {
		return in_array( WC()->countries->get_base_country(), array( 'US', 'GB', 'DE', 'JP' ) ) ? WC()->countries->get_base_country() : 'US';
	}

	/**
	 * Get Amazon Register URL
	 *
	 * @return string
	 */
	public static function get_register_url() {
		$location = self::get_location();

		return array_key_exists( $location, self::$register_urls ) ? self::$register_urls[ $location ] : '';
	}

	/**
	 * Get Amazon Login App setup URL
	 *
	 * @return string
	 */
	public static function get_client_id_instructions_url() {
		$location = self::get_location();

		return array_key_exists( $location, self::$client_id_instructions ) ? self::$client_id_instructions[ $location ] : '';
	}

	/**
	 * Get widget URL
	 *
	 * @return string
	 */
	public static function get_widgets_url() {

		$location = self::get_location();
		$settings = self::get_settings();
		$path     = '';

		if ( 'yes' == $settings['sandbox'] ) {

			$path .= 'sandbox/';

		}

		// The UK and DE widgets URLs need "lpa" in the path when in "login app" mode
		if ( ( 'yes' == $settings['enable_login_app'] ) && in_array( $location, array( 'GB', 'DE', 'JP' ) ) ) {

			$path .= 'lpa/';

		}

		$url = sprintf( self::$widgets_urls[ $location ], $path );

		if ( 'yes' == $settings['enable_login_app'] ) {

			return $url;

		}

		return $url . '?sellerId=' . $settings['seller_id'];
	}

	/**
	 * Get API endpoint
	 *
	 * @return string
	 */
	protected static function get_endpoint( $is_sandbox = false ) {
		$location = self::get_location();

		return $is_sandbox ? self::$endpoints['sandbox'][ $location ] : self::$endpoints['production'][ $location ];
	}

	/**
	 * Safe load XML.
	 *
	 * @param  string $source
	 * @param  int    $options
	 *
	 * @return SimpleXMLElement|bool
	 */
	public static function safe_load_xml( $source, $options = 0 ) {
		$old = null;

		if ( '<' !== substr( $source, 0, 1 ) ) {
			return false;
		}

		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			$old = libxml_disable_entity_loader( true );
		}

		$dom    = new DOMDocument();
		$return = $dom->loadXML( $source, $options );

		if ( ! is_null( $old ) ) {
			libxml_disable_entity_loader( $old );
		}

		if ( ! $return ) {
			return false;
		}

		if ( isset( $dom->doctype ) ) {
			return false;
		}

		return simplexml_import_dom( $dom );
	}

	/**
	 * Make an api request
	 *
	 * @param  args $args
	 * @return wp_error or parsed response array
	 */
	public static function request( $args ) {
		$settings = self::get_settings();
		$defaults = array(
			'AWSAccessKeyId' => $settings['mws_access_key'],
			'SellerId'       => $settings['seller_id']
		);

		$args     = apply_filters( 'woocommerce_amazon_pa_api_request_args', wp_parse_args( $args, $defaults ) );
		$endpoint = self::get_endpoint( 'yes' === $settings['sandbox'] );

		$url = self::get_signed_amazon_url( $endpoint . '?' . http_build_query( $args, '', '&' ), $settings['secret_key'] );
		wc_apa()->log( __METHOD__, sprintf( 'GET: %s', wc_apa()->sanitize_remote_request_log( $url ) ) );

		$response = wp_remote_get( $url, array(
			'timeout' => 12
		) );

		if ( ! is_wp_error( $response ) ) {
			$response        = self::safe_load_xml( $response['body'], LIBXML_NOCDATA );
			$logged_response = wc_apa()->sanitize_remote_response_log( $response );

			wc_apa()->log( __METHOD__, sprintf( 'Response: %s', $logged_response ) );
		} else {
			wc_apa()->log( __METHOD__, sprintf( 'Error: %s', $response->get_error_message() ) );
		}

		return $response;
	}

	/**
	 * Sign a url for amazon
	 *
	 * @param  string $url
	 *
	 * @return string
	 */
	protected static function get_signed_amazon_url( $url, $secret_key ) {
		$urlparts = parse_url( $url );

		// Build $params with each name/value pair
		foreach ( explode( '&', $urlparts['query'] ) as $part ) {
			if ( strpos( $part, '=' ) ) {
				list( $name, $value ) = explode( '=', $part, 2 );
			} else {
				$name  = $part;
				$value = '';
			}
			$params[ $name ] = $value;
		}

		// Include a timestamp if none was provided
		if ( empty( $params['Timestamp'] ) ) {
			$params['Timestamp'] = gmdate( 'Y-m-d\TH:i:s\Z' );
		}

		$params['SignatureVersion'] = '2';
		$params['SignatureMethod']  = 'HmacSHA256';

		// Sort the array by key
		ksort( $params );

		// Build the canonical query string
		$canonical = '';

		// Don't encode here - http_build_query already did it.
		foreach ( $params as $key => $val ) {
			$canonical  .= $key . "=" . rawurlencode( utf8_decode( urldecode( $val ) ) ) . '&';
		}

		// Remove the trailing ampersand
		$canonical = preg_replace( '/&$/', '', $canonical );

		// Some common replacements and ones that Amazon specifically mentions
		$canonical = str_replace( array( ' ', '+', ',', ';' ), array( '%20', '%20', urlencode( ',' ), urlencode( ':' ) ), $canonical );

		// Build the sign
		$string_to_sign = "GET\n{$urlparts['host']}\n{$urlparts['path']}\n$canonical";

		// Calculate our actual signature and base64 encode it
		$signature = base64_encode( hash_hmac( 'sha256', $string_to_sign, $secret_key, true ) );

		// Finally re-build the URL with the proper string and include the Signature
		$url = "{$urlparts['scheme']}://{$urlparts['host']}{$urlparts['path']}?$canonical&Signature=" . rawurlencode( $signature );

		return $url;
	}

	/**
	 * VAT registered sellers - Obtaining the Billing Address
	 * http://docs.developer.amazonservices.com/en_UK/apa_guide/APAGuide_GetAuthorizationStatus.html
	 *
	 * @param int $order_id
	 * @param array $result
	 *
	 * @deprecated
	 */
	public static function maybe_update_billing_details( $order_id, $result ) {
		_deprecated_function( 'WC_Amazon_Payments_Advanced_API::maybe_update_billing_details', '1.6.0', 'WC_Amazon_Payments_Advanced_API::update_order_billing_address' );

		if ( ! empty( $result->AuthorizationBillingAddress ) ) {
			$address = (array) $result->AuthorizationBillingAddress;

			self::update_order_billing_address( $order_id, $address );
		}
	}

	/**
	 * Get auth state from amazon API
	 *
	 * @param  string $id
	 *
	 * @return string or false on failure
	 */
	public static function get_reference_state( $order_id, $id ) {
		if ( $state = get_post_meta( $order_id, 'amazon_reference_state', true ) ) {
			return $state;
		}

		$response = self::request( array(
			'Action'                 => 'GetOrderReferenceDetails',
			'AmazonOrderReferenceId' => $id,
		) );

		if ( is_wp_error( $response ) || isset( $response->Error->Message ) ) {
			return false;
		}

		$state = (string) $response->GetOrderReferenceDetailsResult->OrderReferenceDetails->OrderReferenceStatus->State;

		update_post_meta( $order_id, 'amazon_reference_state', $state );

		return $state;
	}

	/**
	 * Get auth state from amazon API
	 *
	 * @param  string $id
	 *
	 * @return string or false on failure
	 */
	public static function get_authorization_state( $order_id, $id ) {
		if ( $state = get_post_meta( $order_id, 'amazon_authorization_state', true ) ) {
			return $state;
		}

		$response = self::request( array(
			'Action'                => 'GetAuthorizationDetails',
			'AmazonAuthorizationId' => $id,
		) );

		if ( is_wp_error( $response ) || isset( $response->Error->Message ) ) {
			return false;
		}

		$state = (string) $response->GetAuthorizationDetailsResult->AuthorizationDetails->AuthorizationStatus->State;

		update_post_meta( $order_id, 'amazon_authorization_state', $state );

		self::update_order_billing_address( $order_id, self::get_billing_address_from_response( $response ) );

		return $state;
	}

	/**
	 * Get capture state from amazon API
	 *
	 * @param  string $id
	 *
	 * @return string or false on failure
	 */
	public static function get_capture_state( $order_id, $id ) {
		if ( $state = get_post_meta( $order_id, 'amazon_capture_state', true ) ) {
			return $state;
		}

		$response = self::request( array(
			'Action'          => 'GetCaptureDetails',
			'AmazonCaptureId' => $id,
		) );

		if ( is_wp_error( $response ) || isset( $response->Error->Message ) ) {
			return false;
		}

		$state = (string) $response->GetCaptureDetailsResult->CaptureDetails->CaptureStatus->State;

		update_post_meta( $order_id, 'amazon_capture_state', $state );

		return $state;
	}

	/**
	 * Authorize payment against an order reference using 'Authorize' method.
	 *
	 * @see https://payments.amazon.com/documentation/apireference/201752010
	 *
	 * @since 1.6.0
	 *
	 * @param int|WC_Order $order Order
	 * @param array        $args  Arguments
	 *
	 * @return bool|WP_Error
	 */
	public static function authorize( $order, $args = array() ) {
		$order = wc_get_order( $order );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', __( 'Invalid order ID', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		if ( 'amazon_payments_advanced' !== $order->payment_method ) {
			return new WP_Error( 'invalid_order', __( 'Order is not paid via Amazon Payments', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		$args = wp_parse_args(
			$args,
			array(
				'amazon_reference_id' => get_post_meta( $order->id, 'amazon_reference_id', true ),
				'capture_now' => false,
			)
		);

		if ( ! $args['amazon_reference_id'] ) {
			return new WP_Error( 'order_missing_reference_id', __( 'Order missing Amazon order reference ID', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		$response = self::request( self::get_authorize_request_args( $order, $args ) );

		if ( isset( $response->Error->Message ) ) {
			$code = isset( $response->Error->Code ) ? (string) $response->Error->Code : 'amazon_error_response';
			return new WP_Error( $code, (string) $response->Error->Message );
		}

		return $response;
	}

	/**
	 * Get args to perform Authorize request.
	 *
	 * @since 1.6.0
	 *
	 * @param WC_Order $order Order object
	 * @param array    $args  Base args
	 */
	public static function get_authorize_request_args( WC_Order $order, $args ) {
		return array(
			'Action'                              => 'Authorize',
			'AmazonOrderReferenceId'              => $args['amazon_reference_id'],
			'AuthorizationReferenceId'            => $order->id . '-' . current_time( 'timestamp', true ),
			'AuthorizationAmount.Amount'          => $order->get_total(),
			'AuthorizationAmount.CurrencyCode'    => strtoupper( get_woocommerce_currency() ),
			'CaptureNow'                          => $args['capture_now'],
			'TransactionTimeout'                  => 0,
			'SellerOrderAttributes.SellerOrderId' => $order->get_order_number(),
			'SellerOrderAttributes.StoreName'     => WC_Amazon_Payments_Advanced::get_site_name()
			// 'SellerAuthorizationNote'          => '{"SandboxSimulation": {"State":"Declined", "ReasonCode":"AmazonRejected"}}'
		);
	}

	/**
	 * Authorize recurring payment against an order reference using
	 * 'AuthorizeOnBillingAgreement' method.
	 *
	 * @see https://payments.amazon.com/documentation/apireference/201752010
	 *
	 * @since 1.6.0
	 *
	 * @param int|WC_Order $order Order
	 * @param array $args Whether to immediately capture or not
	 *
	 * @return bool|WP_Error
	 */
	public function authorize_recurring( $order, $args = array() ) {
		$order = wc_get_order( $order );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', __( 'Invalid order ID', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		if ( 'amazon_payments_advanced' !== $order->payment_method ) {
			return new WP_Error( 'invalid_order', __( 'Order is not paid via Amazon Payments', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		$args = wp_parse_args(
			$args,
			array(
				'amazon_reference_id' => get_post_meta( $order->id, 'amazon_billing_agreement_id', true ),
				'capture_now'         => false,
			)
		);

		if ( ! $args['amazon_billing_agreement_id'] ) {
			return new WP_Error( 'order_missing_billing_agreement_id', __( 'Order missing Amazon billing agreement ID', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		$response = self::request( self::get_authorize_recurring_request_args( $order, $args ) );

		if ( isset( $response->Error->Message ) ) {
			$code = isset( $response->Error->Code ) ? (string) $response->Error->Code : 'amazon_error_response';
			return new WP_Error( $code, (string) $response->Error->Message );
		}

		return $response;
	}

	/**
	 * Get args to perform AuthorizeBillingAgreement request.
	 *
	 * @since 1.6.0
	 */
	public static function get_authorize_recurring_request_args( WC_Order $order, $args ) {
		return array(
			'Action'                              => 'AuthorizeOnBillingAgreement',
			'AmazonBillingAgreementId'            => $args['amazon_billing_agreement_id'],
			'AuthorizationReferenceId'            => $order->id . '-' . current_time( 'timestamp', true ),
			'AuthorizationAmount.Amount'          => $order->get_total(),
			'AuthorizationAmount.CurrencyCode'    => strtoupper( get_woocommerce_currency() ),
			'CaptureNow'                          => $capture_now,
			'TransactionTimeout'                  => 0,
			'SellerOrderAttributes.SellerOrderId' => $order->get_order_number(),
			'SellerOrderAttributes.StoreName'     => WC_Amazon_Payments_Advanced::get_site_name()
		);
	}
	/**
	 * Authorize payment against an order reference using 'Authorize' method
	 * See: https://payments.amazon.com/documentation/apireference/201752010
	 *
	 * @param int $order_id Order ID 
	 * @param string $amazon_reference_id Amazon reference ID
	 * @param bool $capture_now Whether to immediately capture or not
	 *
	 * @return bool
	 */
	public static function authorize_payment( $order_id, $amazon_reference_id, $capture_now = false ) {
		$response = self::authorize( $order_id, array(
			'amazon_reference_id' => $amazon_reference_id,
			'capture_now'         => $capture_now,
		) );

		return self::handle_payment_authorization_response( $response, $order_id, $capture_now );
	}

	/**
	 * Authorize payment against a billing agreement using 'AuthorizeOnBillingAgreement' method
	 * See: https://payments.amazon.com/documentation/automatic/201752090#201757380
	 *
	 * @param int        $order_id
	 * @param string     $amazon_billing_agreement_id
	 * @param bool|false $capture_now
	 *
	 * @return bool
	 */
	public static function authorize_recurring_payment( $order_id, $amazon_billing_agreement_id, $capture_now = false ) {
		$response = self::authorize_recurring( $order_id, array(
			'amazon_billing_agreement_id' => $amazon_billing_agreement_id,
		) );

		return self::handle_payment_authorization_response( $response, $order_id, $capture_now );
	}

	/**
	 * Handle the result of an authorization request
	 *
	 * @param object $response return from self::request()
	 * @param int|WC_Order $order
	 * @param bool $capture_now
	 * @param string $auth_method Deprecated. Which API authorization method was used. (Authorize, or AuthorizeOnBillingAgreement)
	 *
	 * @return bool whether or not payment was authorized
	 */
	public static function handle_payment_authorization_response( $response, $order, $capture_now, $auth_method = null ) {
		$order = wc_get_order( $order );

		if ( $auth_method !== null ) {
			_deprecated_function( 'WC_Amazon_Payments_Advanced_API::handle_payment_authorization_response', '1.6.0', 'Parameter auth_method is not used anymore' );
		}

		if ( is_wp_error( $response ) ) {
			$order->add_order_note( sprintf( __( 'Error: Unable to authorize funds with Amazon. Reason: %s', 'woocommerce-gateway-amazon-payments-advanced' ), $response->get_error_message() ) );

			return false;
		}

		return self::update_order_from_authorize_response( $order, $response, $capture_now );
	}

	/**
	 * Get Authorization ID from reesponse.
	 *
	 * @since 1.6.9
	 *
	 * @param object $response return from self::request()
	 *
	 * @return string|bool String of Authorization ID. Otherwise false is returned.
	 */
	public static function get_auth_id_from_response( $response ) {
		$auth_id = false;
		if ( isset( $response->AuthorizeOnBillingAgreementResult->AuthorizationDetails->AmazonAuthorizationId ) ) {
			$auth_id = (string) $response->AuthorizeOnBillingAgreementResult->AuthorizationDetails->AmazonAuthorizationId;
		} elseif ( isset( $response->AuthorizeResult->AuthorizationDetails->AmazonAuthorizationId ) ) {
			$auth_id = (string) $response->AuthorizeResult->AuthorizationDetails->AmazonAuthorizationId;
		}
		return $auth_id;
	}

	/**
	 * Get Authorization state from reesponse.
	 *
	 * @since 1.6.9
	 *
	 * @param object $response Response from self::request()
	 *
	 * @return string|bool String of Authorization state
	 */
	public static function get_auth_state_from_reponse( $response ) {
		$state = 'pending';
		if ( isset( $response->AuthorizeOnBillingAgreementResult->AuthorizationDetails->AuthorizationStatus->State ) ) {
			$state = strtolower( (string) $response->AuthorizeOnBillingAgreementResult->AuthorizationDetails->AuthorizationStatus->State );
		} elseif ( isset( $response->AuthorizeResult->AuthorizationDetails->AuthorizationStatus->State ) ) {
			$state = strtolower( (string) $response->AuthorizeResult->AuthorizationDetails->AuthorizationStatus->State );
		}
		return $state;
	}

	/**
	 * Get Authorization state reason code from reesponse.
	 *
	 * @see   https://payments.amazon.com/documentation/apireference/201752950
	 * @since 1.6.9
	 *
	 * @param object $response Response from self::request()
	 *
	 * @return string|bool String of Authorization state
	 */
	public static function get_auth_state_reason_code_from_response( $response ) {
		$reason_code = 'Unknown';
		if ( isset( $response->AuthorizeOnBillingAgreementResult->AuthorizationDetails->AuthorizationStatus->ReasonCode ) ) {
			$reason_code = (string) $response->AuthorizeOnBillingAgreementResult->AuthorizationDetails->AuthorizationStatus->ReasonCode;
		} elseif ( isset( $response->AuthorizeResult->AuthorizationDetails->AuthorizationStatus->ReasonCode ) ) {
			$reason_code = (string) $response->AuthorizeResult->AuthorizationDetails->AuthorizationStatus->ReasonCode;
		}
		return $reason_code;
	}

	/**
	 * Get billing address from response.
	 *
	 * @since 1.6.0
	 *
	 * @param object $response Response from self::request()
	 *
	 * @return array Billing address
	 */
	public static function get_billing_address_from_response( $response ) {
		$details = array();
		if ( isset( $response->AuthorizeOnBillingAgreementResult->AuthorizationDetails->AuthorizationBillingAddress ) ) {
			$details = (array) $response->AuthorizeOnBillingAgreementResult->AuthorizationDetails->AuthorizationBillingAddress;
		} elseif ( isset( $response->AuthorizeResult->AuthorizationDetails->AuthorizationBillingAddress ) ) {
			$details = (array) $response->AuthorizeResult->AuthorizationDetails->AuthorizationBillingAddress;
		}
		return $details;
	}

	/**
	 * Update order billing address.
	 *
	 * @since 1.6.0
	 *
	 * @param int   $order_id Order
	 * @param array $address  Billing address
	 *
	 * @return bool
	 */
	public static function update_order_billing_address( $order_id, $address = array() ) {
		// Format address and map to WC fields
		$address_lines = array();

		if ( ! empty( $address['AddressLine1'] ) ) {
			$address_lines[] = $address['AddressLine1'];
		}
		if ( ! empty( $address['AddressLine2'] ) ) {
			$address_lines[] = $address['AddressLine2'];
		}
		if ( ! empty( $address['AddressLine3'] ) ) {
			$address_lines[] = $address['AddressLine3'];
		}

		if ( 3 === sizeof( $address_lines ) ) {
			update_post_meta( $order_id, '_billing_company', $address_lines[0] );
			update_post_meta( $order_id, '_billing_address_1', $address_lines[1] );
			update_post_meta( $order_id, '_billing_address_2', $address_lines[2] );
		} elseif ( 2 === sizeof( $address_lines ) ) {
			update_post_meta( $order_id, '_billing_address_1', $address_lines[0] );
			update_post_meta( $order_id, '_billing_address_2', $address_lines[1] );
		} elseif ( sizeof( $address_lines ) ) {
			update_post_meta( $order_id, '_billing_address_1', $address_lines[0] );
		}

		if ( isset( $address['City'] ) ) {
			update_post_meta( $order_id, '_billing_city', $address['City'] );
		}

		if ( isset( $address['PostalCode'] ) ) {
			update_post_meta( $order_id, '_billing_postcode', $address['PostalCode'] );
		}

		if ( isset( $address['StateOrRegion'] ) ) {
			update_post_meta( $order_id, '_billing_state', $address['StateOrRegion'] );
		}

		if ( isset( $address['CountryCode'] ) ) {
			update_post_meta( $order_id, '_billing_country', $address['CountryCode'] );
		}

		return true;
	}

	/**
	 * Update order from authorization response.
	 *
	 * @since 1.6.0
	 *
	 * @param WC_Order $order       Order object
	 * @param Object   $response    Response from self::request
	 * @param bool     $capture_now Whether to capture immediately
	 *
	 * @return bool
	 */
	public static function update_order_from_authorize_response( $order, $response, $capture_now = false ) {
		$auth_id = self::get_auth_id_from_response( $response );
		if ( ! $auth_id ) {
			return false;
		}
		update_post_meta( $order->id, 'amazon_authorization_id', $auth_id );

		self::update_order_billing_address( $order->id, self::get_billing_address_from_response( $response ) );

		$state = self::get_auth_state_from_reponse( $response );
		if ( 'declined' === $state ) {
			$order->add_order_note( sprintf( __( 'Order Declined with reason code: %s', 'woocommerce-gateway-amazon-payments-advanced' ), self::get_auth_state_reason_code_from_response( $response ) ) );
			// Payment was not authorized
			return false;
		}

		if ( $capture_now ) {
			update_post_meta( $order->id, 'amazon_capture_id', str_replace( '-A', '-C', $auth_id ) );

			$order->add_order_note( sprintf( __( 'Captured (Auth ID: %s)', 'woocommerce-gateway-amazon-payments-advanced' ), str_replace( '-A', '-C', $auth_id ) ) );
		} else {
			$order->add_order_note( sprintf( __( 'Authorized (Auth ID: %s)', 'woocommerce-gateway-amazon-payments-advanced' ), $auth_id ) );
		}

		return true;
	}

	/**
	 * Close order reference
	 *
	 * @since 1.6.0
	 *
	 * @param int|WC_Order $order Order
	 *
	 * @return bool|WP_Error Return true when succeed. Otherwise WP_Error is returned
	 */
	public static function close_order_reference( $order ) {
		$order = wc_get_order( $order );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', __( 'Invalid order ID', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		if ( 'amazon_payments_advanced' !== $order->payment_method ) {
			return new WP_Error( 'invalid_order', __( 'Order is not paid via Amazon Payments', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		$amazon_reference_id = get_post_meta( $order->id, 'amazon_reference_id', true );
		if ( ! $amazon_reference_id ) {
			return new WP_Error( 'order_missing_amazon_reference_id', __( 'Order missing Amazon reference ID', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		$response = self::request( array(
			'Action'                 => 'CloseOrderReference',
			'AmazonOrderReferenceId' => $amazon_reference_id,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif ( isset( $response->Error->Message ) ) {

			$order->add_order_note( (string) $response->Error->Message );

			$code = isset( $response->Error->Code ) ? (string) $response->Error->Code : 'amazon_error_response';
			return new WP_Error( $code, (string) $response->Error->Message );
		} else {

			$order->add_order_note( sprintf( __( 'Order reference %s closed ', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_reference_id ) );

		}

		return true;
	}

	/**
	 * Close authorization.
	 *
	 * @param int    $order_id                Order ID
	 * @param string $amazon_authorization_id Authorization ID
	 *
	 * @return bool|WP_Error True if succeed. Otherwise WP_Error is returned
	 */
	public static function close_authorization( $order_id, $amazon_authorization_id ) {
		$order = new WC_Order( $order_id );

		if ( 'amazon_payments_advanced' == $order->payment_method ) {
			$response = self::request( array(
				'Action'                => 'CloseAuthorization',
				'AmazonAuthorizationId' => $amazon_authorization_id,
			) );

			if ( is_wp_error( $response ) ) {
				$ret = $response;
			} elseif ( isset( $response->Error->Message ) ) {
				$order->add_order_note( (string) $response->Error->Message );
				$code = isset( $response->Error->Code ) ? (string) $response->Error->Code : 'amazon_error_response';
				$ret = new WP_Error( $code, (string) $response->Error->Message );
			} else {
				delete_post_meta( $order_id, 'amazon_authorization_id' );

				$order->add_order_note( sprintf( __( 'Authorization closed (Auth ID: %s)', 'woocommerce-gateway-amazon-payments-advanced' ), $amazon_authorization_id ) );
				$ret = true;
			}
		} else {
			$ret = new WP_Error( 'invalid_order', __( 'Order is not paid via Amazon Payments', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		return $ret;
	}

	/**
	 * Capture payment.
	 *
	 * @see https://payments.amazon.com/documentation/apireference/201752040
	 *
	 * @since 1.6.0
	 *
	 * @param int|WC_Order $order Order
	 * @param array $args Whether to immediately capture or not
	 *
	 * @return bool|WP_Error
	 */
	public static function capture( $order, $args = array() ) {
		$order = wc_get_order( $order );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', __( 'Invalid order ID', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		if ( 'amazon_payments_advanced' !== $order->payment_method ) {
			return new WP_Error( 'invalid_order', __( 'Order is not paid via Amazon Payments', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		$args = wp_parse_args(
			$args,
			array(
				'amazon_authorization_id' => get_post_meta( $order->id, 'amazon_authorization_id', true ),
				'capture_now'             => false,
			)
		);

		if ( ! $args['amazon_authorization_id'] ) {
			return new WP_Error( 'order_missing_authorization_id', __( 'Order missing Amazon authorization ID', 'woocommerce-gateway-amazon-payments-advanced' ) );
		}

		$response = self::request( self::get_capture_request_args( $order, $args ) );

		if ( isset( $response->Error->Message ) ) {
			$code = isset( $response->Error->Code ) ? (string) $response->Error->Code : 'amazon_error_response';
			return new WP_Error( $code, (string) $response->Error->Message );
		}

		return $response;
	}

	/**
	 * Get args to perform Capture request.
	 *
	 * @since 1.6.0
	 *
	 * @param WC_Order $order Order object
	 * @param array    $args  Base args
	 */
	public static function get_capture_request_args( WC_Order $order, $args ) {
		return array(
			'Action'                     => 'Capture',
			'AmazonAuthorizationId'      => $args['amazon_authorization_id'],
			'CaptureReferenceId'         => $order->id . '-' . current_time( 'timestamp', true ),
			'CaptureAmount.Amount'       => $order->get_total(),
			'CaptureAmount.CurrencyCode' => strtoupper( get_woocommerce_currency() )
		);
	}

	/**
	 * Capture payment
	 *
	 * @param int $order_id Order ID
	 * @param string $amazon_authorization_id Optional Amazon authorization ID.
	 *                                        If not provided, value from order
	 *                                        meta will be used.
	 */
	public static function capture_payment( $order_id, $amazon_authorization_id = null ) {
		$response = self::capture( $order_id, array(
			'amazon_authorization_id' => $amazon_authorization_id,
		) );

		return self::handle_payment_capture_response( $response, $order_id );
	}

	/**
	 * Handle the result of a capture request.
	 *
	 * @since 1.6.0
	 *
	 * @param object       $response Response from self::request()
	 * @param int|WC_Order $order    Order
	 *
	 * @return bool whether or not payment was captured
	 */
	public static function handle_payment_capture_response( $response, $order ) {
		$order = wc_get_order( $order );

		if ( is_wp_error( $response ) ) {
			$order->add_order_note( sprintf( __( 'Error: Unable to capture funds with Amazon. Reason: %s', 'woocommerce-gateway-amazon-payments-advanced' ), $response->get_error_message() ) );

			return false;
		}

		return self::update_order_from_capture_response( $order, $response );
	}

	/**
	 * Update order from capture response.
	 *
	 * @since 1.6.0
	 *
	 * @param WC_Order $order    Order object
	 * @param Object   $response Response from self::request
	 *
	 * @return bool
	 */
	public static function update_order_from_capture_response( $order, $response ) {
		$capture_id = (string) $response->CaptureResult->CaptureDetails->AmazonCaptureId;
		if ( ! $capture_id ) {
			return false;
		}

		$order->add_order_note( sprintf( __( 'Capture Attempted (Capture ID: %s)', 'woocommerce-gateway-amazon-payments-advanced' ), $capture_id ) );

		update_post_meta( $order->id, 'amazon_capture_id', $capture_id );

		$order->payment_complete();

		return true;
	}

	/**
	 * Refund a payment
	 *
	 * @param int    $order_id
	 * @param string $capture_id
	 * @param float  $amount
	 * @param stirng $note
	 *
	 * @return bool Returns true if succeed
	 */
	public static function refund_payment( $order_id, $capture_id, $amount, $note ) {
		$order = new WC_Order( $order_id );
		$ret   = false;

		if ( $order->payment_method == 'amazon_payments_advanced' ) {

			if ( 'US' == WC()->countries->get_base_country() && $amount > $order->get_total() ) {
				$order->add_order_note( sprintf( __( 'Unable to refund funds via Amazon: %s', 'woocommerce-gateway-amazon-payments-advanced' ), __( 'Refund amount is greater than order total.', 'woocommerce-gateway-amazon-payments-advanced' ) ) );

				return false;
			} elseif ( $amount > min( ( $order->get_total() * 1.15 ), ( $order->get_total() + 75 ) ) ) {
				$order->add_order_note( sprintf( __( 'Unable to refund funds via Amazon: %s', 'woocommerce-gateway-amazon-payments-advanced' ), __( 'Refund amount is greater than the max refund amount.', 'woocommerce-gateway-amazon-payments-advanced' ) ) );

				return false;
			}

			$response = self::request( array(
				'Action'                    => 'Refund',
				'AmazonCaptureId'           => $capture_id,
				'RefundReferenceId'         => $order->id . '-' . current_time( 'timestamp', true ),
				'RefundAmount.Amount'       => $amount,
				'RefundAmount.CurrencyCode' => strtoupper( get_woocommerce_currency() ),
				'SellerRefundNote'          => $note
			) );

			if ( is_wp_error( $response ) ) {

				$order->add_order_note( sprintf( __( 'Unable to refund funds via Amazon: %s', 'woocommerce-gateway-amazon-payments-advanced' ), $response->get_error_message() ) );

			} elseif ( isset( $response->Error->Message ) ) {

				$order->add_order_note( sprintf( __( 'Unable to refund funds via Amazon: %s', 'woocommerce-gateway-amazon-payments-advanced' ), (string) $response->Error->Message ) );

			} else {
				$refund_id = (string) $response->RefundResult->RefundDetails->AmazonRefundId;

				/* Translators: 1: refund amount, 2: refund note */
				$order->add_order_note( sprintf( __( 'Refunded %1$s (%2$s)', 'woocommerce-gateway-amazon-payments-advanced' ), wc_price( $amount ), $note ) );

				add_post_meta( $order_id, 'amazon_refund_id', $refund_id );

				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Get order ID from reference ID
	 *
	 * @param  string $reference_id
	 *
	 * @return int
	 */
	public static function get_order_id_from_reference_id( $reference_id ) {
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT post_id
			FROM $wpdb->postmeta
			WHERE meta_key = 'amazon_reference_id'
			AND meta_value = %s
		", $reference_id ) );

		if ( ! is_wp_error( $order_id ) ) {
			return $order_id;
		}

		return 0;
	}

	/**
	 * Get reference state.
	 *
	 * @param int    $order_id Order ID
	 * @param string $state    State to retrieve
	 *
	 * @return string Reference state
	 */
	public static function get_order_ref_state( $order_id, $state = 'amazon_reference_state' ) {
		$ret_state = '';

		switch ( $state ) {
			case 'amazon_reference_state':
				$ref_id = get_post_meta( $order_id, 'amazon_reference_id', true );
				if ( $ref_id ) {
					$ret_state = self::get_reference_state( $order_id, $ref_id );
				}
				break;

			case 'amazon_authorization_state':
				$ref_id = get_post_meta( $order_id, 'amazon_authorization_id', true );
				if ( $ref_id ) {
					$ret_state = self::get_authorization_state( $order_id, $ref_id );
				}
				break;

			case 'amazon_capture_state':
				$ref_id = get_post_meta( $order_id, 'amazon_capture_id', true );
				if ( $ref_id ) {
					$ret_state = self::get_capture_state( $order_id, $ref_id );
				}
				break;
		}

		return $ret_state;
	}

	/**
	 * Format an Amazon Payments Address DataType for WooCommerce
	 * See: https://payments.amazon.com/documentation/apireference/201752430
	 *
	 * @param array $address Address object from Amazon Payments API
	 *
	 * @return array Address formatted for WooCommerce
	 */
	public static function format_address( $address ) {

		$formatted = array();

		// Get first and last names
		$address_name            = explode( ' ', (string) $address->Name );
		$formatted['last_name']  = array_pop( $address_name );
		$formatted['first_name'] = implode( ' ', $address_name );

		// Special handling for German speaking countries.
		//
		// @see https://github.com/woothemes/woocommerce-gateway-amazon-payments-advanced/issues/25
		if ( ! empty( $address->CountryCode ) && in_array( $address->CountryCode, array( 'AT', 'DE' ) ) ) {

			if ( ! empty( $address->AddressLine3 ) ) {

				$formatted['company']   = trim( (string) $address->AddressLine1 . ' ' . (string) $address->AddressLine2 );
				$formatted['address_1'] = (string) $address->AddressLine3;

			} elseif ( ! empty( $address->AddressLine2 ) ) {

				$formatted['company']   = (string) $address->AddressLine1;
				$formatted['address_1'] = (string) $address->AddressLine2;

			} else {

				$formatted['address_1'] = (string) $address->AddressLine1;

			}

		} else {

			// Format address and map to WC fields
			$address_lines = array();

			if ( ! empty( $address->AddressLine1 ) ) {
				$address_lines[] = (string) $address->AddressLine1;
			}
			if ( ! empty( $address->AddressLine2 ) ) {
				$address_lines[] = (string) $address->AddressLine2;
			}
			if ( ! empty( $address->AddressLine3 ) ) {
				$address_lines[] = (string) $address->AddressLine3;
			}

			if ( 3 === sizeof( $address_lines ) ) {

				$formatted['company']   = $address_lines[0];
				$formatted['address_1'] = $address_lines[1];
				$formatted['address_2'] = $address_lines[2];

			} elseif ( 2 === sizeof( $address_lines ) ) {

				$formatted['address_1'] = $address_lines[0];
				$formatted['address_2'] = $address_lines[1];

			} elseif ( sizeof( $address_lines ) ) {

				$formatted['address_1'] = $address_lines[0];

			}

		}

		$formatted['phone'] = isset( $address->Phone ) ? (string) $address->Phone : null;
		$formatted['city'] = isset( $address->City ) ? (string) $address->City : null;
		$formatted['postcode'] = isset( $address->PostalCode ) ? (string) $address->PostalCode : null;
		$formatted['state'] = isset( $address->StateOrRegion ) ? (string) $address->StateOrRegion : null;
		$formatted['country'] = isset( $address->CountryCode ) ? (string) $address->CountryCode : null;

		return array_filter( $formatted );

	}
}
