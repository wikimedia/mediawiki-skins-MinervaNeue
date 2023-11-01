( function () {
	const
		mobile = require( 'mobile.startup' ),
		View = mobile.View,
		IssueNotice = require( './IssueNotice.js' );

	/**
	 * IssueList
	 *
	 * @class IssueList
	 * @extends View
	 *
	 * @param {IssueSummary} issues
	 */
	class IssueList extends View {
		constructor( issues ) {
			super( { className: 'cleanup' } );
			this.issues = issues;
			this.tagName = 'ul';
		}
		postRender() {
			super.postRender();
			this.append(
				this.issues.map( function ( issue ) {
					return new IssueNotice( issue ).$el;
				} )
			);
		}
	}
	module.exports = IssueList;

}() );
