( function ( M, $ ) {
	var AB = M.require( 'skins.minerva.scripts/AB' ),
		isInGroupB = new AB(
			'WME.PageIssuesAB',
			mw.config.get( 'wgMinervaABSamplingRate', 0 ),
			mw.user.sessionId()
		).getBucket() === 'B',
		Icon = M.require( 'mobile.startup/Icon' );

	( function () {
		var action = mw.config.get( 'wgAction' ),
			page = M.getCurrentPage(),
			getIconFromAmbox = M.require( 'skins.minerva.scripts/utils' )
				.getIconFromAmbox,
			overlayManager = M.require( 'skins.minerva.scripts/overlayManager' ),
			CleanupOverlay = M.require( 'mobile.issues/CleanupOverlay' );

		/**
		 * Extract a summary message from a cleanup template generated element that is
		 * friendly for mobile display.
		 * @param {Object} $box element to extract the message from
		 * @ignore
		 * @typedef {object} IssueSummary
		 * @prop {string} icon HTML string.
		 * @prop {string} text HTML string.
		 * @return {IssueSummary}
		 */
		function extractMessage( $box ) {
			var selector = '.mbox-text, .ambox-text',
				$container = $( '<div>' );

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
			return {
				icon: getIconFromAmbox( $box ).toHtmlString(),
				text: $container.html()
			};
		}

		/**
		 * Create a link element that opens the issues overlay.
		 *
		 * @ignore
		 *
		 * @param {string} labelText The text value of the element
		 * @return {jQuery}
		 */
		function createLinkElement( labelText ) {
			return $( '<a class="cleanup mw-mf-cleanup"></a>' )
				.text( labelText );
		}

		/**
		 * Render a banner in a containing element.
		 * @param {jQuery.Object} $container to render the page issues banner inside.
		 * @param {string} labelText what the label of the page issues banner should say
		 * @param {string} headingText the heading of the overlay that is created when the page issues banner is clicked
		 * @param {boolean} inline - if true the first ambox in the section will become the entry point for the issues overlay
		 *  and if false, a link will be rendered under the heading.
		 * @ignore
		 */
		function createBanner( $container, labelText, headingText, inline ) {
			var $learnMore,
				selector = 'table.ambox, table.tmbox, table.cmbox, table.fmbox',
				$metadata = $container.find( selector ),
				issues = [],
				$link;

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

			if ( inline ) {
				new Icon( {
					glyphPrefix: 'minerva',
					name: 'warning',
					isSmall: true
				} ).prependTo( $metadata.find( '.mbox-text' ) );
				$learnMore = $( '<span>' )
					.addClass( 'ambox-learn-more' )
					.text( mw.msg( 'skin-minerva-issue-learn-more' ) );
				if ( $( '.mw-collapsible-content' ).length ) {
					// e.g. Template:Multiple issues
					$learnMore.insertBefore( $metadata.find( '.mw-collapsible-content' ) );
				} else {
					// e.g. Template:merge from
					$learnMore.appendTo( $metadata.find( '.mbox-text-span' ) );
				}
				$metadata.click( function () {
					overlayManager.router.navigate( '#/issues' );
					return false;
				} );
			} else {
				$link = createLinkElement( labelText );
				$link.attr( 'href', '#/issues' );
				if ( $metadata.length ) {
					$link.insertAfter( $( 'h1#section_0' ) );
					$metadata.remove();
				}
			}

			overlayManager.add( /^\/issues$/, function () {
				return new CleanupOverlay( {
					issues: issues,
					headingText: headingText
				} );
			} );
		}

		/**
		 * Scan an element for any known cleanup templates and replace them with a button
		 * that opens them in a mobile friendly overlay.
		 * @ignore
		 */
		function initPageIssues() {
			var ns = mw.config.get( 'wgNamespaceNumber' ),
				inline = isInGroupB && ns === 0,
				// Categories have no lead section
				$container = ns === 14 || isInGroupB ? $( '#bodyContent' ) :
					page.getLeadSectionElement();

			// set A-B test class.
			$( 'html' ).addClass( isInGroupB ? 'issues-group-B' : 'issues-group-A' );
			if ( action === 'edit' ) {
				$container = $( '#mw-content-text' );
			} else if ( $container === null ) {
				return;
			}

			if ( action === 'edit' ) {
				createBanner( $container, mw.msg( 'edithelp' ),
					mw.msg( 'edithelp' ), inline );
			} else if ( ns === 0 ) {
				createBanner( $container, mw.msg( 'mobile-frontend-meta-data-issues' ),
					mw.msg( 'mobile-frontend-meta-data-issues-header' ), inline );
			// Create a banner for talk pages (namespace 1) in beta mode to make them more readable.
			} else if ( ns === 1 ) {
				createBanner( $container, mw.msg( 'mobile-frontend-meta-data-issues-talk' ),
					mw.msg( 'mobile-frontend-meta-data-issues-header-talk' ), inline );
			} else if ( ns === 14 ) {
				createBanner( $container, mw.msg( 'mobile-frontend-meta-data-issues-categories' ),
					mw.msg( 'mobile-frontend-meta-data-issues-header-talk' ), inline );
			}
		}

		// Setup the issues banner on the page
		// Pages which dont exist (id 0) cannot have issues
		if ( !page.isMissing ) {
			initPageIssues();
		}
	}() );

}( mw.mobileFrontend, jQuery ) );
