(function( $ ) {
	'use strict';

	/**
	 * Called when the page has loaded.
	 *
	 * @since 1.6.4
	 */
	$(function() {

		// Render slick carousel
		$( '.acadp-slick' ).each(function() {		
			var style_prev_arrow  = $( this ).data( 'style_prev_arrow' ),
				style_next_arrow  = $( this ).data( 'style_next_arrow' ),
				style_arrow_icon  = $( this ).data( 'style_arrow_icon' ),
				style_dots        = $( this ).data( 'style_dots' ),
				style_dots_active = $( this ).data( 'style_dots_active' );

			$( this ).on( 'init', function( slick ) {
            	$( this ).fadeIn( 1000 );
			}).slick({
				rtl: ( parseInt( acadp.is_rtl ) ? true : false ),
				lazyLoad: 'ondemand',
				nextArrow: '<div class="acadp-slick-next" style="' + style_next_arrow + '"><span class="glyphicon glyphicon-menu-right" aria-hidden="true" style="' + style_arrow_icon + '"></span></div>',
				prevArrow: '<div class="acadp-slick-prev" style="' + style_prev_arrow + '"><span class="glyphicon glyphicon-menu-left" aria-hidden="true" style="' + style_arrow_icon + '"></span></div>',
				dotsClass: 'acadp-slick-dots',
				customPaging: function( slider, i ) {					
        			return '<span class="acadp-slick-dot" style="' + style_dots + '"><span class="acadp-slick-dot-active" style="' + style_dots_active + '"></span></span>';
    			}
			});			
		});
				
	});

})( jQuery );