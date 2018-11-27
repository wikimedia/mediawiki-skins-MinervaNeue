( function ( M ) {
	var Page = M.require( 'mobile.startup/Page' ),
		allIssues = {},
		KEYWORD_ALL_SECTIONS = 'all',
		config = mw.config,
		NS_MAIN = 0,
		NS_TALK = 1,
		NS_CATEGORY = 14,
		CURRENT_NS = config.get( 'wgNamespaceNumber' ),
		features = mw.config.get( 'wgMinervaFeatures', {} ),
		pageIssuesParser = M.require( 'skins.minerva.scripts/pageIssuesParser' ),
		pageIssuesOverlay = M.require( 'skins.minerva.scripts/pageIssuesOverlay' ),
		// When the query string flag is set force on new treatment.
		// When wgMinervaPageIssuesNewTreatment is the default this line can be removed.
		QUERY_STRING_FLAG = mw.util.getParamValue( 'minerva-issues' ),
		newTreatmentEnabled = features.pageIssues || QUERY_STRING_FLAG;

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
	 * Creates a "read more" button with given text.
	 * @param {string} msg
	 * @return {JQuery}
	 */
	function createLearnMoreLink( msg ) {
		return $( '<span>' )
			.addClass( 'ambox-learn-more' )
			.text( msg );
	}

	/**
	 * Modifies the `issue` DOM to create a banner designed for
	 * single issue templates, and handles event-binding for that issues overlay.
	 *
	 * @param {IssueSummary} issue
	 * @param {string} msg
	 * @param {string} overlayUrl
	 * @param {Object} overlayManager
	 */
	function createPageIssueBanner( issue, msg, overlayUrl, overlayManager ) {
		var $learnMoreEl = createLearnMoreLink( msg ),
			$issueContainer = issue.$el.find( '.mbox-text' );

		$issueContainer.prepend( issue.iconString );
		$issueContainer.prepend( $learnMoreEl );

		issue.$el.click( function () {
			overlayManager.router.navigate( overlayUrl );
			return false;
		} );
	}

	/**
	 * Modifies the `issue` DOM to create a banner designed for
	 * multiple issue templates, and handles event-binding for that issues overlay.
	 *
	 * @param {IssueSummary} issue
	 * @param {string} msg
	 * @param {string} overlayUrl
	 * @param {Object} overlayManager
	 */
	function createPageIssueBannerMultiple( issue, msg, overlayUrl, overlayManager ) {
		var $learnMoreEl = createLearnMoreLink( msg ),
			$parentContentContainer = issue.$el.parents( '.mbox-text-span, .mbox-text-div' ),
			$parentContainer = issue.$el.parents( '.mbox-text' );

		$parentContentContainer.prepend( issue.iconString );
		$parentContentContainer.prepend( $learnMoreEl );

		$parentContainer.click( function () {
			overlayManager.router.navigate( overlayUrl );
			return false;
		} );
	}

	/**
	 * Modifies the page DOM to insert a page-issue notice below the title of the page,
	 * containing a link with a message like "this page has issues".
	 * Used on talk & category namespaces, or when page-issue banners have been disabled.s
	 *
	 * @param {string} labelText
	 * @param {string} section
	 */
	function createPageIssueNotice( labelText, section ) {
		var $link = createLinkElement( labelText );
		$link.attr( 'href', '#/issues/' + section );
		$link.insertAfter( $( 'h1#section_0' ) );
	}

	/**
	 * Render a banner in a containing element.
	 * if in group B, a learn more link will be append to any amboxes inside $container
	 * if in group A or control, any amboxes in container will be removed and a link "page issues"
	 * will be rendered above the heading.
	 * This function comes with side effects. It will populate a global "allIssues" object which
	 * will link section numbers to issues.
	 * @param {Page} page to search for page issues inside
	 * @param {string} labelText what the label of the page issues banner should say
	 * @param {string} section that the banner and its issues belong to.
	 *  If string KEYWORD_ALL_SECTIONS banner should apply to entire page.
	 * @param {boolean} inline - if true the first ambox in the section will become the entry point
	 *                           for the issues overlay
	 *  and if false, a link will be rendered under the heading.
	 * @param {OverlayManager} overlayManager
	 * @ignore
	 *
	 * @return {JQuery.Object}
	 */
	function createBanner( page, labelText, section, inline, overlayManager ) {
		var
			$metadata,
			issueUrl = section === KEYWORD_ALL_SECTIONS ? '#/issues/' + KEYWORD_ALL_SECTIONS : '#/issues/' + section,
			selector = 'table.ambox, table.tmbox, table.cmbox, table.fmbox',
			issueSummaries = [];

		if ( section === KEYWORD_ALL_SECTIONS ) {
			$metadata = page.$( selector );
		} else {
			// find heading associated with the section
			$metadata = page.findChildInSectionLead( parseInt( section, 10 ), selector );
		}
		// clean it up a little
		$metadata.find( '.NavFrame' ).remove();
		$metadata.each( function () {
			var issueSummary,
				$this = $( this );

			if ( $this.find( selector ).length === 0 ) {
				issueSummary = pageIssuesParser.extract( $this );
				// Some issues after "extract" has been run will have no text.
				// For example in Template:Talk header the table will be removed and no issue found.
				// These should not be rendered.
				if ( issueSummary.text ) {
					issueSummaries.push( issueSummary );
				}
			}
		} );
		// store it for later
		allIssues[ section ] = issueSummaries;

		if ( inline ) {
			issueSummaries.forEach( function ( issueSummary, i ) {
				var isGrouped = issueSummary.issue.grouped,
					lastIssueIsGrouped = issueSummaries[ i - 1 ] &&
						issueSummaries[ i - 1 ].issue.grouped;
				// only render the first grouped issue of each group
				if ( isGrouped && !lastIssueIsGrouped ) {
					createPageIssueBannerMultiple( issueSummary, mw.msg( 'skin-minerva-issue-learn-more' ), issueUrl, overlayManager );
				} else {
					createPageIssueBanner( issueSummary, mw.msg( 'skin-minerva-issue-learn-more' ), issueUrl, overlayManager );
				}
			} );
		} else if ( issueSummaries.length ) {
			createPageIssueNotice( labelText, section );
		}

		return $metadata;
	}

	/**
	 * Obtains the list of issues for the current page and provided section
	 * @param {number|string} section either KEYWORD_ALL_SECTIONS or a number relating to the
	 *                                section the issues belong to
	 * @return {jQuery.Object[]} array of all issues.
	 */
	function getIssues( section ) {
		if ( section !== KEYWORD_ALL_SECTIONS ) {
			return allIssues[ section ] || [];
		}
		// Note section.all may not exist, depending on the structure of the HTML page.
		// It will only exist when Minerva has been run in desktop mode.
		// If it's absent, we'll reduce all the other lists into one.
		return allIssues.all || Object.keys( allIssues ).reduce(
			function ( all, key ) {
				return all.concat( allIssues[ key ] );
			},
			[]
		);
	}

	/**
	 * Returns an array containing the section of each page issue.
	 * In the case that several page issues are grouped in a 'multiple issues' template,
	 * returns the section of those issues as one item.
	 * @param {Object} allIssues mapping section {Number} to {IssueSummary}
	 * @return {array}
	 */
	function getAllIssuesSections( allIssues ) {
		return Object.keys( allIssues ).reduce( function ( acc, section ) {
			if ( allIssues[ section ].length ) {
				allIssues[ section ].forEach( function ( issue, i ) {
					var lastIssue = allIssues[ section ][ i - 1 ];
					// If the last issue belongs to a "Multiple issues" template,
					// and so does the current one, don't add the current one.
					if ( lastIssue && lastIssue.grouped && issue.grouped ) {
						acc[ acc.length - 1 ] = section;
					} else {
						acc.push( section );
					}
				} );
			}
			return acc;
		}, [] );
	}

	/**
	 * Scan an element for any known cleanup templates and replace them with a button
	 * that opens them in a mobile friendly overlay.
	 * @ignore
	 * @param {OverlayManager} overlayManager
	 * @param {Page} page
	 */
	function initPageIssues( overlayManager, page ) {
		var label,
			$lead = page.getLeadSectionElement(),
			issueOverlayShowAll = CURRENT_NS === NS_CATEGORY || CURRENT_NS === NS_TALK || !$lead,
			inline = newTreatmentEnabled && CURRENT_NS === 0;

		// set A-B test class.
		// When wgMinervaPageIssuesNewTreatment is the default this can be removed.
		if ( newTreatmentEnabled ) {
			$( 'html' ).addClass( 'issues-group-B' );
		}

		if ( CURRENT_NS === NS_TALK || CURRENT_NS === NS_CATEGORY ) {
			// e.g. Template:English variant category; Template:WikiProject
			createBanner( page, mw.msg( 'mobile-frontend-meta-data-issues-header-talk' ),
				KEYWORD_ALL_SECTIONS, inline, overlayManager );
		} else if ( CURRENT_NS === NS_MAIN ) {
			label = mw.msg( 'mobile-frontend-meta-data-issues-header' );
			if ( issueOverlayShowAll ) {
				createBanner( page, label, KEYWORD_ALL_SECTIONS, inline, overlayManager );
			} else {
				// parse lead
				createBanner( page, label, '0', inline, overlayManager );
				if ( newTreatmentEnabled ) {
					// parse other sections but only in group B. In treatment A no issues are shown
					// for sections.
					page.$( Page.HEADING_SELECTOR ).each( function ( i, headingEl ) {
						var $headingEl = $( headingEl ),
							sectionNum = $headingEl.find( '.edit-page' ).data( 'section' );

						// Note certain headings matched using Page.HEADING_SELECTOR may not be
						// headings and will not have a edit link. E.g. table of contents.
						if ( sectionNum ) {
							// Render banner for sectionNum associated with headingEl inside
							// Page
							createBanner(
								page, label, sectionNum.toString(), inline, overlayManager
							);
						}
					} );
				}
			}
		}

		// Setup the overlay route.
		overlayManager.add( new RegExp( '^/issues/(\\d+|' + KEYWORD_ALL_SECTIONS + ')$' ), function ( section ) {
			return pageIssuesOverlay(
				getIssues( section ), section, CURRENT_NS
			);
		} );
	}

	M.define( 'skins.minerva.scripts/pageIssues', {
		init: initPageIssues,
		test: {
			getAllIssuesSections: getAllIssuesSections,
			createBanner: createBanner
		}
	} );

}( mw.mobileFrontend ) );
