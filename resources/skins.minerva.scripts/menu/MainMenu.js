( function ( M ) {
	var
		mobile = M.require( 'mobile.startup' ),
		mfExtend = mobile.mfExtend,
		browser = mobile.Browser.getSingleton(),
		util = mobile.util,
		View = mobile.View;

	/**
	 * Representation of the main menu
	 *
	 * @class MainMenu
	 * @extends View
	 * @param {Object} options Configuration options
	 * @param {Function} options.onOpen executed when the menu opens
	 * @param {Function} options.onClose executed when the menu closes
	 */
	function MainMenu( options ) {
		this.activator = options.activator;
		View.call( this,
			util.extend( {
				onOpen: function () {},
				onClose: function () {}
			}, options )
		);
	}

	mfExtend( MainMenu, View, {
		isTemplateMode: true,
		template: mw.template.get( 'skins.minerva.scripts', 'menu.mustache' ),
		templatePartials: {
			menuGroup: mw.template.get( 'skins.minerva.scripts', 'menuGroup.mustache' )
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
		 * @private
		 */
		isOpen: function () {
			return this.$el.hasClass( 'menu--open' );
		},

		/**
		 * Close all open navigation drawers
		 * @memberof MainMenu
		 * @instance
		 * @private
		 */
		closeNavigationDrawers: function () {
			this.$el.removeClass( 'menu--open' );
			this.options.onClose();
		},

		/**
		 * Toggle open navigation drawer
		 * @memberof MainMenu
		 * @instance
		 * @private
		 */
		openNavigationDrawer: function () {
			// close any existing ones first.
			this.closeNavigationDrawers();
			this.$el.addClass( 'menu--open' );
			this.options.onOpen();
		}
	} );

	module.exports = MainMenu;

}( mw.mobileFrontend ) );
