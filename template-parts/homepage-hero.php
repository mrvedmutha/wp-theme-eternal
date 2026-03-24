<?php
/**
 * Template part: Homepage Hero Section
 *
 * Renders the full-width hero section on the front page.
 * All content is managed via Appearance > Customize > Homepage Hero.
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

$hero_image_id  = (int) get_theme_mod( 'hero_image', 0 );
$hero_heading   = get_theme_mod( 'hero_heading', __( 'The Pinnacle of Longevity Supplements', 'wp-rig' ) );
$hero_subtext   = get_theme_mod( 'hero_subtext', __( 'Advanced nutraceutical formulations developed to support wellbeing from within.', 'wp-rig' ) );
$hero_cta_label = get_theme_mod( 'hero_cta_label', __( 'SHOP NOW', 'wp-rig' ) );
$hero_cta_url   = get_theme_mod( 'hero_cta_url', '/shop' );

$hero_image_url = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';

$inline_style = $hero_image_url
	? ' style="background-image: url(\'' . esc_url( $hero_image_url ) . '\');"'
	: '';
?>
<section class="homepage-hero"<?php echo $inline_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>>
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
