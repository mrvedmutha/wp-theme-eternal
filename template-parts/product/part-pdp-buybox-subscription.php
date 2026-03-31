<?php
/**
 * PDP — Buy box: Subscription product state.
 *
 * Renders buy box for subscription products with tiered pricing.
 *
 * Args:
 *   $args['product']        WC_Product
 *   $args['meta']           array
 *   $args['plans']          array  — ESP_Frontend tiers
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
$plans         = isset( $args['plans'] ) ? $args['plans'] : array();
$is_on_sale    = isset( $args['is_on_sale'] ) ? (bool) $args['is_on_sale'] : false;
$regular_price = isset( $args['regular_price'] ) ? (float) $args['regular_price'] : 0.0;
$price         = isset( $args['price'] ) ? (float) $args['price'] : 0.0;

if ( ! $product || empty( $plans ) ) {
	return;
}

$currency    = get_woocommerce_currency();
$symbol      = get_woocommerce_currency_symbol( $currency );
$first_plan  = $plans[0];
$first_price = $first_plan['final_price'];
?>

<!-- Purchase mode radio cards -->
<div class="pdp-buybox__plans" role="radiogroup" aria-label="<?php esc_attr_e( 'Purchase options', 'wp-rig' ); ?>" data-purchase-mode>

	<!-- One Time Purchase card -->
	<label class="pdp-buybox__plan-card" data-plan-card="one-time">
		<input
			type="radio"
			name="eternal_purchase_mode"
			value="one-time"
			class="pdp-buybox__plan-radio"
		>
		<div class="pdp-buybox__plan-header">
			<span class="pdp-buybox__radio-dot" aria-hidden="true"></span>
			<span class="pdp-buybox__plan-label"><?php esc_html_e( 'One Time Purchase', 'wp-rig' ); ?></span>
		</div>
		<span class="pdp-buybox__plan-price" data-one-time-price>
			<?php echo esc_html( $symbol . number_format( $price, 0, '.', ',' ) ); ?>
		</span>
	</label>

	<!-- Supply Plan card -->
	<label class="pdp-buybox__plan-card pdp-buybox__plan-card--expanded" data-plan-card="subscription">
		<input
			type="radio"
			name="eternal_purchase_mode"
			value="subscription"
			class="pdp-buybox__plan-radio"
			checked
		>
		<div class="pdp-buybox__plan-header">
			<span class="pdp-buybox__radio-dot" aria-hidden="true"></span>
			<span class="pdp-buybox__plan-label"><?php esc_html_e( 'Subscription', 'wp-rig' ); ?></span>
		</div>

		<!-- Plan body: [select+note | price] row — visible when subscription is selected -->
		<div class="pdp-buybox__plan-body" data-plan-dropdown>
			<div class="pdp-buybox__plan-dropdown-col">
				<div class="pdp-buybox__select-wrap">
					<select class="pdp-buybox__select pdp-buybox__plan-select" name="eternal_supply_months_select" data-plan-select>
						<?php foreach ( $plans as $plan ) : ?>
							<option
								value="<?php echo esc_attr( $plan['months'] ); ?>"
								data-price="<?php echo esc_attr( $plan['final_price'] ); ?>"
								data-mrp="<?php echo esc_attr( $plan['mrp'] ); ?>"
								data-symbol="<?php echo esc_attr( $plan['symbol'] ); ?>"
							>
								<?php echo esc_html( $plan['label'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="pdp-buybox__select-chevron" aria-hidden="true">
						<svg width="10" height="6" viewBox="0 0 10 6" fill="none">
							<path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</span>
				</div>

				<?php if ( ! empty( $first_plan['contents_note'] ) ) : ?>
					<p class="pdp-buybox__plan-note" data-plan-note>
						<?php echo esc_html( $first_plan['contents_note'] ); ?>
					</p>
				<?php endif; ?>
			</div><!-- .pdp-buybox__plan-dropdown-col -->

			<span class="pdp-buybox__plan-price" data-subscription-price>
				<?php echo esc_html( $symbol . number_format( $first_price, 0, '.', ',' ) ); ?>
			</span>
		</div><!-- .pdp-buybox__plan-body -->
	</label><!-- subscription card -->

</div><!-- .pdp-buybox__plans -->

<!-- Price block — updates dynamically via JS when plan is changed -->
<div class="pdp-buybox__price" data-subscription-price-block>
	<span class="pdp-buybox__price-mrp"><?php esc_html_e( 'MRP', 'wp-rig' ); ?></span>

	<?php if ( $is_on_sale ) : ?>
		<s class="pdp-buybox__price-regular" data-mrp-display>
			<?php echo esc_html( $symbol . number_format( $regular_price, 0, '.', ',' ) ); ?>
		</s>
	<?php endif; ?>

	<span class="pdp-buybox__price-amount" data-price-display>
		<?php echo esc_html( $symbol . number_format( $first_price, 0, '.', ',' ) ); ?>
	</span>

	<?php if ( ! empty( $meta['buy_box_amount'] ) && ! empty( $meta['buy_box_unit'] ) ) : ?>
		<span
			class="pdp-buybox__price-unit"
			data-unit-display
			data-unit-amount="<?php echo esc_attr( $meta['buy_box_amount'] ); ?>"
			data-unit-text="<?php echo esc_attr( $meta['buy_box_unit'] ); ?>"
		>/ <?php echo esc_html( $meta['buy_box_amount'] . ' ' . $meta['buy_box_unit'] ); ?></span>
	<?php endif; ?>

	<p class="pdp-buybox__price-tax"><?php esc_html_e( '(Incl. of all taxes)', 'wp-rig' ); ?></p>
</div><!-- .pdp-buybox__price -->

<!-- Add to bag form -->
<form class="pdp-form cart" method="post" enctype="multipart/form-data" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" data-subscription-form>

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
		<input type="hidden" name="eternal_supply_months" value="<?php echo esc_attr( $first_plan['months'] ); ?>" class="pdp-supply-months">

		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>

		<button type="submit" class="pdp-cta">
			<?php esc_html_e( 'ADD TO BAG', 'wp-rig' ); ?>
		</button>

	</div><!-- .pdp-buybox__actions -->

</form>
