/*global jQuery, window, document, setTimeout, console, amazon_payments_advanced_params, amazon, OffAmazonPayments */
jQuery( function( $ ) {
	var referenceId, billingAgreementId, addressBookWidgetExists, buttonLoaded = false;

	/**
	 * Helper method for logging - don't want to cause an error trying to log an error!
	 */
	function logError() {
		if ( 'undefined' === typeof console.log ) {
			return;
		}

		console.log.apply( console, arguments );
	}

	function wcAmazonErrorToString( error ) {
		var message = '';

		if ( 'object' !== typeof error ) {
			return message;
		}

		if ( 'function' === typeof error.getErrorCode ) {
			message += '(' + error.getErrorCode() + ') ';
		}

		if ( 'function' === typeof error.getErrorMessage ) {
			message += error.getErrorMessage();
		}

		return message;
	}

	/**
	 * Pre-populate "create account" form with Amazon profile data if an existing WC user isn't present
	 */
	function wcAmazonPrefillAccountCreationForm() {
		if ( amazon && amazon_payments_advanced_params.is_checkout && amazon_payments_advanced_params.access_token ) {
			// Only retrieve profile data if no user is logged in (e.g. the "create account" form exists)
			if ( 0 === $( '.create-account' ).length ) {
				return;
			}

			amazon.Login.authorize(
				{
					scope: 'profile',
					interactive: 'never'
				},
				function( response ) {
					if ( response.error ) {
						return logError( 'Error encountered in amazon.Login.authorize:', response.error );
					}

					/* eslint-disable no-shadow */
					amazon.Login.retrieveProfile( function( response ) {
					/* eslint-enable no-shadow */
						if ( response.success ) {
							var names = response.profile.Name.split( ' ' );

							$( '#billing_first_name' ).val( names.shift() );
							$( '#billing_last_name' ).val( names.join( ' ' ) );
							$( '#billing_email' ).val( response.profile.PrimaryEmail );
						} else {
							logError( 'Error encountered in amazon.Login.retrieveProfile:', response );
						}
					} );
				}
			);
		}
	}

	// Potentially pre-fill account creation form.
	wcAmazonPrefillAccountCreationForm();

	// Login with Amazon Widget.
	wcAmazonPaymentsButton();

	// AddressBook, Wallet, and maybe Recurring Payment Consent widgets.
	addressBookWidgetExists = ( $( '#amazon_addressbook_widget' ).length > 0 );
	if ( addressBookWidgetExists ) {
		wcAmazonWidgets();
	} else {
		wcAmazonWalletWidget();
	}

	function wcAmazonOnPaymentSelect() {
		renderReferenceIdInput();
	}

	function wcAmazonOnOrderReferenceCreate( orderReference ) {
		if ( referenceId ) {
			return;
		}

		referenceId = orderReference.getAmazonOrderReferenceId();
		renderReferenceIdInput();
	}

	function renderReferenceIdInput() {
		// Added the reference ID field.
		$( 'input.amazon-reference-id' ).remove();

		var referenceIdInput = '<input class="amazon-reference-id" type="hidden" name="amazon_reference_id" value="' + referenceId + '" />';

		$( 'form.checkout' ).append( referenceIdInput );
		$( 'form#order_review' ).append( referenceIdInput );
	}

	function wcAmazonOnBillingAgreementCreate( billingAgreement ) {
		if ( billingAgreementId ) {
			return;
		}

		billingAgreementId = billingAgreement.getAmazonBillingAgreementId();

		var billingAgreementIdInput = '<input class="amazon-billing-agreement-id" type="hidden" name="amazon_billing_agreement_id" value="' + billingAgreementId + '" />';

		$( 'form.checkout' ).append( billingAgreementIdInput );
		$( 'form#order_review' ).append( billingAgreementIdInput );
		$( '#amazon_consent_widget' ).show();
	}

	function wcAmazonPaymentsButton() {
		if ( buttonLoaded ) {
			return;
		}

		if ( 0 !== $( '#pay_with_amazon' ).length ) {
			var buttonWidgetParams = {
				type: amazon_payments_advanced_params.button_type,
				color: amazon_payments_advanced_params.button_color,
				size: amazon_payments_advanced_params.button_size,

				authorization: function() {
					var loginOptions = {
						scope: 'profile postal_code payments:widget payments:shipping_address payments:billing_address'
					};
					amazon.Login.authorize( loginOptions, amazon_payments_advanced_params.redirect );
				},
				onError: function( error ) {
					var msg = wcAmazonErrorToString( error );

					logError( 'Error encountered in OffAmazonPayments.Button', msg ? ': ' + msg : '' );
				}
			};

			if ( '' !== amazon_payments_advanced_params.button_language ) {
				buttonWidgetParams.language = amazon_payments_advanced_params.button_language;
			}

			OffAmazonPayments.Button( 'pay_with_amazon', amazon_payments_advanced_params.seller_id, buttonWidgetParams );
			buttonLoaded = true;
		}
	}

	function wcAmazonWidgets() {
		var addressBookConfig = {
			sellerId: amazon_payments_advanced_params.seller_id,
			onReady: function() {
				wcAmazonWalletWidget();
				$( document ).trigger( 'wc_amazon_pa_widget_ready' );
			},
			onOrderReferenceCreate: wcAmazonOnOrderReferenceCreate,
			onAddressSelect: function() {
				$( 'body' ).trigger( 'update_checkout' );
			},
			design: {
				designMode: 'responsive'
			},
			onError: function( error ) {
				var msg = wcAmazonErrorToString( error );
				logError( 'Error encountered in OffAmazonPayments.Widgets.AddressBook', msg ? ': ' + msg : '' );
			}
		};
		var isRecurring = amazon_payments_advanced_params.is_recurring;
		var declinedCode = amazon_payments_advanced_params.declined_code;

		if ( isRecurring ) {
			addressBookConfig.agreementType = 'BillingAgreement';

			addressBookConfig.onReady = function( billingAgreement ) {
				wcAmazonOnBillingAgreementCreate( billingAgreement );
				wcAmazonWalletWidget();
				wcAmazonConsentWidget();
				$( document ).trigger( 'wc_amazon_pa_widget_ready' );
			};
		}

		if ( declinedCode ) {
			addressBookConfig.displayMode = 'Read';
			addressBookConfig.amazonOrderReferenceId = amazon_payments_advanced_params.reference_id;

			delete addressBookConfig.onOrderReferenceCreate;
		}

		new OffAmazonPayments.Widgets.AddressBook( addressBookConfig ).bind( 'amazon_addressbook_widget' );
	}

	// Wallet widget
	function wcAmazonWalletWidget() {
		// If previously declined with redirection to cart, do not render the
		// wallet widget.
		if ( amazon_payments_advanced_params.declined_redirect_url ) {
			return;
		}

		var walletConfig = {
			sellerId: amazon_payments_advanced_params.seller_id,
			design: {
				designMode: 'responsive'
			},
			onError: function( error ) {
				var msg = wcAmazonErrorToString( error );
				logError( 'Error encountered in OffAmazonPayments.Widgets.Wallet', msg ? ': ' + msg : '' );
			}
		};

		if ( amazon_payments_advanced_params.reference_id ) {
			referenceId = amazon_payments_advanced_params.reference_id;
			walletConfig.amazonOrderReferenceId = referenceId;
			walletConfig.onPaymentSelect = wcAmazonOnPaymentSelect;
		}

		if ( ! addressBookWidgetExists ) {
			walletConfig.onOrderReferenceCreate = wcAmazonOnOrderReferenceCreate;
		}

		if ( amazon_payments_advanced_params.is_recurring ) {
			walletConfig.agreementType = 'BillingAgreement';

			if ( billingAgreementId ) {
				walletConfig.amazonBillingAgreementId = billingAgreementId;
			} else {
				walletConfig.onReady = function( billingAgreement ) {
					wcAmazonOnBillingAgreementCreate( billingAgreement );
					wcAmazonConsentWidget();
				};
			}
		}

		new OffAmazonPayments.Widgets.Wallet( walletConfig ).bind( 'amazon_wallet_widget' );
	}

	// Recurring payment consent widget
	function wcAmazonConsentWidget() {
		if ( ! amazon_payments_advanced_params.is_recurring || ! billingAgreementId ) {
			return;
		}

		new OffAmazonPayments.Widgets.Consent( {
			sellerId: amazon_payments_advanced_params.seller_id,
			amazonBillingAgreementId: billingAgreementId,
			design: {
				designMode: 'responsive'
			},
			onConsent: function( billingAgreementConsentStatus ) {
				var buyerBillingAgreementConsentStatus = billingAgreementConsentStatus.getConsentStatus();

				/* eslint-disable eqeqeq */
				$( '#place_order' ).css( 'opacity', ( 'true' == buyerBillingAgreementConsentStatus ) ? 1 : 0.5 );
				$( '#place_order' ).prop( 'disabled', ( 'true' != buyerBillingAgreementConsentStatus ) );
				/* eslint-enable eqeqeq */
			},
			onError: function( error ) {
				var msg = wcAmazonErrorToString( error );
				logError( 'Error encountered in OffAmazonPayments.Widgets.Consent', msg ? ': ' + msg : '' );
			}
		} ).bind( 'amazon_consent_widget' );
	}

	$( 'body' ).on( 'click', '#amazon-logout', function() {
		amazon.Login.logout();
	} );

	/**
	 *
	 * The AJAX order review refresh causes some duplicate form fields to be created.
	 * If we're checking out with Amazon enabled and creating a new account, disable the duplicate fields
	 * that don't have values so we don't overwrite the good values in $_POST
	 *
	 */
	$( 'form.checkout' ).on( 'checkout_place_order', function() {
		var fieldSelectors = [
			':input[name=billing_email]',
			':input[name=billing_first_name]',
			':input[name=billing_last_name]',
			':input[name=account_username]',
			':input[name=account_password]',
			':input[name=createaccount]'
		].join( ',' );

		$( this ).find( fieldSelectors ).each( function() {
			var $input = $( this );
			if ( '' === $input.val() && $input.is( ':hidden' ) ) {
				$input.attr( 'disabled', 'disabled' );
			}

			// For createaccount checkbox, the value on dupe element should
			// matches with visible createaccount checkbox.
			if ( 'createaccount' === $input.attr( 'name' ) && $( '#createaccount' ).length ) {
				$input.prop( 'checked', $( '#createaccount' ).is( ':checked' ) );
			}
		} );
	} );

	$( 'body' ).on( 'updated_checkout', wcAmazonPaymentsButton );
	$( 'body' ).on( 'updated_cart_totals', function() {
		buttonLoaded = false;
		wcAmazonPaymentsButton();
	} );

	$( window.document ).on( 'wc_amazon_pa_widget_ready', function() {
		// For declined authorization.
		//
		// @see https://github.com/woocommerce/woocommerce-gateway-amazon-payments-advanced/issues/214
		if ( amazon_payments_advanced_params.declined_redirect_url ) {
			// Scroll to top so customer notices with the error message
			// before redirected to cancel order URL.
			$( 'body' ).on( 'updated_checkout', function() {
				$( 'html, body' ).scrollTop( 0 );

				// Gives time for customer to read the notice.
				setTimeout( function() {
					window.location = amazon_payments_advanced_params.declined_redirect_url;
				}, 5000 );
			} );
		}
	} );
} );
