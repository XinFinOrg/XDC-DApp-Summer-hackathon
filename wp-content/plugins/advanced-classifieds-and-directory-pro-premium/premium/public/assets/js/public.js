(function( $ ) {
	'use strict';	

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.6.5
	 */
	$(function() {
		// Multi Categories: Load the custom fields
		var $acadp_category_checklist = $( '.acadp-category-checkbox', '#acadp-multi-categories-checklist' );	

		$acadp_category_checklist.on( 'change', function() {
			$acadp_category_checklist.prop( 'disabled', true );
			$( '.acadp-listing-form-submit-btn' ).prop( 'disabled', true );

			$( '#acadp-custom-fields-listings' ).html( '<div class="acadp-spinner"></div>' );
						
			var category_ids = $( '.acadp-category-checkbox:checked', '#acadp-multi-categories-checklist' ).map(function( value, index ) {
				return this.value;
			}).get();
			
			if ( 0 == category_ids.length ) {
				category_ids = '';
			}
									
			var data = {
				'action': 'acadp_public_custom_fields_listings',
				'post_id': $( '#acadp-custom-fields-listings' ).data( 'post_id' ),
				'terms': category_ids,
				'security': acadp.ajax_nonce
			};
			
			$.post( acadp.ajax_url, data, function( response ) {	
				$( '#acadp-custom-fields-listings' ).html( response );

				$( '.acadp-listing-form-submit-btn' ).prop( 'disabled', false );
				$acadp_category_checklist.prop( 'disabled', false );					
			});				
		});			
	});

})( jQuery );