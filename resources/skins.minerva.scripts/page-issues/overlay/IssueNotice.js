( function () {
	const
		mobile = require( 'mobile.startup' ),
		View = mobile.View;

	/**
	 * IssueNotice
	 *
	 * @class IssueNotice
	 * @extends View
	 *
	 * @param {IssueSummary} props
	 */
	class IssueNotice extends View {
		constructor() {
			super( { className: 'cleanup' } );
			this.tagName = 'li';
			this.template = mw.template.get( 'skins.minerva.scripts', 'IssueNotice.mustache' );
		}
		postRender() {
			super.postRender();
			this.$el.find( '.issue-notice' ).prepend( this.options.issue.icon.$el );
		}
	}
	module.exports = IssueNotice;

}() );
