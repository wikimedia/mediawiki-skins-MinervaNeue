var WATCHED_CLASS = 'mw-ui-icon-wikimedia-unStar-progressive',
	UNWATCHED_CLASS = 'mw-ui-icon-wikimedia-star-base20';

/**
 * Tweaks the global watchstar handler in core to use the correct classes for Minerva.
 * @param {jQuery.Object} $icon
 */
module.exports = function init( $icon ) {
	$icon.on( 'watchpage.mw', function ( _ev, action ) {
		$( this ).find( 'a' ).removeClass(
			[ WATCHED_CLASS, 'watched', UNWATCHED_CLASS ]
		).addClass(
			action === 'watch' ? [ WATCHED_CLASS, 'watched' ] : UNWATCHED_CLASS
		);
	} );
};
