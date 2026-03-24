<?php
/**
 * WP_Rig\WP_Rig\Homepage_Hero\Component class
 *
 * Registers the homepage hero JS (GSAP animations) on the front page only.
 *
 * @package wp_rig
 *
 * @js-file assets/js/src/homepage-hero.js  Entrance reveal + CTA underline hover animations
 */

namespace WP_Rig\WP_Rig\Homepage_Hero;

use WP_Rig\WP_Rig\Component_Interface;
use function add_filter;
use function is_front_page;

/**
 * Class for Homepage Hero component.
 */
class Component implements Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug(): string {
		return 'homepage-hero';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize(): void {
		add_filter( 'wp_rig_js_files', array( $this, 'filter_register_script' ) );
	}

	/**
	 * Registers the homepage hero animation script.
	 * Only enqueued on the front page to avoid loading GSAP animations elsewhere.
	 *
	 * @param array $js_files Existing JS files array.
	 * @return array
	 */
	public function filter_register_script( array $js_files ): array {
		if ( ! is_front_page() ) {
			return $js_files;
		}

		$js_files['wp-rig-homepage-hero'] = array(
			'file'    => 'homepage-hero.min.js',
			'global'  => true,
			'footer'  => true,
			'loading' => 'defer',
			'deps'    => array(),
		);

		return $js_files;
	}
}
