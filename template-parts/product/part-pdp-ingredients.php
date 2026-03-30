<?php
/**
 * PDP — Key ingredients section.
 *
 * Only renders if at least one ingredient is configured.
 *
 * Args:
 *   $args['meta'] array — from wp_rig()->get_product_meta()
 *
 * @package wp_rig
 */

defined( 'ABSPATH' ) || exit;

$meta          = isset( $args['meta'] ) ? $args['meta'] : array();
$ingredients   = isset( $meta['key_ingredients'] ) ? $meta['key_ingredients'] : array();
$section_title = isset( $meta['ingredients_title'] ) ? $meta['ingredients_title'] : '';

if ( empty( $ingredients ) ) {
	return;
}

$count         = count( $ingredients );
$has_title     = ! empty( $section_title );
$section_class = 'pdp-ingredients' . ( $has_title ? ' pdp-ingredients--tinted' : '' );
?>

<section class="<?php echo esc_attr( $section_class ); ?>">

	<?php if ( $has_title ) : ?>
		<h2 class="pdp-ingredients__title"><?php echo esc_html( $section_title ); ?></h2>
	<?php endif; ?>

	<div class="pdp-ingredients__grid pdp-ingredients__grid--<?php echo esc_attr( $count ); ?>col">

		<?php
		foreach ( $ingredients as $ingredient ) :
			$image_id  = isset( $ingredient['image_id'] ) ? (int) $ingredient['image_id'] : 0;
			$image_url = isset( $ingredient['image_url'] ) ? $ingredient['image_url'] : '';
			$name      = isset( $ingredient['name'] ) ? $ingredient['name'] : '';
			$desc      = isset( $ingredient['description'] ) ? $ingredient['description'] : '';
			?>
			<div class="pdp-ingredients__card">

				<div class="pdp-ingredients__img">
					<?php if ( $image_id > 0 ) : ?>
						<?php
						echo wp_get_attachment_image(
							$image_id,
							'large',
							false,
							array(
								'loading' => 'lazy',
								'alt'     => esc_attr( $name ),
							)
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?>
					<?php elseif ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
					<?php endif; ?>
					<!-- If neither: placeholder bg from CSS -->
				</div><!-- .pdp-ingredients__img -->

				<?php if ( $name ) : ?>
					<p class="pdp-ingredients__name"><?php echo esc_html( $name ); ?></p>
				<?php endif; ?>

				<?php if ( $desc ) : ?>
					<p class="pdp-ingredients__desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>

			</div><!-- .pdp-ingredients__card -->
		<?php endforeach; ?>

	</div><!-- .pdp-ingredients__grid -->

</section><!-- .pdp-ingredients -->
