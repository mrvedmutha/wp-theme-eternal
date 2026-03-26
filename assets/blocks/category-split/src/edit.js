import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

function PanelControls( { label, imageId, imageUrl, name, subtitle, discoverUrl, onImageSelect, onImageRemove, onNameChange, onSubtitleChange, onUrlChange } ) {
	return (
		<PanelBody title={ label } initialOpen={ true }>
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ onImageSelect }
					allowedTypes={ [ 'image' ] }
					value={ imageId }
					render={ ( { open } ) => (
						<div style={ { marginBottom: '16px' } }>
							{ imageUrl && (
								<img
									src={ imageUrl }
									alt=""
									style={ { width: '100%', marginBottom: '8px' } }
								/>
							) }
							<Button variant="secondary" onClick={ open }>
								{ imageId ? 'Replace Image' : 'Select Image' }
							</Button>
							{ imageId && (
								<Button
									variant="link"
									isDestructive
									onClick={ onImageRemove }
									style={ { marginLeft: '8px' } }
								>
									Remove
								</Button>
							) }
						</div>
					) }
				/>
			</MediaUploadCheck>
			<TextControl
				label="Category Name"
				value={ name }
				onChange={ onNameChange }
			/>
			<TextControl
				label="Subtitle"
				value={ subtitle }
				onChange={ onSubtitleChange }
			/>
			<TextControl
				label="Discover URL"
				value={ discoverUrl }
				onChange={ onUrlChange }
			/>
		</PanelBody>
	);
}

export default function Edit( { name, attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const {
		panel1ImageId,
		panel1ImageUrl,
		panel1Name,
		panel1Subtitle,
		panel1DiscoverUrl,
		panel2ImageId,
		panel2ImageUrl,
		panel2Name,
		panel2Subtitle,
		panel2DiscoverUrl,
	} = attributes;

	return (
		<>
			<InspectorControls>
				<PanelControls
					label="Panel 1"
					imageId={ panel1ImageId }
					imageUrl={ panel1ImageUrl }
					name={ panel1Name }
					subtitle={ panel1Subtitle }
					discoverUrl={ panel1DiscoverUrl }
					onImageSelect={ ( media ) => setAttributes( { panel1ImageId: media.id, panel1ImageUrl: media.url } ) }
					onImageRemove={ () => setAttributes( { panel1ImageId: 0, panel1ImageUrl: '' } ) }
					onNameChange={ ( val ) => setAttributes( { panel1Name: val } ) }
					onSubtitleChange={ ( val ) => setAttributes( { panel1Subtitle: val } ) }
					onUrlChange={ ( val ) => setAttributes( { panel1DiscoverUrl: val } ) }
				/>
				<PanelControls
					label="Panel 2"
					imageId={ panel2ImageId }
					imageUrl={ panel2ImageUrl }
					name={ panel2Name }
					subtitle={ panel2Subtitle }
					discoverUrl={ panel2DiscoverUrl }
					onImageSelect={ ( media ) => setAttributes( { panel2ImageId: media.id, panel2ImageUrl: media.url } ) }
					onImageRemove={ () => setAttributes( { panel2ImageId: 0, panel2ImageUrl: '' } ) }
					onNameChange={ ( val ) => setAttributes( { panel2Name: val } ) }
					onSubtitleChange={ ( val ) => setAttributes( { panel2Subtitle: val } ) }
					onUrlChange={ ( val ) => setAttributes( { panel2DiscoverUrl: val } ) }
				/>
			</InspectorControls>

			<div { ...blockProps }>
				<ServerSideRender block={ name } attributes={ attributes } />
			</div>
		</>
	);
}
