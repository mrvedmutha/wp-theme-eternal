<?php
/**
 * PDP — Breadcrumb bar.
 *
 * Renders breadcrumb navigation for product detail pages.
 *
 * Args:
 *   $args['product'] WC_Product
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

$categories = $product ? wc_get_product_category_list( $product->get_id(), ', ', '<span class="pdp-breadcrumb__cat">', '</span>' ) : '';
?>

<nav class="pdp-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wp-rig' ); ?>">
	<a class="pdp-breadcrumb__link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php esc_html_e( 'HOME', 'wp-rig' ); ?>
	</a>
	<span class="pdp-breadcrumb__sep" aria-hidden="true">/</span>

	<?php if ( $categories ) : ?>
		<span class="pdp-breadcrumb__segment">
			<?php echo wp_kses_post( $categories ); ?>
		</span>
		<span class="pdp-breadcrumb__sep" aria-hidden="true">/</span>
	<?php endif; ?>

	<span class="pdp-breadcrumb__current" aria-current="page">
		<?php echo esc_html( $product ? $product->get_name() : get_the_title() ); ?>
	</span>
</nav>
