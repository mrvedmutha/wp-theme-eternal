<?php
/**
 * Template part: Utility navigation (right side of header).
 *
 * Outputs: Search, Currency Switcher, Login, Cart.
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

// Get active currency label from plugin (safe fallback if plugin inactive).
$currency_code   = '';
$currency_symbol = '';
if ( class_exists( 'CMC_Currency_Manager' ) ) {
	$currency_code   = \CMC_Currency_Manager::get_active_currency();
	$currency_symbol = \CMC_Currency_Manager::get_currency_symbol( $currency_code );
}

// Cart count via Header component template tag.
$cart_count = wp_rig()->get_header_cart_count();

// WooCommerce account URL.
$account_url = function_exists( 'wc_get_account_endpoint_url' )
	? wc_get_account_endpoint_url( 'dashboard' )
	: wp_login_url();

// WooCommerce cart URL.
$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
?>
<div class="header-utility" role="navigation" aria-label="<?php esc_attr_e( 'Utility menu', 'wp-rig' ); ?>">

	<?php /* ── Search ── */ ?>
	<button
		class="header-utility__btn header-search-trigger"
		aria-label="<?php esc_attr_e( 'Open search', 'wp-rig' ); ?>"
		aria-expanded="false"
		type="button"
	>
		<span class="header-utility__label"><?php esc_html_e( 'SEARCH', 'wp-rig' ); ?></span>
		<svg class="header-utility__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
			<circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="1.5"/>
			<path d="M13 13L17.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
		</svg>
	</button>

	<?php /* ── Currency Switcher ── */ ?>
	<?php if ( $currency_code ) : ?>
	<div class="header-utility__item header-currency">
		<button
			class="header-utility__btn header-currency__trigger"
			<?php /* translators: %s = currency code (e.g., USD, EUR) */ ?>
			aria-label="<?php echo esc_attr( sprintf( __( 'Switch currency. Currently %s', 'wp-rig' ), $currency_code ) ); ?>"
			aria-expanded="false"
			type="button"
		>
			<span class="header-utility__label">
				<?php echo esc_html( $currency_code ); ?>
				<?php if ( $currency_symbol ) : ?>
					/ <?php echo esc_html( $currency_symbol ); ?>
				<?php endif; ?>
			</span>
			<span class="header-currency__icons" aria-hidden="true">
				<?php /* Globe icon — visible by default */ ?>
				<svg class="header-currency__icon header-currency__icon--globe" width="20" height="20" viewBox="0 0 20 20" fill="none" focusable="false">
					<circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.5"/>
					<ellipse cx="10" cy="10" rx="3" ry="7.5" stroke="currentColor" stroke-width="1.5"/>
					<path d="M2.5 10H17.5" stroke="currentColor" stroke-width="1.5"/>
					<path d="M3.5 6.5H16.5M3.5 13.5H16.5" stroke="currentColor" stroke-width="1.25"/>
				</svg>
				<?php /* Chevron — visible on hover (CSS swap) */ ?>
				<svg class="header-currency__icon header-currency__icon--chevron" width="20" height="20" viewBox="0 0 20 20" fill="none" focusable="false">
					<path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</span>
		</button>

		<?php /* Dropdown panel — rendered by plugin, hidden by default */ ?>
		<div class="header-currency__dropdown" hidden>
			<?php
			if ( class_exists( 'CMC_Currency_Switcher' ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo \CMC_Currency_Switcher::get_instance()->render_switcher( 'buttons' );
			}
			?>
		</div>
	</div>
	<?php endif; ?>

	<?php /* ── Login / Account ── */ ?>
	<a
		href="<?php echo esc_url( $account_url ); ?>"
		class="header-utility__btn header-utility__link header-account"
		aria-label="<?php esc_attr_e( 'My account', 'wp-rig' ); ?>"
	>
		<span class="header-utility__label">
			<?php
			if ( is_user_logged_in() ) {
				esc_html_e( 'ACCOUNT', 'wp-rig' );
			} else {
				esc_html_e( 'LOGIN', 'wp-rig' );
			}
			?>
		</span>
	</a>

	<?php /* ── Cart ── */ ?>
	<a
		href="<?php echo esc_url( $cart_url ); ?>"
		class="header-utility__btn header-utility__link header-cart"
		<?php /* translators: %d = number of items in cart */ ?>
		aria-label="<?php echo esc_attr( sprintf( __( 'Cart, %d items', 'wp-rig' ), $cart_count ) ); ?>"
	>
		<span class="header-utility__label">
			<?php esc_html_e( 'BAG', 'wp-rig' ); ?>(<!-- no space --><span class="header-cart__count" <?php /* translators: %d = number of items in cart */ ?>aria-label="<?php echo esc_attr( sprintf( __( '%d items in cart', 'wp-rig' ), $cart_count ) ); ?>"><?php echo esc_html( $cart_count ); ?></span>)
		</span>
	</a>

</div>
