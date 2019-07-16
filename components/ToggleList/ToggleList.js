( function () {
	var
		/** The component selector. */
		selector = '.toggle-list',
		/** The visible label icon associated with the checkbox. */
		toggleSelector = '.toggle-list__toggle',
		/** The underlying hidden checkbox that controls list visibility. */
		checkboxSelector = '.toggle-list__checkbox',
		listSelector = '.toggle-list__list';

	/**
	 * Automatically dismiss the list when clicking or focusing elsewhere and update the
	 * aria-expanded attribute based on list visibility.
	 * @param {Window} window
	 * @param {HTMLElement} component
	 * @param {OO.EventEmitter} eventBus
	 * @param {boolean} [resize] If true, resize the menu on scroll and window resize.
	 * @return {void}
	 */
	function bind( window, component, eventBus, resize ) {
		var
			toggle = component.querySelector( toggleSelector ),
			checkbox = /** @type {HTMLInputElement} */ (
				component.querySelector( checkboxSelector )
			),
			list = component.querySelector( listSelector );

		window.addEventListener( 'click', function ( event ) {
			if ( event.target !== toggle && event.target !== checkbox ) {
				// Something besides the button or checkbox was tapped. Dismiss the list.
				_dismiss( checkbox );
			}
		}, true );

		// If focus is given to any element outside the list, dismiss the list. Setting a focusout
		// listener on list would be preferable, but this interferes with the click listener.
		window.addEventListener( 'focusin', function ( event ) {
			if ( event.target instanceof Node && !component.contains( event.target ) ) {
				// Something besides the button or checkbox was focused. Dismiss the list.
				_dismiss( checkbox );
			}
		}, true );

		checkbox.addEventListener( 'change', _updateAriaExpanded.bind( undefined, checkbox ) );

		if ( resize ) {
			eventBus.on( 'scroll:throttled', _resize.bind( undefined, list ) );
			eventBus.on( 'resize:throttled', _resize.bind( undefined, list ) );
		}
	}

	/**
	 * @param {HTMLElement} component
	 * @param {boolean} [resize] If true, resize the menu to fit within the window.
	 * @return {void}
	 */
	function render( component, resize ) {
		var list = /** @type {HTMLElement} */ ( component.querySelector( listSelector ) );
		if ( resize ) {
			_resize( list );
		}
	}

	/**
	 * Hides the list.
	 * @param {HTMLInputElement} checkbox
	 * @return {void}
	 */
	function _dismiss( checkbox ) {
		checkbox.checked = false;
		_updateAriaExpanded( checkbox );
	}

	/**
	 * @param {HTMLElement} list
	 * @return {void}
	 */
	function _resize( list ) {
		var rect = list.getClientRects()[ 0 ];
		if ( rect ) {
			list.style.maxHeight = window.document.documentElement.clientHeight - rect.top + 'px';
		}
	}

	/**
	 * Revise the aria-expanded state to match the checked state.
	 * @param {HTMLInputElement} checkbox
	 * @return {void}
	 */
	function _updateAriaExpanded( checkbox ) {
		checkbox.setAttribute( 'aria-expanded', ( !!checkbox.checked ).toString() );
	}

	module.exports = Object.freeze( {
		selector: selector,
		render: render,
		bind: bind
	} );
}() );
