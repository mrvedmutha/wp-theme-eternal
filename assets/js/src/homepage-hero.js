/**
 * Homepage Hero Animations
 *
 * Entrance: heading → subtext → CTA fade in from top on page load.
 * CTA hover: underline draws left→right on enter, right→left on leave.
 *
 * Requires GSAP (already installed as a project dependency).
 */

import { gsap } from 'gsap';

const hero = document.querySelector( '.homepage-hero' );

if ( hero ) {
	const heading = hero.querySelector( '.hero-heading' );
	const subtext = hero.querySelector( '.hero-subtext' );
	const cta     = hero.querySelector( '.hero-cta' );
	const line    = cta ? cta.querySelector( '.hero-cta__line' ) : null;

	// ── Entrance timeline ─────────────────────────────────────────
	// Each element starts transparent and 20px above its resting position,
	// then animates in sequentially with a staggered delay.

	const tl = gsap.timeline( { defaults: { ease: 'power2.out' } } );

	if ( heading ) {
		tl.from( heading, { opacity: 0, y: -20, duration: 0.8 }, 0.2 );
	}

	if ( subtext ) {
		tl.from( subtext, { opacity: 0, y: -20, duration: 0.7 }, 0.5 );
	}

	if ( cta ) {
		tl.from( cta, { opacity: 0, y: -20, duration: 0.6 }, 0.8 );
	}

	// ── CTA underline hover ───────────────────────────────────────
	// Line starts fully visible (scaleX: 1).
	// Both mouseenter and mouseleave trigger the same wipe:
	//   1. Wipe out right→left  (scaleX 1→0, origin right, fast)
	//   2. Wipe in  left→right  (scaleX 0→1, origin left, slightly slower)

	if ( cta && line ) {
		gsap.set( line, { scaleX: 1, transformOrigin: 'left center' } );

		const wipe = () => {
			gsap.killTweensOf( line );
			// Set transformOrigin synchronously before GSAP captures the initial state
			gsap.set( line, { transformOrigin: 'right center' } );
			const wipeTl = gsap.timeline();
			wipeTl.to( line, { scaleX: 0, duration: 0.35, ease: 'power2.in' } );
			wipeTl.set( line, { transformOrigin: 'left center' } );
			wipeTl.to( line, { scaleX: 1, duration: 0.45, ease: 'power2.out' } );
		};

		cta.addEventListener( 'mouseenter', wipe );
		cta.addEventListener( 'mouseleave', wipe );
	}
}
