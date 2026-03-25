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

	<?php /* ── Search (desktop only — hidden on mobile via CSS) ── */ ?>
	<button
		class="header-utility__btn header-search-trigger header-search--desktop"
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

		<?php /* Dropdown panel — text links, centered, CODE / SYMBOL format */ ?>
		<div class="header-currency__dropdown" hidden>
			<?php if ( class_exists( 'CMC_Currency_Manager' ) ) : ?>
			<div class="header-currency__list">
				<?php
				$enabled = \CMC_Currency_Manager::get_enabled_currencies();
				$active  = \CMC_Currency_Manager::get_active_currency();
				foreach ( $enabled as $code ) :
					$symbol = \CMC_Currency_Manager::get_currency_symbol( $code );
					$label  = $symbol ? $code . ' / ' . $symbol : $code;
					?>
				<a
					href="<?php echo esc_url( add_query_arg( 'currency', $code ) ); ?>"
					class="header-currency__option<?php echo $active === $code ? ' is-active' : ''; ?>"
					<?php echo $active === $code ? 'aria-current="true"' : ''; ?>
				><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php /* ── Account: text on desktop, icon on mobile ── */ ?>
	<a
		href="<?php echo esc_url( $account_url ); ?>"
		class="header-utility__btn header-utility__link header-account"
		aria-label="<?php is_user_logged_in() ? esc_attr_e( 'My account', 'wp-rig' ) : esc_attr_e( 'Login', 'wp-rig' ); ?>"
	>
		<?php /* Desktop: text label only */ ?>
		<span class="header-utility__label header-utility__label--desktop">
			<?php is_user_logged_in() ? esc_html_e( 'ACCOUNT', 'wp-rig' ) : esc_html_e( 'LOGIN', 'wp-rig' ); ?>
		</span>
		<?php /* Mobile: icon only */ ?>
		<svg class="header-utility__icon header-utility__icon--mobile" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
			<path d="M21.3243 20.0618C19.7755 17.3843 17.293 15.5562 14.4168 14.8971C15.7835 14.3372 16.9134 13.32 17.6134 12.0194C18.3135 10.7189 18.5402 9.21558 18.2548 7.7664C17.9694 6.31723 17.1897 5.01211 16.0489 4.07401C14.9081 3.13591 13.4769 2.62305 11.9999 2.62305C10.5229 2.62305 9.09173 3.13591 7.9509 4.07401C6.81007 5.01211 6.03037 6.31723 5.74501 7.7664C5.45966 9.21558 5.68636 10.7189 6.38637 12.0194C7.08639 13.32 8.21629 14.3372 9.58303 14.8971C6.71053 15.5534 4.22428 17.3843 2.67553 20.0618C2.6307 20.1475 2.62071 20.2472 2.64766 20.3401C2.67461 20.433 2.73641 20.5119 2.82015 20.5603C2.90388 20.6087 3.00309 20.6229 3.09703 20.5999C3.19097 20.5769 3.27239 20.5185 3.32428 20.4368C5.15616 17.2671 8.40178 15.3743 11.9999 15.3743C15.598 15.3743 18.8437 17.2671 20.6755 20.4368C20.7084 20.4938 20.7557 20.5411 20.8126 20.574C20.8696 20.6069 20.9341 20.6243 20.9999 20.6243C21.0658 20.6245 21.1306 20.607 21.1874 20.5737C21.2734 20.5239 21.3361 20.4421 21.3617 20.3461C21.3874 20.2501 21.3739 20.1479 21.3243 20.0618ZM6.37491 8.99933C6.37491 7.88681 6.70481 6.79927 7.32289 5.87425C7.94097 4.94922 8.81948 4.22825 9.84731 3.80251C10.8751 3.37677 12.0061 3.26537 13.0973 3.48241C14.1884 3.69946 15.1907 4.23518 15.9774 5.02186C16.7641 5.80853 17.2998 6.8108 17.5168 7.90195C17.7339 8.99309 17.6225 10.1241 17.1967 11.1519C16.771 12.1798 16.05 13.0583 15.125 13.6763C14.2 14.2944 13.1124 14.6243 11.9999 14.6243C10.5086 14.6226 9.07886 14.0294 8.02435 12.9749C6.96983 11.9204 6.37664 10.4906 6.37491 8.99933Z" fill="currentColor"/>
		</svg>
	</a>

	<?php /* ── Cart: text+count on desktop, icon on mobile ── */ ?>
	<a
		href="<?php echo esc_url( $cart_url ); ?>"
		class="header-utility__btn header-utility__link header-cart"
		<?php /* translators: %d = number of items in cart */ ?>
		aria-label="<?php echo esc_attr( sprintf( __( 'Cart, %d items', 'wp-rig' ), $cart_count ) ); ?>"
	>
		<?php /* Desktop: BAG(count) text */ ?>
		<span class="header-utility__label header-utility__label--desktop">
			<?php esc_html_e( 'BAG', 'wp-rig' ); ?>(<span class="header-cart__count"><?php echo esc_html( $cart_count ); ?></span>)
		</span>
		<?php /* Mobile: icon + badge */ ?>
		<span class="header-cart__icon-wrap header-utility__icon--mobile">
			<svg class="header-utility__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
				<path d="M21.2878 5.76C21.2527 5.71779 21.2087 5.68382 21.1589 5.66049C21.1092 5.63717 21.0549 5.62505 21 5.625H5.56312L4.93687 2.1825C4.92109 2.09626 4.87559 2.01827 4.80829 1.9621C4.74098 1.90592 4.65611 1.8751 4.56844 1.875H2.25C2.15054 1.875 2.05516 1.91451 1.98484 1.98484C1.91451 2.05516 1.875 2.15054 1.875 2.25C1.875 2.34946 1.91451 2.44484 1.98484 2.51517C2.05516 2.58549 2.15054 2.625 2.25 2.625H4.25531L4.8825 6.075L6.70219 16.0856C6.78608 16.5431 7.03701 16.9531 7.40625 17.2359C7.01943 17.4853 6.71836 17.8472 6.54364 18.2729C6.36893 18.6987 6.32897 19.1678 6.42915 19.6169C6.52933 20.0661 6.76483 20.4738 7.1039 20.7849C7.44297 21.0961 7.8693 21.2958 8.32541 21.3571C8.78151 21.4184 9.24544 21.3384 9.65465 21.1278C10.0639 20.9173 10.3987 20.5863 10.6139 20.1795C10.8292 19.7728 10.9145 19.3098 10.8584 18.853C10.8023 18.3962 10.6076 17.9676 10.3003 17.625H15.9497C15.6062 18.009 15.4049 18.4994 15.3795 19.014C15.3541 19.5286 15.506 20.0364 15.81 20.4524C16.114 20.8684 16.5515 21.1675 17.0495 21.2997C17.5475 21.4319 18.0758 21.3891 18.5461 21.1786C19.0164 20.9681 19.4001 20.6025 19.6333 20.143C19.8664 19.6835 19.9347 19.1579 19.8269 18.6541C19.7191 18.1503 19.4416 17.6987 19.0408 17.3749C18.64 17.0511 18.1402 16.8746 17.625 16.875H8.54719C8.28387 16.8749 8.02895 16.7824 7.82681 16.6136C7.62466 16.4449 7.48811 16.2106 7.44094 15.9516L7.06594 13.875H18.3844C18.8235 13.8751 19.2487 13.721 19.5859 13.4397C19.923 13.1584 20.1508 12.7676 20.2294 12.3356L21.3694 6.0675C21.3792 6.0134 21.3769 5.95781 21.3628 5.90467C21.3487 5.85153 21.3231 5.80214 21.2878 5.76ZM10.125 19.125C10.125 19.4217 10.037 19.7117 9.8722 19.9584C9.70738 20.205 9.47311 20.3973 9.19902 20.5108C8.92494 20.6244 8.62334 20.6541 8.33236 20.5962C8.04139 20.5383 7.77412 20.3954 7.56434 20.1857C7.35456 19.9759 7.2117 19.7086 7.15382 19.4176C7.09594 19.1267 7.12565 18.8251 7.23918 18.551C7.35271 18.2769 7.54497 18.0426 7.79164 17.8778C8.03832 17.713 8.32833 17.625 8.625 17.625C9.02282 17.625 9.40435 17.783 9.68566 18.0643C9.96696 18.3456 10.125 18.7272 10.125 19.125ZM19.125 19.125C19.125 19.4217 19.037 19.7117 18.8722 19.9584C18.7074 20.205 18.4731 20.3973 18.199 20.5108C17.9249 20.6244 17.6233 20.6541 17.3324 20.5962C17.0414 20.5383 16.7741 20.3954 16.5643 20.1857C16.3546 19.9759 16.2117 19.7086 16.1538 19.4176C16.0959 19.1267 16.1256 18.8251 16.2392 18.551C16.3527 18.2769 16.545 18.0426 16.7916 17.8778C17.0383 17.713 17.3283 17.625 17.625 17.625C18.0228 17.625 18.4044 17.783 18.6857 18.0643C18.967 18.3456 19.125 18.7272 19.125 19.125ZM19.4916 12.2016C19.4444 12.4608 19.3077 12.6952 19.1053 12.8639C18.903 13.0327 18.6478 13.1251 18.3844 13.125H6.92625L5.69906 6.375H20.5509L19.4916 12.2016Z" fill="currentColor"/>
			</svg>
			<?php if ( $cart_count > 0 ) : ?>
			<span class="header-cart__badge" aria-hidden="true"><?php echo esc_html( $cart_count ); ?></span>
			<?php endif; ?>
		</span>
	</a>

</div>
