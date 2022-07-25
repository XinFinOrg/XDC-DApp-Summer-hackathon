/**
 * BLOCK: Advanced Classifieds and Directory Pro Search Form.
 */

// Import block dependencies and components
import edit from './edit';

// Components
const { __ } = wp.i18n;

const { registerBlockType } = wp.blocks;

/**
 * Register the block.
 *
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'acadp/search-form', {
	title: __( 'ACADP - Search Form' ),
	description: __( 'Display a ACADP Listings Search Form.' ),
	icon: 'search',
	category: 'advanced-classifieds-and-directory-pro',
	keywords: [
		__( 'classifieds' ),
		__( 'listings' ),		
		__( 'directory' )
	],
	attributes: {
		style: {
			type: 'string',
			default: acadp.search_form.style
		},
		location: {
			type: 'boolean',
			default: acadp.search_form.location
		},
		category: {
			type: 'boolean',
			default: acadp.search_form.category
		},
		custom_fields: {
			type: 'boolean',
			default: acadp.search_form.custom_fields
		},
		price: {
			type: 'boolean',
			default: acadp.search_form.price
		},
	},
	supports: {
		customClassName: false,
	},

	edit,

	// Render via PHP
	save: function( props ) {
		return null;
	},
} );
