/**
 * Search Megamenu
 *
 * Open/close the full-width search overlay, inline skeleton→results swap,
 * and debounced live product search via the eternal/v1/search REST endpoint.
 *
 * Requires: #search-megamenu markup (template-parts/header/search-megamenu.php)
 * Localised: window.eternalSearch { endpoint, nonce, viewMoreBase }
 */

document.addEventListener( 'DOMContentLoaded', () => {

	// ── Element refs ────────────────────────────────────────────────

	const megamenu    = document.getElementById( 'search-megamenu' );
	if ( ! megamenu ) return;

	const siteHeader  = document.getElementById( 'masthead' );
	const input       = document.getElementById( 'search-megamenu-input' );
	const closeBtn    = document.getElementById( 'search-megamenu-close' );
	const grid        = document.getElementById( 'search-megamenu-grid' );
	const colLabel    = document.getElementById( 'search-megamenu-col-label' );
	const viewMore    = document.getElementById( 'search-megamenu-view-more' );
	const emptyMsg    = document.getElementById( 'search-megamenu-empty' );
	const triggers    = document.querySelectorAll( '.header-search-trigger' );

	const { endpoint, nonce, viewMoreBase } = window.eternalSearch || {};

	// ── State ─────────────────────────────────────────────────────

	let currentQuery    = '';
	let originalGridHTML = grid ? grid.innerHTML : '';
	// Did we force the header into its solid/scrolled state?
	const isTransparentPage = document.body.classList.contains( 'has-transparent-header' );
	let forcedHeaderSolid   = false;

	// ── Helpers ─────────────────────────────────────────────────────

	/**
	 * Minimal HTML escaping for JS-rendered card content.
	 * price_html from WooCommerce is pre-escaped and must NOT be run through this.
	 *
	 * @param {string} str
	 * @returns {string}
	 */
	function esc( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	/**
	 * Returns a debounced version of fn.
	 *
	 * @param {Function} fn
	 * @param {number}   wait ms
	 * @returns {Function}
	 */
	function debounce( fn, wait ) {
		let timer;
		return ( ...args ) => {
			clearTimeout( timer );
			timer = setTimeout( () => fn( ...args ), wait );
		};
	}

	// ── Grid state management ────────────────────────────────────────

	/**
	 * Restore the grid to the original PHP-rendered recommended products.
	 */
	function restoreDefaultGrid() {
		if ( grid ) grid.innerHTML = originalGridHTML;
		if ( colLabel ) colLabel.textContent = 'RECOMMENDED PRODUCTS';
		if ( viewMore ) viewMore.hidden  = true;
		if ( emptyMsg ) emptyMsg.hidden  = true;
	}

	/**
	 * Show skeleton placeholder cards while search results are loading.
	 *
	 * @param {number} count Number of skeleton cards (default: 2 to match default grid).
	 */
	function showSkeletonGrid( count = 2 ) {
		if ( ! grid ) return;
		grid.innerHTML = Array.from( { length: count }, () => `
			<div class="search-megamenu__product-card search-megamenu__product-card--skeleton">
				<div class="search-megamenu__skeleton-img"></div>
				<div class="search-megamenu__skeleton-body">
					<div class="search-megamenu__skeleton-line search-megamenu__skeleton-line--xs"></div>
					<div class="search-megamenu__skeleton-line search-megamenu__skeleton-line--lg"></div>
					<div class="search-megamenu__skeleton-line search-megamenu__skeleton-line--md"></div>
					<div class="search-megamenu__skeleton-line search-megamenu__skeleton-line--sm"></div>
					<div class="search-megamenu__skeleton-line search-megamenu__skeleton-line--md"></div>
				</div>
			</div>
		` ).join( '' );

		if ( viewMore ) viewMore.hidden = true;
		if ( emptyMsg ) emptyMsg.hidden = true;
	}

	/**
	 * Populate the grid with live search result cards.
	 *
	 * @param {Array}  products Array of product objects from the REST endpoint.
	 * @param {string} query    The search term used.
	 */
	function showResultsInGrid( products, query ) {
		if ( ! grid ) return;

		if ( colLabel ) colLabel.textContent = 'SEARCH RESULTS';

		if ( ! products.length ) {
			grid.innerHTML  = '';
			if ( emptyMsg ) emptyMsg.hidden = false;
			if ( viewMore ) viewMore.hidden  = true;
			return;
		}

		grid.innerHTML = products.map( renderProductCard ).join( '' );

		if ( viewMore ) {
			viewMore.href   = viewMoreBase + encodeURIComponent( query );
			viewMore.hidden = products.length <= 2;
		}
		if ( emptyMsg ) emptyMsg.hidden = true;
	}

	// ── Product card renderer ────────────────────────────────────────

	/**
	 * Returns the HTML string for a single product card.
	 * Mirrors the PHP render_product_card() output.
	 *
	 * @param {Object} p  Product object from the REST response.
	 * @returns {string}
	 */
	function renderProductCard( p ) {
		const badge = p.size_badge
			? `<span class="search-megamenu__product-card-badge">${ esc( p.size_badge ) }</span>`
			: '';

		const nameFr = p.name_fr
			? `<p class="search-megamenu__product-card-name-fr">${ esc( p.name_fr ) }</p>`
			: '';

		const desc = p.short_description
			? `<p class="search-megamenu__product-card-desc">${ esc( p.short_description ) }</p>`
			: '';

		// price_html is safe WooCommerce-escaped output — do NOT double-escape.
		return `
			<a href="${ esc( p.permalink ) }"
			   class="search-megamenu__product-card"
			   aria-label="${ esc( p.name ) }">
				<img
					class="search-megamenu__product-card-img"
					src="${ esc( p.image_url ) }"
					alt="${ esc( p.image_alt ) }"
					width="115"
					height="115"
					loading="lazy"
				>
				<div class="search-megamenu__product-card-body">
					${ badge }
					<p class="search-megamenu__product-card-name">${ esc( p.name ) }</p>
					${ nameFr }
					<div class="search-megamenu__product-card-price">${ p.price_html }</div>
					${ desc }
				</div>
			</a>
		`;
	}

	// ── Fetch ────────────────────────────────────────────────────────

	/**
	 * Fetch search results and update the grid.
	 * Race-condition safe: stale responses are discarded.
	 *
	 * @param {string} query
	 */
	async function fetchResults( query ) {
		if ( ! endpoint ) return;

		currentQuery = query;

		// Show skeleton immediately (2 cards matches default recommended layout).
		showSkeletonGrid( 2 );

		try {
			const url = new URL( endpoint );
			url.searchParams.set( 'q', query );

			const res = await fetch( url.toString(), {
				headers: { 'X-WP-Nonce': nonce },
			} );

			// Discard if a newer query has already started.
			if ( query !== currentQuery ) return;

			if ( ! res.ok ) {
				restoreDefaultGrid();
				return;
			}

			const data = await res.json();
			showResultsInGrid( data.products || [], query );

		} catch {
			// Network failure — revert silently.
			if ( query === currentQuery ) restoreDefaultGrid();
		}
	}

	const debouncedFetch = debounce( fetchResults, 300 );

	// ── Open / Close ─────────────────────────────────────────────────

	function openMegamenu() {
		// Bring header back if GSAP scroll-hide has animated it up.
		// body.search-megamenu-open CSS sets transform:translateY(0) !important.
		document.body.classList.add( 'search-megamenu-open' );

		// Force header solid state on transparent-header pages.
		if ( siteHeader && isTransparentPage && ! siteHeader.classList.contains( 'is-scrolled' ) ) {
			siteHeader.classList.add( 'is-scrolled' );
			forcedHeaderSolid = true;
		}

		megamenu.removeAttribute( 'hidden' );

		// Double rAF: ensure the element is in the layout tree before the
		// CSS transition fires (hidden removal → rAF → class addition).
		requestAnimationFrame( () => {
			requestAnimationFrame( () => {
				megamenu.classList.add( 'is-open' );
				megamenu.setAttribute( 'aria-hidden', 'false' );
				triggers.forEach( btn => btn.setAttribute( 'aria-expanded', 'true' ) );
				input.focus();
			} );
		} );
	}

	function closeMegamenu() {
		megamenu.classList.remove( 'is-open' );
		megamenu.setAttribute( 'aria-hidden', 'true' );
		triggers.forEach( btn => btn.setAttribute( 'aria-expanded', 'false' ) );
		document.body.classList.remove( 'search-megamenu-open' );

		// Restore header transparent state if we forced it solid and user is at top.
		if ( forcedHeaderSolid && siteHeader && window.scrollY < 80 ) {
			siteHeader.classList.remove( 'is-scrolled' );
		}
		forcedHeaderSolid = false;

		// Wait for CSS transition, then hide the element and reset state.
		megamenu.addEventListener(
			'transitionend',
			() => {
				if ( megamenu.classList.contains( 'is-open' ) ) return;
				megamenu.setAttribute( 'hidden', '' );
				input.value  = '';
				currentQuery = '';
				restoreDefaultGrid();
			},
			{ once: true }
		);

		// Return focus to the visible trigger.
		const activeTrigger = [ ...triggers ].find( t => t.offsetParent !== null );
		if ( activeTrigger ) activeTrigger.focus();
	}

	// ── Event wiring ─────────────────────────────────────────────────

	// Toggle on each search trigger (desktop header + mobile header-left).
	triggers.forEach( btn => {
		btn.addEventListener( 'click', () => {
			megamenu.classList.contains( 'is-open' ) ? closeMegamenu() : openMegamenu();
		} );
	} );

	// X close button.
	closeBtn.addEventListener( 'click', closeMegamenu );

	// Escape key.
	document.addEventListener( 'keydown', e => {
		if ( e.key === 'Escape' && megamenu.classList.contains( 'is-open' ) ) {
			closeMegamenu();
		}
	} );

	// Click outside the megamenu (but not on a trigger button).
	document.addEventListener( 'click', e => {
		if (
			megamenu.classList.contains( 'is-open' ) &&
			! megamenu.contains( e.target ) &&
			! [ ...triggers ].some( t => t.contains( e.target ) )
		) {
			closeMegamenu();
		}
	} );

	// ── Input handling ───────────────────────────────────────────────

	input.addEventListener( 'input', () => {
		const q = input.value.trim();

		if ( ! q ) {
			currentQuery = '';
			restoreDefaultGrid();
			return;
		}

		if ( q.length >= 3 ) {
			debouncedFetch( q );
		}
	} );

	// Enter key → navigate to full search results.
	input.addEventListener( 'keydown', e => {
		if ( e.key === 'Enter' ) {
			const q = input.value.trim();
			if ( q ) {
				e.preventDefault();
				window.location.href = viewMoreBase + encodeURIComponent( q );
			}
		}
	} );

} );
