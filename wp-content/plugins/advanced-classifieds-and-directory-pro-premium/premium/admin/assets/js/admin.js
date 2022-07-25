(function( $ ) {
	'use strict';	

	/**
 	 * Display the media uploader.
 	 *
 	 * @since 1.7.5
 	 */
	 function acadp_premium_render_media_uploader( field ) { 
    	var file_frame, json;
 
     	// If an instance of file_frame already exists, then we can open it rather than creating a new instance
    	if ( undefined !== file_frame ) { 
        	file_frame.open();
        	return; 
    	}; 

     	// Here, use the wp.media library to define the settings of the media uploader
    	file_frame = wp.media.frames.file_frame = wp.media({
        	frame: 'post',
        	state: 'insert',
        	multiple: false
    	});
 
     	// Setup an event handler for what to do when a file has been selected
    	file_frame.on( 'insert', function() { 
        	// Read the JSON data returned from the media uploader
    		json = file_frame.state().get( 'selection' ).first().toJSON();
		
			// Make sure that we have the URL of a file to display
    		if ( 0 > $.trim( json.url.length ) ) {
        		return;
    		};
		
			// Set the file URL and ID in the form
			$( '#acadp-import-' + field + '-name' ).html( json.url );
			$( '#acadp-import-' + field ).val( json.id );
    	});
 
    	// Now display the actual file_frame
    	file_frame.open(); 
	};

	/**
 	 * Widget: Initiate color picker 
 	 *
 	 * @since 1.8.5
 	 */
	 function acadp_widget_color_picker( widget ) {
		widget.find( '.acadp-slider-color-picker' ).wpColorPicker( {
			change: _.throttle( function() { // For Customizer
				$( this ).trigger( 'change' );
			}, 3000 )
		});
	}

	function on_acadp_widget_update( event, widget ) {
		acadp_widget_color_picker( widget );
	}

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.6.4
	 */
	$(function() {		
		
		// WooCommerce Plans
		if ( acadp_premium.is_woocommerce_plans_enabled > 0 ) {
			// Show pricing fields for "listings_package" product.
			$( '.options_group.pricing' ).addClass( 'show_if_listings_package' ).show();

			// Show pricing fields for "listings_featured" product.
			$( '.options_group.pricing' ).addClass( 'show_if_listings_featured' ).show();
			
			// Load relative WooCommerce plans based on the category selection in the listing form.
			$( '#acadp_category' ).on( 'change', function() {			
				var data = {
					'action': 'acadp_listings_wc_plans',
					'post_id': $( '#acadp-listings-wc-plans' ).data( 'post_id' ),
					'term_id': $( this ).val()
				};
				
				$.post( ajaxurl, data, function(response) {
					$( '#acadp-listings-wc-plans' ).html( response );
				});			
			});
		}		

		// Fee Plans
		if ( acadp_premium.is_fee_plans_enabled > 0 ) {
			// Load relative fee plans based on the category selection in the listing form.
			$( '#acadp_category' ).on( 'change', function() {			
				var data = {
					'action': 'acadp_listings_fee_plans',
					'post_id': $( '#acadp-listings-fee-plans' ).data('post_id'),
					'term_id': $(this).val()
				};
				
				$.post( ajaxurl, data, function(response) {
					$( '#acadp-listings-fee-plans' ).html( response );
				});			
			});	
		}

		// Slider: Initialize the color picker.
		if ( $.fn.wpColorPicker ) {
			$( '.acadp-slider-color-picker' ).wpColorPicker();

			$( '#widgets-right .widget:has(.acadp-slider-color-picker)' ).each(function() {
				acadp_widget_color_picker( $( this ) );
			});
	
			$( document ).on( 'widget-added widget-updated', on_acadp_widget_update );
		}
		
		// Multi Categories: Load the custom fields
		if ( acadp_premium.is_multi_categories_enabled > 0 ) {
			var $acadp_category_checklist = $( '.acadp-category-checkbox', '#acadp-multi-categories-checklist' );	

			$acadp_category_checklist.on( 'change', function( e ) {	
				e.preventDefault();
							
				$acadp_category_checklist.prop( 'disabled', true );
				$( '#acadp-custom-fields-list' ).html( '<div class="spinner"></div>' );
							
				var category_ids = $( '.acadp-category-checkbox:checked', '#acadp-multi-categories-checklist' ).map(function( value, index ) {
					return this.value;
				}).get();
				
				if ( 0 == category_ids.length ) {
					category_ids = '';
				}
										
				var data = {
					'action': 'acadp_custom_fields_listings',
					'post_id': $( '#acadp-custom-fields-list' ).data( 'post_id' ),
					'terms': category_ids,
					'security': acadp.ajax_nonce
				};
				
				$.post( ajaxurl, data, function( response ) {					
					$( '#acadp-custom-fields-list' ).html( response );
					$acadp_category_checklist.prop( 'disabled', false );					
				});				
			});			
		}

		// Import: Upload Fields
		$( '.acadp-import-upload-button' ).on( 'click', function( e ) {
			e.preventDefault();

			var field = $( this ).data( 'field' );
			acadp_premium_render_media_uploader( field );
		});

		// Import: Parse CSV, Collate Columns and Import Listings
		var import_step = 1;

		$( '#acadp-import-button' ).on( 'click', function( e ) {
			e.preventDefault();

			if ( 3 == import_step ) {
				window.location = window.location.href;
				return;
			}

			$( '#acadp-import-button' ).prop( 'disabled', true );
			$( '#acadp-import-status' ).html( '<span class="spinner"></span>' );

			var collated_fields = $( "select[name='collated_fields[]']" ).map(function() {
				return this.value;
			}).get();
			  
			var data = {
				'action': 'acadp_import',
				'csv_file': $( '#acadp-import-csv_file' ).val(),
				'images_file': $( '#acadp-import-images_file' ).val(),
				'columns_separator': $( '#acadp-import-columns_separator' ).val(),
				'values_separator': $( '#acadp-import-values_separator' ).val(),
				'add_new_term': $( '#acadp-import-add_new_term' ).val(),
				'add_new_user': $( '#acadp-import-add_new_user' ).val(),
				'do_geocode': $( '#acadp-import-do_geocode' ).val(),
				'step': import_step,
				'collated_fields': collated_fields,
				'security': acadp.ajax_nonce
			};
			
			$.post( 
				ajaxurl, 
				data, 
				function( response ) {
					if ( response.error ) {
						$( '#acadp-import-button' ).prop( 'disabled', false );
						$( '#acadp-import-status' ).html( '<span class="acadp-text-error">' + response.message + '</span>' );
					} else {
						$( '#acadp-import-button' ).prop( 'disabled', false );
						$( '#acadp-import-status' ).html( '<span class="acadp-text-success">' + response.message + '</span>' );

						if ( 1 == import_step ) {
							if ( response.html ) {								
								$( '#acadp-import-csv_file-button' ).prop( 'disabled', true );	
								$( response.html ).insertAfter( '#acadp-import-step1' );
								$( '#acadp-import-button' ).val( acadp_premium.i18n.import_listings );

								++import_step;
							}
						} else if ( 2 == import_step ) {
							$( '#acadp-import-logs' ).val( response.logs );
							$( '#acadp-import-button' ).val( acadp_premium.i18n.import_new_file );

							++import_step;
						}
					}					
				},
				'json'
			);
		});

	});

})( jQuery );