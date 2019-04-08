const { api } = require( '../support/world' ),
	Api = require( 'wdio-mediawiki/Api' );

const login = () => {
	return api.loginGetEditToken( {
		username: browser.options.username,
		password: browser.options.password,
		apiUrl: `${browser.options.baseUrl}/api.php`
	} );
};

const waitForPropagation = ( timeMs ) => {
	// wait 2 seconds so the change can propogate.
	const d = new Date();
	browser.waitUntil( () => new Date() - d > timeMs );
};

const iAmInAWikiThatHasCategories = ( title ) => {
	const msg = 'This page is used by Selenium to test category related features.',
		summary = 'edit by selenium test',
		wikitext = `
            ${msg}

            [[Category:Test category]]
            [[Category:Selenium artifacts]]
            [[Category:Selenium hidden category]]
        `;

	login().then( () => api.batch( [
		[ 'create', 'Category:Selenium artifacts', msg, summary ],
		[ 'create', 'Category:Test category', msg, summary ],
		[ 'create', 'Category:Selenium hidden category', '__HIDDENCAT__', summary ]
	] ) )
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

module.exports = {
	waitForPropagation,
	iAmInAWikiThatHasCategories
};
