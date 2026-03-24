<?php
/**
 * WP_Rig\WP_Rig\Homepage_Hero\Component class
 *
 * Enqueues the GSAP animation script on any page that contains
 * the wp-rig/homepage-hero block.
 *
 * @package wp_rig
 *
 * @js-file assets/js/src/homepage-hero.js  Entrance reveal + CTA underline hover
 */

namespace WP_Rig\WP_Rig\Homepage_Hero;

use WP_Rig\WP_Rig\Component_Interface;
use function WP_Rig\WP_Rig\wp_rig;
use function add_action;
use function has_block;
use function wp_enqueue_script;
use function get_theme_file_uri;
use function get_theme_file_path;

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
		add_action( 'wp_enqueue_scripts', array( $this, 'action_enqueue_script' ) );
	}

	/**
	 * Enqueues the homepage hero animation script only on pages
	 * that contain the wp-rig/homepage-hero block.
	 */
	public function action_enqueue_script(): void {
		global $post;

		if ( ! $post || ! has_block( 'wp-rig/homepage-hero', $post ) ) {
			return;
		}

		$js_path = get_theme_file_path( '/assets/js/homepage-hero.min.js' );
		$js_uri  = get_theme_file_uri( '/assets/js/homepage-hero.min.js' );

		wp_enqueue_script(
			'wp-rig-homepage-hero',
			$js_uri,
			array(),
			wp_rig()->get_asset_version( $js_path ),
			true
		);
	}
}
