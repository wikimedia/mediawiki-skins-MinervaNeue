( function ( M ) {
	'use strict';

	var Drawer = M.require( 'mobile.startup/Drawer' ),
		Button = M.require( 'mobile.startup/Button' ),
		Icon = M.require( 'mobile.startup/Icon' ),
		util = M.require( 'mobile.startup/util' );

	/**
	 * This creates the drawer at the bottom of the screen that appears when a
	 * blocked user tries to edit.
	 * @class BlockReason
	 * @extends Drawer
	 */
	function BlockMessage() {
		Drawer.apply( this, arguments );
	}

	OO.mfExtend( BlockMessage, Drawer, {
		defaults: util.extend( {}, Drawer.prototype.defaults, {
			stopHandIcon: new Icon( {
				glyphPrefix: 'minerva',
				name: 'stop-hand'
			} ).options,
			userIcon: new Icon( {
				tagName: 'span',
				glyphPrefix: 'minerva',
				name: 'profile'
			} ).options,
			okButton: new Button( {
				label: mw.msg( 'ok' ),
				tagName: 'button',
				progressive: true,
				additionalClassNames: 'cancel'
			} ).options,
			title: mw.msg( 'skin-minerva-blocked-drawer-title' ),
			reasonHeader: mw.msg( 'skin-minerva-blocked-drawer-reason-header' ),
			creatorHeader: mw.msg( 'skin-minerva-blocked-drawer-creator-header' ),
			expiryHeader: mw.msg( 'skin-minerva-blocked-drawer-expiry-header' )
		} ),
		templatePartials: util.extend( {}, Drawer.prototype.templatePartials, {
			button: Button.prototype.template,
			icon: Icon.prototype.template
		} ),
		template: mw.template.get( 'skins.minerva.editor.blockMessage', 'BlockMessage.hogan' )
	} );

	M.define( 'skins.minerva.editor/BlockMessage', BlockMessage );

}( mw.mobileFrontend ) );
