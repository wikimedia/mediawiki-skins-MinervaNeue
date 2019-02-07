( function ( M ) {
	var
		pageIssues = M.require( 'skins.minerva.scripts/pageIssues' ),
		mobile = M.require( 'mobile.startup' ),
		util = mobile.util,
		createBanner = pageIssues.test.createBanner,
		icon = {},
		MEDIUM_ISSUE = {
			issue: {
				severity: 'MEDIUM',
				icon: icon
			},
			iconString: 'i',
			text: 't'
		},
		LOW_ISSUE = {
			issue: {
				severity: 'LOW',
				icon: icon
			},
			iconString: 'i',
			text: 't'
		},
		HIGH_ISSUE = {
			issue: {
				severity: 'HIGH',
				icon: icon
			},
			iconString: 'i',
			text: 't'
		},
		getAllIssuesSections = pageIssues.test.getAllIssuesSections,
		OverlayManager = mobile.OverlayManager,
		Page = mobile.Page,
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
		SECTION = '0',
		processedAmbox = createBanner(
			new Page( { el: $mockContainer } ),
			labelText, SECTION, inline, overlayManager
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
		assert.strictEqual( window.location.hash, '#/issues/' + SECTION );
	} );

	QUnit.test( 'getAllIssuesSections', function ( assert ) {
		var multipleIssuesWithDeletion,
			multipleIssues, allIssuesOldTreatment, allIssuesNewTreatment;
		allIssuesOldTreatment = {
			0: [
				MEDIUM_ISSUE,
				LOW_ISSUE,
				MEDIUM_ISSUE
			]
		};
		multipleIssues = {
			0: [
				util.extend( {}, MEDIUM_ISSUE, { grouped: true } ),
				util.extend( {}, LOW_ISSUE, { grouped: true } ),
				util.extend( {}, MEDIUM_ISSUE, { grouped: true } )
			]
		};
		multipleIssuesWithDeletion = {
			0: [
				HIGH_ISSUE,
				util.extend( {}, MEDIUM_ISSUE, { grouped: true } ),
				util.extend( {}, LOW_ISSUE, { grouped: true } ),
				util.extend( {}, MEDIUM_ISSUE, { grouped: true } )
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
		assert.deepEqual(
			getAllIssuesSections( multipleIssues ),
			[ '0' ],
			'multiple issues are packed into one entry since there is one box'
		);
		assert.deepEqual(
			getAllIssuesSections( multipleIssuesWithDeletion ),
			[ '0', '0' ],
			'while multiple issues are grouped, non-multiple issues are still reported'
		);
	} );
}( mw.mobileFrontend ) );
