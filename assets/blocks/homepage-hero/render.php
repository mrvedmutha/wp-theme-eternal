<?php
/**
 * Homepage Hero block — frontend render template.
 *
 * Variables provided by WordPress at include-time:
 *   $attributes (array)   Block attributes from block.json + editor input.
 *   $content    (string)  Inner blocks HTML (unused — no innerBlocks).
 *   $block      (WP_Block) Block instance.
 *
 * @package wp_rig
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = is_array( $attributes ?? null ) ? $attributes : array();

$hero_image_id         = (int) ( $attributes['heroImageId'] ?? 0 );
$hero_image_url        = $attributes['heroImageUrl'] ?? '';
$hero_mobile_image_id  = (int) ( $attributes['heroMobileImageId'] ?? 0 );
$hero_mobile_image_url = $attributes['heroMobileImageUrl'] ?? '';
$hero_heading          = $attributes['heroHeading'] ?? __( 'The Pinnacle of Longevity Supplements', 'wp-rig' );
$hero_subtext          = $attributes['heroSubtext'] ?? __( 'Advanced nutraceutical formulations developed to support wellbeing from within.', 'wp-rig' );
$hero_cta_label        = $attributes['heroCtaLabel'] ?? __( 'SHOP NOW', 'wp-rig' );
$hero_cta_url          = $attributes['heroCtaUrl'] ?? '/shop';

// Prefer full WP attachment URL over stored URL (handles media library moves).
if ( $hero_image_id ) {
	$attachment_url = wp_get_attachment_image_url( $hero_image_id, 'full' );
	if ( $attachment_url ) {
		$hero_image_url = $attachment_url;
	}
}

if ( $hero_mobile_image_id ) {
	$mobile_attachment_url = wp_get_attachment_image_url( $hero_mobile_image_id, 'full' );
	if ( $mobile_attachment_url ) {
		$hero_mobile_image_url = $mobile_attachment_url;
	}
}

$has_desktop  = ! empty( $hero_image_url );
$has_mobile   = ! empty( $hero_mobile_image_url );
$fallback_url = $has_desktop ? $hero_image_url : $hero_mobile_image_url;

?>
<section class="homepage-hero">
	<?php if ( $has_desktop || $has_mobile ) : ?>
	<picture>
		<?php if ( $has_mobile ) : ?>
		<source
			media="(max-width: 1024px)"
			srcset="<?php echo esc_url( $hero_mobile_image_url ); ?>"
		>
		<?php endif; ?>
		<img
			class="homepage-hero__bg"
			src="<?php echo esc_url( $fallback_url ); ?>"
			alt=""
			aria-hidden="true"
			decoding="async"
		>
	</picture>
	<?php endif; ?>
	<div class="homepage-hero__content">
		<div class="homepage-hero__text">
			<h1 class="hero-heading"><?php echo esc_html( $hero_heading ); ?></h1>
			<p class="hero-subtext"><?php echo esc_html( $hero_subtext ); ?></p>
		</div>

		<a class="hero-cta" href="<?php echo esc_url( $hero_cta_url ); ?>">
			<span class="hero-cta__label"><?php echo esc_html( $hero_cta_label ); ?></span>
			<span class="hero-cta__line" aria-hidden="true"></span>
		</a>
	</div>
</section>
