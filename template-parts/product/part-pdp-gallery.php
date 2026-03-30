<?php
/**
 * PDP — Sticky gallery: thumbnails + hero image.
 *
 * Renders product image gallery with thumbnail strip.
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

if ( ! $product ) {
	return;
}

$main_image_id = $product->get_image_id();
$gallery_ids   = $product->get_gallery_image_ids();

// Build ordered thumbnail list: main image first, then gallery (up to 3 more).
$thumb_ids = array_filter( array_merge( array( $main_image_id ), array_slice( $gallery_ids, 0, 3 ) ) );

$main_src    = wp_get_attachment_image_src( $main_image_id, 'large' );
$main_url    = $main_src ? $main_src[0] : wc_placeholder_img_src( 'large' );
$main_srcset = $main_image_id ? wp_get_attachment_image_srcset( $main_image_id, 'large' ) : '';
$main_alt    = $main_image_id ? get_post_meta( $main_image_id, '_wp_attachment_image_alt', true ) : $product->get_name();
?>

<div class="pdp-gallery" data-gallery>

	<!-- Thumbnail strip -->
	<div class="pdp-gallery__thumbs" role="list">
		<?php
		foreach ( $thumb_ids as $index => $image_id ) :
			$thumb_src   = wp_get_attachment_image_src( $image_id, 'thumbnail' );
			$thumb_url   = $thumb_src ? $thumb_src[0] : '';
			$thumb_alt   = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			$full_src    = wp_get_attachment_image_src( $image_id, 'large' );
			$full_url    = $full_src ? $full_src[0] : '';
			$full_srcset = wp_get_attachment_image_srcset( $image_id, 'large' );
			$is_active   = ( 0 === $index ) ? ' is-active' : '';
			?>
			<button
				class="pdp-gallery__thumb<?php echo esc_attr( $is_active ); ?>"
				role="listitem"
				aria-label="<?php echo esc_attr( $thumb_alt ? $thumb_alt : $product->get_name() ); ?>"
				data-full-url="<?php echo esc_url( $full_url ); ?>"
				data-full-srcset="<?php echo esc_attr( $full_srcset ? $full_srcset : '' ); ?>"
			>
				<?php if ( $thumb_url ) : ?>
					<img
						src="<?php echo esc_url( $thumb_url ); ?>"
						alt=""
						width="80"
						height="100"
						loading="lazy"
					/>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div><!-- .pdp-gallery__thumbs -->

	<!-- Hero image -->
	<div class="pdp-gallery__hero">
		<img
			class="pdp-gallery__hero-img"
			src="<?php echo esc_url( $main_url ); ?>"
			<?php if ( $main_srcset ) : ?>
				srcset="<?php echo esc_attr( $main_srcset ); ?>"
				sizes="555px"
			<?php endif; ?>
			alt="<?php echo esc_attr( $main_alt ? $main_alt : $product->get_name() ); ?>"
			width="555"
			height="700"
		/>
	</div><!-- .pdp-gallery__hero -->

</div><!-- .pdp-gallery -->
