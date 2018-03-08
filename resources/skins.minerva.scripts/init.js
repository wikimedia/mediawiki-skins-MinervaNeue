( function ( M, track, config, $ ) {
	var
		toast = M.require( 'mobile.startup/toast' ),
		time = M.require( 'mobile.startup/time' ),
		skin = M.require( 'mobile.init/skin' ),
		DownloadIcon = M.require( 'skins.minerva.scripts/DownloadIcon' ),
		browser = M.require( 'mobile.startup/Browser' ).getSingleton(),
		loader = M.require( 'mobile.startup/rlModuleLoader' ),
		router = require( 'mediawiki.router' ),
		OverlayManager = M.require( 'mobile.startup/OverlayManager' ),
		overlayManager = new OverlayManager( require( 'mediawiki.router' ) ),
		page = M.getCurrentPage(),
		thumbs = page.getThumbnails();

	/**
	 * Event handler for clicking on an image thumbnail
	 * @param {jQuery.Event} ev
	 * @ignore
	 */
	function onClickImage( ev ) {
		ev.preventDefault();
		routeThumbnail( $( this ).data( 'thumb' ) );
	}

	/**
	 * @param {jQuery.Element} thumbnail
	 * @ignore
	 */
	function routeThumbnail( thumbnail ) {
		router.navigate( '#/media/' + encodeURIComponent( thumbnail.getFileName() ) );
	}

	/**
	 * Add routes to images and handle clicks
	 * @method
	 * @ignore
	 */
	function initMediaViewer() {
		thumbs.forEach( function ( thumb ) {
			thumb.$el.off().data( 'thumb', thumb ).on( 'click', onClickImage );
		} );
	}

	/**
	 * Hijack the Special:Languages link and replace it with a trigger to a LanguageOverlay
	 * that displays the same data
	 * @ignore
	 */
	function initButton() {
		// This catches language selectors in page actions and in secondary actions (e.g. Main Page)
		var $primaryBtn = $( '.language-selector' );

		if ( $primaryBtn.length ) {
			// We only bind the click event to the first language switcher in page
			$primaryBtn.on( 'click', function ( ev ) {
				ev.preventDefault();

				if ( $primaryBtn.attr( 'href' ) || $primaryBtn.find( 'a' ).length ) {
					router.navigate( '/languages' );
				} else {
					toast.show( mw.msg( 'mobile-frontend-languages-not-available' ) );
				}
			} );
		}
	}

	/**
	 * Return the language code of the device in lowercase
	 *
	 * @ignore
	 * @return {string|undefined}
	 */
	function getDeviceLanguage() {
		var lang = navigator && navigator.languages ?
			navigator.languages[0] :
			navigator.language || navigator.userLanguage ||
				navigator.browserLanguage || navigator.systemLanguage;

		return lang ? lang.toLowerCase() : undefined;
	}

	/**
	 * Loads tablet modules when the skin is in tablet mode and the
	 * current page is in the main namespace.
	 * @method
	 * @ignore
	 */
	function loadTabletModules() {
		if ( browser.isWideScreen() && page.inNamespace( '' ) ) {
			mw.loader.using( 'skins.minerva.tablet.scripts' );
		}
	}

	/**
	 * Load image overlay
	 * @method
	 * @ignore
	 * @uses ImageOverlay
	 * @param {string} title Url of image
	 * @return {jQuery.Deferred}
	 */
	function loadImageOverlay( title ) {
		if ( mw.loader.getState( 'mmv.bootstrap' ) === 'ready' ) {
			// This means MultimediaViewer has been installed and is loaded.
			// Avoid loading it (T169622)
			return $.Deferred().reject();
		} else {
			return loader.loadModule( 'mobile.mediaViewer' ).then( function () {
				var ImageOverlay = M.require( 'mobile.mediaViewer/ImageOverlay' ),
					imageOverlay = new ImageOverlay( {
						api: new mw.Api(),
						thumbnails: thumbs,
						title: decodeURIComponent( title )
					} );
				imageOverlay.on( ImageOverlay.EVENT_EXIT, function () {
					// Actually dismiss the overlay whenever the cross is closed.
					window.location.hash = '';
					// Clear the hash.
					router.navigate( '' );
				} );
				imageOverlay.on( ImageOverlay.EVENT_SLIDE, function ( nextThumbnail ) {
					routeThumbnail( nextThumbnail );
				} );
				return imageOverlay;
			} );
		}
	}

	// Routes
	overlayManager.add( /^\/media\/(.+)$/, loadImageOverlay );
	overlayManager.add( /^\/languages$/, function () {
		var result = $.Deferred(),
			lang = mw.config.get( 'wgUserLanguage' );

		loader.loadModule( 'mobile.languages.structured', true ).done( function ( loadingOverlay ) {
			var PageGateway = M.require( 'mobile.startup/PageGateway' ),
				gateway = new PageGateway( new mw.Api() ),
				LanguageOverlay = M.require( 'mobile.languages.structured/LanguageOverlay' );

			gateway.getPageLanguages( mw.config.get( 'wgPageName' ), lang ).done( function ( data ) {
				loadingOverlay.hide();
				result.resolve( new LanguageOverlay( {
					currentLanguage: mw.config.get( 'wgContentLanguage' ),
					languages: data.languages,
					variants: data.variants,
					deviceLanguage: getDeviceLanguage()
				} ) );
			} );
		} );
		return result;
	} );

	// Setup
	$( function () {
		initButton();
		initMediaViewer();
	} );

	/**
	 * Initialisation function for last modified module.
	 *
	 * Enhances an element representing a time
	 * to show a human friendly date in seconds, minutes, hours, days
	 * months or years
	 * @ignore
	 * @param {JQuery.Object} [$lastModifiedLink]
	 */
	function initHistoryLink( $lastModifiedLink ) {
		var delta, historyUrl, msg, $bar,
			ts, username, gender;

		historyUrl = $lastModifiedLink.attr( 'href' );
		ts = $lastModifiedLink.data( 'timestamp' );
		username = $lastModifiedLink.data( 'user-name' ) || false;
		gender = $lastModifiedLink.data( 'user-gender' );

		if ( ts ) {
			delta = time.getTimeAgoDelta( parseInt( ts, 10 ) );
			if ( time.isRecent( delta ) ) {
				$bar = $lastModifiedLink.closest( '.last-modified-bar' );
				$bar.addClass( 'active' );
				// in beta update icons to be inverted
				$bar.find( '.mw-ui-icon' ).each( function () {
					$( this ).attr( 'class', $( this ).attr( 'class' ).replace( '-gray', '-invert' ) );
				} );
			}
			msg = time.getLastModifiedMessage( ts, username, gender, historyUrl );
			$lastModifiedLink.replaceWith( msg );
		}
	}

	/**
	 * Initialisation function for last modified times
	 *
	 * Enhances .modified-enhancement element
	 * to show a human friendly date in seconds, minutes, hours, days
	 * months or years
	 * @ignore
	 */
	function initModifiedInfo() {
		$( '.modified-enhancement' ).each( function () {
			initHistoryLink( $( this ) );
		} );
	}

	/**
	 * Initialisation function for user creation module.
	 *
	 * Enhances an element representing a time
	 + to show a human friendly date in seconds, minutes, hours, days
	 * months or years
	 * @ignore
	 * @param {JQuery.Object} [$tagline]
	 */
	function initRegistrationDate( $tagline ) {
		var msg, ts;

		ts = $tagline.data( 'userpage-registration-date' );

		if ( ts ) {
			msg = time.getRegistrationMessage( ts, $tagline.data( 'userpage-gender' ) );
			$tagline.text( msg );
		}
	}

	/**
	 * Initialisation function for registration date on user page
	 *
	 * Enhances .tagline-userpage element
	 * to show human friendly date in seconds, minutes, hours, days
	 * months or years
	 * @ignore
	 */
	function initRegistrationInfo() {
		$( '#tagline-userpage' ).each( function () {
			initRegistrationDate( $( this ) );
		} );
	}

	/**
	 * Initialize and inject the download button
	 *
	 * There are many restrictions when we can show the download button, this function should handle
	 * all device/os/operating system related checks and if device supports printing it will inject
	 * the Download icon
	 * @ignore
	 */
	function appendDownloadButton() {
		var downloadIcon = new DownloadIcon( skin, config.get( 'wgMinervaDownloadNamespaces' ), window );

		if ( downloadIcon.isAvailable( navigator.userAgent ) ) {
			// Because the page actions are floated to the right, their order in the
			// DOM is reversed in the display. The watchstar is last in the DOM and
			// left-most in the display. Since we want the download button to be to
			// the left of the watchstar, we put it after it in the DOM.
			downloadIcon.$el.insertAfter( '#ca-watch' );
			track( 'minerva.downloadAsPDF', {
				action: 'buttonVisible'
			} );
		}
	}

	$( function () {
		// Update anything else that needs enhancing (e.g. watchlist)
		initModifiedInfo();
		initRegistrationInfo();
		initHistoryLink( $( '.last-modifier-tagline a' ) );
		M.on( 'resize', loadTabletModules );
		loadTabletModules();
		appendDownloadButton();
	} );

	M.define( 'skins.minerva.scripts/overlayManager', overlayManager );
}( mw.mobileFrontend, mw.track, mw.config, jQuery ) );
