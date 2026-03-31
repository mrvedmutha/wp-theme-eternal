<?php
/**
 * PDP — Buy box wrapper. Detects product type and delegates to the correct
 * sub-template, then always includes the accordion.
 *
 * Renders the main buy box section with product info and purchase options.
 *
 * Args:
 *   $args['product'] WC_Product
 *   $args['meta']    array — from wp_rig()->get_product_meta()
 *   $args['plans']   array — from wp_rig()->get_supply_plans()
 *
 * @package wp_rig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product object.
 *
 * @var \WC_Product $product
 */
$product = isset( $args['product'] ) ? $args['product'] : wc_get_product( get_the_ID() );
$meta    = isset( $args['meta'] ) ? $args['meta'] : array();
$plans   = isset( $args['plans'] ) ? $args['plans'] : array();

if ( ! $product ) {
	return;
}

$is_subscription = ! empty( $plans );
$is_variable     = $product->is_type( 'variable' );

$review_count = (int) $product->get_review_count();
$avg_rating   = (float) $product->get_average_rating();

$regular_price = (float) $product->get_regular_price();
$price         = (float) $product->get_price();
$is_on_sale    = $regular_price > $price && $price > 0;
?>

<section class="pdp-buybox">

	<?php if ( ! empty( $meta['caption'] ) ) : ?>
		<p class="pdp-buybox__eyebrow"><?php echo esc_html( $meta['caption'] ); ?></p>
	<?php endif; ?>

	<h1 class="pdp-buybox__name"><?php echo esc_html( $product->get_name() ); ?></h1>

	<?php if ( ! empty( $meta['french_text'] ) ) : ?>
		<p class="pdp-buybox__subtitle"><?php echo esc_html( $meta['french_text'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $meta['tagline'] ) ) : ?>
		<p class="pdp-buybox__tagline"><?php echo esc_html( $meta['tagline'] ); ?></p>
	<?php endif; ?>

	<!-- Star rating — hidden when there are no reviews -->
	<div class="pdp-buybox__stars<?php echo ( 0 === $review_count ) ? ' is-hidden' : ''; ?>" aria-label="
		<?php
		/* translators: %s: average rating (e.g. "4.5") */
		echo esc_attr( sprintf( __( 'Rated %s out of 5', 'wp-rig' ), $avg_rating ) );
		?>
	">
		<span class="pdp-buybox__stars-group" aria-hidden="true">
			<?php
			for ( $i = 1; $i <= 5; $i++ ) :
				$filled = $i <= round( $avg_rating );
				?>
				<svg class="pdp-star<?php echo $filled ? ' pdp-star--filled' : ''; ?>" width="13" height="13" viewBox="0 0 12 12" fill="none">
					<path d="M6 1l1.35 2.74L10.5 4.27l-2.25 2.19.53 3.09L6 8l-2.78 1.55.53-3.09L1.5 4.27l3.15-.53L6 1z" fill="<?php echo $filled ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="0.75"/>
				</svg>
			<?php endfor; ?>
		</span>
		<span class="pdp-buybox__review-count">
			<?php
			/* translators: 1: average rating, 2: review count */
			echo esc_html( sprintf( __( '%1$s/5 - %2$s Reviews', 'wp-rig' ), $avg_rating, $review_count ) );
			?>
		</span>
	</div>

	<?php
	// Delegate to the correct buy-box state template.
	if ( $is_subscription ) {
		get_template_part(
			'template-parts/product/part-pdp',
			'buybox-subscription',
			array(
				'product'       => $product,
				'meta'          => $meta,
				'plans'         => $plans,
				'is_on_sale'    => $is_on_sale,
				'regular_price' => $regular_price,
				'price'         => $price,
			)
		);
	} elseif ( $is_variable ) {
		get_template_part(
			'template-parts/product/part-pdp',
			'buybox-variable',
			array(
				'product'    => $product,
				'meta'       => $meta,
				'is_on_sale' => $is_on_sale,
			)
		);
	} else {
		get_template_part(
			'template-parts/product/part-pdp',
			'buybox-simple',
			array(
				'product'       => $product,
				'meta'          => $meta,
				'is_on_sale'    => $is_on_sale,
				'regular_price' => $regular_price,
				'price'         => $price,
			)
		);
	}

	// Divider between buy box top and accordion.
	echo '<hr class="pdp-divider pdp-divider--section">';

	// Accordion — always shown.
	get_template_part(
		'template-parts/product/part-pdp',
		'accordion',
		array(
			'product' => $product,
			'meta'    => $meta,
		)
	);
	?>

</section><!-- .pdp-buybox -->
