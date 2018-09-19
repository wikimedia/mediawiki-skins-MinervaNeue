( function ( M, $ ) {
	var AB = M.require( 'skins.minerva.scripts/AB' ),
		Page = M.require( 'mobile.startup/Page' ),
		allIssues = {},
		KEYWORD_ALL_SECTIONS = 'all',
		config = mw.config,
		user = mw.user,
		NS_MAIN = 0,
		NS_TALK = 1,
		NS_CATEGORY = 14,
		CURRENT_NS = config.get( 'wgNamespaceNumber' ),
		Icon = M.require( 'mobile.startup/Icon' ),
		pageIssuesLogger = M.require( 'skins.minerva.scripts/pageIssuesLogger' ),
		pageIssuesParser = M.require( 'skins.minerva.scripts/pageIssuesParser' ),
		PageIssuesOverlay = M.require( 'skins.minerva.scripts/PageIssuesOverlay' ),
		// setup ab test
		abTest = new AB( {
			testName: 'WME.PageIssuesAB',
			// Run AB only on article namespace, otherwise set samplingRate to 0,
			// forcing user into control (i.e. ignored/not logged) group.
			samplingRate: ( CURRENT_NS === NS_MAIN ) ? config.get( 'wgMinervaABSamplingRate', 0 ) : 0,
			sessionId: user.sessionId()
		} ),
		QUERY_STRING_FLAG = mw.util.getParamValue( 'minerva-issues' ),
		// Per T204746 a user can request the new treatment regardless of test group
		isUserRequestingNewTreatment = QUERY_STRING_FLAG === 'b',
		newTreatmentEnabled = abTest.isB() || isUserRequestingNewTreatment;

	/**
	 * @typedef {Object} IssueSummary
	 * @prop {string} severity A PageIssue.severity.
	 * @prop {Boolean} isMultiple Whether or not the issue is part of a "multiple issues" template.
	 * @prop {string} icon HTML string.
	 * @prop {string} text HTML string.
	*/

	function isLoggingRequired( pageIssues ) {
		// No logging necessary when the A/B test is disabled (control group).
		return !isUserRequestingNewTreatment && abTest.isEnabled() && pageIssues.length;
	}

	/**
	 * Array.reduce callback that returns the severity of page issues.
	 * In the case that a page-issue is part of a "multiple issues" template,
	 * returns the maximum severity for that group of issues.
	 *
	 * @param {array} formattedArr - the return array containing severities
	 * @param {IssueSummary} currentItem current IssueSummary object
	 * @param {number} currentIndex current index of pageIssues
	 * @param {array} pageIssues array of pageIssues
	 *
	 * @return {array} acc
	 */
	function formatPageIssuesSeverity( formattedArr, currentItem, currentIndex, pageIssues ) {
		var lastItem = pageIssues[ currentIndex - 1 ],
			lastFormattedIndex = formattedArr.length - 1,
			lastFormattedValue = formattedArr[ lastFormattedIndex ];
		// If the last and current item `isMultiple`, fold the maxSeverity
		// of the two items into a single value.
		if ( lastItem && lastItem.isMultiple && currentItem.isMultiple ) {
			formattedArr[ lastFormattedIndex ] = pageIssuesParser.maxSeverity(
				[ lastFormattedValue, currentItem.severity ]
			);
		} else {
			formattedArr.push( currentItem.severity );
		}
		return formattedArr;
	}

	/**
	 * Extract a summary message from a cleanup template generated element that is
	 * friendly for mobile display.
	 * @param {Object} $box element to extract the message from
	 * @return {IssueSummary}
	 */
	function extractMessage( $box ) {
		var SELECTOR = '.mbox-text, .ambox-text',
			MULTIPLE_SELECTOR = '.mw-collapsible-content',
			$container = $( '<div>' ),
			pageIssue;

		$box.find( SELECTOR ).each( function () {
			var contents,
				$this = $( this );
			// Clean up talk page boxes
			$this.find( 'table, .noprint' ).remove();
			contents = $this.html();

			if ( contents ) {
				$( '<p>' ).html( contents ).appendTo( $container );
			}
		} );

		pageIssue = pageIssuesParser.parse( $box.get( 0 ) );

		return {
			severity: pageIssue.severity,
			isMultiple: $box.parent().is( MULTIPLE_SELECTOR ),
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
		var $learnMore, $metadata,
			issueUrl = section === KEYWORD_ALL_SECTIONS ? '#/issues/' + KEYWORD_ALL_SECTIONS : '#/issues/' + section,
			selector = 'table.ambox, table.tmbox, table.cmbox, table.fmbox',
			issues = [],
			$link,
			severity;

		if ( section === KEYWORD_ALL_SECTIONS ) {
			$metadata = page.$( selector );
		} else {
			// find heading associated with the section
			$metadata = page.findChildInSectionLead( parseInt( section, 10 ), selector );
		}
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
		// store it for later
		allIssues[section] = issues;

		if ( $metadata.length && inline ) {
			severity = pageIssuesParser.maxSeverity(
				issues.map( function ( issue ) { return issue.severity; } )
			);
			new Icon( {
				glyphPrefix: 'minerva',
				name: pageIssuesParser.iconName( $metadata.get( 0 ), severity )
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
				var pageIssue = pageIssuesParser.parse( this );
				pageIssuesLogger.log( {
					action: 'issueClicked',
					issuesSeverity: [ pageIssue.severity ],
					sectionNumbers: [ section ]
				} );
				overlayManager.router.navigate( issueUrl );
				return false;
			} );
		} else {
			$link = createLinkElement( labelText );
			$link.attr( 'href', '#/issues/' + section );
			$link.click( function () {
				pageIssuesLogger.log( {
					action: 'issueClicked',
					issuesSeverity: [
						pageIssuesParser.maxSeverity(
							getIssues( '0' )
								.map( function ( issue ) { return issue.severity; } )
						)
					],
					// In the old treatment, an issuesClicked event will always be '0'
					// as the old treatment is always associated with the lead section and we
					// are only sending one maximum severity for all of them.
					// An issuesClicked event should only ever be associated with one issue box.
					sectionNumbers: [ '0' ]
				} );
			} );
			if ( $metadata.length ) {
				$link.insertAfter( $( 'h1#section_0' ) );
				$metadata.remove();
			}
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
			return allIssues[section] || [];
		}
		// Note section.all may not exist, depending on the structure of the HTML page.
		// It will only exist when Minerva has been run in desktop mode.
		// If it's absent, we'll reduce all the other lists into one.
		return allIssues.all || Object.keys( allIssues ).reduce(
			function ( all, key ) {
				return all.concat( allIssues[key] );
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
					var lastIssue = allIssues[ section ][i - 1];
					// If the last issue belongs to a "Multiple issues" template,
					// and so does the current one, don't add the current one.
					if ( lastIssue && lastIssue.isMultiple && issue.isMultiple ) {
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
		$( 'html' ).addClass( newTreatmentEnabled ? 'issues-group-B' : 'issues-group-A' );

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

		if ( isLoggingRequired( getIssues( KEYWORD_ALL_SECTIONS ) ) ) {
			// Enable logging of the PageIssues schema, setting up defaults.
			pageIssuesLogger.subscribe(
				newTreatmentEnabled,
				pageIssuesLogger.newPageIssueSchemaData(
					newTreatmentEnabled,
					CURRENT_NS,
					getIssues( KEYWORD_ALL_SECTIONS ).reduce( formatPageIssuesSeverity, [] ),
					getAllIssuesSections( allIssues )
				)
			);

			// Report that the page has been loaded.
			pageIssuesLogger.log( {
				action: 'pageLoaded'
			} );
		}

		// Setup the overlay route.
		overlayManager.add( new RegExp( '^/issues/(\\d+|' + KEYWORD_ALL_SECTIONS + ')$' ), function ( section ) {
			return new PageIssuesOverlay(
				getIssues( section ), pageIssuesLogger, section, CURRENT_NS );
		} );
	}

	M.define( 'skins.minerva.scripts/pageIssues', {
		init: initPageIssues,
		// The logger requires initialization (subscription). Ideally, the logger would be
		// initialized and passed to initPageIssues() by the client. Since it's not, expose a log
		// method and hide the subscription call in cleanuptemplates.
		log: pageIssuesLogger.log,
		test: {
			formatPageIssuesSeverity: formatPageIssuesSeverity,
			extractMessage: extractMessage,
			getAllIssuesSections: getAllIssuesSections,
			createBanner: createBanner
		}
	} );

}( mw.mobileFrontend, jQuery ) );
