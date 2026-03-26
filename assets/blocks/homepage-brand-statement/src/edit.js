import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, TextareaControl } from '@wordpress/components';

const LOGO_TEMPLATE = [
	[ 'core/image', { className: 'brand-statement-logo', alt: '' } ],
];

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps( {
		className: 'homepage-brand-statement-editor',
	} );
	const { statementText } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title="Brand Statement" initialOpen={ true }>
					<TextareaControl
						label="Statement Text"
						value={ statementText }
						onChange={ ( val ) => setAttributes( { statementText: val } ) }
						rows={ 6 }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="homepage-brand-statement-editor__logo">
					<p className="homepage-brand-statement-editor__logo-label">Logo SVG</p>
					<InnerBlocks
						template={ LOGO_TEMPLATE }
						templateLock={ false }
					/>
				</div>
				<p className="homepage-brand-statement-editor__text">
					{ statementText }
				</p>
			</div>
		</>
	);
}
