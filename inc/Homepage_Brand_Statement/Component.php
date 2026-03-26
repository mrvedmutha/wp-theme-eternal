<?php
/**
 * WP_Rig\WP_Rig\Homepage_Brand_Statement\Component class
 *
 * Placeholder component for the wp-rig/homepage-brand-statement block.
 * The block's viewScript (build/view.js) is auto-enqueued by WordPress
 * when the block is present on a page — no manual enqueueing needed.
 *
 * @package wp_rig
 *
 * @js-file assets/blocks/homepage-brand-statement/src/view.js  GSAP one-shot word reveal
 */

namespace WP_Rig\WP_Rig\Homepage_Brand_Statement;

use WP_Rig\WP_Rig\Component_Interface;

/**
 * Class for Homepage Brand Statement component.
 */
class Component implements Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug(): string {
		return 'homepage-brand-statement';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize(): void {
		// Block registration is handled by inc/Blocks/Component.php.
		// JS is auto-loaded via viewScript in block.json.
	}
}
