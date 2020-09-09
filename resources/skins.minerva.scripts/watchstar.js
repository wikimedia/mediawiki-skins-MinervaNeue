var WATCHED_CLASS = 'mw-ui-icon-wikimedia-unStar-progressive',
	TEMP_WATCHED_CLASS = 'mw-ui-icon-wikimedia-halfStar-progressive',
	UNWATCHED_CLASS = 'mw-ui-icon-wikimedia-star-base20';

/**
 * Tweaks the global watchstar handler in core to use the correct classes for Minerva.
 *
 * @param {jQuery.Object} $icon
 */
module.exports = function init( $icon ) {
	$icon.on( 'watchpage.mw', function ( _ev, action, expiry ) {
		$( this ).find( 'a' ).removeClass(
			[ WATCHED_CLASS, 'watched', TEMP_WATCHED_CLASS, UNWATCHED_CLASS ]
		).addClass( function () {
			var classes = UNWATCHED_CLASS;
			if ( action === 'watch' ) {
				classes = [ 'watched' ];
				if ( expiry !== null ) {
					classes.push( TEMP_WATCHED_CLASS );
				} else {
					classes.push( WATCHED_CLASS );
				}
			}

			return classes;
		} );
	} );
};
