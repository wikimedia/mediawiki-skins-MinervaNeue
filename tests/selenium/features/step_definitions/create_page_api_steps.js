const { api, ArticlePage } = require( '../support/world' );
const Api = require( 'wdio-mediawiki/Api' );
const {
	iAmOnPage,
	createPages,
	createPage
} = require( './common_steps' );

const waitForPropagation = ( timeMs ) => {
	// wait 2 seconds so the change can propogate.
	const d = new Date();
	browser.waitUntil( () => new Date() - d > timeMs );
};

const iAmInAWikiThatHasCategories = ( title ) => {
	const msg = 'This page is used by Selenium to test category related features.',
		wikitext = `
            ${msg}

            [[Category:Test category]]
            [[Category:Selenium artifacts]]
            [[Category:Selenium hidden category]]
        `;

	createPages( [
		[ 'create', 'Category:Selenium artifacts', msg ],
		[ 'create', 'Category:Test category', msg ],
		[ 'create', 'Category:Selenium hidden category', '__HIDDENCAT__' ]
	] )
		.catch( ( err ) => {
			if ( err.code === 'articleexists' ) {
				return;
			}
			throw err;
		} );

	// A pause is necessary to let the categories register with database before trying to use
	// them in an article
	waitForPropagation( 5000 );
	Api.edit( title, wikitext );
	// categories are handled by a JobRunner so need extra time to appear via API calls!
	waitForPropagation( 5000 );
};

const iAmOnAPageThatHasTheFollowingEdits = function ( table ) {
	const randomString = Math.random().toString( 36 ).substring( 7 ),
		pageTitle = `Selenium_diff_test_${randomString}`,
		edits = table.rawTable.map( ( row, i ) =>
			[ i === 0 ? 'create' : 'edit', pageTitle, row[ 0 ] ] );

	api.loginGetEditToken( {
		username: browser.options.username,
		password: browser.options.password,
		apiUrl: `${browser.options.baseUrl}/api.php`
	} )
		.then( () => api.batch( edits ) )
		.then( () => ArticlePage.open( pageTitle ) )
		.catch( ( err ) => { throw err; } );
	waitForPropagation( 5000 );
};

const iGoToAPageThatHasLanguages = () => {
	const wikitext = `This page is used by Selenium to test language related features.

	[[es:Selenium language test page]]
`;

	return createPage( 'Selenium language test page', wikitext ).then( () => {
		iAmOnPage( 'Selenium language test page' );
	} );
};

module.exports = {
	waitForPropagation,
	iAmOnAPageThatHasTheFollowingEdits,
	iAmInAWikiThatHasCategories,
	iGoToAPageThatHasLanguages
};
