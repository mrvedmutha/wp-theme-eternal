/**
 * Homepage Hero — frontend animations
 *
 * Loaded automatically by WordPress only on pages that contain
 * the wp-rig/homepage-hero block (via viewScript in block.json).
 *
 * Entrance: heading → subtext → CTA fade + slide in from top.
 * CTA hover: wipe out right→left then wipe in left→right on mouseenter.
 */

import { gsap } from 'gsap';

const hero = document.querySelector( '.homepage-hero' );

if ( hero ) {
	const heading = hero.querySelector( '.hero-heading' );
	const subtext = hero.querySelector( '.hero-subtext' );
	const cta     = hero.querySelector( '.hero-cta' );
	const line    = cta ? cta.querySelector( '.hero-cta__line' ) : null;

	// ── Entrance timeline ─────────────────────────────────────────

	const tl = gsap.timeline( { defaults: { ease: 'power2.out' } } );

	if ( heading ) {
		tl.fromTo( heading, { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.8 }, 0.2 );
	}

	if ( subtext ) {
		tl.fromTo( subtext, { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.7 }, 0.5 );
	}

	if ( cta ) {
		tl.fromTo( cta, { opacity: 0, y: -20 }, { opacity: 1, y: 0, duration: 0.6 }, 0.8 );
	}

	// ── CTA underline hover ───────────────────────────────────────
	// Line starts visible. On mouseenter: wipe out right→left,
	// then wipe back in left→right.

	if ( cta && line ) {
		gsap.set( line, { scaleX: 1, transformOrigin: 'left center' } );

		cta.addEventListener( 'mouseenter', () => {
			gsap.killTweensOf( line );
			gsap.set( line, { transformOrigin: 'right center' } );
			const wipeTl = gsap.timeline();
			wipeTl.to( line, { scaleX: 0, duration: 0.35, ease: 'power2.in' } );
			wipeTl.set( line, { transformOrigin: 'left center' } );
			wipeTl.to( line, { scaleX: 1, duration: 0.45, ease: 'power2.out' } );
		} );
	}
}
