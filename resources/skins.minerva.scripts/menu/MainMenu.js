( function () {
	/**
	 * Representation of the main menu
	 *
	 * @class MainMenu
	 * @extends View
	 * @param {string} activator selector for element that when clicked can open or
	 *                                  close the menu
	 */
	function MainMenu( activator ) {
		// eslint-disable-next-line no-jquery/no-global-selector
		$( '#mw-mf-page-left' ).removeClass( 'navigation-drawer--loading' )
			.addClass( 'navigation-drawer--enabled' );
		this.activator = activator;
		this.registerClickEvents();
	}

	MainMenu.prototype = {
		/**
		 * Registers events for opening and closing the main menu
		 * @memberof MainMenu
		 * @instance
		 */
		registerClickEvents: function () {
			var self = this;

			// Listen to the main menu button clicks
			$( this.activator )
				.off( 'click' )
				.on( 'click', function ( ev ) {
					self.openNavigationDrawer();
					ev.preventDefault();
					// DO NOT USE stopPropagation or you'll break click tracking in WikimediaEvents
				} );
		},

		/**
		 * Check whether the navigation drawer is open
		 * @memberof MainMenu
		 * @instance
		 * @return {boolean}
		 */
		isOpen: function () {
			// FIXME: We should be moving away from applying classes to the body
			// eslint-disable-next-line no-jquery/no-class-state
			return $( document.body ).hasClass( 'navigation-enabled' );
		},

		/**
		 * Close all open navigation drawers
		 * @memberof MainMenu
		 * @instance
		 */
		closeNavigationDrawers: function () {
			// FIXME: We should be moving away from applying classes to the body
			$( document.body ).removeClass( 'navigation-enabled' )
				.removeClass( 'secondary-navigation-enabled' )
				.removeClass( 'primary-navigation-enabled' );
		},

		/**
		 * Toggle open navigation drawer
		 * @param {string} [drawerType] A name that identifies the navigation drawer that
		 *     should be toggled open. Defaults to 'primary'.
		 * @fires MainMenu#open
		 * @memberof MainMenu
		 * @instance
		 */
		openNavigationDrawer: function ( drawerType ) {
			// close any existing ones first.
			this.closeNavigationDrawers();
			drawerType = drawerType || 'primary';
			// FIXME: We should be moving away from applying classes to the body
			// eslint-disable-next-line no-jquery/no-class-state
			$( document.body )
				.toggleClass( 'navigation-enabled' )
				.toggleClass( drawerType + '-navigation-enabled' );
		}
	};

	module.exports = MainMenu;

}() );
