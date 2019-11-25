/**
 * @param {Object} mobile mobileFrontend component library
 */
module.exports = function ( mobile ) {
	var
		SKIN_MINERVA_TALK_SIMPLIFIED_CLASS = 'skin-minerva--talk-simplified',
		toast = mobile.toast,
		currentPage = mobile.currentPage(),
		loader = mobile.rlModuleLoader,
		api = new mw.Api(),
		overlayManager = mobile.OverlayManager.getSingleton(),
		// FIXME: This dependency shouldn't exist
		skin = mobile.Skin.getSingleton(),
		talkTitle = currentPage.titleObj.getTalkPage() ?
			currentPage.titleObj.getTalkPage().getPrefixedText() :
			undefined;

	/**
	 * Render a type of talk overlay
	 * @param {string} className name of talk overlay to create
	 * @param {Object} talkOptions
	 * @return {Overlay|JQuery.Promise}
	 */
	function createOverlay( className, talkOptions ) {
		// eslint-disable-next-line no-restricted-properties
		var M = mw.mobileFrontend;

		return mw.loader.getState( 'mobile.talk.overlays' ) === 'ready' ?
			new ( M.require( 'mobile.talk.overlays/' + className ) )( talkOptions ) :
			// otherwise pull it from ResourceLoader async
			loader.loadModule( 'mobile.talk.overlays' ).then( function () {
				return new ( M.require( 'mobile.talk.overlays/' + className ) )( talkOptions );
			} );
	}

	function removeTalkSectionListeners() {
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '.section-heading' ).each( function () {
			var $heading = $( this );
			$heading.off( 'click.talkSectionOverlay' );
		} );
	}

	/**
	 * @param {JQuery.Element} $heading
	 * @param {JQuery.Element} $headline
	 * @param {string} sectionId
	 * @return {JQuery.Promise} A promise that rejects if simplified mode is off.
	 * A promise that resolves to the TalkSectionOverlay otherwise (unless a
	 * network error occurs).
	 */
	function createTalkSectionOverlay( $heading, $headline, sectionId ) {
		if ( !isSimplifiedViewEnabled() ) {
			// If the simplified view is not enabled, we don't want to show the
			// talk section overlay (e.g. when user clicks on a link in TOC)
			return mobile.util.Deferred().reject();
		}

		// FIXME: Yes, this is hacky. Async code is needed to deal with the
		// overlay opening in a scroll position that is not at the top. What
		// causes this scroll to occur is code from Toggler.js which also
		// listens to hash changes and causes the window to scroll to the top of
		// the clicked on section after the overlay is open. We should probably
		// stop the Toggler code from executing at all when the simplified view
		// is enabled to prevent this code from happening, but MobileFrontend
		// currently initializes the toggler code in mobile.init.js.
		//
		// https://github.com/wikimedia/mediawiki-extensions-MobileFrontend/blob/5c646a9881a787215b7d77c1d8b6b9126f302697/src/mobile.startup/Toggler.js#L216
		return mobile.util.Deferred().resolve().then(
			function () {
				return createOverlay( 'TalkSectionOverlay', {
					id: sectionId,
					section: new mobile.Section( {
						line: $headline.text(),
						text: $heading.next().html()
					} ),
					// FIXME: Replace this api param with onSaveComplete
					api: api,
					title: talkTitle,
					// T184273 using `currentPage` because 'wgPageName'
					// contains underscores instead of spaces.
					licenseMsg: skin.getLicenseMsg(),
					onSaveComplete: function () {
						toast.showOnPageReload( mw.message( 'minerva-talk-reply-success' ).text() );
						window.location.reload();
					}
				} );
			} );
	}

	/**
	 * Initializes code needed to display the TalkSectionOverlay
	 */
	function initTalkSection() {
		// register route for each of the sub-headings
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '.section-heading' ).each( function () {
			var
				sectionId,
				$heading = $( this ),
				$headline = $heading.find( '.mw-headline' ),
				sectionIdMatch = $heading.next().attr( 'class' ).match( /mf-section-(\d+)/ ),
				headlineId = $headline.attr( 'id' );

			if ( sectionIdMatch === null || sectionIdMatch.length !== 2 ) {
				// If section id couldn't be parsed, there is no point in continuing
				return;
			}
			sectionId = sectionIdMatch[ 1 ];

			$heading.on( 'click.talkSectionOverlay', function ( ev ) {
				ev.preventDefault();
				window.location.hash = '#' + headlineId;

			} );

			overlayManager.add( headlineId, function () {
				return createTalkSectionOverlay( $heading, $headline, sectionId );
			} );
		} );
	}

	/**
	 * Initializes code needed to display the TalkSectionAddOverlay
	 */
	function initTalkSectionAdd() {
		overlayManager.add( /^\/talk\/new$/, function () {
			return createOverlay( 'TalkSectionAddOverlay', {
				api: api,
				title: talkTitle,
				// T184273 using `currentPage` because 'wgPageName'
				// contains underscores instead of spaces.
				licenseMsg: skin.getLicenseMsg(),

				currentPageTitle: currentPage.title,
				onSaveComplete: function () {
					toast.showOnPageReload( mw.message( 'minerva-talk-topic-feedback' ).text() );
					window.location = currentPage.titleObj.getTalkPage().getUrl();
				}
			} );
		} );
	}

	/**
	 * T230695: In the simplified view, we need to display a "Read as wikipage"
	 * button which enables the user to switch from simplified mode to the regular
	 * version of the mobile talk page (with TOC and sections that you can
	 * expand/collapse).
	 */
	function renderReadAsWikiPageButton() {
		$( '<button>' )
			.addClass( 'minerva-talk-full-page-button' )
			.text( mw.message( 'minerva-talk-full-page' ).text() )
			.on( 'click', function () {
				// We need to cleanup the listeners previously added in initTalkSection
				// so that events like clicking on a section) won't cause
				// scroll changes/things only needed for the simplified view
				removeTalkSectionListeners();
				// eslint-disable-next-line no-jquery/no-global-selector
				$( 'body' ).removeClass( 'skin-minerva--talk-simplified' );
				$( this ).remove();
				// send user back up to top of page so they don't land awkwardly in
				// middle of page when it expands
				window.scrollTo( 0, 0 );
			} )
			.appendTo( '#content' );
	}

	/**
	 * @param {JQuery.Event} ev
	 * @param {Function} onDismiss
	 * @param {Function} onFollowRoute
	 * @param {string} returnToQuery
	 * @return {undefined}
	 */
	function showDrawerIfEligible( ev, onDismiss, onFollowRoute, returnToQuery ) {
		var
			amcOutreach = mobile.amcOutreach,
			amcCampaign = amcOutreach.loadCampaign();

		// avoiding navigating to original URL
		// DO NOT USE stopPropagation or you'll break click tracking in WikimediaEvents
		ev.preventDefault();

		if (
			amcCampaign.showIfEligible(
				amcOutreach.ACTIONS.onTalkLink,
				onDismiss,
				talkTitle,
				returnToQuery
			)
		) {
			return;
		}

		onFollowRoute();
	}

	/**
	 * Called when user clicks on the add dicussion button located on a talk
	 * page
	 *
	 * @param {JQuery.Event} ev
	 */
	function amcAddTalkClickHandler( ev ) {
		var
			onFollowRoute = function () {
				window.location.hash = '#/talk/new';
			},
			onDismiss = function () {
				onFollowRoute();

				toast.show( mw.message( 'mobile-frontend-amc-outreach-dismissed-message' ).text() );
			};

		showDrawerIfEligible( ev, onDismiss, onFollowRoute, '#/talk/new' );
	}

	/**
	 * Called when user clicks on a talk button that links to the talk page
	 *
	 * @param {JQuery.Event} ev
	 */
	function amcTalkClickHandler( ev ) {
		var
			$button = $( ev.target ),
			onFollowRoute = function () {
				window.location = $button.attr( 'href' );
			},
			onDismiss = function () {
				onFollowRoute();

				toast.showOnPageReload( mw.message( 'mobile-frontend-amc-outreach-dismissed-message' ).text() );
			};

		showDrawerIfEligible( ev, onDismiss, onFollowRoute, '' );
	}

	/**
	 * @return {boolean}
	 */
	function isSimplifiedViewEnabled() {
		// eslint-disable-next-line no-jquery/no-class-state, no-jquery/no-global-selector
		return $( 'body' ).hasClass( SKIN_MINERVA_TALK_SIMPLIFIED_CLASS );
	}

	/**
	 * Sets up necessary event handlers related to the talk page and talk buttons.
	 * Also renders the "Read as wikipage" button for the simplified mode
	 * (T230695).
	 */
	function init() {
		// eslint-disable-next-line no-jquery/no-global-selector
		var $talk = $( '.talk' ),
			// eslint-disable-next-line no-jquery/no-global-selector
			$addTalk = $( '.minerva-talk-add-button' );

		$talk.on( 'click', amcTalkClickHandler );
		$addTalk.on( 'click', amcAddTalkClickHandler );

		// We only want the talk section add overlay to show when the user is on the
		// view action (default action) of the talk page and not when the user is on
		// other actions of the talk page (e.g. like the history action)
		if ( currentPage.titleObj.isTalkPage() && mw.config.get( 'wgAction' ) === 'view' ) {
			initTalkSectionAdd();
		}
		// SkinMinerva sets a class on the body which effectively controls when this
		// mode is on
		if ( isSimplifiedViewEnabled() ) {
			initTalkSection();
			renderReadAsWikiPageButton();
		}
	}

	if ( talkTitle ) {
		init();
	}
};
