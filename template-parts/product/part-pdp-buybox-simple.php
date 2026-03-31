<?php
/**
 * PDP — Buy box: Simple product state.
 *
 * Renders buy box for simple products.
 *
 * Args:
 *   $args['product']        WC_Product
 *   $args['meta']           array
 *   $args['is_on_sale']     bool
 *   $args['regular_price']  float
 *   $args['price']          float
 *
 * @package wp_rig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product object.
 *
 * @var \WC_Product $product
 */
$product       = isset( $args['product'] ) ? $args['product'] : wc_get_product( get_the_ID() );
$meta          = isset( $args['meta'] ) ? $args['meta'] : array();
$is_on_sale    = isset( $args['is_on_sale'] ) ? (bool) $args['is_on_sale'] : false;
$regular_price = isset( $args['regular_price'] ) ? (float) $args['regular_price'] : 0.0;
$price         = isset( $args['price'] ) ? (float) $args['price'] : 0.0;

if ( ! $product ) {
	return;
}

$currency = get_woocommerce_currency();
$symbol   = get_woocommerce_currency_symbol( $currency );
?>

<!-- Price block -->
<div class="pdp-buybox__price">
	<span class="pdp-buybox__price-mrp"><?php esc_html_e( 'MRP', 'wp-rig' ); ?></span>

	<?php if ( $is_on_sale ) : ?>
		<s class="pdp-buybox__price-regular">
			<?php echo esc_html( $symbol . number_format( $regular_price, 0, '.', ',' ) ); ?>
		</s>
	<?php endif; ?>

	<span class="pdp-buybox__price-amount">
		<?php echo esc_html( $symbol . number_format( $price, 0, '.', ',' ) ); ?>
	</span>

	<?php if ( ! empty( $meta['buy_box_amount'] ) && ! empty( $meta['buy_box_unit'] ) ) : ?>
		<span class="pdp-buybox__price-unit">
			/ <?php echo esc_html( $meta['buy_box_amount'] . $meta['buy_box_unit'] ); ?>
		</span>
	<?php endif; ?>

	<p class="pdp-buybox__price-tax"><?php esc_html_e( '(Incl. of all taxes)', 'wp-rig' ); ?></p>
</div><!-- .pdp-buybox__price -->

<!-- Add to bag form -->
<form class="pdp-form cart" method="post" enctype="multipart/form-data" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">

	<div class="pdp-buybox__actions">

		<div class="pdp-qty">
			<select class="pdp-qty__select" name="quantity" aria-label="<?php esc_attr_e( 'Quantity', 'wp-rig' ); ?>">
				<?php for ( $q = 1; $q <= 10; $q++ ) : ?>
					<option value="<?php echo esc_attr( $q ); ?>"><?php echo esc_html( $q ); ?></option>
				<?php endfor; ?>
			</select>
			<span class="pdp-qty__chevron" aria-hidden="true">
				<svg width="10" height="6" viewBox="0 0 10 6" fill="none">
					<path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
		</div>
		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">

		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>

		<button type="submit" class="pdp-cta">
			<?php esc_html_e( 'ADD TO BAG', 'wp-rig' ); ?>
		</button>

	</div><!-- .pdp-buybox__actions -->

</form>
