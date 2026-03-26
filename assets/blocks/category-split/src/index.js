// Use WP globals
// eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
const { registerBlockType } = (wp).blocks;
// eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
const { __ } = (wp).i18n;
import Edit from './edit';

registerBlockType('wp-rig/category-split', {
	apiVersion: 2,
	title: __('Category Split', 'wp-rig'),
	edit: Edit,
	save() {
		return null;
	},
});
