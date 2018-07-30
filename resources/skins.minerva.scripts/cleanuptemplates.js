( function ( M, $ ) {
	var AB = M.require( 'skins.minerva.scripts/AB' ),
		allIssues = {},
		KEYWORD_ALL_SECTIONS = 'all',
		ACTION_EDIT = mw.config.get( 'wgAction' ) === 'edit',
		NS_MAIN = 0,
		NS_TALK = 1,
		NS_CATEGORY = 14,
		isInGroupB = new AB( {
			testName: 'WME.PageIssuesAB',
			samplingRate: mw.config.get( 'wgMinervaABSamplingRate', 0 ),
			sessionId: mw.user.sessionId(),
			onABStart: function ( bucket ) {
				// See: https://gerrit.wikimedia.org/r/#/c/mediawiki/extensions/WikimediaEvents/+/437686/
				var READING_DEPTH_BUCKET_KEYS = {
						A: 'page-issues-a_sample',
						B: 'page-issues-b_sample'
					},
					readingDepthBucket = READING_DEPTH_BUCKET_KEYS[ bucket ];
				mw.track( 'wikimedia.event.ReadingDepthSchema.enable', readingDepthBucket );
			}
		} ).getBucket() === 'B',
		Icon = M.require( 'mobile.startup/Icon' ),
		page = M.getCurrentPage(),
		pageIssueParser = M.require( 'skins.minerva.scripts/pageIssueParser' ),
		overlayManager = M.require( 'skins.minerva.scripts/overlayManager' ),
		CleanupOverlay = M.require( 'mobile.issues/CleanupOverlay' );

	/**
	 * Extract a summary message from a cleanup template generated element that is
	 * friendly for mobile display.
	 * @param {Object} $box element to extract the message from
	 * @ignore
	 * @typedef {Object} IssueSummary
	 * @prop {PageIssue} pageIssue
	 * @prop {string} icon HTML string.
	 * @prop {string} text HTML string.
	 * @return {IssueSummary}
	 */
	function extractMessage( $box ) {
		var selector = '.mbox-text, .ambox-text',
			$container = $( '<div>' ),
			pageIssue;

		$box.find( selector ).each( function () {
			var contents,
				$this = $( this );
			// Clean up talk page boxes
			$this.find( 'table, .noprint' ).remove();
			contents = $this.html();

			if ( contents ) {
				$( '<p>' ).html( contents ).appendTo( $container );
			}
		} );

		pageIssue = pageIssueParser.parse( $box.get( 0 ) );
		return {
			pageIssue: pageIssue,
			icon: pageIssue.icon.toHtmlString(),
			text: $container.html()
		};
	}

	/**
	 * Create a link element that opens the issues overlay.
	 *
	 * @ignore
	 *
	 * @param {string} labelText The text value of the element
	 * @return {JQuery}
	 */
	function createLinkElement( labelText ) {
		return $( '<a class="cleanup mw-mf-cleanup"></a>' )
			.text( labelText );
	}

	/**
	 * Render a banner in a containing element.
	 * if in group B, a learn more link will be append to any amboxes inside $container
	 * if in group A or control, any amboxes in container will be removed and a link "page issues"
	 * will be rendered above the heading.
	 * This function comes with side effects. It will populate a global "allIssues" object which
	 * will link section numbers to issues.
	 * @param {JQuery.Object} $container to render the page issues banner inside.
	 * @param {string} labelText what the label of the page issues banner should say
	 * @param {number|string} section that the banner and its issues belong to.
	 *  If string KEYWORD_ALL_SECTIONS banner should apply to entire page.
	 * @param {boolean} inline - if true the first ambox in the section will become the entry point for the issues overlay
	 *  and if false, a link will be rendered under the heading.
	 * @ignore
	 */
	function createBanner( $container, labelText, section, inline ) {
		var $learnMore,
			issueUrl = section === KEYWORD_ALL_SECTIONS ? '#/issues/' + KEYWORD_ALL_SECTIONS : '#/issues/' + section,
			selector = 'table.ambox, table.tmbox, table.cmbox, table.fmbox',
			$metadata = $container.find( selector ),
			issues = [],
			$link,
			severity;

		// clean it up a little
		$metadata.find( '.NavFrame' ).remove();
		$metadata.each( function () {
			var issue,
				$this = $( this );

			if ( $this.find( selector ).length === 0 ) {
				issue = extractMessage( $this );
				// Some issues after "extractMessage" has been run will have no text.
				// For example in Template:Talk header the table will be removed and no issue found.
				// These should not be rendered.
				if ( issue.text ) {
					issues.push( issue );
				}
			}
		} );
		// store it for late
		allIssues[section] = issues;

		if ( inline ) {
			severity = pageIssueParser.maxSeverity(
				issues.map( function ( issue ) { return issue.pageIssue; } )
			);
			new Icon( {
				glyphPrefix: 'minerva',
				name: pageIssueParser.iconName( $metadata, severity )
			} ).prependTo( $metadata.find( '.mbox-text' ) );
			$learnMore = $( '<span>' )
				.addClass( 'ambox-learn-more' )
				.text( mw.msg( 'skin-minerva-issue-learn-more' ) );
			if ( $( '.mw-collapsible-content' ).length ) {
				// e.g. Template:Multiple issues
				$learnMore.insertAfter( $metadata.find( '.mbox-text-span' ) );
			} else {
				// e.g. Template:merge from
				$learnMore.appendTo( $metadata.find( '.mbox-text' ) );
			}
			$metadata.click( function () {
				overlayManager.router.navigate( issueUrl );
				return false;
			} );
		} else {
			$link = createLinkElement( labelText );
			// In group B, we link to all issues no matter where the banner is.
			$link.attr( 'href', '#/issues/' + KEYWORD_ALL_SECTIONS );
			if ( $metadata.length ) {
				$link.insertAfter( $( 'h1#section_0' ) );
				$metadata.remove();
			}
		}
	}

	/**
	 * Obtains the list of issues for the current page and provided section
	 * @param {number|string} section either KEYWORD_ALL_SECTIONS or a number relating to the section
	 *  the issues belong to
	 * @return {jQuery.Object[]} array of all issues.
	 */
	function getIssues( section ) {
		if ( section === KEYWORD_ALL_SECTIONS ) {
			// Note section.all may not exist, depending on the structure of the HTML page.
			// It will only exist when Minerva has been run in desktop mode.
			// If it's absent, we'll reduce all the other lists into one.
			return allIssues.all || Object.keys( allIssues ).reduce(
				function ( all, key ) {
					return all.concat( allIssues[key] );
				},
				[]
			);
		} else {
			return allIssues[section] || [];
		}
	}

	/**
	 * Obtain a suitable heading for the issues overlay based on the namespace
	 * @param {number} ns is the namespace to generate heading for
	 * @return {string} heading for overlay
	 */
	function getNamespaceHeadingText( ns ) {
		switch ( ns ) {
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

	/**
	 * Scan an element for any known cleanup templates and replace them with a button
	 * that opens them in a mobile friendly overlay.
	 * @ignore
	 */
	function initPageIssues() {
		var ns = mw.config.get( 'wgNamespaceNumber' ),
			label,
			headingText = ACTION_EDIT ? mw.msg( 'edithelp' ) : getNamespaceHeadingText( ns ),
			$lead = page.getLeadSectionElement(),
			issueOverlayShowAll = ns === NS_CATEGORY || ns === NS_TALK || ACTION_EDIT || !$lead,
			inline = isInGroupB && ns === 0,
			$container = $( '#bodyContent' );

		// set A-B test class.
		$( 'html' ).addClass( isInGroupB ? 'issues-group-B' : 'issues-group-A' );

		if ( ACTION_EDIT ) {
			// Editor uses different parent element
			$container = $( '#mw-content-text' );
			createBanner( $container, mw.msg( 'edithelp' ), KEYWORD_ALL_SECTIONS, inline );
		} else if ( ns === NS_TALK || ns === NS_CATEGORY ) {
			// e.g. Template:English variant category; Template:WikiProject
			createBanner( $container, mw.msg( 'mobile-frontend-meta-data-issues-header-talk' ), KEYWORD_ALL_SECTIONS, inline );
		} else if ( ns === NS_MAIN ) {
			label = mw.msg( 'mobile-frontend-meta-data-issues-header' );
			if ( issueOverlayShowAll ) {
				createBanner( $container, label, KEYWORD_ALL_SECTIONS, inline );
			} else {
				// parse lead
				createBanner( $lead, label, 0, inline );
				if ( isInGroupB ) {
					// parse other sections but only in group B. In treatment A no issues are shown for sections.
					$lead.nextAll( 'h1,h2,h3,h4,h5,h6' ).each( function ( i, headingEl ) {
						var $headingEl = $( headingEl ),
							$section = $headingEl.next(),
							sectionNum = $headingEl.find( '.edit-page' ).data( 'section' );

						createBanner( $section, label, sectionNum, inline );
					} );
				}
			}
		}

		// Setup the overlay route.
		overlayManager.add( new RegExp( '^/issues/(\\d+|' + KEYWORD_ALL_SECTIONS + ')$' ), function ( section ) {
			return new CleanupOverlay( {
				issues: getIssues( section ),
				// Note only the main namespace is expected to make use of section issues, so the heading will always be
				// minerva-meta-data-issues-section-header regardless of namespace
				headingText: section === '0' || section === KEYWORD_ALL_SECTIONS ? headingText :
					mw.msg( 'minerva-meta-data-issues-section-header' )
			} );
		} );
	}

	// Setup the issues banner on the page
	// Pages which dont exist (id 0) cannot have issues
	if ( !page.isMissing ) {
		$( initPageIssues );
	}

}( mw.mobileFrontend, jQuery ) );
