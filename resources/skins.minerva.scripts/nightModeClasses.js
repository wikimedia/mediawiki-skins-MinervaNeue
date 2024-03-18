const LEGACY_NIGHT = 'skin-night-mode-clientpref-1';
const LEGACY_AUTO = 'skin-night-mode-clientpref-2';
const THEME_NIGHT = 'skin-theme-clientpref-night';
const THEME_AUTO = 'skin-theme-clientpref-os';

/**
 * Prepares for rename in T359983. Ensures that the new classes are present in the DOM on page load.
 * Since the controls for mobile are only on Special:MobileOptions we only worry about page load and
 * assume they won't change later on.
 */
module.exports = function nightModeClasses() {
	const node = document.documentElement;

	if ( node.classList.contains( LEGACY_AUTO ) ) {
		node.classList.add( THEME_AUTO );
	}
	if ( node.classList.contains( LEGACY_NIGHT ) ) {
		node.classList.add( THEME_NIGHT );
	}
};
