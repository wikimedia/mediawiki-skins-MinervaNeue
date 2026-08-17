const track = mw.track;
let printSetTimeoutReference = 0;

/**
 * Helper function to detect iOs
 *
 * @ignore
 * @param {string} userAgent User Agent
 * @return {boolean}
 */
function isIos( userAgent ) {
	return /ipad|iphone|ipod/i.test( userAgent );
}

/**
 * Checks whether DownloadIcon is available for given user agent
 *
 * @memberof DownloadIcon
 * @instance
 * @param {mw.config} mw.config instance
 * @param config
 * @param {string} userAgent User agent
 * @param {Window} [windowObj] window object
 * @return {boolean}
 */
function isAvailable( config, userAgent, windowObj ) {
	const supportedNamespaces = config.get( 'wgMinervaDownloadNamespaces', [] );
	if ( typeof window.print !== 'function' ) {
		// T309591: No window.print support
		return false;
	}

	const doc = ( windowObj && windowObj.document ) || document;
	if ( doc && doc.body && doc.body.classList.contains( 'minerva--minimal' ) ) {
		// Minimal Minerva does not display the download button
		return false;
	}

	// Download button is restricted to certain namespaces T181152.
	// Not shown on missing pages
	// Defaults to 0, in case cached JS has been served.
	if ( !supportedNamespaces.includes( config.get( 'wgNamespaceNumber' ) ) ||
		config.get( 'wgIsMainPage' ) || config.get( 'wgArticleId' ) === 0 ) {
		// namespace is not supported or it's a main page
		return false;
	}

	if ( isIos( userAgent ) ) {
		// iOS devices have known issues with window.print
		return false;
	}
	return true;
}
/**
 * onClick handler for button that invokes print function
 *
 * @private
 */
function onClick() {
	track( 'minerva.downloadAsPDF', {
		action: 'callPrint'
	} );
	printSetTimeoutReference = clearTimeout( printSetTimeoutReference );
	// The beforeprint wont necessarily be called when calling window.print
	// programmatically (e.g. Chrome) so strip loading attribute from any
	// images before invoking print.
	Array.prototype.forEach.call(
		document.querySelectorAll( 'img[loading]' ),
		( img ) => {
			img.loading = 'eager';
		}
	);
	if ( !printSetTimeoutReference ) {
		printSetTimeoutReference = setTimeout( window.print, 500 );
	}
}

/**
 * Generate a download icon for triggering print functionality if
 * printing is available.
 * Calling this method has side effects:
 * It calls mw.util.addPortletLink and may inject an element into the page.
 *
 * @ignore
 * @param {mw.config} config
 * @param {Window} [windowObj] window object
 * @param {boolean} [overflowList] Append to overflow list
 * @return {jQuery|null}
 */
function downloadPageAction( config, windowObj, overflowList ) {
	if (
		isAvailable(
			config, navigator.userAgent, windowObj
		)
	) {
		// FIXME: Use p-views when cache has cleared.
		const actionID = document.querySelector( '#p-views' ) ? 'p-views' : 'page-actions';
		const portletLink = mw.util.addPortletLink(
			overflowList ? 'page-actions-overflow' : actionID,
			'#',
			mw.msg( 'minerva-download' ),
			// id
			'minerva-download',
			// tooltip
			mw.msg( 'minerva-download' ),
			// access key
			'p',
			overflowList ? null : document.getElementById( 'page-actions-watch' )
		);
		if ( portletLink ) {
			portletLink.addEventListener( 'click', () => {
				onClick();
			} );
			const iconElement = portletLink.querySelector( '.minerva-icon' );
			if ( iconElement ) {
				iconElement.classList.add( 'minerva-icon--download' );
			}
		}
		return portletLink;
	} else {
		return null;
	}
}

module.exports = {
	downloadPageAction,
	test: {
		isAvailable,
		onClick
	}
};
