const assert = require( 'assert' ),
	{ ArticlePage, SpecialHistoryPage,
		SpecialMobileDiffPage } = require( '../support/world.js' );

const iClickOnTheHistoryLinkInTheLastModifiedBar = () => {
	ArticlePage.last_modified_bar_history_link_element.waitForVisible();
	ArticlePage.last_modified_bar_history_link_element.click();
	assert.strictEqual( SpecialHistoryPage.side_list_element.isVisible(), true );
};

const iOpenTheLatestDiff = () => {
	SpecialHistoryPage.last_contribution_link_element.waitForExist();
	SpecialHistoryPage.last_contribution_link_element.click();
	assert.strictEqual( SpecialMobileDiffPage.user_info_element.isVisible(), true );
};

module.exports = {
	iOpenTheLatestDiff,
	iClickOnTheHistoryLinkInTheLastModifiedBar
};
