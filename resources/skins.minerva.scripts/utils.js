( function ( M ) {

	var Icon = M.require( 'mobile.startup/Icon' );

	function getIconFromAmbox( $box ) {
		var glyph,
			names = [
				'speedy',
				'delete',
				'protection',
				'pov',
				'move',
				'style',
				'content'
			];

		// since objects have no concept of ordering we repeat ourselves here
		names.forEach( function ( name ) {
			if ( !glyph && $box.hasClass( 'ambox-' + name ) ) {
				// with a match, exit
				glyph = name;
			}
		} );

		glyph = glyph || 'default';
		return new Icon( {
			glyphPrefix: 'minerva',
			name: 'issue-' + glyph
		} );
	}

	/**
	 * @module skins.minerva.scripts/utils
	 */
	M.define( 'skins.minerva.scripts/utils', {
		/**
		 * Extract an icon for use with the issue.
		 * @param {jQuery.Object} $box element to extract the icon from
		 * @return {Icon} representing the icon
		 */
		getIconFromAmbox: getIconFromAmbox
	} );

}( mw.mobileFrontend ) );
