( function ( M ) {
	var
		mobile = M.require( 'mobile.startup' ),
		downloadPageAction = M.require( 'skins.minerva.scripts/downloadPageAction' ),
		Icon = mobile.Icon,
		skin = M.require( 'mobile.init/skin' ),
		/** The top level menu. */
		toolbarSelector = '.page-actions-menu',
		/** The secondary overflow submenu component container. */
		overflowSubmenuSelector = '#page-actions-overflow',
		/** The visible label icon associated with the checkbox. */
		overflowButtonSelector = '.toolbar-overflow-menu__button',
		/** The underlying hidden checkbox that controls secondary overflow submenu visibility. */
		overflowCheckboxSelector = '#toolbar-overflow-menu__checkbox',
		overflowListSelector = '.toolbar-overflow-menu__list';

	/**
	 * @param {Window} window
	 * @param {Element} toolbar
	 * @param {OO.EventEmitter} eventBus
	 * @return {void}
	 */
	function bind( window, toolbar, eventBus ) {
		var
			overflowSubmenu = toolbar.querySelector( overflowSubmenuSelector ),
			overflowButton = toolbar.querySelector( overflowButtonSelector ),
			overflowCheckbox = toolbar.querySelector( overflowCheckboxSelector ),
			overflowList = toolbar.querySelector( overflowListSelector );

		if ( overflowSubmenu ) {
			bindOverflowSubmenu(
				window, overflowSubmenu, overflowButton, overflowCheckbox, overflowList, eventBus
			);
		}
	}

	/**
	 * @param {Window} window
	 * @param {Element} toolbar
	 * @return {void}
	 */
	function render( window, toolbar ) {
		var overflowList = toolbar.querySelector( overflowListSelector );
		renderEditButton();
		renderDownloadButton( window, overflowList );
		if ( overflowList ) {
			resizeOverflowList( overflowList );
		}
	}

	/**
	 * Automatically dismiss the submenu when clicking or focusing elsewhere, resize the menu on
	 * scroll and window resize, and update the aria-expanded attribute based on submenu visibility.
	 * @param {Window} window
	 * @param {Element} submenu
	 * @param {Element} button
	 * @param {HTMLInputElement} checkbox
	 * @param {Element} list
	 * @param {OO.EventEmitter} eventBus
	 * @return {void}
	 */
	function bindOverflowSubmenu( window, submenu, button, checkbox, list, eventBus ) {
		var
			resize = resizeOverflowList.bind( undefined, list ),
			updateAriaExpanded = function () {
				checkbox.setAttribute( 'aria-expanded', ( !!checkbox.checked ).toString() );
			};

		window.addEventListener( 'click', function ( event ) {
			if ( event.target !== button && event.target !== checkbox ) {
				// Something besides the button or checkbox was tapped. Dismiss the submenu.
				checkbox.checked = false;
				updateAriaExpanded();
			}
		} );

		// If focus is given to any element outside the menu, dismiss the submenu. Setting a
		// focusout listener on submenu would be preferable, but this interferes with the click
		// listener.
		window.addEventListener( 'focusin', function ( event ) {
			if ( event.target instanceof Node && !submenu.contains( event.target ) ) {
				// Something besides the button or checkbox was focused. Dismiss the menu.
				checkbox.checked = false;
				updateAriaExpanded();
			}
		} );

		eventBus.on( 'scroll:throttled', resize );
		eventBus.on( 'resize:throttled', resize );

		checkbox.addEventListener( 'change', updateAriaExpanded );
	}

	/**
	 * @param {HTMLElement} list
	 * @return {void}
	 */
	function resizeOverflowList( list ) {
		var rect = list.getClientRects()[ 0 ];
		if ( rect ) {
			list.style.maxHeight = window.document.documentElement.clientHeight - rect.top + 'px';
		}
	}

	/**
	 * Initialize page edit action link (#ca-edit)
	 *
	 * Mark the edit link as disabled if the user is not actually able to edit the page for some
	 * reason (e.g. page is protected or user is blocked).
	 *
	 * Note that the link is still clickable, but clicking it will probably open a view-source
	 * form or display an error message, rather than open an edit form.
	 *
	 * FIXME: Review this code as part of T206262
	 *
	 * @ignore
	 */
	function renderEditButton() {
		var
			// FIXME: create a utility method to generate class names instead of
			//       constructing temporary objects. This affects disabledEditIcon,
			//       enabledEditIcon, enabledEditIcon, and disabledClass and
			//       a number of other places in the code base.
			disabledEditIcon = new Icon( {
				name: 'edit',
				glyphPrefix: 'minerva'
			} ),
			enabledEditIcon = new Icon( {
				name: 'edit-enabled',
				glyphPrefix: 'minerva'
			} ),
			enabledClass = enabledEditIcon.getGlyphClassName(),
			disabledClass = disabledEditIcon.getGlyphClassName();

		if ( mw.config.get( 'wgMinervaReadOnly' ) ) {
			// eslint-disable-next-line no-jquery/no-global-selector
			$( '#ca-edit' )
				.removeClass( enabledClass )
				.addClass( disabledClass );
		}
	}

	/**
	 * Initialize and inject the download button
	 *
	 * There are many restrictions when we can show the download button, this function should handle
	 * all device/os/operating system related checks and if device supports printing it will inject
	 * the Download icon
	 * @param {Window} window
	 * @param {Element|null} overflowList
	 * @return {void}
	 */
	function renderDownloadButton( window, overflowList ) {
		var $downloadAction = downloadPageAction( skin,
			mw.config.get( 'wgMinervaDownloadNamespaces', [] ), window, !!overflowList );

		if ( $downloadAction ) {
			if ( overflowList ) {
				$downloadAction.appendTo( overflowList );
			} else {
				$downloadAction.insertAfter( '.page-actions-menu__list-item:first-child' );
			}

			mw.track( 'minerva.downloadAsPDF', {
				action: 'buttonVisible'
			} );
		}
	}

	M.define( 'skins.minerva.scripts/Toolbar', {
		selector: toolbarSelector,
		bind: bind,
		render: render
	} );
}( mw.mobileFrontend ) );
