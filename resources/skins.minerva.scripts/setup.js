/**
 * This setups the Minerva skin.
 * It should run without errors even if MobileFrontend is not installed.
 *
 * @ignore
 */
const ms = require( 'mobile.startup' );
const reportIfNightModeWasDisabledOnPage = require( './reportIfNightModeWasDisabledOnPage.js' );
const addPortletLink = require( './addPortletLink.js' );
const { teleportTarget, enableSearchDialog } = require( 'mediawiki.page.ready' );
const config = require( './config.json' );

function init() {
	const permissions = mw.config.get( 'wgMinervaPermissions' ) || {};
	const searchIcon = document.querySelector( '#searchIcon' );
	if ( config.MinervaTypeahead && config.MinervaTypeahead.enabled && searchIcon ) {
		enableSearchDialog( searchIcon );
	}

	// eslint-disable-next-line no-jquery/no-global-selector
	const $watch = $( '#page-actions-watch' );

	if ( permissions.watch ) {
		require( './watchstar.js' ).init( $watch );
	}

	addPortletLink.init();
	mw.hook( 'util.addPortletLink' ).add(
		addPortletLink.hookHandler
	);

	// Setup Minerva with MobileFrontend
	if ( ms && !ms.stub ) {
		require( './initMobile.js' )();
	}

	// This hot fix should be reviewed and possibly removed circa January 2021.
	// It's assumed that Apple will prioritize fixing this bug in one of its next releases.
	// See T264376.
	if ( navigator.userAgent.match( /OS 14_[0-9]/ ) ) {
		document.body.classList.add( 'hotfix-T264376' );
	}

	// Apply content styles to teleported elements
	teleportTarget.classList.add( 'content' );
	reportIfNightModeWasDisabledOnPage(
		document.documentElement, mw.user.options, mw.user.isNamed()
	);
}

if ( !window.QUnit ) {
	init();
}

module.exports = {
	// Version number allows breaking changes to be detected by other extensions
	VERSION: 1
};
