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

document.addEventListener("DOMContentLoaded", () => {
	initGallery();
	initGalleryZoom();
	initLightbox();
	initAccordion();
	initQtyStepper();
	initVariantSwitching();
	initSubscriptionToggle();
});

// ─── 1. Gallery thumbnail switching ─────────────────────────────────────────

let currentGalleryIndex = 0;

function initGallery(): void {
	const gallery = document.querySelector<HTMLElement>("[data-gallery]");
	if (!gallery) return;

	const heroContainer = gallery.querySelector<HTMLElement>(
		"[data-lightbox-trigger]",
	);
	const heroImg = gallery.querySelector<HTMLImageElement>("[data-hero-img]");
	const thumbs = gallery.querySelectorAll<HTMLButtonElement>(
		".pdp-gallery__thumb",
	);
	const navPrev = gallery.querySelector<HTMLButtonElement>("[data-nav-prev]");
	const navNext = gallery.querySelector<HTMLButtonElement>("[data-nav-next]");

	if (!heroImg || !thumbs.length) return;

	// Update gallery image to specific index
	const updateGalleryImage = (index: number): void => {
		const thumb = thumbs[index];
		if (!thumb) return;

		const fullUrl = thumb.dataset.fullUrl ?? "";
		const fullSrcset = thumb.dataset.fullSrcset ?? "";

		if (!fullUrl) return;

		heroImg.style.opacity = "0";

		setTimeout(() => {
			heroImg.src = fullUrl;
			if (fullSrcset) {
				heroImg.srcset = fullSrcset;
			}
			heroImg.style.opacity = "1";
		}, 100);

		// Update active state.
		thumbs.forEach((t) => t.classList.remove("is-active"));
		thumb.classList.add("is-active");

		// Update current index
		currentGalleryIndex = index;
		if (heroContainer) {
			heroContainer.dataset.currentIndex = String(index);
		}
	};

	// Thumbnail clicks
	thumbs.forEach((thumb, index) => {
		thumb.addEventListener("click", () => {
			updateGalleryImage(index);
		});
	});

	// Navigation buttons
	if (navPrev) {
		navPrev.addEventListener("click", (e) => {
			e.stopPropagation();
			const newIndex =
				currentGalleryIndex > 0
					? currentGalleryIndex - 1
					: thumbs.length - 1;
			updateGalleryImage(newIndex);
		});
	}

	if (navNext) {
		navNext.addEventListener("click", (e) => {
			e.stopPropagation();
			const newIndex =
				currentGalleryIndex < thumbs.length - 1
					? currentGalleryIndex + 1
					: 0;
			updateGalleryImage(newIndex);
		});
	}
}

// ─── 1b. Gallery zoom + pan on hover ────────────────────────────────────────

function initGalleryZoom(): void {
	const heroContainer = document.querySelector<HTMLElement>(
		".pdp-gallery__hero",
	);
	const heroImg = document.querySelector<HTMLImageElement>(
		".pdp-gallery__hero-img",
	);
	if (!heroContainer || !heroImg) return;

	const SCALE = 1.3;

	const applyTransform = (tx: number, ty: number): void => {
		// translate is applied pre-scale, so divide by SCALE to get correct visual offset
		heroImg.style.transform = `scale(${SCALE}) translate(${tx / SCALE}px, ${ty / SCALE}px)`;
	};

	heroContainer.addEventListener("mouseenter", () => {
		applyTransform(0, 0);
	});

	heroContainer.addEventListener("mousemove", (e: MouseEvent) => {
		const rect = heroContainer.getBoundingClientRect();
		// Normalize mouse to -1…1 from center
		const nx = ((e.clientX - rect.left) / rect.width - 0.5) * 2;
		const ny = ((e.clientY - rect.top) / rect.height - 0.5) * 2;
		// Max translate keeps the scaled image edges flush with the container
		const maxTx = (rect.width * (SCALE - 1)) / 2;
		const maxTy = (rect.height * (SCALE - 1)) / 2;
		applyTransform(nx * maxTx, ny * maxTy);
	});

	heroContainer.addEventListener("mouseleave", () => {
		heroImg.style.transform = "";
	});
}

// ─── 1c. Lightbox Modal ──────────────────────────────────────────────────────

class PDPLightbox {
	private modal: HTMLElement | null = null;
	private backdrop: HTMLElement | null = null;
	private modalImg: HTMLImageElement | null = null;
	private modalThumbs: NodeListOf<HTMLButtonElement> | null = null;
	private thumbsContainer: HTMLElement | null = null;
	private closeBtn: HTMLButtonElement | null = null;
	private navPrev: HTMLButtonElement | null = null;
	private navNext: HTMLButtonElement | null = null;
	private gradientLeft: HTMLElement | null = null;
	private gradientRight: HTMLElement | null = null;

	private currentIndex = 0;
	private totalImages = 0;
	private isOpen = false;
	private gradientTimer: number | null = null;
	private focusableElements: HTMLElement[] = [];
	private lastFocusedElement: HTMLElement | null = null;

	// Touch gesture support
	private touchStartX = 0;
	private touchEndX = 0;

	constructor() {
		this.modal = document.querySelector<HTMLElement>("[data-pdp-modal]");
		if (!this.modal) return;

		this.backdrop = this.modal.querySelector<HTMLElement>(
			"[data-modal-backdrop]",
		);
		this.modalImg =
			this.modal.querySelector<HTMLImageElement>("[data-modal-img]");
		this.modalThumbs =
			this.modal.querySelectorAll<HTMLButtonElement>(
				"[data-modal-thumb]",
			);
		this.thumbsContainer = this.modal.querySelector<HTMLElement>(
			"[data-modal-thumbs]",
		);
		this.closeBtn =
			this.modal.querySelector<HTMLButtonElement>("[data-modal-close]");
		this.navPrev = this.modal.querySelector<HTMLButtonElement>(
			"[data-modal-nav-prev]",
		);
		this.navNext = this.modal.querySelector<HTMLButtonElement>(
			"[data-modal-nav-next]",
		);
		this.gradientLeft = this.modal.querySelector<HTMLElement>(
			".pdp-modal__gradient--left",
		);
		this.gradientRight = this.modal.querySelector<HTMLElement>(
			".pdp-modal__gradient--right",
		);

		this.bindEvents();
	}

	private bindEvents(): void {
		// Hero image click - open lightbox
		const heroContainer = document.querySelector<HTMLElement>(
			"[data-lightbox-trigger]",
		);
		if (heroContainer) {
			heroContainer.addEventListener("click", (e) => {
				// Don't open if clicking navigation buttons
				if (
					(e.target as HTMLElement).closest(
						"[data-nav-prev], [data-nav-next]",
					)
				) {
					return;
				}
				const index = parseInt(
					heroContainer.dataset.currentIndex ?? "0",
					10,
				);
				this.open(index);
			});
		}

		// Close button
		this.closeBtn?.addEventListener("click", () => this.close());

		// Backdrop click
		this.backdrop?.addEventListener("click", () => this.close());

		// Navigation buttons
		this.navPrev?.addEventListener("click", (e) => {
			e.stopPropagation();
			this.navigate("prev");
			this.showGradientsTemporarily();
		});

		this.navNext?.addEventListener("click", (e) => {
			e.stopPropagation();
			this.navigate("next");
			this.showGradientsTemporarily();
		});

		// Modal thumbnail clicks - removed as thumbnails are hidden

		// Keyboard navigation
		document.addEventListener("keydown", (e) => {
			if (!this.isOpen) return;

			switch (e.key) {
				case "ArrowLeft":
					e.preventDefault();
					this.navigate("prev");
					break;
				case "ArrowRight":
					e.preventDefault();
					this.navigate("next");
					break;
				case "Escape":
					this.close();
					break;
				case "Tab":
					this.handleTabKey(e);
					break;
			}
		});

		// Touch gestures
		if (this.modal) {
			this.modal.addEventListener(
				"touchstart",
				(e) => {
					this.touchStartX = e.changedTouches[0].screenX;
				},
				{ passive: true },
			);

			this.modal.addEventListener(
				"touchend",
				(e) => {
					this.touchEndX = e.changedTouches[0].screenX;
					this.handleGesture();
				},
				{ passive: true },
			);
		}
	}

	public open(index = 0): void {
		if (!this.modal || !this.modalImg) return;

		// Store last focused element
		this.lastFocusedElement = document.activeElement as HTMLElement;

		this.isOpen = true;
		this.currentIndex = index;

		// Get total from data attribute
		const heroContainer = document.querySelector<HTMLElement>(
			"[data-lightbox-trigger]",
		);
		this.totalImages = parseInt(
			heroContainer?.dataset.totalImages ?? "1",
			10,
		);

		// Update image
		this.updateImage(index);

		// Show modal
		this.modal.hidden = false;
		document.body.style.overflow = "hidden";
		document.body.classList.add("pdp-modal-open");

		// Setup focus trap
		this.setupFocusTrap();

		// Focus close button
		setTimeout(() => this.closeBtn?.focus(), 100);

		// Show gradients temporarily on open (for touch devices)
		this.showGradientsTemporarily();
	}

	public close(): void {
		if (!this.modal) return;

		this.isOpen = false;
		this.modal.hidden = true;
		document.body.style.overflow = "";
		document.body.classList.remove("pdp-modal-open");

		// Restore focus
		this.lastFocusedElement?.focus();

		// Clear gradient timer
		if (this.gradientTimer !== null) {
			clearTimeout(this.gradientTimer);
			this.gradientTimer = null;
		}
	}

	private navigate(direction: "prev" | "next"): void {
		if (direction === "prev") {
			this.currentIndex =
				this.currentIndex > 0
					? this.currentIndex - 1
					: this.totalImages - 1;
		} else {
			this.currentIndex =
				this.currentIndex < this.totalImages - 1
					? this.currentIndex + 1
					: 0;
		}
		this.updateImage(this.currentIndex);
	}

	private updateImage(index: number): void {
		if (!this.modalImg) return;

		// Get image data from modal thumbs (hidden) or gallery thumbs
		const galleryThumbs = document.querySelectorAll<HTMLButtonElement>(
			".pdp-gallery__thumb",
		);
		const thumb = this.modalThumbs?.[index] || galleryThumbs[index];
		if (!thumb) return;

		const fullUrl = thumb.dataset.fullUrl ?? "";
		const fullSrcset = thumb.dataset.fullSrcset ?? "";

		if (!fullUrl) return;

		// Fade out
		this.modalImg.style.opacity = "0";

		setTimeout(() => {
			if (!this.modalImg) return;

			this.modalImg.src = fullUrl;
			if (fullSrcset) {
				this.modalImg.srcset = fullSrcset;
			}
			this.modalImg.alt = thumb.getAttribute("aria-label") ?? "";

			// Fade in
			this.modalImg.style.opacity = "1";
		}, 150);

		// Update current index
		this.currentIndex = index;

		// Preload adjacent images
		this.preloadAdjacentImages(index);
	}

	private scrollThumbIntoView(thumb: HTMLElement): void {
		if (!this.thumbsContainer) return;

		const containerRect = this.thumbsContainer.getBoundingClientRect();
		const thumbRect = thumb.getBoundingClientRect();

		const offset =
			thumbRect.left -
			containerRect.left -
			containerRect.width / 2 +
			thumbRect.width / 2;

		this.thumbsContainer.scrollBy({
			left: offset,
			behavior: "smooth",
		});
	}

	private preloadAdjacentImages(currentIndex: number): void {
		const indicesToPreload = [currentIndex - 1, currentIndex + 1];

		// Get image URLs from gallery thumbnails
		const galleryThumbs = document.querySelectorAll<HTMLButtonElement>(
			".pdp-gallery__thumb",
		);

		indicesToPreload.forEach((idx) => {
			if (idx >= 0 && idx < this.totalImages) {
				const thumb = this.modalThumbs?.[idx] || galleryThumbs[idx];
				const url = thumb?.dataset.fullUrl;
				if (url) {
					const img = new Image();
					img.src = url;
				}
			}
		});
	}

	private showGradientsTemporarily(): void {
		if (!this.modal) return;

		// Clear existing timer
		if (this.gradientTimer !== null) {
			clearTimeout(this.gradientTimer);
		}

		// Add class to show gradients
		this.modal.classList.add("pdp-modal--gradient-visible");

		// Remove after 3 seconds
		this.gradientTimer = window.setTimeout(() => {
			this.modal?.classList.remove("pdp-modal--gradient-visible");
			this.gradientTimer = null;
		}, 3000);
	}

	private handleGesture(): void {
		const swipeThreshold = 50;
		const diff = this.touchStartX - this.touchEndX;

		if (Math.abs(diff) > swipeThreshold) {
			if (diff > 0) {
				// Swiped left - next image
				this.navigate("next");
			} else {
				// Swiped right - previous image
				this.navigate("prev");
			}
			this.showGradientsTemporarily();
		}
	}

	private setupFocusTrap(): void {
		if (!this.modal) return;

		// Get all focusable elements
		const focusableSelectors =
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
		this.focusableElements = Array.from(
			this.modal.querySelectorAll<HTMLElement>(focusableSelectors),
		).filter((el) => !el.hasAttribute("disabled") && !el.hidden);
	}

	private handleTabKey(e: KeyboardEvent): void {
		if (this.focusableElements.length === 0) return;

		const firstElement = this.focusableElements[0];
		const lastElement =
			this.focusableElements[this.focusableElements.length - 1];

		if (e.shiftKey) {
			// Shift + Tab
			if (document.activeElement === firstElement) {
				e.preventDefault();
				lastElement?.focus();
			}
		} else {
			// Tab
			if (document.activeElement === lastElement) {
				e.preventDefault();
				firstElement?.focus();
			}
		}
	}
}

function initLightbox(): void {
	new PDPLightbox();
}

// ─── 2. Accordion ────────────────────────────────────────────────────────────

function initAccordion(): void {
	const accordion = document.querySelector<HTMLElement>("[data-accordion]");
	if (!accordion) return;

	const headers = accordion.querySelectorAll<HTMLButtonElement>(
		"[data-accordion-header]",
	);

	headers.forEach((header) => {
		header.addEventListener("click", () => {
			const isOpen = header.getAttribute("aria-expanded") === "true";
			const bodyId = header.getAttribute("aria-controls");
			const body = bodyId ? document.getElementById(bodyId) : null;
			const icon = header.querySelector<HTMLElement>(
				".pdp-accordion__icon",
			);

			if (!body) return;

			const willOpen = !isOpen;

			header.setAttribute("aria-expanded", String(willOpen));
			body.hidden = !willOpen;

			if (icon) {
				icon.textContent = willOpen ? "−" : "+";
			}
		});
	});
}

// ─── 3. Quantity stepper ─────────────────────────────────────────────────────

function initQtyStepper(): void {
	document.querySelectorAll<HTMLElement>("[data-qty]").forEach((wrapper) => {
		const form = wrapper.closest<HTMLFormElement>("form");
		const display = wrapper.querySelector<HTMLElement>(".pdp-qty__display");
		const btnMinus = wrapper.querySelector<HTMLButtonElement>(
			".pdp-qty__btn--minus",
		);
		const btnPlus = wrapper.querySelector<HTMLButtonElement>(
			".pdp-qty__btn--plus",
		);
		const hiddenQty =
			form?.querySelector<HTMLInputElement>(".pdp-qty__input");

		if (!display || !btnMinus || !btnPlus) return;

		let qty = 1;

		const update = (newQty: number): void => {
			qty = Math.max(1, newQty);
			display.textContent = String(qty);
			if (hiddenQty) {
				hiddenQty.value = String(qty);
			}
		};

		btnMinus.addEventListener("click", () => update(qty - 1));
		btnPlus.addEventListener("click", () => update(qty + 1));
	});
}

// ─── 4. Variant switching (variable products) ─────────────────────────────────

function initVariantSwitching(): void {
	const variationForm = document.querySelector<HTMLElement>(
		"[data-variation-form]",
	);
	if (
		!variationForm ||
		typeof EternalPDP === "undefined" ||
		!EternalPDP.variations.length
	)
		return;

	const selects = variationForm.querySelectorAll<HTMLSelectElement>(
		".pdp-buybox__select",
	);
	const priceBlock = document.querySelector<HTMLElement>(
		"[data-variation-price]",
	);
	const heroImg = document.querySelector<HTMLImageElement>(
		".pdp-gallery__hero-img",
	);
	const variationIdEl =
		document.querySelector<HTMLInputElement>(".pdp-variation-id");

	if (!selects.length) return;

	const getSelectedAttributes = (): Record<string, string> => {
		const attrs: Record<string, string> = {};
		selects.forEach((sel) => {
			if (sel.dataset.attribute_name) {
				attrs[sel.dataset.attribute_name] = sel.value;
			}
		});
		return attrs;
	};

	const findMatchingVariation = (attrs: Record<string, string>) => {
		return EternalPDP.variations.find((v) => {
			return Object.entries(v.attributes).every(([key, val]) => {
				// Empty attribute value in variation means "any".
				return val === "" || attrs[key] === val;
			});
		});
	};

	const updatePriceDisplay = (
		match: (typeof EternalPDP.variations)[0] | undefined,
	): void => {
		if (!priceBlock) return;

		const amountEl = priceBlock.querySelector<HTMLElement>(
			".pdp-buybox__price-amount",
		);
		if (!amountEl) return;

		if (match) {
			const symbol = EternalPDP.plans.length
				? EternalPDP.plans[0].symbol
				: "₹";
			const formatted =
				symbol + new Intl.NumberFormat("en-IN").format(match.price);
			amountEl.textContent = formatted;
			amountEl.classList.remove("pdp-buybox__price-range");

			if (variationIdEl) {
				variationIdEl.value = String(match.variation_id);
			}

			// Swap hero image.
			if (heroImg && match.image) {
				heroImg.style.opacity = "0";
				setTimeout(() => {
					heroImg.src = match.image;
					if (match.image_srcset) heroImg.srcset = match.image_srcset;
					heroImg.style.opacity = "1";
				}, 100);
			}
		} else {
			// No match — show price range placeholder.
			if (variationIdEl) variationIdEl.value = "";
		}
	};

	selects.forEach((sel) => {
		sel.addEventListener("change", () => {
			const attrs = getSelectedAttributes();
			const allSelected = Object.values(attrs).every((v) => v !== "");

			if (allSelected) {
				const match = findMatchingVariation(attrs);
				updatePriceDisplay(match);
			}
		});
	});
}

// ─── 5. Subscription purchase mode toggle ────────────────────────────────────

function initSubscriptionToggle(): void {
	const modeGroup = document.querySelector<HTMLElement>(
		"[data-purchase-mode]",
	);
	if (!modeGroup || typeof EternalPDP === "undefined") return;

	const radios = modeGroup.querySelectorAll<HTMLInputElement>(
		".pdp-buybox__plan-radio",
	);
	const oneTimeCard = modeGroup.querySelector<HTMLElement>(
		'[data-plan-card="one-time"]',
	);
	const subCard = modeGroup.querySelector<HTMLElement>(
		'[data-plan-card="subscription"]',
	);
	const planDropdown = modeGroup.querySelector<HTMLElement>(
		"[data-plan-dropdown]",
	);
	const planSelect =
		modeGroup.querySelector<HTMLSelectElement>("[data-plan-select]");
	const priceDisplay = document.querySelector<HTMLElement>(
		"[data-price-display]",
	);
	const supplyMonths =
		document.querySelector<HTMLInputElement>(".pdp-supply-months");
	const planNote = modeGroup.querySelector<HTMLElement>("[data-plan-note]");
	const unitDisplay = document.querySelector<HTMLElement>("[data-unit-display]");
	const baseUnitAmount = unitDisplay
		? parseInt(unitDisplay.dataset.unitAmount ?? "0", 10)
		: 0;
	const baseUnitText = unitDisplay?.dataset.unitText ?? "";

	const formatPrice = (symbol: string, amount: number): string =>
		symbol + new Intl.NumberFormat("en-IN").format(amount);

	const applyPlanTier = (months: number): void => {
		const tier = EternalPDP.plans.find((p) => p.months === months);
		if (!tier || !priceDisplay) return;

		const formatted = formatPrice(tier.symbol, tier.finalPrice);
		priceDisplay.textContent = formatted;

		// Also update the inline price inside the subscription card.
		const cardPriceEl = document.querySelector<HTMLElement>(
			"[data-subscription-price]",
		);
		if (cardPriceEl) {
			cardPriceEl.textContent = formatted;
		}

		if (supplyMonths) supplyMonths.value = String(tier.months);

		if (planNote) {
			planNote.textContent = tier.contentsNote || "";
		}

		// Update unit quantity: base amount × months.
		if (unitDisplay && baseUnitAmount) {
			unitDisplay.textContent = `/ ${baseUnitAmount * months} ${baseUnitText}`;
		}
	};

	const setMode = (mode: "one-time" | "subscription"): void => {
		const isSubscription = mode === "subscription";

		// Toggle card border.
		oneTimeCard?.classList.toggle(
			"pdp-buybox__plan-card--expanded",
			!isSubscription,
		);
		subCard?.classList.toggle(
			"pdp-buybox__plan-card--expanded",
			isSubscription,
		);

		// Show / hide plan body.
		if (planDropdown) {
			planDropdown.style.display = isSubscription ? "flex" : "none";
		}

		if (isSubscription) {
			// Restore first plan price.
			const firstMonths = planSelect
				? parseInt(planSelect.value, 10)
				: EternalPDP.plans[0]?.months;
			if (firstMonths) applyPlanTier(firstMonths);
			if (supplyMonths) supplyMonths.value = String(firstMonths);
		} else {
			// One-time: show base product price.
			const oneTimeEl = document.querySelector<HTMLElement>(
				"[data-one-time-price]",
			);
			if (priceDisplay && oneTimeEl) {
				priceDisplay.textContent = oneTimeEl.textContent ?? "";
			}
			if (supplyMonths) supplyMonths.value = "0";

			// Reset unit to base amount.
			if (unitDisplay && baseUnitAmount) {
				unitDisplay.textContent = `/ ${baseUnitAmount} ${baseUnitText}`;
			}
		}
	};

	// Radio card clicks.
	radios.forEach((radio) => {
		radio.addEventListener("change", () => {
			setMode(radio.value as "one-time" | "subscription");
		});
	});

	// Clicking anywhere on a plan card selects it.
	modeGroup
		.querySelectorAll<HTMLElement>("[data-plan-card]")
		.forEach((card) => {
			card.addEventListener("click", (e) => {
				const radio = card.querySelector<HTMLInputElement>(
					".pdp-buybox__plan-radio",
				);
				if (!radio) return;
				// Avoid double-firing when clicking directly on the input.
				if (e.target === radio) return;
				radio.checked = true;
				radio.dispatchEvent(new Event("change", { bubbles: true }));
			});
		});

	// Plan dropdown change.
	planSelect?.addEventListener("change", () => {
		const months = parseInt(planSelect.value, 10);
		if (!isNaN(months)) applyPlanTier(months);
	});

	// Initialise: subscription is default.
	if (EternalPDP.plans.length) {
		const defaultMonths = EternalPDP.plans[0].months;
		applyPlanTier(defaultMonths);
	}
}
