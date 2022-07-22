(function( $ ) {
	'use strict';	

	/**
     * [Map: OpenStreetMap] Render a Map onto the selected jQuery element.
     *
	 * @since 1.8.0
	 */
	 function acadp_osm_render_map( $el ) {
		// Vars
		var $marker = $el.find( '.marker' );
		var lat = $( '#acadp-latitude' ).val();
		var lng = $( '#acadp-longitude' ).val();

		// Creating map options
		var map_options = {
            center: [ lat, lng ],
            zoom: acadp.zoom_level
        }

		// Creating a map object        	
		var map = new L.map( $el[0], map_options );

		// Creating a Layer object
		var layer = new L.TileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		});

		// Adding layer to the map
		map.addLayer( layer );

		// Creating Marker Options
		var marker_options = {
            clickable: true,
            draggable: true
        }

		// Creating a Marker
		var marker = L.marker( [ lat, lng ], marker_options );

		// Adding popup to the marker
		var content = $marker.html();
		if ( content ) {
			marker.bindPopup( content );
		}

		// Adding marker to the map
		marker.addTo( map );

		// Update latitude and longitude values in the form when marker is moved
		marker.on( 'dragend', function( event ) {
			var position = event.target.getLatLng();

			map.panTo( new L.LatLng( position.lat, position.lng ) );
			acadp_update_latlng( position.lat, position.lng );
		});

		// Update map when contact details fields are updated in the custom post type "acadp_listings"
		$ ( '.acadp-map-field', '#acadp-contact-details' ).on( 'blur', function() {
			var query = [];

			var $location = $( '#acadp_location' ).find( 'option:selected' );
			var location = $location.text();
			if ( location ) {   
				if ( ! $location.hasClass( 'level-0' ) ) {
					query.push( $location.prev( '.level-0' ).text() );
				}

				query.push( location.trim() );
			}

			var zipcode = $( '#acadp-zipcode' ).val();
			if ( zipcode ) {
				query.push( zipcode );
			}

			if ( 0 == query.length ) {
				var address = $( '#acadp-address' ).val();
				if ( address ) {
					query.push( address );
				}
			}

			query = query.join();
			
			$.get( 'https://nominatim.openstreetmap.org/search.php?q=' + encodeURIComponent( query ) +'&polygon_geojson=1&format=jsonv2', function( response ) {
				if ( response.length > 0 ) {
					var latlng = new L.LatLng( response[0].lat, response[0].lon );

					marker.setLatLng( latlng );
					map.panTo( latlng );
					acadp_update_latlng( response[0].lat, response[0].lon );
				}
			}, 'json');
		});

		if ( acadp_is_empty( lat ) ) {
			$( '#acadp-address' ).trigger( 'blur' );
		}
	}

	/**
     * [Map: Google] Render a Map onto the selected jQuery element.
     *
	 * @since 1.0.0
	 */
	function acadp_google_render_map( $el ) {
		// var
		var $marker = $el.find( '.marker' );

		// vars
		var args = {
			zoom: parseInt( acadp.zoom_level ),
			center: new google.maps.LatLng(0, 0),
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			scrollwheel: false
		};

		// create map	        	
		var map = new google.maps.Map( $el[0], args );

		// add a markers reference
		map.marker = null;
	
		// add marker
		acadp_google_add_marker( $marker, map );

		// center map
		acadp_google_center_map( map );
		
		// Update map when contact details fields are updated in the custom post type "acadp_listings"
		$ ( '.acadp-map-field', '#acadp-contact-details' ).on( 'blur', function() {
			var address = [];
			address.push( $( '#acadp-address' ).val() );
			address.push( $( '#acadp_location' ).find('option:selected').text() );
			address.push( $( '#acadp-zipcode' ).val() );			
			address = address.filter( function( v ) { return v !== '' } );
			address = address.join();

			var geoCoder = new google.maps.Geocoder();
		
			geoCoder.geocode({ 'address': address }, function( results, status ) {															
				if ( status == google.maps.GeocoderStatus.OK ) {					
					var point = results[0].geometry.location;									
					map.marker.setPosition( point );
					acadp_google_center_map( map );
					acadp_update_latlng( point.lat(), point.lng() );				
				};				
			});
		});

		if ( acadp_is_empty( $( '#acadp-latitude' ).val() ) ) {
			$( '#acadp-address' ).trigger( 'blur' );
		}
	};	
	
	/**
	 * [Map: Google] Add a marker to the selected Map.
	 *
	 * @since 1.0.0
	 */
	function acadp_google_add_marker( $marker, map ) {
		var lat = $( '#acadp-latitude' ).val();
		var lng = $( '#acadp-longitude' ).val();
		
		var latlng = new google.maps.LatLng( lat, lng );

		// create marker
		var marker = new google.maps.Marker({
			position: latlng,
			map: map,
			draggable: true
		});

		// add to array
		map.marker = marker;
	
		// if marker contains HTML, add it to an infoWindow
		if ( $marker.html() ) {
			// create info window
			var infowindow = new google.maps.InfoWindow({
				content: $marker.html()
			});

			// show info window when marker is clicked
			google.maps.event.addListener( marker, 'click', function() {	
				infowindow.open( map, marker );
			});
		};
		
		// update latitude and longitude values in the form when marker is moved
		google.maps.event.addListener( marker, 'dragend', function() {																  
  			var point = marker.getPosition();
			map.panTo( point );
			acadp_update_latlng( point.lat(), point.lng() );			
		});
	};	

	/**
	 * [Map: Google] Center the map, showing all markers attached to this map.
     *
	 * @since 1.0.0
	 */
	function acadp_google_center_map( map ) {
		// vars
		var bounds = new google.maps.LatLngBounds();

		// create bounds
		if ( map.marker != null ) {			
			var latlng = new google.maps.LatLng( map.marker.position.lat(), map.marker.position.lng() );		
			bounds.extend( latlng );			
		}

		map.setCenter( bounds.getCenter() );
	    map.setZoom( parseInt( acadp.zoom_level ) );
	};	
	
	/**
	 * Update the latitude and logitude values in the custom post type "acadp_listings".
     *
	 * @since 1.0.0
	 */
	function acadp_update_latlng( lat, lng ) {		
		$( '#acadp-latitude' ).val( lat );
		$( '#acadp-longitude' ).val( lng );			
	};
	
	/**
	 *  Make images inside the listing form sortable.
     *
	 *  @since 1.0.0
	 */
	function acadp_sort_images() {		
		var $sortable_element = $('#acadp-images tbody');
			
		if ( $sortable_element.hasClass( 'ui-sortable' ) ) {
			$sortable_element.sortable( 'destroy' );
		};
			
		$sortable_element.sortable({
			handle: '.acadp-handle'
		});
		
		$sortable_element.disableSelection();
	};	
	
	/**
 	 * Display the media uploader for selecting an image.
 	 *
 	 * @since 1.0.0
 	 */
	function acadp_render_media_uploader( page ) { 
    	var file_frame, image_data, json;
 
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
 
     	// Setup an event handler for what to do when an image has been selected
    	file_frame.on( 'insert', function() { 
        	// Read the JSON data returned from the media uploader
    		json = file_frame.state().get( 'selection' ).first().toJSON();
		
			// First, make sure that we have the URL of an image to display
    		if ( 0 > $.trim( json.url.length ) ) {
        		return;
    		};
		
			// After that, set the properties of the image and display it
			if ( 'listings' == page ) {				
				var html = '<tr class="acadp-image-row">' + 
					'<td class="acadp-handle"><span class="dashicons dashicons-screenoptions"></span></td>' +          	
					'<td class="acadp-image">' + 
						'<img src="' + json.url + '" />' + 
						'<input type="hidden" name="images[]" value="' + json.id + '" />' + 
					'</td>' + 
					'<td>' + 
						json.url + '<br />' + 
						'<a href="post.php?post=' + json.id + '&action=edit" target="_blank">' + acadp.edit + '</a> | ' + 
						'<a href="javascript:;" class="acadp-delete-image" data-attachment_id="' + json.id + '">' + acadp.delete_permanently + '</a>' + 
					'</td>' +                 
				'</tr>';
			
				$( '#acadp-images' ).append( html );
				
				acadp_sort_images();			
			} else {				
				$( '#acadp-categories-image-id' ).val( json.id );
				$( '#acadp-categories-image-wrapper' ).html( '<img src="' + json.url + '" />' );
				
				$( '#acadp-categories-upload-image' ).hide();
				$( '#acadp-categories-remove-image' ).show();
			} 
    	});
 
    	// Now display the actual file_frame
    	file_frame.open(); 
	};
	
	/**
 	 * Close the popup.
 	 *
 	 * @since 1.7.3
 	 */
	 function acadp_modal_hide() {		
		$( '.acadp-modal' ).hide();
		$( 'html' ).removeClass( 'acadp-no-scroll' );
	}

	/**
 	 * Check if value is empty.
 	 *
 	 * @since 1.8.0
 	 */
	 function acadp_is_empty( value ) {		
		if ( '' == value || 0 == value || null == value ) {
			return true;
		}

		return false;
	}

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.0.0
	 */
	$(function() {
		// Dashboard: Initiate color picker
		if ( $.fn.wpColorPicker ) {
			$( '.acadp-color-picker', '#acadp-dashboard' ).wpColorPicker();
		}

		// Dashboard: On shortcode type changed
		$( 'input[type=radio]', '#acadp-shortcode-selector' ).on( 'change', function( e ) {
			var shortcode = $( 'input[type=radio]:checked', '#acadp-shortcode-selector' ).val();

			$( '.acadp-shortcode-form' ).hide();
			$( '#acadp-shortcode-form-' + shortcode ).show();
		}).trigger( 'change' );

		// Dashboard: Toggle between field sections
		$( document ).on( 'click', '.acadp-shortcode-section-header', function( e ) {
			var $elem = $( this ).parent();

			if ( ! $elem.hasClass( 'acadp-active' ) ) {
				$( this ).closest( '.acadp-shortcode-form' )
					.find( '.acadp-shortcode-section.acadp-active' )
					.toggleClass( 'acadp-active' )
					.find( '.acadp-shortcode-controls' )
					.slideToggle();
			}			

			$elem.toggleClass( 'acadp-active' )
				.find( '.acadp-shortcode-controls' )
				.slideToggle();
		});

		// Dashboard: Generate shortcode
		$( '#acadp-generate-shortcode' ).on( 'click', function( e ) { 
			e.preventDefault();			

			// Shortcode
			var shortcode = $( 'input[type=radio]:checked', '#acadp-shortcode-selector' ).val();

			// Attributes
			var props = {};
			
			$( '.acadp-shortcode-field', '#acadp-shortcode-form-' + shortcode ).each(function() {							
				var $this = $( this );
				var type  = $this.attr( 'type' );
				var key   = $this.attr( 'name' );				
				var value = $this.val();				
				var def   = 0;	
				
				if ( 'undefined' !== typeof $this.data( 'default' ) ) {
					def = $this.data( 'default' );
				}
				
				// field type = checkbox
				if ( 'checkbox' == type ) {
					value = $this.is( ':checked' ) ? 1 : 0;
				}
				
				// Add only if the user input differ from the global configuration
				if ( value != def ) {
					props[ key ] = value;
				}				
			});

			var attrs = shortcode;
			for ( var key in props ) {
				if ( props.hasOwnProperty( key ) ) {
					attrs += ( ' ' + key + '="' + props[ key ] + '"' );
				}
			}

			// Shortcode output		
			$( '#acadp-shortcode').val( '[acadp_' + attrs + ']' ); 

			// Initialize the popup
			$( 'html' ).addClass( 'acadp-no-scroll' );
			$( '#acadp-shortcode-modal' ).show();
		});

		// Dashboard: Close the shortcode builder popup
		$( '.acadp-modal-close' ).on( 'click', function( e ) {		
			e.preventDefault();
			acadp_modal_hide();			
		});	
		
		$( '.acadp-modal-content' ).on( 'click', function( e ) {		
			if ( $( e.target ).hasClass( 'acadp-modal-content' ) ) {
				acadp_modal_hide();
			};			
		});

		// Dashboard: Check/Uncheck all checkboxes in the issues table list.
		$( '#acadp-check-all' ).on( 'change', function( e ) {
			var value = $( this ).is( ':checked' ) ? 1 : 0;	

			if ( value ) {
				$( 'tbody', '#acadp-issues' ).find( 'input[type=checkbox]' ).prop( 'checked', true );
			} else {
				$( 'tbody', '#acadp-issues' ).find( 'input[type=checkbox]' ).prop( 'checked', false );
			}
		});	

		// Dashboard: Validate the issues form.
		$( 'form', '#acadp-issues' ).submit(function() {
			var has_input = 0;

			$( 'tbody', '#acadp-issues' ).find( 'input[type="checkbox"]:checked' ).each(function() {
				has_input = 1;
			});

			if ( ! has_input ) {
				alert( acadp.i18n.no_issues_slected );
				return false;
			}			
		});
		
		// Show or hide field options when field type changed in the custom post type "acadp_fields"
		$( '.field-type select', '#acadp-field-details' ).on( 'change', function() {								
			var num_fields = $( '.field-options' ).length;
			var option = $( this ).val();
			$( '.field-options', '#acadp-field-details' ).fadeOut( 200, function() {
				if ( --num_fields > 0 ) return;
				$( '.field-option-' + option, '#acadp-field-details' ).fadeIn( 200 );
			});			
		}).change();
		
		// Load custom fields of the selected category in the custom post type "acadp_listings"
		$( '#acadp_category' ).on( 'change', function() {								
			$( '#acadp-custom-fields-list' ).html( '<div class="spinner"></div>' );
			
			var data = {
				'action': 'acadp_custom_fields_listings',
				'post_id': $( '#acadp-custom-fields-list' ).data( 'post_id' ),
				'terms': $( this ).val(),
				'security': acadp.ajax_nonce
			};
			
			$.post( ajaxurl, data, function( response ) {
				$( '#acadp-custom-fields-list' ).html( response );
			});			
		});		
		
		// Render map in the custom post type "acadp_listings"	
		$( '.acadp-map' ).each(function() {
			if ( 'osm' == acadp.map_service ) {
				acadp_osm_render_map( $( this ) );
			} else {
				acadp_google_render_map( $( this ) );
			}									  
		});		
		
		// Display the media uploader when "Upload Image" button clicked in the custom post type "acadp_listings"		
		$( '#acadp-upload-image' ).on( 'click', function( e ) { 
            e.preventDefault(); 
            acadp_render_media_uploader( 'listings' ); 
        });
		
		// Make the isting images sortable in the custom post type "acadp_listings"
		acadp_sort_images();
		
		// Delete the selected image when "Delete Permanently" button clicked in the custom post type "acadp_listings"	
		$( '#acadp-images' ).on( 'click', 'a.acadp-delete-image', function( e ) {														 
            e.preventDefault();
								
			var $this = $( this );
			
			var data = {
				'action': 'acadp_delete_attachment',
				'attachment_id': $this.data('attachment_id'),
				'security': acadp.ajax_nonce
			};
			
			$.post( ajaxurl, data, function(response) {
				$this.closest( 'tr' ).remove();
			});			
		});
		
		// Display the media uploader when "Upload Image" button clicked in the custom taxonomy "acadp_categories"		
		$( '#acadp-categories-upload-image' ).on( 'click', function( e ) { 
            e.preventDefault(); 
            acadp_render_media_uploader( 'categories' ); 
        });
		
		// Delete the image when "Remove Image" button clicked in the custom taxonomy "acadp_categories"	
		$( '#acadp-categories-remove-image' ).on( 'click', function( e ) {														 
            e.preventDefault();
			
			var id = parseInt( $( '#acadp-categories-image-id' ).val() );			
			if ( id > 0 ) {				
				var data = {
					'action': 'acadp_delete_attachment',
					'attachment_id': id,
					'security': acadp.ajax_nonce
				};
				
				$.post( ajaxurl, data, function(response) {
					$( '#acadp-categories-image-id' ).val( '' );
					$( '#acadp-categories-image-wrapper' ).html( '' );

					$( '#acadp-categories-remove-image' ).hide();
					$( '#acadp-categories-upload-image' ).show();
				});				
			};			
		});
		
		// Clear the image field after the custom taxonomy "acadp_categories" term was created.
		$( document ).ajaxComplete(function( event, xhr, settings ) {			
			if ( $( "#acadp-categories-image-id" ).length ) {				
				var queryStringArr = settings.data.split( '&' );
			   
				if ( $.inArray( 'action=add-tag', queryStringArr ) !== -1 ) {
					var xml = xhr.responseXML;
					var response = $( xml ).find( 'term_id' ).text();
					if ( '' != response ) {
						$( '#acadp-categories-image-id' ).val( '' );
						$( '#acadp-categories-image-wrapper' ).html( '' );

						$( '#acadp-categories-remove-image' ).hide();
						$( '#acadp-categories-upload-image' ).show();
					};
				};			
			};			
		});	
	});
})( jQuery );
