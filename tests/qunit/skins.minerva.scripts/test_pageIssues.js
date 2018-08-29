( function ( M ) {
	var pageIssues = M.require( 'skins.minerva.scripts/pageIssues' ),
		createBanner = pageIssues.test.createBanner,
		MEDIUM_ISSUE = {
			severity: 'MEDIUM',
			icon: 'i',
			text: 't'
		},
		LOW_ISSUE = {
			severity: 'LOW',
			icon: 'i',
			text: 't'
		},
		HIGH_ISSUE = {
			severity: 'HIGH',
			icon: 'i',
			text: 't'
		},
		getAllIssuesSections = pageIssues.test.getAllIssuesSections,
		OverlayManager = M.require( 'mobile.startup/OverlayManager' ),
		Page = M.require( 'mobile.startup/Page' ),
		overlayManager = new OverlayManager( require( 'mediawiki.router' ) ),
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
		processedAmbox = createBanner(
			new Page( { el: $mockContainer } ),
			labelText, '0', inline, overlayManager
		);

	QUnit.module( 'Minerva cleanuptemplates' );

	QUnit.test( 'createBanner() should add a "learn more" message', function ( assert ) {
		assert.strictEqual( /⧼skin-minerva-issue-learn-more⧽/.test( processedAmbox.html() ), true );
	} );

	QUnit.test( 'createBanner() should add an icon', function ( assert ) {
		assert.strictEqual( /mw-ui-icon/.test( processedAmbox.html() ), true );
	} );
	QUnit.test( 'clicking on the product of createBanner() should trigger a URL change', function ( assert ) {
		processedAmbox.click();
		assert.strictEqual( window.location.hash, '#/issues/0' );
	} );

	// NOTE: Only for PageIssues AB
	QUnit.test( 'clicking on the product of createBanner() should trigger a custom event', function ( assert ) {
		var mockAction = {
			action: 'issueClicked',
			issueSeverity: [ 'MEDIUM' ]
		};
		mw.trackSubscribe( 'minerva.PageIssuesAB', function ( topic, data ) {
			assert.equal( JSON.toString( mockAction ), JSON.toString( data ) );
		} );
	} );

	QUnit.test( 'getAllIssuesSections', function ( assert ) {
		var allIssuesOldTreatment, allIssuesNewTreatment;
		allIssuesOldTreatment = {
			0: [
				MEDIUM_ISSUE,
				LOW_ISSUE,
				MEDIUM_ISSUE
			]
		};
		allIssuesNewTreatment = {
			0: [
				HIGH_ISSUE,
				LOW_ISSUE,
				MEDIUM_ISSUE
			],
			1: [
				MEDIUM_ISSUE
			]
		};
		assert.deepEqual(
			getAllIssuesSections( allIssuesOldTreatment ),
			[ '0', '0', '0' ],
			'section numbers correctly extracted from old treatment'
		);
		assert.deepEqual(
			getAllIssuesSections( allIssuesNewTreatment ),
			[ '0', '0', '0', '1' ],
			'section numbers correctly extracted from new treatment'
		);
	} );
}( mw.mobileFrontend ) );
