( function ( M ) {
	var utils = M.require( 'skins.minerva.scripts/utils' ),
		getIconFromAmbox = utils.getIconFromAmbox;

	QUnit.module( 'Minerva utils' );
	QUnit.test( 'getIconFromAmbox', function ( assert ) {
		var tests = [
			[
				'', 'issue-default'
			],
			[
				'ambox', 'issue-default'
			],
			[
				'ambox-content ambox-speedy', 'issue-speedy'
			],
			[
				'ambox-content ambox-delete', 'issue-delete'
			],
			[
				'ambox-content', 'issue-content'
			],
			[
				'ambox-content ambox-pov', 'issue-pov'
			],
			[
				'ambox-content ambox-style', 'issue-style'
			],
			[
				'ambox-content ambox-move', 'issue-move'
			],
			[
				'ambox-content ambox-protection', 'issue-protection'
			]
		];
		tests.forEach( function ( test, i ) {
			var $box = $( '<div>' );
			$box.addClass( test[0] );
			assert.strictEqual(
				getIconFromAmbox( $box ).options.name,
				test[1],
				i
			);
		} );
	} );

}( mw.mobileFrontend ) );
