/*
 * Warn people if they're trying to switch to desktop but have cookies disabled.
 */
module.exports = function ( amcOutreach, currentPage ) {
	/**
	 * Checks whether cookies are enabled
	 * @method
	 * @ignore
	 * @return {boolean} Whether cookies are enabled
	 */
	function cookiesEnabled() {
		// If session cookie already set, return true
		if ( $.cookie( 'mf_testcookie' ) === 'test_value' ) {
			return true;
			// Otherwise try to set mf_testcookie and return true if it was set
		} else {
			$.cookie( 'mf_testcookie', 'test_value', {
				path: '/'
			} );
			return $.cookie( 'mf_testcookie' ) === 'test_value';
		}
	}

	/**
	 * An event handler for the toggle to desktop link.
	 * If cookies are enabled it will redirect you to desktop site as described in
	 * the link href associated with the handler.
	 * If cookies are not enabled, show a toast and die.
	 * @method
	 * @ignore
	 * @return {boolean|undefined}
	 */
	function desktopViewClick() {
		if ( !cookiesEnabled() ) {
			mw.notify(
				mw.msg( 'mobile-frontend-cookies-required' ),
				{ type: 'error' }
			);
			// Prevent default action
			return false;
		}
	}

	/**
	 * @method
	 * @ignore
	 * @return {boolean|undefined}
	 */
	function amcDesktopClickHandler() {
		var
			self = this,
			executeWrappedEvent = function () {
				if ( desktopViewClick() === false ) {
					return false;
				}

				window.location = self.href;
			},
			amcCampaign = amcOutreach.loadCampaign(),
			onDismiss = function () {
				executeWrappedEvent();
			};

		if ( amcCampaign.showIfEligible( amcOutreach.ACTIONS.onDesktopLink,
			onDismiss, currentPage.title ) ) {
			// prevent default/stop propagation
			return false;
		}

		return executeWrappedEvent();
	}

	// eslint-disable-next-line no-jquery/no-global-selector
	$( '#mw-mf-display-toggle' ).on( 'click', amcDesktopClickHandler );
};
