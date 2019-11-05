const { iAmOnAPageWithNoTalkTopics } = require( '../features/step_definitions/create_page_api_steps' ),
	{
		pageExists, iAmOnAPageThatDoesNotExist,
		iAmUsingTheMobileSite,
		iAmLoggedIntoTheMobileWebsite,
		iAmOnPage
	} = require( '../features/step_definitions/common_steps' ),
	{
		iClickTheTalkButton,
		iAddATopic,
		iSeeTheTalkOverlay,
		thereShouldBeASaveDiscussionButton,
		noTopicIsPresent,
		thereShouldBeAnAddDiscussionButton,
		thereShouldBeNoTalkButton,
		iShouldSeeTheTopicInTheListOfTopics
	} = require( '../features/step_definitions/talk_steps' );

// @chrome @en.m.wikipedia.beta.wmflabs.org @firefox @test2.m.wikipedia.org @vagrant
describe( 'Talk', () => {

	before( () => {
		pageExists( 'Talk:Selenium talk test' );
		pageExists( 'Selenium talk test' );
	} );

	beforeEach( () => {
		iAmUsingTheMobileSite();
	} );

	it( 'Add discussion on talk page not possible as logged out user', () => {
		iAmOnPage( 'Selenium talk test' );
		thereShouldBeNoTalkButton();
	} );

	// @login
	it( 'Talk on a page that does exist', () => {
		iAmLoggedIntoTheMobileWebsite();
		iAmOnPage( 'Selenium talk test' );
		iClickTheTalkButton();
		iSeeTheTalkOverlay();
	} );

	// @login
	it( 'Talk on a page that doesn\'t exist (bug 64268)', () => {
		iAmLoggedIntoTheMobileWebsite();
		iAmOnAPageThatDoesNotExist();
		iClickTheTalkButton();
		iSeeTheTalkOverlay();
	} );

	// @smoke @login
	it( 'Add discussion for talk page possible as logged in user', () => {
		iAmLoggedIntoTheMobileWebsite();
		iAmOnPage( 'Selenium talk test' );
		iClickTheTalkButton();
		thereShouldBeAnAddDiscussionButton();
	} );

	// @smoke @login
	it( 'Add topic button shows on talk pages for logged in users', () => {
		iAmLoggedIntoTheMobileWebsite();
		iAmOnAPageThatDoesNotExist();
		iAmOnPage( 'Talk:Selenium talk test' );
		iClickTheTalkButton();
		thereShouldBeASaveDiscussionButton();
	} );

	it( 'A newly created topic appears in the list of topics immediately', () => {
		iAmLoggedIntoTheMobileWebsite();
		iAmOnAPageWithNoTalkTopics();
		iClickTheTalkButton();
		iSeeTheTalkOverlay();
		noTopicIsPresent();
		iAddATopic( 'New topic' );
		iShouldSeeTheTopicInTheListOfTopics( 'New topic' );
	} );

} );
