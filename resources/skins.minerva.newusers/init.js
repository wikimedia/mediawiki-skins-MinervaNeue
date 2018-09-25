/* This code handles the editing tutorial/CTA:

EditTutorial - When an editor registers via the edit page action, upon returning to the
page, show a blue guider prompting them to continue editing. You can replicate this by
appending article_action=signup-edit to the URL of an editable page whilst logged in.
*/
( function ( M, $ ) {
	var PointerOverlay = M.require( 'skins.minerva.newusers/PointerOverlay' ),
		skin = M.require( 'skins.minerva.scripts/skin' ),
		mainMenu = M.require( 'skins.minerva.scripts.top/mainMenu' ),
		util = M.require( 'mobile.startup/util' ),
		escapeHash = util.escapeHash,
		inEditor = window.location.hash.indexOf( '#editor/' ) > -1,
		hash = window.location.hash,
		editOverlay, target;

	/**
	 * If the user came from an edit button signup, show guider.
	 * @ignore
	 * @return {boolean}
	 */
	function shouldShowTutorial() {
		var shouldShowEditTutorial = mw.util.getParamValue( 'article_action' ) === 'signup-edit' && !inEditor;
		return shouldShowEditTutorial;
	}

	if ( hash && hash.indexOf( '/' ) === -1 ) {
		target = escapeHash( hash ) + ' ~ .edit-page';
	} else {
		target = '#ca-edit .edit-page';
	}

	// Note the element might have a new ID if the wikitext was changed so check it exists
	if ( $( target ).length > 0 && shouldShowTutorial() ) {
		editOverlay = new PointerOverlay( {
			target: target,
			skin: skin,
			isTutorial: true,
			className: 'slide active editing',
			appendToElement: '#mw-mf-page-center',
			summary: mw.msg( 'mobile-frontend-editor-tutorial-summary', mw.config.get( 'wgTitle' ) ),
			confirmMsg: mw.msg( 'mobile-frontend-editor-tutorial-confirm' ),
			cancelMsg: mw.msg( 'mobile-frontend-editor-tutorial-cancel' )
		} );
		mainMenu.on( 'open', function () {
			editOverlay.hide();
		} );
		editOverlay.show();
		$( '#ca-edit' ).on( 'mousedown', $.proxy( editOverlay, 'hide' ) );
		// Initialize the 'Start editing' button
		editOverlay.$( '.actionable' ).on( 'click', function () {
			// Hide the tutorial
			editOverlay.hide();
			// Load the editing interface by changing the URL hash
			window.location.href = $( target ).attr( 'href' );
		} );
	}

}( mw.mobileFrontend, jQuery ) );
