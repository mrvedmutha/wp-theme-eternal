<?php
/**
 * PDP — Accordion: Description / Ingredients & Safety / How to Apply.
 *
 * Renders expandable product information sections.
 *
 * Args:
 *   $args['product'] WC_Product
 *   $args['meta']    array
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

defined( 'ABSPATH' ) || exit;

/**
 * Product object.
 *
 * @var \WC_Product $product
 */
$product = isset( $args['product'] ) ? $args['product'] : wc_get_product( get_the_ID() );
$meta    = isset( $args['meta'] ) ? $args['meta'] : array();

if ( ! $product ) {
	return;
}

$description  = $product->get_description();
$ingredients  = ! empty( $meta['buy_box_ingredients'] ) ? $meta['buy_box_ingredients'] : '';
$caution      = ! empty( $meta['buy_box_caution'] ) ? $meta['buy_box_caution'] : '';
$how_to       = ! empty( $meta['buy_box_how_to_apply'] ) ? $meta['buy_box_how_to_apply'] : '';
$bonus_tip    = ! empty( $meta['buy_box_bonus_tip'] ) ? $meta['buy_box_bonus_tip'] : '';
$notes_top    = ! empty( $meta['notes_top'] ) ? $meta['notes_top'] : '';
$notes_middle = ! empty( $meta['notes_middle'] ) ? $meta['notes_middle'] : '';
$notes_base   = ! empty( $meta['notes_base'] ) ? $meta['notes_base'] : '';

// Determine "How to Apply" vs "How to Use" label.
$how_to_label = ( str_starts_with( trim( $how_to ), '**Use:**' ) || str_starts_with( trim( $how_to ), 'Use:' ) )
	? __( 'HOW TO USE', 'wp-rig' )
	: __( 'HOW TO APPLY', 'wp-rig' );
?>

<div class="pdp-accordion" data-accordion>

	<!-- Panel 1: Description (open by default) -->
	<div class="pdp-accordion__item">
		<button
			class="pdp-accordion__header"
			aria-expanded="true"
			aria-controls="pdp-accordion-description"
			data-accordion-header
		>
			<span class="pdp-accordion__label"><?php esc_html_e( 'DESCRIPTION', 'wp-rig' ); ?></span>
			<span class="pdp-accordion__icon" aria-hidden="true">−</span>
		</button>

		<div id="pdp-accordion-description" class="pdp-accordion__body" data-accordion-body>
			<?php if ( $description ) : ?>
				<div class="pdp-accordion__description">
					<?php echo wp_kses_post( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $notes_top && $notes_middle && $notes_base ) : ?>
				<div class="pdp-accordion__notes">
					<p class="pdp-accordion__notes-heading"><?php esc_html_e( 'Key Notes', 'wp-rig' ); ?></p>
					<p class="pdp-accordion__note">
						<strong><?php esc_html_e( 'Top:', 'wp-rig' ); ?></strong>
						<?php echo esc_html( $notes_top ); ?>
					</p>
					<p class="pdp-accordion__note">
						<strong><?php esc_html_e( 'Middle:', 'wp-rig' ); ?></strong>
						<?php echo esc_html( $notes_middle ); ?>
					</p>
					<p class="pdp-accordion__note">
						<strong><?php esc_html_e( 'Base:', 'wp-rig' ); ?></strong>
						<?php echo esc_html( $notes_base ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div><!-- #pdp-accordion-description -->
	</div><!-- .pdp-accordion__item -->

	<hr class="pdp-divider">

	<!-- Panel 2: Ingredients & Safety (closed) -->
	<?php if ( $ingredients || $caution ) : ?>
	<div class="pdp-accordion__item">
		<button
			class="pdp-accordion__header"
			aria-expanded="false"
			aria-controls="pdp-accordion-ingredients"
			data-accordion-header
		>
			<span class="pdp-accordion__label"><?php esc_html_e( 'INGREDIENTS AND SAFETY', 'wp-rig' ); ?></span>
			<span class="pdp-accordion__icon" aria-hidden="true">+</span>
		</button>

		<div id="pdp-accordion-ingredients" class="pdp-accordion__body" hidden data-accordion-body>
			<?php if ( $ingredients ) : ?>
				<div class="pdp-accordion__ingredients">
					<?php echo wp_kses_post( wp_rig()->parse_markdown_light( $ingredients ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $caution ) : ?>
				<p class="pdp-accordion__caution">
					<?php echo esc_html( $caution ); ?>
				</p>
			<?php endif; ?>
		</div><!-- #pdp-accordion-ingredients -->
	</div><!-- .pdp-accordion__item -->

	<hr class="pdp-divider">
	<?php endif; ?>

	<!-- Panel 3: How to Apply / How to Use (closed) -->
	<?php if ( $how_to ) : ?>
	<div class="pdp-accordion__item">
		<button
			class="pdp-accordion__header"
			aria-expanded="false"
			aria-controls="pdp-accordion-how-to"
			data-accordion-header
		>
			<span class="pdp-accordion__label"><?php echo esc_html( $how_to_label ); ?></span>
			<span class="pdp-accordion__icon" aria-hidden="true">+</span>
		</button>

		<div id="pdp-accordion-how-to" class="pdp-accordion__body" hidden data-accordion-body>
			<div class="pdp-accordion__how-to">
				<?php echo wp_kses_post( wp_rig()->parse_markdown_light( $how_to ) ); ?>
			</div>

			<?php if ( $bonus_tip ) : ?>
				<div class="pdp-accordion__tip">
					<p class="pdp-accordion__tip-label"><?php esc_html_e( 'Bonus Tip', 'wp-rig' ); ?></p>
					<p class="pdp-accordion__tip-text"><?php echo esc_html( $bonus_tip ); ?></p>
				</div>
			<?php endif; ?>
		</div><!-- #pdp-accordion-how-to -->
	</div><!-- .pdp-accordion__item -->

	<hr class="pdp-divider">
	<?php endif; ?>

</div><!-- .pdp-accordion -->
