<?php
/**
 * Category Split block — frontend render template.
 *
 * Variables provided by WordPress at include-time:
 *   $attributes (array)    Block attributes from block.json + editor input.
 *   $content    (string)   Inner blocks HTML (unused).
 *   $block      (WP_Block) Block instance.
 *
 * @package wp_rig
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use function WP_Rig\WP_Rig\wp_rig;

$attributes = is_array( $attributes ?? null ) ? $attributes : array();

// Panel 1.
$p1_image_id  = (int) ( $attributes['panel1ImageId'] ?? 0 );
$p1_image_url = $attributes['panel1ImageUrl'] ?? '';
$p1_name      = $attributes['panel1Name'] ?? '';
$p1_subtitle  = $attributes['panel1Subtitle'] ?? '';
$p1_discover  = $attributes['panel1DiscoverUrl'] ?? '/';

// Panel 2.
$p2_image_id  = (int) ( $attributes['panel2ImageId'] ?? 0 );
$p2_image_url = $attributes['panel2ImageUrl'] ?? '';
$p2_name      = $attributes['panel2Name'] ?? '';
$p2_subtitle  = $attributes['panel2Subtitle'] ?? '';
$p2_discover  = $attributes['panel2DiscoverUrl'] ?? '/';

// Prefer resolved WP attachment URLs so media library moves don't break images.
if ( $p1_image_id ) {
	$resolved = wp_get_attachment_image_url( $p1_image_id, 'full' );
	if ( $resolved ) {
		$p1_image_url = $resolved;
	}
}

if ( $p2_image_id ) {
	$resolved = wp_get_attachment_image_url( $p2_image_id, 'full' );
	if ( $resolved ) {
		$p2_image_url = $resolved;
	}
}

$wrapper_attrs = wp_rig()->block_wrapper_attributes( array( 'category-split' ), $attributes );

// Closure avoids fatal "Cannot redeclare" when render.php is included
// multiple times on the same page (e.g. block + ServerSideRender preview).
$render_panel = static function ( string $image_url, string $name, string $subtitle, string $discover ): void {
	?>
	<div class="category-split__panel">
		<?php if ( $image_url ) : ?>
		<img
			class="category-split__image"
			src="<?php echo esc_url( $image_url ); ?>"
			alt=""
			aria-hidden="true"
			decoding="async"
			loading="lazy"
		>
		<?php endif; ?>

		<div class="category-split__content-track">
			<div class="category-split__content">
				<div class="category-split__meta">
					<?php if ( $name ) : ?>
					<p class="category-split__name"><?php echo esc_html( $name ); ?></p>
					<?php endif; ?>
					<?php if ( $subtitle ) : ?>
					<p class="category-split__subtitle"><?php echo esc_html( $subtitle ); ?></p>
					<?php endif; ?>
				</div>

				<a class="category-split__discover" href="<?php echo esc_url( $discover ); ?>">
					<span class="category-split__discover-label"><?php esc_html_e( 'DISCOVER', 'wp-rig' ); ?></span>
					<span class="category-split__discover-rule" aria-hidden="true"></span>
				</a>
			</div>
		</div>
	</div>
	<?php
};

?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php $render_panel( $p1_image_url, $p1_name, $p1_subtitle, $p1_discover ); ?>
	<?php $render_panel( $p2_image_url, $p2_name, $p2_subtitle, $p2_discover ); ?>
</section>
