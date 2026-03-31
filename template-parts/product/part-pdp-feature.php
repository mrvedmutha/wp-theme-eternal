<?php
/**
 * PDP — Editorial feature panel (sticky text + full-bleed image).
 *
 * Args:
 *   $args['feature'] array { image_id, image_url, heading, body }
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

use function WP_Rig\WP_Rig\wp_rig;

defined( 'ABSPATH' ) || exit;

$feature = isset( $args['feature'] ) ? $args['feature'] : array();

if ( empty( $feature ) ) {
	return;
}

$image_id  = isset( $feature['image_id'] ) ? (int) $feature['image_id'] : 0;
$image_url = isset( $feature['image_url'] ) ? $feature['image_url'] : '';
$heading   = isset( $feature['heading'] ) ? $feature['heading'] : '';
$body      = isset( $feature['body'] ) ? $feature['body'] : '';

// Resolve image: prefer attachment, fall back to raw URL.
if ( $image_id > 0 ) {
	$img_tag = wp_get_attachment_image(
		$image_id,
		'full',
		false,
		array(
			'class'   => 'pdp-feature__img',
			'loading' => 'lazy',
			'alt'     => esc_attr( $heading ),
		)
	);
} elseif ( $image_url ) {
	$img_tag = '<img class="pdp-feature__img" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $heading ) . '" loading="lazy">';
} else {
	$img_tag = '';
}

// Convert body text: **bold** → <strong>, double newlines → <p> blocks.
$body_html = $body ? wp_kses_post( wp_rig()->parse_markdown_light( $body ) ) : '';
?>

<div class="pdp-feature">

	<div class="pdp-feature__text">
		<?php if ( $heading ) : ?>
			<h3 class="pdp-feature__headline"><?php echo esc_html( $heading ); ?></h3>
		<?php endif; ?>

		<?php if ( $body_html ) : ?>
			<div class="pdp-feature__body">
				<?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized via wp_kses_post above ?>
			</div>
		<?php endif; ?>
	</div><!-- .pdp-feature__text -->

	<?php if ( $img_tag ) : ?>
		<div class="pdp-feature__image">
			<?php echo $img_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>
		</div>
	<?php endif; ?>

</div><!-- .pdp-feature -->
