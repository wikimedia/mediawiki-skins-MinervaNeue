( function ( M ) {
	var
		mobile = M.require( 'mobile.startup' ),
		pageIssues = M.require( 'skins.minerva.scripts/pageIssues' ),
		insertBannersOrNotice = pageIssues.test.insertBannersOrNotice,
		OverlayManager = mobile.OverlayManager,
		Page = mobile.Page,
		overlayManager = OverlayManager.getSingleton(),
		$mockContainer = $(
			'<div id=\'bodyContent\'>' +
				'<table class=\'ambox ambox-content\'>' +
					'<tbody class=\'mbox-text\'>' +
						'<tr><td><span class=\'mbox-text-span\'> ambox text span </span></td></tr>' +
					'</tbody>' +
				'</table>' +
			'</div>'
		),
		labelText = 'label text',
		inline = true,
		SECTION = '0',
		processedAmbox = insertBannersOrNotice(
			new Page( { el: $mockContainer } ),
			labelText, SECTION, inline, overlayManager
		).ambox;

	QUnit.module( 'Minerva pageIssues' );

	QUnit.test( 'insertBannersOrNotice() should add a "learn more" message', function ( assert ) {
		assert.strictEqual( /⧼skin-minerva-issue-learn-more⧽/.test( processedAmbox.html() ), true );
	} );

	QUnit.test( 'insertBannersOrNotice() should add an icon', function ( assert ) {
		assert.strictEqual( /mw-ui-icon/.test( processedAmbox.html() ), true );
	} );
	QUnit.test( 'clicking on the product of insertBannersOrNotice() should trigger a URL change', function ( assert ) {
		processedAmbox.click();
		assert.strictEqual( window.location.hash, '#/issues/' + SECTION );
	} );
}( mw.mobileFrontend ) );
