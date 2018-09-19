( function ( M ) {
	var pageIssues = M.require( 'skins.minerva.scripts/pageIssues' ),
		util = M.require( 'mobile.startup/util' ),
		pageIssuesParser = M.require( 'skins.minerva.scripts/pageIssuesParser' ),
		extractMessage = pageIssues.test.extractMessage,
		createBanner = pageIssues.test.createBanner,
		formatPageIssuesSeverity = pageIssues.test.formatPageIssuesSeverity,
		MEDIUM_ISSUE = {
			severity: 'MEDIUM',
			icon: 'i',
			text: 't'
		},
		MEDIUM_MULTIPLE_ISSUE = {
			severity: 'MEDIUM',
			isMultiple: true,
			icon: 'i',
			text: 't'
		},
		LOW_MULTIPLE_ISSUE = {
			severity: 'LOW',
			isMultiple: true,
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

	// NOTE: Only for PageIssues AB
	QUnit.test( 'clicking on the product of createBanner() should trigger a custom event', function ( assert ) {
		var mockAction = {
			action: 'issueClicked',
			issuesSeverity: [ 'MEDIUM' ],
			sectionNumbers: [ SECTION ]
		};
		mw.trackSubscribe( 'minerva.PageIssuesAB', function ( topic, data ) {
			assert.deepEqual( mockAction, data );
		} );
		processedAmbox.click();
	} );

	QUnit.test( 'formatPageIssuesSeverity', function ( assert ) {
		var multipleIssues = [
				MEDIUM_MULTIPLE_ISSUE,
				LOW_MULTIPLE_ISSUE,
				LOW_MULTIPLE_ISSUE
			],
			multipleSingleIssues = [
				LOW_ISSUE,
				HIGH_ISSUE,
				MEDIUM_ISSUE
			],
			mixedMultipleSingle = [
				HIGH_ISSUE,
				LOW_MULTIPLE_ISSUE,
				MEDIUM_MULTIPLE_ISSUE,
				LOW_ISSUE,
				MEDIUM_ISSUE,
				HIGH_ISSUE
			],
			testMultiple = multipleIssues.reduce( formatPageIssuesSeverity, [] ),
			testSingle = multipleSingleIssues.reduce( formatPageIssuesSeverity, [] ),
			testMixed = mixedMultipleSingle.reduce( formatPageIssuesSeverity, [] );

		assert.deepEqual( testMultiple, [ 'MEDIUM' ], 'Multiple issues return one maxSeverity value' );
		assert.deepEqual( testSingle, [ 'LOW', 'HIGH', 'MEDIUM' ], 'Single issues return each corresponding severity' );
		assert.deepEqual( testMixed, [ 'HIGH', 'MEDIUM', 'LOW', 'MEDIUM', 'HIGH' ], 'Mixed single/multiple return one value for multiples' );

	} );

	QUnit.test( 'extractMessage', function ( assert ) {
		this.sandbox.stub( pageIssuesParser, 'parse' ).returns(
			{
				severity: 'LOW',
				icon: {
					toHtmlString: function () {
						return '<icon />';
					}
				}
			}
		);
		[
			[
				$( '<div />' ).html(
					'<div class="mbox-text">Smelly</div>'
				).appendTo( '<div class="mw-collapsible-content" />' ),
				{
					severity: 'LOW',
					isMultiple: true,
					icon: '<icon />',
					text: '<p>Smelly</p>'
				},
				'When the box is a child of mw-collapsible-content it isMultiple'
			],
			[
				$( '<div />' ).html(
					'<div class="mbox-text">Dirty</div>'
				),
				{
					severity: 'LOW',
					isMultiple: false,
					icon: '<icon />',
					text: '<p>Dirty</p>'
				},
				'When the box is not child of mw-collapsible-content it !isMultiple'
			]
		].forEach( function ( test ) {
			assert.deepEqual(
				extractMessage( test[ 0 ] ),
				test[ 1 ],
				test[ 2 ]
			);
		} );
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
				util.extend( {}, MEDIUM_ISSUE, { isMultiple: true } ),
				util.extend( {}, LOW_ISSUE, { isMultiple: true } ),
				util.extend( {}, MEDIUM_ISSUE, { isMultiple: true } )
			]
		};
		multipleIssuesWithDeletion = {
			0: [
				HIGH_ISSUE,
				util.extend( {}, MEDIUM_ISSUE, { isMultiple: true } ),
				util.extend( {}, LOW_ISSUE, { isMultiple: true } ),
				util.extend( {}, MEDIUM_ISSUE, { isMultiple: true } )
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
