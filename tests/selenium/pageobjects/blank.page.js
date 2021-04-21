'use strict';

const Page = require( 'wdio-mediawiki/Page' );

class BlankPage extends Page {
	get mobileView() { return $( '#footer-places-mobileview' ); }

	open() {
		super.openTitle( 'Special:BlankPage', { uselang: 'en' } );
	}
}

module.exports = new BlankPage();
