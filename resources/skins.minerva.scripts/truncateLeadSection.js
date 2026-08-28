/**
 * Lead section truncation.
 *
 * Given a lead section of an article page that exceeds a threshold,
 * collapses it, and appends a "Read more" button that expands the section on tap.
 *
 * The content of a section can be a mix of text, templates, images, etc.
 * Hence, the threshold is based on the height of a line box in pixels
 * (lineHeight CSS property), multiplied by MAX_LINES.
 *
 * The height of the given lead section is based on its inner text
 * and measured via the scrollHeight CSS property of a temporary container.
 *
 * Uses a CSS custom property to set section heights that are computed at runtime.
 *
 * @module
 */

const MAX_LINES = 30;
const DEFAULT_LINE_HEIGHT_IN_PIXELS = 22;
// https://www.mediawiki.org/wiki/Specs/HTML#Headings_and_Sections
const LEAD_SECTION_SELECTOR = 'section[data-mw-section-id="0"]';

( function () {
	'use strict';

	/**
	 * Measure line height from computed styles.
	 *
	 * @param {HTMLElement} element
	 * @return {number} line height in pixels
	 */
	function getLineHeightInPixels( element ) {
		const computedStyle = window.getComputedStyle( element );
		const lineHeight = computedStyle.lineHeight;

		if ( lineHeight === 'normal' ) {
			return parseFloat( computedStyle.fontSize ) * 1.4;
		}

		return parseFloat( lineHeight ) || DEFAULT_LINE_HEIGHT_IN_PIXELS;
	}

	/**
	 * Check whether the lead section text exceeds `maxLines` lines.
	 *
	 * Measures the actual rendered height of visible text content.
	 *
	 * @param {HTMLElement} leadSectionElement
	 * @param {number} lineHeight
	 * @param {number} maxLines
	 * @return {boolean}
	 */
	function exceedsLineThreshold( leadSectionElement, lineHeight, maxLines ) {
		const innerText = leadSectionElement.innerText;

		if ( innerText.trim().length === 0 ) {
			// No meaningful text content, so fall back to the section's scrollHeight
			return leadSectionElement.scrollHeight > lineHeight * maxLines;
		}

		// Build a temporary container to measure the height of visible text
		const tempContainer = document.createElement( 'div' );
		tempContainer.textContent = innerText;
		// Metadata attribute for JS measurements
		tempContainer.setAttribute( 'data-mw-jsmeas', 'true' );
		document.body.appendChild( tempContainer );
		const measuredHeight = tempContainer.scrollHeight;
		tempContainer.remove();

		return measuredHeight > lineHeight * maxLines;
	}

	/**
	 * Apply truncation to the lead section.
	 *
	 * @param {HTMLElement} leadSectionElement
	 * @param {number} lineHeight
	 * @param {number} maxLines
	 */
	function truncate( leadSectionElement, lineHeight, maxLines ) {
		const threshold = lineHeight * maxLines;

		leadSectionElement.classList.add( 'minerva--lead-section--collapsed' );
		// Replace the section with a positioned wrapper so the expand button
		// sits exactly at the truncation edge and is clickable.
		const wrapper = document.createElement( 'div' );
		wrapper.className = 'minerva--lead-section-container';
		// Set the dynamic height value via a CSS custom property
		wrapper.style.setProperty( '--mw-lead-section-container-height', threshold + 'px' );
		leadSectionElement.parentNode.replaceChild( wrapper, leadSectionElement );
		wrapper.appendChild( leadSectionElement );

		// Button wrapper
		const buttonWrapper = document.createElement( 'div' );
		buttonWrapper.className = 'minerva--lead-fade-container';

		// Expand button
		const button = document.createElement( 'button' );
		button.type = 'button';
		button.classList.add(
			'minerva--lead-section__button',
			'cdx-button',
			'cdx-button--size-large',
			'cdx-button--weight-quiet'
		);
		button.textContent = mw.msg( 'minimal-minerva-lead-section-read-more' );

		buttonWrapper.appendChild( button );
		wrapper.appendChild( buttonWrapper );

		// Expand the section and make the button disappear on click
		button.addEventListener( 'click', () => {
			// Go back to the original scroll height
			wrapper.style.setProperty( '--mw-lead-section-container-height', leadSectionElement.scrollHeight + 'px' );
			leadSectionElement.classList.remove( 'minerva--lead-section--collapsed' );

			wrapper.style.setProperty( '--mw-lead-section-container-height', '' );
			wrapper.parentNode.replaceChild( leadSectionElement, wrapper );
		} );
	}

	/** @return {void} */
	function init() {
		const leadSectionElement = document.querySelector( LEAD_SECTION_SELECTOR );
		if ( leadSectionElement === null ) {
			return;
		}

		const lineHeight = getLineHeightInPixels( leadSectionElement );
		if ( exceedsLineThreshold( leadSectionElement, lineHeight, MAX_LINES ) ) {
			truncate( leadSectionElement, lineHeight, MAX_LINES );
		}
	}

	module.exports = {
		init: init
	};
}() );
