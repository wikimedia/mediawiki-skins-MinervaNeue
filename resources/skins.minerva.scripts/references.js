module.exports = function () {
	// eslint-disable-next-line no-restricted-properties
	var M = mw.mobileFrontend,
		mobile = M.require( 'mobile.startup' ),
		references = mobile.references,
		$drawerContainer = $( document.body ),
		currentPage = mobile.currentPage(),
		BODY_CLASSES_DRAWER_OPEN = 'navigation-enabled has-drawer--with-scroll-locked',
		currentPageHTMLParser = mobile.currentPageHTMLParser(),
		ReferencesHtmlScraperGateway = mobile.ReferencesHtmlScraperGateway,
		gateway = new ReferencesHtmlScraperGateway( new mw.Api() );

	/**
	 * Discard a drawer from display on the page.
	 * @ignore
	 * @param {Drawer} drawer
	 */
	function discardDrawer( drawer ) {
		// remove the class
		$drawerContainer.removeClass( BODY_CLASSES_DRAWER_OPEN );
		// queue removal from DOM (using setTimeout so that any animations have time to run)
		setTimeout( function () {
			// remove the node from the DOM.
			drawer.$el.remove();
		}, 1000 );
	}

	/**
	 * Event handler to show reference when a reference link is clicked
	 * @ignore
	 * @param {JQuery.Event} ev Click event of the reference element
	 */
	function showReference( ev ) {
		var urlComponents,
			$dest = $( ev.currentTarget ),
			href = $dest.attr( 'href' );

		ev.preventDefault();

		// If necessary strip the URL portion of the href so we are left with the
		// fragment
		urlComponents = href.split( '#' );
		if ( urlComponents.length > 1 ) {
			href = '#' + urlComponents[ 1 ];
		}

		references.showReference( href, currentPage, $dest.text(),
			currentPageHTMLParser, gateway, {
				onShow: function () {
					// show shield and lock scrolling
					$drawerContainer.addClass( BODY_CLASSES_DRAWER_OPEN );
				},
				onBeforeHide: discardDrawer
			}
		).then( function ( drawer ) {
			$drawerContainer.append( drawer.$el[ 0 ] );
			// A click outside the reference drawer should close it.
			$( window ).one( 'click', function () {
				drawer.hide();
			} );
		} );
	}

	/**
	 * Event handler to show reference when a reference link is clicked.
	 * Delegates to `showReference` once the references drawer is ready.
	 *
	 * @ignore
	 * @param {JQuery.Event} ev Click event of the reference element
	 */
	function onClickReference( ev ) {
		showReference( ev );
	}

	function init() {
		// Make references clickable and show a drawer when clicked on.
		mobile.util.getDocument().on( 'click', 'sup.reference a', onClickReference );
	}

	init();
};
