/**
 * BLOCK: Advanced Classifieds and Directory Pro Listings.
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
registerBlockType( 'acadp/listings', {
	title: __( 'ACADP - Listings' ),
	description: __( 'Display a list of ACADP Listings.' ),
	icon: 'welcome-widgets-menus',
	category: 'advanced-classifieds-and-directory-pro',
	keywords: [
		__( 'classifieds' ),
		__( 'listings' ),		
		__( 'directory' )
	],
	attributes: {
		view: {
			type: 'string',
			default: acadp.listings.view
		},
		category: {
			type: 'number',
			default: acadp.listings.category
		},
		location: {
			type: 'number',
			default: acadp.listings.location
		},		
		filterby: {
			type: 'string',
			default: acadp.listings.filterby
		},
		orderby: {
			type: 'string',
			default: acadp.listings.orderby
		},
		order: {
			type: 'string',
			default: acadp.listings.order
		},
		columns: {
			type: 'number',
			default: acadp.listings.columns
		},
		listings_per_page: {
			type: 'number',
			default: acadp.listings.listings_per_page
		},
		featured: {
			type: 'boolean',
			default: acadp.listings.featured
		},		
		header: {
			type: 'boolean',
			default: acadp.listings.header
		},
		pagination: {
			type: 'boolean',
			default: acadp.listings.pagination
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
