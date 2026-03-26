/**
 * Homepage Brand Statement — frontend animation
 *
 * Sticky behaviour is handled by CSS (position: sticky).
 * The 80px spacer div in the HTML ensures pinning starts from
 * the SVG logo position, not the paragraph.
 *
 * Word reveal: one-shot, plays once when section enters viewport.
 *
 * Requires GSAP + ScrollTrigger (bundled via esbuild).
 */

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin( ScrollTrigger );

const section = document.querySelector( '.homepage-brand-statement' );

if ( section ) {
	const textEl = section.querySelector( '.homepage-brand-statement__text' );

	if ( textEl ) {
		const rawText = textEl.textContent.trim();
		const words   = rawText.split( /\s+/ );

		textEl.innerHTML = words
			.map( ( w ) => `<span class="hbs-word">${ w }</span>` )
			.join( ' ' );

		const wordSpans = textEl.querySelectorAll( '.hbs-word' );

		const tl = gsap.timeline( {
			scrollTrigger: {
				trigger:       section,
				start:         'top bottom',
				toggleActions: 'play none none none',
			},
		} );

		tl.to( wordSpans, {
			color:    '#021f1d',
			opacity:  1,
			ease:     'power2.out',
			duration: 0.4,
			stagger:  {
				each: 0.05,
				from: 'start',
			},
		} );
	}
}
