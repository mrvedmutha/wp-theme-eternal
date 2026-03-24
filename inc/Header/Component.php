<?php
/**
 * WP_Rig\WP_Rig\Header\Component class
 *
 * Manages the Eternal Labs header: three-state behaviour (transparent / sticky /
 * solid), two-layer header-style resolution, native meta box override, and
 * WooCommerce cart-count AJAX fragments.
 *
 * @package wp_rig
 *
 * @css-file assets/css/src/_header-eternal.css   All header states and animations
 * @js-file  assets/js/src/header.js              GSAP scroll logic + currency toggle
 */

namespace WP_Rig\WP_Rig\Header;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function WP_Rig\WP_Rig\wp_rig;
use function add_action;
use function add_filter;
use function is_front_page;
use function is_page;
use function get_page_template_slug;
use function get_post_meta;
use function update_post_meta;
use function get_queried_object_id;
use function wp_nonce_field;
use function wp_verify_nonce;
use function current_user_can;
use function sanitize_text_field;
use function get_post_type;

/**
 * Header component.
 *
 * Exposes template tags:
 * * `wp_rig()->is_transparent_header()`
 * * `wp_rig()->get_header_cart_count()`
 */
class Component implements Component_Interface, Templating_Component_Interface {

	/**
	 * Post meta key for per-page header style override.
	 */
	const META_KEY = '_header_style';

	/**
	 * Body class added when transparent header is active.
	 */
	const TRANSPARENT_CLASS = 'has-transparent-header';

	// -------------------------------------------------------------------------
	// Component_Interface
	// -------------------------------------------------------------------------

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug(): string {
		return 'header';
	}

	/**
	 * Adds action and filter hooks.
	 */
	public function initialize(): void {
		add_filter( 'body_class', array( $this, 'filter_body_class' ) );
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_box' ) );
		add_action( 'save_post', array( $this, 'action_save_meta' ) );
		add_filter( 'wp_rig_js_files', array( $this, 'filter_register_script' ) );

		// WooCommerce cart fragment so count updates without page reload.
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'filter_cart_count_fragment' ) );
	}

	// -------------------------------------------------------------------------
	// Templating_Component_Interface
	// -------------------------------------------------------------------------

	/**
	 * Exposes template tags via wp_rig().
	 *
	 * @return array
	 */
	public function template_tags(): array {
		return array(
			'is_transparent_header'  => array( $this, 'is_transparent_header' ),
			'get_header_cart_count'  => array( $this, 'get_header_cart_count' ),
		);
	}

	// -------------------------------------------------------------------------
	// Two-layer header style resolution
	// -------------------------------------------------------------------------

	/**
	 * Determines whether the current page should use the transparent header.
	 *
	 * Layer 1: per-page meta box override (_header_style = transparent|solid|auto).
	 * Layer 2: theme-level route rules defined in get_transparent_routes().
	 *
	 * @return bool
	 */
	public function is_transparent_header(): bool {
		$post_id  = get_queried_object_id();
		$override = $post_id ? get_post_meta( $post_id, self::META_KEY, true ) : 'auto';

		if ( 'transparent' === $override ) {
			return true;
		}

		if ( 'solid' === $override ) {
			return false;
		}

		// Layer 2 — theme route rules.
		return $this->matches_transparent_route();
	}

	/**
	 * Theme-level route rules for transparent header.
	 * Add slugs, templates, post types, or WP conditional tags here.
	 * No admin UI needed — just edit this array.
	 *
	 * @return bool True if the current request matches a transparent route.
	 */
	private function matches_transparent_route(): bool {
		$routes = $this->get_transparent_routes();

		// WordPress conditional tags (string function names).
		foreach ( $routes['conditionals'] as $conditional ) {
			if ( function_exists( $conditional ) && call_user_func( $conditional ) ) {
				return true;
			}
		}

		// Page slugs.
		if ( ! empty( $routes['slugs'] ) && is_page( $routes['slugs'] ) ) {
			return true;
		}

		// Page templates.
		if ( ! empty( $routes['templates'] ) ) {
			$current_template = get_page_template_slug();
			if ( $current_template && in_array( $current_template, $routes['templates'], true ) ) {
				return true;
			}
		}

		// Post types.
		if ( ! empty( $routes['post_types'] ) ) {
			$current_post_type = get_post_type();
			if ( $current_post_type && in_array( $current_post_type, $routes['post_types'], true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Defines which pages automatically receive the transparent header.
	 *
	 * To add a new transparent route:
	 *   - 'conditionals' → WordPress conditional tag name string, e.g. 'is_front_page'
	 *   - 'slugs'        → page slug string or array of slugs
	 *   - 'templates'    → page template filename, e.g. 'template-hero.php'
	 *   - 'post_types'   → post type slug, e.g. 'product'
	 *
	 * @return array
	 */
	private function get_transparent_routes(): array {
		return array(
			'conditionals' => array(
				'is_front_page',
			),
			'slugs'        => array(
				// Add page slugs here, e.g. 'about', 'skincare'.
			),
			'templates'    => array(
				// Add page template filenames here, e.g. 'template-hero.php'.
			),
			'post_types'   => array(
				// Add post type slugs here, e.g. 'product'.
			),
		);
	}

	// -------------------------------------------------------------------------
	// Body class
	// -------------------------------------------------------------------------

	/**
	 * Adds has-transparent-header to <body> when applicable.
	 *
	 * @param array $classes Existing body classes.
	 * @return array
	 */
	public function filter_body_class( array $classes ): array {
		if ( $this->is_transparent_header() ) {
			$classes[] = self::TRANSPARENT_CLASS;
		}
		return $classes;
	}

	// -------------------------------------------------------------------------
	// Meta box (per-page override)
	// -------------------------------------------------------------------------

	/**
	 * Registers the Header Style meta box on all public post types.
	 */
	public function action_add_meta_box(): void {
		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'eternal_header_style',
				__( 'Header Style', 'wp-rig' ),
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Renders the Header Style meta box HTML.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render_meta_box( \WP_Post $post ): void {
		$value = get_post_meta( $post->ID, self::META_KEY, true );
		$value = $value ? $value : 'auto';
		wp_nonce_field( 'eternal_header_style_nonce', 'eternal_header_style_nonce' );
		$options = array(
			'auto'        => __( 'Auto (follow theme rules)', 'wp-rig' ),
			'transparent' => __( 'Force Transparent', 'wp-rig' ),
			'solid'       => __( 'Force Solid', 'wp-rig' ),
		);
		echo '<fieldset style="margin:0;padding:0;border:0;">';
		foreach ( $options as $option_value => $option_label ) {
			printf(
				'<label style="display:block;margin-bottom:6px;cursor:pointer;">'
				. '<input type="radio" name="%1$s" value="%2$s" %3$s style="margin-right:6px;"> %4$s'
				. '</label>',
				esc_attr( self::META_KEY ),
				esc_attr( $option_value ),
				checked( $value, $option_value, false ),
				esc_html( $option_label )
			);
		}
		echo '</fieldset>';
		echo '<p style="margin:8px 0 0;font-size:11px;color:#757575;">'
			. esc_html__( 'Transparent overlays a hero image. Solid sits above content.', 'wp-rig' )
			. '</p>';
	}

	/**
	 * Saves the Header Style meta value on post save.
	 *
	 * @param int $post_id Post ID being saved.
	 */
	public function action_save_meta( int $post_id ): void {
		// Verify nonce.
		if (
			! isset( $_POST['eternal_header_style_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['eternal_header_style_nonce'] ) ), 'eternal_header_style_nonce' )
		) {
			return;
		}

		// Skip auto-saves and permission checks.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$allowed = array( 'auto', 'transparent', 'solid' );
		$value   = isset( $_POST[ self::META_KEY ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::META_KEY ] ) ) : 'auto';

		if ( in_array( $value, $allowed, true ) ) {
			update_post_meta( $post_id, self::META_KEY, $value );
		}
	}

	// -------------------------------------------------------------------------
	// WooCommerce cart count
	// -------------------------------------------------------------------------

	/**
	 * Returns the current WooCommerce cart item count.
	 * Safe to call even when WooCommerce is inactive.
	 *
	 * @return int
	 */
	public function get_header_cart_count(): int {
		if ( function_exists( 'WC' ) && WC()->cart ) {
			return (int) WC()->cart->get_cart_contents_count();
		}
		return 0;
	}

	/**
	 * Updates the cart count span via WooCommerce AJAX fragments.
	 * WooCommerce calls this after add-to-cart, ensuring the count
	 * updates without a page reload.
	 *
	 * @param array $fragments Existing WC fragments.
	 * @return array
	 */
	public function filter_cart_count_fragment( array $fragments ): array {
		$count = $this->get_header_cart_count();
		ob_start();
		?>
		<span class="header-cart__count" <?php /* translators: %d = number of items in cart */ ?>aria-label="<?php echo esc_attr( sprintf( __( '%d items in cart', 'wp-rig' ), $count ) ); ?>">
			<?php echo esc_html( $count ); ?>
		</span>
		<?php
		$fragments['span.header-cart__count'] = ob_get_clean();
		return $fragments;
	}

	// -------------------------------------------------------------------------
	// Script registration
	// -------------------------------------------------------------------------

	/**
	 * Adds the header script to the WP Rig JS files list.
	 *
	 * @param array $js_files Existing JS files array.
	 * @return array
	 */
	public function filter_register_script( array $js_files ): array {
		$js_files['wp-rig-header'] = array(
			'file'    => 'header.min.js',
			'global'  => true,
			'footer'  => true,
			'loading' => 'defer',
			'deps'    => array(),
		);
		return $js_files;
	}
}
