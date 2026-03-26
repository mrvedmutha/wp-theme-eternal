const { registerBlockType } = (wp).blocks;
const { __ } = (wp).i18n;
const { InnerBlocks } = (wp).blockEditor;
import Edit from './edit';

registerBlockType( 'wp-rig/homepage-brand-statement', {
	apiVersion: 2,
	title: __( 'Homepage Brand Statement', 'wp-rig' ),
	edit: Edit,
	save() {
		// InnerBlocks.Content serialises the logo block(s) into the DB
		// so render.php receives them as $content.
		return <InnerBlocks.Content />;
	},
} );
