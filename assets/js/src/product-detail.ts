/**
 * Product Detail Page (PDP) — client-side interactions.
 *
 * Modules:
 *   1. Gallery thumbnail switching
 *   2. Accordion open / close
 *   3. Quantity stepper
 *   4. Variant switching (variable products, WC AJAX)
 *   5. Subscription purchase mode toggle + plan select
 *   6. Add-to-bag form (subscription months hidden input)
 */

declare const EternalPDP: {
	ajaxUrl: string;
	nonce: string;
	productId: number;
	variations: Array<{
		variation_id: number;
		attributes: Record<string, string>;
		price: number;
		image: string;
		image_srcset: string;
	}>;
	plans: Array<{
		months: number;
		label: string;
		contentsNote: string;
		finalPrice: number;
		mrp: number;
		symbol: string;
	}>;
};

document.addEventListener( 'DOMContentLoaded', () => {
	initGallery();
	initAccordion();
	initQtyStepper();
	initVariantSwitching();
	initSubscriptionToggle();
} );

// ─── 1. Gallery thumbnail switching ─────────────────────────────────────────

function initGallery(): void {
	const gallery  = document.querySelector<HTMLElement>( '[data-gallery]' );
	if ( ! gallery ) return;

	const heroImg  = gallery.querySelector<HTMLImageElement>( '.pdp-gallery__hero-img' );
	const thumbs   = gallery.querySelectorAll<HTMLButtonElement>( '.pdp-gallery__thumb' );

	if ( ! heroImg || ! thumbs.length ) return;

	thumbs.forEach( ( thumb ) => {
		thumb.addEventListener( 'click', () => {
			const fullUrl    = thumb.dataset.fullUrl    ?? '';
			const fullSrcset = thumb.dataset.fullSrcset ?? '';

			if ( ! fullUrl ) return;

			heroImg.style.opacity = '0';

			setTimeout( () => {
				heroImg.src = fullUrl;
				if ( fullSrcset ) {
					heroImg.srcset = fullSrcset;
				}
				heroImg.style.opacity = '1';
			}, 100 );

			// Update active state.
			thumbs.forEach( ( t ) => t.classList.remove( 'is-active' ) );
			thumb.classList.add( 'is-active' );
		} );
	} );
}

// ─── 2. Accordion ────────────────────────────────────────────────────────────

function initAccordion(): void {
	const accordion = document.querySelector<HTMLElement>( '[data-accordion]' );
	if ( ! accordion ) return;

	const headers = accordion.querySelectorAll<HTMLButtonElement>( '[data-accordion-header]' );

	headers.forEach( ( header ) => {
		header.addEventListener( 'click', () => {
			const isOpen  = header.getAttribute( 'aria-expanded' ) === 'true';
			const bodyId  = header.getAttribute( 'aria-controls' );
			const body    = bodyId ? document.getElementById( bodyId ) : null;
			const icon    = header.querySelector<HTMLElement>( '.pdp-accordion__icon' );

			if ( ! body ) return;

			const willOpen = ! isOpen;

			header.setAttribute( 'aria-expanded', String( willOpen ) );
			body.hidden = ! willOpen;

			if ( icon ) {
				icon.textContent = willOpen ? '−' : '+';
			}
		} );
	} );
}

// ─── 3. Quantity stepper ─────────────────────────────────────────────────────

function initQtyStepper(): void {
	document.querySelectorAll<HTMLElement>( '[data-qty]' ).forEach( ( wrapper ) => {
		const form      = wrapper.closest<HTMLFormElement>( 'form' );
		const display   = wrapper.querySelector<HTMLElement>( '.pdp-qty__display' );
		const btnMinus  = wrapper.querySelector<HTMLButtonElement>( '.pdp-qty__btn--minus' );
		const btnPlus   = wrapper.querySelector<HTMLButtonElement>( '.pdp-qty__btn--plus' );
		const hiddenQty = form?.querySelector<HTMLInputElement>( '.pdp-qty__input' );

		if ( ! display || ! btnMinus || ! btnPlus ) return;

		let qty = 1;

		const update = ( newQty: number ): void => {
			qty = Math.max( 1, newQty );
			display.textContent = String( qty );
			if ( hiddenQty ) {
				hiddenQty.value = String( qty );
			}
		};

		btnMinus.addEventListener( 'click', () => update( qty - 1 ) );
		btnPlus.addEventListener( 'click', () => update( qty + 1 ) );
	} );
}

// ─── 4. Variant switching (variable products) ─────────────────────────────────

function initVariantSwitching(): void {
	const variationForm = document.querySelector<HTMLElement>( '[data-variation-form]' );
	if ( ! variationForm || typeof EternalPDP === 'undefined' || ! EternalPDP.variations.length ) return;

	const selects       = variationForm.querySelectorAll<HTMLSelectElement>( '.pdp-buybox__select' );
	const priceBlock    = document.querySelector<HTMLElement>( '[data-variation-price]' );
	const heroImg       = document.querySelector<HTMLImageElement>( '.pdp-gallery__hero-img' );
	const variationIdEl = document.querySelector<HTMLInputElement>( '.pdp-variation-id' );

	if ( ! selects.length ) return;

	const getSelectedAttributes = (): Record<string, string> => {
		const attrs: Record<string, string> = {};
		selects.forEach( ( sel ) => {
			if ( sel.dataset.attribute_name ) {
				attrs[ sel.dataset.attribute_name ] = sel.value;
			}
		} );
		return attrs;
	};

	const findMatchingVariation = ( attrs: Record<string, string> ) => {
		return EternalPDP.variations.find( ( v ) => {
			return Object.entries( v.attributes ).every( ( [ key, val ] ) => {
				// Empty attribute value in variation means "any".
				return val === '' || attrs[ key ] === val;
			} );
		} );
	};

	const updatePriceDisplay = ( match: typeof EternalPDP.variations[0] | undefined ): void => {
		if ( ! priceBlock ) return;

		const amountEl = priceBlock.querySelector<HTMLElement>( '.pdp-buybox__price-amount' );
		if ( ! amountEl ) return;

		if ( match ) {
			const symbol    = EternalPDP.plans.length ? EternalPDP.plans[0].symbol : '₹';
			const formatted = symbol + new Intl.NumberFormat( 'en-IN' ).format( match.price );
			amountEl.textContent = formatted;
			amountEl.classList.remove( 'pdp-buybox__price-range' );

			if ( variationIdEl ) {
				variationIdEl.value = String( match.variation_id );
			}

			// Swap hero image.
			if ( heroImg && match.image ) {
				heroImg.style.opacity = '0';
				setTimeout( () => {
					heroImg.src = match.image;
					if ( match.image_srcset ) heroImg.srcset = match.image_srcset;
					heroImg.style.opacity = '1';
				}, 100 );
			}
		} else {
			// No match — show price range placeholder.
			if ( variationIdEl ) variationIdEl.value = '';
		}
	};

	selects.forEach( ( sel ) => {
		sel.addEventListener( 'change', () => {
			const attrs = getSelectedAttributes();
			const allSelected = Object.values( attrs ).every( ( v ) => v !== '' );

			if ( allSelected ) {
				const match = findMatchingVariation( attrs );
				updatePriceDisplay( match );
			}
		} );
	} );
}

// ─── 5. Subscription purchase mode toggle ────────────────────────────────────

function initSubscriptionToggle(): void {
	const modeGroup = document.querySelector<HTMLElement>( '[data-purchase-mode]' );
	if ( ! modeGroup || typeof EternalPDP === 'undefined' ) return;

	const radios         = modeGroup.querySelectorAll<HTMLInputElement>( '.pdp-buybox__plan-radio' );
	const oneTimeCard    = modeGroup.querySelector<HTMLElement>( '[data-plan-card="one-time"]' );
	const subCard        = modeGroup.querySelector<HTMLElement>( '[data-plan-card="subscription"]' );
	const planDropdown   = modeGroup.querySelector<HTMLElement>( '[data-plan-dropdown]' );
	const planSelect     = modeGroup.querySelector<HTMLSelectElement>( '[data-plan-select]' );
	const priceDisplay   = document.querySelector<HTMLElement>( '[data-price-display]' );
	const supplyMonths   = document.querySelector<HTMLInputElement>( '.pdp-supply-months' );
	const planNote       = modeGroup.querySelector<HTMLElement>( '[data-plan-note]' );

	const formatPrice = ( symbol: string, amount: number ): string =>
		symbol + new Intl.NumberFormat( 'en-IN' ).format( amount );

	const applyPlanTier = ( months: number ): void => {
		const tier = EternalPDP.plans.find( ( p ) => p.months === months );
		if ( ! tier || ! priceDisplay ) return;

		priceDisplay.textContent = formatPrice( tier.symbol, tier.finalPrice );

		if ( supplyMonths ) supplyMonths.value = String( tier.months );

		if ( planNote ) {
			planNote.textContent = tier.contentsNote || '';
		}
	};

	const setMode = ( mode: 'one-time' | 'subscription' ): void => {
		const isSubscription = mode === 'subscription';

		// Toggle card border.
		oneTimeCard?.classList.toggle( 'pdp-buybox__plan-card--expanded', ! isSubscription );
		subCard?.classList.toggle( 'pdp-buybox__plan-card--expanded', isSubscription );

		// Show / hide plan dropdown.
		if ( planDropdown ) {
			planDropdown.style.display = isSubscription ? 'block' : 'none';
		}

		if ( isSubscription ) {
			// Restore first plan price.
			const firstMonths = planSelect ? parseInt( planSelect.value, 10 ) : EternalPDP.plans[0]?.months;
			if ( firstMonths ) applyPlanTier( firstMonths );
			if ( supplyMonths ) supplyMonths.value = String( firstMonths );
		} else {
			// One-time: show base product price.
			const oneTimeEl = document.querySelector<HTMLElement>( '[data-one-time-price]' );
			if ( priceDisplay && oneTimeEl ) {
				priceDisplay.textContent = oneTimeEl.textContent ?? '';
			}
			if ( supplyMonths ) supplyMonths.value = '0';
		}
	};

	// Radio card clicks.
	radios.forEach( ( radio ) => {
		radio.addEventListener( 'change', () => {
			setMode( radio.value as 'one-time' | 'subscription' );
		} );
	} );

	// Clicking anywhere on a plan card selects it.
	modeGroup.querySelectorAll<HTMLElement>( '[data-plan-card]' ).forEach( ( card ) => {
		card.addEventListener( 'click', ( e ) => {
			const radio = card.querySelector<HTMLInputElement>( '.pdp-buybox__plan-radio' );
			if ( ! radio ) return;
			// Avoid double-firing when clicking directly on the input.
			if ( e.target === radio ) return;
			radio.checked = true;
			radio.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	} );

	// Plan dropdown change.
	planSelect?.addEventListener( 'change', () => {
		const months = parseInt( planSelect.value, 10 );
		if ( ! isNaN( months ) ) applyPlanTier( months );
	} );

	// Initialise: subscription is default.
	if ( EternalPDP.plans.length ) {
		const defaultMonths = EternalPDP.plans[0].months;
		applyPlanTier( defaultMonths );
	}
}
