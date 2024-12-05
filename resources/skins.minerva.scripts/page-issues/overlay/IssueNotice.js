const
	mobile = require( 'mobile.startup' ),
	View = mobile.View;

/**
 * IssueNotice
 *
 * @class
 * @ignore
 * @extends View
 *
 * @param {IssueSummary} props
 */
class IssueNotice extends View {
	constructor( props ) {
		super( props );
	}

	get tagName() {
		return 'li';
	}

	get template() {
		return mw.template.get( 'skins.minerva.scripts', 'IssueNotice.mustache' );
	}

	postRender() {
		super.postRender();
		this.$el.find( '.issue-notice' ).prepend( this.options.issue.iconElement );
	}
}

module.exports = IssueNotice;
