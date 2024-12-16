import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType( 'wcrp-rental-products/availability-checker', {
	title: __( 'Availability Checker', 'wcrp-rental-products' ),
	icon: 'calendar-alt',
	category: 'wcrp-rental-products',
	edit: function() {
		const blockProps = useBlockProps( {
			className: 'wp-block',
		} );
		return (
			<div { ...blockProps }><strong>{ __( 'Availability checker for rentals appears here', 'wcrp-rental-products' ) }</strong></div>
		);
	},
	save: () => {
		return null;
	},
});
