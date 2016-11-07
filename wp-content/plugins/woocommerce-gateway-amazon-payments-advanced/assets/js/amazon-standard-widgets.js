/*global amazon_payments_advanced_params */
jQuery( function( $ ) {

	// Make sure we only try to load the Amazon widgets once
	var widgetsLoaded = false, buttonLoaded = false;

	// Login Widget
	function wcAmazonPaymentsButton() {

		// if button skeleton element is not on the page or if button has already been created within the skeleton element
		if ( 0 === $( '#pay_with_amazon' ).length || $( '#pay_with_amazon' ).html().length > 0 ) {
			return;
		}

		new OffAmazonPayments.Widgets.Button( {
			sellerId            : amazon_payments_advanced_params.seller_id,
			useAmazonAddressBook: amazon_payments_advanced_params.is_checkout_pay_page ? false : true,
			onSignIn            : function ( orderReference ) {
				amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
				window.location = amazon_payments_advanced_params.redirect + '&amazon_reference_id=' + amazonOrderReferenceId;
			}
		} ).bind( 'pay_with_amazon' );

	}

	wcAmazonPaymentsButton();

	$( 'body' ).on( 'updated_shipping_method', wcAmazonPaymentsButton );

	function isAmazonCheckout() {

		return ( 'amazon_payments_advanced' === $( 'input[name=payment_method]:checked' ).val() );

	}

	$( 'body' ).on( 'updated_checkout', function() {

		if ( isAmazonCheckout() ) {

			loadWidgets();

			$( '#amazon_customer_details' ).show();
			$( '#customer_details :input' ).detach();

		}

		wcAmazonPaymentsButton();

	} );

	/**
	 *
	 * The AJAX order review refresh causes some duplicate form fields to be created.
	 * If we're checking out with Amazon enabled and creating a new account, disable the duplicate fields
	 * that don't have values so we don't overwrite the good values in $_POST
	 *
	 */
	$( 'form.checkout' ).on( 'checkout_place_order', function() {

		if ( ! $( ':checkbox[name=createaccount]' ).is( ':checked' ) ) {
			return;
		}

		$( this ).find( ':input[name=billing_email],:input[name=account_password]' ).each( function() {
			var $input = $( this );
			if ( '' === $input.val() && $input.is(':hidden') ) {
				$input.attr( 'disabled', 'disabled' );
			}
		} );

	} );

	// Addressbook widget
	function wcAmazonAddressBookWidget() {
		new OffAmazonPayments.Widgets.AddressBook( {
			sellerId              : amazon_payments_advanced_params.seller_id,
			amazonOrderReferenceId: amazon_payments_advanced_params.reference_id,
			onAddressSelect       : function ( orderReference ) {
				$( 'body' ).trigger( 'update_checkout' );
			},
			design                : {
				designMode: 'responsive'
			}
		} ).bind( 'amazon_addressbook_widget' );
	}

	// Wallet widget
	function wcAmazonWalletWidget() {
		new OffAmazonPayments.Widgets.Wallet( {
			sellerId              : amazon_payments_advanced_params.seller_id,
			amazonOrderReferenceId: amazon_payments_advanced_params.reference_id,
			design                : {
				designMode: 'responsive'
			}
		} ).bind( 'amazon_wallet_widget' );
	}

	// Helper method to load widgets and limit to a single instantiation
	function loadWidgets() {

		if ( widgetsLoaded ) {
			return;
		}

		wcAmazonAddressBookWidget();
		wcAmazonWalletWidget();

		widgetsLoaded = true;

		// Not exactly widgets ready, but no onReady param for standard widgets.
		// Use the same name with app widgets for consistency.
		wcAmazonMaybeTriggerReadyEvent();
	}

	// Only load widgets on the initial render if Amazon Payments is the chosen method
	if ( isAmazonCheckout() ) {

		loadWidgets();

	}

	/**
	 * Maybe trigger wc_amazon_pa_widget_ready.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/API/MutationObserver
	 */
	function wcAmazonMaybeTriggerReadyEvent() {
		if ( 'function' !== typeof MutationObserver  ) {
			return false;
		}

		var triggered = false;
		var observer = new MutationObserver( function( mutations ) {
			mutations.forEach( function( mutation ) {
				if ( ! triggered ) {
					$( document ).trigger( 'wc_amazon_pa_widget_ready' );
					triggered = true;
					observer.disconnect();
				}
			} );
		} );

		observer.observe( document.getElementById( 'amazon_wallet_widget' ), {
			childList: true
		} );
	}

});
