(function( $ ) {
	'use strict';

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.6.4
	 */
	$(function() {		
		
		// Checkout: Toogle submt button's label display based on the plan selection
		$( 'input[type="radio"]', '#acadp-wc-checkout-form' ).on( 'change', function() {			
			var $selected_plan = $( 'input[name=wc_plan]:checked', '#acadp-wc-checkout-form' );
			
			if ( $selected_plan.hasClass( 'free' ) || $selected_plan.hasClass( 'active' ) ) {
				$( '#acadp-wc-checkout-submit-btn' ).val( acadp.finish_submission_btn_label );
			} else {
				$( '#acadp-wc-checkout-submit-btn' ).val( acadp.proceed_to_payment_btn_label );
			}			
		}).trigger( 'change' );
		
	});

})( jQuery );
