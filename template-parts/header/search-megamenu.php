<?php
/**
 * Template part: Search megamenu overlay
 *
 * Full-width panel that expands below the header when the user activates
 * the SEARCH button. Controlled entirely by search-megamenu.js.
 *
 * Default state  — recommended search terms + 2 WC product cards (PHP-rendered)
 * Typing state   — skeleton cards replace products → then live results
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

?>
<div
	id="search-megamenu"
	class="search-megamenu"
	role="dialog"
	aria-modal="false"
	aria-label="<?php esc_attr_e( 'Search', 'wp-rig' ); ?>"
	aria-hidden="true"
	hidden
>
	<div class="search-megamenu__inner">

		<?php /* ── Input row ─────────────────────────────────────────── */ ?>
		<div class="search-megamenu__input-row">
			<label for="search-megamenu-input" class="screen-reader-text">
				<?php esc_html_e( 'Search products', 'wp-rig' ); ?>
			</label>
			<input
				id="search-megamenu-input"
				class="search-megamenu__input"
				type="search"
				autocomplete="off"
				autocorrect="off"
				spellcheck="false"
				placeholder="<?php esc_attr_e( 'What are you looking for?', 'wp-rig' ); ?>"
			>
			<button
				id="search-megamenu-close"
				class="search-megamenu__close"
				type="button"
				aria-label="<?php esc_attr_e( 'Close search', 'wp-rig' ); ?>"
			>
				<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M14.1405 13.6099C14.2109 13.6803 14.2504 13.7757 14.2504 13.8752C14.2504 13.9747 14.2109 14.0702 14.1405 14.1405C14.0702 14.2109 13.9747 14.2504 13.8752 14.2504C13.7757 14.2504 13.6803 14.2109 13.6099 14.1405L7.12521 7.65583L0.640521 14.1405C0.570156 14.2109 0.47472 14.2504 0.375208 14.2504C0.275697 14.2504 0.180261 14.2109 0.109896 14.1405C0.0395309 14.0702 1.96161e-09 13.9747 0 13.8752C-1.96161e-09 13.7757 0.0395306 13.6803 0.109896 13.6099L6.59458 7.12521L0.109896 0.640521C0.0395306 0.570156 0 0.47472 0 0.375208C0 0.275697 0.0395306 0.180261 0.109896 0.109896C0.180261 0.0395306 0.275697 0 0.375208 0C0.47472 0 0.570156 0.0395306 0.640521 0.109896L7.12521 6.59458L13.6099 0.109896C13.6447 0.0750545 13.6861 0.0474169 13.7316 0.0285609C13.7771 0.00970488 13.8259 9.7129e-10 13.8752 0C13.9245 -9.71289e-10 13.9733 0.00970488 14.0188 0.0285609C14.0643 0.0474169 14.1057 0.0750545 14.1405 0.109896C14.1754 0.144737 14.203 0.1861 14.2219 0.231622C14.2407 0.277145 14.2504 0.325935 14.2504 0.375208C14.2504 0.424482 14.2407 0.473272 14.2219 0.518795C14.203 0.564317 14.1754 0.60568 14.1405 0.640521L7.65583 7.12521L14.1405 13.6099Z" fill="currentColor"/>
				</svg>
			</button>
		</div>

		<?php /* ── Divider ──────────────────────────────────────────── */ ?>
		<hr class="search-megamenu__rule" aria-hidden="true">

		<?php /* ── Two-column layout: always visible ─────────────────── */ ?>
		<div id="search-megamenu-default" class="search-megamenu__default">

			<?php /* Left: static recommended searches */ ?>
			<div class="search-megamenu__col search-megamenu__col--searches">
				<p class="search-megamenu__label">
					<?php esc_html_e( 'RECOMMENDED SEARCHES', 'wp-rig' ); ?>
				</p>
				<ul class="search-megamenu__search-list" role="list">
					<?php
					$recommended_searches = apply_filters(
						'wp_rig_search_recommended_terms',
						array( 'ARGAN OIL', 'EMOLLIENT OIL', 'ROSEMARY OIL', 'ETERNAL YOUTH' )
					);
					foreach ( $recommended_searches as $search_term ) :
						?>
						<li>
							<a
								href="<?php echo esc_url( home_url( '/?post_type=product&s=' . rawurlencode( $search_term ) ) ); ?>"
								class="search-megamenu__search-link"
							>
								<?php echo esc_html( $search_term ); ?>
							</a>
						</li>
						<?php endforeach; ?>
				</ul>
			</div>

			<?php /* Right: product cards (PHP default → JS skeleton → JS results) */ ?>
			<div class="search-megamenu__col search-megamenu__col--products">
				<p class="search-megamenu__label" id="search-megamenu-col-label">
					<?php esc_html_e( 'RECOMMENDED PRODUCTS', 'wp-rig' ); ?>
				</p>

				<?php /* Grid — JS replaces innerHTML with skeleton or results */ ?>
				<div
					id="search-megamenu-grid"
					class="search-megamenu__products-grid search-megamenu__products-grid--default"
					aria-live="polite"
					aria-atomic="true"
				>
					<?php
					if ( function_exists( 'WC' ) ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo wp_rig()->get_search_recommended_products();
					}
					?>
				</div>

				<?php /* View More — shown by JS when there are live results */ ?>
				<a
					id="search-megamenu-view-more"
					class="search-megamenu__view-more"
					href="#"
					hidden
				>
					<?php esc_html_e( 'View More', 'wp-rig' ); ?>
				</a>

				<?php /* No-results message — shown by JS */ ?>
				<p
					id="search-megamenu-empty"
					class="search-megamenu__empty-msg"
					hidden
				>
					<?php esc_html_e( 'No products found. Try a different search.', 'wp-rig' ); ?>
				</p>
			</div>

		</div><!-- /#search-megamenu-default -->

	</div><!-- /.search-megamenu__inner -->
</div><!-- #search-megamenu -->
