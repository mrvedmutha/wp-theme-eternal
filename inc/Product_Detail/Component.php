<?php
/**
 * WP_Rig\WP_Rig\Product_Detail\Component class
 *
 * Provides template tags and JS data localisation for the WooCommerce
 * single-product (PDP) template.
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Product_Detail;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use function WP_Rig\WP_Rig\wp_rig;
use function add_action;
use function is_product;
use function get_the_ID;
use function wp_enqueue_script;
use function wp_localize_script;
use function get_theme_file_uri;
use function get_theme_file_path;
use function get_post_meta;
use function wc_get_product;
use function get_woocommerce_currency;
use function wp_create_nonce;
use function admin_url;
use function number_format;
use function json_decode;
use function class_exists;

/**
 * Class for the Product Detail Page component.
 */
class Component implements Component_Interface, Templating_Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug(): string {
		return 'product-detail';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize(): void {
		add_action( 'after_setup_theme', array( $this, 'add_woocommerce_support' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Declares WooCommerce theme support so WC uses the proper template hierarchy
	 * instead of the unsupported-theme filter fallback.
	 */
	public function add_woocommerce_support(): void {
		add_theme_support( 'woocommerce' );
	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance.
	 *
	 * @return array Associative array of $method_name => $callable pairs.
	 */
	public function template_tags(): array {
		return array(
			'get_product_meta'     => array( $this, 'get_product_meta' ),
			'get_supply_plans'     => array( $this, 'get_supply_plans' ),
			'parse_markdown_light' => array( $this, 'parse_markdown_light' ),
			'format_price'         => array( $this, 'format_price' ),
		);
	}

	/**
	 * Enqueues the product detail JS on single product pages.
	 */
	public function enqueue_assets(): void {
		if ( ! is_product() ) {
			return;
		}

		$js_path = get_theme_file_path( '/assets/js/product-detail.min.js' );
		$js_uri  = get_theme_file_uri( '/assets/js/product-detail.min.js' );

		if ( ! file_exists( $js_path ) ) {
			return;
		}

		wp_enqueue_script(
			'eternal-pdp',
			$js_uri,
			array( 'jquery' ),
			(string) filemtime( $js_path ),
			true
		);

		wp_localize_script( 'eternal-pdp', 'EternalPDP', $this->get_js_data() );
	}

	/**
	 * Builds the data object localised to `window.EternalPDP`.
	 *
	 * @return array JS data payload.
	 */
	private function get_js_data(): array {
		$product_id = get_the_ID();
		$product    = wc_get_product( $product_id );

		$data = array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'wc_get_variation' ),
			'productId'     => $product_id,
			'priceDecimals' => wc_get_price_decimals(),
			'variations'    => array(),
			'plans'         => array(),
		);

		if ( $product && $product->is_type( 'variable' ) ) {
			/**
			 * Variable product with variations.
			 *
			 * @var \WC_Product_Variable $product
			 */
			$available = $product->get_available_variations();
			foreach ( $available as $variation ) {
				$data['variations'][] = array(
					'variation_id' => $variation['variation_id'],
					'attributes'   => $variation['attributes'],
					'price'        => $variation['display_price'],
					'image'        => isset( $variation['image']['url'] ) ? $variation['image']['url'] : '',
					'image_srcset' => isset( $variation['image']['srcset'] ) ? $variation['image']['srcset'] : '',
				);
			}
		}

		$plans = $this->get_supply_plans( (int) $product_id );
		foreach ( $plans as $plan ) {
			$data['plans'][] = array(
				'months'       => $plan['months'],
				'label'        => $plan['label'],
				'contentsNote' => $plan['contents_note'],
				'finalPrice'   => $plan['final_price'],
				'mrp'          => $plan['mrp'],
				'symbol'       => $plan['symbol'],
			);
		}

		return $data;
	}

	// -------------------------------------------------------------------------
	// Template tag methods
	// -------------------------------------------------------------------------

	/**
	 * Returns sanitised product meta for the given product ID.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return array Product meta map.
	 */
	public function get_product_meta( int $product_id ): array {
		$raw = function ( string $key ) use ( $product_id ): string {
			return (string) get_post_meta( $product_id, $key, true );
		};

		$features_raw    = get_post_meta( $product_id, 'product_features', true );
		$ingredients_raw = get_post_meta( $product_id, 'product_key_ingredients', true );

		$features    = is_string( $features_raw ) ? json_decode( $features_raw, true ) : array();
		$ingredients = is_string( $ingredients_raw ) ? json_decode( $ingredients_raw, true ) : array();

		if ( ! is_array( $features ) ) {
			$features = array();
		}
		if ( ! is_array( $ingredients ) ) {
			$ingredients = array();
		}

		// Normalise encoding artifacts in feature body text.
		foreach ( $features as &$feature ) {
			if ( isset( $feature['body'] ) ) {
				// Paragraph breaks first (rnrn), then single rn only when NOT flanked by letters
				// so words like "Eternal" or "return" are not mangled.
				$feature['body'] = str_replace( 'rnrn', "\n\n", $feature['body'] );
				$feature['body'] = preg_replace( '/(?<![a-zA-Z])rn(?![a-zA-Z])/', "\n", $feature['body'] );

				// Decode bare uXXXX unicode literals (backslash was stripped by the data source).
				$feature['body'] = preg_replace_callback(
					'/u([0-9a-fA-F]{4})/',
					function ( array $m ): string {
						return mb_convert_encoding( pack( 'H*', $m[1] ), 'UTF-8', 'UCS-2BE' );
					},
					$feature['body']
				);
			}
		}
		unset( $feature );

		return array(
			'caption'               => sanitize_text_field( $raw( 'product_caption' ) ),
			'french_text'           => sanitize_text_field( $raw( 'product_french_text' ) ),
			'tagline'               => sanitize_text_field( $raw( 'product_tagline' ) ),
			'card_bg'               => sanitize_hex_color( $raw( 'product_card_bg' ) ) ?? '',
			'buy_box_amount'        => sanitize_text_field( $raw( 'product_buy_box_amount' ) ),
			'buy_box_unit'          => sanitize_text_field( $raw( 'product_buy_box_unit' ) ),
			'buy_box_ingredients'   => wp_kses_post( $raw( 'product_buy_box_ingredients' ) ),
			'buy_box_caution'       => sanitize_text_field( $raw( 'product_buy_box_caution' ) ),
			'buy_box_how_to_apply'  => wp_kses_post( $raw( 'product_buy_box_how_to_apply' ) ),
			'buy_box_bonus_tip'     => sanitize_text_field( $raw( 'product_buy_box_bonus_tip' ) ),
			'ingredients_title'     => sanitize_text_field( $raw( 'product_ingredients_title' ) ),
			'key_ingredients'       => $ingredients,
			'features'              => $features,
			'benefits_bullets'      => sanitize_textarea_field( $raw( 'product_benefits_bullets' ) ),
			'notes_top'             => sanitize_text_field( $raw( 'product_notes_top' ) ),
			'notes_middle'          => sanitize_text_field( $raw( 'product_notes_middle' ) ),
			'notes_base'            => sanitize_text_field( $raw( 'product_notes_base' ) ),
		);
	}

	/**
	 * Returns active supply plan tiers for the given product, or [] if the
	 * eternal-subscription plugin is inactive or no tiers are configured.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return array Array of tier data maps.
	 */
	public function get_supply_plans( int $product_id ): array {
		if ( ! class_exists( 'ESP_Frontend' ) ) {
			return array();
		}

		$tiers = \ESP_Frontend::get_active_tiers( $product_id );

		return is_array( $tiers ) ? $tiers : array();
	}

	/**
	 * Converts lightweight markdown (`**bold**`, double-newlines) to HTML.
	 *
	 * @param string $text Raw text with optional markdown.
	 * @return string HTML string.
	 */
	public function parse_markdown_light( string $text ): string {
		// Normalise line endings.
		$text = str_replace( "\r\n", "\n", $text );

		// **bold** → <strong>bold</strong>.
		$text = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text );

		// Double newlines → paragraph breaks.
		$parts = preg_split( '/\n{2,}/', trim( $text ) );
		if ( count( $parts ) > 1 ) {
			$text = '<p>' . implode( '</p><p>', array_map( 'nl2br', $parts ) ) . '</p>';
		} else {
			$text = nl2br( $text );
		}

		return $text;
	}

	/**
	 * Formats a price amount with the active WooCommerce currency symbol.
	 *
	 * @param float  $amount   The numeric price.
	 * @param string $currency Optional ISO currency code (defaults to store currency).
	 * @return string Formatted price string, e.g. "₹1,599".
	 */
	public function format_price( float $amount, string $currency = '' ): string {
		if ( '' === $currency ) {
			$currency = get_woocommerce_currency();
		}
		$symbol = get_woocommerce_currency_symbol( $currency );
		return $symbol . number_format( $amount, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
	}
}
