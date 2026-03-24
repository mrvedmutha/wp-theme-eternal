/**
 * Header — scroll behaviour & currency switcher toggle.
 *
 * Responsibilities:
 *  1. Transparent → sticky state switch (adds .is-scrolled once user scrolls
 *     past the hero viewport).
 *  2. Scroll-direction hide/show on ALL page types (yPercent via GSAP).
 *  3. Currency dropdown open/close toggle.
 *
 * Dependencies: gsap, gsap/ScrollTrigger (installed via npm install gsap).
 */

import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin( ScrollTrigger );

document.addEventListener( 'DOMContentLoaded', () => {
	const header = document.getElementById( 'masthead' );
	if ( ! header ) return;

	const isTransparentPage = document.body.classList.contains(
		'has-transparent-header'
	);

	// ── 1. Transparent → sticky state switch ─────────────────────────────────
	// Only fires on pages that start with a transparent header.
	if ( isTransparentPage ) {
		ScrollTrigger.create( {
			start: 'top -80px', // trigger once user scrolls 80px past top
			onEnter() {
				header.classList.add( 'is-scrolled' );
			},
			onLeaveBack() {
				header.classList.remove( 'is-scrolled' );
			},
		} );
	}

	// ── 2. Scroll-direction hide / show ──────────────────────────────────────
	// Hides header on scroll-down, reveals on scroll-up.
	// A small threshold prevents accidental triggers on tiny scroll jitters.
	const THRESHOLD = 5; // px/s velocity threshold
	let hidden = false;

	// Give the page a top-padding equal to header height on solid pages
	// so content never jumps when the header becomes sticky.
	if ( ! isTransparentPage ) {
		const headerH = header.getBoundingClientRect().height;
		document.documentElement.style.setProperty(
			'--header-height',
			`${ headerH }px`
		);
	}

	ScrollTrigger.create( {
		start: 'top top',
		onUpdate( self ) {
			const velocity = self.getVelocity();

			if ( velocity > THRESHOLD && ! hidden ) {
				// Scrolling DOWN — hide header.
				gsap.to( header, {
					yPercent: -100,
					duration: 0.4,
					ease: 'power2.out',
					overwrite: true,
				} );
				hidden = true;
			} else if ( velocity < -THRESHOLD && hidden ) {
				// Scrolling UP — reveal header.
				gsap.to( header, {
					yPercent: 0,
					duration: 0.35,
					ease: 'power2.out',
					overwrite: true,
				} );
				hidden = false;
			}
		},
	} );

	// ── 3. Currency dropdown toggle ───────────────────────────────────────────
	const currencyTrigger = header.querySelector( '.header-currency__trigger' );
	const currencyDropdown = header.querySelector( '.header-currency__dropdown' );

	if ( currencyTrigger && currencyDropdown ) {
		currencyTrigger.addEventListener( 'click', () => {
			const isOpen = currencyTrigger.getAttribute( 'aria-expanded' ) === 'true';

			if ( isOpen ) {
				closeDropdown();
			} else {
				openDropdown();
			}
		} );

		// Close on outside click.
		document.addEventListener( 'click', ( e ) => {
			if (
				! currencyTrigger.contains( e.target ) &&
				! currencyDropdown.contains( e.target )
			) {
				closeDropdown();
			}
		} );

		// Close on Escape.
		document.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'Escape' ) closeDropdown();
		} );
	}

	function openDropdown() {
		currencyDropdown.removeAttribute( 'hidden' );
		currencyTrigger.setAttribute( 'aria-expanded', 'true' );
		gsap.fromTo(
			currencyDropdown,
			{ opacity: 0, y: -6 },
			{ opacity: 1, y: 0, duration: 0.2, ease: 'power1.out' }
		);
	}

	function closeDropdown() {
		gsap.to( currencyDropdown, {
			opacity: 0,
			y: -6,
			duration: 0.15,
			ease: 'power1.in',
			onComplete() {
				currencyDropdown.setAttribute( 'hidden', '' );
			},
		} );
		currencyTrigger.setAttribute( 'aria-expanded', 'false' );
	}
} );
