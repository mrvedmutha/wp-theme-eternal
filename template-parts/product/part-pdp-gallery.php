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

// Build ordered thumbnail list: main image first, then ALL gallery images.
$thumb_ids    = array_filter( array_merge( array( $main_image_id ), $gallery_ids ) );
$total_images = count( $thumb_ids );
$has_multiple = $total_images > 1;

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
				data-index="<?php echo esc_attr( $index ); ?>"
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

	<!-- Hero image with navigation -->
	<div class="pdp-gallery__hero" data-lightbox-trigger data-current-index="0" data-total-images="<?php echo esc_attr( $total_images ); ?>">
		<!-- Gradient overlays -->
		<?php if ( $has_multiple ) : ?>
			<div class="pdp-gallery__gradient pdp-gallery__gradient--left"></div>
			<div class="pdp-gallery__gradient pdp-gallery__gradient--right"></div>

			<!-- Navigation controls -->
			<button class="pdp-gallery__nav pdp-gallery__nav--prev" aria-label="Previous image" data-nav-prev>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<button class="pdp-gallery__nav pdp-gallery__nav--next" aria-label="Next image" data-nav-next>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		<?php endif; ?>

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
			data-hero-img
		/>
	</div><!-- .pdp-gallery__hero -->


<!-- Lightbox Modal -->
<div class="pdp-modal" data-pdp-modal hidden role="dialog" aria-modal="true" aria-label="Product image gallery">
	<div class="pdp-modal__backdrop" data-modal-backdrop></div>
	
	<div class="pdp-modal__container">
		<!-- Close button -->
		<button class="pdp-modal__close" aria-label="Close gallery" data-modal-close>
			<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M14.1405 13.6099C14.2109 13.6803 14.2504 13.7757 14.2504 13.8752C14.2504 13.9747 14.2109 14.0702 14.1405 14.1405C14.0702 14.2109 13.9747 14.2504 13.8752 14.2504C13.7757 14.2504 13.6803 14.2109 13.6099 14.1405L7.12521 7.65583L0.640521 14.1405C0.570156 14.2109 0.47472 14.2504 0.375208 14.2504C0.275697 14.2504 0.180261 14.2109 0.109896 14.1405C0.0395309 14.0702 1.96161e-09 13.9747 0 13.8752C-1.96161e-09 13.7757 0.0395306 13.6803 0.109896 13.6099L6.59458 7.12521L0.109896 0.640521C0.0395306 0.570156 0 0.47472 0 0.375208C0 0.275697 0.0395306 0.180261 0.109896 0.109896C0.180261 0.0395306 0.275697 0 0.375208 0C0.47472 0 0.570156 0.0395306 0.640521 0.109896L7.12521 6.59458L13.6099 0.109896C13.6447 0.0750545 13.6861 0.0474169 13.7316 0.0285609C13.7771 0.00970488 13.8259 9.7129e-10 13.8752 0C13.9245 -9.71289e-10 13.9733 0.00970488 14.0188 0.0285609C14.0643 0.0474169 14.1057 0.0750545 14.1405 0.109896C14.1754 0.144737 14.203 0.1861 14.2219 0.231622C14.2407 0.277145 14.2504 0.325935 14.2504 0.375208C14.2504 0.424482 14.2407 0.473272 14.2219 0.518795C14.203 0.564317 14.1754 0.60568 14.1405 0.640521L7.65583 7.12521L14.1405 13.6099Z" fill="currentColor"/>
			</svg>
		</button>

		<!-- Image content area -->
		<div class="pdp-modal__content">
			<?php if ( $has_multiple ) : ?>
				<!-- Gradient overlays -->
				<div class="pdp-modal__gradient pdp-modal__gradient--left"></div>
				<div class="pdp-modal__gradient pdp-modal__gradient--right"></div>

				<!-- Navigation controls -->
				<button class="pdp-modal__nav pdp-modal__nav--prev" aria-label="Previous image" data-modal-nav-prev>
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>

				<button class="pdp-modal__nav pdp-modal__nav--next" aria-label="Next image" data-modal-nav-next>
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			<?php endif; ?>

			<!-- Modal hero image -->
			<img
				class="pdp-modal__img"
				src=""
				srcset=""
				alt=""
				data-modal-img
			/>
		</div><!-- .pdp-modal__content -->

		<!-- Thumbnail strip -->
		<?php if ( $has_multiple ) : ?>
			<div class="pdp-modal__thumbs" role="list" data-modal-thumbs>
				<?php foreach ( $thumb_ids as $index => $image_id ) : ?>
					<?php
					$thumb_src   = wp_get_attachment_image_src( $image_id, 'thumbnail' );
					$thumb_url   = $thumb_src ? $thumb_src[0] : '';
					$thumb_alt   = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
					$full_src    = wp_get_attachment_image_src( $image_id, 'large' );
					$full_url    = $full_src ? $full_src[0] : '';
					$full_srcset = wp_get_attachment_image_srcset( $image_id, 'large' );
					?>
					<button
						class="pdp-modal__thumb"
						role="listitem"
						aria-label="<?php echo esc_attr( $thumb_alt ? $thumb_alt : $product->get_name() ); ?>"
						data-modal-thumb
						data-full-url="<?php echo esc_url( $full_url ); ?>"
						data-full-srcset="<?php echo esc_attr( $full_srcset ? $full_srcset : '' ); ?>"
						data-index="<?php echo esc_attr( $index ); ?>"
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
			</div><!-- .pdp-modal__thumbs -->
		<?php endif; ?>
	</div><!-- .pdp-modal__container -->
</div><!-- .pdp-modal -->
</div><!-- .pdp-gallery -->
