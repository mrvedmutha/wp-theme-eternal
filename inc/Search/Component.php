<?php
/**
 * WP_Rig\WP_Rig\Search\Component class
 *
 * Provides the search megamenu feature: REST endpoint for live product search,
 * recommended products query, and JS/CSS asset registration.
 *
 * @package wp_rig
 *
 * @js-file  assets/js/src/search-megamenu.js  Search megamenu open/close, debounced fetch, card render
 */

namespace WP_Rig\WP_Rig\Search;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_Query;
use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;

use function add_action;
use function add_filter;
use function register_rest_route;
use function rest_url;
use function home_url;
use function wp_create_nonce;
use function sanitize_text_field;
use function get_post_meta;
use function wp_get_attachment_image_url;
use function get_the_permalink;
use function wc_get_product;
use function wc_placeholder_img_src;
use function wp_kses_post;
use function esc_html;
use function esc_url;
use function esc_attr;
use function add_query_arg;
use function ob_start;
use function ob_get_clean;
use function apply_filters;

/**
 * Search megamenu component.
 *
 * Registers:
 * - REST endpoint: GET /wp-json/eternal/v1/search?q={term}
 * - Template tag: wp_rig()->get_search_recommended_products()
 * - JS asset via wp_rig_js_files filter
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const REST_NAMESPACE = 'eternal/v1';
	const REST_ROUTE     = '/search';
	const RESULTS_LIMIT  = 4;
	const DEFAULT_LIMIT  = 2;

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug(): string {
		return 'search';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize(): void {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_filter( 'wp_rig_js_files', array( $this, 'filter_register_script' ) );
	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance.
	 *
	 * @return array Associative array of $method_name => $callback_info pairs.
	 */
	public function template_tags(): array {
		return array(
			'get_search_recommended_products' => array( $this, 'get_recommended_products_html' ),
		);
	}

	/**
	 * Registers the REST API endpoint for product search.
	 */
	public function register_rest_routes(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE,
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_search' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'q' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $value ) {
							return strlen( trim( $value ) ) >= 3;
						},
					),
					'limit' => array(
						'required'          => false,
						'type'              => 'integer',
						'default'           => self::RESULTS_LIMIT,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Handles an incoming product search request.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_search( WP_REST_Request $request ) {
		if ( ! function_exists( 'WC' ) ) {
			return new WP_REST_Response(
				array(
					'products'      => array(),
					'total'         => 0,
					'view_more_url' => '',
				),
				200
			);
		}

		$query_term = sanitize_text_field( $request->get_param( 'q' ) );
		$limit      = absint( $request->get_param( 'limit' ) ) ? absint( $request->get_param( 'limit' ) ) : self::RESULTS_LIMIT;

		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				's'              => $query_term,
				'posts_per_page' => $limit,
				'no_found_rows'  => false,
			)
		);

		$products = array();
		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}
			$products[] = $this->format_product( $product );
		}

		return new WP_REST_Response(
			array(
				'products'      => $products,
				'total'         => (int) $query->found_posts,
				'view_more_url' => add_query_arg(
					array(
						'post_type' => 'product',
						's'         => $query_term,
					),
					home_url( '/' )
				),
			),
			200
		);
	}

	/**
	 * Returns buffered HTML of recommended product cards for the default megamenu state.
	 *
	 * Tries featured products ordered by total_sales DESC. Falls back to
	 * best-sellers only if no featured products are found.
	 *
	 * @return string Safe HTML string of product card anchor elements.
	 */
	public function get_recommended_products_html(): string {
		if ( ! function_exists( 'WC' ) ) {
			return '';
		}

		// First: featured products, ordered by sales.
		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => self::DEFAULT_LIMIT,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
					),
				),
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_orderby
				'orderby'        => 'meta_value_num',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_key'       => 'total_sales',
				'order'          => 'DESC',
			)
		);

		// Fallback: best-sellers with no featured filter.
		if ( ! $query->have_posts() ) {
			$query = new WP_Query(
				array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => self::DEFAULT_LIMIT,
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_orderby
					'orderby'        => 'meta_value_num',
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_key'       => 'total_sales',
					'order'          => 'DESC',
				)
			);
		}

		if ( ! $query->have_posts() ) {
			return '';
		}

		ob_start();
		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}
			$this->render_product_card( $product );
		}
		return ob_get_clean();
	}

	/**
	 * Outputs a single product card anchor element.
	 *
	 * @param \WC_Product $product The WooCommerce product.
	 */
	private function render_product_card( $product ): void {
		$data = $this->format_product( $product );
		?>
		<a
			href="<?php echo esc_url( $data['permalink'] ); ?>"
			class="search-megamenu__product-card"
			aria-label="<?php echo esc_attr( $data['name'] ); ?>"
		>
			<img
				class="search-megamenu__product-card-img"
				src="<?php echo esc_url( $data['image_url'] ); ?>"
				alt="<?php echo esc_attr( $data['image_alt'] ); ?>"
				width="115"
				height="115"
				loading="lazy"
			>
			<div class="search-megamenu__product-card-body">
				<?php if ( $data['size_badge'] ) : ?>
				<span class="search-megamenu__product-card-badge">
					<?php echo esc_html( $data['size_badge'] ); ?>
				</span>
				<?php endif; ?>
				<p class="search-megamenu__product-card-name">
					<?php echo esc_html( $data['name'] ); ?>
				</p>
				<?php if ( $data['name_fr'] ) : ?>
				<p class="search-megamenu__product-card-name-fr">
					<?php echo esc_html( $data['name_fr'] ); ?>
				</p>
				<?php endif; ?>
				<div class="search-megamenu__product-card-price">
					<?php echo $data['price_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php if ( $data['short_description'] ) : ?>
				<p class="search-megamenu__product-card-desc">
					<?php echo wp_kses_post( $data['short_description'] ); ?>
				</p>
				<?php endif; ?>
			</div>
		</a>
		<?php
	}

	/**
	 * Formats a WC_Product into the response/render array shape.
	 *
	 * @param \WC_Product $product The WooCommerce product.
	 * @return array Associative array of product data.
	 */
	private function format_product( $product ): array {
		$image_id  = $product->get_image_id();
		$image_url = $image_id
			? wp_get_attachment_image_url( $image_id, array( 115, 115 ) )
			: wc_placeholder_img_src( array( 115, 115 ) );
		$image_alt = $image_id
			? (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true )
			: $product->get_name();

		// First value of pa_size attribute, if set.
		$size_raw   = $product->get_attribute( 'pa_size' );
		$size_badge = $size_raw ? explode( ',', $size_raw )[0] : '';

		return array(
			'id'                => $product->get_id(),
			'name'              => $product->get_name(),
			'name_fr'           => (string) get_post_meta( $product->get_id(), '_product_name_fr', true ),
			'permalink'         => $product->get_permalink(),
			'price_html'        => $product->get_price_html(),
			'image_url'         => $image_url ? $image_url : '',
			'image_alt'         => $image_alt,
			'size_badge'        => trim( $size_badge ),
			'short_description' => $product->get_short_description(),
		);
	}

	/**
	 * Registers the search megamenu JS via the wp_rig_js_files filter.
	 *
	 * @param array $js_files Existing JS files array.
	 * @return array Modified JS files array.
	 */
	public function filter_register_script( array $js_files ): array {
		$js_files['eternal-search-megamenu'] = array(
			'file'     => 'search-megamenu.min.js',
			'global'   => true,
			'footer'   => true,
			'loading'  => 'defer',
			'deps'     => array(),
			'localize' => array(
				'eternalSearch' => array(
					'endpoint'     => rest_url( self::REST_NAMESPACE . self::REST_ROUTE ),
					'nonce'        => wp_create_nonce( 'wp_rest' ),
					'viewMoreBase' => home_url( '/?post_type=product&s=' ),
				),
			),
		);

		return $js_files;
	}
}
