const Vue = require( 'vue' );
const router = require( 'mediawiki.router' );
const {
	App, restSearchClient, urlGenerator
} = require( 'mediawiki.skinning.typeaheadSearch' );
const urlGeneratorInstance = urlGenerator( mw.config.get( 'wgScript' ) );
const searchConfig = require( './searchConfig.json' ).MinervaTypeahead;
const recommendationApiUrl = searchConfig.recommendationApiUrl;
const searchApiUrl = searchConfig.apiUrl || `${ mw.config.get( 'wgScriptPath' ) }/rest.php`;
const restClient = restSearchClient( searchApiUrl, urlGeneratorInstance, recommendationApiUrl );
let appDefaults;

/**
 * @ignore
 * @return {Object}
 */
function getSearchProps() {
	if ( appDefaults ) {
		return appDefaults;
	}
	const searchInput = document.getElementById( 'searchInput' );
	const searchForm = document.querySelector( '.minerva-header .minerva-search-form' );
	if ( !searchInput || !searchForm ) {
		throw new Error( 'Minerva missing .minerva-search-form or #searchInput' );
	}
	const action = searchForm.getAttribute( 'action' );
	const autocapitalizeValue = searchInput.getAttribute( 'autocapitalize' );
	const searchAccessKey = searchInput.getAttribute( 'accessKey' );
	const searchTitle = searchInput.getAttribute( 'title' );
	const searchPlaceholder = searchInput.getAttribute( 'placeholder' );
	const titleInput = document.querySelector( '.minerva-header input[name=title]' );
	const searchQuery = searchInput.value;
	const searchPageTitle = titleInput && titleInput.value;
	const searchTerm = searchInput.value;
	appDefaults = {
		router,
		restClient,
		supportsMobileExperience: true,
		urlGenerator: urlGeneratorInstance,
		id: 'minerva-overlay-search',
		autofocusInput: true,
		searchButtonLabel: '',
		autoExpandWidth: false,
		showThumbnail: true,
		showEmptySearchRecommendations: !!recommendationApiUrl,
		showDescription: true,
		action,
		searchQuery,
		searchTitle,
		searchTerm,
		searchPlaceholder,
		searchPageTitle,
		autocapitalizeValue,
		searchAccessKey
	};
	return appDefaults;
}

/**
 * @ignore
 * @return {Vue.Component}
 */
function renderTypeaheadSearch() {
	return Vue.createMwApp(
		App,
		getSearchProps()
	);
}

let searchTypeaheadInitialized = false;

/**
 * @ignore
 * @param {boolean} useOverlay whether the overlay should be launched.
 * @return {Promise}
 */
function searchTypeahead() {
	// On first run we setup Vue app defaults, load and render the App.
	// Since all these elements will get destroyed they are cached
	// to appDefaults for future runs.
	if ( !searchTypeaheadInitialized ) {
		const app = renderTypeaheadSearch();
		app.mount( document.querySelector( 'header .minerva-search-form .search-box' ) );
		searchTypeaheadInitialized = true;
	}
}

module.exports = {
	searchTypeahead
};
