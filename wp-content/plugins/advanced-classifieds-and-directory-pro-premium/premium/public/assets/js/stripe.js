(function( $ ) {
	'use strict';
	
	/**
	 * Display stripe cc form.
	 *
	 * @since 1.6.4
	 */
	function acadp_stripe_display_cc_form() {		
		// Disable the payment gateway radio buttons to prevent repeated selection
		$( "input[name='payment_gateway']" ).attr( "disabled", "disabled" );
					
		// ...
		$( '#acadp-cc-form' ).html( '<div class="acadp-spinner"></div>' );
			
		var data = {
			'action': 'acadp_cc_form_stripe'
		};
			
		$.post( acadp.ajax_url, data, function( response ) {													   
			$( '#acadp-cc-form' ).html( response );
					
			$( "input[type='text']", '#acadp-cc-form-stripe' ).unbind( 'blur' ).on( 'blur', function( e ) {						
				if ( 0 === $( this ).val().length ) {
					$( this ).closest( '.form-group' ).addClass( 'has-error' );
				} else {
					$( this ).closest( '.form-group' ).removeClass( 'has-error' );
					$( "#acadp-checkout-errors" ).slideUp( 'normal', function() { 
						$( this ).html( '' );
					});
				}																									 
			});
					
			// Re-enable the payment gateway radio buttons
			$( "input[name='payment_gateway']" ).attr( "disabled", false );					
		});		
	}

	/**
	 * Stripe response handler.
	 *
	 * @since 1.6.4
	 */
	function acadp_stripe_response_handler( status, response ) {		
    	if ( response.error ) {			
			// Clear form fields
			$( "input[type='text']", '#acadp-cc-form-stripe' ).val( '' ).closest( '.form-group' ).addClass( 'has-error' );
			
			// Show errors returned by Stripe
        	$( "#acadp-checkout-errors" ).hide().html( '<span>'+response.error.message+'</span>' ).slideDown();
			
			// Re-enable the submit button
			$( '#acadp-checkout-submit-btn' ).attr( "disabled", false );			
    	} else {			
        	var form$ = $( "#acadp-checkout-form" );
			
        	// Token contains id, last4, and card type
        	var token = response['id'];
			
        	// Insert the token into the form so it gets submitted to the server
        	form$.append( "<input type='hidden' name='stripe_token' value='" + token + "'/>" );
			
        	// Submit
        	form$.get(0).submit();
    	}		
	}

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.6.4
	 */
	$(function() {			   
		// Get the default gateway selected
		var gateway = $( "input[name='payment_gateway']:checked" ).val();
		
		// Display stripe cc form if default gateway = stripe
		if ( 'stripe' == gateway ) acadp_stripe_display_cc_form();
		
		// Listen to payment gateway selection, display stripe cc form if applicable.
		$( "input[name='payment_gateway']" ).on( 'change', function() {						
			if ( 'stripe' == this.value ) {
				acadp_stripe_display_cc_form();
			} else {
				$( '#acadp-cc-form-stripe' ).remove();
			}			
		});
			   
		// Set stripe publishable key
		Stripe.setPublishableKey( acadp_stripe.publishable_key );

		// Listen to checkout form submission, process stripe cc form if applicable
		$( "#acadp-checkout-form" ).on( 'submit', function( e ) {
			var selected = $( "input[name='payment_gateway']:checked" ).val();
			
			if ( 'stripe' == selected ) {
				var amount = parseFloat( $( '#acadp-checkout-total-amount' ).html() );
				
				if ( amount > 0 ) {					
					// Get the values
					var cc_num    = $( '#acadp-card-number' ).val(),
    					cvc_num   = $( '#acadp-card-cvc' ).val(),
    					exp_month = $( '#acadp-card-expiry-month' ).val(),
    					exp_year  = $( '#acadp-card-expiry-year' ).val();
						
					var error = [];
				
					// Validate the number
					if ( ! Stripe.card.validateCardNumber( cc_num ) ) {
    					error.push( acadp_stripe.card_number_validation_error );
						$( '#acadp-card-number' ).val( '' ).closest( '.form-group' ).addClass( 'has-error' );
					}
				
					// Validate the CVC
					if ( ! Stripe.card.validateCVC( cvc_num ) ) {
    					error.push( acadp_stripe.card_cvc_validation_error );
						$( '#acadp-card-cvc' ).val( '' ).closest( '.form-group' ).addClass( 'has-error' );
					}
				
					// Validate the expiration
					if ( ! Stripe.card.validateExpiry( exp_month, exp_year ) ) {
    					error.push( acadp_stripe.card_expiry_validation_error );
						$( '#acadp-card-expiry-month, #acadp-card-expiry-year' ).val( '' ).closest( '.form-group' ).addClass( 'has-error' );
					}
				
					// ...
					if ( error.length > 0 ) {					
						// Show errors
        				$( "#acadp-checkout-errors" ).hide().html( '<span>'+error.join( '</span><span>' )+'</span>' ).slideDown();					
					} else {					
						// Disable the submit button to prevent repeated clicks
						$( '#acadp-checkout-submit-btn' ).attr( "disabled", "disabled" );
				
						// Send the card details to Stripe
						Stripe.createToken({
							number: cc_num,
							cvc: cvc_num,
							exp_month: exp_month,
							exp_year: exp_year
						}, acadp_stripe_response_handler );				
					}
				
					// Prevent the form from submitting with the default action
					return false;				
				}				
			}		
		});		
	});

})( jQuery );