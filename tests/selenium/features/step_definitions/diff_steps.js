const assert = require( 'assert' ),
	{ DiffPage } = require( '../support/world.js' );

const iShouldSeeAddedContent = ( text ) => {
	DiffPage.inserted_content_element.waitForVisible();
	assert.strictEqual( DiffPage.inserted_content_element.getText(), text );
};
const iShouldSeeRemovedContent = ( text ) => {
	assert.strictEqual( DiffPage.deleted_content_element.getText(), text );
};

module.exports = { iShouldSeeAddedContent, iShouldSeeRemovedContent };
