/**
 * @param {Object} mobile mobileFrontend component library
 */
module.exports = function ( mobile ) {
	var
		loader = mobile.rlModuleLoader,
		loadingOverlay = mobile.loadingOverlay,
		eventBus = mobile.eventBusSingleton,
		PageGateway = mobile.PageGateway,
		api = new mw.Api(),
		gateway = new PageGateway( api ),
		// eslint-disable-next-line no-jquery/no-global-selector
		$talk = $( '.talk, [rel="discussion"]' ),
		// use the plain return value here - T128273
		title = $talk.attr( 'data-title' ),
		overlayManager = mobile.OverlayManager.getSingleton(),
		// FIXME: This dependency shouldn't exist
		skin = mobile.Skin.getSingleton(),
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
	pageTitle = mw.Title.newFromText( mw.config.get( 'wgRelevantPageName' ) );
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

	/**
	 * Render a talk overlay for a given section
	 * @param {string} id (a number e.g. '1' or the string 'new')
	 * @param {Object} talkOptions
	 * @return {Overlay}
	 */
	function talkSectionOverlay( id, talkOptions ) {
		var M = mw.mobileFrontend;
		if ( id === 'new' ) {
			return new ( M.require( 'mobile.talk.overlays/TalkSectionAddOverlay' ) )( talkOptions );
		}
		return new ( M.require( 'mobile.talk.overlays/TalkSectionOverlay' ) )( talkOptions );
	}

	overlayManager.add( /^\/talk\/?(.*)$/, function ( id ) {
		var title = talkTitle.toText(),
			talkOptions = {
				api: api,
				title: title,
				onSaveComplete: function () {
					gateway.invalidatePage( title );
					// navigate back. the overlay is done with so close it
					overlayManager.router.back();
					try {
						overlayManager.replaceCurrent(
							mobile.talk.overlay( title, gateway )
						);
						overlayManager.router.navigateTo( null, {
							// This should be defined in Minerva.
							path: '#/talk',
							useReplaceState: true
						} );
					} catch ( e ) {
						// the user came directly - there is no overlay to replace
						// so no overlay to refresh
					}
					mw.notify( mw.msg( 'mobile-frontend-talk-topic-feedback' ) );
				},
				// T184273 using `currentPage` because 'wgPageName'
				// contains underscores instead of spaces.
				currentPageTitle: mobile.currentPage().title,
				licenseMsg: skin.getLicenseMsg(),
				eventBus: eventBus,
				id: id
			};

		// talk case
		if ( id ) {
			// If the module is already loaded return it instantly and synchronously.
			// this avoids a flash of
			// content when transitioning from mobile.talk.overlay to this overlay (T221978)
			return mw.loader.getState( 'mobile.talk.overlays' ) === 'ready' ?
				talkSectionOverlay( id, talkOptions ) :
				// otherwise pull it from ResourceLoader async
				loader.loadModule( 'mobile.talk.overlays' ).then( function () {
					return talkSectionOverlay( id, talkOptions );
				} );
		} else {
			return mobile.talk.overlay( title, gateway );
		}
	} );

	/**
	 * Create route '#/talk'
	 * @ignore
	 */
	function init() {
		$talk.on( 'click', function ( ev ) {
			// eslint-disable-next-line no-jquery/no-class-state
			if ( $talk.hasClass( 'add' ) ) {
				window.location.hash = '#/talk/new';
			} else {
				window.location.hash = '#/talk';
			}
			// avoiding navigating to original URL
			// DO NOT USE stopPropagation or you'll break click tracking in WikimediaEvents
			ev.preventDefault();
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
};
