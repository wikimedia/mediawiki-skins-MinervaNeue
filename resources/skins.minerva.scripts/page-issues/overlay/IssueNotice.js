( function ( M ) {
	var View = M.require( 'mobile.startup' ).View;

	/**
	 * IssueNotice
	 * @class IssueNotice
	 * @extends View
	 *
	 * @param {IssueSummary} props
	 */
	function IssueNotice( props ) {
		View.call( this, props );
	}
	OO.mfExtend( IssueNotice, View, {
		tagName: 'li',
		template: mw.template.get( 'skins.minerva.scripts', 'IssueNotice.hogan' ),
		postRender: function () {
			View.prototype.postRender.apply( this, arguments );
			this.$( '.issue-notice' ).prepend( this.options.issue.icon.$el );
		}
	} );
	M.define( 'skins.minerva.scripts/IssueNotice', IssueNotice );
}( mw.mobileFrontend ) );
