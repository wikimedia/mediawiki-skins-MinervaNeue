( function ( M, EventEmitter ) {
	var
		mobile = M.require( 'mobile.startup' ),
		loader = mobile.rlModuleLoader,
		loadingOverlay = mobile.loadingOverlay,
		eventBus = new EventEmitter(),
		// eslint-disable-next-line jquery/no-global-selector
		$talk = $( '.talk, [rel="discussion"]' ),
		// use the plain return value here - T128273
		title = $talk.attr( 'data-title' ),
		overlayManager = M.require( 'skins.minerva.scripts/overlayManager' ),
		skin = M.require( 'skins.minerva.scripts/skin' ),
		inTalkNamespace = false,
		pageTitle, talkTitle, talkNs, pageNs;

	// T127190
	if ( title ) {
		title = decodeURIComponent( title );
	}

	// sanity check: the talk namespace needs to have the next higher integer
	// of the page namespace (the api should add topics only to the talk page of the current
	// page)
	// (https://www.mediawiki.org/wiki/Manual:Using_custom_namespaces#Creating_a_custom_namespace)
	// The method to get associated namespaces will change later (maybe), see T487
	pageTitle = mw.Title.newFromText( mw.config.get( 'wgPageName' ) );
	talkTitle = title ? mw.Title.newFromText( title ) : pageTitle.getTalkPage();

	// Check that there is a valid page and talk title
	if ( !pageTitle || !talkTitle ||
		// the talk link points to something other than the current page
		// so we chose to leave this as a normal link
		pageTitle.getMainText() !== talkTitle.getMainText() ) {
		return;
	}
	talkNs = talkTitle.getNamespaceId();
	pageNs = pageTitle.getNamespaceId();
	inTalkNamespace = talkNs === pageNs;

	if ( pageNs + 1 !== talkNs && !inTalkNamespace ) {
		return;
	}

	overlayManager.add( /^\/talk\/?(.*)$/, function ( id ) {
		var talkOptions = {
			api: new mw.Api(),
			title: talkTitle.toText(),
			// T184273 using `getCurrentPage` because 'wgPageName' contains underscores instead of
			// spaces.
			currentPageTitle: M.getCurrentPage().title,
			licenseMsg: skin.getLicenseMsg(),
			eventBus: eventBus
		};

		return loader.loadModule( 'mobile.talk.overlays' ).then( function () {
			if ( id === 'new' ) {
				return new ( M.require( 'mobile.talk.overlays/TalkSectionAddOverlay' ) )( talkOptions );
			}
			if ( id ) {
				talkOptions.id = id;
				return new ( M.require( 'mobile.talk.overlays/TalkSectionOverlay' ) )( talkOptions );
			}
			return M.require( 'mobile.talk.overlays/talkOverlay' )( talkOptions );
		} );
	} );

	/**
	 * Create route '#/talk'
	 * @ignore
	 */
	function init() {
		$talk.on( 'click', function () {
			if ( $talk.hasClass( 'add' ) ) {
				window.location.hash = '#/talk/new';
			} else {
				window.location.hash = '#/talk';
			}
			return false;
		} );
	}

	init();

	if ( inTalkNamespace ) {
		// reload the page after the new discussion was added
		eventBus.on( 'talk-added-wo-overlay', function () {
			var overlay = loadingOverlay();

			window.location.hash = '';
			// setTimeout to make sure, that loadingOverlay's overlayenabled class on html doesnt
			// get removed by OverlayManager (who closes TalkSectionAddOverlay).
			window.setTimeout( function () {
				overlay.show();
				window.location.reload();
			}, 10 );
		} );
	}
}( mw.mobileFrontend, OO.EventEmitter ) );
