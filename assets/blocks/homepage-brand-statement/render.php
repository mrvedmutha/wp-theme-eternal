<?php
/**
 * Homepage Brand Statement block — frontend render template.
 *
 * Variables provided by WordPress at include-time:
 *   $attributes (array)    Block attributes from block.json + editor input.
 *   $content    (string)   Inner blocks HTML (logo SVG/image added via editor).
 *   $block      (WP_Block) Block instance.
 *
 * @package wp_rig
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes     = is_array( $attributes ?? null ) ? $attributes : array();
$statement_text = $attributes['statementText'] ?? __( 'Manufactured in Switzerland and developed with globally sourced ingredients, Eternal Labs formulations are meticulously crafted to support vitality, cellular health, and visible skin wellbeing.', 'wp-rig' );
?>
<section class="homepage-brand-statement">
	<div class="homepage-brand-statement__spacer" aria-hidden="true"></div>
	<div class="homepage-brand-statement__sticky">

		<?php if ( ! empty( $content ) ) : ?>
		<div class="homepage-brand-statement__logo" aria-hidden="true">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inner blocks HTML ?>
		</div>
		<?php endif; ?>

		<p class="homepage-brand-statement__text">
			<?php echo esc_html( $statement_text ); ?>
		</p>

	</div>
</section>
