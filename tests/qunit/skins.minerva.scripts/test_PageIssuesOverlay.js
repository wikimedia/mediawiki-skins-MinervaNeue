( function ( M ) {
	var PageIssuesOverlay = M.require( 'skins.minerva.scripts/PageIssuesOverlay' );

	QUnit.module( 'Minerva PageIssuesOverlay', {
		beforeEach: function () {
			this.logger = {
				log: this.sandbox.spy()
			};
		}
	} );

	QUnit.test( '#log (section=all)', function ( assert ) {
		var overlay = new PageIssuesOverlay( [], this.logger, 'all', 0 );
		overlay.onExit();
		assert.strictEqual( this.logger.log.calledOnce, true, 'Logger called once' );
		assert.strictEqual(
			this.logger.log.calledWith( {
				action: 'modalClose',
				issuesSeverity: []
			} ), true, 'sectionNumbers is not set (T202940)'
		);
	} );

	QUnit.test( '#log (section=1)', function ( assert ) {
		var overlay = new PageIssuesOverlay( [
			{
				severity: 'MEDIUM'
			}
		], this.logger, '1', 0 );
		overlay.onExit();
		assert.strictEqual(
			this.logger.log.calledWith( {
				action: 'modalClose',
				issuesSeverity: [ 'MEDIUM' ],
				sectionNumbers: [ '1' ]
			} ), true, 'sectionNumbers is set'
		);
	} );

	QUnit.test( '#log (section=2) multiple issues', function ( assert ) {
		var overlay = new PageIssuesOverlay(
			[ {
				severity: 'MEDIUM'
			},
			{
				severity: 'LOW'
			} ], this.logger, '2', 0 );
		overlay.onExit();
		assert.strictEqual(
			this.logger.log.calledWith( {
				action: 'modalClose',
				issuesSeverity: [ 'MEDIUM', 'LOW' ],
				sectionNumbers: [ '2', '2' ]
			} ), true, 'sectionNumbers is set for each issue'
		);
	} );
}( mw.mobileFrontend ) );
