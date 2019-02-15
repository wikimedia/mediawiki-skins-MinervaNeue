( function ( M ) {
	var
		mobile = M.require( 'mobile.startup' ),
		mfExtend = mobile.mfExtend,
		browser = mobile.Browser.getSingleton(),
		View = mobile.View;

	/**
	 * Representation of the main menu
	 *
	 * @class MainMenu
	 * @extends View
	 * @param {Object} options Configuration options
	 */
	function MainMenu( options ) {
		this.activator = options.activator;
		View.call( this, options );
	}

	mfExtend( MainMenu, View, {
		isTemplateMode: true,
		template: mw.template.get( 'skins.minerva.scripts', 'menu.hogan' ),
		templatePartials: {
			menuGroup: mw.template.get( 'skins.minerva.scripts', 'menuGroup.hogan' )
		},

		/**
		 * @cfg {object} defaults Default options hash.
		 * @cfg {string} defaults.activator selector for element that when clicked can open or
		 *                                  close the menu
		 */
		defaults: {
			activator: undefined
		},

		/**
		 * Turn on event logging on the existing main menu by reading `event-name` data
		 * attributes on elements.
		 * @memberof MainMenu
		 * @instance
		 */
		enableLogging: function () {
			this.$el.find( 'a' ).on( 'click', function () {
				var $link = $( this ),
					eventName = $link.data( 'event-name' );
				if ( eventName ) {
					mw.track( 'minerva.schemaMobileWebMainMenuClickTracking', {
						name: eventName,
						destination: $link.attr( 'href' )
					} );
				}
			} );
		},
		/**
		 * Remove the nearby menu entry if the browser doesn't support geo location
		 * @memberof MainMenu
		 * @instance
		 */
		postRender: function () {
			if ( !browser.supportsGeoLocation() ) {
				this.$el.find( '.nearby' ).parent().remove();
			}

			this.registerClickEvents();
		},

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
					if ( self.isOpen() ) {
						self.closeNavigationDrawers();
					} else {
						self.openNavigationDrawer();
					}
					ev.preventDefault();
					// Stop propagation, otherwise the Skin will close the open menus on page center
					// click
					ev.stopPropagation();
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
			$( document.body ).toggleClass( 'navigation-enabled' )
				.toggleClass( drawerType + '-navigation-enabled' );

			this.emit( 'open' );
		}
	} );

	M.define( 'skins.minerva.scripts/MainMenu', MainMenu );

}( mw.mobileFrontend ) );
