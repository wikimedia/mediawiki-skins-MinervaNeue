'use strict';

const { iAmUsingTheMobileSite } = require( '../features/step_definitions/common_steps' ),
	{ iVisitMyUserPage, iShouldBeOnMyUserPage,
		thereShouldBeALinkToMyContributions, thereShouldBeALinkToMyTalkPage
	} = require( '../features/step_definitions/user_page_steps' );

// @chrome @firefox @login @test2.m.wikipedia.org @vagrant
describe( 'User:<username>', () => {

	beforeEach( async () => {
		await iAmUsingTheMobileSite();
		await iVisitMyUserPage();
	} );

	// </username>@en.m.wikipedia.beta.wmflabs.org
	// Skipped on 2023-04-06 because of T293084
	it.skip( 'Check components in user page', async () => {
		await iShouldBeOnMyUserPage();
		await thereShouldBeALinkToMyTalkPage();
		await thereShouldBeALinkToMyContributions();
	} );
} );
