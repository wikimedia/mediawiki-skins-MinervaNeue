const { iGoToAPageThatHasLanguages } = require( './../features/step_definitions/create_page_api_steps' ),
	{
		pageExists, iAmOnAPageThatDoesNotExist, iShouldSeeAToastNotification,
		iAmLoggedIntoTheMobileWebsite
	} = require( './../features/step_definitions/common_steps' ),
	{
		iClickTheEditButton, iSeeTheWikitextEditorOverlay, iClearTheEditor,
		iDoNotSeeTheWikitextEditorOverlay,
		iTypeIntoTheEditor, iClickContinue, iClickSubmit, iSayOkayInTheConfirmDialog,
		theTextOfTheFirstHeadingShouldBe, thereShouldBeARedLinkWithText
	} = require( './../features/step_definitions/editor_steps' );

// @test2.m.wikipedia.org @login
describe( 'Wikitext Editor (Makes actual saves)', () => {

	beforeEach( () => {
		iAmLoggedIntoTheMobileWebsite();
	} );

	// @editing
	it( 'It is possible to edit', () => {
		iGoToAPageThatHasLanguages();
		iClickTheEditButton();
		iSeeTheWikitextEditorOverlay();
		iTypeIntoTheEditor( 'ABC GHI' );
		iClickContinue();
		iClickSubmit();
		iDoNotSeeTheWikitextEditorOverlay();
		iShouldSeeAToastNotification();
	} );

	// @editing @en.m.wikipedia.beta.wmflabs.org
	it( 'Redirects', () => {
		const title = 'Selenium wikitext editor test ' + Math.random();
		pageExists( title );
		iAmOnAPageThatDoesNotExist();
		iClickTheEditButton();
		iSeeTheWikitextEditorOverlay();
		iClearTheEditor();
		iTypeIntoTheEditor( `#REDIRECT [[${title}]]` );
		iClickContinue();
		iClickSubmit();
		iSayOkayInTheConfirmDialog();
		iDoNotSeeTheWikitextEditorOverlay();
		theTextOfTheFirstHeadingShouldBe( title );
	} );

	// @editing @en.m.wikipedia.beta.wmflabs.org
	it( 'Broken redirects', () => {
		iAmOnAPageThatDoesNotExist();
		iClickTheEditButton();
		iSeeTheWikitextEditorOverlay();
		iClearTheEditor();
		iTypeIntoTheEditor( '#REDIRECT [[AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA]]' );
		iClickContinue();
		iClickSubmit();
		iSayOkayInTheConfirmDialog();
		iDoNotSeeTheWikitextEditorOverlay();
		thereShouldBeARedLinkWithText( 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' );
	} );
} );
