<?php
/**
 * Template part: Site Footer
 *
 * Two sections:
 *   1. Newsletter banner — atmospheric background image + email capture form
 *   2. Main footer       — black bg, logo/tagline, nav columns, bottom bar
 *
 * Settings pulled from Customizer (footer section):
 *   - newsletter_bg_image   Background image for the newsletter banner
 *   - newsletter_subtext    Subtext below the newsletter heading
 *   - payment_icon_{1-4}    Payment icon media URLs (renders only if set)
 *   - payment_icon_{1-4}_alt  Alt text for each payment icon
 *
 * Currency switcher reuses the CMC plugin identical to the header.
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

// ── Customizer values ────────────────────────────────────────────────────────
// WP_Customize_Media_Control stores attachment IDs — convert to URLs.
$newsletter_bg_id  = get_theme_mod( 'newsletter_bg_image', '' );
$newsletter_bg_url = $newsletter_bg_id ? wp_get_attachment_url( (int) $newsletter_bg_id ) : '';

$newsletter_sub = get_theme_mod(
	'newsletter_subtext',
	'Subscribe and embark on a timeless beauty journey and enjoy 15% off your first purchase* above CHF 350.'
);

$payment_icons = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$icon_id  = get_theme_mod( "payment_icon_{$i}", '' );
	$icon_url = $icon_id ? wp_get_attachment_url( (int) $icon_id ) : '';
	if ( $icon_url ) {
		$payment_icons[] = array(
			'url' => $icon_url,
			'alt' => get_theme_mod( "payment_icon_{$i}_alt", '' ),
		);
	}
}

// ── Currency switcher (same source as header) ─────────────────────────────────
$currency_code   = '';
$currency_symbol = '';
if ( class_exists( 'CMC_Currency_Manager' ) ) {
	$currency_code   = \CMC_Currency_Manager::get_active_currency();
	$currency_symbol = \CMC_Currency_Manager::get_currency_symbol( $currency_code );
}

// ── Logo ─────────────────────────────────────────────────────────────────────
$custom_logo_id = get_theme_mod( 'custom_logo' );
$logo_html      = '';
if ( $custom_logo_id ) {
	$logo_image = wp_get_attachment_image(
		$custom_logo_id,
		'full',
		false,
		array(
			'class' => 'footer-brand__logo-img',
			'alt'   => get_bloginfo( 'name' ),
		)
	);
	$logo_html  = '<a href="' . esc_url( home_url( '/' ) ) . '" class="footer-brand__logo-link" aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . $logo_image . '</a>';
}

$tagline = get_bloginfo( 'description' );

// ── REST endpoint URL for newsletter form ────────────────────────────────────
$newsletter_endpoint = rest_url( 'eternal/v1/newsletter/subscribe' );
$newsletter_nonce    = wp_create_nonce( 'wp_rest' );
?>


<?php
/*
 * ════════════════════════════════════════════════════════════════════
 * OUTER WRAPPER — atmospheric bg spans both newsletter + footer-main
 * ════════════════════════════════════════════════════════════════════
 */
?>
<div
	class="footer-outer"
	<?php if ( $newsletter_bg_url ) : ?>
		style="--footer-bg: url('<?php echo esc_url( $newsletter_bg_url ); ?>');"
	<?php endif; ?>
>


<?php
/*
 * ════════════════════════════════════════════════════════════════════
 * SECTION 1 — Newsletter Banner
 * ════════════════════════════════════════════════════════════════════
 */
?>
<section
	class="footer-newsletter"
	aria-label="<?php esc_attr_e( 'Newsletter signup', 'wp-rig' ); ?>"
>
	<div class="footer-newsletter__inner">
		<div class="footer-newsletter__text">
			<p class="footer-newsletter__heading"><?php esc_html_e( 'JOIN OUR NEWSLETTER', 'wp-rig' ); ?></p>
			<?php if ( $newsletter_sub ) : ?>
				<p class="footer-newsletter__sub"><?php echo esc_html( $newsletter_sub ); ?></p>
			<?php endif; ?>
		</div>

		<form
			class="footer-newsletter__form js-newsletter-form"
			id="footer-newsletter-form"
			novalidate
			data-endpoint="<?php echo esc_url( $newsletter_endpoint ); ?>"
			data-nonce="<?php echo esc_attr( $newsletter_nonce ); ?>"
		>
			<div class="footer-newsletter__field-wrap">
				<input
					class="footer-newsletter__input"
					type="email"
					name="email"
					id="footer-newsletter-email"
					placeholder="<?php esc_attr_e( 'Your email address', 'wp-rig' ); ?>"
					autocomplete="email"
					required
					aria-label="<?php esc_attr_e( 'Email address', 'wp-rig' ); ?>"
				/>
				<button
					class="footer-newsletter__submit"
					type="submit"
					aria-label="<?php esc_attr_e( 'Sign up for newsletter', 'wp-rig' ); ?>"
				>
					<span class="footer-newsletter__submit-text"><?php esc_html_e( 'SIGN UP', 'wp-rig' ); ?></span>
					<span class="footer-newsletter__submit-rule" aria-hidden="true"></span>
				</button>
			</div>
			<p class="footer-newsletter__message js-newsletter-msg" aria-live="polite" hidden></p>
		</form>
	</div>
</section>


<?php
/*
 * ════════════════════════════════════════════════════════════════════
 * SECTION 2 — Main Footer
 * ════════════════════════════════════════════════════════════════════
 */
?>
<div class="footer-main">

	<?php /* ── Top row: logo + nav columns ── */ ?>
	<div class="footer-main__top">

		<?php /* Logo + tagline */ ?>
		<div class="footer-brand">
			<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $tagline ) : ?>
				<p class="footer-brand__tagline"><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
		</div>

		<?php /* Nav columns */ ?>
		<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'wp-rig' ); ?>">

			<?php
			$footer_menus = array(
				'footer_eternal'          => esc_html__( 'ETERNAL', 'wp-rig' ),
				'footer_shop'             => esc_html__( 'SHOP', 'wp-rig' ),
				'footer_customer_service' => esc_html__( 'CUSTOMER SERVICE', 'wp-rig' ),
				'footer_follow_us'        => esc_html__( 'FOLLOW US', 'wp-rig' ),
			);

			foreach ( $footer_menus as $location => $default_label ) :
				if ( ! has_nav_menu( $location ) ) {
					continue;
				}

				// Use the assigned menu's name as the column heading.
				$menu_locations = get_nav_menu_locations();
				$menu_obj       = isset( $menu_locations[ $location ] )
					? wp_get_nav_menu_object( $menu_locations[ $location ] )
					: null;
				$column_heading = ( $menu_obj && ! empty( $menu_obj->name ) )
					? $menu_obj->name
					: $default_label;
				?>
				<div class="footer-nav__column">
					<p class="footer-nav__heading"><?php echo esc_html( $column_heading ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $location,
							'container'      => false,
							'menu_class'     => 'footer-nav__list',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</div>
			<?php endforeach; ?>

		</nav>
	</div>

	<?php /* ── Bottom bar ── */ ?>
	<div class="footer-bar">

		<div class="footer-bar__left">
			<span class="footer-bar__copyright">
				<?php
				printf(
					/* translators: %s: current year */
					esc_html__( '© %s Eternallabs', 'wp-rig' ),
					esc_html( gmdate( 'Y' ) )
				);
				?>
			</span>

			<?php
			if ( has_nav_menu( 'footer_legal' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer_legal',
						'container'      => false,
						'menu_class'     => 'footer-bar__legal-list',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			}
			?>

			<?php if ( $currency_code ) : ?>
			<div class="footer-bar__currency">
				<span class="footer-bar__currency-label">
					<?php echo esc_html( $currency_code ); ?>
					<?php if ( $currency_symbol ) : ?>
						/ <?php echo esc_html( $currency_symbol ); ?>
					<?php endif; ?>
				</span>
				<svg class="footer-bar__globe" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
					<circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.5"/>
					<ellipse cx="10" cy="10" rx="3" ry="7.5" stroke="currentColor" stroke-width="1.5"/>
					<path d="M2.5 10H17.5" stroke="currentColor" stroke-width="1.5"/>
					<path d="M3.5 6.5H16.5M3.5 13.5H16.5" stroke="currentColor" stroke-width="1.25"/>
				</svg>
			</div>
			<?php endif; ?>
		</div>

		<div class="footer-bar__right">

			<?php if ( ! empty( $payment_icons ) ) : ?>
			<div class="footer-bar__payment" aria-label="<?php esc_attr_e( 'Payment methods', 'wp-rig' ); ?>">
				<?php foreach ( $payment_icons as $icon ) : ?>
					<img
						class="footer-bar__payment-icon"
						src="<?php echo esc_url( $icon['url'] ); ?>"
						alt="<?php echo esc_attr( $icon['alt'] ); ?>"
						loading="lazy"
					/>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<a
				href="https://wings.design"
				class="footer-bar__wings-credit"
				target="_blank"
				rel="noopener noreferrer"
			><?php esc_html_e( 'Design by', 'wp-rig' ); ?> <span class="footer-bar__wings-link"><?php esc_html_e( 'WINGS', 'wp-rig' ); ?></span></a>

		</div>
	</div>

</div><!-- .footer-main -->
</div><!-- .footer-outer -->


<?php
/*
 * ════════════════════════════════════════════════════════════════════
 * MOBILE / TABLET FOOTER  (shown ≤1024px, hidden on desktop)
 * ════════════════════════════════════════════════════════════════════
 */
?>
<div class="footer-mobile">

	<?php /* ── Newsletter ── */ ?>
	<div class="footer-mobile__newsletter">
		<p class="footer-mobile__newsletter-heading"><?php esc_html_e( 'JOIN OUR NEWSLETTER', 'wp-rig' ); ?></p>
		<p class="footer-mobile__newsletter-sub"><?php esc_html_e( 'Be the first to receive the latest news from the House of Eternal Laboratories', 'wp-rig' ); ?></p>
		<form
			class="footer-mobile__form js-newsletter-form"
			id="footer-mobile-newsletter-form"
			novalidate
			data-endpoint="<?php echo esc_url( $newsletter_endpoint ); ?>"
			data-nonce="<?php echo esc_attr( $newsletter_nonce ); ?>"
		>
			<div class="footer-mobile__field-wrap">
				<input
					class="footer-mobile__input"
					type="email"
					name="email"
					placeholder="<?php esc_attr_e( 'Email address', 'wp-rig' ); ?>"
					autocomplete="email"
					required
					aria-label="<?php esc_attr_e( 'Email address', 'wp-rig' ); ?>"
				/>
				<button
					class="footer-mobile__submit"
					type="submit"
					aria-label="<?php esc_attr_e( 'Sign up for newsletter', 'wp-rig' ); ?>"
				>
					<span class="footer-mobile__submit-text"><?php esc_html_e( 'SIGN UP', 'wp-rig' ); ?></span>
					<span class="footer-mobile__submit-rule" aria-hidden="true"></span>
				</button>
			</div>
			<p class="footer-mobile__msg js-newsletter-msg" aria-live="polite" hidden></p>
		</form>
	</div>

	<?php /* ── Accordion nav ── */ ?>
	<nav class="footer-mobile__nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'wp-rig' ); ?>">
		<?php
		foreach ( $footer_menus as $location => $default_label ) :
			if ( ! has_nav_menu( $location ) ) {
				continue;
			}
			$menu_locations_mobile = get_nav_menu_locations();
			$menu_obj_mobile       = isset( $menu_locations_mobile[ $location ] )
				? wp_get_nav_menu_object( $menu_locations_mobile[ $location ] )
				: null;
			$col_heading_mobile    = ( $menu_obj_mobile && ! empty( $menu_obj_mobile->name ) )
				? $menu_obj_mobile->name
				: $default_label;
			?>
			<div class="footer-mobile__acc-item">
				<button
					class="footer-mobile__acc-trigger"
					type="button"
					aria-expanded="false"
				>
					<span><?php echo esc_html( $col_heading_mobile ); ?></span>
					<svg class="footer-mobile__acc-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
						<path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
				<div class="footer-mobile__acc-body">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => $location,
							'container'      => false,
							'menu_class'     => 'footer-mobile__menu-list',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</div>
			</div>
		<?php endforeach; ?>
	</nav>

	<?php /* ── Bottom ── */ ?>
	<div class="footer-mobile__bottom">

		<?php if ( $logo_html ) : ?>
		<div class="footer-mobile__logo">
			<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php endif; ?>

		<?php if ( has_nav_menu( 'footer_legal' ) ) : ?>
		<nav class="footer-mobile__legal-nav" aria-label="<?php esc_attr_e( 'Legal links', 'wp-rig' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_legal',
					'container'      => false,
					'menu_class'     => 'footer-mobile__legal-list',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
		<?php endif; ?>

		<p class="footer-mobile__copyright">
			<?php
			printf(
				/* translators: %s: current year */
				esc_html__( '© %s Eternallabs', 'wp-rig' ),
				esc_html( gmdate( 'Y' ) )
			);
			?>
		</p>

		<a
			href="https://wings.design"
			class="footer-mobile__wings"
			target="_blank"
			rel="noopener noreferrer"
		><?php esc_html_e( 'Design by', 'wp-rig' ); ?> <span class="footer-mobile__wings-name"><?php esc_html_e( 'WINGS', 'wp-rig' ); ?></span></a>

	</div>

</div><!-- .footer-mobile -->
