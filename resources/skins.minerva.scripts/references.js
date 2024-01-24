var drawers = require( './drawers.js' );

module.exports = function () {

	var mobile = require( 'mobile.startup' ),
		references = mobile.references,
		currentPage = mobile.currentPage(),
		currentPageHTMLParser = mobile.currentPageHTMLParser(),
		ReferencesHtmlScraperGateway = mobile.references.ReferencesHtmlScraperGateway,
		gateway = new ReferencesHtmlScraperGateway( new mw.Api() );

	/**
	 * Event handler to show reference when a reference link is clicked
	 *
	 * @ignore
	 * @param {jQuery.Event} ev Click event of the reference element
	 */
	function showReference( ev ) {
		var $dest = $( ev.currentTarget ),
			href = $dest.attr( 'href' );

		ev.preventDefault();

		// If necessary strip the URL portion of the href so we are left with the
		// fragment
		var i = href.indexOf( '#' );
		if ( i > 0 ) {
			href = href.slice( i );
		}

		references.showReference( href, currentPage, $dest.text(),
			currentPageHTMLParser, gateway, {
				onShow: function () {
					drawers.lockScroll();
				},
				onShowNestedReference: true,
				onBeforeHide: drawers.discardDrawer
			},
			function ( oldDrawer, newDrawer ) {
				oldDrawer.hide();
				drawers.displayDrawer( newDrawer, {} );
			}
		).then( function ( drawer ) {
			drawers.displayDrawer( drawer, {} );
		} );
	}

	/**
	 * Event handler to show reference when a reference link is clicked.
	 * Delegates to `showReference` once the references drawer is ready.
	 *
	 * @ignore
	 * @param {jQuery.Event} ev Click event of the reference element
	 */
	function onClickReference( ev ) {
		showReference( ev );
	}

	function init() {
		// Make references clickable and show a drawer when clicked on.
		$( document ).on( 'click', 'sup.reference a', onClickReference );
	}

	init();
};
