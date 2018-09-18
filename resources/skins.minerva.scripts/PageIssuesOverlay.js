( function ( M, mwMsg ) {
	var
		Overlay = M.require( 'mobile.startup/Overlay' ),
		util = M.require( 'mobile.startup/util' ),
		KEYWORD_ALL_SECTIONS = 'all',
		NS_MAIN = 0,
		NS_TALK = 1,
		NS_CATEGORY = 14;

	/**
	 * Overlay for displaying page issues
	 * @class PageIssuesOverlay
	 * @extends Overlay
	 *
	 * @param {IssueSummary[]} issues list of page issue summaries for display.
	 * @param {PageIssuesLogger} logger E.g., { log: console.log }.
	 * @param {string} section
	 * @param {number} namespaceID
	 */
	function PageIssuesOverlay( issues, logger, section, namespaceID ) {
		var
			options,
			// Note only the main namespace is expected to make use of section issues, so the
			// heading will always be minerva-meta-data-issues-section-header regardless of
			// namespace.
			headingText = section === '0' || section === KEYWORD_ALL_SECTIONS ?
				getNamespaceHeadingText( namespaceID ) :
				mwMsg( 'minerva-meta-data-issues-section-header' );

		this.issues = issues;
		this.logger = logger;
		this.section = section;

		options = {};
		options.issues = issues;

		// Set default logging data
		this.defaultLoggerData = {};
		// In the case of KEYWORD_ALL_SECTIONS all issues are in the overlay and the sectionNumbers
		// field should be no different from the default behaviour.
		if ( this.section !== KEYWORD_ALL_SECTIONS ) {
			this.defaultLoggerData.sectionNumbers = [ this.section ];
		}
		options.heading = '<strong>' + headingText + '</strong>';
		Overlay.call( this, options );

		this.on( Overlay.EVENT_EXIT, this.onExit.bind( this ) );
	}

	OO.mfExtend( PageIssuesOverlay, Overlay, {
		/**
		 * @memberof PageIssuesOverlay
		 * @instance
		 */
		className: 'overlay overlay-issues',

		/**
		 * @memberof PageIssuesOverlay
		 * @instance
		 */
		events: util.extend( {}, Overlay.prototype.events, {
			'click a[href*=redlink]': 'onRedLinkClick',
			'click a:not(.external):not([href*=edit])': 'onInternalClick',
			// Only register attempts to edit an existing page (should be the one we are on),
			// not internal clicks on redlinks to nonexistent pages:
			'click a[href*="edit"]:not([href*=redlink])': 'onEditClick'
		} ),

		/**
		 * @memberof PageIssuesOverlay
		 * @instance
		 */
		templatePartials: util.extend( {}, Overlay.prototype.templatePartials, {
			content: mw.template.get( 'skins.minerva.scripts', 'PageIssuesOverlayContent.hogan' )
		} ),

		/**
		 * Log data via the associated logger, adding sectionNumbers to override the event default
		 * if applicable.
		 * @param {Object} data
		 * @instance
		 */
		log: function ( data ) {
			this.logger.log( util.extend( {}, this.defaultLoggerData, data ) );
		},

		/**
		 * Note: an "on enter" state is tracked by the issueClicked log event.
		 * @return {void}
		 */
		onExit: function () {
			var logData = {
					action: 'modalClose',
					issuesSeverity: this.issues.map( issueSummaryToSeverity )
				},
				currentSection = this.section;
			// When users close the modal, `sectionNumbers` should correlate to each visible issue
			// in the modal, provided that this.section is a valid number and not
			// `KEYWORD_ALL_SECTIONS`.
			if ( this.section !== KEYWORD_ALL_SECTIONS ) {
				logData.sectionNumbers = this.issues.map( function () {
					return currentSection;
				} );
			}
			this.log( logData );
		},

		/**
		 * Event that is triggered when an internal link inside the overlay is clicked.
		 * This event will not be triggered if the link contains the edit keyword,
		 * in which case onEditClick will be
		 * fired. This is primarily used for instrumenting page issues (see
		 * https://meta.wikimedia.org/wiki/Schema:PageIssues).
		 * @param {JQuery.Event} ev
		 * @memberof PageIssuesOverlay
		 * @instance
		 */
		onInternalClick: function ( ev ) {
			var severity = parseSeverity( this.$( ev.target ) );
			this.log( {
				action: 'modalInternalClicked',
				issuesSeverity: [ severity ]
			} );
		},

		/**
		 * Event that is triggered when a red link (e.g. a link to a page which doesn't exist)
		 * inside the overlay is clicked.
		 * @param {JQuery.Event} ev
		 * @memberof PageIssuesOverlay
		 * @instance
		 */
		onRedLinkClick: function ( ev ) {
			var severity = parseSeverity( this.$( ev.target ) );
			this.log( {
				action: 'modalRedLinkClicked',
				issuesSeverity: [ severity ]
			} );
		},

		/**
		 * Event that is triggered when an edit link inside the overlay is clicked.
		 * This is primarily
		 * used for instrumenting page issues (see https://meta.wikimedia.org/wiki/Schema:PageIssues).
		 * The event will not be triggered in the case of red links.
		 * See onRedLinkClick for red links.
		 * @param {JQuery.Event} ev
		 * @memberof PageIssuesOverlay
		 * @instance
		 */
		onEditClick: function ( ev ) {
			var severity = parseSeverity( this.$( ev.target ) );
			this.log( {
				action: 'modalEditClicked',
				issuesSeverity: [ severity ]
			} );
		}
	} );

	/**
	 * Obtain severity associated with a given $target node by looking at associated parent node
	 * (defined by templatePartials, PageIssuesOverlayContent.hogan).
	 *
	 * @param {JQuery.Object} $target
	 * @return {string[]} severity as defined in associated PageIssue
	 */
	function parseSeverity( $target ) {
		return $target.parents( '.issue-notice' ).data( 'severity' );
	}

	/**
	 * @param {IssueSummary} issue
	 * @return {string} A PageIssue.severity.
	 */
	function issueSummaryToSeverity( issue ) {
		return issue.severity;
	}

	/**
	 * Obtain a suitable heading for the issues overlay based on the namespace
	 * @param {number} namespaceID is the namespace to generate heading for
	 * @return {string} heading for overlay
	 */
	function getNamespaceHeadingText( namespaceID ) {
		switch ( namespaceID ) {
			case NS_CATEGORY:
				return mw.msg( 'mobile-frontend-meta-data-issues-categories' );
			case NS_TALK:
				return mw.msg( 'mobile-frontend-meta-data-issues-talk' );
			case NS_MAIN:
				return mw.msg( 'mobile-frontend-meta-data-issues' );
			default:
				return '';
		}
	}

	M.define( 'skins.minerva.scripts/PageIssuesOverlay', PageIssuesOverlay );
}( mw.mobileFrontend, mw.msg ) );
