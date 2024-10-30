/**
 * BLOCK: instagram: Instagram for Gutenberg
 */

//  Import CSS.
import './style.scss';
import './editor.scss';

import { attributes } from './attributes';
import { default as edit } from './edit';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

import icons from './icons';

/**
 * Register: aa Gutenberg Block.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'instagram/instagram', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Instagram for Gutenberg' ), // Block title.
	icon: icons.instagram, // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [ __( 'instagram' ) ],
	attributes,

	/**
	 * Edit
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	edit,
	/**
	 * Save
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 */
	save( { attributes, className } ) {
		//gutenberg will save attributes we can use in server-side callback
		return null;
	},
} );
