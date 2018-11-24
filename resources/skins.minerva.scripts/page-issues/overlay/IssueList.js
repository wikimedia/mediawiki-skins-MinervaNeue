( function ( M ) {
	var View = M.require( 'mobile.startup' ).View,
		IssueNotice = M.require( 'skins.minerva.scripts/IssueNotice' );

	/**
	 * IssueList
	 * @class IssueList
	 * @extends View
	 *
	 * @param {IssueSummary} issues
	 */
	function IssueList( issues ) {
		this.issues = issues;
		View.call( this, { className: 'cleanup' } );
	}

	OO.mfExtend( IssueList, View, {
		tagName: 'ul',
		postRender: function () {
			View.prototype.postRender.apply( this, arguments );
			this.append(
				this.issues.map( function ( issue ) {
					return new IssueNotice( issue ).$el;
				} )
			);
		}
	} );

	M.define( 'skins.minerva.scripts/IssueList', IssueList );
}( mw.mobileFrontend ) );
