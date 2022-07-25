/**
 * BLOCK: Advanced Classifieds and Directory Pro Listing Form.
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
registerBlockType( 'acadp/listing-form', {
	title: __( 'ACADP - Listing Form' ),
	description: __( 'Display a Listing submission form.' ),
	icon: 'edit',
	category: 'advanced-classifieds-and-directory-pro',
	keywords: [
		__( 'classifieds' ),
		__( 'listings' ),		
		__( 'directory' )
	],
	supports: {
		customClassName: false,
	},

	edit,

	// Render via PHP
	save: function( props ) {
		return null;
	},
} );
