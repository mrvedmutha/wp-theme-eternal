<?php
/**
 * PDP — Buy box: Variable product state.
 *
 * Renders variant selectors for variable products.
 *
 * Args:
 *   $args['product']    WC_Product_Variable
 *   $args['meta']       array
 *   $args['is_on_sale'] bool
 *
 * @package wp_rig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Variable product object.
 *
 * @var \WC_Product_Variable $product
 */
$product = isset( $args['product'] ) ? $args['product'] : wc_get_product( get_the_ID() );
$meta    = isset( $args['meta'] ) ? $args['meta'] : array();

if ( ! $product || ! $product->is_type( 'variable' ) ) {
	return;
}

$currency   = get_woocommerce_currency();
$symbol     = get_woocommerce_currency_symbol( $currency );
$attributes = $product->get_variation_attributes();
$price_html = $product->get_price_html();
?>

<!-- Variant selectors -->
<div class="pdp-buybox__variant" data-variation-form>

	<?php
	foreach ( $attributes as $attribute_name => $options ) :
		$label = wc_attribute_label( $attribute_name );
		?>
		<div class="pdp-buybox__variant-group">
			<label
				class="pdp-buybox__variant-label"
				for="pdp-attr-<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
			>
				<?php
				/* translators: %s: attribute name e.g. "Fragrance" */
				printf( esc_html__( 'Choose %s', 'wp-rig' ), esc_html( $label ) );
				?>
			</label>

			<div class="pdp-buybox__select-wrap">
				<select
					id="pdp-attr-<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
					class="pdp-buybox__select"
					name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
					data-attribute_name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
				>
					<option value=""><?php esc_html_e( 'Select', 'wp-rig' ); ?></option>
					<?php
					foreach ( $options as $option ) :
						$option_term = get_term_by( 'slug', $option, $attribute_name );
						$label       = $option_term ? $option_term->name : $option;
						?>
						<option value="<?php echo esc_attr( $option ); ?>">
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<span class="pdp-buybox__select-chevron" aria-hidden="true">
					<svg width="10" height="6" viewBox="0 0 10 6" fill="none">
						<path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			</div><!-- .pdp-buybox__select-wrap -->
		</div><!-- .pdp-buybox__variant-group -->
	<?php endforeach; ?>

</div><!-- .pdp-buybox__variant -->

<!-- Price block — shows range until variation selected -->
<div class="pdp-buybox__price" data-variation-price>
	<span class="pdp-buybox__price-mrp"><?php esc_html_e( 'MRP', 'wp-rig' ); ?></span>
	<span class="pdp-buybox__price-amount pdp-buybox__price-range">
		<?php echo wp_kses_post( $price_html ); ?>
	</span>
	<?php if ( ! empty( $meta['buy_box_amount'] ) && ! empty( $meta['buy_box_unit'] ) ) : ?>
		<span class="pdp-buybox__price-unit">
			/ <?php echo esc_html( $meta['buy_box_amount'] . $meta['buy_box_unit'] ); ?>
		</span>
	<?php endif; ?>
	<p class="pdp-buybox__price-tax"><?php esc_html_e( '(Incl. of all taxes)', 'wp-rig' ); ?></p>
</div><!-- .pdp-buybox__price -->

<hr class="pdp-divider">

<!-- Add to bag form -->
<form class="pdp-form cart" method="post" enctype="multipart/form-data" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">

	<div class="pdp-buybox__actions">

		<div class="pdp-qty" data-qty>
			<button type="button" class="pdp-qty__btn pdp-qty__btn--minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'wp-rig' ); ?>">−</button>
			<span class="pdp-qty__display" aria-live="polite">1</span>
			<button type="button" class="pdp-qty__btn pdp-qty__btn--plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'wp-rig' ); ?>">+</button>
		</div>

		<input type="hidden" name="quantity" value="1" class="pdp-qty__input">
		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
		<input type="hidden" name="variation_id" value="" class="pdp-variation-id">

		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>

		<button type="submit" class="pdp-cta">
			<?php esc_html_e( 'ADD TO BAG', 'wp-rig' ); ?>
		</button>

	</div><!-- .pdp-buybox__actions -->

</form>
