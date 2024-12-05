const
	mobile = require( 'mobile.startup' ),
	View = mobile.View,
	IssueNotice = require( './IssueNotice.js' );

/**
 * IssueList
 *
 * @class
 * @ignore
 * @extends View
 *
 * @param {IssueSummary} issues
 */
class IssueList extends View {
	constructor( issues ) {
		super( {
			className: 'cleanup',
			issues
		} );
	}

	get tagName() {
		return 'ul';
	}

	postRender() {
		super.postRender();
		this.append(
			( this.options.issues || [] ).map( ( issue ) => new IssueNotice( issue ).$el )
		);
	}
}

module.exports = IssueList;
