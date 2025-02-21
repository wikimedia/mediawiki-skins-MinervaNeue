'use strict';

const { ArticlePage } = require( '../support/world.js' );
const { iAmOnPage } = require( './common_steps' );
const { theTextOfTheFirstHeadingShouldBe } = require( './editor_steps' );

const username = browser.config.mwUser.replace( /_/g, ' ' );

const iVisitMyUserPage = async () => {
	await iAmOnPage( `User:${ username }` );
};

const iShouldBeOnMyUserPage = async () => {
	await theTextOfTheFirstHeadingShouldBe( username );
};

const thereShouldBeALinkToMyContributions = async () => {
	await expect( ArticlePage.contributions_link_element ).toBeDisplayed();
};

const thereShouldBeALinkToMyTalkPage = async () => {
	await expect( ArticlePage.talk_tab_element ).toBeDisplayed();
};

module.exports = { iVisitMyUserPage, iShouldBeOnMyUserPage,
	thereShouldBeALinkToMyContributions, thereShouldBeALinkToMyTalkPage };
