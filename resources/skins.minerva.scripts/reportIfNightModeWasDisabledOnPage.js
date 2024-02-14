/**
 * @return {boolean}
 */
function reportDisabled() {
	mw.notify( mw.msg( 'skin-minerva-night-mode-unavailable' ) );
	return true;
}

/**
 * @param {Document} doc
 * @return {boolean} whether it was reported as disabled.
 * @ignore
 */
function reportIfNightModeWasDisabledOnPage( doc ) {
	if ( !doc.classList.contains( 'skin-night-mode-page-disabled' ) ) {
		return false;
	}
	// Cast to string.
	let userExpectedNightMode = `${ mw.user.options.get( 'minerva-night-mode' ) }`;
	if ( !mw.user.isNamed() ) {
		// bit more convoulated here and will break with upstream changes...
		// this is protected by an integration test in integration.test.js
		const cookieValue = mw.cookie.get( 'mwclientpreferences' ) || '';
		const match = cookieValue.match( /skin-night-mode-clientpref-(\d)/ );
		if ( match ) {
			// we found something in the cookie.
			userExpectedNightMode = match[ 1 ];
		}
	}
	if ( userExpectedNightMode === '1' ) {
		return reportDisabled();
	} else if ( userExpectedNightMode === '2' && matchMedia( '( prefers-color-scheme: dark )' ).matches ) {
		return reportDisabled();
	} else {
		return false;
	}
}

module.exports = reportIfNightModeWasDisabledOnPage;
